<?php

namespace Bolt\Extension\Bolt\BoltForms\Choice;

use Bolt\Extension\Bolt\BoltForms\Exception;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;
use Symfony\Component\PropertyAccess\PropertyPath;

/**
 * Base choice type.
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
abstract class AbstractChoiceOptionResolver implements ChoiceInterface
{
    /** @var string */
    private $formName;
    /** @var string */
    protected $name;
    /** @var array */
    protected $options;
    /** @var bool */
    protected $initialised;

    /**
     * @param string $formName     Name of the form containing the field
     * @param string $fieldName    Name of the BoltForms field
     * @param array  $fieldOptions Options for field
     */
    public function __construct($formName, $fieldName, array $fieldOptions)
    {
        $this->formName = $formName;
        $this->name = $fieldName;
        $this->options = $fieldOptions;
    }

    /**
     * @return string
     */
    public function getFormName()
    {
        return $this->formName;
    }

    /**
     * Get the name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Return choices array
     *
     * @throws Exception\FormOptionException
     *
     * @return array
     */
    public function getChoices()
    {
        if (!isset($this->options['choices'])) {
            throw new Exception\FormOptionException(sprintf('Choice array for field "%s" was not set in configuration.', $this->name));
        }

        if (!$this->initialised) {
            $this->getResolvedChoiceValues();
        }

        return $this->options['choices'];
    }

    /**
     * Set-up resolved choices.
     *
     * @throws Exception\FormOptionException
     */
    protected function getResolvedChoiceValues()
    {
        if ($this->initialised) {
            return;
        }

        if (!is_string($this->options['choices'])) {
            return;
        }

        $parts = explode('::', $this->options['choices']);
        $class = $parts[0];
        $context = $parts[1];

        if (!class_exists($class)) {
            throw new Exception\FormOptionException(sprintf('Configured "choices" field "%s" requires the %s class that was not found.', $this->name, $class));
        }

        // It the passed-in class name implements \Traversable we instantiate
        // that object passing in the parameter string to the constructor
        if (is_subclass_of($class, '\Traversable')) {
            $choiceObject = new $class($context);
            $this->options['choices'] = $choiceObject;
            $this->initialised = true;

            return;
        }

        $method = new \ReflectionMethod($class, $context);
        if ($method->isStatic()) {
            $this->options['choices'] = (array) call_user_func([$class, $context]);
            $this->initialised = true;

            return;
        }

        throw new Exception\FormOptionException(sprintf('Configured "choices" field "%s" is invalid!', $this->name));
    }

    /**
     * @throws Exception\FormOptionException
     *
     * @return ChoiceLoaderInterface|null
     */
    public function getChoiceLoader()
    {
        if (!isset($this->options['choice_loader'])) {
            return null;
        }
        if (!class_exists($this->options['choice_loader'])) {
            throw new Exception\FormOptionException(sprintf('Specified choice_loader class % does not exist for field "%s"!', $this->options['choice_loader'], $this->name));
        }

        $loader = new $this->options['choice_loader']();
        if (!$loader instanceof ChoiceLoaderInterface) {
            throw new Exception\FormOptionException(sprintf('Specified choice_loader class does not implement Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface for field "%s"!', $this->options['choice_loader'], $this->name));
        }

        return $loader;
    }

    /**
     * Legacy for Symfony 2.7+
     *
     * @return bool
     */
    public function isChoicesAsValues()
    {
        if (isset($this->options['choices_as_values'])) {
            return (bool) $this->options['choices_as_values'];
        }

        return true;
    }

    /**
     * @return callable|PropertyPath|null
     */
    public function getChoiceName()
    {
        return $this->getResolvedOptionValue('choice_name');
    }

    /**
     * @param string $optionKey
     * @param mixed  $default
     *
     * @return array|callable|null|PropertyPath
     */
    protected function getResolvedOptionValue($optionKey, $default = null)
    {
        if (!isset($this->options[$optionKey])) {
            return $default;
        }

        if (is_array($this->options[$optionKey]) || is_callable($this->options[$optionKey])) {
            return $this->options[$optionKey];
        }

        return new PropertyPath($this->options[$optionKey]);
    }

    /**
     * @return callable|PropertyPath|null
     */
    public function getChoiceValue()
    {
        return $this->getResolvedOptionValue('choice_value');
    }

    /**
     * @return bool|callable|PropertyPath|null
     */
    public function getChoiceLabel()
    {
        if (!isset($this->options['choice_label'])) {
            return null;
        }

        if (is_bool($this->options['choice_label']) || is_callable($this->options['choice_label'])) {
            return $this->options['choice_label'];
        }

        return new PropertyPath($this->options['choice_label']);
    }

    /**
     * @return array|callable|PropertyPath|null
     */
    public function getChoiceAttr()
    {
        return $this->getResolvedOptionValue('choice_attr');
    }

    /**
     * @return array|callable|PropertyPath|null
     */
    public function getGroupBy()
    {
        return $this->getResolvedOptionValue('group_by');
    }

    /**
     * @return array|callable|PropertyPath|null
     */
    public function getPreferredChoices()
    {
        return $this->getResolvedOptionValue('preferred_choices', []);
    }
}
