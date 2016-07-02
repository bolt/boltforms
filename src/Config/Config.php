<?php

namespace Bolt\Extension\Bolt\BoltForms\Config;

use Bolt\Extension\Bolt\BoltForms\Exception;
use Bolt\Helpers\Arr;
use Bolt\Storage\EntityManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * General configuration.
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
class Config extends ParameterBag
{
    /** @var ParameterBag */
    protected $baseForms;
    /** @var ParameterBag */
    protected $resolvedForms;

    /**
     * Constructor.
     *
     * @param array $parameters
     */
    public function __construct(array $parameters = [])
    {
        parent::__construct();
        $nonForms = ['csrf', 'debug', 'fieldmap', 'recaptcha', 'templates', 'uploads'];
        $this->baseForms = new ParameterBag();
        $this->resolvedForms = new ParameterBag();

        foreach ($parameters as $key => $value) {
            if ($value instanceof FieldMap\Email) {
                $this->set($key, $value);
            } elseif (is_array($value)) {
                if (in_array($key, $nonForms)) {
                    $this->set($key, new ParameterBag($value));
                } else {
                    $this->baseForms->set($key, new ParameterBag($value));
                }
            } else {
                $this->set($key, $value);
            }
        }
    }

    /**
     * @return boolean
     */
    public function isCsrf()
    {
        return $this->get('csrf');
    }

    /**
     * @return ParameterBag
     */
    public function getReCaptcha()
    {
        return $this->get('recaptcha');
    }

    /**
     * @return ParameterBag
     */
    public function getTemplates()
    {
        return $this->get('templates');
    }

    /**
     * @return ParameterBag
     */
    public function getDebug()
    {
        return $this->get('debug');
    }

    /**
     * @return ParameterBag
     */
    public function getUploads()
    {
        return $this->get('uploads');
    }

    /**
     * @return FieldMap\Email
     */
    public function getFieldMap()
    {
        return $this->get('fieldmap');
    }

    /**
     * Return the base configuration of a form.
     *
     * @param string $formName
     *
     * @return ParameterBag
     */
    public function getBaseForm($formName)
    {
        return $this->baseForms->get($formName);
    }

    /**
     * Return the base configuration of all forms.
     *
     * @return ParameterBag
     */
    public function getBaseForms()
    {
        return $this->baseForms;
    }

    /**
     * Override a section of a form's configuration.
     *
     * @param string $formName
     * @param array  $overrides
     */
    public function addFormOverride($formName, array $overrides)
    {
        if ($this->baseForms->has($formName)) {
            $orig = $this->baseForms->get($formName)->all();
        } else {
            throw new Exception\UnknownFormException(sprintf('Unknown form requested: %s', $formName));
        }

        foreach ($overrides as $key => $value) {
            if (isset($orig['fields'][$key])) {
                $normalisedOverrides['fields'][$key] = $value;
            } else {
                $normalisedOverrides[$key] = $value;
            }
        }

        $new = Arr::mergeRecursiveDistinct($orig, $normalisedOverrides);
        $this->baseForms->set($formName, new ParameterBag($new));
    }

    /**
     * Get the configuration object for a form.
     *
     * @param string $formName
     *
     * @return FormConfig
     */
    public function getForm($formName)
    {
        if (!$this->baseForms->has($formName)) {
            throw new Exception\UnknownFormException(sprintf('Unknown form requested: %s', $formName));
        }

        if (!$this->resolvedForms->has($formName)) {
            throw new Exception\UnknownFormException(sprintf('Unresolved form requested: %s', $formName));
        }

        return $this->resolvedForms->get($formName);
    }

    /**
     * Get the configuration objects for all forms.
     *
     * @return ParameterBag
     */
    public function getForms()
    {
        return $this->baseForms;
    }

    /**
     * Resolve a form's configuration.
     *
     * @param string                   $formName
     * @param EntityManager            $em
     * @param EventDispatcherInterface $dispatcher
     *
     * @throws Exception\FormOptionException
     */
    public function resolveForm($formName, EntityManager $em, EventDispatcherInterface $dispatcher)
    {
        if (!$this->baseForms->has($formName)) {
            throw new Exception\UnknownFormException(sprintf('Unknown form requested: %s', $formName));
        }

        $config = $this->baseForms->get($formName)->all();

        if (!isset($config['fields'])) {
            throw new Exception\FormOptionException(sprintf('[BoltForms] Form "%s" does not have any fields defined!', $formName));
        }

        foreach ($config['fields'] as $fieldName => $data) {
            $this->assetValidField($formName, $fieldName, $data);

            $options = !empty($data['options']) ? $data['options'] : [];
            $fieldOptions = new FieldOptions($formName, $fieldName, $data['type'], $options, $em, $dispatcher);
            $config['fields'][$fieldName]['options'] = $fieldOptions;
        }

        $resolvedForm = new FormConfig($formName, $config);
        $this->resolvedForms->set($formName, $resolvedForm);
    }

    /**
     * @param string $formName
     * @param string $fieldName
     * @param string $data
     *
     * @throws Exception\FormOptionException
     */
    protected function assetValidField($formName, $fieldName, $data)
    {
        if (!isset($data['type'])) {
            throw new Exception\FormOptionException(sprintf('[BoltForms] Form "%s" field "%s" does not have a type defined!', $formName, $fieldName));
        }
    }
}
