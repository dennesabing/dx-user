<?php

namespace DxUser\Controller;

use DxUser\Controller\ZfcUser;
use Zend\View\Model\ViewModel;
use Zend\Stdlib\Parameters;

class RegisterController extends ZfcUser
{
	public function indexAction()
	{
		if (!$this->dxController()->getAuth()->hasIdentity())
		{
			return $this->redirect()->toRoute($this->dxController()->getModuleOptions('dxuser')->getRouteRegistration());
		}
		return $this->redirect()->toRoute($this->dxController()->getModuleOptions()->getRouteMain());
	}

	public function registerAction()
	{
		$this->layout('layout/2column-rightbar');
		if (!$this->dxController()->getAuth()->hasIdentity())
		{
			$viewData = array();
			$viewData['redirect'] = $this->getRedirectUrl();
			$viewData['enableRegistration'] = $this->dxController()->getModuleOptions('zfcuser_module_options')->getEnableRegistration();
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
						if ($this->dxController()->getModuleOptions('dxuser')->getEnableEmailVerification())
						{
							$viewData['userCode'] = $user;
							$user = $user->getUser();
						}
						if ($this->dxController()->getModuleOptions('zfcuser_module_options')->getLoginAfterRegistration())
						{
							$identityFields = $this->dxController()->getModuleOptions('zfcuser_module_options')->getAuthIdentityFields();
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
		return $this->redirect()->toRoute($this->dxController()->getModuleOptions()->getRouteMain());
	}

	/**
	 * Verify a given email address
	 * @return type
	 */
	public function verifyAction()
	{
		$this->layout('layout/2column-leftbar');
		if ($this->dxController()->getModuleOptions('dxuser')->getEnableEmailVerification())
		{
			$viewData = array();
			$viewData['enableRegistration'] = $this->dxController()->getModuleOptions('zfcuser_module_options')->getEnableRegistration();
			$email = urldecode($this->getEvent()->getRouteMatch()->getParam('email'));
			$code = $this->getEvent()->getRouteMatch()->getParam('code');
			$user = $this->getUserService()->getUserByEmail($email);
			if ($user)
			{
				if ($this->dxController()->getAuth()->hasIdentity())
				{
					$viewData['hasIdentity'] = TRUE;
					$user = $this->getUserService()->getUserById($this->dxController()->getAuth()->getIdentity()->getId());
					if (!$email == $user->getEmail())
					{
						return $this->redirect()->toRoute($this->dxController()->getModuleOptions()->getRouteMain());
					}
				}
				if ($user->isEmailVerified())
				{
					$viewData['alreadyVerified'] = TRUE;
				}
				$data = array(
					'code' => $code,
					'email' => $email,
					'user' => $user
				);
				$viewData['success'] = $this->getUserService()->verifyEmail($data);
				return new ViewModel($viewData);
			}
		}
		return $this->redirect()->toRoute($this->dxController()->getModuleOptions()->getRouteMain());
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