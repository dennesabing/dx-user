<?php

namespace DxUser\Controller;

use DxUser\Controller\ZfcUser;
use Zend\View\Model\ViewModel;

class AccountController extends ZfcUser
{

	public function indexAction()
	{
		$this->getUserService()->getCurrentUser();
		$this->layout('layout/2column-leftbar');
		if ($this->zfcUserAuthentication()->hasIdentity())
		{
			$viewData = array();
			$viewData['enableEmailVerification'] = $this->getModuleOptions('dxuser')->getEnableEmailVerification();
			$viewData['user'] = $this->getUserService()->getCurrentUser();
			return new ViewModel($viewData);
		}
		return $this->redirect()->toRoute($this->getModuleOptions()->getRouteLogin());
	}

	/**
	 * The Profile update
	 * @return \Zend\View\Model\ViewModel
	 */
	public function profileAction()
	{
		$this->layout('layout/2column-leftbar');
		if ($this->zfcUserAuthentication()->hasIdentity())
		{
			$viewData = array();
			$viewData['user'] = $this->getUserService()->getCurrentUser();
			$form = $this->getProfileChangeForm();
			$user = $this->getUserService()->getCurrentUser();
			$userProfile = $this->getUserService()->getProfile($user);
			$profile = new \ArrayObject;
			$profile['fsDisplayName']['displayName'] = $user->getDisplayName();
			$profile['fsMain']['firstName'] = $userProfile->getFirstName();
			$profile['fsMain']['lastName'] = $userProfile->getLastName();
			$form->bind($profile);
			if ($this->getRequest()->isPost())
			{
				$form->setData($this->getRequest()->getPost());
				if ($form->isValid())
				{
					$viewData['success'] = $this->getUserService()->updateProfile($form->getData(\Zend\Form\FormInterface::VALUES_AS_ARRAY));
				}
			}
			$viewData['form'] = $form;
			$viewData['formDisplayOptions'] = $form->getDisplayOptions();
			return new ViewModel($viewData);
		}
		return $this->redirect()->toRoute($this->getModuleOptions()->getRouteLogin());
	}

	/**
	 * Update account password
	 * @return \Zend\View\Model\ViewModel
	 */
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
					if ($this->getUserService()->checkPassword($oldPassword))
					{
						$data['newCredential'] = $data['fsReset']['newPassword'];
						$changed = $this->getUserService()->changePassword($data);
						if ($changed)
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

	/**
	 * Update account email address
	 * @return \Zend\View\Model\ViewModel
	 */
	public function emailAction()
	{
		if ($this->zfcUserAuthentication()->hasIdentity())
		{
			$this->layout('layout/2column-leftbar');
			$viewData = array();
			$viewData['enableEmailVerification'] = $this->getModuleOptions('dxuser')->getEnableEmailVerification();
			$userIdentity = $this->getUserService()->getCurrentUser();
			$viewData['emailAddress'] = $userIdentity->getEmail();
			if (!$userIdentity->isEmailVerified())
			{
				$viewData['emailVerified'] = FALSE;
			}
			$viewData['currentEmail'] = $userIdentity->getEmail();
			$form = $this->getEmailChangeForm();
			if ($this->getRequest()->isPost())
			{
				$form->setData($this->getRequest()->getPost());
				if ($form->isValid())
				{
					$data = $form->getData();
					$oldPassword = $data['fsMain']['password'];
					if ($this->getUserService()->checkPassword($oldPassword))
					{
						$data['email'] = $data['fsReset']['email'];
						$userCode = $this->getUserService()->changeEmail($data);
						if ($userCode)
						{
							$viewData['success'] = TRUE;
							$viewData['userCode'] = $userCode;
						}
					}
					else
					{
						$viewData['passwordError'] = TRUE;
					}
				}
			}
			$viewData['form'] = $form;
			$viewData['formDisplayOptions'] = $form->getDisplayOptions();
			return new ViewModel($viewData);
		}
		return $this->redirect()->toRoute($this->getModuleOptions()->getRouteLogin());
	}

	/**
	 * Resend user's email verification
	 * @return \Zend\View\Model\ViewModel
	 */
	public function emailresendAction()
	{
		if ($this->zfcUserAuthentication()->hasIdentity() && $this->getModuleOptions('dxuser')->getEnableEmailVerification())
		{
			$this->layout('layout/2column-leftbar');
			$viewData = array();
			$userIdentity = $this->getUserService()->getCurrentUser();
			$viewData['emailAddress'] = $userIdentity->getEmail();
			if ($userIdentity->isEmailVerified())
			{
				$viewData['emailVerified'] = TRUE;
			}
			else
			{
				$userCode = $this->getUserService()->resendEmailVerification();
				if ($userCode)
				{
					$viewData['success'] = TRUE;
					$viewData['userCode'] = $userCode;
					$this->dxController()->getSession()->offsetSet('accountEmailresend', TRUE);
					$this->dxController()->getSession()->setExpirationSeconds(300, 'accountEmailResend');
				}
			}
			return new ViewModel($viewData);
		}
		return $this->redirect()->toRoute($this->getModuleOptions()->getRouteLogin());
	}

	/**
	 * Get the Password change form
	 * @return \Zend\Form\Form
	 */
	public function getPasswordChangeForm()
	{
		return $this->getServiceLocator()->get('dxuser_form_account_password');
	}

	/**
	 * Get the Email change form
	 * @return \Zend\Form\Form
	 */
	public function getEmailChangeForm()
	{
		return $this->getServiceLocator()->get('dxuser_form_account_email');
	}

	/**
	 * Get the Email change form
	 * @return \Zend\Form\Form
	 */
	public function getProfileChangeForm()
	{
		return $this->getServiceLocator()->get('dxuser_form_account_profile');
	}

}