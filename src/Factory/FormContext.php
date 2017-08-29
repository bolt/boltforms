<?php

namespace Bolt\Extension\Bolt\BoltForms\Factory;

use Bolt\Extension\Bolt\BoltForms\BoltForms;
use Bolt\Extension\Bolt\BoltForms\Config\Config;
use Bolt\Extension\Bolt\BoltForms\Submission\Result;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

/**
 * Form context compiler.
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
class FormContext
{
    /** @var string */
    protected $webPath;

    /** @var string */
    protected $action;
    /** @var array */
    protected $defaults;
    /** @var string */
    protected $htmlPreSubmit;
    /** @var string */
    protected $htmlPostSubmit;
    /** @var Result */
    protected $result;
    /** @var array */
    protected $reCaptchaResponse;

    /**
     * Constructor.
     *
     * @param string $webPath
     */
    public function __construct($webPath)
    {
        $this->webPath = $webPath;
    }

    /**
     * @param BoltForms         $boltForms
     * @param Config            $config
     * @param string            $formName
     * @param FlashBagInterface $feedBack
     *
     * @return array
     */
    public function build(BoltForms $boltForms, Config $config, $formName, FlashBagInterface $feedBack)
    {
        // reCaptcha configuration
        $reCaptchaConfig = $config->getReCaptcha();

        $info = $feedBack->get('info', []);
        $errors = $feedBack->get('error', []);
        $debugs = $feedBack->get('debug', []);

        /** @var Form[] $fields Values to be passed to Twig */
        $fields = $boltForms->get($formName)->getForm()->all();
        $context = [
            'fields'    => $fields,
            'defaults'  => $this->defaults,
            'html_pre'  => $this->htmlPreSubmit,
            'html_post' => $this->htmlPostSubmit,
            'messages'  => [
                'info'  => $info,
                'error' => $errors,
                'debug' => $debugs,
            ],
            'sent'      => $this->result ? $this->result->isPass('email') : false,
            'result'    => $this->result ?: new Result(),
            'templates' => $config->getForm($formName)->getTemplates(),
            'recaptcha' => [
                'enabled'        => $reCaptchaConfig->isEnabled() && $config->getForm($formName)->getReCaptcha() !== false,
                'label'          => $reCaptchaConfig->getLabel(),
                'public_key'     => $reCaptchaConfig->getPublicKey(),
                'theme'          => $reCaptchaConfig->getTheme(),
                'type'           => $reCaptchaConfig->getType(),
                'error_message'  => $reCaptchaConfig->getErrorMessage(),
                'badge_location' => $reCaptchaConfig->getBadgeLocation(),
                'error_codes'    => $this->reCaptchaResponse ? $this->reCaptchaResponse['errorCodes'] : null,
                'valid'          => $this->reCaptchaResponse ? $this->reCaptchaResponse['success'] : null,
            ],
            'formname'         => $formName,
            'form_start_param' => [
                'attr' => [
                    'name' => $formName,
                ],
                'method' => 'POST',
                'action' => $this->action,
            ],
            'webpath'   => $this->webPath,
            'debug'     => $config->getForm($formName)->getNotification()->isDebug(),
        ];

        return $context;
    }

    /**
     * @param string $action
     *
     * @return FormContext
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * @param array $defaults
     *
     * @return FormContext
     */
    public function setDefaults(array $defaults)
    {
        $this->defaults = $defaults;

        return $this;
    }

    /**
     * @param string $htmlPreSubmit
     *
     * @return FormContext
     */
    public function setHtmlPreSubmit($htmlPreSubmit)
    {
        $this->htmlPreSubmit = $htmlPreSubmit;

        return $this;
    }

    /**
     * @param string $htmlPostSubmit
     *
     * @return FormContext
     */
    public function setHtmlPostSubmit($htmlPostSubmit)
    {
        $this->htmlPostSubmit = $htmlPostSubmit;

        return $this;
    }

    /**
     * @param Result $result
     *
     * @return FormContext
     */
    public function setResult(Result $result)
    {
        $this->result = $result;

        return $this;
    }

    /**
     * @param array $reCaptchaResponse
     *
     * @return FormContext
     */
    public function setReCaptchaResponse($reCaptchaResponse)
    {
        $this->reCaptchaResponse = $reCaptchaResponse;

        return $this;
    }
}
