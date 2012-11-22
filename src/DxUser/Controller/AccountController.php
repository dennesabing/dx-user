<?php

namespace DxUser\Controller;

use DxUser\Controller\ZfcUser;
use Zend\View\Model\ViewModel;

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

	public function passwordAction()
	{
		if ($this->zfcUserAuthentication()->hasIdentity())
		{
			$this->layout('layout/2column-leftbar');
			$viewData = array();
			$form = $this->getPasswordChangeForm();

			if ($this->getRequest()->isPost())
			{
				$form->setData($this->getRequest()->getPost());
				if ($form->isValid())
				{
					$data = $form->getData();
					$oldPassword = $data['fsMain']['oldPassword'];
					if($this->getUserService()->checkPassword($oldPassword))
					{
						$data['newCredential'] = $data['fsReset']['newPassword'];
						$changed = $this->getUserService()->changePassword($data);
						if($changed)
						{
							$viewData['success'] = TRUE;
						}
					}
					else
					{
						$viewData['oldPasswordError'] = TRUE;
					}
				}
			}
			$viewData['form'] = $form;
			$viewData['formDisplayOptions'] = $form->getDisplayOptions();
			return new ViewModel($viewData);
		}
		return $this->redirect()->toRoute($this->getModuleOptions()->getRouteLogin());
	}
	
	public function getPasswordChangeForm()
	{
		return $this->getServiceLocator()->get('dxuser_form_account_password');
	}

	public function emailAction()
	{
		if ($this->zfcUserAuthentication()->hasIdentity())
		{
			
		}
	}

	public function profileAction()
	{
		if ($this->zfcUserAuthentication()->hasIdentity())
		{
			
		}
	}
	
	

}