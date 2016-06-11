<?php
namespace Bolt\Extension\Bolt\BoltForms\Tests;

use Bolt\Extension\Bolt\BoltForms\Choice\ChoiceResolver;

/**
 * Array choices test
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class SymfonyChoiceTypeTest extends AbstractBoltFormsUnitTest
{
    public function testGetName()
    {
        $obj = new ChoiceResolver('koala', ['gum', 'leaves']);
        $this->assertInstanceOf('\Bolt\Extension\Bolt\BoltForms\Choice\ArrayType', $obj);
        $this->assertSame($obj->getName(), 'koala');
    }

    public function testGetChoices()
    {
        $obj = new ChoiceResolver('koala', ['gum', 'leaves']);
        $choices = $obj->getChoices();

        $this->assertContains('gum', $choices);
        $this->assertContains('leaves', $choices);
    }
}
