<?php

namespace Bolt\Extension\Bolt\BoltForms;

use Bolt\Extension\Bolt\BoltForms\Choice\ArrayType;
use Bolt\Extension\Bolt\BoltForms\Choice\ContentType;
use Psr\Log\LoggerInterface;

/**
 * Choices options for BoltForms
 *
 * Copyright (C) 2015 Gawain Lynch
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
 * @copyright Copyright (c) 2015, Gawain Lynch
 * @license   http://opensource.org/licenses/GPL-3.0 GNU Public License 3.0
 */
class FieldOptions
{
    /** @var string */
    private $formname;
    /** @var string */
    private $fieldname;
    /** @var string */
    private $type;
    /** @var array */
    private $baseOptions;
    /** @var array */
    private $options;
    /** @var \Bolt\Storage */
    private $storage;
    /** @var LoggerInterface */
    private $logger;
    /** @var boolean */
    private $initialised;

    /**
     * @params array $options
     */
    public function __construct($formname, $fieldname, $type, array $baseOptions, $storage, LoggerInterface $logger)
    {
        $this->formname = $formname;
        $this->fieldname = $fieldname;
        $this->type = $type;
        $this->baseOptions = $baseOptions;
        $this->storage = $storage;
        $this->logger = $logger;
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
        $options = $this->baseOptions;
        if ($this->type === 'choice') {
            $options['choices'] = $this->getChoiceValues($options);
        }
        $this->options = $options;

        // Set up contraint objects
        $this->setContraints();
    }

    /**
     * Get customised value parameters for choice field types.
     *
     * @return array
     */
    protected function getChoiceValues(array &$options)
    {
        if (is_string($this->baseOptions['choices']) && strpos($this->baseOptions['choices'], 'contenttype::') === 0) {
            $choice = new ContentType($this->storage, $this->fieldname, $this->baseOptions);

            // Only unset for a this type as it's custom
            unset($options['sort']);
            unset($options['limit']);
            unset($options['filters']);
        } else {
            $choice = new ArrayType($this->fieldname, $this->baseOptions['choices']);
        }

        return $choice->getChoices();
    }

    /**
     * Set the contraints classes properly.
     */
    protected function setContraints()
    {
        if (!isset($this->baseOptions['constraints'])) {
            return;
        }

        if (gettype($this->baseOptions['constraints']) === 'string') {
            $this->options['constraints'] = $this->getConstraintObject($this->formname, $this->baseOptions['constraints']);
        } else {
            foreach ($this->baseOptions['constraints'] as $key => $constraint) {
                $this->options['constraints'][$key] = $this->getConstraintObject($this->formname, array($key => $constraint));
            }
        }
    }

    /**
     * Extract, expand and set & create validator instance array(s)
     *
     * @param string $formname
     * @param mixed  $input
     *
     * @return \Symfony\Component\Validator\Constraint
     */
    protected function getConstraintObject($formname, $input)
    {
        $params = null;

        $namespace = "\\Symfony\\Component\\Validator\\Constraints\\";
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

        $this->logger->error("[BoltForms] The form '$formname' has an invalid field constraint: '$class'.", array('event' => 'extensions'));
    }
}
