<?php

namespace DxUser\Controller;

use Zend\View\Model\ViewModel;
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

		$redirect = FALSE;
		if ($this->getOptions()->getUseRedirectParameterIfPresent() && $this->getRequest()->getQuery()->get('redirect'))
		{
			$redirect = $this->getRequest()->getQuery()->get('redirect');
		}

		if (!$this->zfcUserAuthentication()->hasIdentity())
		{
			$this->layout('layout/2column-rightbar');
			$viewData = array();
			$validData = NULL;
			$form = $this->getLoginForm();

			if ($this->getRequest()->isPost())
			{
				$form->setData($this->getRequest()->getPost());
				if ($form->isValid())
				{
					return $this->forward()->dispatch('zfcuser', array('action' => 'authenticate'));
				}
			}
			$viewData['redirect'] = $redirect;
			$viewData['form'] = $form;
			$viewData['validData'] = $validData;
			$viewData['formDisplayOptions'] = $form->getDisplayOptions();
			$viewData['enableRegistration'] = $this->getOptions()->getEnableRegistration();
			return new ViewModel($viewData);
		}
		return $this->redirect()->toRoute($this->getModuleOptions()->getRouteMain());
	}

	public function getLoginForm()
	{
		return $this->getServiceLocator()->get('dxuser_form_login');
	}

	protected function getModuleOptions()
	{
		return $this->dxController()->getModuleOptions($this->modulePrefix);
	}

}