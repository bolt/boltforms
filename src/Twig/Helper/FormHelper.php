<?php

namespace Bolt\Extension\Bolt\BoltForms\Twig\Helper;

use Bolt\Extension\Bolt\BoltForms\BoltForms;
use Bolt\Extension\Bolt\BoltForms\Config\Config;
use Bolt\Extension\Bolt\BoltForms\Config\FormConfig;
use Bolt\Extension\Bolt\BoltForms\Exception;
use Bolt\Extension\Bolt\BoltForms\Factory\FormContext;
use Bolt\Extension\Bolt\BoltForms\Submission\Processor;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Twig function helpers.
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
class FormHelper
{
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
    /** @var FlashBag */
    private $feedback;
    /** @var RequestStack */
    private $requestStack;
    /** @var LoggerInterface */
    private $loggerSystem;

    /**
     * Constructor.
     *
     * @param BoltForms        $boltForms
     * @param Config           $config
     * @param Processor        $processor
     * @param callable         $contextFactory
     * @param FlashBag         $feedback
     * @param SessionInterface $session
     * @param RequestStack     $requestStack
     * @param LoggerInterface  $loggerSystem
     */
    public function __construct(
        BoltForms $boltForms,
        Config $config,
        Processor $processor,
        callable $contextFactory,
        FlashBag $feedback,
        SessionInterface $session,
        RequestStack $requestStack,
        LoggerInterface $loggerSystem
    ) {
        $this->boltForms = $boltForms;
        $this->config = $config;
        $this->processor = $processor;
        $this->contextFactory = $contextFactory;
        $this->feedback = $feedback;
        $this->session = $session;
        $this->requestStack = $requestStack;
        $this->loggerSystem = $loggerSystem;
    }

    /**
     * Get the context compiler either from session, or factory.
     *
     * @param string $formName
     *
     * @return FormContext
     */
    public function getContextCompiler($formName)
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
    public function handleFormRequest(FormConfig $formConfig, FormContext $compiler, $reCaptchaResponse)
    {
        $formName = $formConfig->getName();
        $sessionKey = sprintf('boltforms_submit_%s', $formName);

        $request = $this->requestStack->getCurrentRequest();
        if ($request && $request->isMethod(Request::METHOD_POST) && $request->request->get($formName) !== null) {
            try {
                $sent = $this->processor->process($formConfig, $reCaptchaResponse);
                $compiler->setSent($sent);
            } catch (Exception\FileUploadException $e) {
                $this->feedback->add(Processor::FEEDBACK_ERROR, $e->getMessage());
                $this->loggerSystem->debug($e->getSystemMessage(), ['event' => 'extensions']);
            } catch (Exception\FormValidationException $e) {
                $this->feedback->add(Processor::FEEDBACK_ERROR, $e->getMessage());
                $this->loggerSystem->debug(
                    '[BoltForms] Form validation exception: ' . $e->getMessage(),
                    ['event' => 'extensions']
                );
            } catch (HttpException $e) {
                throw $e;
            }
        } elseif ($request->isMethod(Request::METHOD_GET)) {
            $compiler->setSent($this->session->get($sessionKey));

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
     * @return \Twig_Markup
     */
    public function getFormRender($formName, FormConfig $formConfig, FormContext $compiler, $loadAjax)
    {
        // If the form has it's own templates defined, use those, else the globals.
        $template = $formConfig->getTemplates()->getForm();
        $context = $compiler->build($this->boltForms, $this->config, $formName, $this->feedback);

        // Render the Twig_Markup
        return $this->boltForms->render($formName, $template, $context, $loadAjax);
    }

    /**
     * Render a form exception.
     *
     * @param string            $formName
     * @param FormContext       $compiler
     * @param \Twig_Environment $twig
     *
     * @return string
     */
    public function getExceptionRender($formName, FormContext $compiler, \Twig_Environment $twig)
    {
        $template = $this->config->getTemplates()->getException();
        $context = $compiler->build($this->boltForms, $this->config, $formName, $this->feedback);

        return $twig->render($template, $context);
    }

    /**
     * @param \Twig_Environment $twig
     * @param string            $str
     *
     * @return string
     */
    public function getOptionalHtml(\Twig_Environment $twig, $str)
    {
        $fileInfo = new \SplFileInfo($str);
        if ($fileInfo->getExtension() === 'twig' || $fileInfo->getExtension() === 'html') {
            return $twig->render($str);
        }

        return $str;
    }
}
