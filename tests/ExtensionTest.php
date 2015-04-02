<?php

namespace Bolt\Extension\Bolt\BoltForms\Tests;

use Bolt\Tests\BoltUnitTest;
use Bolt\Extension\Bolt\BoltForms\Extension;

/**
 * Ensure that BoltForms loads correctly.
 *
 */
class ExtensionTest extends BoltUnitTest
{
    public function testExtensionRegister()
    {
        $app = $this->getApp();
        $extension = new Extension($app);
        $app['extensions']->register( $extension );
        $name = $extension->getName();
        $this->assertSame($name, 'BoltForms');
        $this->assertSame($extension, $app["extensions.$name"]);
    }
}
