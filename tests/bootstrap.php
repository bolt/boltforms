<?php

define('TEST_ROOT', __DIR__ . '/tmp');
define('PHPUNIT_ROOT', __DIR__);

@mkdir('tmp/app/cache', 0777, true);
@mkdir('tmp/app/config', 0777, true);
@mkdir('tmp/app/database', 0777, true);

require '../vendor/autoload.php';
