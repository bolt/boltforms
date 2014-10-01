<?php

namespace Bolt\Extension\Bolt\Forms\Twig;

use Bolt\Extension\Bolt\Forms\Database;
use Bolt\Extension\Bolt\Forms\Email;
use Bolt\Extension\Bolt\Forms\Extension;
use Bolt\Extension\Bolt\Forms\Forms;
use Silex\Application;

/**
 * Twig functions for Forms
 *
 * Copyright (C) 2014  Gawain Lynch
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
class FormsExtension extends \Twig_Extension
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
     * @var \Twig_Environment
     */
    private $twig = null;

    /**
     * @var Bolt\Extension\Bolt\Forms\Database
     */
    private $database;

    /**
     * @var Bolt\Extension\Bolt\Forms\Email
     */
    private $email;

    public function __construct(Application $app)
    {
        $this->app      = $app;
        $this->config   = $this->app[Extension::CONTAINER]->config;
        $this->forms    = new Forms($app);
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
        return 'forms.extension';
    }

    /**
     * The functions we add
     */
    public function getFunctions()
    {
        return array(
            'forms' => new \Twig_Function_Method($this, 'twigForms')
        );
    }

    /**
     * Twig function for form generation
     *
     * @param string $formname
     * @return Twig_Markup
     */
    public function twigForms($formname)
    {
        if (isset($this->config[$formname])) {
            $message = '';
            $error = '';
            $postdata = false;

            $this->forms->makeForm($formname, 'form', $options, $data);

            $fields = $this->config[$formname]['fields'];

            // Add our fields all at once
            $this->forms->addFieldArray($formname, $fields);

            if ($this->app['request']->getMethod() == 'POST') {
                $postdata = $this->forms->handleRequest($formname);

                if ($postdata) {
                    // Don't keep token data around where not needed
                    unset ($postdata['_token']);

                    // Write to a Contenttype
                    if (isset($this->config[$formname]['database']['contenttype'])) {
                        $this->database->writeToContentype($this->config[$formname]['database']['contenttype'], $postdata);
                    }

                    // Write to a normal database table
                    if (isset($this->config[$formname]['database']['table'])) {
                        $this->database->writeToTable($this->config[$formname]['database']['table'], $postdata);
                    }

                    // Send notification email
                    if (isset($this->config[$formname]['notification']['enabled'])) {
                        $emailconfig = $this->getEmailConfig($formname, $postdata);
                        $this->email->doNotification($this->config[$formname], $emailconfig, $postdata);
                    }
                }
            }

            // Get our values to be passed to Twig
            $twigvalues = array(
                'error'     => $error,
                'message'   => $message,
                'sent'      => $this->forms($formname)->isSubmitted(),
                'recaptcha' => array(
                    'html'  => ($this->config['recaptcha']['enabled'] ? recaptcha_get_html($this->config['recaptcha']['public_key']) : ''),
                    'theme' => ($this->config['recaptcha']['enabled'] ? $this->config['recaptcha']['theme'] : ''),
                ),
                'formname'  => $formname
            );

            // Render the Twig_Markup
            return $this->forms->renderForm($formname, $this->config['templates']['form'], $twigvalues);
        }
    }

    /**
     * Get a usable email configuration array
     *
     * @param string $formname
     * @param array  $postdata
     */
    private function getEmailConfig($formname, $postdata)
    {
        $notify_form = $this->config[$formname]['notification'];

        // Global debug enabled  - takes preference over form specific settings
        // Global debug disabled - form specfic setting used
        $emailconfig = array(
            'debug'         => $this->config['debug']['enabled'] === false && isset($notify_form['debug']) ? $notify_form['debug'] : $this->config['debug']['enabled'],
            'debug_address' => $this->config['debug']['address'],
            'to_name'       => isset($notify_form['to_name'])    ? $notify_form['to_name']    : '',
            'to_email'      => isset($notify_form['to_email'])   ? $notify_form['to_email']   : '',
            'from_name'     => isset($notify_form['from_name'])  ? $notify_form['from_name']  : '',
            'from_email'    => isset($notify_form['from_email']) ? $notify_form['from_email'] : '',
            'cc_name'       => isset($notify_form['cc_name'])    ? $notify_form['cc_name']    : '',
            'cc_email'      => isset($notify_form['cc_email'])   ? $notify_form['cc_email']   : '',
            'bcc_name'      => isset($notify_form['bcc_name'])   ? $notify_form['bcc_name']   : '',
            'bcc_email'     => isset($notify_form['bcc_email'])  ? $notify_form['bcc_email']  : ''
        );

        // If any fields rely on posted data populate them now
        foreach ($emailconfig as $key => $value) {
            if ($key == 'debug' || $key == 'debug_address') {
                continue;
            }

            if (isset($postdata[$value])) {
                $emailconfig[$key] = $postdata[$value];
            }
        }

        return $emailconfig;
    }
}
