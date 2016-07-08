<?php

namespace Bolt\Extension\Bolt\BoltForms\Config\Section;

use Bolt\Extension\Bolt\BoltForms\Config\Config;
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
    /** @var Config */
    private $rootConfig;

    /**
     * Constructor.
     *
     * @param array       $parameters
     * @param Config|null $rootConfig
     */
    public function __construct(array $parameters = [], Config $rootConfig = null)
    {
        parent::__construct($parameters);
        $this->rootConfig = $rootConfig;
    }

    /**
     * @return string
     */
    public function getAjax()
    {
        return $this->getHierarchicalValue('ajax');
    }

    /**
     * @param string $ajax
     *
     * @return Templates
     */
    public function setAjax($ajax)
    {
        $this->set('ajax', $ajax);

        return $this;
    }

    /**
     * @return string
     */
    public function getCss()
    {
        return $this->getHierarchicalValue('css');
    }

    /**
     * @param string $css
     *
     * @return Templates
     */
    public function setCss($css)
    {
        $this->set('css', $css);

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->getHierarchicalValue('email');
    }

    /**
     * @param string $email
     *
     * @return Templates
     */
    public function setEmail($email)
    {
        $this->set('email', $email);

        return $this;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->getHierarchicalValue('subject');
    }

    /**
     * @param string $subject
     *
     * @return Templates
     */
    public function setSubject($subject)
    {
        $this->set('subject', $subject);

        return $this;
    }

    /**
     * @return string
     */
    public function getMessages()
    {
        return $this->getHierarchicalValue('messages');
    }

    /**
     * @param string $messages
     *
     * @return Templates
     */
    public function setMessages($messages)
    {
        $this->set('messages', $messages);

        return $this;
    }

    /**
     * @return string
     */
    public function getException()
    {
        return $this->getHierarchicalValue('exception');
    }

    /**
     * @param string $exception
     *
     * @return Templates
     */
    public function setException($exception)
    {
        $this->set('exception', $exception);

        return $this;
    }

    /**
     * @return string
     */
    public function getFiles()
    {
        return $this->getHierarchicalValue('files');
    }

    /**
     * @param string $files
     *
     * @return Templates
     */
    public function setFiles($files)
    {
        $this->set('files', $files);

        return $this;
    }

    /**
     * @return string
     */
    public function getForm()
    {
        return $this->getHierarchicalValue('form');
    }

    /**
     * @param string $form
     *
     * @return Templates
     */
    public function setForm($form)
    {
        $this->set('form', $form);

        return $this;
    }

    /**
     * @return string
     */
    public function getFormTheme()
    {
        return $this->getHierarchicalValue('formtheme');
    }

    /**
     * @param string $formTheme
     *
     * @return Templates
     */
    public function setFormTheme($formTheme)
    {
        $this->set('formtheme', $formTheme);

        return $this;
    }

    /**
     * @return string
     */
    public function getFields()
    {
        return $this->getHierarchicalValue('fields');
    }

    /**
     * @param string $fields
     *
     * @return Templates
     */
    public function setFields($fields)
    {
        $this->set('fields', $fields);

        return $this;
    }

    /**
     * @return string
     */
    public function getReCaptcha()
    {
        return $this->getHierarchicalValue('recaptcha');
    }

    /**
     * @param string $reCaptcha
     *
     * @return Templates
     */
    public function setReCaptcha($reCaptcha)
    {
        $this->set('recaptcha', $reCaptcha);

        return $this;
    }

    /**
     * @return string
     */
    public function getMacros()
    {
        return $this->getHierarchicalValue('macros');
    }

    /**
     * @param string $macros
     *
     * @return Templates
     */
    public function setMacros($macros)
    {
        $this->set('macros', $macros);

        return $this;
    }

    /**
     * If there is a root configuration supplied, return its value as a default.
     *
     * @param string $key
     *
     * @return mixed
     */
    private function getHierarchicalValue($key)
    {
        if ($this->rootConfig === null) {
            return $this->get($key);
        }

        return $this->get($key) ?: $this->rootConfig->getTemplates()->get($key);
    }
}
