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
		$this->dropDb = FALSE;
		$this->entities = array(
			'DxCdRace\Entity\User',
			'DxUser\Entity\UserCodes'
		);
		parent::setup();
		$this->repoUser = $this->em->getRepository('DxCdRace\Entity\User');
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
		$userService = $this->getServiceManager()->get('dxuser_service_user');
		$user = $this->getUserJuan();
		$this->em->persist($user);
		$this->em->flush();
		$userCode = $userService->register($user);
		if ($userService->getOptions()->getEnableEmailVerification())
		{
			$this->assertTrue($userService->getOptions()->getEnableEmailVerification());
			$userCodeRow = $this->repoUserCodes->findUserCode($user, $userCode->getTypeOf(), $userCode->getCode());
			$this->assertEquals($userCode->getCode(), $userCodeRow->getCode());
		}
		else
		{
			$this->assertFalse($userService->getOptions()->getEnableEmailVerification());
		}
	}

	public function testResetPassword()
	{
		$userService = $this->getServiceManager()->get('dxuser_service_user');
		$user = $this->getUserJuan();
		$this->em->persist($user);
		$this->em->flush();
		$userCode = $userService->resetPassword($user->getEmail());
		$userCodeRow = $this->repoUserCodes->findUserCode($user, $userCode->getTypeOf(), $userCode->getCode());
		$this->assertEquals($userCode->getCode(), $userCodeRow->getCode());
		$this->assertEquals($userCode->getTypeOf(), 'reset-password');
	}

	public function testChangeResetPassword()
	{
		$userService = $this->getServiceManager()->get('dxuser_service_user');
		$user = $this->getUserJuan();
		$this->em->persist($user);
		$this->em->flush();
		$userCode = $userService->resetPassword($user->getEmail());
		$newPassword = time();
		$data = array(
			'email' => $user->getEmail(),
			'code' => $userCode->getCode(),
			'newCredential' => $newPassword
		);
		$bcrypt = new Bcrypt;
		$bcrypt->setCost($userService->getZfcUserOptions()->getPasswordCost());
		$userService->changeResetPassword($data);
		$userRepo = $this->em->getRepository($userService->getOptions()->getUserEntityClass());
		$user = $userRepo->findByEmail($user->getEmail());
		$this->assertTrue($bcrypt->verify($newPassword, $user->getPassword()));
	}

}