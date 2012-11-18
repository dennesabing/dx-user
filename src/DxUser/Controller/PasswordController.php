<?php

namespace DxUser\Controller;

use Zend\View\Model\ViewModel;
use ZfcUser\Controller\UserController as ZfcUserController;

class PasswordController extends ZfcUserController
{

	protected $modulePrefix = 'dxuser';

	public function indexAction()
	{
		if (!$this->zfcUserAuthentication()->hasIdentity())
		{
			return $this->redirect()->toRoute($this->getModuleOptions()->getRoutePasswordReset());
		}
		return $this->redirect()->toRoute($this->getModuleOptions()->getRouteMain());
	}

	public function passwordAction()
	{
		$this->layout('layout/2column-rightbar');
		if (!$this->zfcUserAuthentication()->hasIdentity())
		{
			$viewData = array();
			$validData = NULL;
			$form = $this->getPasswordResetForm();
			if ($this->request->isPost())
			{
				$form->setData($this->request->getPost());
				if ($form->isValid())
				{
					$validData = $form->getData();
					$userService = $this->getUserService()->resetPassword($validData['fsMain']['email']);
					if($userService)
					{
						$viewData['success'] = TRUE;
					}
					else
					{
						$viewData['error'] = TRUE;
					}
				}
			}
			$viewData['form'] = $form;
			$viewData['formType'] = \DluTwBootstrap\Form\FormUtil::FORM_TYPE_VERTICAL;
			$viewData['validData'] = $validData;
			$viewData['formDisplayOptions'] = $form->getDisplayOptions();
			return new ViewModel($viewData);
		}
		return $this->redirect()->toRoute($this->getModuleOptions()->getRouteMain());
	}

	public function getUserService()
	{
		return $this->getServiceLocator()->get('dxuser_service_user');
	}
	
	public function getPasswordResetForm()
	{
		return $this->getServiceLocator()->get('dxuser_form_password_reset');
	}

	protected function getModuleOptions()
	{
		return $this->dxController()->getModuleOptions($this->modulePrefix);
	}
}