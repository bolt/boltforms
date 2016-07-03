<?php

namespace Bolt\Extension\Bolt\BoltForms\Provider;

use Bolt\Extension\Bolt\BoltForms\BoltFormsExtension;
use ReCaptcha\ReCaptcha;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;

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

        $app['recapture.response.factory'] = $app->protect(
            function () use ($app) {
                $request = $app['request_stack']->getCurrentRequest();
                $config = $app['boltforms.config'];

                // Check reCaptcha, if enabled.  If not just return true
                if (!$request->isMethod(Request::METHOD_POST) || $config->getReCaptcha()->get('enabled') === false) {
                    return [
                        'success'    => true,
                        'errorCodes' => null,
                    ];
                }

                /** @var \ReCaptcha\ReCaptcha $reCaptcha */
                $reCaptcha = $app['recaptcha'];
                $reCaptchaResponse = $reCaptcha->verify($request->get('g-recaptcha-response'), $request->getClientIp());

                return [
                    'success'    => $reCaptchaResponse->isSuccess(),
                    'errorCodes' => $reCaptchaResponse->getErrorCodes(),
                ];
            }
        );
    }

    public function boot(Application $app)
    {
    }
}
