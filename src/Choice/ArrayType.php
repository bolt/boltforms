<?php

namespace Bolt\Extension\Bolt\BoltForms\Choice;

/**
 * Array choices for BoltForms
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
class ArrayType implements ChoiceInterface
{
    /** @var string */
    private $name;
    /** @var array */
    private $choices;

    /**
     * @param string $name    Name of the BoltForms field
     * @param array  $choices Choices for field
     */
    public function __construct($name, array $choices)
    {
        $this->name    = $name;
        $this->choices = $choices;
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
     * @return array
     */
    public function getChoices()
    {
        return $this->choices;
    }
}
