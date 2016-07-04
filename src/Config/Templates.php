<?php

namespace Bolt\Extension\Bolt\BoltForms\Config;

use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Templates configuration object.
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
class Templates extends ParameterBag
{
    /** @var string */
    protected $ajax;
    /** @var string */
    protected $css;
    /** @var string */
    protected $email;
    /** @var string */
    protected $subject;
    /** @var string */
    protected $messages;
    /** @var string */
    protected $exception;
    /** @var string */
    protected $files;
    /** @var string */
    protected $form;
    /** @var string */
    protected $formtheme;
    /** @var string */
    protected $fields;
    /** @var string */
    protected $recaptcha;
    /** @var string */
    protected $macros;

    /**
     * Constructor.
     *
     * @param array $parameters
     */
    public function __construct(array $parameters = [])
    {
        parent::__construct($parameters);
        foreach ($parameters as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    /**
     * Get a global template name.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getGlobal($name)
    {
        return $this->get($name);
    }

    /**
     * @return string
     */
    public function getAjax()
    {
        return $this->ajax;
    }

    /**
     * @param string $ajax
     *
     * @return Templates
     */
    public function setAjax($ajax)
    {
        $this->ajax = $ajax;

        return $this;
    }

    /**
     * @return string
     */
    public function getCss()
    {
        return $this->css;
    }

    /**
     * @param string $css
     *
     * @return Templates
     */
    public function setCss($css)
    {
        $this->css = $css;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return Templates
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     *
     * @return Templates
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return string
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @param string $messages
     *
     * @return Templates
     */
    public function setMessages($messages)
    {
        $this->messages = $messages;

        return $this;
    }

    /**
     * @return string
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * @param string $exception
     *
     * @return Templates
     */
    public function setException($exception)
    {
        $this->exception = $exception;

        return $this;
    }

    /**
     * @return string
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @param string $files
     *
     * @return Templates
     */
    public function setFiles($files)
    {
        $this->files = $files;

        return $this;
    }

    /**
     * @return string
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @param string $form
     *
     * @return Templates
     */
    public function setForm($form)
    {
        $this->form = $form;

        return $this;
    }

    /**
     * @return string
     */
    public function getFormTheme()
    {
        return $this->formtheme;
    }

    /**
     * @param string $formTheme
     *
     * @return Templates
     */
    public function setFormTheme($formTheme)
    {
        $this->formtheme = $formTheme;

        return $this;
    }

    /**
     * @return string
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param string $fields
     *
     * @return Templates
     */
    public function setFields($fields)
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * @return string
     */
    public function getReCaptcha()
    {
        return $this->recaptcha;
    }

    /**
     * @param string $reCaptcha
     *
     * @return Templates
     */
    public function setReCaptcha($reCaptcha)
    {
        $this->recaptcha = $reCaptcha;

        return $this;
    }

    /**
     * @return string
     */
    public function getMacros()
    {
        return $this->macros;
    }

    /**
     * @param string $macros
     *
     * @return Templates
     */
    public function setMacros($macros)
    {
        $this->macros = $macros;

        return $this;
    }
}
