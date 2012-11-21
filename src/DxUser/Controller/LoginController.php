<?php

namespace DxUser\Controller;

use Zend\View\Model\ViewModel;
use Zend\Stdlib\Parameters;
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
					$data = $form->getData();
					$identityFields = $this->getUserService()->getZfcUserOptions()->getAuthIdentityFields();
					if (in_array('email', $identityFields))
					{
						$post['identity'] = $data['fsMain']['email'];
					}
					elseif (in_array('username', $identityFields))
					{
						$post['identity'] = $data['fsMain']['username'];
					}
					$post['credential'] = $data['fsMain']['password'];
					$this->getRequest()->setPost(new Parameters($post));
					$auth = $this->authenticate($this->getRequest());
					if (!$auth)
					{
						$viewData['error'] = TRUE;
					}
					else
					{
						if ($this->getUserService()->getZfcUserOptions()->getUseRedirectParameterIfPresent() && $redirect)
						{
							return $this->redirect()->toUrl($redirect);
						}
						return $this->redirect()->toRoute($this->getModuleOptions()->getRouteMain());
					}
				}
			}
			$viewData['redirect'] = $redirect;
			$viewData['form'] = $form;
			$viewData['validData'] = $validData;
			$viewData['formDisplayOptions'] = $form->getDisplayOptions();
			$viewData['enableRegistration'] = $this->getOptions()->getEnableRegistration();
			$viewData['scnSocialAuthOptions'] = $this->dxController()->getModuleOptions('ScnSocialAuth-ModuleOptions');
			return new ViewModel($viewData);
		}
		return $this->redirect()->toRoute($this->getModuleOptions()->getRouteMain());
	}

	protected function authenticate($request)
	{
		$adapter = $this->zfcUserAuthentication()->getAuthAdapter();
		$result = $adapter->prepareForAuthentication($request);
		if ($result instanceof Response)
		{
			return FALSE;
		}
		$auth = $this->zfcUserAuthentication()->getAuthService()->authenticate($adapter);
		if (!$auth->isValid())
		{
			$adapter->resetAdapters();
			return FALSE;
		}
		return TRUE;
	}

	public function getUserService()
	{
		return $this->getServiceLocator()->get('dxuser_service_user');
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