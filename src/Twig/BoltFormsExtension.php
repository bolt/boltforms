<?php

namespace Bolt\Extension\Bolt\BoltForms\Twig;

use Bolt\Extension\Bolt\BoltForms\BoltForms;
use Bolt\Extension\Bolt\BoltForms\Exception\FileUploadException;
use Bolt\Extension\Bolt\BoltForms\Exception\FormValidationException;
use Silex\Application;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Form;

/**
 * Twig functions for BoltForms
 *
 * Copyright (C) 2014-2016 Gawain Lynch
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
class BoltFormsExtension
{
    /** @var Application */
    private $app;
    /** @var array */
    private $config;

    public function __construct(Application $app, array $config)
    {
        $this->app      = $app;
        $this->config   = $config;
    }

    /**
     * Twig function for form generation
     *
     * @param string $formName
     * @param string $htmlPreSubmit  Intro HTML to display BEFORE successful submit
     * @param string $htmlPostSubmit Intro HTML to display AFTER successful submit
     * @param array  $data
     * @param array  $options
     * @param array  $defaults
     *
     * @return \Twig_Markup
     */
    public function twigBoltForms($formName, $htmlPreSubmit = '', $htmlPostSubmit = '', $data = [], $options = [], $defaults = [])
    {
        if (!isset($this->config[$formName])) {
            return new \Twig_Markup("<p><strong>BoltForms is missing the configuration for the form named '$formName'!</strong></p>", 'UTF-8');
        }

        /** @var BoltForms $boltForms */
        $boltForms = $this->app['boltforms'];
        $sent = false;
        $message = '';
        $error = '';
        $reCaptchaResponse = [
            'success'    => true,
            'errorCodes' => null,
        ];

        $boltForms->makeForm($formName, FormType::class, $data, $options);

        $fields = $this->config[$formName]['fields'];

        // Add our fields all at once
        $boltForms->addFieldArray($formName, $fields);

        // Handle the POST
        $request = $this->app['request_stack']->getCurrentRequest();
        if ($request && $request->isMethod('POST') && $request->get($formName) !== null) {
            // Check reCaptcha, if enabled.
            $reCaptchaResponse = $this->app['boltforms.processor']->reCaptchaResponse($this->app['request']);

            try {
                $sent = $this->app['boltforms.processor']->process($formName, $this->config[$formName], $reCaptchaResponse);
                $message = isset($this->config[$formName]['feedback']['success']) ? $this->config[$formName]['feedback']['success'] : 'Form submitted sucessfully';
            } catch (FileUploadException $e) {
                $error = $e->getMessage();
                $this->app['logger.system']->debug('[BoltForms] File upload exception: ' . $error, ['event' => 'extensions']);
            } catch (FormValidationException $e) {
                $error = $e->getMessage();
                $this->app['logger.system']->debug('[BoltForms] Form validation exception: ' . $error, ['event' => 'extensions']);
            }
        }

        /** @var Form[] $fields Values to be passed to Twig */
        $fields = $boltForms->getForm($formName)->all();
        $context = [
            'fields'    => $fields,
            'defaults'  => $defaults,
            'html_pre'  => $htmlPreSubmit,
            'html_post' => $htmlPostSubmit,
            'error'     => $error,
            'message'   => $message,
            'sent'      => $sent,
            'recaptcha' => [
                'enabled'       => $this->config['recaptcha']['enabled'],
                'label'         => $this->config['recaptcha']['label'],
                'public_key'    => $this->config['recaptcha']['public_key'],
                'theme'         => $this->config['recaptcha']['theme'],
                'error_message' => $this->config['recaptcha']['error_message'],
                'error_codes'   => $reCaptchaResponse ? $reCaptchaResponse['errorCodes'] : null,
                'valid'         => $reCaptchaResponse ? $reCaptchaResponse['success'] : null,
            ],
            'formname'  => $formName,
            'webpath'   => $this->app['extensions']->get('Bolt/BoltForms')->getWebDirectory()->getPath(),
            'debug'     => $this->config['debug']['enabled'] || (isset($this->config[$formName]['notification']['debug']) && $this->config[$formName]['notification']['debug']),
        ];

        // If the form has it's own templates defined, use those, else the globals.
        $template = isset($this->config[$formName]['templates']['form'])
            ? $this->config[$formName]['templates']['form']
            : $this->config['templates']['form'];

        // Render the Twig_Markup
        return $boltForms->renderForm($formName, $template, $context);
    }

    /**
     * Twig function to display uploaded files, downloadable via the controller.
     *
     * @param string $formName
     *
     * @return \Twig_Markup
     */
    public function twigBoltFormsUploads($formName = null)
    {
        $dir = realpath($this->config['uploads']['base_directory'] . DIRECTORY_SEPARATOR . $formName);
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

        $context = [
            'directories' => $finder->directories(),
            'files'       => $finder->files(),
            'base_uri'    => '/' . $this->config['uploads']['base_uri'] . '/download',
            'webpath'     => $this->app['extensions']->get('bolt/boltforms')->getWebDirectory()->getPath(),
        ];

        // Render the Twig
        $this->app['twig.loader.filesystem']->addPath(dirname(dirname(__DIR__)) . '/templates');
        $html = $this->app['render']->render($this->config['templates']['files'], $context);

        return new \Twig_Markup($html, 'UTF-8');
    }
}
