<?php

namespace Bolt\Extension\Bolt\BoltForms\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * External event interface for BoltForms
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
class BoltFormsProcessorEvent extends Event
{
    /** @var string */
    protected $formName;
    /** @var array */
    protected $data;

    /**
     * @param string $formName
     * @param array  $data
     */
    public function __construct($formName, array $data)
    {
        $this->formName = $formName;
        $this->data = $data;
    }

    public function getFormName()
    {
        return $this->formName;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData(array $data)
    {
        $this->data = $data;
    }
}
