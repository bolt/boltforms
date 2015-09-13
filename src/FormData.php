<?php

namespace Bolt\Extension\Bolt\BoltForms;

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
 * @copyright Copyright (c) 2014, Gawain Lynch
 * @license   http://opensource.org/licenses/GPL-3.0 GNU Public License 3.0
 */
class FormData implements \ArrayAccess
{
    /** @var array */
    protected $postData;

    /**
     * @param array $postData
     */
    public function __construct(array $postData)
    {
        $this->postData = $postData;

        // Don't keep token data around where not needed
        unset($this->postData['_token']);
    }

    /**
     * Get the data that was recived during the POST.
     *
     * @return array
     */
    public function getPostData()
    {
        return $this->postData;
    }

    /**
     * Return the data key names.
     *
     * @return string[]
     */
    public function keys()
    {
        return array_keys($this->postData);
    }

    /**
     * Check if we have a posted value.
     *
     * @param string $name
     *
     * @return boolean
     */
    public function has($name)
    {
        if (isset($this->postData[$name])) {
            return true;
        }

        return false;
    }

    /**
     * Get a POST value.
     *
     * @param string  $name
     * @param boolean $transform
     *
     * @return mixed
     */
    public function get($name, $transform = false)
    {
        if ($transform === false) {
            return $this->postData[$name];
        }

        return $this->getTransform($name);
    }

    /**
     * Set a POST value.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return mixed
     */
    public function set($name, $value)
    {
        $this->postData[$name] = $value;
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
        $value = $this->postData[$name];

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
        if ($value instanceof FileUpload && $value->isValid()) {
            return $value->relativePath();
        }

        return $value;
    }

    public function offsetSet($offset, $value)
    {
    }

    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    public function offsetUnset($offset)
    {
    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }
}
