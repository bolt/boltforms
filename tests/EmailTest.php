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
        $this->getExtension($app)->config['contact']['notification']['enabled'] = true;
        $this->getExtension($app)->config['contact']['notification']['debug'] = false;
        $this->getExtension($app)->config['contact']['notification']['from_name'] = 'Gawain Lynch';
        $this->getExtension($app)->config['contact']['notification']['from_email'] = 'gawain@example.com';
        $this->getExtension($app)->config['contact']['notification']['cc_name'] = 'Bob den Otter';
        $this->getExtension($app)->config['contact']['notification']['cc_email'] = 'bob@example.com';
        $this->getExtension($app)->config['contact']['notification']['bcc_name'] = 'Lodewijk Evers';
        $this->getExtension($app)->config['contact']['notification']['bcc_email'] = 'lodewijk@example.com';

        $app['request'] = Request::create('/');

        $boltforms = new BoltForms($app);
        $boltforms->makeForm('contact');
        $fields = $this->formValues();

        $boltforms->addFieldArray('contact', $fields);

        $parameters = array(
            'contact' => array(
                'name'    => 'Gawain Lynch',
                'email'   => 'gawain.lynch@gmail.com',
                'message' => 'Hello',
            )
        );

        $app['request'] = Request::create('/', 'POST', $parameters);

        $result = $boltforms->processRequest('contact', array('success' => true));

        $this->assertTrue($result);
    }
}
