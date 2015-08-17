<?php

namespace Bolt\Extension\Bolt\BoltForms\Tests;

/**
 * RecaptchaServiceProvider tests.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class RecaptchaServiceProviderTest extends AbstractBoltFormsUnitTest
{
    public function testProviderRegister()
    {
        $app = $this->getApp();
        $this->getExtension()->config['recaptcha']['private_key'] = 'abc123';

        $this->assertInstanceOf('\ReCaptcha\ReCaptcha', $app['recaptcha']);
    }
}
