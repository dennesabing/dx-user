<?php

namespace DxUser\Options;

use Zend\Stdlib\AbstractOptions;

class ModuleOptions extends AbstractOptions
{

	/**
	 * The service manager
	 * @var type 
	 */
	protected $serviceManager = NULL;

	/**
	 * The User Code Entity
	 * @var string
	 */
	protected $entityUserCode = 'DxUser\Entity\UserCodes';

	/**
	 * Enable/Disable email senting
	 * @var boolean
	 */
	protected $emailSending = TRUE;

	/**
	 * Enable email verification
	 * @var boolean
	 */
	protected $enableEmailVerification = TRUE;

	/**
	 * Set the email verification template
	 * @var string
	 */
	protected $templateVerifyEmail = 'dx-user/email/email-verify';

	/**
	 * The email address of the sender of the email verification
	 * @var string
	 */
	protected $emailNoReplySender = 'no-reply@localhost';

	/**
	 * The name of the sender of the email verification
	 * @var string
	 */
	protected $emailNoReplySenderName = 'No Reply';

	/**
	 * The subject of the email verification
	 * @var string
	 */
	protected $emailVerifySubject = 'Please verify email address';

	/**
	 * The mode of how to reset password. reset or retrieve
	 * If reset = verification code will be sent to email with link to reset password
	 * if retrieve = send password to user email address.
	 * @TODO retrieve mode of forgotten password. Needs implementation
	 * @var string
	 */
	protected $passwordForgottenMode = 'reset';

	/**
	 * The Reset Password subject
	 * @var string
	 */
	protected $emailResetPasswordSubject = 'Reset your password';

	/**
	 * The Subject in an email sent after a successfull password changed.
	 * @var string
	 */
	protected $emailPasswordChangedSubject = 'Your password was changed';

	/**
	 * The ResetPassword email template
	 * @var string
	 */
	protected $templateResetPasswordEmail = 'dx-user/email/reset-password';
	
	/**
	 * The template of an email to use after a successfull password update
	 * @var string
	 */
	protected $templateChangedPasswordEmail = 'dx-user/email/changed-password';
	
	/**
	 * The Login Route
	 * @var string
	 */
	protected $routeLogin = 'dx-user-login';

	/**
	 * The Registration Route
	 * @var string
	 */
	protected $routeRegistration = 'dx-user-register';

	/**
	 * The Password Reset Route
	 * @var string
	 */
	protected $routePasswordReset = 'dx-user-password';

	/**
	 * Route to user acount page
	 * @var string
	 */
	protected $routeUserAccount = 'dx-user-account';

	/**
	 * The Locatio for XML Forms
	 * @var type 
	 */
	protected $xmlFormFolder = NULL;

	/**
	 * Set The template of an email to use after a successfull password update
	 * @param string $template The template
	 * @return \DxUser\Options\ModuleOptions
	 */
	public function setTemplateChangedPasswordEmail($template)
	{
		$this->templateChangedPasswordEmail = $template;
		return $this;
	}
	
	/**
	 * Get The template of an email to use after a successfull password update
	 * @return string
	 */
	public function getTemplateChangedPasswordEmail()
	{
		return $this->templateChangedPasswordEmail;
	}
	
	/**
	 * Set the Subject in an email of a successful password changed
	 * @param string $subject The Subject
	 * @return \DxUser\Options\ModuleOptions
	 */
	public function setEmailPasswordChangedSubject($subject)
	{
		$this->emailPasswordChangedSubject = $subject;
		return $this;
	}

	/**
	 * Get the Subject in an email of a successful password changed
	 */
	public function getEmailPasswordChangedSubject()
	{
		return $this->emailPasswordChangedSubject;
	}

	/**
	 * Set the UserCode Entity
	 * @param string $entity
	 * @return \DxUser\Options\ModuleOptions 
	 */
	public function setEntityUserCode($entity)
	{
		$this->entityUserCode = $entity;
		return $this;
	}

	/**
	 * REturn the UserCode Entity
	 * @return type 
	 */
	public function getEntityUserCode()
	{
		return $this->entityUserCode;
	}

	/**
	 * Set the location of the XML Form Folder
	 * @param string $folder
	 * @return \DxUser\Options\ModuleOptions 
	 */
	public function setXmlFormFolder($folder)
	{
		if (file_exists($folder))
		{
			$this->xmlFormFolder = $folder;
		}
		else
		{
			$this->xmlFormFolder = __DIR__ . '/../../../data/forms';
		}
		return $this;
	}

	/**
	 * Get the XML Form Folder
	 * @return string
	 */
	public function getXmlFormFolder()
	{
		return $this->xmlFormFolder;
	}

	/**
	 * SEt the Route to the user account page
	 * @param string $route
	 * @return \DxUser\Options\ModuleOptions 
	 */
	public function setRouteUserAccount($route)
	{
		$this->routeUserAccount = $route;
		return $this;
	}

	/**
	 * Get the route to the user account page
	 * @return string
	 */
	public function getRouteUserAccount()
	{
		return $this->routeUserAccount;
	}

	/**
	 * SEt the Route to login
	 * @param string $route
	 * @return \DxUser\Options\ModuleOptions 
	 */
	public function setRouteLogin($route)
	{
		$this->routeLogin = $route;
		return $this;
	}

	/**
	 * Get the login Route
	 * @return string
	 */
	public function getRouteLogin()
	{
		return $this->routeLogin;
	}

	/**
	 * Set the Route to Registration Page
	 * @param string $route
	 * @return \DxUser\Options\ModuleOptions 
	 */
	public function setRouteRegistration($route)
	{
		$this->routeRegistration = $route;
		return $this;
	}

	/**
	 * Get the Route to Registration Page
	 * @return string
	 */
	public function getRouteRegistration()
	{
		return $this->routeRegistration;
	}

	/**
	 * GEt the Route to Reset Password
	 * @param string $route
	 * @return \DxUser\Options\ModuleOptions 
	 */
	public function setRoutePasswordReset($route)
	{
		$this->routePasswordReset = $route;
		return $this;
	}

	/**
	 * Get the Route to reset Password
	 * @return string
	 */
	public function getRoutePasswordReset()
	{
		return $this->routePasswordReset;
	}

	/**
	 * Get the main route
	 * @return string
	 */
	public function getRouteMain()
	{
		return $this->getServiceManager()->get('dxapp_module_options')->getRouteMain();
	}

	/**
	 * Return email sending status
	 * @return boolean
	 */
	public function getEmailSending()
	{
		return $this->getServiceManager()->get('dxapp_module_options')->getEmailSending();
	}

	/**
	 * Set the sender - email of the Email verifcation
	 * @param string $email The email address
	 * @return \DxUser\Options\ModuleOptions 
	 */
	public function setEmailNoReplySender($email)
	{
		$this->emailNoReplySender = $email;
		return $this;
	}

	/**
	 * Return the email address of the sender of email verify
	 * @return string
	 */
	public function getEmailNoReplySender()
	{
		return $this->emailNoReplySender;
	}

	/**
	 * Set the Name of the sender for the email verification
	 * @param string $name The Name of the sender
	 * @return \DxUser\Options\ModuleOptions 
	 */
	public function setEmailNoReplySenderName($name)
	{
		$this->emailNoReplySenderName = $name;
		return $this;
	}

	/**
	 * Get the name of the sender of the email verification
	 * @return string
	 */
	public function getEmailNoReplySenderName()
	{
		return $this->emailNoReplySenderName;
	}

	/**
	 * Set the subject of the email verification
	 * @param string $subject The email subject
	 * @return \DxUser\Options\ModuleOptions 
	 */
	public function setEmailVerifySubject($subject)
	{
		$this->emailVerifySubject = $subject;
		return $this;
	}

	/**
	 * Get the email verification subject
	 * @return string
	 */
	public function getEmailVerifySubject()
	{
		return $this->emailVerifySubject;
	}

	/**
	 * Set the subject of the password reset
	 * @param string $subject The email subject
	 * @return \DxUser\Options\ModuleOptions 
	 */
	public function setEmailResetPasswordSubject($subject)
	{
		$this->emailResetPasswordSubject = $subject;
		return $this;
	}

	/**
	 * Get the email verification subject
	 * @return string
	 */
	public function getEmailResetPasswordSubject()
	{
		return $this->emailResetPasswordSubject;
	}

	/**
	 * Set the Email verification template file
	 * @param string $template The email template file
	 * @return \DxUser\Options\Module 
	 */
	public function setTemplateVerifyEmail($template)
	{
		$this->templateVerifyEmail = $template;
		return $this;
	}

	/**
	 * Get the email verification template
	 * @return string
	 */
	public function getTemplateVerifyEmail()
	{
		return $this->templateVerifyEmail;
	}

	/**
	 * Set the REset Password email template
	 * @param string $template
	 * @return \DxUser\Options\ModuleOptions 
	 */
	public function setTemplateResetPasswordEmail($template)
	{
		$this->templateResetPasswordEmail = $template;
		return $this;
	}

	/**
	 * GEt the Reset Password email template
	 * @return string
	 */
	public function getTemplateResetPasswordEmail()
	{
		return $this->templateResetPasswordEmail;
	}

	/**
	 * If to enable the email verification
	 * @param boolean $flag
	 * @return \DxUser\Options\Module 
	 */
	public function setEnableEmailVerification($flag)
	{
		$this->enableEmailVerification = $flag;
		return $this;
	}

	/**
	 * Return if to enable the email verification
	 * @return boolean
	 */
	public function getEnableEmailVerification()
	{
		return $this->enableEmailVerification;
	}

	/**
	 * SEt the service manager instance
	 * @param object $sm
	 * @return \DxUser\Options\ModuleOptions 
	 */
	public function setServiceManager($sm)
	{
		$this->serviceManager = $sm;
		return $this;
	}

	/**
	 * Return the ServiceManager instance
	 * @return object 
	 */
	public function getServiceManager()
	{
		return $this->serviceManager;
	}

	/**
	 * Return the User Entity Class from sfcUser ModuleOptions
	 */
	public function getUserEntityClass()
	{
		return $this->getServiceManager()->get('zfcuser_module_options')->getUserEntityClass();
	}

}
