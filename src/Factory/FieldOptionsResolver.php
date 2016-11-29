<?php

namespace Bolt\Extension\Bolt\BoltForms\Factory;

use Bolt\Extension\Bolt\BoltForms\Choice;
use Bolt\Extension\Bolt\BoltForms\Factory;
use Bolt\Storage\EntityManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Choices options for BoltForms
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
class FieldOptionsResolver extends ParameterBag
{
    /** @var string */
    private $type;
    /** @var array */
    private $baseOptions;

    /**
     * Constructor.
     *
     * @param string $type
     * @param array  $parameters
     */
    public function __construct($type, array $parameters)
    {
        parent::__construct();

        $this->type = $type;
        $this->baseOptions = $parameters;
    }

    /**
     * Get the options array suitable for passing to Symfony Forms.
     *
     * @param EntityManager            $storage
     * @param EventDispatcherInterface $dispatcher
     *
     * @return array
     */
    public function getOptions(EntityManager $storage, EventDispatcherInterface $dispatcher)
    {
        $this->initialise($storage, $dispatcher);

        return $this->get('options', []);
    }

    /**
     * Set a clean array of options to be passed to Symfony Forms.
     *
     * @param EntityManager            $storage
     * @param EventDispatcherInterface $dispatcher
     */
    protected function initialise(EntityManager $storage, EventDispatcherInterface $dispatcher)
    {
        $options = (array) $this->baseOptions;
        if ($this->type === 'choice') {
            $options = $this->resolveChoiceOptions($options, $storage, $dispatcher);
        }

        // Set up constraint objects
        $options = $this->setConstraints($options);

        $this->set('options', $options);
    }

    /**
     * Get customised value parameters for choice field types.
     *
     * @param array                    $options
     * @param EntityManager            $storage
     * @param EventDispatcherInterface $dispatcher
     *
     * @return array
     */
    protected function resolveChoiceOptions(array $options, EntityManager $storage, EventDispatcherInterface $dispatcher)
    {
        $choices = isset($options['choices']) ? $options['choices'] : null;

        if (is_string($choices)) {
            $choiceObj = $this->handleCustomChoice($choices, $storage, $dispatcher);
        } else {
            $choiceObj = new Choice\ChoiceResolver($this->get('formName'), $this->get('fieldName'), $this->baseOptions);
        }

        $choiceOptions = [
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

        $options = array_merge($options, $choiceOptions);

        unset($options['params']);

        return $options;
    }

    /**
     * @param string                   $choices
     * @param EntityManager            $storage
     * @param EventDispatcherInterface $dispatcher
     *
     * @return Choice\AbstractChoiceOptionResolver
     */
    protected function handleCustomChoice($choices, EntityManager $storage, EventDispatcherInterface $dispatcher)
    {
        // Check if it is one of our custom types
        if (strpos($choices, 'content') === 0) {
            return new Choice\ContentTypeResolver($this->get('formName'), $this->get('fieldName'), $this->baseOptions, $storage);
        }

        if (strpos($choices, 'event') === 0) {
            return new Choice\EventResolver($this->get('formName'), $this->get('fieldName'), $this->baseOptions, $dispatcher);
        }

        return new Choice\ChoiceResolver($this->get('formName'), $this->get('fieldName'), $this->baseOptions);
    }

    /**
     * Set the constraints classes properly.
     *
     * @param array $options
     *
     * @return array
     */
    protected function setConstraints(array $options)
    {
        if (!isset($this->baseOptions['constraints'])) {
            return $options;
        }

        if (gettype($this->baseOptions['constraints']) === 'string') {
            $options['constraints'] = Factory\FieldConstraint::get($this->get('formName'), $this->baseOptions['constraints']);

            return $options;
        }

        foreach ($this->baseOptions['constraints'] as $key => $constraint) {
            $options['constraints'][$key] = Factory\FieldConstraint::get($this->get('formName'), [$key => $constraint]);
        }

        return $options;
    }
}
