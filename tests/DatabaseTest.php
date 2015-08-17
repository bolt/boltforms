<?php
namespace Bolt\Extension\Bolt\BoltForms\Tests;

use Bolt\Extension\Bolt\BoltForms\BoltForms;
use Bolt\Extension\Bolt\BoltForms\Database;
// use Bolt\Tests\Mocks\DoctrineMockBuilder;
use Bolt\Extension\Bolt\BoltForms\FileUpload;
use Bolt\Extension\Bolt\BoltForms\FormData;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

/**
 * BoltForms\Database class tests.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class DatabaseTest extends AbstractBoltFormsUnitTest
{
    public function testConstructor()
    {
        $app = $this->getApp();
        $boltforms = new Database($app);

        $this->assertInstanceOf('\Bolt\Extension\Bolt\BoltForms\Database', $boltforms);
    }

    public function testWriteToTable()
    {
        $app = $this->getApp();
        $this->getExtension()->config['csrf'] = false;
        $this->getExtension()->config['uploads']['enabled'] = true;
        $this->getExtension()->config['uploads']['base_directory'] = __DIR__;
        $this->getExtension()->config['testing_form']['database']['table'] = 'koala';

        $app['request'] = Request::create('/');

        $app['boltforms']->makeForm('testing_form');
        $fields = $this->formFieldConfig();
        $app['boltforms']->addFieldArray('testing_form', $fields);

        $parameters = $this->getParameters($app);

        // Mock the database query
        $mocker = new Mock\DoctrineMockBuilder();
        $db = $mocker->getConnectionMock();
        $sm = $mocker->getSchemaManagerMock($db, true, array_keys($parameters['testing_form']));
        $db->expects($this->any())
            ->method('getSchemaManager')
            ->will($this->returnValue($sm));
        $db->expects($this->any())
            ->method('insert')
            ->will($this->returnValue(true));

        $app['db'] = $db;

        // Mock Bolt\Users
        $users = $this->getMock('\Bolt\Users', array('getUsers'), array($app));
        $users->expects($this->any())
            ->method('getUsers')
            ->willReturn(array('id' => 1));
        $app['users'] = $users;

        $app['request'] = Request::create('/', 'POST', $parameters);

        $result = $this->processor()->process('testing_form', $this->getExtension()->config['testing_form'], array('success' => true));

        $this->assertTrue($result);
    }

    public function testWriteToTableInvalid()
    {
        $app = $this->getApp();
        $logger = $this->getMock('\Monolog\Logger', array('error'), array('testlogger'));
        $logger->expects($this->atLeastOnce())
            ->method('error');
        $app['logger.system'] = $logger;
        $formData = new FormData(array());

        $retval = $app['boltforms.database']->writeToTable('nothere', $formData);
        $this->assertFalse($retval);
    }

    public function testWriteToTableException()
    {
        $app = $this->getApp();
        $this->getExtension()->config['csrf'] = false;
        $this->getExtension()->config['testing_form']['database']['table'] = 'koala';

        $app['request'] = Request::create('/');

        $app['boltforms']->makeForm('testing_form');
        $fields = $this->formFieldConfig();
        $app['boltforms']->addFieldArray('testing_form', $fields);

        $parameters = $this->getParameters($app);

        // Mock the database query
        $e = new \Doctrine\DBAL\DBALException();
        $mocker = new Mock\DoctrineMockBuilder();
        $db = $mocker->getConnectionMock();
        $sm = $mocker->getSchemaManagerMock($db, true, array_keys($parameters['testing_form']));
        $db->expects($this->any())
            ->method('getSchemaManager')
            ->will($this->returnValue($sm));
        $db->expects($this->any())
            ->method('insert')
            ->will($this->throwException($e));

        $app['db'] = $db;

        // Keep an eye on the logger
        $logger = $this->getMock('\Monolog\Logger', array('critical', 'debug'), array('testlogger'));
        $logger->expects($this->atLeastOnce())
            ->method('critical');
        $app['logger.system'] = $logger;

        // Mock Bolt\Users
        $users = $this->getMock('\Bolt\Users', array('getUsers'), array($app));
        $users->expects($this->any())
            ->method('getUsers')
            ->willReturn(array('id' => 1));
        $app['users'] = $users;

        $app['request'] = Request::create('/', 'POST', $parameters);

        $result = $this->processor()->process('testing_form', $this->getExtension()->config['testing_form'], array('success' => true));

        $this->assertTrue($result);
    }

    public function testWriteGetData()
    {
        $app = $this->getApp();
        $parameters = $this->getParameters($app);

        // Mock the database query
        $mocker = new Mock\DoctrineMockBuilder();
        $db = $mocker->getConnectionMock();
        $sm = $mocker->getSchemaManagerMock($db, true, array_keys($parameters['testing_form']));
        $db->expects($this->any())
            ->method('getSchemaManager')
            ->will($this->returnValue($sm));
        $db->expects($this->any())
            ->method('insert')
            ->will($this->returnValue(true));

        $app['db'] = $db;

        $parameters['testing_form']['message'] = $parameters['testing_form']['date'];
        $parameters['testing_form']['date'] = new \DateTime();
        $parameters['testing_form']['file'] = new FileUpload($app, 'testing_form', $parameters['testing_form']['file']);
        $formData = new FormData($parameters['testing_form']);

        $retval = $app['boltforms.database']->writeToTable('koalas', $formData);
        $this->assertNull($retval);
    }

    public function testWriteToContentype()
    {
        $app = $this->getApp();
        $this->getExtension()->config['csrf'] = false;
        $this->getExtension()->config['uploads']['enabled'] = true;
        $this->getExtension()->config['uploads']['base_directory'] = __DIR__;
        $this->getExtension()->config['testing_form']['database']['contenttype'] = 'koala';

        $app['request'] = Request::create('/');

        $app['boltforms']->makeForm('testing_form');
        $fields = $this->formFieldConfig();
        $app['boltforms']->addFieldArray('testing_form', $fields);

        $parameters = $this->getParameters($app);

        // Mock Bolt\Users
        $storage = $this->getMock('\Bolt\Storage', array('saveContent'), array($app));
        $storage->expects($this->any())
            ->method('saveContent')
            ->willReturn(42);
        $app['storage'] = $storage;

        $app['request'] = Request::create('/', 'POST', $parameters);

        $result = $this->processor()->process('testing_form', $this->getExtension()->config['testing_form'], array('success' => true));

        $this->assertTrue($result);
    }

    public function testWriteToContentypeException()
    {
        $app = $this->getApp();
        $this->getExtension()->config['csrf'] = false;
        $this->getExtension()->config['testing_form']['database']['contenttype'] = 'koala';

        $app['request'] = Request::create('/');

        $app['boltforms']->makeForm('testing_form');
        $fields = $this->formFieldConfig();
        $app['boltforms']->addFieldArray('testing_form', $fields);

        $parameters = $this->getParameters($app);

        // Mock Bolt\Storage
        $e = new \Exception();
        $storage = $this->getMock('\Bolt\Storage', array('saveContent'), array($app));
        $storage->expects($this->any())
            ->method('saveContent')
            ->will($this->throwException($e));
        $app['storage'] = $storage;

        // Keep an eye on the logger
        $logger = $this->getMock('\Monolog\Logger', array('critical', 'debug'), array('testlogger'));
        $logger->expects($this->atLeastOnce())
            ->method('critical');
        $app['logger.system'] = $logger;

        $app['request'] = Request::create('/', 'POST', $parameters);

        $result = $this->processor()->process('testing_form', $this->getExtension()->config['testing_form'], array('success' => true));

        $this->assertTrue($result);
    }

    protected function getParameters($app)
    {
        // Upload file set up
        $this->getExtension()->config['uploads']['enabled'] = true;
        $this->getExtension()->config['uploads']['base_directory'] = sys_get_temp_dir();
        $srcFile = EXTENSION_TEST_ROOT . '/tests/data/bolt-logo.png';
        $tmpFile = sys_get_temp_dir() . '/' . uniqid('php_');

        $fs = new Filesystem();
        $fs->copy($srcFile, $tmpFile, true);

        $parameters = $this->formData();
        $parameters['testing_form']['file'] = new UploadedFile($tmpFile, 'bolt-logo.png', null, null, null, true);

        return $parameters;
    }
}
