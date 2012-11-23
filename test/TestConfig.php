<?php
define('ZF2_PATH', __DIR__ . '/../../../../vendor/zendframework/zendframework/library');
define('ZF2_MODULES_TEST_PATHS', __DIR__ .'/../../../../vendor');

return array(
    'modules' => array(
        'Dxapp',
        'DxUser',
    ),
    'module_listener_options' => array(
        'module_paths' => array(
            './',
        ),
    ),
);