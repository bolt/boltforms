<?php

namespace Bolt\Extension\Bolt\BoltForms\Provider;

use Bolt\Extension\Bolt\BoltForms\BoltForms;
use Bolt\Extension\Bolt\BoltForms\BoltFormsExtension;
use Bolt\Extension\Bolt\BoltForms\Config;
use Bolt\Extension\Bolt\BoltForms\Factory;
use Bolt\Extension\Bolt\BoltForms\Submission;
use Bolt\Extension\Bolt\BoltForms\Subscriber\BoltFormsCustomDataSubscriber;
use Bolt\Extension\Bolt\BoltForms\Twig;
use Pimple as Container;
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
                $compiler = new Factory\FormContext($webPath);

                return $compiler;
            }
        );

        $app['boltforms.processor'] = $app->share(
            function ($app) {
                $processor = new Submission\Processor(
                    $app['boltforms.config'],
                    $app['boltforms'],
                    $app['dispatcher'],
                    $app['logger.system'],
                    $app
                );

                return $processor;
            }
        );

        $app['boltforms.database'] = $app->share(
            function ($app) {
                $database = new Submission\Database($app);

                return $database;
            }
        );

        $app['boltforms.email'] = $app->share(
            function ($app) {
                $email = new Submission\Email($app);

                return $email;
            }
        );

        $app['boltforms.twig.helper'] = $app->share(
            function ($app) {
                return new Container([
                    'form' => $app->share(
                        function () use ($app) {
                            return new Twig\Helper\FormHelper(
                                $app['boltforms'],
                                $app['boltforms.config'],
                                $app['boltforms.processor'],
                                $app['boltforms.form.context.factory'],
                                $app['boltforms.feedback'],
                                $app['session'],
                                $app['request_stack'],
                                $app['logger.system']
                            );
                        }
                    ),
                ]);
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
