<?php

namespace Bolt\Extension\Bolt\BoltForms;

use Bolt\Extension\Bolt\BoltForms\Submission\Handler\Upload;
use Bolt\Storage\Entity;

/**
 * Submitted form data functionality for BoltForms
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
class FormData extends Entity\Content
{
    /**
     * @param array $parameters
     */
    public function __construct($parameters)
    {
        parent::__construct($parameters);

        // Don't keep token data around where not needed
        unset($this->parameters['_token']);
    }

    /**
     * Get a POST value.
     *
     * @param string  $name
     * @param mixed   $default
     * @param boolean $transform
     *
     * @return mixed
     */
    public function get($name, $default = null, $transform = false)
    {
        if ($transform === false) {
            return array_key_exists($name, $this->toArray()) ? $this->{$name} : $default;
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
        $value = $this->{$name};

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
        if ($value instanceof Upload && $value->isValid()) {
            return $value->relativePath();
        }

        return $value;
    }
}
