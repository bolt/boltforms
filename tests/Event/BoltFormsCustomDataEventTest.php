<?php
namespace Bolt\Extension\Bolt\BoltForms\Tests;

use Bolt\Extension\Bolt\BoltForms\Event\BoltFormsCustomDataEvent;

/**
 * BoltFormsCustomDataEvent class tests.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class BoltFormsCustomDataEventTest extends AbstractBoltFormsUnitTest
{
    public function testConstructor()
    {
        $event = new BoltFormsCustomDataEvent('koala', array('leaves'));
        $event->setData('gum-leaves');

        $this->assertSame('koala', $event->eventName());
        $this->assertSame(array('leaves'), $event->eventParams());
        $this->assertSame('gum-leaves', $event->getData());
    }
}
