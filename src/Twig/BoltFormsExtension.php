<?php

namespace Bolt\Extension\Bolt\BoltForms\Twig;

use Bolt\Extension\Bolt\BoltForms\BoltForms;
use Bolt\Extension\Bolt\BoltForms\Config\Config;
use Bolt\Extension\Bolt\BoltForms\Exception;
use Bolt\Extension\Bolt\BoltForms\Twig\Helper\FormHelper;
use Silex\Application;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Twig functions for BoltForms
 *
 * Copyright (c) 2014-2016 Gawain Lynch
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
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
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
     * @param array  $override
     *
     * @return \Twig_Markup
     */
    public function twigBoltForms($formName, $htmlPreSubmit = '', $htmlPostSubmit = '', $data = [], $options = [], $defaults = [], $override = null)
    {
        if (!$this->config->getBaseForms()->has($formName)) {
            return new \Twig_Markup(
                "<p><strong>BoltForms is missing the configuration for the form named '$formName'!</strong></p>",
                'UTF-8'
            );
        }

        // Set field overrides
        $this->config->addFormOverride($formName, ['fields' => $override]);

        /** @var FormHelper $formHelper */
        $formHelper = $this->app['boltforms.twig.helper']['form'];
        /** @var RequestStack $requestStack */
        $requestStack = $this->app['request_stack'];
        /** @var BoltForms $boltForms */
        $boltForms = $this->app['boltforms'];
        /** @var Session $session */
        $session = $this->app['session'];
        $feedback = $this->app['boltforms.feedback'];

        $reCaptchaResponse = null;
        $formConfig = null;

        try {
            $boltForms->makeForm($formName, FormType::class, $data, $options);
            $formConfig = $this->config->getForm($formName);
        } catch (Exception\BoltFormsException $e) {
            if ($formConfig === null) {
                $feedback->add('debug', $this->getSafeTrace($e));
                $feedback->add('error', $e->getMessage());

                $requestStack->getCurrentRequest()->request->set($formName, true);
                $compiler = $formHelper->getContextCompiler($formName);
                $html = $formHelper->getExceptionRender($formName, $compiler, $this->app['twig']);

                return new \Twig_Markup($html, 'UTF-8');
            }
        }

        // Get the context compiler
        $compiler = $formHelper->getContextCompiler($formName);

        // Handle the POST
        $formHelper->handleFormRequest($formName, $compiler);

        $loadAjax = $formConfig->getSubmission()->getAjax();

        $compiler
            ->setAction($this->getRelevantAction($formName, $loadAjax))
            ->setHtmlPreSubmit($htmlPreSubmit)
            ->setHtmlPostSubmit($htmlPostSubmit)
            ->setReCaptchaResponse($reCaptchaResponse)
            ->setDefaults($defaults)
        ;
        $session->set('boltforms_compiler_' . $formName, $compiler);

        return $formHelper->getFormRender($formName, $formConfig, $compiler, $loadAjax);
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
        $html = $this->app['twig']->render($this->config->getTemplates()->get('files'), $context);

        return new \Twig_Markup($html, 'UTF-8');
    }

    /**
     * Determine the form 'action' to be used.
     *
     * @param string $formName
     * @param bool   $loadAjax
     *
     * @return string
     */
    protected function getRelevantAction($formName, $loadAjax)
    {
        if ($loadAjax) {
            return $this->app['url_generator']->generate('boltFormsAsyncSubmit', ['form' => $formName]);
        }

        /** @var RequestStack $requestStack */
        $requestStack = $this->app['request_stack'];

        return $requestStack->getCurrentRequest()->getRequestUri();
    }

    /**
     * Remove the root path from the trace.
     *
     * @param \Exception $e
     *
     * @return string
     */
    protected function getSafeTrace(\Exception $e)
    {
        $rootDir = $this->app['resources']->getPath('root');
        $trace = explode("\n", $e->getTraceAsString());
        $trace = array_slice($trace, 0, 10);
        $trace = implode("\n", $trace);
        $trace = str_replace($rootDir, '{root}', $trace);
        $message = sprintf(
            "%s\n%s",
            $e->getMessage(),
            $trace
        );

        return $message;
    }
}
