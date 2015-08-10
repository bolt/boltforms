<?php

namespace Bolt\Extension\Bolt\BoltForms\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Custom data event interface for BoltForms
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
class BoltFormsCustomDataEvent extends Event
{
    /** @var string */
    protected $eventName;
    /** @var array */
    protected $eventParams;
    /** @var mixed */
    protected $data;

    /**
     * @param string $eventName
     * @param array  $eventParams
     */
    public function __construct($eventName, $eventParams)
    {
        $this->eventName = $eventName;
        $this->eventParams = $eventParams;
    }

    /**
     * Get the event name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->eventName;
    }

    /**
     * Get the user supplied parameters.
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->eventParams;
    }

    /**
     * Get the event's data.
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set the event's data.
     *
     * @param mixed
     */
    public function setData($data)
    {
        $this->data = $data;
    }
}
