<?php

// module/Album/Module.php

namespace DxUser;

use Zend\Mvc\ModuleRouteListener;
use Zend\Module\Manager,
	Zend\EventManager\StaticEventManager,
	Zend\EventManager\EventInterface as Event;

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
		$serviceManager = $application->getServiceManager();

		$zfcUser = $serviceManager->get('zfcuser_user_service');
		$zfcUserListener = new \DxUser\Listener\ZfcUser\Service\User();
		$zfcUserListener->setServiceManager($serviceManager);
		$zfcUserListener->attach($zfcUser->getEventManager());
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
					$userService->setAuthService($sm->get('zfcuser_auth_service'));
					$userService->setEntityManager($sm->get('doctrine.entitymanager.orm_default'));
					$userService->setOptions($sm->get('dxuser_module_options'));
					$userService->setViewRenderer($sm->get('ViewRenderer'));
					return $userService;
				},
				'dxuser_form_login' => function($sm)
				{
					$options = $sm->get('dxuser_module_options');
					$form = new \Dxapp\Form\Form('login','login.xml', $options, $sm);
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
			)
		);
	}

}