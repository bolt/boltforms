<?php

namespace Bolt\Extension\Bolt\BoltForms\Tests;

use Bolt\Tests\BoltUnitTest;
use Bolt\Extension\Bolt\BoltForms\Extension;

/**
 * Base class for BoltForms testing.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
abstract class AbstractBoltFormsUnitTest extends BoltUnitTest
{
    public function getApp()
    {
        $app = parent::getApp();
        $extension = new Extension($app);
        $app['extensions']->register($extension);

        return $app;
    }

    public function getExtension($app = null)
    {
        if ($app === null) {
            $app = $this->getApp();
        }

        return $app["extensions.BoltForms"];
    }
}
