<?php

namespace Bolt\Extension\Bolt\BoltForms;

use Bolt\Extension\Bolt\BoltForms\Config\FieldMap;
use Bolt\Extension\Bolt\BoltForms\Subscriber\ProcessLifecycleSubscriber;
use Bolt\Extension\SimpleExtension;
use Silex\Application;

/**
 * BoltForms a Symfony Forms interface for Bolt
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
class BoltFormsExtension extends SimpleExtension
{
    /**
     * {@inheritdoc}
     */
    public function getServiceProviders()
    {
        return [
            $this,
            new Provider\BoltFormsServiceProvider(),
            new Provider\RecaptchaServiceProvider(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function boot(Application $app)
    {
        parent::boot($app);

        $dispatcher = $this->container['dispatcher'];
        $dispatcher->addSubscriber(new ProcessLifecycleSubscriber($app));
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
     * {@inheritdoc}
     */
    protected function registerFrontendControllers()
    {
        $app = $this->getContainer();
        $controllers = [
            '/async/boltforms' => new Controller\Async(),
        ];

        if ($this->getConfig()['uploads']['management_controller']) {
            $url = $app['boltforms.config']->getUploads()->get('base_uri');
            $controllers[$url] = new Controller\UploadManagement();
        }

        return $controllers;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return parent::getConfig();
    }

    /**
     * {@inheritdoc}
     */
    protected function registerTwigPaths()
    {
        return [
            'templates' => ['namespace' => 'BoltForms'],
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
     * Set the defaults for configuration parameters.
     *
     * {@inheritdoc}
     */
    protected function getDefaultConfig()
    {
        return [
            'csrf'      => true,
            'recaptcha' => [
                'enabled'        => false,
                'label'          => "Please enter the CAPTCHA text to prove you're a human",
                'public_key'     => '',
                'private_key'    => '',
                'error_message'  => "The CAPTCHA wasn't entered correctly. Please try again.",
                'theme'          => 'clean',
                'badge_location' => 'bottomright',
            ],
            'templates' => [
                'ajax'       => '@BoltForms/asset/_ajax.twig',
                'css'        => '@BoltForms/asset/_css.twig',
                'js'         => '@BoltForms/asset/_js.twig',
                'email'      => '@BoltForms/email/email.twig',
                'subject'    => '@BoltForms/email/subject.twig',
                'messages'   => '@BoltForms/feedback/_messages.twig',
                'exception'  => '@BoltForms/feedback/_exception.twig',
                'files'      => '@BoltForms/file/browser.twig',
                'form'       => '@BoltForms/form/form.twig',
                'form_theme' => '@BoltForms/form/_form_theme.twig',
                'fields'     => '@BoltForms/form/_fields.twig',
                'recaptcha'  => '@BoltForms/form/_recaptcha.twig',
                'macros'     => '@BoltForms/_macros.twig',
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
                'email' => new FieldMap\Email(),
            ],
        ];
    }
}
