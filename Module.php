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
		$eventManager = $application->getEventManager();
		
		$zfcUser = $serviceManager->get('zfcuser_user_service');
		$zfcUserListener = new \DxUser\Listener\ZfcUser\Service\User();
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
                'dxuser_module_options' => function ($sm) {
                    $config = $sm->get('Config');
                    return new \DxUser\Options\Module(isset($config['dxcuser']) ? $config['dxcuser'] : array());
                },
                'dxuser_service_user' => function ($sm) {
                    $userService = new \DxUser\Service\User();
					$userService->setEntityManager($sm->get('doctrine.entitymanager.orm_default'));
                    $userService->setOptions($sm->get('dxuser_module_options'));
                },
			)
		);
	}
}