<?php
namespace DxUserBaseTest;

use Zend\Loader\AutoloaderFactory;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\ArrayUtils;
use RuntimeException;

error_reporting(E_ALL | E_STRICT);
chdir(__DIR__);

class Bootstrap
{
    protected static $serviceManager;

    public static function init()
    {
		$global = array();
		$local = array();
		if(file_exists(__DIR__ . '/config.global.php'))
		{
			$global = include __DIR__ . '/config.global.php';
		}
		if(file_exists(__DIR__ . '/config.local.php'))
		{
			$local = include __DIR__ . '/config.local.php';
		}
		$config = array_merge_recursive($global, $local);
		self::initAutoloader();
        $serviceManager = new ServiceManager(new ServiceManagerConfig());
        $serviceManager->setService('ApplicationConfig', $config);
        $serviceManager->get('ModuleManager')->loadModules();
        static::$serviceManager = $serviceManager;
    }

    public static function getServiceManager()
    {
        return static::$serviceManager;
    }

    protected static function initAutoloader()
    {
        $loader = include VENDOR_PATH . '/autoload.php';
        $zf2Path = VENDOR_PATH . '/zendframework/zendframework/library';
		$loader->add('Zend', $zf2Path . '/Zend');
		include $zf2Path . '/Zend/Loader/AutoloaderFactory.php';
		AutoloaderFactory::factory(array(
			'Zend\Loader\StandardAutoloader' => array(
				'autoregister_zf' => true,
				'namespaces' => array(
					__NAMESPACE__ => __DIR__ . '/' . __NAMESPACE__,
					'Dx' => DX_PATH
				),
			),
		));
    }
	
}

Bootstrap::init();
