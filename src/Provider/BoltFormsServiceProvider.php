<?php

namespace Bolt\Extension\Bolt\BoltForms\Provider;

use Bolt\Extension\Bolt\BoltForms\BoltForms;
use Bolt\Extension\Bolt\BoltForms\BoltFormsExtension;
use Bolt\Extension\Bolt\BoltForms\Config;
use Bolt\Extension\Bolt\BoltForms\Factory;
use Bolt\Extension\Bolt\BoltForms\Form;
use Bolt\Extension\Bolt\BoltForms\Submission;
use Bolt\Extension\Bolt\BoltForms\Subscriber\DynamicDataSubscriber;
use Bolt\Extension\Bolt\BoltForms\Twig;
use Bolt\Version as BoltVersion;
use Pimple as Container;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * BoltForms service provider.
 *
 * Copyright (c) 2014-2016 Gawain Lynch
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
class BoltFormsServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['form.extensions'] = $app->share(
            $app->extend(
                'form.extensions',
                function ($extensions, $app) {
                    $extensions[] = new Form\BoltFormsExtension($app);

                    return $extensions;
                }
            )
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

        $app['session'] = $app->share(
            $app->extend(
                'session',
                function (SessionInterface $session) use ($app) {
                    $session->registerBag($app['boltforms.feedback']);

                    return $session;
                }
            )
        );

        $app['boltforms.form.context.factory'] = $app->protect(
            function () use ($app) {
                $webPath = $app['extensions']->get('Bolt/BoltForms')->getWebDirectory()->getPath();
                $compiler = new Factory\FormContext($webPath);

                return $compiler;
            }
        );

        $app['boltforms.form.field_options.factory'] = $app->protect(
            function ($type, $options) use ($app) {
                $resolver = new Factory\FieldOptionsResolver($type, $options);

                return $resolver->getOptions($app['storage'], $app['dispatcher']);
            }
        );

        $app['boltforms.subscriber.custom_data'] = $app->share(function ($app) {
            return new DynamicDataSubscriber($app);
        });

        $this->registerHandlers($app);
        $this->registerProcessors($app);
        $this->registerTwig($app);
    }

    public function boot(Application $app)
    {
        $dispatcher = $app['dispatcher'];
        $dispatcher->addSubscriber($app['boltforms.subscriber.custom_data']);
    }

    private function registerTwig(Application $app)
    {
        if (version_compare(BoltVersion::forComposer(), '3.3.0', '<')) {
            if (!isset($app['twig.runtimes'])) {
                $app['twig.runtimes'] = function () {
                    return [];
                };
            }
            if (!isset($app['twig.runtime_loader'])) {
                $app['twig.runtime_loader'] = function ($app) {
                    return new Twig\RuntimeLoader($app, $app['twig.runtimes']);
                };
            }
        }

        $app['twig.runtime.boltforms'] = function ($app) {
            $rootPath = version_compare(BoltVersion::forComposer(), '3.3.0', '<')
                ? $app['resources']->getPath('root')
                : $app['path_resolver']->resolve('%root%')
            ;

            return new Twig\Extension\BoltFormsRuntime(
                $app['boltforms'],
                $app['boltforms.config'],
                $app['boltforms.processor'],
                $app['boltforms.form.context.factory'],
                $app['recapture.response.factory'],
                $app['session'],
                $app['request_stack'],
                $app['logger.system'],
                $app['url_generator'],
                $app['extensions']->get('bolt/boltforms')->getWebDirectory()->getPath(),
                $rootPath
            );
        };

        $app['twig.runtimes'] = $app->extend(
            'twig.runtimes',
            function (array $runtimes) {
                return $runtimes + [
                    Twig\Extension\BoltFormsRuntime::class => 'twig.runtime.boltforms',
                ];
            }
        );

        $app['twig'] = $app->share(
            $app->extend(
                'twig',
                function (\Twig_Environment $twig, $app) {
                    $twig->addExtension(new Twig\Extension\BoltFormsExtension());

                    if (version_compare(BoltVersion::forComposer(), '3.3.0', '<')) {
                        $twig->addRuntimeLoader($app['twig.runtime_loader']);
                    }

                    return $twig;
                }
            )
        );
    }

    private function registerHandlers(Application $app)
    {
        $app['boltforms.handlers'] = $app->share(
            function (Application $app) {
                return new Container([
                    'content'  => $app->share(
                        function () use ($app) {
                            return new Submission\Handler\ContentType(
                                $app['boltforms.config'],
                                $app['storage'],
                                $app['session'],
                                $app['logger.system'],
                                $app['mailer']
                            );
                        }
                    ),
                    'database' => $app->share(
                        function () use ($app) {
                            return new Submission\Handler\DatabaseTable(
                                $app['boltforms.config'],
                                $app['storage'],
                                $app['session'],
                                $app['logger.system'],
                                $app['mailer']
                            );
                        }
                    ),
                    'email'    => $app->share(
                        function () use ($app) {
                            return new Submission\Handler\Email(
                                $app['boltforms.config'],
                                $app['storage'],
                                $app['session'],
                                $app['logger.system'],
                                $app['mailer'],
                                $app['dispatcher'],
                                $app['twig'],
                                $app['url_generator']
                            );
                        }
                    ),
                    'redirect' => $app->share(
                        function () use ($app) {
                            return new Submission\Handler\Redirect($app['url_matcher']);
                        }
                    ),
                    'request' => $app->share(
                        function () use ($app) {
                            return new Submission\Handler\PostRequest($app['request_stack']);
                        }
                    ),
                    'upload'  => $app->protect(
                        function (Config\FormConfig $formConfig, UploadedFile $file) use ($app) {
                            return new Submission\Handler\Upload(
                                $app['boltforms.config'],
                                $formConfig,
                                $file
                            );
                        }
                    ),
                ]);
            }
        );
    }

    private function registerProcessors(Application $app)
    {
        $app['boltforms.processors'] = $app->share(
            function (Application $app) {
                return new Container([
                    'content'  => $app->share(function () use ($app) { return new Submission\Processor\ContentType($app['boltforms.handlers']); }),
                    'database' => $app->share(function () use ($app) { return new Submission\Processor\DatabaseTable($app['boltforms.handlers']); }),
                    'email'    => $app->share(function () use ($app) { return new Submission\Processor\Email($app['boltforms.handlers']); }),
                    'feedback' => $app->share(function () use ($app) { return new Submission\Processor\Feedback($app['boltforms.handlers'], $app['session']); }),
                    'fields'   => $app->share(function () use ($app) { return new Submission\Processor\Fields($app['boltforms.handlers'], $app['boltforms.config']); }),
                    'redirect' => $app->share(function () use ($app) { return new Submission\Processor\Redirect($app['boltforms.handlers'], $app['request_stack'], $app['session']); }),
                    'uploads'  => $app->share(function () use ($app) { return new Submission\Processor\Uploads($app['boltforms.handlers'], $app['boltforms.config'], $app['session']); }),
                ]);
            }
        );

        $app['boltforms.processor'] = $app->share(
            function (Application $app) {
                $processor = new Submission\Processor(
                    $app['boltforms'],
                    $app['boltforms.config'],
                    $app['boltforms.processors'],
                    $app['boltforms.handlers'],
                    $app['dispatcher'],
                    $app['logger.system'],
                    $app['boltforms.feedback'],
                    $app['config']->get('general/debug')
                );

                return $processor;
            }
        );
    }
}
