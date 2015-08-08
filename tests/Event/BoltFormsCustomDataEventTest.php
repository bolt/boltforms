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

        $this->assertSame('koala', $event->getName());
        $this->assertSame(array('leaves'), $event->getParameters());
        $this->assertSame('gum-leaves', $event->getData());
    }
}
