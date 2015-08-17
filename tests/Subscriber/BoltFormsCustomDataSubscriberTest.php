<?php

namespace Bolt\Extension\Bolt\BoltForms\Tests;

use Bolt\Extension\Bolt\BoltForms\BoltForms;
use Bolt\Extension\Bolt\BoltForms\Subscriber\BoltFormsCustomDataSubscriber;
use Bolt\Tests\Mocks\DoctrineMockBuilder;
use Symfony\Component\HttpFoundation\Request;

class BoltFormsCustomDataSubscriberTest extends AbstractBoltFormsUnitTest
{
    public function testNextIncrementInvalidParameters()
    {
        $app = $this->getApp();

        $event = $this->getMock('\Bolt\Extension\Bolt\BoltForms\Event\BoltFormsCustomDataEvent',
            array('setData'),
            array('testevent', array()));
        $event->expects($this->never())
            ->method('setData');

        $sub = new BoltFormsCustomDataSubscriber($app);

        $sub->nextIncrement($event);
    }

    public function testNextIncrementInvalidEmptyTableName()
    {
        $app = $this->getApp();

        $event = $this->getMock('\Bolt\Extension\Bolt\BoltForms\Event\BoltFormsCustomDataEvent',
            array('setData'),
            array('testevent', array('table' => '')));
        $event->expects($this->never())
            ->method('setData');

        $logger = $this->getMock('\Monolog\Logger', array('error'), array('testlogger'));
        $logger->expects($this->atLeastOnce())
            ->method('error');
        $app['logger.system'] = $logger;

        $sub = new BoltFormsCustomDataSubscriber($app);

        $sub->nextIncrement($event);
    }

    public function testNextIncrementInvalidQuery()
    {
        $app = $this->getApp();

        $event = $this->getMock('\Bolt\Extension\Bolt\BoltForms\Event\BoltFormsCustomDataEvent',
            array('setData'),
            array('testevent', array('table' => 'koala')));
        $event->expects($this->never())
            ->method('setData');

        $logger = $this->getMock('\Monolog\Logger', array('error'), array('testlogger'));
        $logger->expects($this->atLeastOnce())
            ->method('error');
        $app['logger.system'] = $logger;

        $sub = new BoltFormsCustomDataSubscriber($app);

        $sub->nextIncrement($event);
    }

    public function testNextIncrementTable()
    {
        $nextIncField = array(
            'type'    => 'hidden',
            'options' => array('label' => false),
            'event'   => array(
                'name'   => 'next_increment',
                'params' => array(
                    'table'  => 'koalas',
                    'column' => 'gum_leaves'
                )
            ),
        );

        $app = $this->getApp(false);
        $this->getExtension()->config['csrf'] = false;
        $this->getExtension()->config['testing_form']['fields']['next_inc'] = $nextIncField;

        $app['request'] = Request::create('/');

        $app['boltforms']->makeForm('testing_form');
        $fields = $this->formFieldConfig();
        $fields['next_inc'] = $nextIncField;
        $app['boltforms']->addFieldArray('testing_form', $fields);

        $parameters = $this->formData();
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

                    return $mocker->getStatementMock(22);
                }
        ));

        $app['db'] = $db;

        // Mock Bolt\Users
        $users = $this->getMock('\Bolt\Users', array('getUsers'), array($app));
        $users->expects($this->any())
            ->method('getUsers')
            ->willReturn(array('id' => 1));
        $app['users'] = $users;

        $result = $this->processor()->process('testing_form', $this->getExtension()->config['testing_form'], array('success' => true), true);

        $this->assertEquals('SELECT MAX(gum_leaves) as max FROM koalas', $queries[0]);
        $this->assertArrayHasKey('next_inc', $result);
        $this->assertSame(23, $result->get('next_inc'));
    }

    public function testNextIncrementTableMin()
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
        $this->getExtension()->config['csrf'] = false;
        $this->getExtension()->config['testing_form']['fields']['next_inc'] = $nextIncField;

        $app['request'] = Request::create('/');

        $app['boltforms']->makeForm('testing_form');
        $fields = $this->formFieldConfig();
        $fields['next_inc'] = $nextIncField;
        $app['boltforms']->addFieldArray('testing_form', $fields);

        $parameters = $this->formData();
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

                    return $mocker->getStatementMock(42);
                }
        ));

        $app['db'] = $db;

        // Mock Bolt\Users
        $users = $this->getMock('\Bolt\Users', array('getUsers'), array($app));
        $users->expects($this->any())
            ->method('getUsers')
            ->willReturn(array('id' => 1));
        $app['users'] = $users;

        $result = $this->processor()->process('testing_form', $this->getExtension()->config['testing_form'], array('success' => true), true);

        $this->assertEquals('SELECT MAX(gum_leaves) as max FROM koalas', $queries[0]);
        $this->assertArrayHasKey('next_inc', $result);
        $this->assertSame(43, $result->get('next_inc'));
    }

    public function testNextIncrementContentType()
    {
        $nextIncField = array(
            'type'    => 'hidden',
            'options' => array('label' => false),
            'event'   => array(
                'name'   => 'next_increment',
                'params' => array(
                    'contenttype' => 'koalas',
                    'column'      => 'gum_leaves',
                    'min'         => 42
                )
            ),
        );

        $app = $this->getApp(false);
        $this->getExtension()->config['csrf'] = false;
        $this->getExtension()->config['testing_form']['fields']['next_inc'] = $nextIncField;

        $app['request'] = Request::create('/');

        $app['boltforms']->makeForm('testing_form');
        $fields = $this->formFieldConfig();
        $fields['next_inc'] = $nextIncField;
        $app['boltforms']->addFieldArray('testing_form', $fields);

        $parameters = $this->formData();
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

        $result = $this->processor()->process('testing_form', $this->getExtension()->config['testing_form'], array('success' => true), true);

        $this->assertEquals('SELECT MAX(gum_leaves) as max FROM bolt_koalas', $queries[0]);
        $this->assertArrayHasKey('next_inc', $result);
        $this->assertSame(42, $result->get('next_inc'));
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
        $this->getExtension()->config['csrf'] = false;
        $this->getExtension()->config['testing_form']['fields']['random_str'] = $randomStringField;

        $app['request'] = Request::create('/');

        $app['boltforms']->makeForm('testing_form');
        $fields = $this->formFieldConfig();
        $fields['random_str'] = $randomStringField;
        $app['boltforms']->addFieldArray('testing_form', $fields);

        $parameters = $this->formData();
        $app['request'] = Request::create('/', 'POST', $parameters);
        $app->boot();

        $result = $this->processor()->process('testing_form', $this->getExtension()->config['testing_form'], array('success' => true), true);

        $this->assertArrayHasKey('random_str', $result);
        $this->assertSame(22, strlen($result->get('random_str')));
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
        $this->getExtension()->config['csrf'] = false;
        $this->getExtension()->config['testing_form']['fields']['server_val'] = $serverValueField;

        $app['request'] = Request::create('/');

        $app['boltforms']->makeForm('testing_form');
        $fields = $this->formFieldConfig();
        $fields['server_val'] = $serverValueField;
        $app['boltforms']->addFieldArray('testing_form', $fields);

        $parameters = $this->formData();
        $app['request'] = Request::create('/', 'POST', $parameters, array(), array(), array('SCRIPT_NAME' => $_SERVER['SCRIPT_NAME']));
        $app->boot();

        $result = $this->processor()->process('testing_form', $this->getExtension()->config['testing_form'], array('success' => true), true);

        $this->assertArrayHasKey('server_val', $result);
        $this->assertSame($_SERVER['SCRIPT_NAME'], $result->get('server_val'));
    }

    public function testServerValueInvalid()
    {
        $serverValueField = array(
            'type'    => 'hidden',
            'options' => array('label' => false),
            'event'   => array(
                'name'   => 'server_value'
            ),
        );

        $app = $this->getApp(false);
        $this->getExtension()->config['csrf'] = false;
        $this->getExtension()->config['testing_form']['fields']['server_val'] = $serverValueField;

        $app['request'] = Request::create('/');

        $app['boltforms']->makeForm('testing_form');
        $fields = $this->formFieldConfig();
        $fields['server_val'] = $serverValueField;
        $app['boltforms']->addFieldArray('testing_form', $fields);

        $parameters = $this->formData();
        $app['request'] = Request::create('/', 'POST', $parameters, array(), array(), array('SCRIPT_NAME' => $_SERVER['SCRIPT_NAME']));
        $app->boot();

        $result = $this->processor()->process('testing_form', $this->getExtension()->config['testing_form'], array('success' => true), true);

        $this->assertArrayHasKey('server_val', $result->getPostData());
        $this->assertNull($result->get('server_val'));
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
        $this->getExtension()->config['csrf'] = false;
        $this->getExtension()->config['testing_form']['fields']['session_value'] = $sessionValueField;

        $app['request'] = Request::create('/');

        $app['boltforms']->makeForm('testing_form');
        $fields = $this->formFieldConfig();
        $fields['session_value'] = $sessionValueField;
        $app['boltforms']->addFieldArray('testing_form', $fields);

        $parameters = $this->formData();
        $app['request'] = Request::create('/', 'POST', $parameters);
        $app->boot();
        $app['session']->set('koala', 'gum-leaves');

        $result = $this->processor()->process('testing_form', $this->getExtension()->config['testing_form'], array('success' => true), true);

        $this->assertArrayHasKey('session_value', $result);
        $this->assertSame('gum-leaves', $result->get('session_value'));
    }

    public function testSessionValueInvalid()
    {
        $sessionValueField = array(
            'type'    => 'hidden',
            'options' => array('label' => false),
            'event'   => array(
                'name'   => 'session_value'
            ),
        );

        $app = $this->getApp(false);
        $this->getExtension()->config['csrf'] = false;
        $this->getExtension()->config['testing_form']['fields']['session_value'] = $sessionValueField;

        $app['request'] = Request::create('/');

        $app['boltforms']->makeForm('testing_form');
        $fields = $this->formFieldConfig();
        $fields['session_value'] = $sessionValueField;
        $app['boltforms']->addFieldArray('testing_form', $fields);

        $parameters = $this->formData();
        $app['request'] = Request::create('/', 'POST', $parameters);
        $app->boot();
        $app['session']->set('koala', 'gum-leaves');

        $result = $this->processor()->process('testing_form', $this->getExtension()->config['testing_form'], array('success' => true), true);

        $this->assertArrayHasKey('session_value', $result->getPostData());
        $this->assertNull($result->get('session_value'));
    }
}
