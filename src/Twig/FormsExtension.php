<?php

namespace Bolt\Extension\Bolt\Forms\Twig;

use Bolt\Extension\Bolt\Forms\Database;
use Bolt\Extension\Bolt\Forms\Extension;
use Bolt\Extension\Bolt\Forms\Forms;

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

    public function __construct(\Silex\Application $app)
    {
        $this->app = $app;
        $this->config = $this->app[Extension::CONTAINER]->config;
        $this->forms = new Forms($app);
        $this->database = new Database($app);
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

    public function twigForms($formname)
    {
        if (isset($this->config[$formname])) {
            $message = '';
            $error = '';
            $sent = false;

            $this->forms->makeForm($formname, 'form', $options, $data);

            $fields = $this->config[$formname]['fields'];

            // Add our fields all at once
            $this->forms->addFieldArray($formname, $fields);

            if ($this->app['request']->getMethod() == 'POST') {
                $sent = $this->forms->handleRequest($formname);

                if ($sent) {
                    unset ($sent['_token']);

                    //
                    if (isset($this->config[$formname]['database']['contenttype'])) {
                        $this->database->writeToContentype($this->config[$formname]['database']['contenttype'], $sent);
                    }

                    //
                    if (isset($this->config[$formname]['database']['table'])) {
                        $this->database->writeToTable($this->config[$formname]['database']['table'], $sent);
                    }

                    //
                    if (isset($this->config[$formname]['notification']['to_email'])) {
                        //
                    }
                }
            }

            // Get our values to be passed to Twig
            $twigvalues = array(
                'error'           => $error,
                'message'         => $message,
                'sent'            => $sent,
                'recaptcha_html'  => ($this->config['recaptcha']['enabled'] ? recaptcha_get_html($this->config['recaptcha']['public_key']) : ''),
                'recaptcha_theme' => ($this->config['recaptcha']['enabled'] ? $this->config['recaptcha']['theme'] : ''),
                'formname'        => $formname
            );

            // Render the Twig_Markup
            return $this->forms->renderForm($formname, $this->config['templates']['form'], $twigvalues);
        }
    }
}
