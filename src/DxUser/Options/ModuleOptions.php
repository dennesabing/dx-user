<?php

namespace DxUser\Options;

use Zend\Stdlib\AbstractOptions;

class ModuleOptions extends AbstractOptions
{
	
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
	protected $templateVerifyEmail = 'email/verify-email.phtml';
	
	/**
	 * The email address of the sender of the email verification
	 * @var string
	 */
	protected $emailVerifySender = 'no-reply@localhost';
	
	/**
	 * The name of the sender of the email verification
	 * @var string
	 */
	protected $emailVerifySenderName = 'No Reply';
	
	/**
	 * The subject of the email verification
	 * @var string
	 */
	protected $emailVerifySubject = 'Please verify email address';

	/**
	 * Enable/Disable email sending
	 * @param boolean $flag
	 * @return \DxUser\Options\ModuleOptions 
	 */
	public function setEmailSending($flag)
	{
		$this->emailSending = $flag;
		return $this;
	}
	
	/**
	 * Return email sending status
	 * @return boolean
	 */
	public function getEmailSending()
	{
		return $this->emailSending;
	}

	/**
	 * Set the sender - email of the Email verifcation
	 * @param string $email The email address
	 * @return \DxUser\Options\ModuleOptions 
	 */
	public function setEmailVerifySender($email)
	{
		$this->emailVerifySender = $email;
		return $this;
	}
	
	/**
	 * Return the email address of the sender of email verify
	 * @return string
	 */
	public function getEmailVerifySender()
	{
		return $this->emailVerifySender;
	}
	
	/**
	 * Set the Name of the sender for the email verification
	 * @param string $name The Name of the sender
	 * @return \DxUser\Options\ModuleOptions 
	 */
	public function setEmailVerifySenderName($name)
	{
		$this->emailVerifySenderName = $name;
		return $this;
	}
	
	/**
	 * Get the name of the sender of the email verification
	 * @return string
	 */
	public function getEmailVerifySenderName()
	{
		return $this->emailVerifySenderName;
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
}
