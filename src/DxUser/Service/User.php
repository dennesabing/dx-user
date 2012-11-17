<?php

namespace DxUser\Service;

use Zend\Authentication\AuthenticationService;
use Zend\Form\Form;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\Hydrator\ClassMethods;
use Dxapp\EventManager\EventProvider;
use DxUser\Options\ModuleOptions;
use Zend\Mail\Message;
use Zend\Mail\Transport\Sendmail as SendmailTransport;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;
use DxUser\Entity\UserCodesInterface;

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
	 * Two Stage SignUp
	 *
	 * @param array $data The User Object
	 * @return \DxUser\Entity\UserCodes|NULL
	 * @throws Exception\InvalidArgumentException
	 */
	public function register($user)
	{
		if ($this->getOptions()->getEnableEmailVerification())
		{
			$userCode = new \DxUser\Entity\UserCodes();
			$userCode->setUser($user);
			$userCode->setTypeOf('verify-email');
			$userCode->setCode(md5(time() . $userCode->getTypeOf() . $user->getEmail()));
			$this->getEventManager()->trigger(__FUNCTION__, $this, array('user' => $user, 'userCode' => $userCode));
			$this->em->persist($userCode);
			$this->em->flush();
			$this->sendVerifyEmail($userCode);
			$this->getEventManager()->trigger(__FUNCTION__ . '.post', $this, array('user' => $user, 'userCode' => $userCode));
			return $userCode;
		}
		else
		{
			return NULL;
		}
	}

	/**
	 * Send email
	 * @param object $userCode 
	 */
	public function sendVerifyEmail(UserCodesInterface $userCode)
	{
		$message = new Message();
		$message->addFrom($this->getOptions()->getEmailVerifySender(), $this->getOptions()->getEmailVerifySender())
				->addTo($userCode->getUser()->getEmail())
				->setSubject($this->getOptions()->getEmailVerifySubject());
		$viewModel = new ViewModel(array(
					'userCode' => $userCode,
				));
		$viewModel->setTemplate($this->getOptions()->getTemplateVerifyEmail());
		$body = $this->renderer->render($viewModel);
		$message->setBody($body);
		if ($this->getOptions()->getEmailSending())
		{
			$transport = new SendmailTransport();
			$transport->send($message);
		}
	}

	/**
	 * Check user email verification
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
				$userCodesRepo = $this->getEntityManager()->getRepository('DxUser\Entity\UserCode');
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