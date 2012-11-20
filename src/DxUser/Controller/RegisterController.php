<?php

namespace DxUser\Controller;

use ZfcUser\Controller\UserController as ZfcUserController;
use Zend\View\Model\ViewModel;
use Zend\Stdlib\Parameters;

class RegisterController extends ZfcUserController
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
			$viewData['enableRegistration'] = $this->getOptions()->getEnableRegistration();
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
						if ($this->getUserService()->getZfcUserOptions()->getLoginAfterRegistration())
						{
							$identityFields = $this->getUserService()->getZfcUserOptions()->getAuthIdentityFields();
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
							return $this->forward()->dispatch('zfcuser', array('action' => 'authenticate'));
						}
					}
					 return $this->redirect()->toUrl($this->url()->fromRoute('zfcuser/login') . ($redirect ? '?redirect='.$redirect : ''));
				}
			}
			$viewData['form'] = $form;
			$viewData['formDisplayOptions'] = $form->getDisplayOptions();
			return new ViewModel($viewData);
		}
		return $this->redirect()->toRoute($this->getModuleOptions()->getRouteMain());
	}

	public function getUserService()
	{
		return $this->getServiceLocator()->get('dxuser_service_user');
	}

	public function getRegisterForm()
	{
		return $this->getServiceLocator()->get('dxuser_form_register');
	}

	protected function getModuleOptions()
	{
		return $this->dxController()->getModuleOptions($this->modulePrefix);
	}

}