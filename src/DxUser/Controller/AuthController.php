<?php

namespace DxUser\Controller;

use Zend\Stdlib\ResponseInterface as Response;
use Zend\Stdlib\Parameters;
use Zend\View\Model\ViewModel;
use ZfcUser\Controller\UserController as ZfcUserController;

class AuthController extends ZfcUserController
{

	protected $modulePrefix = 'dxuser';

	public function indexAction()
	{
		if ($this->zfcUserAuthentication()->getAuthService()->hasIdentity())
		{
			return $this->redirect()->toRoute($this->getOptions()->getLoginRedirectRoute());
		}
		$request = $this->getRequest();
		$adapter = $this->zfcUserAuthentication()->getAuthAdapter();
		$redirect = $request->getPost()->get('redirect') ? $request->getPost()->get('redirect') : false;

		$result = $adapter->prepareForAuthentication($request);

		// Return early if an adapter returned a response
		if ($result instanceof Response)
		{
			return $result;
		}

		$auth = $this->zfcUserAuthentication()->getAuthService()->authenticate($adapter);

		if (!$auth->isValid())
		{
			$this->dxController()->addMessage('Email and Password don\'t match.');
			$adapter->resetAdapters();
			return $this->redirect()->toUrl($this->url()->fromRoute('zfcuser/login') . ($redirect ? '?redirect=' . $redirect : ''));
		}

		if ($this->getOptions()->getUseRedirectParameterIfPresent() && $redirect)
		{
			return $this->redirect()->toUrl($redirect);
		}

		return $this->redirect()->toRoute($this->getOptions()->getLoginRedirectRoute());
	}
	
	

	public function getUserService()
	{
		return $this->getServiceLocator()->get('dxuser_service_user');
	}

	protected function getModuleOptions()
	{
		return $this->dxController()->getModuleOptions($this->modulePrefix);
	}

}