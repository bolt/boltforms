<?php

namespace Bolt\Extension\Bolt\BoltForms\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Event to generate event choices for BoltForms.
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
class ChoiceEvent extends Event
{
    /** @var string */
    private $formName;
    /** @var string */
    private $fieldName;
    /** @var array */
    private $options;
    /** @var ParameterBag */
    private $choices;

    /**
     * Constructor.
     *
     * @param string $formName
     * @param string $fieldName
     * @param array  $options
     */
    public function __construct($formName, $fieldName, array $options)
    {
        $this->formName = $formName;
        $this->fieldName = $fieldName;
        $this->options = $options;
        $this->choices = new ParameterBag();
    }

    /**
     * @return string
     */
    public function getFormName()
    {
        return $this->formName;
    }

    /**
     * @return string
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return mixed $key
     */
    public function getChoice($key)
    {
        return $this->choices->get($key);
    }

    /**
     * @return array
     */
    public function getChoices()
    {
        return $this->choices->all();
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function setChoice($key, $value)
    {
        $this->choices->set($key, $value);
    }

    /**
     * @param array $choices
     */
    public function setChoices(array $choices)
    {
        $this->choices = new ParameterBag($choices);
    }
}
