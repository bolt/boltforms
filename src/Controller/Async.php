<?php

namespace Bolt\Extension\Bolt\BoltForms\Controller;

use Bolt\Extension\Bolt\BoltForms\BoltForms;
use Bolt\Extension\Bolt\BoltForms\Config\Config;
use Bolt\Extension\Bolt\BoltForms\Exception\FileUploadException;
use Bolt\Extension\Bolt\BoltForms\Exception\FormValidationException;
use Bolt\Extension\Bolt\BoltForms\Twig\FormContext;
use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Asynchronous route handling.
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
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 */
class Async implements ControllerProviderInterface
{
    /** @var Config */
    private $config;

    /**
     * Constructor.
     *
     * @param $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function connect(Application $app)
    {
        /** @var $ctr \Silex\ControllerCollection */
        $ctr = $app['controllers_factory'];

        $ctr->match('submit', [$this, 'submit'])
            ->bind('boltFormsAsyncSubmit')
            ->method(Request::METHOD_POST);

        return $ctr;
    }

    /**
     * @param Application $app
     * @param Request     $request
     *
     * @return JsonResponse
     */
    public function submit(Application $app, Request $request)
    {
        $formName = $request->query->get('form', null);
        if ($formName === null) {
            return new JsonResponse(['Invalid form'], Response::HTTP_BAD_REQUEST);
        }

        /** @var FormContext $compiler */
        $compiler = $app['session']->get('boltforms_compiler_' . $formName);
        if ($compiler === null) {
            return new JsonResponse(['Invalid compiler'], Response::HTTP_BAD_REQUEST);
        }

        $sent = false;

        /** @var BoltForms $boltForms */
        $boltForms = $app['boltforms'];

        $boltForms->makeForm($formName, FormType::class, [], []);
        $formConfig = $boltForms->getFormConfig($formName);
        $fields = $formConfig->getFields();
        $boltForms->addFieldArray($formName, $fields->toArray());
        $formConfig = $boltForms->getFormConfig($formName);

        $reCaptchaResponse = $app['boltforms.processor']->reCaptchaResponse($request);

        try {
            $sent = $app['boltforms.processor']->process($formName, null, $reCaptchaResponse);
        } catch (FileUploadException $e) {
            $app['boltforms.feedback']->add('error', $e->getMessage());
            $app['logger.system']->debug($e->getSystemMessage(), ['event' => 'extensions']);
        } catch (FormValidationException $e) {
            $app['boltforms.feedback']->add('error', $e->getMessage());
            $app['logger.system']->debug('[BoltForms] Form validation exception: ' . $e->getMessage(), ['event' => 'extensions']);
        } catch (FileException $e) {
            $app['boltforms.feedback']->add('debug', $e->getMessage());
            $app['logger.system']->error($e->getMessage(), ['event' => 'extensions']);
        }

        $compiler->setSent($sent);
        $context = $compiler->build($boltForms, $formName, $app['boltforms.feedback']);
        $template = $formConfig->getTemplates()->getForm() ?: $this->config->getTemplates()->get('form');

        // Render the Twig_Markup
        return $boltForms->renderForm($formName, $template, $context, false);
    }
}
