<?php

namespace Bolt\Extension\Bolt\BoltForms\Tests;

use Bolt\Extension\Bolt\BoltForms\Extension;
use Symfony\Component\HttpFoundation\Request;

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
        $extension = $this->getExtension();

        // Check getName() returns the correct value
        $name = $extension->getName();
        $this->assertSame($name, 'BoltForms');

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

    public function testExtensionController()
    {
        $app = $this->getApp();
        $app['request'] = Request::create('/');
        $this->getExtension()->config['uploads']['management_controller'] = true;
        $this->getExtension()->config['uploads']['base_uri'] = 'koala-country';

        $app[Extension::CONTAINER]->initialize();
    }

    public function testExtensionTwig()
    {
        $app = $this->getApp();
        $config = $this->getMock('\Bolt\Config', array('getWhichEnd'), array($app));
        $config->expects($this->any())
            ->method('getWhichEnd')
            ->will($this->returnValue('frontend'));
        $app['config'] = $config;
        $app[Extension::CONTAINER]->initialize();

        $twigExt = $app['twig']->getExtension('boltforms.extension');
        $this->assertInstanceOf('\Bolt\Extension\Bolt\BoltForms\Twig\BoltFormsExtension', $twigExt);
        $this->assertSame('boltforms.extension', $twigExt->getName());

        $twigExt = $app['safe_twig']->getExtension('boltforms.extension');
        $this->assertInstanceOf('\Bolt\Extension\Bolt\BoltForms\Twig\BoltFormsExtension', $twigExt);
        $this->assertSame('boltforms.extension', $twigExt->getName());
    }
}
