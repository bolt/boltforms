<?php
namespace Bolt\Extension\Bolt\BoltForms\Tests;

use Bolt\Extension\Bolt\BoltForms\BoltForms;
// use Bolt\Tests\Mocks\DoctrineMockBuilder;
use Bolt\Extension\Bolt\BoltForms\Email;
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
        $this->getExtension($app)->config['testing_form']['notification']['to_name'] = 'Kenny Koala';
        $this->getExtension($app)->config['testing_form']['notification']['to_email'] = 'kenny.koala@example.com';
        $this->getExtension($app)->config['testing_form']['notification']['from_name'] = 'Gawain Lynch';
        $this->getExtension($app)->config['testing_form']['notification']['from_email'] = 'gawain@example.com';
        $this->getExtension($app)->config['testing_form']['notification']['cc_name'] = 'Bob den Otter';
        $this->getExtension($app)->config['testing_form']['notification']['cc_email'] = 'bob@example.com';
        $this->getExtension($app)->config['testing_form']['notification']['bcc_name'] = 'Lodewijk Evers';
        $this->getExtension($app)->config['testing_form']['notification']['bcc_email'] = 'lodewijk@example.com';

        $app['request'] = Request::create('/');

        $boltforms = new BoltForms($app);
        $boltforms->makeForm('testing_form');
        $fields = $this->formValues();

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
}
