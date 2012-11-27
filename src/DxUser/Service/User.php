<?php

namespace DxUser\Service;

use Dxapp\Service\Dx as DxService;
use DxUser\Entity\UserCodesInterface;
use Zend\View\Model\ViewModel;
use Zend\Mail\Message;
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
		if (NULL === $user)
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
	public function changeEmail(array $data, $user = NULL)
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
				$userCodesRepo = $this->getUserCodesRepo();
				$typeOf = 'verify-email';
				$userCodesRepo->removeCode($user, $typeOf);
				$userCodesEntity = $this->getUserCodesEntity();
				$userCode = new $userCodesEntity;
				$userCode->addExtra('email', $email);
				$userCode->setUser($user);
				$userCode->setTypeOf($typeOf);
				$userCode->setCode(md5(time() . $userCode->getTypeOf() . $user->getEmail()));
				$this->getEntityManager()->persist($userCode);
				$this->getEntityManager()->flush();
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
		$userCodesRepo = $this->getUserCodesRepo();
		$typeOf = 'verify-email';
		$formerCode = $userCodesRepo->findUserCode($user, $typeOf, FALSE);
		$userCodesRepo->removeCode($user, $typeOf);
		$userCodesEntity = $this->getUserCodesEntity();
		$userCode = new $userCodesEntity;
		if ($formerCode)
		{
			$oldEmail = $formerCode->getExtra('email');
			$userCode->addExtra('email', $oldEmail);
		}
		$userCode->setUser($user);
		$userCode->setTypeOf($typeOf);
		$userCode->setCode(md5(time() . $userCode->getTypeOf() . $user->getEmail()));
		$this->getEntityManager()->persist($userCode);
		$this->getEntityManager()->flush();
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
			$userEntityClass = $this->getUserEntity();
			$userProfileEntityClass = $this->getUserProfileEntity();
			$user = new $userEntityClass;
			$userProfile = new $userProfileEntityClass;
			$userProfile->setFirstName('tesdfsdf');
			$user->setEmail($data['fsMain']['email']);
			$user->setPassword($data['fsMain']['newPassword']);
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
			$this->getEntityManager()->persist($user);
			$this->getEntityManager()->flush();
			$userProfile->setUser($user);
			$this->getEntityManager()->persist($userProfile);
			$this->getEntityManager()->flush();
			if ($this->getModuleOptions('dxuser')->getEnableEmailVerification())
			{
				$userCodesRepo = $this->getUserCodesRepo();
				$typeOf = 'verify-email';
				$userCodesRepo->removeCode($user, $typeOf);
				$userCodesEntity = $this->getUserCodesEntity();
				$userCode = new $userCodesEntity;
				$userCode->setUser($user);
				$userCode->setTypeOf($typeOf);
				$userCode->setCode(md5(time() . $userCode->getTypeOf() . $user->getEmail()));
				$this->getEntityManager()->persist($userCode);
				$this->sendVerifyEmail($userCode);
				$this->getEntityManager()->flush();
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
				->setSubject($this->getModuleOptions('dxuser')->getEmailVerifySubject());
		$viewModel = new ViewModel(array(
					'userCode' => $userCode,
				));
		$viewModel->setTemplate($this->getModuleOptions('dxuser')->getTemplateVerifyEmail());
		$body = $this->renderer->render($viewModel);
		$message->setBody($body);
		$this->send($message);
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
			$userCodesRepo = $this->getUserCodesRepo();
			$userCode = $userCodesRepo->findUserCode($user, $typeOf, $code);
			if ($userCode)
			{
				$this->getEventManager()->trigger(__FUNCTION__, $this, array('user' => $user, 'userCode' => $userCode));
				$user->verifyEmailAddress();
				$this->getEntityManager()->persist($user);
				$this->getEntityManager()->remove($userCode);
				$this->getEntityManager()->flush();
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
		$userRepo = $this->getUserRepo();
		$user = $userRepo->findByEmail($email);
		if ($user)
		{
			$userCodesRepo = $this->getUserCodesRepo();
			$typeOf = 'reset-password';
			$userCodesRepo->removeCode($user, $typeOf);
			$userCodesEntity = $this->getUserCodesEntity();
			$userCode = new $userCodesEntity;
			$userCode->setUser($user);
			$userCode->setTypeOf($typeOf);
			$userCode->setCode(md5(time() . $userCode->getTypeOf() . $user->getEmail()));
			$this->getEventManager()->trigger(__FUNCTION__, $this, array('user' => $user, 'userCode' => $userCode));
			$this->getEntityManager()->persist($userCode);
			$this->getEntityManager()->flush();
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
				->setSubject($this->getModuleOptions('dxuser')->getEmailResetPasswordSubject());
		$viewModel = new ViewModel(array(
					'userCode' => $userCode,
				));
		$viewModel->setTemplate($this->getModuleOptions('dxuser')->getTemplateResetPasswordEmail());
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
			$userRepo = $this->getUserRepo();
			$user = $userRepo->findByEmail($data['email']);
			if ($user)
			{
				$code = $data['code'];
				$typeOf = 'reset-password';
				$userCodesRepo = $this->getUserCodesRepo();
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
						->setSubject($this->getModuleOptions('dxuser')->getEmailPasswordChangedSubject());
				$viewModel = new ViewModel(array(
							'newCredential' => $newPass,
							'user' => $currentUser,
							'hasIdentity' => TRUE
						));
				$viewModel->setTemplate($this->getModuleOptions('dxuser')->getTemplateChangedPasswordEmail());
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
		$userRepo = $this->getUserRepo();
		$user = $userRepo->findByEmail($email);
		$userCodesRepo = $this->getUserCodesRepo();
		$userCode = $userCodesRepo->findUserCode($user, $typeOf, $code);
		if ($userCode)
		{
			$bcrypt = new Bcrypt;
			$bcrypt->setCost($this->getZfcUserOptions()->getPasswordCost());

			$pass = $bcrypt->create($newPassword);
			$user->setPassword($pass);

			$this->getEventManager()->trigger(__FUNCTION__, $this, array('user' => $user));
			$this->changedPasswordSendEmail($newPassword, $userCode);
			$this->getEntityManager()->persist($user);
			$this->getEntityManager()->remove($userCode);
			$userCodesRepo->removeCode($user, $typeOf);
			$this->getEntityManager()->flush();
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
				->setSubject($this->getModuleOptions('dxuser')->getEmailPasswordChangedSubject());
		$viewModel = new ViewModel(array(
					'userCode' => $userCode,
					'newCredential' => $newCredential
				));
		$viewModel->setTemplate($this->getModuleOptions('dxuser')->getTemplateChangedPasswordEmail());
		$body = $this->renderer->render($viewModel);
		$message->setBody($body);
		$this->send($message);
	}

	/**
	 * Check if the given user is an admin
	 * @param object $user DxUser\Entity\User
	 * @return boolean
	 */
	public function isAdmin($user = NULL)
	{
		if(NULL == $user)
		{
			$user = $this->getCurrentUser();
		}
		if($user)
		{
			$user = $this->getUserById($user);
			$role = $user->getRole();
			if($role == 'admin')
			{
				return TRUE;
			}
		}
		return FALSE;
	}
	
	/**
	 * Check if a user is loggedIn
	 * @param object $user DxUser\Entity\User
	 * @TODO check if a certain user is login
	 * @return boolean
	 */
	public function isLogin($user = NULL)
	{
		$user = '';
		return $this->getAuth()->hasIdentity();
	}
	
	
	/**
	 * REturn the Current User
	 * @return boolean|\DxUser\Entity\Users
	 */
	public function getCurrentUser()
	{
		if ($this->getAuth()->hasIdentity() && $this->getAuth() instanceof \Zend\Authentication\AuthenticationService)
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
		if(is_object($userId))
		{
			$userId = $userId->getId();
		}
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
	 * REturn the User Entity Name
	 * @return string
	 */
	public function getUserEntity()
	{
		return $this->getModuleOptions('dxuser')->getUserEntityClass();
	}

	/**
	 * REturnt he User Repo
	 * @return type
	 */
	public function getUserRepo()
	{
		return $this->getEntityManager()->getRepository($this->getUserEntity());
	}

	/**
	 * REturn the UserProfile Entity name
	 * @return string
	 */
	public function getUserProfileEntity()
	{
		return 'DxUser\Entity\UserProfile';
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
	 * REturn the User Codes Entity Name
	 * @return string
	 */
	public function getUserCodesEntity()
	{
		return $this->getModuleOptions('dxuser')->getEntityUserCode();
	}

	/**
	 * Return the UserCode Repo
	 * @return type
	 */
	public function getUserCodesRepo()
	{
		return $this->getEntityManager()->getRepository($this->getUserCodesEntity());
	}

	/**
	 * Return the ZfcUserOptions
	 */
	public function getZfcUserOptions()
	{
		return $this->getModuleOptions('zfcuser_module_options');
	}

}