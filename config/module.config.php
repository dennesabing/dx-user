<?php

$config = array();
$config['doctrine'] = array(
	'driver' => array(
		'DxUser_driver' => array(
			'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
			'cache' => 'memcache',
			'paths' => array('/../src/DxUser/Entity')
		),
		'orm_default' => array(
			'drivers' => array(
				'DxUser\Entity' => 'DxUser_driver'
			)
		)
	),
);
return $config;