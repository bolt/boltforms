<?php

namespace Bolt\Extension\Bolt\BoltForms;

use Bolt\Extension\SimpleExtension;
use Pimple as Container;
use Silex\Application;

/**
 * BoltForms a Symfony Forms interface for Bolt
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
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
 */
class BoltFormsExtension extends SimpleExtension
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'BoltForms';
    }

    /**
     * {@inheritdoc}
     */
    public function register(Application $app)
    {
        parent::register($app);

        // Providers
        $app->register(new Provider\BoltFormsServiceProvider());
        $app->register(new Provider\RecaptchaServiceProvider());
    }

    /**
     * {@inheritdoc}
     */
    protected function registerFrontendControllers()
    {
        if ($this->getConfig()['uploads']['management_controller']) {
            $url = '/' . ltrim($this->getConfig()['uploads']['base_uri'], '/');
            return [
                $url=> new Controller\UploadManagement($this->getConfig()),
            ];
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    protected function registerTwigPaths()
    {
        return ['templates'];
    }

    /**
     * {@inheritdoc}
     */
    protected function registerTwigFunctions()
    {
        $twig = Container::share(
            function () {
                return new Twig\BoltFormsExtension($this->getContainer(), $this->getConfig());
            }
        );
        $forms = Container::share(
            function ($formName, $htmlPreSubmit = '', $htmlPostSubmit = '', $data = [], $options = []) use ($twig) {
                return $twig(true)->twigBoltForms($formName, $htmlPreSubmit, $htmlPostSubmit, $data, $options);
            }
        );
        $uploads = Container::share(
            function () use ($twig) {
                return $twig(true)->twigBoltFormsUploads();
            }
        );

        return [
            'boltforms'         => [
                $forms,
                ['is_safe' => ['html'], 'is_safe_callback' => true],
            ],
            'boltforms_uploads' => [
                $uploads,
                [],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function isSafe()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return parent::getConfig();
    }

    /**
     * All the non-forms config keys.
     *
     * @return string[]
     */
    public function getConfigKeys()
    {
        return [
            'csrf',
            'recaptcha',
            'templates',
            'debug',
            'uploads',
            'fieldmap',
        ];
    }

    /**
     * Set the defaults for configuration parameters
     *
     * {@inheritdoc}
     */
    protected function getDefaultConfig()
    {
        return [
            'csrf'      => true,
            'recaptcha' => [
                'enabled'       => false,
                'label'         => "Please enter the reCaptch text to prove you're a human",
                'public_key'    => '',
                'private_key'   => '',
                'error_message' => "The CAPTCHA wasn't entered correctly. Please try again.",
                'theme'         => 'clean',
            ],
            'templates' => [
                'fields'  => 'boltforms_fields.twig',
                'form'    => 'boltforms_form.twig',
                'email'   => 'boltforms_email.twig',
                'subject' => 'boltforms_email_subject.twig',
                'files'   => 'boltforms_file_browser.twig',
            ],
            'debug' => [
                'enabled' => false,
                'address' => '',
            ],
            'uploads' => [
                'enabled'               => false,
                'base_directory'        => '/tmp/',
                'filename_handling'     => 'suffix',
                'management_controller' => false,
                'base_uri'              => 'boltforms',
            ],
            'fieldmap' => [
                'email' => [
                    'config'  => 'config',
                    'data'    => 'data',
                    'fields'  => 'fields',
                    'subject' => 'subject',
                ],
            ],
        ];
    }
}
