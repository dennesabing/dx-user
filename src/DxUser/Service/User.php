<?php

namespace DxUser\Service;

use Dxapp\Service\Dx as DxService;
use DxUser\Entity\UserCodesInterface;
use Zend\Crypt\Password\Bcrypt;

class User extends DxService
{
	/**
	 * Update user profile
	 * @param object $user
	 * @param array $profile Form Data Object or array
	 * 
	 * @return boolean
	 */
	public function updateProfile(array $profile, $user = NULL)
	{
		if(NULL === $user)
		{
			$user = $this->getCurrentUser();
		}
		$user->setDataArray($profile);
		$userProfileRepo = $this->getUserProfileRepo();
		$userProfile = $userProfileRepo->findByUser($user);
		$userProfile->setDataArray($profile);
		$this->getUserRepo()->update($user);
		return $userProfileRepo->update($userProfile);
	}
	
	/**
	 * Change an email address
	 * @param array $data
	 * @param type $user
	 * @return boolean
	 */
	public function changeEmail(array $data, $user)
	{
		$success = FALSE;
		if (NULL === $user)
		{
			$email = $data['email'];
			$user = $this->getAuth()->getIdentity();
			$user->setEmail($email);
			$user->unVerifyEmailAddress(FALSE);
			$this->getEventManager()->trigger(__FUNCTION__, $this, array('user' => $user));
			$this->getUserRepo()->update($user);
			if ($this->getModuleOptions('dxuser')->getEnableEmailVerification())
			{
				$userCodesRepo = $this->getEntityManager()->getRepository($this->getModuleOptions()->getEntityUserCode());
				$typeOf = 'verify-email';
				$userCodesRepo->removeCode($user, $typeOf);
				$userCodeClass = $this->getModuleOptions()->getEntityUserCode();
				$userCode = new $userCodeClass;
				$userCode->addExtra('email', $email);
				$userCode->setUser($user);
				$userCode->setTypeOf($typeOf);
				$userCode->setCode(md5(time() . $userCode->getTypeOf() . $user->getEmail()));
				$this->em->persist($userCode);
				$this->em->flush();
				$this->sendVerifyEmail($userCode);
				$success = TRUE;
			}
			$this->getEventManager()->trigger(__FUNCTION__, $this, array('user' => $user, 'userCode' => $userCode));
			if (!$success)
			{
				return FALSE;
			}
		}
		if (isset($userCode))
		{
			return $userCode;
		}
		return TRUE;
	}

	/**
	 * Resend the email verification code
	 * @return boolean
	 */
	public function resendEmailVerification()
	{
		$user = $this->getAuth()->getIdentity();
		$this->getEventManager()->trigger(__FUNCTION__, $this, array('user' => $user));
		$userCodesRepo = $this->getEntityManager()->getRepository($this->getModuleOptions()->getEntityUserCode());
		$typeOf = 'verify-email';
		$formerCode = $userCodesRepo->findUserCode($user, $typeOf, FALSE);
		$userCodesRepo->removeCode($user, $typeOf);
		$userCodeClass = $this->getModuleOptions()->getEntityUserCode();
		$userCode = new $userCodeClass;
		if ($formerCode)
		{
			$oldEmail = $formerCode->getExtra('email');
			$userCode->addExtra('email', $oldEmail);
		}
		$userCode->setUser($user);
		$userCode->setTypeOf($typeOf);
		$userCode->setCode(md5(time() . $userCode->getTypeOf() . $user->getEmail()));
		$this->em->persist($userCode);
		$this->em->flush();
		$this->sendVerifyEmail($userCode);
		$this->getEventManager()->trigger(__FUNCTION__, $this, array('user' => $user, 'userCode' => $userCode));
		return $userCode;
	}

	/**
	 * Two Stage SignUp
	 *
	 * @param array $data The User Object
	 * @return \DxUser\Entity\UserCodes|NULL
	 * @throws Exception\InvalidArgumentException
	 */
	public function register($data)
	{
		if (is_array($data))
		{
			$userClass = $this->getModuleOptions()->getUserEntityClass();
			$user = new $userClass;
			$userProfile = new \DxUser\Entity\UserProfile;
			$user->setEmail($data['fsMain']['email']);
			$user->setPassword($data['fsMain']['newPassword']);
			$user->setProfile($userProfile);
			$user->unVerifyEmailAddress();

			$bcrypt = new Bcrypt;
			$bcrypt->setCost($this->getZfcUserOptions()->getPasswordCost());
			$user->setPassword($bcrypt->create($user->getPassword()));

			if ($this->getZfcUserOptions()->getEnableUsername())
			{
				$user->setUsername($data['username']);
			}
			if ($this->getZfcUserOptions()->getEnableDisplayName())
			{
				$user->setDisplayName($data['display_name']);
			}
			$this->getEventManager()->trigger(__FUNCTION__, $this, array('user' => $user));
			$this->getUserRepo()->insert($user);
			if ($this->getModuleOptions()->getEnableEmailVerification())
			{
				$userCodesRepo = $this->getEntityManager()->getRepository($this->getModuleOptions()->getEntityUserCode());
				$typeOf = 'verify-email';
				$userCodesRepo->removeCode($user, $typeOf);
				$userCodeClass = $this->getModuleOptions()->getEntityUserCode();
				$userCode = new $userCodeClass;
				$userCode->setUser($user);
				$userCode->setTypeOf($typeOf);
				$userCode->setCode(md5(time() . $userCode->getTypeOf() . $user->getEmail()));
				$this->em->persist($userCode);
				$this->em->flush();
				$this->sendVerifyEmail($userCode);
				return $userCode;
			}
			$this->getEventManager()->trigger(__FUNCTION__ . '.post', $this, array('user' => $user));
			return $user;
		}
	}

	/**
	 * Send email to new registered user
	 * @param object $userCode 
	 */
	public function sendVerifyEmail(UserCodesInterface $userCode)
	{
		$message = new Message();
		$message->addFrom($this->getModuleOptions()->getEmailNoReplySender(), $this->getModuleOptions()->getEmailNoReplySender())
				->addTo($userCode->getUser()->getEmail())
				->setSubject($this->getModuleOptions()->getEmailVerifySubject());
		$viewModel = new ViewModel(array(
					'userCode' => $userCode,
				));
		$viewModel->setTemplate($this->getModuleOptions()->getTemplateVerifyEmail());
		$body = $this->renderer->render($viewModel);
		$message->setBody($body);
		$transport = new SendmailTransport($this->getServiceManager());
		$transport->send($message);
	}

	/**
	 * Check user during email verification
	 * And verify user if needed.
	 * @param array $data 
	 * @throws Exception\InvalidArgumentException
	 * 
	 * @return boolean|string FALSE if error, TRUE if successfull, string if already verified and others.
	 */
	public function verifyEmail(array $data)
	{
		if (isset($data['user']) && isset($data['code']))
		{
			$user = isset($data['user']) ? $data['user'] : NULL;
			if (isset($data['email']) && NULL === $user)
			{
				$user = $this->getUserByEmail($data['email']);
			}
			$code = $data['code'];
			$typeOf = 'verify-email';
			$userCodesRepo = $this->getEntityManager()->getRepository($this->getModuleOptions()->getEntityUserCode());
			$userCode = $userCodesRepo->findUserCode($user, $typeOf, $code);
			if ($userCode)
			{
				$this->getEventManager()->trigger(__FUNCTION__, $this, array('user' => $user, 'userCode' => $userCode));
				$user->verifyEmailAddress();
				$this->em->persist($user);
				$this->em->remove($userCode);
				$this->em->flush();
				$this->getEventManager()->trigger(__FUNCTION__ . '.post', $this, array('user' => $user, 'userCode' => $userCode));
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 * Reset password. Send a code to user to reset password
	 * @param string $email 
	 * @return boolean|string FALSE if error, TRUE if successfull, string for other errors
	 */
	public function resetPassword($email)
	{
		$userRepo = $this->getEntityManager()->getRepository($this->getModuleOptions()->getUserEntityClass());
		$user = $userRepo->findByEmail($email);
		if ($user)
		{
			$userCodesRepo = $this->getEntityManager()->getRepository($this->getModuleOptions()->getEntityUserCode());
			$typeOf = 'reset-password';
			$userCodesRepo->removeCode($user, $typeOf);
			$userCodeEntity = $this->getModuleOptions()->getEntityUserCode();
			$userCode = new $userCodeEntity;
			$userCode->setUser($user);
			$userCode->setTypeOf($typeOf);
			$userCode->setCode(md5(time() . $userCode->getTypeOf() . $user->getEmail()));
			$this->getEventManager()->trigger(__FUNCTION__, $this, array('user' => $user, 'userCode' => $userCode));
			$this->em->persist($userCode);
			$this->em->flush();
			$this->sendResetPassword($userCode);
			$this->getEventManager()->trigger(__FUNCTION__ . '.post', $this, array('user' => $user, 'userCode' => $userCode));
			return $userCode;
		}
		return FALSE;
	}

	/**
	 * Send an email to user witha  link to reset password
	 * @param UserCodesInterface $userCode 
	 */
	public function sendResetPassword(UserCodesInterface $userCode)
	{
		$message = new Message();
		$message->addFrom($this->getModuleOptions()->getEmailNoReplySender(), $this->getModuleOptions()->getEmailNoReplySender())
				->addTo($userCode->getUser()->getEmail())
				->setSubject($this->getModuleOptions()->getEmailResetPasswordSubject());
		$viewModel = new ViewModel(array(
					'userCode' => $userCode,
				));
		$viewModel->setTemplate($this->getModuleOptions()->getTemplateResetPasswordEmail());
		$body = $this->renderer->render($viewModel);
		$message->setBody($body);
		$this->send($message);
	}

	/**
	 * Verify if user is the right to verify password
	 * @param array $data 
	 * @return object
	 */
	public function verifyResetPassword(array $data)
	{
		if (isset($data['email']) && isset($data['code']))
		{
			$userRepo = $this->getEntityManager()->getRepository($this->getModuleOptions()->getUserEntityClass());
			$user = $userRepo->findByEmail($data['email']);
			if ($user)
			{
				$code = $data['code'];
				$typeOf = 'reset-password';
				$userCodesRepo = $this->getEntityManager()->getRepository($this->getModuleOptions()->getEntityUserCode());
				$userCode = $userCodesRepo->findUserCode($user, $typeOf, $code);
				if ($userCode)
				{
					return $userCode;
				}
			}
		}
		return FALSE;
	}

	/**
	 * Change user password
	 * @param array $data 
	 */
	public function changePassword(array $data)
	{
		if ($this->getAuth()->hasIdentity())
		{
			$newPass = $data['newCredential'];
			$currentUser = $this->getAuth()->getIdentity();
			$bcrypt = new Bcrypt;
			$bcrypt->setCost($this->getZfcUserOptions()->getPasswordCost());
			$pass = $bcrypt->create($newPass);
			$currentUser->setPassword($pass);
			$this->getEventManager()->trigger(__FUNCTION__, $this, array('user' => $currentUser));
			$this->getUserRepo()->update($currentUser);
			if ($this->getModuleOptions('dxuser')->getSendEmailAfterPasswordUpdate())
			{
				$message = new Message();
				$message->addFrom($this->getModuleOptions()->getEmailNoReplySender(), $this->getModuleOptions()->getEmailNoReplySender())
						->addTo($currentUser->getEmail())
						->setSubject($this->getModuleOptions()->getEmailPasswordChangedSubject());
				$viewModel = new ViewModel(array(
							'newCredential' => $newPass,
							'user' => $currentUser,
							'hasIdentity' => TRUE
						));
				$viewModel->setTemplate($this->getModuleOptions()->getTemplateChangedPasswordEmail());
				$body = $this->renderer->render($viewModel);
				$message->setBody($body);
				$this->send($message);
			}
			$this->getEventManager()->trigger(__FUNCTION__ . '.post', $this, array('user' => $currentUser));
		}
		else
		{
			return $this->changeResetPassword($data);
		}
		return TRUE;
	}

	/**
	 * Check if the given password match that is in the db
	 * @param string $password The password to check
	 * @param object $user The user entity
	 * @return boolean
	 */
	public function checkPassword($password, $user = NULL)
	{
		if (NULL === $user)
		{
			$user = $this->getAuth()->getIdentity();
		}
		$bcrypt = new Bcrypt;
		$bcrypt->setCost($this->getZfcUserOptions()->getPasswordCost());

		if (!$bcrypt->verify($password, $user->getPassword()))
		{
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * Reset User Password
	 * @param array $data 
	 * 
	 * @return boolean
	 */
	public function changeResetPassword(array $data)
	{
		$code = $data['code'];
		$email = $data['email'];
		$newPassword = $data['newCredential'];
		$typeOf = 'reset-password';
		$userRepo = $this->getEntityManager()->getRepository($this->getModuleOptions()->getUserEntityClass());
		$user = $userRepo->findByEmail($email);
		$userCodesRepo = $this->getEntityManager()->getRepository($this->getModuleOptions()->getEntityUserCode());
		$userCode = $userCodesRepo->findUserCode($user, $typeOf, $code);
		if ($userCode)
		{
			$bcrypt = new Bcrypt;
			$bcrypt->setCost($this->getZfcUserOptions()->getPasswordCost());

			$pass = $bcrypt->create($newPassword);
			$user->setPassword($pass);

			$this->getEventManager()->trigger(__FUNCTION__, $this, array('user' => $user));
			$this->changedPasswordSendEmail($newPassword, $userCode);
			$this->em->persist($user);
			$this->em->remove($userCode);
			$userCodesRepo->removeCode($user, $typeOf);
			$this->em->flush();
			$this->getEventManager()->trigger(__FUNCTION__ . '.post', $this, array('user' => $user));
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Notify Account owner of a password changed through email
	 * @param UserCodes $userCode
	 */
	public function changedPasswordSendEmail($newCredential, $userCode)
	{
		$message = new Message();
		$message->addFrom($this->getModuleOptions()->getEmailNoReplySender(), $this->getModuleOptions()->getEmailNoReplySender())
				->addTo($userCode->getUser()->getEmail())
				->setSubject($this->getModuleOptions()->getEmailPasswordChangedSubject());
		$viewModel = new ViewModel(array(
					'userCode' => $userCode,
					'newCredential' => $newCredential
				));
		$viewModel->setTemplate($this->getModuleOptions()->getTemplateChangedPasswordEmail());
		$body = $this->renderer->render($viewModel);
		$message->setBody($body);
		$this->send($message);
	}

	/**
	 * REturn the Current User
	 * @return boolean|\DxUser\Entity\Users
	 */
	public function getCurrentUser()
	{
		if ($this->getAuth()->hasIdentity())
		{
			return $this->getUserById($this->getAuth()->getIdentity()->getId());
		}
		return FALSE;
	}

	/**
	 * REturn the User Profile
	 * @param object $user DxUser\Entity\User
	 * @return DxUser\Entity\UserProfile
	 */
	public function getProfile($user)
	{
		$userRepo = $this->getUserProfileRepo();
		return $userRepo->findByUser($user);
	}
	
	/**
	 * Get user by Id
	 * @param integer $userId
	 * @return boolean|\DxUser\Entity\Users
	 */
	public function getUserById($userId)
	{
		$userRepo = $this->getEntityManager()->getRepository($this->getModuleOptions('dxuser')->getUserEntityClass());
		return $userRepo->findById($userId);
	}

	/**
	 * Get a user by Email Address
	 * @param string $email
	 * @return boolean|\DxUser\Entity\Users
	 */
	public function getUserByEmail($email)
	{
		$userRepo = $this->getEntityManager()->getRepository($this->getModuleOptions('dxuser')->getUserEntityClass());
		return $userRepo->findByEmail($email);
	}

	/**
	 * Get a user by Username
	 * @param string $username
	 * @return boolean|\DxUser\Entity\Users
	 */
	public function getUserByUsername($username)
	{
		$userRepo = $this->getEntityManager()->getRepository($this->getModuleOptions('dxuser')->getUserEntityClass());
		return $userRepo->findByUsername($username);
	}

	/**
	 * getUserMapper
	 *
	 * @return UserMapperInterface
	 */
	public function getUserMapper()
	{
		return $this->getServiceManager()->get('zfcuser_user_mapper');
	}

	/**
	 * REturnt he User Repo
	 * @return type
	 */
	public function getUserRepo()
	{
		return $this->getEntityManager()->getRepository($this->getModuleOptions('dxuser')->getUserEntityClass());
	}

	/**
	 * REturnt he User Repo
	 * @return type
	 */
	public function getUserProfileRepo()
	{
		return $this->getEntityManager()->getRepository('DxUser\Entity\UserProfile');
	}
	
	/**
	 * Return the ZfcUserOptions
	 */
	public function getZfcUserOptions()
	{
		return $this->getModuleOptions('zfcuser_module_options');
	}
}