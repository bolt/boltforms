<?php

namespace Bolt\Extension\Bolt\BoltForms\Subscriber;

use Bolt\Extension\Bolt\BoltForms\Event\BoltFormsEvents;
use Bolt\Extension\Bolt\BoltForms\Event\LifecycleEvent;
use Bolt\Extension\Bolt\BoltForms\Submission\Processor;
use Silex\Application;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Internal subscriber to the processor events.
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
class ProcessLifecycleSubscriber implements EventSubscriberInterface
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
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
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
     * Handle local processing of ProcessLifecycleEvents.
     *
     * @param LifecycleEvent           $lifeEvent
     * @param string                   $eventName
     * @param EventDispatcherInterface $dispatcher
     */
    public function onProcessLifecycleEvent(LifecycleEvent $lifeEvent, $eventName, EventDispatcherInterface $dispatcher)
    {
        $this->getProcessorManager()->runInternalProcessor($lifeEvent, $eventName, $dispatcher);
    }

    /**
     * @return Processor
     */
    public function getProcessorManager()
    {
        return $this->app['boltforms.processor'];
    }
}
