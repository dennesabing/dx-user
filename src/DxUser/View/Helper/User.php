<?php

/**
 * Dx View Helper
 * 
 */

namespace DxUser\View\Helper;

use Dxapp\View\AbstractHelper;

class User extends AbstractHelper
{
	public function __invoke()
	{
		return $this;
	}
	
	/**
	 * Check if a user is loggedIn
	 * @param object $user DxUser\Entity\User
	 * @return boolean
	 */
	public function isLogin($user = NULL)
	{
		return $this->view->zfcUserIdentity();
	}
	
	/**
	 * Check if the given user is an admin
	 * @param object $user DxUser\Entity\User
	 * @return boolean
	 */
	public function isAdmin($user = NULL)
	{
		return $this->getUserService()->isAdmin($user);
	}
	
	/**
	 * Return the DxService
	 * @return type
	 */
	public function getUserService()
	{
		return $this->getServiceManager()->get('dxuser_service_user');
	}
}
