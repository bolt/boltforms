<?php

namespace Bolt\Extension\Bolt\BoltForms\Tests;

use Bolt\Tests\BoltUnitTest;
use Bolt\Extension\Bolt\BoltForms\Extension;

/**
 * Ensure that BoltForms loads correctly.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class ExtensionTest extends AbstractBoltFormsUnitTest
{
    public function testExtensionRegister()
    {
        $app = $this->getApp();
        $extension = $this->getExtension($app);

        // Check getName() returns the correct value
        $name = $extension->getName();
        $this->assertSame($name, 'BoltForms');

        // Check that we're able to use "safe Twig"
        $this->assertTrue($extension->isSafe());

        // Check that we're giving warnings for mail
        $this->assertTrue($extension->sendsMail());
    }
}
