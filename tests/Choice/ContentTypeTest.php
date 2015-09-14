<?php
namespace Bolt\Extension\Bolt\BoltForms\Tests;

use Bolt\Extension\Bolt\BoltForms\Choice\ContentType;
use Symfony\Component\HttpFoundation\Request;

/**
 * ContentType choices test
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class ContentTypeTest extends AbstractBoltFormsUnitTest
{
    public function testGetName()
    {
        $app = $this->getApp();
        $obj = new ContentType($app, 'koala', array('choices' => 'contenttype::pets::title::slug'));
        $this->assertInstanceOf('\Bolt\Extension\Bolt\BoltForms\Choice\ContentType', $obj);
        $this->assertSame($obj->getName(), 'koala');
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testGetChoicesException()
    {
        $app = $this->getApp();
        $obj = new ContentType($app['storage'], 'koala', array('choices' => 'contenttype'));
        $obj->getChoices();
        $obj = new ContentType($app['storage'], 'koala', array('choices' => 'contenttype::pages'));
        $obj->getChoices();
        $obj = new ContentType($app['storage'], 'koala', array('choices' => 'contenttype::pages::title'));
        $obj->getChoices();
    }

    public function testGetChoices()
    {
        $app = $this->getApp();
        $app['request'] = Request::create('/');

        $record = $app['storage']->getEmptyContent('pages');
        $record->setValues(array('title' => 'Koala', 'slug' => 'gum-tree'));
        $storage = $this->getMock('\Bolt\Storage', array('getContent'), array($app));
        $storage
            ->expects($this->any())
            ->method('getContent')
            ->will($this->returnValue(array($record)));
        $app['storage'] = $storage;

        $obj = new ContentType($app['storage'], 'koala', array('choices' => 'contenttype::pets::title::slug'));
        $choice = $obj->getChoices();

        $this->assertNotEmpty($choice);
        $this->assertArrayHasKey('gum-tree', $choice);
        $this->assertSame('Koala', $choice['gum-tree']);
    }
}
