<?php

namespace Bolt\Extension\Bolt\BoltForms\Config;
use Bolt\Extension\Bolt\BoltForms\Config\Form\FormOptionsBag;

/**
 * Form configuration for BoltForms
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
class FormConfig
{
    /** @var string */
    protected $name;
    /** @var Form\DatabaseOptionsBag */
    protected $database;
    /** @var Form\FeedbackOptionsBag */
    protected $feedback;
    /** @var Form\FieldOptionsBag */
    protected $fields;
    /** @var Form\SubmissionOptionsBag */
    protected $submission;
    /** @var Form\NotificationOptionsBag */
    protected $notification;
    /** @var Form\TemplateOptionsBag */
    protected $templates;
    /** @var Form\UploadsOptionsBag */
    protected $uploads;
    /** @var string */
    protected $formRecaptcha;
    /** @var FormOptionsBag */
    private $options;

    /** @var Config */
    private $rootConfig;

    /**
     * Constructor.
     *
     * @param string $formName
     * @param array  $formConfig
     * @param Config $rootConfig
     */
    public function __construct($formName, array $formConfig, Config $rootConfig)
    {
        $this->name = $formName;
        $this->rootConfig = $rootConfig;

        $defaults = $this->getDefaults();
        $formConfig = $this->mergeRecursiveDistinct($defaults, $formConfig);

        $this->feedback     = new Form\FeedbackOptionsBag($formConfig['feedback']);
        $this->fields       = new Form\FieldsBag($formConfig['fields']);
        $this->database     = new Form\DatabaseOptionsBag($formConfig['database'], $this->fields);
        $this->notification = new Form\NotificationOptionsBag($formConfig['notification'], $rootConfig);
        $this->submission   = new Form\SubmissionOptionsBag($formConfig['submission']);
        $this->templates    = new Form\TemplateOptionsBag($formConfig['templates'], $rootConfig);
        $this->uploads      = new Form\UploadsOptionsBag($formConfig['uploads']);
        $this->options      = new Form\FormOptionsBag($formConfig['options']);
        $this->formRecaptcha = $formConfig['recaptcha'] == false ? false : true;
    }

    /**
     * @return Config
     */
    public function getRootConfig()
    {
        return $this->rootConfig;
    }

    /**
     * Get form name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get form database configuration object.
     *
     * @return Form\DatabaseOptionsBag
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * Get form feedback configuration object.
     *
     * @return Form\FeedbackOptionsBag
     */
    public function getFeedback()
    {
        return $this->feedback;
    }

    /**
     * Get form fields configuration object.
     *
     * @return Form\FieldOptionsBag
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Get form submission configuration object.
     *
     * @return Form\SubmissionOptionsBag
     */
    public function getSubmission()
    {
        return $this->submission;
    }

    /**
     * Get form notification configuration object.
     *
     * @return Form\NotificationOptionsBag
     */
    public function getNotification()
    {
        return $this->notification;
    }

    /**
     * Get form template configuration object.
     *
     * @return Form\TemplateOptionsBag
     */
    public function getTemplates()
    {
        return $this->templates;
    }

    /**
     * Get form upload configuration object.
     *
     * @return Form\UploadsOptionsBag
     */
    public function getUploads()
    {
        return $this->uploads;
    }

    /**
     * Get form recaptcha status.
     *
     * @return bool
     */
    public function getRecaptcha()
    {
        return $this->formRecaptcha;
    }

    /**
     * Get form options.
     *
     * @return FormOptionsBag
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * A set of default keys for a form's config.
     *
     * @return array
     */
    protected function getDefaults()
    {
        return [
            'submission'   => [
                'ajax' => false,
            ],
            'notification' => [
                'enabled'       => false,
                'debug'         => false,
                'subject'       => 'Your message was submitted',
                'from_name'     => null,
                'from_email'    => null,
                'replyto_name'  => null,
                'replyto_email' => null,
                'to_name'       => null,
                'to_email'      => null,
                'cc_name'       => null,
                'cc_email'      => null,
                'bcc_name'      => null,
                'bcc_email'     => null,
                'attach_files'  => false,
            ],
            'feedback' => [
                'success'  => 'Form submission successful',
                'error'    => 'There are errors in the form, please fix before trying to resubmit',
                'redirect' => [
                    'target' => null,
                    'query'  => null,
                ],
            ],
            'database'  => [
                'table'       => null,
                'contenttype' => null,
            ],
            'templates' => [
                'form'    => null,
                'subject' => null,
                'email'   => null,
            ],
            'uploads' => [
                'subdirectory' => null,
            ],
            'fields'    => [],
            'recaptcha' => true,
        ];
    }

    /**
     * Customised array merging function.
     *
     * @param array $array1
     * @param array $array2
     *
     * @return array
     */
    private function mergeRecursiveDistinct(array &$array1, array &$array2)
    {
        $merged = $array1;

        foreach ($array2 as $key => &$value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = self::mergeRecursiveDistinct($merged[$key], $value);
            } elseif (!empty($value) || $value == false) {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }
}
