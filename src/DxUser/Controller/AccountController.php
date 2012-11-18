<?php

namespace DxUser\Controller;

use ZfcUser\Controller\UserController as ZfcUserController;

class AccountController extends ZfcUserController
{

	protected $modulePrefix = 'dxuser';
	
	public function indexAction()
	{
		if ($this->zfcUserAuthentication()->hasIdentity())
		{
			return $this->redirect()->toRoute($this->getModuleOptions()->getRouteUserAccount());
		}
		return $this->redirect()->toRoute($this->getModuleOptions()->getRouteLogin());
	}

	public function accountAction()
	{
		if ($this->zfcUserAuthentication()->hasIdentity())
		{
			
		}
	}
	
	protected function getModuleOptions()
	{
		return $this->dxController()->getModuleOptions($this->modulePrefix);
	}

}