<?php

namespace Bolt\Extension\Bolt\BoltForms\Provider;

use Bolt\Extension\Bolt\BoltForms\BoltForms;
use Bolt\Extension\Bolt\BoltForms\Database;
use Bolt\Extension\Bolt\BoltForms\Email;
use Silex\Application;
use Silex\ServiceProviderInterface;

class BoltFormsServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['boltforms'] = $app->share(
            function ($app) {
                $forms = new BoltForms($app);

                return $forms;
            }
        );

        $app['boltforms.database'] = $app->share(
            function ($app) {
                $database = new Database($app);

                return $database;
            }
        );

        $app['boltforms.email'] = $app->share(
            function ($app) {
                $email = new Email($app);

                return $email;
            }
        );
    }

    public function boot(Application $app)
    {
    }
}
