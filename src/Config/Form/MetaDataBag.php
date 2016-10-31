<?php

namespace Bolt\Extension\Bolt\BoltForms\Config\Form;

/**
 * Form meta data section bag.
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
class MetaDataBag
{
    /** @var string */
    protected $name;
    /** @var array */
    protected $use;
    /** @var mixed */
    protected $value;

    /**
     * Constructor.
     *
     * @param string $name
     * @param array  $parameters
     */
    public function __construct($name, array $parameters = [])
    {
        $this->name = $name;
        $this->use = isset($parameters['use']) ? (array) $parameters['use'] : null;
        $this->value = isset($parameters['value']) ? $parameters['value'] : null;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return MetaDataBag
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return array
     */
    public function getUse()
    {
        return $this->use;
    }

    /**
     * @param array $use
     *
     * @return MetaDataBag
     */
    public function setUse($use)
    {
        $this->use = $use;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     *
     * @return MetaDataBag
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }
}
