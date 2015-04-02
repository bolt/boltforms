<?php

define('TEST_ROOT', __DIR__ . '/tmp');
define('PHPUNIT_ROOT', __DIR__);
define('VENDOR_ROOT', realpath(dirname(__DIR__) . '/vendor'));

@mkdir('tmp/app/cache', 0777, true);
@mkdir('tmp/app/config', 0777, true);
@mkdir('tmp/app/database', 0777, true);

require VENDOR_ROOT . '/autoload.php';
