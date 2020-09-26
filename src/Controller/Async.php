<?php

namespace Bolt\Extension\Bolt\BoltForms\Controller;

use Bolt\Controller\Backend\Async\AsyncZoneInterface;
use Bolt\Extension\Bolt\BoltForms\BoltForms;
use Bolt\Extension\Bolt\BoltForms\Config;
use Bolt\Extension\Bolt\BoltForms\Exception\FileUploadException;
use Bolt\Extension\Bolt\BoltForms\Exception\FormValidationException;
use Bolt\Extension\Bolt\BoltForms\Factory\FormContext;
use Bolt\Extension\Bolt\BoltForms\Form\Type\BoltFormType;
use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Asynchronous route handling.
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
class Async implements AsyncZoneInterface
{

    /** @var BoltForms */
    private $boltForms;

    /**  @var SessionInterface */
    private $session;

    public function __construct(BoltForms $boltForms, SessionInterface $session)
    {
        $this->boltForms = $boltForms;
        $this->session = $session;
    }

    /**
     * @param Request     $request
     *
     * @return JsonResponse|\Twig_Markup
     */
    public function submit(Request $request)
    {
        $formName = $request->query->get('form', null);
        if ($formName === null) {
            return new JsonResponse(['Invalid form'], Response::HTTP_BAD_REQUEST);
        }

        /** @var FormContext $formContext */
        $formContext = $this->session->get('boltforms_compiler_' . $formName);
        if ($formContext === null) {
            return new JsonResponse(['Invalid compiler'], Response::HTTP_BAD_REQUEST);
        }

        $meta = $this->session->get(BoltForms::META_FIELD_NAME);
        $this->session->remove(BoltForms::META_FIELD_NAME);

        $this->boltForms
            ->create($formName, BoltFormType::class, [], [])
            ->setMeta($meta)
        ;
        /** @var Config\Config $config */
        $config = $this->boltForms->getConfig();
        /** @var Config\FormConfig $formConfig */
        $formConfig = $config->getForm($formName);

        try {
            $result = $app['boltforms.processor']->process($formConfig, $app['recapture.response.factory']());
            $formContext->setResult($result);
        } catch (FileUploadException $e) {
            $app['boltforms.feedback']->add('error', $e->getMessage());
            $app['logger.system']->debug($e->getSystemMessage(), ['event' => 'extensions']);
        } catch (FormValidationException $e) {
            $app['boltforms.feedback']->add('error', $e->getMessage());
            $app['logger.system']->debug('[BoltForms] Form validation exception: ' . $e->getMessage(), ['event' => 'extensions']);
        }

        $context = $formContext->build($this->boltForms, $config, $formName, $app['boltforms.feedback']);
        $template = $config->getForm($formName)->getTemplates()->getForm();

        // Render the Twig_Markup
        $output = $this->boltForms->render($formName, $template, $context, false);

        // Because this handles the response via ajax, we don't want the feedback to persist over another request so
        // we clear it here after the ajax response has been rendered.
        $app['boltforms.feedback']->clear();

        return $output;
    }
}
