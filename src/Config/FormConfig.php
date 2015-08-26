<?php
namespace Bolt\Extension\Bolt\BoltForms\Config;

/**
 * Form configuration for BoltForms
 *
 * Copyright (C) 2014-2015 Gawain Lynch
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
 * @copyright Copyright (c) 2014, Gawain Lynch
 * @license   http://opensource.org/licenses/GPL-3.0 GNU Public License 3.0
 */
class FormConfig
{
    /** @var string */
    protected $name;
    /** @var FormConfigSection */
    protected $database;
    /** @var FormConfigSection */
    protected $feedback;
    /** @var FormConfigSection */
    protected $fields;
    /** @var FormConfigSection */
    protected $notification;
    /** @var FormConfigSection */
    protected $templates;
    /** @var FormConfigSection */
    protected $uploads;

    /**
     * @param string $formName
     * @param array  $formConfig
     */
    public function __construct($formName, array $formConfig)
    {
        $this->name = $formName;

        $defaults = $this->getDefaults();
        $formConfig = $this->mergeRecursiveDistinct($defaults, $formConfig);

        $this->database     = new FormConfigSection($formConfig['database']);
        $this->feedback     = new FormConfigSection($formConfig['feedback']);
        $this->fields       = new FormConfigSection($formConfig['fields']);
        $this->notification = new FormConfigSection($formConfig['notification']);
        $this->templates    = new FormConfigSection($formConfig['templates']);
        $this->uploads      = new FormConfigSection($formConfig['uploads']);
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
     * @return FormConfigSection
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * Get form feedback configuration object.
     *
     * @return FormConfigSection
     */
    public function getFeedback()
    {
        return $this->feedback;
    }

    /**
     * Get form fields configuration object.
     *
     * @return FormConfigSection
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Get form notification configuration object.
     *
     * @return FormConfigSection
     */
    public function getNotification()
    {
        return $this->notification;
    }

    /**
     * Get form template configuration object.
     *
     * @return FormConfigSection
     */
    public function getTemplates()
    {
        return $this->templates;
    }

    /**
     * Get form upload configuration object.
     *
     * @return FormConfigSection
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
        return array(
            'notification' => array(
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
            ),
            'feedback' => array(
                'success'  => 'Form submission sucessful',
                'error'    => 'There are errors in the form, please fix before trying to resubmit',
                'redirect' => array(
                    'target' => null,
                    'query'  => null
                )
            ),
            'database'  => array(
                'table'       => null,
                'contenttype' => null,
            ),
            'templates' => array(
                'form'    => null,
                'subject' => null,
                'email'   => null,
            ),
            'uploads' => array(
                'subdirectory' => null
            ),
            'fields' => array(),
        );
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
