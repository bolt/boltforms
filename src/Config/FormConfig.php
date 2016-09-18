<?php

namespace Bolt\Extension\Bolt\BoltForms\Config;

use Bolt\Extension\Bolt\BoltForms\Config\Section\FormOptionBag;

/**
 * Form configuration for BoltForms
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
class FormConfig
{
    /** @var Config */
    private $rootConfig;
    /** @var string */
    protected $name;
    /** @var FormOptionBag */
    protected $database;
    /** @var FormOptionBag */
    protected $feedback;
    /** @var FormOptionBag */
    protected $fields;
    /** @var FormOptionBag */
    protected $submission;
    /** @var Section\Notification */
    protected $notification;
    /** @var Section\Templates */
    protected $templates;
    /** @var Section\UploadsOptionBag */
    protected $uploads;

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

        $this->database     = new FormOptionBag($formConfig['database']);
        $this->feedback     = new FormOptionBag($formConfig['feedback']);
        $this->fields       = new FormOptionBag($formConfig['fields']);
        $this->submission   = new FormOptionBag($formConfig['submission']);
        $this->notification = new Section\Notification($formConfig['notification'], $rootConfig);
        $this->templates    = new Section\Templates($formConfig['templates'], $rootConfig);
        $this->uploads      = new FormOptionBag($formConfig['uploads']);
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
     * @return FormOptionBag
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * Get form feedback configuration object.
     *
     * @return FormOptionBag
     */
    public function getFeedback()
    {
        return $this->feedback;
    }

    /**
     * Get form fields configuration object.
     *
     * @return FormOptionBag
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Get form submission configuration object.
     *
     * @return FormOptionBag
     */
    public function getSubmission()
    {
        return $this->submission;
    }

    /**
     * Get form notification configuration object.
     *
     * @return Section\Notification
     */
    public function getNotification()
    {
        return $this->notification;
    }

    /**
     * Get form template configuration object.
     *
     * @return FormOptionBag
     */
    public function getTemplates()
    {
        return $this->templates;
    }

    /**
     * Get form upload configuration object.
     *
     * @return FormOptionBag
     */
    public function getUploads()
    {
        return $this->uploads;
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
            'fields' => [],
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
            } elseif (!empty($value)) {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }
}
