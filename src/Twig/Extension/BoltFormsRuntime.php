<?php

namespace Bolt\Extension\Bolt\BoltForms\Twig\Extension;

use Bolt\Extension\Bolt\BoltForms\BoltForms;
use Bolt\Extension\Bolt\BoltForms\Config\Config;
use Bolt\Extension\Bolt\BoltForms\Config\FormConfig;
use Bolt\Extension\Bolt\BoltForms\Exception;
use Bolt\Extension\Bolt\BoltForms\Factory\FormContext;
use Bolt\Extension\Bolt\BoltForms\Form\Entity;
use Bolt\Extension\Bolt\BoltForms\Form\Type\BoltFormType;
use Bolt\Extension\Bolt\BoltForms\Submission\FeedbackTrait;
use Bolt\Extension\Bolt\BoltForms\Submission\Processor;
use Bolt\Legacy;
use Psr\Log\LoggerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig_Environment;
use Twig_Markup;

/**
 * Twig function helpers.
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
class BoltFormsRuntime
{
    use FeedbackTrait;

    /** @var Config */
    private $config;
    /** @var SessionInterface */
    private $session;
    /** @var BoltForms */
    private $boltForms;
    /** @var Processor */
    private $processor;
    /** @var callable */
    private $contextFactory;
    /** @var RequestStack */
    private $requestStack;
    /** @var LoggerInterface */
    private $logger;
    /** @var callable */
    private $recaptureResponseFactory;
    /** @var UrlGeneratorInterface */
    private $urlGenerator;
    /** @var string */
    private $webPath;
    /** @var string */
    private $rootPath;

    /**
     * Constructor.
     *
     * @param BoltForms             $boltForms
     * @param Config                $config
     * @param Processor             $processor
     * @param callable              $contextFactory
     * @param callable              $recaptureResponseFactory
     * @param SessionInterface      $session
     * @param RequestStack          $requestStack
     * @param LoggerInterface       $logger
     * @param UrlGeneratorInterface $urlGenerator
     * @param string                $webPath
     * @param string                $rootPath
     */
    public function __construct(
        BoltForms $boltForms,
        Config $config,
        Processor $processor,
        callable $contextFactory,
        callable $recaptureResponseFactory,
        SessionInterface $session,
        RequestStack $requestStack,
        LoggerInterface $logger,
        UrlGeneratorInterface $urlGenerator,
        $webPath,
        $rootPath
    ) {
        $this->boltForms = $boltForms;
        $this->config = $config;
        $this->processor = $processor;
        $this->contextFactory = $contextFactory;
        $this->recaptureResponseFactory = $recaptureResponseFactory;
        $this->session = $session;
        $this->requestStack = $requestStack;
        $this->logger = $logger;
        $this->urlGenerator = $urlGenerator;
        $this->webPath = $webPath;
        $this->rootPath = $rootPath;
    }

    /**
     * Twig function for form generation.
     *
     * @param Twig_Environment $twig
     * @param string           $formName       Name of the BoltForm to render
     * @param string           $htmlPreSubmit  HTML or template name to display BEFORE submit
     * @param string           $htmlPostSubmit HTML or template name to display AFTER successful submit
     * @param array            $data           Data array passed to Symfony Forms
     * @param array            $options        Options array passed to Symfony Forms
     * @param array            $defaults       Default field values
     * @param array            $override       Array of form parameters / fields to override settings for
     * @param mixed            $meta           Meta data that is not transmitted with the form
     * @param string           $action
     *
     * @throws \Exception
     *
     * @return Twig_Markup
     */
    public function twigBoltForms(
        Twig_Environment $twig,
        $formName,
        $htmlPreSubmit = null,
        $htmlPostSubmit = null,
        $data = null,
        $options = [],
        $defaults = null,
        $override = null,
        $meta = null,
        $action = null
    ) {
        if (!$this->config->getBaseForms()->has($formName)) {
            return new Twig_Markup(
                "<p><strong>BoltForms is missing the configuration for the form named '$formName'!</strong></p>",
                'UTF-8'
            );
        }

        // If defaults are passed in, set them in data but don't override the
        // data array that might also be passed in
        if ($data === null && $defaults !== null) {
            $data = $defaults;
        } elseif ($data instanceof Legacy\Content) {
            $data = new Entity\Content($data->getValues());
        }

        // Set form runtime overrides
        if ($override !== null) {
            $this->config->addFormOverride($formName, $override);
        }

        try {
            $this->boltForms
                ->create($formName, BoltFormType::class, $data, $options)
                ->setMeta((array) $meta)
            ;
        } catch (Exception\BoltFormsException $e) {
            return $this->handleException($formName, $e, $twig);
        }

        // Get the form's configuration object
        $formConfig = $this->config->getForm($formName);

        // Get the form context compiler
        $formContext = $this->getContextCompiler($formName);

        // Handle the POST
        $factory = $this->recaptureResponseFactory;
        $request = $this->requestStack->getCurrentRequest();
        $recaptchaEnabled = $request->request->get($formName) === null ? false : $formConfig->getRecaptcha();
        $reCaptchaResponse = $factory($recaptchaEnabled);

        try {
            $this->handleFormRequest($formConfig, $formContext, $reCaptchaResponse);
        } catch (HttpException $e) {
            throw $e;
        }

        $loadAjax = $formConfig->getSubmission()->isAjax();

        $formContext
            ->setAction($this->getRelevantAction($formName, $loadAjax, $action))
            ->setHtmlPreSubmit($this->getOptionalHtml($twig, $htmlPreSubmit))
            ->setHtmlPostSubmit($this->getOptionalHtml($twig, $htmlPostSubmit))
            ->setReCaptchaResponse($reCaptchaResponse)
            ->setDefaults((array) $defaults)
        ;

        // Save to session for AJAX requests
        if ($loadAjax) {
            $this->session->set('boltforms_compiler_' . $formName, $formContext);
        }

        return $this->getFormRender($formName, $formConfig, $formContext, $loadAjax);
    }

    /**
     * Twig function to display uploaded files, downloadable via the controller.
     *
     * @param Twig_Environment $twig
     * @param string           $formName
     *
     * @return Twig_Markup
     */
    public function twigBoltFormsUploads(Twig_Environment $twig, $formName = null)
    {
        $uploadConfig = $this->config->getUploads();
        $dir = realpath($uploadConfig->get('base_directory') . DIRECTORY_SEPARATOR . $formName);
        if ($dir === false) {
            return new Twig_Markup('<p><strong>Invalid upload directory</strong></p>', 'UTF-8');
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
            'webpath'     => $this->webPath,
        ];

        // Render the Twig
        $html = $twig->render($this->config->getTemplates()->get('files'), $context);

        return new Twig_Markup($html, 'UTF-8');
    }

    /**
     * Twig test to determine if this is a root form object
     *
     * @param FormView $formView
     * @return bool
     */
    public function twigIsRootForm(FormView $formView)
    {
        return null === $formView->parent;
    }
    /**
     * Get the context compiler either from session, or factory.
     *
     * @param string $formName
     *
     * @return FormContext
     */
    protected function getContextCompiler($formName)
    {
        /** @var FormContext $compiler */
        $compiler = $this->session->get('boltforms_compiler_' . $formName);
        if ($compiler === null) {
            $factory = $this->contextFactory;
            $compiler = $factory();
        }

        return $compiler;
    }

    /**
     * Handle request and perform relative actions.
     *
     * @param FormConfig  $formConfig
     * @param FormContext $compiler
     * @param array       $reCaptchaResponse
     */
    protected function handleFormRequest(FormConfig $formConfig, FormContext $compiler, $reCaptchaResponse)
    {
        $formName = $formConfig->getName();

        $request = $this->requestStack->getCurrentRequest();
        if ($request && $request->isMethod(Request::METHOD_POST) && $request->request->get($formName) !== null) {
            try {
                $result = $this->processor->process($formConfig, $reCaptchaResponse);
                $compiler->setResult($result);
            } catch (Exception\FileUploadException $e) {
                $this->message($e->getMessage(), Processor::FEEDBACK_ERROR);
                $this->exception($e, false, 'File upload exception: ');
            } catch (Exception\FormValidationException $e) {
                $this->message($e->getMessage(), Processor::FEEDBACK_ERROR);
                $this->exception($e, false, 'Form validation exception: ');
            } catch (HttpException $e) {
                throw $e;
            }
        } elseif ($request->isMethod(Request::METHOD_GET)) {

            // For BC on templates
            $request->attributes->set($formName, $formName);
        }
    }

    /**
     * Do the final form render.
     *
     * @param string      $formName
     * @param FormConfig  $formConfig
     * @param FormContext $compiler
     * @param bool        $loadAjax
     *
     * @return Twig_Markup
     */
    protected function getFormRender($formName, FormConfig $formConfig, FormContext $compiler, $loadAjax)
    {
        // If the form has it's own templates defined, use those, else the globals.
        $template = $formConfig->getTemplates()->getForm();
        $context = $compiler->build($this->boltForms, $this->config, $formName, $this->getFeedback());

        // Render the Twig_Markup
        return $this->boltForms->render($formName, $template, $context, $loadAjax);
    }

    /**
     * Render a form exception.
     *
     * @param string           $formName
     * @param FormContext      $compiler
     * @param Twig_Environment $twig
     *
     * @return string
     */
    protected function getExceptionRender($formName, FormContext $compiler, Twig_Environment $twig)
    {
        $template = $this->config->getTemplates()->getException();
        $context = $compiler->build($this->boltForms, $this->config, $formName, $this->getFeedback());

        return $twig->render($template, $context);
    }

    /**
     * @param Twig_Environment $twig
     * @param string           $str
     *
     * @return string
     */
    protected function getOptionalHtml(Twig_Environment $twig, $str)
    {
        $fileInfo = new \SplFileInfo($str);
        if ($fileInfo->getExtension() === 'twig' || $fileInfo->getExtension() === 'html') {
            return $twig->render($str);
        }

        return $str;
    }

    /**
     * Determine the form 'action' to be used.
     *
     * @param string $formName
     * @param bool   $loadAjax
     * @param $action
     *
     * @return string
     */
    protected function getRelevantAction($formName, $loadAjax, $action = null)
    {
        if ($loadAjax) {
            return $this->urlGenerator->generate('boltFormsAsyncSubmit', ['form' => $formName]);
        }

        if ($action !== null) {
            return $action;
        }

        return $this->requestStack->getCurrentRequest()->getRequestUri();
    }

    /**
     * Handle an exception and render something user friendly.
     *
     * @param string                       $formName
     * @param Exception\BoltFormsException $e
     * @param Twig_Environment             $twig
     *
     * @return Twig_Markup
     */
    protected function handleException($formName, Exception\BoltFormsException $e, Twig_Environment $twig)
    {
        /** @var \Exception $e */
        $this->message($this->getSafeTrace($e), 'debug');
        $this->message($e->getMessage(), 'error');

        $this->requestStack->getCurrentRequest()->request->set($formName, true);
        $compiler = $this->getContextCompiler($formName);
        $html = $this->getExceptionRender($formName, $compiler, $twig);

        return new Twig_Markup($html, 'UTF-8');
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
        $rootDir = $this->rootPath;
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

    /**
     * {@inheritdoc}
     */
    protected function getFeedback()
    {
        /** @var FlashBagInterface $feedback */
        $feedback = $this->session->getBag('boltforms');

        return $feedback;
    }

    /**
     * {@inheritdoc}
     */
    protected function getLogger()
    {
        return $this->logger;
    }
}
