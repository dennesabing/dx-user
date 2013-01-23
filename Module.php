<?php

namespace DxUser;

use Zend\EventManager\EventInterface as Event;

class Module
{

	public $namespace = __NAMESPACE__;
	public $dir = __DIR__;

	public function getConfig()
	{
		return include $this->dir . '/config/module.config.php';
	}

	public function onBootstrap(Event $e)
	{
		$application = $e->getApplication();
	}

	public function getAutoloaderConfig()
	{
		return array(
			'Zend\Loader\ClassMapAutoloader' => array(
				$this->dir . '/autoload_classmap.php',
			),
			'Zend\Loader\StandardAutoloader' => array(
				'namespaces' => array(
					$this->namespace => $this->dir . '/src/' . $this->namespace,
				),
			),
		);
	}

	public function getServiceConfig()
	{
		return array(
			'factories' => array(
				'dxuser_module_options' => function ($sm)
				{
					$config = $sm->get('Config');
					$config = new \DxUser\Options\ModuleOptions(isset($config['dxuser']) ? $config['dxuser'] : array());
					$config->setServiceManager($sm);
					return $config;
				},
				'dxuser_service_user' => function ($sm)
				{
					$userService = new \DxUser\Service\User();
					$userService->setEntityManager($sm->get('doctrine.entitymanager.orm_default'));
					$userService->setViewRenderer($sm->get('ViewRenderer'));
					return $userService;
				},
				'dxuser_form_login' => function($sm)
				{
					$options = $sm->get('dxuser_module_options');
					$form = new \Dxapp\Form\Form('login', 'login.xml', $options, $sm);
					$form->setInputFilter(new \Dxapp\InputFilter\InputFilter('login.xml', $options, $sm));
					return $form;
				},
				'dxuser_form_register' => function($sm)
				{
					$options = $sm->get('dxuser_module_options');
					$form = new \Dxapp\Form\Form('register','register.xml', $options, $sm);
					$form->setInputFilter(new \Dxapp\InputFilter\InputFilter('register.xml', $options, $sm));
					return $form;
				},
				'dxuser_form_password_reset' => function($sm)
				{
					$options = $sm->get('dxuser_module_options');
					$form = new \Dxapp\Form\Form('passwordReset','passwordReset.xml', $options, $sm);
					$form->setInputFilter(new \Dxapp\InputFilter\InputFilter('passwordReset.xml', $options, $sm));
					return $form;
				},
				'dxuser_form_password_reset_change' => function($sm)
				{
					$options = $sm->get('dxuser_module_options');
					$form = new \Dxapp\Form\Form('passwordReset','passwordResetChange.xml', $options, $sm);
					$form->setInputFilter(new \Dxapp\InputFilter\InputFilter('passwordResetChange.xml', $options, $sm));
					return $form;
				},
				'dxuser_form_account_password' => function($sm)
				{
					$options = $sm->get('dxuser_module_options');
					$form = new \Dxapp\Form\Form('accountPasswordReset','accountPasswordReset.xml', $options, $sm);
					$form->setInputFilter(new \Dxapp\InputFilter\InputFilter('accountPasswordReset.xml', $options, $sm));
					return $form;
				},
				'dxuser_form_account_email' => function($sm)
				{
					$options = $sm->get('dxuser_module_options');
					$form = new \Dxapp\Form\Form('accountEmail','accountEmail.xml', $options, $sm);
					$form->setInputFilter(new \Dxapp\InputFilter\InputFilter('accountEmail.xml', $options, $sm));
					return $form;
				},
				'dxuser_form_account_profile' => function($sm)
				{
					$options = $sm->get('dxuser_module_options');
					$form = new \Dxapp\Form\Form('accountProfile','accountProfile.xml', $options, $sm);
					$form->setInputFilter(new \Dxapp\InputFilter\InputFilter('accountProfile.xml', $options, $sm));
					return $form;
				}
			)
		);
	}

	

	public function getViewHelperConfig()
	{
		return array(
			'factories' => array(
				'dxUser' => function()
				{
					return new \DxUser\View\Helper\User();
				}
			),
		);
	}
	
}