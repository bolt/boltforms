<?php

namespace Bolt\Extension\Bolt\BoltForms\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * External event interface for BoltForms
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
class BoltFormsEvent extends FormEvent
{
    /**
     * @var Symfony\Component\Form\FormEvent
     */
    protected $event;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var Symfony\Component\Form\FormInterface
     */
    protected $form;

    /**
     * @param FormEvent $event
     */
    public function __construct(FormEvent $event)
    {
        $this->event = $event;
        $this->data  = $event->getData();
        $this->form  = $event->getForm();
    }

    public function getEvent()
    {
        return $this->event;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        if ($this->event->getName() == FormEvents::PRE_SUBMIT) {
            $this->event->setData($data);
        } else {
            trigger_error(__CLASS__ . "::" . __FUNCTION__ . " can only be called in BoltFormsEvents::PRE_SUBMIT", E_USER_ERROR);
        }
    }

    public function getForm()
    {
        return $this->form;
    }
}
