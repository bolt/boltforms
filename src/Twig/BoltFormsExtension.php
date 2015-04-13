<?php

namespace Bolt\Extension\Bolt\BoltForms\Twig;

use Bolt\Extension\Bolt\BoltForms\BoltForms;
use Bolt\Extension\Bolt\BoltForms\Database;
use Bolt\Extension\Bolt\BoltForms\Email;
use Bolt\Extension\Bolt\BoltForms\Extension;
use ReCaptcha\ReCaptcha;
use Silex\Application;

/**
 * Twig functions for BoltForms
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
class BoltFormsExtension extends \Twig_Extension
{
    /** @var Application */
    private $app;

    /** @var array */
    private $config;

    /** @var array */
    private $recaptcha = array(
        'success'    => true,
        'errorCodes' => null
    );

    /** @var \Twig_Environment */
    private $twig = null;

    public function __construct(Application $app)
    {
        $this->app      = $app;
        $this->config   = $this->app[Extension::CONTAINER]->config;
    }

    public function initRuntime(\Twig_Environment $environment)
    {
        $this->twig = $environment;
    }

    /**
     * Return the name of the extension
     */
    public function getName()
    {
        return 'boltforms.extension';
    }

    /**
     * The functions we add
     */
    public function getFunctions()
    {
        return array(
            'boltforms' => new \Twig_Function_Method($this, 'twigBoltForms')
        );
    }

    /**
     * Twig function for form generation
     *
     * @param string $formname
     * @param string $html_pre  Intro HTML to display BEFORE successful submit
     * @param string $html_post Intro HTML to display AFTER successful submit
     *
     * @return \Twig_Markup
     */
    public function twigBoltForms($formname, $html_pre = '', $html_post = '')
    {
        if (!isset($this->config[$formname])) {
            return new \Twig_Markup("<p><strong>BoltForms is missing the configuration for the form named '$formname'!</strong></p>", 'UTF-8');
        }

        $options    = array();
        $data       = array();
        $sent       = false;
        $message    = '';
        $error      = '';
        $formdata   = false;

        $this->app['boltforms']->makeForm($formname, 'form', $options, $data);

        $fields = $this->config[$formname]['fields'];

        // Add our fields all at once
        $this->app['boltforms']->addFieldArray($formname, $fields);

        if ($this->app['request']->getMethod() === 'POST') {
            $formdata = $this->app['boltforms']->handleRequest($formname);
            $sent = $this->app['boltforms']->getForm($formname)->isSubmitted();

            // Check reCaptcha, if enabled.
            $this->getReCaptchaResponses();

            if ($formdata && $this->recaptcha['success']) {
                // Don't keep token data around where not needed
                unset($formdata['_token']);

                // Write to a Contenttype
                if (isset($this->config[$formname]['database']['contenttype'])) {
                    $this->app['boltforms.database']->writeToContentype($this->config[$formname]['database']['contenttype'], $formdata);
                }

                // Write to a normal database table
                if (isset($this->config[$formname]['database']['table'])) {
                    $this->app['boltforms.database']->writeToTable($this->config[$formname]['database']['table'], $formdata);
                }

                // Send notification email
                if (isset($this->config[$formname]['notification']['enabled'])) {
                    $this->app['boltforms.email']->doNotification($formname, $this->config[$formname], $formdata);
                }

                $message = isset($this->config[$formname]['feedback']['success']) ? $this->config[$formname]['feedback']['success'] : 'Form submitted sucessfully';
            } else {
                $sent = false;
                $error = isset($this->config[$formname]['feedback']['error']) ? $this->config[$formname]['feedback']['error'] : 'There are errors in the form, please fix before trying to resubmit';
            }
        }

        // Get our values to be passed to Twig
        $fields = $this->app['boltforms']->getForm($formname)->all();
        $twigvalues = array(
            'fields'    => $fields,
            'html_pre'  => $html_pre,
            'html_post' => $html_post,
            'error'     => $error,
            'message'   => $message,
            'sent'      => $sent,
            'recaptcha' => array(
                'enabled'       => $this->config['recaptcha']['enabled'],
                'label'         => $this->config['recaptcha']['label'],
                'error_message' => $this->config['recaptcha']['error_message'],
                'error_codes'   => $this->recaptcha['errorCodes'],
                'public_key'    => $this->config['recaptcha']['public_key'],
                'theme'         => $this->config['recaptcha']['theme'],
                'valid'         => $this->recaptcha['success']
            ),
            'formname'  => $formname
        );

        // If the form has it's own templates defined, use those, else the globals.
        $template = isset($this->config[$formname]['templates']['form'])
            ? $this->config[$formname]['templates']['form']
            : $this->config['templates']['form'];

        // Render the Twig_Markup
        return $this->app['boltforms']->renderForm($formname, $template, $twigvalues);
    }

    /**
     * Check reCaptcha, if enabled.
     */
    private function getReCaptchaResponses()
    {
        // Check reCaptcha, if enabled.  If not just return true
        if ($this->config['recaptcha']['enabled']) {
            $rc = new ReCaptcha($this->config['recaptcha']['private_key']);
            $reCaptchaResponse = $rc->verify($this->app['request']->get('g-recaptcha-response'), $this->app['request']->getClientIp());

            $this->recaptcha = array(
                'success'    => $reCaptchaResponse->isSuccess(),
                'errorCodes' => $reCaptchaResponse->getErrorCodes()
            );
        }
    }
}
