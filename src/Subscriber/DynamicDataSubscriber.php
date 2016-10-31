<?php

namespace Bolt\Extension\Bolt\BoltForms\Subscriber;

use Bolt\Extension\Bolt\BoltForms\Event\BoltFormsEvents;
use Bolt\Extension\Bolt\BoltForms\Event\CustomDataEvent;
use Doctrine\DBAL\DBALException;
use Silex\Application;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Custom data functions for BoltForms
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
class DynamicDataSubscriber implements EventSubscriberInterface
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
     * @param CustomDataEvent $event
     */
    public function nextIncrement(CustomDataEvent $event)
    {
        $params = $event->getParameters();

        if ($params->has('table')) {
            $table = $params->get('table');
        } elseif ($params->has('contenttype')) {
            $table = $this->app['schema']->getTableName($params->get('contenttype'));
        } else {
            return;
        }
        $min = $params->get('min');
        $data = $this->getNextNumber($table, $params->get('column'), $min);

        if ($data !== null) {
            $event->setData($data);
        }
    }

    /**
     * Create a random string.
     *
     * @param CustomDataEvent $event
     */
    public function randomString(CustomDataEvent $event)
    {
        $params = $event->getParameters();
        $length = $params->getInt('length', 12);
        $event->setData(bin2hex(random_bytes($length)));
    }

    /**
     * Fetch a value from the $_SERVER super global.
     *
     * @param CustomDataEvent $event
     */
    public function serverValue(CustomDataEvent $event)
    {
        $params = $event->getParameters();
        $key = $params->get('key');
        if ($key === null) {
            return;
        }

        $event->setData($this->app['request']->server->get($key));
    }

    /**
     * Fetch a value from the session data.
     *
     * @param CustomDataEvent $event
     */
    public function sessionValue(CustomDataEvent $event)
    {
        $params = $event->getParameters();
        $key = $params->get('key');
        if ($key === null) {
            return;
        }

        $event->setData($this->app['session']->get($key));
    }

    /**
     * Fetch the current (formatted) timestamp.
     *
     * @param CustomDataEvent $event
     */
    public function timestamp(CustomDataEvent $event)
    {
        $params = $event->getParameters();
        $format = $params->get('format');
        if ($format === null) {
            return;
        }

        $event->setData(strftime($format));
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
            $this->app['logger.system']->error('[BoltForms] No table name specified for `next_increment` event.', ['event' => 'extensions']);

            return null;
        }

        $query = $this->app['db']->createQueryBuilder()
            ->select("MAX($column) as max")
            ->from($table)
        ;
        try {
            $sequence = $query->execute()->fetchColumn();
        } catch (DBALException $e) {
            $this->app['logger.system']->error("[BoltForms] Could not fetch next sequence number from table '$table'. Check if the table exists.", ['event' => 'extensions']);

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
        return [
            BoltFormsEvents::DATA_NEXT_INCREMENT => 'nextIncrement',
            BoltFormsEvents::DATA_RANDOM_STRING  => 'randomString',
            BoltFormsEvents::DATA_SERVER_VALUE   => 'serverValue',
            BoltFormsEvents::DATA_SESSION_VALUE  => 'sessionValue',
            BoltFormsEvents::DATA_TIMESTAMP      => 'timeStamp',
        ];
    }
}
