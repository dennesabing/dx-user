<?php

// module/Album/Module.php

namespace DxUser;

use Dx\Module as xModule;

class Module extends xModule
{

	public $namespace = __NAMESPACE__;
	public $dir = __DIR__;

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