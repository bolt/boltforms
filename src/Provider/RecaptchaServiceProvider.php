<?php

namespace Bolt\Extension\Bolt\BoltForms\Provider;

use Bolt\Extension\Bolt\BoltForms\BoltFormsExtension;
use ReCaptcha\ReCaptcha;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * reCaptcha service provider.
 *
 *  * Copyright (c) 2014-2016 Gawain Lynch
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License or GNU Lesser
 * General Public License as published by the Free Software Foundation,
 * either version 3 of the Licenses, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   http://opensource.org/licenses/GPL-3.0 GNU Public License 3.0
 * @license   http://opensource.org/licenses/LGPL-3.0 GNU Lesser General Public License 3.0
 */
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
            function ($enabled = true) use ($app) {
                $request = $app['request_stack']->getCurrentRequest();
                $config = $app['boltforms.config'];

                // Check reCaptcha, if enabled.  If not just return true
                if (!$request->isMethod(Request::METHOD_POST) || !$config->getReCaptcha()->isEnabled() || $enabled === false) {
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
