<?php

namespace Bolt\Extension\Bolt\BoltForms\Tests;

use Bolt\Extension\Bolt\BoltForms\BoltForms;
use Bolt\Extension\Bolt\BoltForms\Database;
use Bolt\Extension\Bolt\BoltForms\Email;

/**
 * BoltFormsServiceProvider tests.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class BoltFormsServiceProviderTest extends AbstractBoltFormsUnitTest
{
    public function testProviderRegister()
    {
        $app = $this->getApp();

        $this->assertInstanceOf('\Bolt\Extension\Bolt\BoltForms\BoltForms', $app['boltforms']);
        $this->assertInstanceOf('\Bolt\Extension\Bolt\BoltForms\Database', $app['boltforms.database']);
        $this->assertInstanceOf('\Bolt\Extension\Bolt\BoltForms\Email', $app['boltforms.email']);
        $this->assertInstanceOf('\Bolt\Extension\Bolt\BoltForms\Subscriber\BoltFormsCustomDataSubscriber', $app['boltforms.subscriber.custom_data']);
    }
}
