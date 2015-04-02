<?php

namespace Bolt\Extension\Bolt\BoltForms;

use Bolt;

/**
 * BoltForms a Symfony Forms interface for Bolt
 *
 * Copyright (C) 2014 Gawain Lynch
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
class Extension extends \Bolt\BaseExtension
{
    /**
     * Extension name
     *
     * @var string
     */
    const NAME = "BoltForms";

    /**
     * Extension's service container
     *
     * @var string
     */
    const CONTAINER = 'extensions.BoltForms';

    public function getName()
    {
        return Extension::NAME;
    }

    /**
     * Allow users to place {{ boltforms() }} tags into content, if
     * `allowtwig: true` is set in the contenttype.
     *
     * @return boolean
     */
    public function isSafe()
    {
        return true;
    }

    /**
     * Let Bolt know this extension sends emails. The user will see a
     * notification on the dashboard if mail is not set up correctly.
     */
    public function sendsMail()
    {
        return true;
    }

    public function initialize()
    {
        /*
         * Config
         */
        $this->setConfig();

        /*
         * Provider
         */
        $this->app->register(new Provider\BoltFormsServiceProvider($this->app));

        /*
         * Backend
         */
        if ($this->app['config']->getWhichEnd() == 'backend') {
            //
        }

        /*
         * Frontend
         */
        if ($this->app['config']->getWhichEnd() == 'frontend') {
            // Twig functions
            $this->app['twig']->addExtension(new Twig\BoltFormsExtension($this->app));
        }
    }

    /**
     * Post config file loading configuration
     *
     * @return void
     */
    private function setConfig()
    {
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
                'subject' => 'boltforms_email_subject.twig'
            ),
            'debug' => array(
                'enabled' => false,
                'address' => ''
            )
        );
    }
}
