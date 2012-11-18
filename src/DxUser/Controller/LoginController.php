<?php

namespace DxUser\Controller;

use ZfcUser\Controller\UserController as ZfcUserController;

class LoginController extends ZfcUserController
{
	
	protected $modulePrefix = 'dxuser';
	
	public function indexAction()
	{
		if (!$this->zfcUserAuthentication()->hasIdentity())
		{
			return $this->redirect()->toRoute($this->getModuleOptions()->getRouteLogin());
		}
		return $this->redirect()->toRoute($this->getModuleOptions()->getRouteMain());
	}

	public function loginAction()
	{
		$this->layout('layout/2column-rightbar');
		if (!$this->zfcUserAuthentication()->hasIdentity())
		{
			return parent::loginAction();
		}
		return $this->redirect()->toRoute($this->getModuleOptions()->getRouteMain());
	}
	
	protected function getModuleOptions()
	{
		return $this->dxController()->getModuleOptions($this->modulePrefix);
	}
}