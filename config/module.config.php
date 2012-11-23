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
			'AuthController' => 'DxUser\Controller\AuthController',
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
			'dx-user-auth' => array(
				'type' => 'Literal',
				'priority' => 10000,
				'options' => array(
					'route' => '/auth',
					'defaults' => array(
						'controller' => 'AuthController',
						'action' => 'index',
					),
				),
			),
			'dx-user-email-verify' => array(
				'type' => 'Segment',
				'priority' => 10000,
				'options' => array(
					'route' => '/verify/email/:email/:code',
					'defaults' => array(
						'controller' => 'DxUser\Controller\Register',
						'action' => 'verify',
					),
				),
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
				'may_terminate' => true,
				'child_routes' => array(
					'verify' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/:email/:code',
							'defaults' => array(
								'action' => 'verify',
							),
						),
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
						'action' => 'index',
					),
				),
				'may_terminate' => true,
				'child_routes' => array(
					'password' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/password',
							'defaults' => array(
								'action' => 'password',
							),
						),
					),
					'email' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/email',
							'defaults' => array(
								'action' => 'email',
							),
						),
					),
					'resend-email-verification' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/email/resend-verification',
							'defaults' => array(
								'action' => 'emailresend',
							),
						),
					),
					'profile' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/profile',
							'defaults' => array(
								'action' => 'profile',
							),
						),
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
			'dx-user-logout' => array(
				'type' => 'Literal',
				'priority' => 10000,
				'options' => array(
					'route' => '/logout',
					'defaults' => array(
						'controller' => 'zfcuser',
						'action' => 'logout',
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
				'may_terminate' => true,
				'child_routes' => array(
					'verify' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/:email/:code',
							'defaults' => array(
								'action' => 'verify',
							),
						),
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