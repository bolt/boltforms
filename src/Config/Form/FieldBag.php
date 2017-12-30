<?php

namespace Bolt\Extension\Bolt\BoltForms\Config\Form;

use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Form fields collection for BoltForms
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
class FieldBag extends ParameterBag
{
    /** @var string */
    protected $fieldName;

    /**
     * Constructor.
     *
     * @param array $fieldName
     * @param array $parameters
     */
    public function __construct($fieldName, array $parameters)
    {
        $this->fieldName = $fieldName;
        $parameters += ['type' => null, 'options' => null];
        $parameters['options'] = new FieldOptionsBag($parameters['options']);
        if (isset($parameters['event'])) {
            $parameters['event'] = new FieldOptionsBag($parameters['event']);
        }

        parent::__construct($parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        $parameters = $this->parameters;
        foreach ($parameters as $key => $value) {
            if ($value instanceof ParameterBag) {
                $parameters[$key] = $value->all();
            } else {
                $parameters[$key] = $value;
            }
        }

        return $parameters;
    }

    /**
     * @return string
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * @return mixed
     */
    public function getOptions()
    {
        return $this->get('options');
    }

    /**
     * {@inheritdoc}
     */
    public function __call($name, $args = [])
    {
        $name = strtolower(preg_replace('/^get/', '', $name));
        if (isset($this->parameters[$name])) {
            return $this->parameters[$name];
        }

        throw new \BadMethodCallException(sprintf('%s ', __CLASS__));
    }
}
