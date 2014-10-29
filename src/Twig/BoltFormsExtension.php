<?php

namespace Bolt\Extension\Bolt\BoltForms\Twig;

use Bolt\Extension\Bolt\BoltForms\Database;
use Bolt\Extension\Bolt\BoltForms\Email;
use Bolt\Extension\Bolt\BoltForms\Extension;
use Bolt\Extension\Bolt\BoltForms\BoltForms;
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
    /**
     * @var Application
     */
    private $app;

    /**
     * @var array
     */
    private $config;

    /**
     * @var Bolt\Extension\Bolt\BoltForms\BoltForms
     */
    private $forms;

    /**
     * @var \Twig_Environment
     */
    private $twig = null;

    /**
     * @var Bolt\Extension\Bolt\BoltForms\Database
     */
    private $database;

    /**
     * @var Bolt\Extension\Bolt\BoltForms\Email
     */
    private $email;

    public function __construct(Application $app)
    {
        $this->app      = $app;
        $this->config   = $this->app[Extension::CONTAINER]->config;
        $this->forms    = new BoltForms($app);
        $this->database = new Database($app);
        $this->email    = new Email($app);
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
     * @param  string      $formname
     * @param  string      $html_pre  HTML to display with for before successful submit
     * @param  string      $html_post HTML to display with for after successful submit
     * @return Twig_Markup
     */
    public function twigBoltForms($formname, $html_pre = '', $html_post = '')
    {
        if (isset($this->config[$formname])) {
            $options = array();
            $data = array();
            $sent = false;
            $message = '';
            $error = '';
            $formdata = false;
            $recaptcha = true;

            $this->forms->makeForm($formname, 'form', $options, $data);

            $fields = $this->config[$formname]['fields'];

            // Add our fields all at once
            $this->forms->addFieldArray($formname, $fields);

            if ($this->app['request']->getMethod() == 'POST') {
                $formdata = $this->forms->handleRequest($formname);
                $sent = $this->forms->getForm($formname)->isSubmitted();

                // Check reCaptcha, if enabled.  If not just return true
                if ($this->config['recaptcha']['enabled']) {
                    $answer = recaptcha_check_answer(
                        $this->config['recaptcha']['private_key'],
                        $this->app['request']->getClientIp(),
                        $this->app['request']->get('recaptcha_challenge_field'),
                        $this->app['request']->get('recaptcha_response_field'));

                    $recaptcha = $answer->is_valid;
                }

                if ($formdata && $recaptcha) {
                    // Don't keep token data around where not needed
                    unset ($formdata['_token']);

                    // Write to a Contenttype
                    if (isset($this->config[$formname]['database']['contenttype'])) {
                        $this->database->writeToContentype($this->config[$formname]['database']['contenttype'], $formdata);
                    }

                    // Write to a normal database table
                    if (isset($this->config[$formname]['database']['table'])) {
                        $this->database->writeToTable($this->config[$formname]['database']['table'], $formdata);
                    }

                    // Send notification email
                    if (isset($this->config[$formname]['notification']['enabled'])) {
                        $this->email->doNotification($formname, $this->config[$formname], $formdata);
                    }
                } else {
                    $sent = false;
                }
            }

            // Get our values to be passed to Twig
            $twigvalues = array(
                'fields'    => $fields,
                'html_pre'  => $html_pre,
                'html_post' => $html_post,
                'error'     => $error,
                'message'   => $message,
                'sent'      => $sent,
                'recaptcha' => array(
                    'label'         => ($this->config['recaptcha']['enabled'] ? $this->config['recaptcha']['label'] : ''),
                    'error_message' => $this->config['recaptcha']['error_message'],
                    'html'          => ($this->config['recaptcha']['enabled'] ? recaptcha_get_html($this->config['recaptcha']['public_key']) : ''),
                    'theme'         => ($this->config['recaptcha']['enabled'] ? $this->config['recaptcha']['theme'] : ''),
                    'valid'         => $recaptcha
                ),
                'formname'  => $formname
            );

            // Render the Twig_Markup
            return $this->forms->renderForm($formname, $this->config['templates']['form'], $twigvalues);
        }
    }
}
