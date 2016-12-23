<?php

namespace Bolt\Extension\Bolt\BoltForms\Submission;

use Bolt\Extension\Bolt\BoltForms\Event\BoltFormsEvents;

/**
 * Map of processor events to data.
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
class ProcessorMap
{
    static $eventMap = [
        'fields'   => BoltFormsEvents::SUBMISSION_PROCESS_FIELDS,
        'uploads'  => BoltFormsEvents::SUBMISSION_PROCESS_UPLOADS,
        'content'  => BoltFormsEvents::SUBMISSION_PROCESS_CONTENTTYPE,
        'database' => BoltFormsEvents::SUBMISSION_PROCESS_DATABASE,
        'email'    => BoltFormsEvents::SUBMISSION_PROCESS_EMAIL,
        'feedback' => BoltFormsEvents::SUBMISSION_PROCESS_FEEDBACK,
        'redirect' => BoltFormsEvents::SUBMISSION_PROCESS_REDIRECT,
    ];

    /**
     * @return array
     */
    public static function subscribedEvents()
    {
        return [
            BoltFormsEvents::SUBMISSION_PROCESS_FIELDS      => ['onProcessLifecycleEvent', BoltFormsEvents::PRIORITY_INTERNAL],
            BoltFormsEvents::SUBMISSION_PROCESS_UPLOADS     => ['onProcessLifecycleEvent', BoltFormsEvents::PRIORITY_INTERNAL],
            BoltFormsEvents::SUBMISSION_PROCESS_CONTENTTYPE => ['onProcessLifecycleEvent', BoltFormsEvents::PRIORITY_INTERNAL],
            BoltFormsEvents::SUBMISSION_PROCESS_DATABASE    => ['onProcessLifecycleEvent', BoltFormsEvents::PRIORITY_INTERNAL],
            BoltFormsEvents::SUBMISSION_PROCESS_EMAIL       => ['onProcessLifecycleEvent', BoltFormsEvents::PRIORITY_INTERNAL],
            BoltFormsEvents::SUBMISSION_PROCESS_FEEDBACK    => ['onProcessLifecycleEvent', BoltFormsEvents::PRIORITY_INTERNAL],
            BoltFormsEvents::SUBMISSION_PROCESS_REDIRECT    => ['onProcessLifecycleEvent', BoltFormsEvents::PRIORITY_INTERNAL],
        ];
    }

    /**
     * @return array
     */
    public static function eventNameToMethodName()
    {
        return array_flip(self::$eventMap);
    }

    /**
     * @param string $name
     *
     * @return array|null
     */
    public static function getEventMethodName($name)
    {
        $map = array_flip(self::$eventMap);;

        return isset($map[$name]) && ($value = $map[$name]) ? $value : null;
    }
}
