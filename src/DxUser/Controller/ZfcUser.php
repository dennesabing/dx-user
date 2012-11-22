<?php

namespace DxUser\Controller;

use Zend\Stdlib\ResponseInterface as Response;
use Zend\Stdlib\Parameters;
use Zend\View\Model\ViewModel;
use ZfcUser\Controller\UserController as ZfcUserController;

class ZfcUser extends ZfcUserController
{
	/**
	 * Authenticate
	 * @param object $request The Request Object
	 * @return boolean TRUE if authentication is succesfull
	 */
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

	/**
	 * Return the Redirect URL
	 * @return string
	 */
	public function getRedirectUrl()
	{
		if ($this->getOptions()->getUseRedirectParameterIfPresent() && $this->getRequest()->getQuery()->get('redirect'))
		{
			return $this->getRequest()->getQuery()->get('redirect');
		}
		return FALSE;
	}
	
	/**
	 * Return the User Service
	 * @return \DxUser\Service\User
	 */
	public function getUserService()
	{
		return $this->getServiceLocator()->get('dxuser_service_user');
	}
	
	/**
	 * Return the Module Options/Configuration Object
	 * @param string $modulePrefix The Module
	 * @return ModuleOptions
	 */
	protected function getModuleOptions($modulePrefix = 'dxuser')
	{
		return $this->dxController()->getModuleOptions($modulePrefix);
	}
	
	/**
	 * Return the ZfcUser Configuration/Option
	 * @return object
	 */
	protected function getZfcUserOptions()
	{
		return $this->getModuleOptions('zfcuser_module_options');
	}
}
