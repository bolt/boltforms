<?php

namespace Bolt\Extension\Bolt\BoltForms\Provider;

use Bolt\Extension\Bolt\BoltForms\BoltForms;
use Bolt\Extension\Bolt\BoltForms\Database;
use Bolt\Extension\Bolt\BoltForms\Email;
use Bolt\Extension\Bolt\BoltForms\Submission\Processor;
use Bolt\Extension\Bolt\BoltForms\Subscriber\BoltFormsCustomDataSubscriber;
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

        $app['boltforms.processor'] = $app->share(
            function ($app) {
                $processor = new Processor($app);

                return $processor;
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

        $app['boltforms.subscriber.custom_data'] = $app->share(function ($app) {
            return new BoltFormsCustomDataSubscriber($app);
        });
    }

    public function boot(Application $app)
    {
        $dispatcher = $app['dispatcher'];
        $dispatcher->addSubscriber($app['boltforms.subscriber.custom_data']);
    }
}
