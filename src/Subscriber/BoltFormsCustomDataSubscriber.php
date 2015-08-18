<?php

namespace Bolt\Extension\Bolt\BoltForms\Subscriber;

use Bolt\Application;
use Bolt\Extension\Bolt\BoltForms\Event\BoltFormsCustomDataEvent;
use Bolt\Extension\Bolt\BoltForms\Event\BoltFormsEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Custom data functions for BoltForms
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
class BoltFormsCustomDataSubscriber implements EventSubscriberInterface
{
    /** @var Application */
    private $app;

    /**
     * Constructor.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Get the next increment of a database column.
     *
     * @param BoltFormsCustomDataEvent $event
     */
    public function nextIncrement(BoltFormsCustomDataEvent $event)
    {
        $params = $event->getParameters();

        if (isset($params['table'])) {
            $table = $params['table'];
        } elseif (isset($params['contenttype'])) {
            $table = $this->app['db.options']['prefix'] . $params['contenttype'];
        } else {
            return;
        }
        $min = isset($params['min']) ? $params['min'] : null;
        $data = $this->getNextNumber($table, $params['column'], $min);

        if ($data !== null) {
            $event->setData($data);
        }
    }

    /**
     * Create a random string.
     *
     * @param BoltFormsCustomDataEvent $event
     */
    public function randomString(BoltFormsCustomDataEvent $event)
    {
        $params = $event->getParameters();
        $length = isset($params['length']) ? $params['length'] : 12;
        $event->setData($this->app['randomgenerator']->generateString($length));
    }

    /**
     * Fetch a value from the $_SERVER super global.
     *
     * @param BoltFormsCustomDataEvent $event
     */
    public function serverValue(BoltFormsCustomDataEvent $event)
    {
        $params = $event->getParameters();
        if (!isset($params['key'])) {
            return;
        }

        $event->setData($this->app['request']->server->get($params['key']));
    }

    /**
     * Fetch a value from the session data.
     *
     * @param BoltFormsCustomDataEvent $event
     */
    public function sessionValue(BoltFormsCustomDataEvent $event)
    {
        $params = $event->getParameters();
        if (!isset($params['key'])) {
            return;
        }

        $event->setData($this->app['session']->get($params['key']));
    }

    /**
     * Fetch the current (formatted) timestamp.
     *
     * @param BoltFormsCustomDataEvent $event
     */
    public function timestamp(BoltFormsCustomDataEvent $event)
    {
        $params = $event->getParameters();
        if (!isset($params['format'])) {
            return;
        }

        $event->setData(strftime($params['format']));
    }

    /**
     * Attempt get the next sequence from a table, if specified.
     *
     * @param string  $table
     * @param string  $column
     * @param integer $minValue
     *
     * @return integer|false
     */
    private function getNextNumber($table, $column, $minValue = 0)
    {
        if (empty($table)) {
            $this->app['logger.system']->error("[BoltForms] No table name specified for `next_increment` event.", array('event' => 'extensions'));

            return null;
        }

        $query = sprintf('SELECT MAX(%s) as max FROM %s', $column, $table);
        try {
            $sequence = $this->app['db']->executeQuery($query)->fetchColumn();
        } catch (\Doctrine\DBAL\DBALException $e) {
            $this->app['logger.system']->error("[BoltForms] Could not fetch next sequence number from table '$table'. Check if the table exists.", array('event' => 'extensions'));

            return null;
        }

        if (++$sequence >= $minValue) {
            return $sequence;
        }

        return $minValue;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            BoltFormsEvents::DATA_NEXT_INCREMENT => 'nextIncrement',
            BoltFormsEvents::DATA_RANDOM_STRING  => 'randomString',
            BoltFormsEvents::DATA_SERVER_VALUE   => 'serverValue',
            BoltFormsEvents::DATA_SESSION_VALUE  => 'sessionValue',
            BoltFormsEvents::DATA_TIMESTAMP      => 'timeStamp',
        );
    }
}
