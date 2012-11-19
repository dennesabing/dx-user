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
			$form->remove('fsReset');
			if ($this->request->isPost())
			{
				$form->setData($this->request->getPost());
				if ($form->isValid())
				{
					$validData = $form->getData();
					$userCode = $this->getUserService()->resetPassword($validData['fsMain']['email']);
					if ($userCode)
					{
						$viewData['userCode'] = $userCode;
						$viewData['success'] = TRUE;
					}
					else
					{
						$viewData['error'] = TRUE;
					}
				}
			}
			$viewData['form'] = $form;
			$viewData['validData'] = $validData;
			$viewData['formDisplayOptions'] = $form->getDisplayOptions();
			return new ViewModel($viewData);
		}
		return $this->redirect()->toRoute($this->getModuleOptions()->getRouteMain());
	}

	public function verifyAction()
	{
		$this->layout('layout/2column-rightbar');
		$email = urldecode($this->getEvent()->getRouteMatch()->getParam('email'));
		$code = $this->getEvent()->getRouteMatch()->getParam('code');
		if (!empty($email) && !empty($code) && strlen($code) == 32)
		{
			$viewData = array();
			$userCode = $this->getUserService()->verifyResetPassword(array('email' => $email, 'code' => $code));
			if ($userCode)
			{
				$form = $this->getPasswordResetForm();
				if ($this->request->isPost())
				{
					$form->setData($this->request->getPost());
					if ($form->isValid())
					{
						$validData = $form->getData();
						$userCode = $this->getUserService()->resetPassword($validData['fsMain']['email']);
						if ($userCode)
						{
							$viewData['userCode'] = $userCode;
							$viewData['success'] = TRUE;
						}
						else
						{
							$viewData['error'] = TRUE;
						}
					}
				}
				$form->remove('fsMain');

				$viewData['form'] = $form;
				$viewData['validData'] = $validData;
				$viewData['formDisplayOptions'] = $form->getDisplayOptions();
				$viewData['resetPasswordValid'] = TRUE;
			}
			return new ViewModel($viewData);
		}
		return $this->dxController()->notFound();
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