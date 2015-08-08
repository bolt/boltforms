<?php
namespace Bolt\Extension\Bolt\BoltForms\Tests;

use Bolt\Extension\Bolt\BoltForms\Event\BoltFormsEvents;

/**
 * BoltForms class tests.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class BoltFormsEventsTest extends AbstractBoltFormsUnitTest
{
    /**
     * @covers \Bolt\Extension\Bolt\BoltForms\Event\BoltFormsEvents::<private>
     */
    public function testNotInstantiable()
    {
        $reflection = new \ReflectionClass('\Bolt\Extension\Bolt\BoltForms\Event\BoltFormsEvents');
        $this->assertFalse($reflection->isInstantiable());
    }

    public function testEventsConstants()
    {
        $this->assertSame('boltforms.pre_bind', BoltFormsEvents::PRE_SUBMIT);
        $this->assertSame('boltforms.bind', BoltFormsEvents::SUBMIT);
        $this->assertSame('boltforms.post_bind', BoltFormsEvents::POST_SUBMIT);
        $this->assertSame('boltforms.pre_set_data', BoltFormsEvents::PRE_SET_DATA);
        $this->assertSame('boltforms.post_set_data', BoltFormsEvents::POST_SET_DATA);
        $this->assertSame('boltforms.next_increment', BoltFormsEvents::DATA_NEXT_INCREMENT);
        $this->assertSame('boltforms.random_string', BoltFormsEvents::DATA_RANDOM_STRING);
        $this->assertSame('boltforms.server_value', BoltFormsEvents::DATA_SERVER_VALUE);
        $this->assertSame('boltforms.session_value', BoltFormsEvents::DATA_SESSION_VALUE);
    }
}
