<?php

namespace Bolt\Extension\Bolt\BoltForms\Factory;

use Bolt\Extension\Bolt\BoltForms\Exception;

/**
 * Field constraint factory class.
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
class FieldConstraint
{
    const SF_NAMESPACE = '\\Symfony\\Component\\Validator\\Constraints\\';

    /**
     * Extract, expand and set & create validator instance array(s)
     *
     * @param string $formName
     * @param mixed  $input
     *
     * @throws Exception\InvalidConstraintException
     *
     * @return \Symfony\Component\Validator\Constraint
     */
    public static function get($formName, $input)
    {
        $class = null;
        $params = null;

        $inputType = gettype($input);

        if ($inputType === 'string') {
            $class = static::getClassFromString($input);
        } elseif ($inputType === 'array') {
            $class = static::getClassFromArray($input, $params);
        }

        if (!class_exists($class)) {
            throw new Exception\InvalidConstraintException(sprintf('[BoltForms] The form "%s" has an invalid field constraint: "%s".', $formName, $class));
        }

        return new $class($params);
    }

    /**
     * @param string $input
     *
     * @return string
     */
    private static function getClassFromString($input)
    {
        return static::SF_NAMESPACE . $input;
    }

    /**
     * @param array $input
     * @param mixed $params
     *
     * @return string|null
     */
    private static function getClassFromArray(array $input, &$params)
    {
        $input = current($input);
        $inputType = gettype($input);
        if ($inputType === 'string') {
            $class = static::SF_NAMESPACE . $input;
        } elseif ($inputType === 'array') {
            $class = static::SF_NAMESPACE . key($input);
            $params = array_pop($input);
        } else {
            $class = null;
        }

        return $class;
    }
}
