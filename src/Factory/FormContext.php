<?php

namespace Bolt\Extension\Bolt\BoltForms\Factory;

use Bolt\Extension\Bolt\BoltForms\BoltForms;
use Bolt\Extension\Bolt\BoltForms\Config\Config;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;

/**
 * Form context compiler.
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
    /** @var bool */
    protected $sent = false;
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
     * @param BoltForms $boltForms
     * @param Config    $config
     * @param string    $formName
     * @param FlashBag  $feedBack
     *
     * @return array
     */
    public function build(BoltForms $boltForms, Config $config, $formName, FlashBag $feedBack)
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
            'error'     => !empty($errors) ? $errors[0] : null, // @deprecated
            'message'   => !empty($info) ? $info[0] : null,     // @deprecated
            'messages'  => [
                'info'  => $info,
                'error' => $errors,
                'debug' => $debugs,
            ],
            'sent'      => $this->sent,
            'recaptcha' => [
                'enabled'       => $reCaptchaConfig->get('enabled'),
                'label'         => $reCaptchaConfig->get('label'),
                'public_key'    => $reCaptchaConfig->get('public_key'),
                'theme'         => $reCaptchaConfig->get('theme'),
                'error_message' => $reCaptchaConfig->get('error_message'),
                'error_codes'   => $this->reCaptchaResponse ? $this->reCaptchaResponse['errorCodes'] : null,
                'valid'         => $this->reCaptchaResponse ? $this->reCaptchaResponse['success'] : null,
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
            'debug'     => $config->getDebug()->get('enabled') || $config->getForm($formName)->getNotification()->getDebug(),
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
     * @param boolean $sent
     *
     * @return FormContext
     */
    public function setSent($sent)
    {
        $this->sent = $sent;

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
