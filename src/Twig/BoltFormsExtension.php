<?php

namespace Bolt\Extension\Bolt\BoltForms\Twig;

use Bolt\Extension\Bolt\BoltForms\BoltForms;
use Bolt\Extension\Bolt\BoltForms\Config\Config;
use Bolt\Extension\Bolt\BoltForms\Exception\FileUploadException;
use Bolt\Extension\Bolt\BoltForms\Exception\FormValidationException;
use Silex\Application;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Twig functions for BoltForms
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
 * @copyright Copyright (c) 2014, Gawain Lynch
 * @license   http://opensource.org/licenses/GPL-3.0 GNU Public License 3.0
 */
class BoltFormsExtension
{
    /** @var Application */
    private $app;
    /** @var Config */
    private $config;

    public function __construct(Application $app, Config $config)
    {
        $this->app    = $app;
        $this->config = $config;
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
        if (!$this->config->has($formName)) {
            return new \Twig_Markup("<p><strong>BoltForms is missing the configuration for the form named '$formName'!</strong></p>", 'UTF-8');
        }

        /** @var BoltForms $boltForms */
        $boltForms = $this->app['boltforms'];
        $session = $this->app['session'];

        $sent = false;
        $reCaptchaResponse = null;

        $boltForms->makeForm($formName, FormType::class, $data, $options);
        $formConfig = $boltForms->getFormConfig($formName);
        $loadAjax = $formConfig->getSubmission()->getAjax();
        $fields = $formConfig->getFields();

        // Add our fields all at once
        $boltForms->addFieldArray($formName, $fields->toArray());

        /** @var FormContext $compiler */
        $compiler = $session->get('boltforms_compiler_' . $formName);
        if ($compiler === null) {
            $compiler = $this->app['boltforms.form.context.factory']();
        }

        // Handle the POST
        $request = $this->app['request_stack']->getCurrentRequest();
        if ($request && $request->isMethod(Request::METHOD_POST) && $request->request->get($formName) !== null) {
            // Check reCaptcha, if enabled.
            $reCaptchaResponse = $this->app['boltforms.processor']->reCaptchaResponse($request);

            try {
                $sent = $this->app['boltforms.processor']->process($formName, null, $reCaptchaResponse);
            } catch (FileUploadException $e) {
                $this->app['boltforms.feedback']->add('error', $e->getMessage());
                $this->app['logger.system']->debug($e->getSystemMessage(), ['event' => 'extensions']);
            } catch (FormValidationException $e) {
                $this->app['boltforms.feedback']->add('error', $e->getMessage());
                $this->app['logger.system']->debug('[BoltForms] Form validation exception: ' . $e->getMessage(), ['event' => 'extensions']);
            } catch (FileException $e) {
                $this->app['boltforms.feedback']->add('debug', $e->getMessage());
                $this->app['logger.system']->error($e->getMessage(), ['event' => 'extensions']);
            }
        } elseif ($request->isMethod(Request::METHOD_GET)) {
            $sessionKey = sprintf('boltforms_submit_%s', $formName);
            $sent = $session->get($sessionKey);

            // For BC on templates
            $request->attributes->set($formName, $formName);
        }

        $compiler
            ->setAction($loadAjax ? $this->app['url_generator']->generate('boltFormsAsyncSubmit', ['form' => $formName]) : $request->getRequestUri())
            ->setHtmlPreSubmit($htmlPreSubmit)
            ->setHtmlPostSubmit($htmlPostSubmit)
            ->setSent($sent)
            ->setReCaptchaResponse($reCaptchaResponse)
            ->setDefaults($defaults)
        ;
        $session->set('boltforms_compiler_' . $formName, $compiler);

        // If the form has it's own templates defined, use those, else the globals.
        $template = $formConfig->getTemplates()->getForm() ?: $this->config->getTemplates()->get('form');
        $context = $compiler->build($boltForms, $formName, $this->app['boltforms.feedback']);

        // Render the Twig_Markup
        return $boltForms->renderForm($formName, $template, $context, $loadAjax);
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
        $uploadConfig = $this->config->getUploads();
        $dir = realpath($uploadConfig->get('base_directory') . DIRECTORY_SEPARATOR . $formName);
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
            'base_uri'    => '/' . $uploadConfig->get('base_uri') . '/download',
            'webpath'     => $this->app['extensions']->get('bolt/boltforms')->getWebDirectory()->getPath(),
        ];

        // Render the Twig
        $this->app['twig.loader.filesystem']->addPath(dirname(dirname(__DIR__)) . '/templates');
        $html = $this->app['render']->render($this->config->getTemplates()->get('files'), $context);

        return new \Twig_Markup($html, 'UTF-8');
    }
}
