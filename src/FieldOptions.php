<?php

namespace Bolt\Extension\Bolt\BoltForms;

use Bolt\Extension\Bolt\BoltForms\Choice\ContentType;
use Bolt\Extension\Bolt\BoltForms\Choice\EventType;
use Bolt\Extension\Bolt\BoltForms\Choice\SymfonyChoiceType;
use Bolt\Extension\Bolt\BoltForms\Exception;
use Bolt\Storage\EntityManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Choices options for BoltForms
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
class FieldOptions
{
    /** @var string */
    private $formName;
    /** @var string */
    private $fieldName;
    /** @var string */
    private $type;
    /** @var array */
    private $baseOptions;
    /** @var array */
    private $options;
    /** @var EntityManager */
    private $em;
    /** @var boolean */
    private $initialised;
    /** @var EventDispatcherInterface */
    private $dispatcher;

    /**
     * Constructor.
     *
     * @param string                   $formName
     * @param string                   $fieldName
     * @param string                   $type
     * @param array                    $baseOptions
     * @param EntityManager            $storage
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct($formName, $fieldName, $type, array $baseOptions, EntityManager $storage, EventDispatcherInterface $dispatcher)
    {
        $this->formName = $formName;
        $this->fieldName = $fieldName;
        $this->type = $type;
        $this->baseOptions = $baseOptions;
        $this->em = $storage;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Get the options array suitable for passing to Symfony Forms.
     *
     * @return array
     */
    public function toArray()
    {
        $this->initialise();

        return $this->options;
    }

    /**
     * Build the options.
     */
    protected function initialise()
    {
        if ($this->initialised) {
            return;
        }

        $this->setValidOptions();
        $this->initialised = true;
    }

    /**
     * Get a clean array of options to be passed to Symfony Forms.
     *
     * @return array
     */
    protected function setValidOptions()
    {
        $this->options = $this->baseOptions;
        if ($this->type === 'choice') {
            $this->resolveChoiceOptions();
        }

        // Set up constraint objects
        $this->setConstraints();
    }

    /**
     * Get customised value parameters for choice field types.
     *
     * @throws Exception\FormOptionException
     */
    protected function resolveChoiceOptions()
    {
        $options = $this->baseOptions;
        $options['choices'] = isset($options['choices']) ? $options['choices'] : null;

        if (is_array($options['choices'])) {
            $this->setSymfonyChoiceType();

            return;
        }

        if (!is_string($options['choices'])) {
            throw new Exception\FormOptionException(sprintf('Configured "choices" field "%s" is invalid on "%s" form!', $this->fieldName, $this->formName));
        }

        if (strpos($options['choices'], 'contenttype::') === 0) {
            $this->setContentTypeChoiceType();

            return;
        }

        if (strpos($options['choices'], 'event') === 0) {
            $this->setEventChoiceType();

            return;
        }

        if (strpos($options['choices'], '::') !== false) {
            $this->setEntityChoiceType();

            return;
        }

        throw new Exception\FormOptionException(sprintf('Configured "choices" field "%s" is invalid on "%s" form!', $this->fieldName, $this->formName));
    }

    /**
     * Sets the field options for Symfony ChoiceType parameters.
     *
     * @throws Exception\FormOptionException
     *
     * @return array
     */
    protected function setSymfonyChoiceType()
    {
        $choiceObj = new SymfonyChoiceType($this->fieldName, $this->baseOptions);

        $options = [
            'choices'           => $choiceObj->getChoices(),
            'choices_as_values' => $choiceObj->isChoicesAsValues(),
            'choice_loader'     => $choiceObj->getChoiceLoader(),
            'choice_name'       => $choiceObj->getChoiceName(),
            'choice_value'      => $choiceObj->getChoiceValue(),
            'choice_label'      => $choiceObj->getChoiceLabel(),
            'choice_attr'       => $choiceObj->getChoiceAttr(),
            'group_by'          => $choiceObj->getGroupBy(),
            'preferred_choices' => $choiceObj->getPreferredChoices(),
        ];

        $this->options = array_merge($this->baseOptions, $options);
    }

    /**
     * Set up choices from a ContentType lookup.
     */
    protected function setContentTypeChoiceType()
    {
        $choiceObj = new ContentType($this->em, $this->fieldName, $this->baseOptions);
        $this->options['choices'] = $choiceObj->getChoices();
    }

    /**
     * Set up choices for an event based type.
     */
    protected function setEventChoiceType()
    {
        $choiceObj = new EventType($this->dispatcher, $this->fieldName, $this->baseOptions, $this->formName);
        $this->options['choices'] = $choiceObj->getChoices();
    }

    /**
     * Set up choices for an entity type.
     *
     * @throws Exception\FormOptionException
     */
    protected function setEntityChoiceType()
    {
        $parts = explode('::', $this->baseOptions['choices']);
        $class = $parts[0];
        $context = $parts[1];

        if (!class_exists($class)) {
            throw new Exception\FormOptionException(sprintf('Configured "choices" field "%s" is invalid on "%s" form!', $this->fieldName, $this->formName));
        }

        // Do initial choice set up
        $this->setSymfonyChoiceType();

        // It the passed-in class name implements \Traversable we instantiate
        // that object passing in the parameter string to the constructor
        if (is_subclass_of($class, 'Traversable')) {
            $choiceObject = new $class($context);
            $this->options['choices'] = $choiceObject;

            return;
        }

        $method = new \ReflectionMethod($class, $context);
        if ($method->isStatic()) {
            $this->options['choices'] = (array) call_user_func([$class, $context]);

            return;
        }

        throw new Exception\FormOptionException(sprintf('Configured "choices" field "%s" is invalid on "%s" form!', $this->fieldName, $this->formName));
    }

    /**
     * Set the constraints classes properly.
     */
    protected function setConstraints()
    {
        if (!isset($this->baseOptions['constraints'])) {
            return;
        }

        if (gettype($this->baseOptions['constraints']) === 'string') {
            $this->options['constraints'] = FieldConstraintFactory::get($this->formName, $this->baseOptions['constraints']);

            return;
        }

        foreach ($this->baseOptions['constraints'] as $key => $constraint) {
            $this->options['constraints'][$key] = FieldConstraintFactory::get($this->formName, [$key => $constraint]);
        }
    }
}
