<?php

namespace Bolt\Extension\Bolt\BoltForms\Provider;

use Bolt\Extension\Bolt\BoltForms\BoltForms;
use Bolt\Extension\Bolt\BoltForms\BoltFormsExtension;
use Bolt\Extension\Bolt\BoltForms\Config;
use Bolt\Extension\Bolt\BoltForms\Database;
use Bolt\Extension\Bolt\BoltForms\Email;
use Bolt\Extension\Bolt\BoltForms\Submission\Processor;
use Bolt\Extension\Bolt\BoltForms\Subscriber\BoltFormsCustomDataSubscriber;
use Bolt\Extension\Bolt\BoltForms\Twig;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;

class BoltFormsServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['session'] = $app->extend(
            'session',
            function ($session) use ($app) {
                $session->registerBag($app['boltforms.feedback']);

                return $session;
            }
        );

        $app['boltforms'] = $app->share(
            function ($app) {
                $forms = new BoltForms($app);

                return $forms;
            }
        );

        $app['boltforms.config'] = $app->share(
            function ($app) {
                /** @var BoltFormsExtension $boltForms */
                $boltForms = $app['extensions']->get('Bolt/BoltForms');
                $config = new Config\Config($boltForms->getConfig());

                return $config;
            }
        );

        $app['boltforms.feedback'] = $app->share(
            function () {
                $bag = new FlashBag('_boltforms');
                $bag->setName('boltforms');

                return $bag;
            }
        );

        $app['boltforms.form.context.factory'] = $app->protect(
            function () use ($app) {
                $webPath = $app['extensions']->get('Bolt/BoltForms')->getWebDirectory()->getPath();
                $compiler = new Twig\FormContext($app['boltforms.config'], $webPath);

                return $compiler;
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

        $app['boltforms.twig'] = $app->share(
            function ($app) {
                $twig = new Twig\BoltFormsExtension($app, $app['boltforms.config']);

                return $twig;
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
