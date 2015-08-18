<?php

namespace Bolt\Extension\Bolt\BoltForms;

use Bolt\BaseExtension;

/**
 * BoltForms a Symfony Forms interface for Bolt
 *
 * Copyright (C) 2014-2015 Gawain Lynch
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
 * @copyright Copyright (c) 2014, Gawain Lynch
 * @license   http://opensource.org/licenses/GPL-3.0 GNU Public License 3.0
 */
class Extension extends BaseExtension
{
    /** @var string Extension name */
    const NAME = 'BoltForms';
    /** @var string Extension's service container */
    const CONTAINER = 'extensions.BoltForms';

    public function getName()
    {
        return Extension::NAME;
    }

    /**
     * Let Bolt know this extension sends emails. The user will see a
     * notification on the dashboard if mail is not set up correctly.
     */
    public function sendsMail()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        /*
         * Provider
         */
        $this->app->register(new Provider\BoltFormsServiceProvider());
        $this->app->register(new Provider\RecaptchaServiceProvider());

        /*
         * Frontend
         */
        if ($this->app['config']->getWhichEnd() === 'frontend') {
            $this->addTwig();
        }

        /*
         * Management controller
         */
        if ($this->config['uploads']['management_controller']) {
            $url = '/' . ltrim($this->config['uploads']['base_uri'], '/');
            $this->app->mount($url, new Controller\UploadManagement());
        }
    }

    /**
     * Add the Twig functions.
     */
    private function addTwig()
    {
        $app = $this->app;

        // Safe
        $this->app->share(
            $this->app->extend(
                'twig',
                function (\Twig_Environment $twig) use ($app) {
                    $twig->addExtension(new Twig\BoltFormsExtension($app));

                    return $twig;
                }
            )
        );

        // Normal
        $this->app->share(
            $this->app->extend(
                'safe_twig',
                function (\Twig_Environment $twig) use ($app) {
                    $twig->addExtension(new Twig\BoltFormsExtension($app));

                    return $twig;
                }
            )
        );
    }

    /**
     * All the non-forms config keys.
     *
     * @return string[]
     */
    public function getConfigKeys()
    {
        return array(
            'csrf',
            'recaptcha',
            'templates',
            'debug',
            'uploads',
            'fieldmap'
        );
    }

    /**
     * Set the defaults for configuration parameters
     *
     * @return array
     */
    protected function getDefaultConfig()
    {
        return array(
            'csrf'      => true,
            'recaptcha' => array(
                'enabled'       => false,
                'label'         => "Please enter the reCaptch text to prove you're a human",
                'public_key'    => '',
                'private_key'   => '',
                'error_message' => "The CAPTCHA wasn't entered correctly. Please try again.",
                'theme'         => 'clean'
            ),
            'templates' => array(
                'fields'  => 'boltforms_fields.twig',
                'form'    => 'boltforms_form.twig',
                'email'   => 'boltforms_email.twig',
                'subject' => 'boltforms_email_subject.twig',
                'files'   => 'boltforms_file_browser.twig'
            ),
            'debug' => array(
                'enabled' => false,
                'address' => ''
            ),
            'uploads' => array(
                'enabled'               => false,
                'base_directory'        => '/tmp/',
                'filename_handling'     => 'suffix',
                'management_controller' => false,
                'base_uri'              => 'boltforms'
            ),
            'fieldmap' => array(
                'email' => array(
                    'config'  => 'config',
                    'data'    => 'data',
                    'fields'  => 'fields',
                    'subject' => 'subject',
                )
            ),
        );
    }
}
