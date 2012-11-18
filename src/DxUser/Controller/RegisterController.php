<?php

namespace DxUser\Controller;

use ZfcUser\Controller\UserController as ZfcUserController;

class RegisterController extends ZfcUserController
{
	
	protected $modulePrefix = 'dxuser';
	
	public function indexAction()
	{
		if (!$this->zfcUserAuthentication()->hasIdentity())
		{
			return $this->redirect()->toRoute($this->getModuleOptions()->getRouteRegistration());
		}
		return $this->redirect()->toRoute($this->getModuleOptions()->getRouteMain());
	}

	public function registerAction()
	{
		if (!$this->zfcUserAuthentication()->hasIdentity())
		{
			return parent::registerAction();
		}
		return $this->redirect()->toRoute($this->getModuleOptions()->getRouteMain());
	}
	
	protected function getModuleOptions()
	{
		return $this->dxController()->getModuleOptions($this->modulePrefix);
	}
}