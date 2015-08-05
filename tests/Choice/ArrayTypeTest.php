<?php
namespace Bolt\Extension\Bolt\BoltForms\Tests;

use Bolt\Tests\BoltUnitTest;
use Bolt\Extension\Bolt\BoltForms\Extension;
use Bolt\Extension\Bolt\BoltForms\Choice\ArrayType;

/**
 * Array choices test
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class ArrayTypeTest extends AbstractBoltFormsUnitTest
{
    public function testGetName()
    {
        $obj = new ArrayType('koala', ['gum', 'leaves']);
        $this->assertInstanceOf('\Bolt\Extension\Bolt\BoltForms\Choice\ArrayType', $obj);
        $this->assertSame($obj->getName(), 'koala');
    }

    public function testGetChoices()
    {
        $obj = new ArrayType('koala', ['gum', 'leaves']);
        $choices = $obj->getChoices();

        $this->assertContains('gum', $choices);
        $this->assertContains('leaves', $choices);
    }
}
