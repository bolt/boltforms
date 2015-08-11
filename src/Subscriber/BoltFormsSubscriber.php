<?php

namespace Bolt\Extension\Bolt\BoltForms\Subscriber;

use Bolt\Application;
use Bolt\Extension\Bolt\BoltForms\Event\BoltFormsEvent;
use Bolt\Extension\Bolt\BoltForms\Event\BoltFormsEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Dedicated subscriber interface for BoltForms
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
class BoltFormsSubscriber implements EventSubscriberInterface
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
     * Events that BoltFormsSubscriber subscribes to
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA  => 'preSetData',
            FormEvents::POST_SET_DATA => 'postSetData',
            FormEvents::PRE_SUBMIT    => 'preSubmit',
            FormEvents::SUBMIT        => 'submit',
            FormEvents::POST_SUBMIT   => 'postSubmit'
        );
    }

    /**
     * Event triggered on FormEvents::PRE_SET_DATA
     *
     * @param FormEvent $event
     * @param string    $eventName
     */
    public function preSetData(FormEvent $event, $eventName)
    {
        $this->dispatch(BoltFormsEvents::PRE_SET_DATA, $event, $eventName);
    }

    /**
     * Event triggered on FormEvents::POST_SET_DATA
     *
     * @param FormEvent $event
     * @param string    $eventName
     */
    public function postSetData(FormEvent $event, $eventName)
    {
        $this->dispatch(BoltFormsEvents::POST_SET_DATA, $event, $eventName);
    }

    /**
     * Form pre submission event
     *
     * Event triggered on FormEvents::SUBMIT
     *
     * To modify data on the fly, this is the point to do it using:
     *  $data = $event->getData();
     *  $event->setData($data);
     *
     * @param FormEvent $event
     * @param string    $eventName
     */
    public function preSubmit(FormEvent $event, $eventName)
    {
        $this->dispatch(BoltFormsEvents::PRE_SUBMIT, $event, $eventName);
    }

    /**
     * Event triggered on FormEvents::SUBMIT
     *
     * @param FormEvent $event
     * @param string    $eventName
     */
    public function submit(FormEvent $event, $eventName)
    {
        $this->dispatch(BoltFormsEvents::SUBMIT, $event, $eventName);
    }

    /**
     * Event triggered on FormEvents::POST_SUBMIT
     *
     * @param FormEvent $event
     * @param string    $eventName
     */
    public function postSubmit(FormEvent $event, $eventName)
    {
        $this->dispatch(BoltFormsEvents::POST_SUBMIT, $event, $eventName);
    }

    /**
     * Dispatch event.
     *
     * @param string    $eventName
     * @param FormEvent $event
     * @param string    $formsEventName
     */
    protected function dispatch($eventName, FormEvent $event, $formsEventName)
    {
        if ($this->app['dispatcher']->hasListeners($eventName)) {
            $event = new BoltFormsEvent($event, $formsEventName);
            try {
                $this->app['dispatcher']->dispatch($eventName, $event);
            } catch (\Exception $e) {
                $this->app['logger.system']->error("[BoltForms] $eventName subscriber had an error: " . $e->getMessage(), array('event' => 'extensions'));
            }
        }
    }
}
