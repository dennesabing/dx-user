<?php

namespace DxUser\Controller;

use DxUser\Controller\ZfcUser;

class AccountController extends ZfcUser
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
		$this->layout('layout/2column-leftbar');
		if ($this->zfcUserAuthentication()->hasIdentity())
		{
			
		}
	}
}