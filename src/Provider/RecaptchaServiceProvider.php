<?php

namespace Bolt\Extension\Bolt\BoltForms\Provider;

use Bolt\Extension\Bolt\BoltForms\BoltFormsExtension;
use ReCaptcha\ReCaptcha;
use Silex\Application;
use Silex\ServiceProviderInterface;

class RecaptchaServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['recaptcha'] = $app->share(
            function ($app) {
                /** @var BoltFormsExtension $extension */
                $extension = $app['extensions']->get('Bolt/BoltForms');
                $key = $extension->getConfig()['recaptcha']['private_key'];
                $reCaptcha = new ReCaptcha($key);

                return $reCaptcha;
            }
        );
    }

    public function boot(Application $app)
    {
    }
}
