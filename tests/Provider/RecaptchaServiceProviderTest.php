<?php

namespace Bolt\Extension\Bolt\BoltForms\Tests;

use Bolt\Tests\BoltUnitTest;
use Bolt\Extension\Bolt\BoltForms\Extension;
use Bolt\Extension\Bolt\BoltForms\BoltForms;
use Bolt\Extension\Bolt\BoltForms\Database;
use Bolt\Extension\Bolt\BoltForms\Email;
use Bolt\Extension\Bolt\BoltForms\Subscriber\BoltFormsCustomDataSubscriber;

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
        $this->getExtension($app)->config['recaptcha']['private_key'] = 'abc123';

        $this->assertInstanceOf('\ReCaptcha\ReCaptcha', $app['recaptcha']);
    }
}