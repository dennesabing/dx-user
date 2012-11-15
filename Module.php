<?php

// module/Album/Module.php

namespace DxUser;

use Dx\Module as xModule;
use Zend\EventManager\EventInterface as Event;

class Module extends xModule
{

	public $namespace = __NAMESPACE__;
	public $dir = __DIR__;

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
}