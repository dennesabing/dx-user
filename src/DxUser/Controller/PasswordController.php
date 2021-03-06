<?php

namespace DxUser\Controller;

use Zend\View\Model\ViewModel;
use DxUser\Controller\ZfcUser;

class PasswordController extends ZfcUser
{
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
		$validData = array();
		if (!empty($email) && !empty($code) && strlen($code) == 32)
		{
			$viewData = array();
			$userCode = $this->getUserService()->verifyResetPassword(array('email' => $email, 'code' => $code));
			if ($userCode)
			{
				$form = $this->getPasswordResetChangeForm();
				if ($this->request->isPost())
				{
					$form->setData($this->getRequest()->getPost());
					if ($form->isValid())
					{
						$validData = $form->getData();
						$data = array(
							'newCredential' => $validData['fsMain']['newPassword'],
							'email' => $email,
							'code' => $code
						);
						$userCode = $this->getUserService()->changePassword($data);
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
					else
					{
						$viewData['error'] = TRUE;
					}
				}
				$viewData['form'] = $form;
				$viewData['validData'] = $validData;
				$viewData['formDisplayOptions'] = $form->getDisplayOptions();
				$viewData['resetPasswordValid'] = TRUE;
			}
			return new ViewModel($viewData);
		}
		return $this->dxController()->notFound();
	}

	public function getPasswordResetForm()
	{
		return $this->getServiceLocator()->get('dxuser_form_password_reset');
	}

	public function getPasswordResetChangeForm()
	{
		return $this->getServiceLocator()->get('dxuser_form_password_reset_change');
	}
}