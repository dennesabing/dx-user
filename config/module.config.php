<?php

$config = array(
	'view_manager' => array(
		'template_path_stack' => array(
			'dxuser' => __DIR__ . '/../view',
		),
	),
	'controllers' => array(
		'invokables' => array(
			'DxUser\Controller\Register' => 'DxUser\Controller\RegisterController',
			'DxUser\Controller\Account' => 'DxUser\Controller\AccountController',
			'DxUser\Controller\Password' => 'DxUser\Controller\PasswordController',
			'DxUser\Controller\Login' => 'DxUser\Controller\LoginController'
		),
	),
	'router' => array(
		'routes' => array(
			'dx-user-account' => array(
				'type' => 'Literal',
				'options' => array(
					'route' => '/account',
					'defaults' => array(
						'__NAMESPACE__' => 'DxUser\Controller',
						'controller' => 'DxUser\Controller\Account',
						'action' => 'index',
					),
				),
                'may_terminate' => true,
			),
			'dx-user-register' => array(
				'type' => 'Literal',
				'priority' => 10000,
				'options' => array(
					'route' => '/register',
					'defaults' => array(
						'controller' => 'DxUser\Controller\Register',
						'action' => 'register',
					),
				),
			),
			'zfcuser-register' => array(
				'type' => 'Literal',
				'priority' => 10000,
				'options' => array(
					'route' => '/user/register',
					'defaults' => array(
						'controller' => 'DxUser\Controller\Register',
						'action' => 'index',
					),
				),
			),
			'dx-user-account' => array(
				'type' => 'Literal',
				'priority' => 10000,
				'options' => array(
					'route' => '/account',
					'defaults' => array(
						'controller' => 'DxUser\Controller\Account',
						'action' => 'account',
					),
				),
			),
			'zfcuser-account' => array(
				'type' => 'Literal',
				'priority' => 10000,
				'options' => array(
					'route' => '/user',
					'defaults' => array(
						'controller' => 'DxUser\Controller\Account',
						'action' => 'index',
					),
				),
			),
			'dx-user-login' => array(
				'type' => 'Literal',
				'priority' => 10000,
				'options' => array(
					'route' => '/login',
					'defaults' => array(
						'controller' => 'DxUser\Controller\Login',
						'action' => 'login',
					),
				),
			),
			'zfcuser-login' => array(
				'type' => 'Literal',
				'priority' => 10000,
				'options' => array(
					'route' => '/user/login',
					'defaults' => array(
						'controller' => 'DxUser\Controller\Login',
						'action' => 'index',
					),
				),
			),
			'dx-user-password' => array(
				'type' => 'Literal',
				'priority' => 10000,
				'options' => array(
					'route' => '/password',
					'defaults' => array(
						'controller' => 'DxUser\Controller\Password',
						'action' => 'password',
					),
				),
			),
			'zfcuser-password' => array(
				'type' => 'Literal',
				'priority' => 10000,
				'options' => array(
					'route' => '/user/password',
					'defaults' => array(
						'controller' => 'DxUser\Controller\Password',
						'action' => 'index',
					),
				),
			),
		)
	)
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