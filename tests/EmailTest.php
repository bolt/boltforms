<?php
namespace Bolt\Extension\Bolt\BoltForms\Tests;

use Bolt\Extension\Bolt\BoltForms\BoltForms;
// use Bolt\Tests\Mocks\DoctrineMockBuilder;
use Bolt\Extension\Bolt\BoltForms\Email;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

/**
 * BoltForms\Email class tests.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class EmailTest extends AbstractBoltFormsUnitTest
{
    public function testConstructor()
    {
        $app = $this->getApp();
        $boltforms = new Email($app);

        $this->assertInstanceOf('\Bolt\Extension\Bolt\BoltForms\Email', $boltforms);
    }

    public function testSendEmail()
    {
        $app = $this->getApp();
        $this->getExtension()->config['csrf'] = false;
        $this->getExtension()->config['debug']['enabled'] = false;
        $this->getExtension()->config['testing_form']['notification'] = $this->formNotificationConfig();

        // Upload file set up
        $this->getExtension()->config['uploads']['enabled'] = true;
        $this->getExtension()->config['uploads']['base_directory'] = sys_get_temp_dir();
        $srcFile = EXTENSION_TEST_ROOT . '/tests/data/bolt-logo.png';
        $tmpFile = sys_get_temp_dir() . '/' . uniqid('php_');

        $fs = new Filesystem();
        $fs->copy($srcFile, $tmpFile, true);

        $app['request'] = Request::create('/');

        $app['boltforms']->makeForm('testing_form');
        $fields = $this->formFieldConfig();
        $app['boltforms']->addFieldArray('testing_form', $fields);

        $parameters = $this->formData();
        $parameters['testing_form']['file'] = new UploadedFile($tmpFile, 'bolt-logo.png', null, null, null, true);
        $app['request'] = Request::create('/', 'POST', $parameters);

        $result = $this->processor()->process('testing_form', $this->getExtension()->config['testing_form'], array('success' => true));

        $this->assertTrue($result);
    }

    public function testSendEmailFail()
    {
        $app = $this->getApp();
        $this->getExtension()->config['csrf'] = false;
        $this->getExtension()->config['debug']['enabled'] = false;
        $this->getExtension()->config['testing_form']['notification'] = $this->formNotificationConfig();

        $app['request'] = Request::create('/');

        $mailer = $this->getMock('\Swift_Mailer', array('send'), array($app['swiftmailer.transport']));
        $mailer->expects($this->any())
            ->method('send')
            ->will($this->returnValue(false));
        $app['mailer'] = $mailer;

        $logger = $this->getMock('\Monolog\Logger', array('error'), array('testlogger'));
        $logger->expects($this->atLeastOnce())
            ->method('error');
        $app['logger.system'] = $logger;

        $app['boltforms']->makeForm('testing_form');
        $fields = $this->formFieldConfig();
        $app['boltforms']->addFieldArray('testing_form', $fields);

        $parameters = $this->formData();
        $app['request'] = Request::create('/', 'POST', $parameters);

        $result = $this->processor()->process('testing_form', $this->getExtension()->config['testing_form'], array('success' => true));

        $this->assertTrue($result);
    }

    public function testSendEmailDebug()
    {
        $app = $this->getApp();
        $this->getExtension()->config['csrf'] = false;
        $this->getExtension()->config['debug']['enabled'] = true;
        $this->getExtension()->config['debug']['address'] = 'noreply@example.com';
        $this->getExtension()->config['testing_form']['notification'] = $this->formNotificationConfig();
        $this->getExtension()->config['testing_form']['notification']['enabled'] = true;

        $app['request'] = Request::create('/');

        $app['boltforms']->makeForm('testing_form');
        $fields = $this->formFieldConfig();
        $app['boltforms']->addFieldArray('testing_form', $fields);

        $parameters = $this->formData();
        $app['request'] = Request::create('/', 'POST', $parameters);

        $result = $this->processor()->process('testing_form', $this->getExtension()->config['testing_form'], array('success' => true));

        $this->assertTrue($result);
    }

    public function testSendEmailDebugFail()
    {
        $app = $this->getApp();
        $this->getExtension()->config['csrf'] = false;
        $this->getExtension()->config['debug']['enabled'] = true;
        $this->getExtension()->config['debug']['address'] = null;
        $this->getExtension()->config['testing_form']['notification']['enabled'] = true;

        $app['request'] = Request::create('/');

        $app['boltforms']->makeForm('testing_form');
        $fields = $this->formFieldConfig();
        $app['boltforms']->addFieldArray('testing_form', $fields);

        $parameters = $this->formData();
        $app['request'] = Request::create('/', 'POST', $parameters);

        $this->setExpectedException('\Bolt\Extension\Bolt\BoltForms\Exception\EmailException', '[BoltForms] Debug email address can not be empty if debugging enabled!');

        $result = $this->processor()->process('testing_form', $this->getExtension()->config['testing_form'], array('success' => true));

        $this->assertTrue($result);
    }
}
