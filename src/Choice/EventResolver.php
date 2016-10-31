<?php

namespace Bolt\Extension\Bolt\BoltForms\Choice;

use Bolt\Extension\Bolt\BoltForms\Event\BoltFormsEvents;
use Bolt\Extension\Bolt\BoltForms\Event\ChoiceEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Event driven choices for BoltForms.
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
class EventResolver extends AbstractChoiceOptionResolver
{
    /** @var array */
    private $choices;
    /** @var string */
    private $formName;
    /** @var EventDispatcherInterface */
    private $dispatcher;

    /**
     * Constructor.
     *
     * @param string                   $formName     Name of the form containing the field
     * @param string                   $fieldName    Name of the field
     * @param array                    $fieldOptions Options for field
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct($formName, $fieldName, array $fieldOptions, EventDispatcherInterface $dispatcher)
    {
        parent::__construct($formName, $fieldName, $fieldOptions);

        $this->dispatcher = $dispatcher;
        $this->formName = $formName;
    }

    /**
     * Get the name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Return choices array.
     *
     * @return array
     */
    public function getChoices()
    {
        if ($this->choices === null) {
            $event = new ChoiceEvent($this->formName, $this->name, $this->options);

            $this->dispatcher->dispatch($this->getEventName(), $event);

            $this->choices = $event->getChoices();
        }

        return $this->choices;
    }

    /**
     * Return the name of the event we want to dispatch.
     *
     * @return string
     */
    private function getEventName()
    {
        $parts = explode('::', $this->options['choices']);

        return isset($parts[1]) ? $parts[1] : BoltFormsEvents::DATA_CHOICE_EVENT;
    }

    /**
     * Get the name.
     *
     * @return string
     */
    public function getFormName()
    {
        return $this->formName;
    }
}
