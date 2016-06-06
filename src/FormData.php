<?php

namespace Bolt\Extension\Bolt\BoltForms;

use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Submitted form data functionality for BoltForms
 *
 * Copyright (C) 2014-2015 Gawain Lynch
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
class FormData extends ParameterBag
{
    /**
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
        parent::__construct($parameters);

        // Don't keep token data around where not needed
        unset($this->parameters['_token']);
    }

    /**
     * Get the data that was received during the POST.
     *
     * @deprecated Deprecated since 3.0 and to be removed in 4.0
     *
     * @return array
     */
    public function getPostData()
    {
        return $this->parameters;
    }

    /**
     * Get a POST value.
     *
     * @param string  $name
     * @param boolean $transform
     * @param mixed   $default
     *
     * @return mixed
     */
    public function get($name, $transform = false, $default = null)
    {
        if ($transform === false) {
            return array_key_exists($name, $this->parameters) ? $this->parameters[$name] : $default;
        }

        return $this->getTransform($name);
    }

    /**
     * Get an adjusted POST value.
     *
     * @param string $name
     *
     * @return mixed
     */
    protected function getTransform($name)
    {
        $value = $this->parameters[$name];

        // Don't try to insert NULLs
        if ($value === null) {
            return '';
        }

        // JSON encode arrays
        if (is_array($value)) {
            return json_encode($value);
        }

        if ($value instanceof \DateTime) {
            return $value->format('c');
        }

        // Handle file storage preparation here
        if ($value instanceof UploadedFileHandler && $value->isValid()) {
            return $value->relativePath();
        }

        return $value;
    }
}
