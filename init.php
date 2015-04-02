<?php

use Bolt\Extension\Bolt\BoltForms\Extension;

if (isset($app)) {
    $app['extensions']->register(new Extension($app));
}
