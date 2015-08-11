<?php
namespace Bolt\Extension\Bolt\BoltForms\Tests;

use Bolt\Extension\Bolt\BoltForms\BoltForms;
use Bolt\Extension\Bolt\BoltForms\FormData;

/**
 * BoltForms\FormData class tests.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class FormDataTest extends AbstractBoltFormsUnitTest
{
    public function testArrayAccess()
    {
        $postData = $this->formData();
        $formData = new FormData($postData['testing_form']);

        $this->assertNull($formData->offsetSet('test', true));
        $this->assertTrue($formData->offsetExists('name'));
        $this->assertSame('Gawain Lynch', $formData->offsetGet('name'));
        $this->assertNull($formData->offsetUnset('test'));
    }
}
