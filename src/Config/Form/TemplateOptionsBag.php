<?php

namespace Bolt\Extension\Bolt\BoltForms\Config\Form;

use Bolt\Extension\Bolt\BoltForms\Config\AbstractCascadingBag;

/**
 * Templates configuration object.
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
class TemplateOptionsBag extends AbstractCascadingBag
{
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
     * @return TemplateOptionsBag
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
     * @return TemplateOptionsBag
     */
    public function setCss($css)
    {
        $this->set('css', $css);

        return $this;
    }

    /**
     * @return string
     */
    public function getJs()
    {
        return $this->getHierarchicalValue('js');
    }

    /**
     * @param string $js
     *
     * @return TemplateOptionsBag
     */
    public function setJs($js)
    {
        $this->set('js', $js);

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
     * @return TemplateOptionsBag
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
     * @return TemplateOptionsBag
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
     * @return TemplateOptionsBag
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
     * @return TemplateOptionsBag
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
     * @return TemplateOptionsBag
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
     * @return TemplateOptionsBag
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
        return $this->getHierarchicalValue('form_theme');
    }

    /**
     * @param string $formTheme
     *
     * @return TemplateOptionsBag
     */
    public function setFormTheme($formTheme)
    {
        $this->set('form_theme', $formTheme);

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
     * @return TemplateOptionsBag
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
     * @return TemplateOptionsBag
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
     * @return TemplateOptionsBag
     */
    public function setMacros($macros)
    {
        $this->set('macros', $macros);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function getRootSection()
    {
        return $this->rootConfig->getTemplates();
    }
}
