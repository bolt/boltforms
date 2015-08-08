<?php
namespace Bolt\Extension\Bolt\BoltForms\Tests;

use Bolt\Extension\Bolt\BoltForms\BoltForms;
use Bolt\Extension\Bolt\BoltForms\Database;
// use Bolt\Tests\Mocks\DoctrineMockBuilder;
use Bolt\Extension\Bolt\BoltForms\FileUpload;
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
        $this->getExtension($app)->config['csrf'] = false;
        $this->getExtension($app)->config['uploads']['enabled'] = true;
        $this->getExtension($app)->config['uploads']['base_directory'] = __DIR__;
        $this->getExtension($app)->config['contact']['database']['table'] = 'koala';

        $app['request'] = Request::create('/');

        $boltforms = new BoltForms($app);
        $boltforms->makeForm('contact');
        $fields = $this->formValues();
//         $fields['date'] = array('type' => 'datetime');
//         $fields['json'] = array('type' => 'text');
        $fields['file'] = array('type' => 'file');

        $boltforms->addFieldArray('contact', $fields);

        $parameters = $this->getParameters($app);

        // Mock the database query
        $mocker = new Mock\DoctrineMockBuilder();
        $db = $mocker->getConnectionMock();
        $sm = $mocker->getSchemaManagerMock($db, true, array_keys($parameters['contact']));
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

        $result = $boltforms->processRequest('contact', array('success' => true));

        $this->assertTrue($result);
    }

    public function testWriteToContentype()
    {
        $app = $this->getApp();
        $this->getExtension($app)->config['csrf'] = false;
        $this->getExtension($app)->config['uploads']['enabled'] = true;
        $this->getExtension($app)->config['uploads']['base_directory'] = __DIR__;
        $this->getExtension($app)->config['contact']['database']['contenttype'] = 'koala';

        $app['request'] = Request::create('/');
        $boltforms = new BoltForms($app);
        $boltforms->makeForm('contact');
        $fields = $this->formValues();
        $boltforms->addFieldArray('contact', $fields);

        $parameters = $this->getParameters($app);

        // Mock Bolt\Users
        $storage = $this->getMock('\Bolt\Storage', array('saveContent'), array($app));
        $storage->expects($this->any())
            ->method('saveContent')
            ->willReturn(42);
        $app['storage'] = $storage;

        $app['request'] = Request::create('/', 'POST', $parameters);

        $result = $boltforms->processRequest('contact', array('success' => true));

        $this->assertTrue($result);
    }

    protected function getParameters($app)
    {
        return array(
            'contact' => array(
                'name'    => 'Gawain Lynch',
                'email'   => 'gawain.lynch@gmail.com',
                'message' => 'Hello',
//                 'date'    => new \DateTime('now'),
//                 'json'    => array('koala', 'leaves'),
//                 'file'    => new FileUpload($app, 'contact', new UploadedFile(__FILE__, __FILE__, null, null, null, true))
            )
        );
    }
}
