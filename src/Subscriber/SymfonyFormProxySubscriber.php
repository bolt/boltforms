<?php

namespace Bolt\Extension\Bolt\BoltForms\Subscriber;

use Bolt\Extension\Bolt\BoltForms\Event\BoltFormsEvent;
use Bolt\Extension\Bolt\BoltForms\Event\BoltFormsEvents;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Dedicated subscriber interface for BoltForms
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
class SymfonyFormProxySubscriber implements EventSubscriberInterface
{
    /**
     * Events that BoltFormsSubscriber subscribes to
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA  => 'preSetData',
            FormEvents::POST_SET_DATA => 'postSetData',
            FormEvents::PRE_SUBMIT    => 'preSubmit',
            FormEvents::SUBMIT        => 'submit',
            FormEvents::POST_SUBMIT   => 'postSubmit',
        ];
    }

    /**
     * Event triggered on FormEvents::PRE_SET_DATA
     *
     * @param FormEvent       $event
     * @param string          $eventName
     * @param EventDispatcher $dispatcher
     */
    public function preSetData(FormEvent $event, $eventName, $dispatcher)
    {
        $this->dispatch(BoltFormsEvents::PRE_SET_DATA, $event, $eventName, $dispatcher);
    }

    /**
     * Event triggered on FormEvents::POST_SET_DATA
     *
     * @param FormEvent       $event
     * @param string          $eventName
     * @param EventDispatcher $dispatcher
     */
    public function postSetData(FormEvent $event, $eventName, $dispatcher)
    {
        $this->dispatch(BoltFormsEvents::POST_SET_DATA, $event, $eventName, $dispatcher);
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
     * @param FormEvent       $event
     * @param string          $eventName
     * @param EventDispatcher $dispatcher
     */
    public function preSubmit(FormEvent $event, $eventName, $dispatcher)
    {
        $this->dispatch(BoltFormsEvents::PRE_SUBMIT, $event, $eventName, $dispatcher);
    }

    /**
     * Event triggered on FormEvents::SUBMIT
     *
     * @param FormEvent       $event
     * @param string          $eventName
     * @param EventDispatcher $dispatcher
     */
    public function submit(FormEvent $event, $eventName, $dispatcher)
    {
        $this->dispatch(BoltFormsEvents::SUBMIT, $event, $eventName, $dispatcher);
    }

    /**
     * Event triggered on FormEvents::POST_SUBMIT
     *
     * @param FormEvent       $event
     * @param string          $eventName
     * @param EventDispatcher $dispatcher
     */
    public function postSubmit(FormEvent $event, $eventName, $dispatcher)
    {
        $this->dispatch(BoltFormsEvents::POST_SUBMIT, $event, $eventName, $dispatcher);
    }

    /**
     * Dispatch event.
     *
     * @param string          $eventName
     * @param FormEvent       $event
     * @param string          $formsEventName
     * @param EventDispatcher $dispatcher
     */
    protected function dispatch($eventName, FormEvent $event, $formsEventName, EventDispatcher $dispatcher)
    {
        $event = new BoltFormsEvent($event, $formsEventName);
        $dispatcher->dispatch($eventName, $event);
    }
}
