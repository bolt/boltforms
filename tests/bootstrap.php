<?php

define('TEST_ROOT',    __DIR__ . '/tmp');
define('PHPUNIT_ROOT', realpath(dirname(__DIR__)));
define('BOLT_AUTOLOAD',  realpath(dirname(__DIR__) . '/vendor/autoload.php'));

@mkdir(TEST_ROOT . '/app/cache', 0777, true);
@mkdir(TEST_ROOT . '/app/config', 0777, true);
@mkdir(TEST_ROOT . '/app/database', 0777, true);

require_once BOLT_AUTOLOAD;
