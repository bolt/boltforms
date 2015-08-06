<?php

namespace Bolt\Extension\Bolt\BoltForms\Tests;

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

    public function testGetConfigKeys()
    {
        $extension = $this->getExtension();
        $keys = $extension->getConfigKeys();

        $this->assertContains('csrf', $keys);
        $this->assertContains('recaptcha', $keys);
        $this->assertContains('templates', $keys);
        $this->assertContains('debug', $keys);
        $this->assertContains('uploads', $keys);
    }
}
