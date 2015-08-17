<?php

namespace Bolt\Extension\Bolt\BoltForms\Tests;

use Bolt\Extension\Bolt\BoltForms\Controller\UploadManagement;
use Symfony\Component\HttpFoundation\Request;

class UploadManagementTest extends AbstractBoltFormsUnitTest
{
    public function testConnect()
    {
        $app = $this->getApp(false);
        $controller = new UploadManagement();
        $ctr = $controller->connect($app);

        $this->assertInstanceOf('\Silex\ControllerCollection', $ctr);
    }

    public function testDownloadRoute()
    {
        $app = $this->getApp(false);
        $this->getExtension()->config['uploads']['base_directory'] = __DIR__;
        $controller = new UploadManagement();
        $controller->connect($app);
        $request = Request::create('/download', 'GET', array('file' => basename(__FILE__)));

        $route = $controller->download($app, $request);

        $this->assertSame(200, $route->getStatusCode());
        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\BinaryFileResponse', $route);
    }

    public function testDownloadRoute404()
    {
        $app = $this->getApp(false);
        $this->getExtension()->config['uploads']['base_directory'] = '/koala';
        $controller = new UploadManagement();
        $controller->connect($app);
        $request = Request::createFromGlobals();

        $route = $controller->download($app, $request);

        $this->assertSame(404, $route->getStatusCode());
        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\Response', $route);
    }
}
