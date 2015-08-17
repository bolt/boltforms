<?php
namespace Bolt\Extension\Bolt\BoltForms\Tests;

use Bolt\Extension\Bolt\BoltForms\BoltForms;
use Bolt\Extension\Bolt\BoltForms\Config\EmailConfig;
use Bolt\Extension\Bolt\BoltForms\Config\FormConfig;
use Bolt\Extension\Bolt\BoltForms\FormData;

/**
 * BoltForms\Config\Email class tests.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class EmailConfigTest extends AbstractBoltFormsUnitTest
{
    public function formConfig()
    {
        return new FormConfig(
            'test_form',
            array(
                'debug'        => false,
                'notification' => $this->formNotificationConfig(),
                'fields'       => $this->formFieldConfig()
        ));
    }

    public function testConstructor()
    {
        $app = $this->getApp();

        $globalDebug = $this->getExtension()->config['debug'];
        $formConfig = $this->formConfig();
        $postData = $this->formData();
        $formData = new FormData($postData);

        $emailconfig = new EmailConfig($globalDebug, $formConfig, $formData);

        $this->assertInstanceOf('\Bolt\Extension\Bolt\BoltForms\Config\EmailConfig', $emailconfig);
        $this->assertSame('Gawain Lynch', $emailconfig->getFromName());
        $this->assertSame('gawain@example.com', $emailconfig->getFromEmail());
        $this->assertSame('Surprised Koala', $emailconfig->getReplyToName());
        $this->assertSame('surprised.koala@example.com', $emailconfig->getReplyToEmail());
        $this->assertSame('Kenny Koala', $emailconfig->getToName());
        $this->assertSame('kenny.koala@example.com', $emailconfig->getToEmail());
        $this->assertSame('Bob den Otter', $emailconfig->getCcName());
        $this->assertSame('bob@example.com', $emailconfig->getCcEmail());
        $this->assertSame('Lodewijk Evers', $emailconfig->getBccName());
        $this->assertSame('lodewijk@example.com', $emailconfig->getBccEmail());
        $this->assertTrue($emailconfig->attachFiles());
    }

    public function testDebug()
    {
        $app = $this->getApp();

        $globalDebug = $this->getExtension()->config['debug'];
        $formConfig = $this->formConfig();
        $postData = $this->formData();
        $formData = new FormData($postData);

        // Global override
        $globalDebug['enabled'] = true;
        $emailconfig = new EmailConfig($globalDebug, $formConfig, $formData);
        $this->assertTrue($emailconfig->isDebug());

        // Form level debugging
        $globalDebug['enabled'] = false;
        $formConfig->getNotification()->debug = true;
        $emailconfig = new EmailConfig($globalDebug, $formConfig, $formData);
        $this->assertTrue($emailconfig->isDebug());

        // All debugging off
        $globalDebug['enabled'] = false;
        $formConfig->getNotification()->debug = false;
        $emailconfig = new EmailConfig($globalDebug, $formConfig, $formData);
        $this->assertFalse($emailconfig->isDebug());
    }

    public function testArrayAccess()
    {
        $app = $this->getApp();

        $globalDebug = $this->getExtension()->config['debug'];
        $formConfig = $this->formConfig();
        $postData = $this->formData();
        $formData = new FormData($postData);

        $emailconfig = new EmailConfig($globalDebug, $formConfig, $formData);

        $this->assertNull($emailconfig->offsetSet('debug', true));
        $this->assertNull($emailconfig->offsetExists('debug'));
        $this->assertNull($emailconfig->offsetUnset('debug'));

        $this->assertSame('Gawain Lynch', $emailconfig->offsetGet('from_name'));
    }
}
