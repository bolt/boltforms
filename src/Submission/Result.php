<?php

namespace Bolt\Extension\Bolt\BoltForms\Submission;

use Bolt\Extension\Bolt\BoltForms\Event\BoltFormsEvents;

/**
 * Submission result.
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
class Result
{
    /** @var array */
    protected $result;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->result = [
            BoltFormsEvents::SUBMISSION_PROCESS_FIELDS      => false,
            BoltFormsEvents::SUBMISSION_PROCESS_UPLOADS     => false,
            BoltFormsEvents::SUBMISSION_PROCESS_CONTENTTYPE => false,
            BoltFormsEvents::SUBMISSION_PROCESS_DATABASE    => false,
            BoltFormsEvents::SUBMISSION_PROCESS_EMAIL       => false,
            BoltFormsEvents::SUBMISSION_PROCESS_FEEDBACK    => false,
            BoltFormsEvents::SUBMISSION_PROCESS_REDIRECT    => false,
        ];
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function isPass($name)
    {
        $name = 'boltforms.submission_process_' . $name;
        $this->assert($name);

        return (bool) $this->result[$name];
    }

    /**
     * @param string $name
     */
    public function passEvent($name)
    {
        $this->assert($name);

        $this->result[$name] = true;
    }

    /**
     * @param string $name
     */
    public function failEvent($name)
    {
        $this->assert($name);

        $this->result[$name] = false;
    }

    /**
     * @param $name
     */
    protected function assert($name)
    {
        if (isset($this->result[$name])) {
            return;
        }

        throw new \RuntimeException(sprintf('Attempted to access invalid result: %s', $name));
    }
}
