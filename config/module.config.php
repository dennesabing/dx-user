<?php

$config = array(
	'view_manager' => array(
		'template_path_stack' => array(
			'dxuser' => __DIR__ . '/../view',
		),
	),	
);
$config['doctrine'] = array(
	'driver' => array(
		'DxUser_driver' => array(
			'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
			'cache' => 'memcache',
			'paths' => array(__DIR__ . '/../src/DxUser/Entity')
		),
		'orm_default' => array(
			'drivers' => array(
				'DxUser\Entity' => 'DxUser_driver'
			)
		)
	),
);
return $config;