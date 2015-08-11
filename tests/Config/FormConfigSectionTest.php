<?php
namespace Bolt\Extension\Bolt\BoltForms\Tests;

use Bolt\Extension\Bolt\BoltForms\Config\FormConfigSection;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

/**
 * FormConfigSection class tests.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class FormConfigSectionTest extends AbstractBoltFormsUnitTest
{
    public function testConstructor()
    {
        $app = $this->getApp();
        $section = new FormConfigSection(array('koala' => 'gum-leaves'));

        $this->assertInstanceOf('\Bolt\Extension\Bolt\BoltForms\Config\FormConfigSection', $section);

        $this->assertTrue(isset($section->koala));
        unset($section->koala);
        $this->assertFalse(isset($section->koala));
    }
}
