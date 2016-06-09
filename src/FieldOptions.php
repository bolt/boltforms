<?php

namespace Bolt\Extension\Bolt\BoltForms;

use Bolt\Extension\Bolt\BoltForms\Choice\ContentType;
use Bolt\Extension\Bolt\BoltForms\Choice\EventType;
use Bolt\Extension\Bolt\BoltForms\Choice\SymfonyChoiceType;
use Bolt\Extension\Bolt\BoltForms\Exception\FormOptionException;
use Bolt\Storage\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher;

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
    /** @var LoggerInterface */
    private $logger;
    /** @var boolean */
    private $initialised;
    /** @var TraceableEventDispatcher */
    private $dispatcher;

    /**
     * Constructor.
     *
     * @param string                   $formName
     * @param string                   $fieldName
     * @param string                   $type
     * @param array                    $baseOptions
     * @param EntityManager            $storage
     * @param LoggerInterface          $logger
     * @param TraceableEventDispatcher $dispatcher
     */
    public function __construct($formName, $fieldName, $type, array $baseOptions, EntityManager $storage, LoggerInterface $logger, TraceableEventDispatcher $dispatcher)
    {
        $this->formName = $formName;
        $this->fieldName = $fieldName;
        $this->type = $type;
        $this->baseOptions = $baseOptions;
        $this->em = $storage;
        $this->logger = $logger;
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
        if ($this->type === 'choice') {
            $this->resolveChoiceOptions();
        } else {
            $this->options = $this->baseOptions;
        }

        // Set up constraint objects
        $this->setConstraints();
    }

    /**
     * Get customised value parameters for choice field types.
     *
     * @throws FormOptionException
     *
     * @return array
     */
    protected function resolveChoiceOptions()
    {
        $options = $this->baseOptions;

        if (isset($options['choices']) && is_string($options['choices'])) {
            if (strpos($options['choices'], 'contenttype::') === 0) {
                $choiceObj = new ContentType($this->em, $this->fieldName, $options);
            } elseif (strpos($options['choices'], 'event') === 0) {
                $choiceObj = new EventType($this->dispatcher, $this->fieldName, $options, $this->formName);
            } else {
                throw new FormOptionException(sprintf('Specified choices key is invalid: %s', $options['choices']));
            }
            $this->options['choices'] = $choiceObj->getChoices();
        } else {
            $choiceObj = new SymfonyChoiceType($this->fieldName, $options);
            $this->options = [
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
        }
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
            $this->options['constraints'] = $this->getConstraintObject($this->formName, $this->baseOptions['constraints']);
        } else {
            foreach ($this->baseOptions['constraints'] as $key => $constraint) {
                $this->options['constraints'][$key] = $this->getConstraintObject($this->formName, [$key => $constraint]);
            }
        }
    }

    /**
     * Extract, expand and set & create validator instance array(s)
     *
     * @param string $formName
     * @param mixed  $input
     *
     * @return \Symfony\Component\Validator\Constraint
     */
    protected function getConstraintObject($formName, $input)
    {
        $params = null;

        $namespace = '\\Symfony\\Component\\Validator\\Constraints\\';
        $inputType = gettype($input);

        if ($inputType === 'string') {
            $class = $namespace . $input;
        } elseif ($inputType === 'array') {
            $input = current($input);
            $inputType = gettype($input);
            if ($inputType === 'string') {
                $class = $namespace . $input;
            } elseif ($inputType === 'array') {
                $class = $namespace . key($input);
                $params = array_pop($input);
            }
        }

        if (class_exists($class)) {
            return new $class($params);
        }

        $this->logger->error(sprintf('[BoltForms] The form "%s" has an invalid field constraint: "%s".', $formName, $class), ['event' => 'extensions']);
    }
}
