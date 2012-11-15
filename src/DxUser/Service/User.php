<?php

namespace DxUser\Service;

use Zend\Authentication\AuthenticationService;
use Zend\Form\Form;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\Hydrator\ClassMethods;
use Dxapp\EventManager\EventProvider;

class User extends EventProvider implements ServiceManagerAwareInterface
{

	/**
	 * The Doctrine Entity Manager
	 * @var type 
	 */
	protected $em = NULL;

	/**
	 * the Module Options
	 * @var type 
	 */
	protected $moduleOptions = NULL;

	/**
	 * Two Stage SignUp
	 *
	 * @param array $data The User Object
	 * @return \DxUser\Entity\UserCodes
	 * @throws Exception\InvalidArgumentException
	 */
	public function register($user)
	{
		$userCode = new \DxUser\Entity\UserCodes();
		$userCode->setUser($user);
		$userCode->setTypeOf('verify-email');
		$userCode->setCode(md5($userCode->getTypeOf() . $user->getEmail()));
		$this->getEventManager()->trigger(__FUNCTION__, $this, array('user' => $user, 'userCode' => $userCode));
		$this->em->persist($userCode);
		$this->getEventManager()->trigger(__FUNCTION__ . '.post', $this, array('user' => $user, 'userCode' => $userCode));
		return $userCode;
	}

	/**
	 * Verify an email address
	 * @param array $data 
	 * @throws Exception\InvalidArgumentException
	 */
	public function verifyEmail(array $data)
	{
		if (isset($data['user']) && isset($data['code']))
		{
			$user = $data['user'];
			$code = $data['code'];
			$typeOf = 'verify-email';
			$userCodesRepo = $this->getEntityManager()->getRepository('DxUser\Entity\UserCode');
			$userCode = $userCodesRepo->findUserCode($user, $typeOf, $code);
			if ($userCode)
			{
				$this->getEventManager()->trigger(__FUNCTION__, $this, array('user' => $user, 'userCode' => $userCode));
				$user->verifyEmailAddress();
				$this->em->remove($userCode);
				$this->em->persist($user);
				$this->em->flush();
				$this->getEventManager()->trigger(__FUNCTION__ . '.post', $this, array('user' => $user, 'userCode' => $userCode));
				return TRUE;
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
	 * Set the Module Options
	 * @param array $options
	 * @return \DxUser\Service\User 
	 */
	public function setModuleOptions($options)
	{
		$this->moduleOptions = $options;
		return $this;
	}

	/**
	 * Get the Module Options
	 * @return \DxUser\Service\User 
	 */
	public function getModuleOptions()
	{
		return $this->moduleOptions;
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

}