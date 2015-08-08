<?php

namespace Bolt\Extension\Bolt\BoltForms\Tests;

use Bolt\Extension\Bolt\BoltForms\BoltForms;
use Bolt\Tests\Mocks\DoctrineMockBuilder;
use Symfony\Component\HttpFoundation\Request;

class BoltFormsCustomDataSubscriberTest extends AbstractBoltFormsUnitTest
{
    public function testNextIncrement()
    {
        $nextIncField = array(
            'type'    => 'hidden',
            'options' => array('label' => false),
            'event'   => array(
                'name'   => 'next_increment',
                'params' => array(
                    'table'  => 'koalas',
                    'column' => 'gum_leaves',
                    'min'    => 42
                )
            ),
        );

        $app = $this->getApp(false);
        $this->getExtension($app)->config['csrf'] = false;
        $this->getExtension($app)->config['contact']['fields']['next_inc'] = $nextIncField;

        $app['request'] = Request::create('/');
        $boltforms = new BoltForms($app);
        $boltforms->makeForm('contact');

        $fields = $this->formValues();
        $fields['next_inc'] = $nextIncField;

        $boltforms->addFieldArray('contact', $fields);

        $parameters = array(
            'contact' => array(
                'name'    => 'Gawain Lynch',
                'email'   => 'gawain.lynch@gmail.com',
                'message' => 'Hello'
            )
        );

        $app['request'] = Request::create('/', 'POST', $parameters);
        $app->boot();

        // Mock the database query
        $mocker = new DoctrineMockBuilder();
        $db = $mocker->getConnectionMock();
        $queries = array();
        $db->expects($this->any())
            ->method('executeQuery')
            ->will($this->returnCallback(
                function ($query, $params) use (&$queries, $mocker) {
                    $queries[] = $query;

                    return $mocker->getStatementMock();
                }
        ));
        $db->expects($this->any())
            ->method('fetchColumn')
            ->with($this->equalTo('koalas'))
            ->willReturn(55);
        $app['db'] = $db;

        // Mock Bolt\Users
        $users = $this->getMock('\Bolt\Users', array('getUsers'), array($app));
        $users->expects($this->any())
            ->method('getUsers')
            ->willReturn(array('id' => 1));
        $app['users'] = $users;

        $result = $boltforms->processRequest('contact', array('success' => true), true);

        $this->assertEquals('SELECT MAX(gum_leaves) as max FROM koalas', $queries[0]);
        $this->assertArrayHasKey('next_inc', $result);
        $this->assertSame(42, $result['next_inc']);
    }

    public function testRandomString()
    {
        $randomStringField = array(
            'type'    => 'hidden',
            'options' => array('label' => false),
            'event'   => array(
                'name'   => 'random_string',
                'params' => array(
                    'length' => 22
                )
            ),
        );

        $app = $this->getApp(false);
        $this->getExtension($app)->config['csrf'] = false;
        $this->getExtension($app)->config['contact']['fields']['random_str'] = $randomStringField;

        $app['request'] = Request::create('/');
        $boltforms = new BoltForms($app);
        $boltforms->makeForm('contact');

        $fields = $this->formValues();
        $fields['random_str'] = $randomStringField;

        $boltforms->addFieldArray('contact', $fields);

        $parameters = array(
            'contact' => array(
                'name'    => 'Gawain Lynch',
                'email'   => 'gawain.lynch@gmail.com',
                'message' => 'Hello'
            )
        );
        $app['request'] = Request::create('/', 'POST', $parameters);
        $app->boot();

        $result = $boltforms->processRequest('contact', array('success' => true), true);

        $this->assertArrayHasKey('random_str', $result);
        $this->assertSame(22, strlen($result['random_str']));
    }

    public function testServerValue()
    {
        $serverValueField = array(
            'type'    => 'hidden',
            'options' => array('label' => false),
            'event'   => array(
                'name'   => 'server_value',
                'params' => array(
                    'key' => 'SCRIPT_NAME'
                )
            ),
        );

        $app = $this->getApp(false);
        $this->getExtension($app)->config['csrf'] = false;
        $this->getExtension($app)->config['contact']['fields']['server_val'] = $serverValueField;

        $app['request'] = Request::create('/');
        $boltforms = new BoltForms($app);
        $boltforms->makeForm('contact');

        $fields = $this->formValues();
        $fields['server_val'] = $serverValueField;

        $boltforms->addFieldArray('contact', $fields);

        $parameters = array(
            'contact' => array(
                'name'    => 'Gawain Lynch',
                'email'   => 'gawain.lynch@gmail.com',
                'message' => 'Hello'
            )
        );

        $app['request'] = Request::create('/', 'POST', $parameters, array(), array(), array('SCRIPT_NAME' => $_SERVER['SCRIPT_NAME']));
        $app->boot();

        $result = $boltforms->processRequest('contact', array('success' => true), true);

        $this->assertArrayHasKey('server_val', $result);
        $this->assertSame($_SERVER['SCRIPT_NAME'], $result['server_val']);
    }

    public function testSessionValue()
    {
        $sessionValueField = array(
            'type'    => 'hidden',
            'options' => array('label' => false),
            'event'   => array(
                'name'   => 'session_value',
                'params' => array(
                    'key' => 'koala'
                )
            ),
        );

        $app = $this->getApp(false);
        $this->getExtension($app)->config['csrf'] = false;
        $this->getExtension($app)->config['contact']['fields']['session_value'] = $sessionValueField;

        $app['request'] = Request::create('/');
        $boltforms = new BoltForms($app);
        $boltforms->makeForm('contact');

        $fields = $this->formValues();
        $fields['session_value'] = $sessionValueField;

        $boltforms->addFieldArray('contact', $fields);

        $parameters = array(
            'contact' => array(
                'name'    => 'Gawain Lynch',
                'email'   => 'gawain.lynch@gmail.com',
                'message' => 'Hello'
            )
        );

        $app['request'] = Request::create('/', 'POST', $parameters);
        $app->boot();
        $app['session']->set('koala', 'gum-leaves');

        $result = $boltforms->processRequest('contact', array('success' => true), true);

        $this->assertArrayHasKey('session_value', $result);
        $this->assertSame('gum-leaves', $result['session_value']);
    }
}
