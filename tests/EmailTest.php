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
        $this->getExtension($app)->config['csrf'] = false;
        $this->getExtension($app)->config['debug']['enabled'] = false;
        $this->getExtension($app)->config['testing_form']['notification']['enabled'] = true;
        $this->getExtension($app)->config['testing_form']['notification']['debug'] = false;
        $this->getExtension($app)->config['testing_form']['notification']['from_name'] = 'Gawain Lynch';
        $this->getExtension($app)->config['testing_form']['notification']['from_email'] = 'gawain@example.com';
        $this->getExtension($app)->config['testing_form']['notification']['to_name'] = 'Kenny Koala';
        $this->getExtension($app)->config['testing_form']['notification']['to_email'] = 'kenny.koala@example.com';
        $this->getExtension($app)->config['testing_form']['notification']['cc_name'] = 'Bob den Otter';
        $this->getExtension($app)->config['testing_form']['notification']['cc_email'] = 'bob@example.com';
        $this->getExtension($app)->config['testing_form']['notification']['bcc_name'] = 'Lodewijk Evers';
        $this->getExtension($app)->config['testing_form']['notification']['bcc_email'] = 'lodewijk@example.com';
        $this->getExtension($app)->config['testing_form']['notification']['attach_files'] = true;

        // Upload file set up
        $this->getExtension($app)->config['uploads']['enabled'] = true;
        $this->getExtension($app)->config['uploads']['base_directory'] = sys_get_temp_dir();
        $srcFile = EXTENSION_TEST_ROOT . '/tests/data/bolt-logo.png';
        $tmpFile = sys_get_temp_dir() . '/' . uniqid('php_');

        $fs = new Filesystem();
        $fs->copy($srcFile, $tmpFile, true);

        $app['request'] = Request::create('/');

        $boltforms = new BoltForms($app);
        $boltforms->makeForm('testing_form');
        $fields = $this->formConfig();

        $boltforms->addFieldArray('testing_form', $fields);

        $parameters = array(
            'testing_form' => array(
                'name'    => 'Gawain Lynch',
                'email'   => 'gawain.lynch@gmail.com',
                'message' => 'Hello',
                'file'    => new UploadedFile($tmpFile, 'bolt-logo.png', null, null, null, true),
                'date'    => array(
                    'date' => array(
                        'day'   => '23',
                        'month' => '10',
                        'year'  => '2010',
                    ),
                    'time' => array(
                        'hour'   => '18',
                        'minute' => '15',
                    ),
                )
            )
        );

        $app['request'] = Request::create('/', 'POST', $parameters);

        $result = $boltforms->processRequest('testing_form', array('success' => true));

        $this->assertTrue($result);
    }

    public function testSendEmailDebug()
    {
        $app = $this->getApp();
        $this->getExtension($app)->config['csrf'] = false;
        $this->getExtension($app)->config['debug']['enabled'] = true;
        $this->getExtension($app)->config['debug']['address'] = 'noreply@example.com';
        $this->getExtension($app)->config['testing_form']['notification']['enabled'] = true;
        $this->getExtension($app)->config['testing_form']['notification']['from_name'] = 'Gawain Lynch';
        $this->getExtension($app)->config['testing_form']['notification']['from_email'] = 'gawain@example.com';

        $app['request'] = Request::create('/');

        $boltforms = new BoltForms($app);
        $boltforms->makeForm('testing_form');
        $fields = $this->formConfig();

        $boltforms->addFieldArray('testing_form', $fields);

        $parameters = array(
            'testing_form' => array(
                'name'    => 'Gawain Lynch',
                'email'   => 'gawain.lynch@gmail.com',
                'message' => 'Hello',
            )
        );

        $app['request'] = Request::create('/', 'POST', $parameters);

        $result = $boltforms->processRequest('testing_form', array('success' => true));

        $this->assertTrue($result);
    }

    public function testSendEmailDebugFail()
    {
        $app = $this->getApp();
        $this->getExtension($app)->config['csrf'] = false;
        $this->getExtension($app)->config['debug']['enabled'] = true;
        $this->getExtension($app)->config['debug']['address'] = null;
        $this->getExtension($app)->config['testing_form']['notification']['enabled'] = true;

        $app['request'] = Request::create('/');

        $boltforms = new BoltForms($app);
        $boltforms->makeForm('testing_form');
        $fields = $this->formConfig();

        $boltforms->addFieldArray('testing_form', $fields);

        $parameters = array(
            'testing_form' => array(
                'name'    => 'Gawain Lynch',
                'email'   => 'gawain.lynch@gmail.com',
                'message' => 'Hello',
            )
        );

        $app['request'] = Request::create('/', 'POST', $parameters);

        $this->setExpectedException('\Bolt\Extension\Bolt\BoltForms\Exception\EmailException', '[BoltForms] Debug email address can not be empty if debugging enabled!');

        $result = $boltforms->processRequest('testing_form', array('success' => true));

        $this->assertTrue($result);
    }
}
