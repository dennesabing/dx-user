<?php

namespace DxUser\Service;

use Zend\Authentication\AuthenticationService;
use Zend\Form\Form;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\Hydrator\ClassMethods;
use Dxapp\EventManager\EventProvider;
use DxUser\Options\ModuleOptions;
use Dxapp\Service\MailTransport as SendmailTransport;
use Zend\View\Model\ViewModel;
use Zend\Mail\Message;
use Zend\View\Renderer\PhpRenderer;
use DxUser\Entity\UserCodesInterface;
use Zend\Crypt\Password\Bcrypt;

class User extends EventProvider implements ServiceManagerAwareInterface
{

	/**
	 * The View Renderer
	 * @var object
	 */
	protected $renderer = NULL;

	/**
	 * The Doctrine Entity Manager
	 * @var type 
	 */
	protected $em = NULL;

	/**
	 * the Module Options
	 * @var type 
	 */
	protected $options = NULL;

	/**
	 * The Auth service
	 * @var type 
	 */
	protected $authService = NULL;

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
			$userClass = $this->getOptions()->getUserEntityClass();
			$user = new $userClass;
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
			$this->getUserMapper()->insert($user);
			if ($this->getOptions()->getEnableEmailVerification())
			{
				$userCodesRepo = $this->getEntityManager()->getRepository($this->getOptions()->getEntityUserCode());
				$typeOf = 'verify-email';
				$userCodesRepo->removeCode($user, $typeOf);
				$userCodeClass = $this->getOptions()->getEntityUserCode();
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
		$message->addFrom($this->getOptions()->getEmailNoReplySender(), $this->getOptions()->getEmailNoReplySender())
				->addTo($userCode->getUser()->getEmail())
				->setSubject($this->getOptions()->getEmailVerifySubject());
		$viewModel = new ViewModel(array(
					'userCode' => $userCode,
				));
		$viewModel->setTemplate($this->getOptions()->getTemplateVerifyEmail());
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
			$user = $data['user'];
			$code = $data['code'];
			$typeOf = 'verify-email';
			if (!$user->isEmailVerified())
			{
				$userCodesRepo = $this->getEntityManager()->getRepository($this->getOptions()->getEntityUserCode());
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
			else
			{
				return 'already-verified';
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
		$userRepo = $this->getEntityManager()->getRepository($this->getOptions()->getUserEntityClass());
		$user = $userRepo->findByEmail($email);
		if ($user)
		{
			$userCodesRepo = $this->getEntityManager()->getRepository($this->getOptions()->getEntityUserCode());
			$typeOf = 'reset-password';
			$userCodesRepo->removeCode($user, $typeOf);
			$userCodeEntity = $this->getOptions()->getEntityUserCode();
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
		$message->addFrom($this->getOptions()->getEmailNoReplySender(), $this->getOptions()->getEmailNoReplySender())
				->addTo($userCode->getUser()->getEmail())
				->setSubject($this->getOptions()->getEmailResetPasswordSubject());
		$viewModel = new ViewModel(array(
					'userCode' => $userCode,
				));
		$viewModel->setTemplate($this->getOptions()->getTemplateResetPasswordEmail());
		$body = $this->renderer->render($viewModel);
		$message->setBody($body);
		$transport = new SendmailTransport($this->getServiceManager());
		$transport->send($message);
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
			$userRepo = $this->getEntityManager()->getRepository($this->getOptions()->getUserEntityClass());
			$user = $userRepo->findByEmail($data['email']);
			if ($user)
			{
				$code = $data['code'];
				$typeOf = 'reset-password';
				$userCodesRepo = $this->getEntityManager()->getRepository($this->getOptions()->getEntityUserCode());
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
		if ($this->getAuthService()->hasIdentity())
		{
			$newPass = $data['newCredential'];
			$oldPass = $data['credential'];
			$currentUser = $this->getAuthService()->getIdentity();
			$bcrypt = new Bcrypt;
			$bcrypt->setCost($this->getZfcUserOptions()->getPasswordCost());

			if (!$bcrypt->verify($oldPass, $currentUser->getPassword()))
			{
				return FALSE;
			}

			$pass = $bcrypt->create($newPass);
			$currentUser->setPassword($pass);
			$this->getEventManager()->trigger(__FUNCTION__, $this, array('user' => $currentUser));
			$this->getUserMapper()->update($currentUser);
			$this->getEventManager()->trigger(__FUNCTION__ . '.post', $this, array('user' => $currentUser));
		}
		else
		{
			return $this->changeResetPassword($data);
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
		$userRepo = $this->getEntityManager()->getRepository($this->getOptions()->getUserEntityClass());
		$user = $userRepo->findByEmail($email);
		$userCodesRepo = $this->getEntityManager()->getRepository($this->getOptions()->getEntityUserCode());
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
		$message->addFrom($this->getOptions()->getEmailNoReplySender(), $this->getOptions()->getEmailNoReplySender())
				->addTo($userCode->getUser()->getEmail())
				->setSubject($this->getOptions()->getEmailPasswordChangedSubject());
		$viewModel = new ViewModel(array(
					'userCode' => $userCode,
					'newCredential' => $newCredential
				));
		$viewModel->setTemplate($this->getOptions()->getTemplateChangedPasswordEmail());
		$body = $this->renderer->render($viewModel);
		$message->setBody($body);
		$transport = new SendmailTransport($this->getServiceManager());
		$transport->send($message);
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
	 * getAuthService
	 *
	 * @return AuthenticationService
	 */
	public function getAuthService()
	{
		if (null === $this->authService)
		{
			$this->setAuthService($this->getServiceManager()->get('zfcuser_auth_service'));
		}
		return $this->authService;
	}

	/**
	 * setAuthenticationService
	 *
	 * @param AuthenticationService $authService
	 * @return User
	 */
	public function setAuthService(AuthenticationService $authService)
	{
		$this->authService = $authService;
		return $this;
	}

	/**
	 * Set Doctrine Entity Manager
	 *
	 * @return User
	 */
	public function setEntityManager($em)
	{
		$this->em = $em;
		return $this;
	}

	/**
	 * Return the Entity Manager
	 * @return type 
	 */
	public function getEntityManager()
	{
		return $this->em;
	}

	/**
	 * Get the Module OPtions from SM
	 *
	 * @return UserServiceOptionsInterface
	 */
	public function getOptions()
	{
		if (!$this->options instanceof ModuleOptions)
		{
			$this->setOptions($this->getServiceManager()->get('dxuser_module_options'));
		}
		return $this->options;
	}

	/**
	 * set service options
	 *
	 * @param UserServiceOptionsInterface $options
	 */
	public function setOptions(ModuleOptions $options)
	{
		$this->options = $options;
	}

	/**
	 * gEt the ZfcUser Module Options
	 * @return type 
	 */
	public function getZfcUserOptions()
	{
		return $this->getServiceManager()->get('zfcuser_module_options');
	}

	/**
	 * Retrieve service manager instance
	 *
	 * @return ServiceManager
	 */
	public function getServiceManager()
	{
		return $this->serviceManager;
	}

	/**
	 * Set service manager instance
	 *
	 * @param ServiceManager $locator
	 * @return User
	 */
	public function setServiceManager(ServiceManager $serviceManager)
	{
		$this->serviceManager = $serviceManager;
		return $this;
	}

	/**
	 * Set the ViewRenderer Object
	 * @param type $viewRenderer
	 * @return \DxUser\Service\User 
	 */
	public function setViewRenderer($viewRenderer)
	{
		$this->renderer = $viewRenderer;
		return $this;
	}

}