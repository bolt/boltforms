<?php

namespace Bolt\Extension\Bolt\BoltForms\Subscriber;

use Bolt\Configuration\ResourceManager;
use Bolt\Extension\Bolt\BoltForms\Event\BoltFormsEvent;
use Bolt\Extension\Bolt\BoltForms\Event\BoltFormsEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Dedicated subscriber interface for BoltForms
 *
 * Copyright (C) 2014 Gawain Lynch
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
    public function __construct()
    {
        $this->app = ResourceManager::getApp();
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
     */
    public function preSetData(FormEvent $event)
    {
        if ($this->app['dispatcher']->hasListeners(BoltFormsEvents::PRE_SET_DATA)) {
            $event = new BoltFormsEvent($event);
            try {
                $this->app['dispatcher']->dispatch(BoltFormsEvents::PRE_SET_DATA, $event);
            } catch (\Exception $e) {
                $this->app['logger.system']->info("[BoltForms] " . BoltFormsEvents::PRE_SET_DATA. " subscriber had an error: " . $e->getMessage(), array('event' => 'extensions'));
            }
        }
    }

    /**
     * Event triggered on FormEvents::POST_SET_DATA
     *
     * @param FormEvent $event
     */
    public function postSetData(FormEvent $event)
    {
        if ($this->app['dispatcher']->hasListeners(BoltFormsEvents::POST_SET_DATA)) {
            $event = new BoltFormsEvent($event);
            try {
                $this->app['dispatcher']->dispatch(BoltFormsEvents::POST_SET_DATA, $event);
            } catch (\Exception $e) {
                $this->app['logger.system']->info("[BoltForms] " . BoltFormsEvents::POST_SET_DATA. " subscriber had an error: " . $e->getMessage(), array('event' => 'extensions'));
            }
        }
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
     */
    public function preSubmit(FormEvent $event)
    {
        if ($this->app['dispatcher']->hasListeners(BoltFormsEvents::PRE_SUBMIT)) {
            $event = new BoltFormsEvent($event);
            try {
                $this->app['dispatcher']->dispatch(BoltFormsEvents::PRE_SUBMIT, $event);
            } catch (\Exception $e) {
                $this->app['logger.system']->info("[BoltForms] " . BoltFormsEvents::PRE_SUBMIT. " subscriber had an error: " . $e->getMessage(), array('event' => 'extensions'));
            }
        }
    }

    /**
     * Event triggered on FormEvents::SUBMIT
     *
     * @param FormEvent $event
     */
    public function submit(FormEvent $event)
    {
        if ($this->app['dispatcher']->hasListeners(BoltFormsEvents::SUBMIT)) {
            $event = new BoltFormsEvent($event);
            try {
                $this->app['dispatcher']->dispatch(BoltFormsEvents::SUBMIT, $event);
            } catch (\Exception $e) {
                $this->app['logger.system']->info("[BoltForms] " . BoltFormsEvents::SUBMIT. " subscriber had an error: " . $e->getMessage(), array('event' => 'extensions'));
            }
        }
    }

    /**
     * Event triggered on FormEvents::POST_SUBMIT
     *
     * @param FormEvent $event
     */
    public function postSubmit(FormEvent $event)
    {
        if ($this->app['dispatcher']->hasListeners(BoltFormsEvents::POST_SUBMIT)) {
            $event = new BoltFormsEvent($event);
            try {
                $this->app['dispatcher']->dispatch(BoltFormsEvents::POST_SUBMIT, $event);
            } catch (\Exception $e) {
                $this->app['logger.system']->info("[BoltForms] " . BoltFormsEvents::POST_SUBMIT. " subscriber had an error: " . $e->getMessage(), array('event' => 'extensions'));
            }
        }
    }
}
