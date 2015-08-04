<?php

namespace Bolt\Extension\Bolt\BoltForms\Twig;

use Bolt\Extension\Bolt\BoltForms\BoltForms;
use Bolt\Extension\Bolt\BoltForms\Exception\FileUploadException;
use Bolt\Extension\Bolt\BoltForms\Exception\FormValidationException;
use Bolt\Extension\Bolt\BoltForms\Extension;
use Bolt\Helpers\Arr;
use ReCaptcha\ReCaptcha;
use Silex\Application;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\RedirectResponse;

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
            'boltforms'         => new \Twig_Function_Method($this, 'twigBoltForms'),
            'boltforms_uploads' => new \Twig_Function_Method($this, 'twigBoltFormsUploads')
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
    public function twigBoltForms($formname, $html_pre = '', $html_post = '', $data = array(), $options = array())
    {
        if (!isset($this->config[$formname])) {
            return new \Twig_Markup("<p><strong>BoltForms is missing the configuration for the form named '$formname'!</strong></p>", 'UTF-8');
        }

        $sent = false;
        $message = '';
        $error = '';
        $recaptchaResponse = false;

        $this->app['boltforms']->makeForm($formname, 'form', $data, $options);

        $fields = $this->config[$formname]['fields'];

        // Add our fields all at once
        $this->app['boltforms']->addFieldArray($formname, $fields);

        // Handle the POST
        if ($this->app['request']->isMethod('POST')) {
            // Check reCaptcha, if enabled.
            $recaptchaResponse = $this->app['boltforms']->getReCaptchaResponses($this->app['request']);

            try {
                $sent = $this->app['boltforms']->processRequest($formname, $recaptchaResponse);
                $message = isset($this->config[$formname]['feedback']['success']) ? $this->config[$formname]['feedback']['success'] : 'Form submitted sucessfully';
            } catch (FileUploadException $e) {
                $error = $e->getMessage();
                $this->app['logger.system']->debug('[BoltForms] File upload exception: ' . $error, array('event' => 'extensions'));
            } catch (FormValidationException $e) {
                $error = $e->getMessage();
                $this->app['logger.system']->debug('[BoltForms] Form validation exception: ' . $error, array('event' => 'extensions'));
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
                'public_key'    => $this->config['recaptcha']['public_key'],
                'theme'         => $this->config['recaptcha']['theme'],
                'error_message' => $this->config['recaptcha']['error_message'],
                'error_codes'   => $recaptchaResponse ? $recaptchaResponse['errorCodes'] : null,
                'valid'         => $recaptchaResponse ? $recaptchaResponse['success'] : null
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
     * Twig function to display uploaded files, downloadable via the controller.
     *
     * @param string $formname
     *
     * @return \Twig_Markup
     */
    public function twigBoltFormsUploads($formname = null)
    {
        $dir = realpath($this->config['uploads']['base_directory'] . DIRECTORY_SEPARATOR . $formname);
        if ($dir === false) {
            return new \Twig_Markup('<p><strong>Invalid upload directory</strong></p>', 'UTF-8');
        }

        $finder = new Finder();
        $finder->files()
            ->in($dir)
            ->ignoreUnreadableDirs()
            ->ignoreDotFiles(true)
            ->ignoreVCS(true)
        ;

        // Render the Twig
        $html = $this->app['render']->render(
        $this->config['templates']['files'], array(
            'directories' => $finder->directories(),
            'files'       => $finder->files(),
            'base_uri'    => '/' . $this->config['uploads']['base_uri'] . '/download'
        ));

        return new \Twig_Markup($html, 'UTF-8');
    }
}
