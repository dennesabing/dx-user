<?php

namespace DxUserBaseTest\Service;

use Dx\PHPUnit\BaseTestCase;
use DxUser\Entity\User;
use DxUser\Entity\UserCodes;
use DxUser\Service\User as UserService;
use Zend\Crypt\Password\Bcrypt;

class UserTest extends BaseTestCase
{
	public function setup()
	{
		$this->entities = array(
			'DxUser\Entity\User',
			'DxUser\Entity\UserCodes'
		);
		parent::setup();
		$this->repoUser = $this->em->getRepository('DxUser\Entity\User');
		$this->repoUserCodes = $this->em->getRepository('DxUser\Entity\UserCodes');
	}
	
	public function hashPassword($password)
	{
		$zfUserOption = $this->getServiceManager()->get('zfcuser_module_options');
		$bcrypt = new Bcrypt;
		$bcrypt->setCost($zfUserOption->getPasswordCost());
		$pass = $bcrypt->create($password);
		return $pass;
	}
	
	public function getUserJuan()
	{
		$u = new User();
		$u->setEmail('juan@amigoas.com');
		$u->setUsername($u->getEmail());
		$u->setDisplayName('Juan Tamad');
		$u->setPassword($this->hashPassword('abc123'));
		return $u;
	}

	public function getUserPedro()
	{
		$u = new User();
		$u->setEmail('pedro@amigoas.com');
		$u->setUsername($u->getEmail());
		$u->setDisplayName('Pedro Penduko');
		$u->setPassword($this->hashPassword('abc123'));
		return $u;
	}
	
	public function testUserEmailVerify()
	{
		$userService = new UserService();
		$userService->setEntityManager($this->em);
		$user = $this->getUserJuan();
		$this->em->persist($user);
		
		$userCode = $userService->register($user);
	}

}