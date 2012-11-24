<?php

namespace DxUser\Controller;

use Zend\View\Model\ViewModel;
use Zend\Stdlib\Parameters;
use DxUser\Controller\ZfcUser;

class LoginController extends ZfcUser
{
	public function indexAction()
	{
		if (!$this->zfcUserAuthentication()->hasIdentity())
		{
			return $this->redirect()->toRoute($this->dxController()->getModuleOptions('dxuser')->getRouteLogin());
		}
		return $this->redirect()->toRoute($this->dxController()->getModuleOptions()->getRouteMain());
	}

	public function loginAction()
	{
		$this->layout('layout/2column-rightbar');

		$redirect = FALSE;
		if ($this->dxController()->getModuleOptions('zfcuser_module_options')->getUseRedirectParameterIfPresent() 
				&& $this->getRequest()->getQuery()->get('redirect'))
		{
			$redirect = $this->getRequest()->getQuery()->get('redirect');
		}

		if (!$this->dxController()->getAuth()->hasIdentity())
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
					$identityFields = $this->dxController()->getModuleOptions('zfcuser_module_options')->getAuthIdentityFields();
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
						if ($this->dxController()->getModuleOptions('zfcuser_module_options')->getUseRedirectParameterIfPresent() && $redirect)
						{
							return $this->redirect()->toUrl($redirect);
						}
						return $this->redirect()->toRoute($this->dxController()->getModuleOptions()->getRouteMain());
					}
				}
			}
			$viewData['redirect'] = $redirect;
			$viewData['form'] = $form;
			$viewData['validData'] = $validData;
			$viewData['formDisplayOptions'] = $form->getDisplayOptions();
			$viewData['enableRegistration'] = $this->dxController()->getModuleOptions('zfcuser_module_options')->getEnableRegistration();
			$viewData['scnSocialAuthOptions'] = $this->dxController()->getModuleOptions('ScnSocialAuth-ModuleOptions');
			return new ViewModel($viewData);
		}
		return $this->redirect()->toRoute($this->dxController()->getModuleOptions()->getRouteMain());
	}

	public function getLoginForm()
	{
		return $this->getServiceLocator()->get('dxuser_form_login');
	}
}