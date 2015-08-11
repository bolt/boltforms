<?php
namespace Bolt\Extension\Bolt\BoltForms\Tests;

use Bolt\Extension\Bolt\BoltForms\Config\FormConfig;
use Bolt\Extension\Bolt\BoltForms\FormData;
use Bolt\Extension\Bolt\BoltForms\Submission\RedirectHandler;

/**
 * RedirectHandler class tests.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class RedirectHandlerTest extends AbstractBoltFormsUnitTest
{
    public function testConstructor()
    {
        $app = $this->getApp();
        $handler = new RedirectHandler($app['url_matcher']);

        $this->assertInstanceOf('\Bolt\Extension\Bolt\BoltForms\Submission\RedirectHandler', $handler);
    }

    public function testMatchNotFound()
    {
        $app = $this->getApp();
        $handler = new RedirectHandler($app['url_matcher']);

        $config = $this->formFieldConfig();
        $config['redirect']['target'] = '/koala/tree';

        $formConfig = new FormConfig('testing_form', $config);
        $formData = new FormData($this->formData());

        $handler->redirect($formConfig, $formData);
        $this->assertFalse($handler->isValid());
    }
}
