<?php

namespace DxUser\Controller;

use DxUser\Controller\ZfcUser;
use Zend\View\Model\ViewModel;
use Zend\Stdlib\Parameters;

class RegisterController extends ZfcUser
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
		$this->layout('layout/2column-rightbar');
		if (!$this->zfcUserAuthentication()->hasIdentity())
		{
			$viewData = array();
			$viewData['redirect'] = $this->getRedirectUrl();
			$viewData['enableRegistration'] = $this->getZfcUserOptions()->getEnableRegistration();
			$form = $this->getRegisterForm();
			if ($this->request->isPost())
			{
				$form->setData($this->request->getPost());
				if ($form->isValid())
				{
					$data = $form->getData();
					$user = $this->getUserService()->register($data);
					if ($user)
					{
						if($this->dxController()->getModuleOptions('dxuser')->getEnableEmailVerification())
						{
							$viewData['userCode'] = $user;
							$user = $user->getUser();
						}
						if ($this->getZfcUserOptions()->getLoginAfterRegistration())
						{
							$identityFields = $this->getZfcUserOptions()->getAuthIdentityFields();
							if (in_array('email', $identityFields))
							{
								$post['identity'] = $user->getEmail();
							}
							elseif (in_array('username', $identityFields))
							{
								$post['identity'] = $user->getUsername();
							}
							$post['credential'] = $data['fsMain']['newPassword'];
							$this->getRequest()->setPost(new Parameters($post));
							$auth = $this->authenticate($this->getRequest());
							if ($auth)
							{
								$viewData['loginSuccess'] = TRUE;
							}
						}
						$viewData['registrationSuccess'] = TRUE;
					}
				}
			}
			$viewData['form'] = $form;
			$viewData['formDisplayOptions'] = $form->getDisplayOptions();
			return new ViewModel($viewData);
		}
		return $this->redirect()->toRoute($this->getModuleOptions()->getRouteMain());
	}
	
	/**
	 * Return the Registration Form
	 * @return type
	 */
	public function getRegisterForm()
	{
		return $this->getServiceLocator()->get('dxuser_form_register');
	}

}