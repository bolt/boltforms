<?php

namespace Bolt\Extension\Bolt\BoltForms\Submission\Processor;

use Bolt\Extension\Bolt\BoltForms\Config\Config;
use Bolt\Extension\Bolt\BoltForms\Config\Form\FieldOptionsBag;
use Bolt\Extension\Bolt\BoltForms\Event\CustomDataEvent;
use Bolt\Extension\Bolt\BoltForms\Event\LifecycleEvent;
use Bolt\Extension\Bolt\BoltForms\Exception\FileUploadException;
use Pimple as Container;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Submission processor file value processing.
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
class Fields extends AbstractProcessor
{
    /** @var Config */
    private $config;

    /**
     * Constructor.
     *
     * @param Container $handlers
     * @param Config    $config
     */
    public function __construct(Container $handlers, Config $config)
    {
        parent::__construct($handlers);
        $this->config = $config;
    }

    /**
     * Process the fields to get usable data.
     *
     * {@inheritdoc}
     *
     * @throws FileUploadException
     */
    public function process(LifecycleEvent $lifeEvent, $eventName, EventDispatcherInterface $dispatcher)
    {
        $formConfig = $lifeEvent->getFormConfig();
        $formData = $lifeEvent->getFormData();

        foreach ($formConfig->getFields() as $fieldName => $fieldConf) {
            /** @var \Bolt\Extension\Bolt\BoltForms\Config\Form\FieldBag $fieldConf */
            if ($fieldConf === null) {
                continue;
            }

            /** @var \Bolt\Extension\Bolt\BoltForms\Config\Form\FieldOptionsBag $fieldOptions */
            if ($fieldConf->has('event') === false) {
                continue;
            }

            // Handle events for custom data
            $eventConfig = $fieldConf->get('event');
            if ($eventConfig->has('name')) {
                $data = $this->dispatchCustomDataEvent($dispatcher, $eventConfig);
                $formData->set($fieldName, $data);
            }
        }
    }

    /**
     * Dispatch custom data events.
     *
     * @param EventDispatcherInterface $dispatcher
     * @param FieldOptionsBag          $eventConfig
     *
     * @return mixed|null
     */
    protected function dispatchCustomDataEvent(EventDispatcherInterface $dispatcher, FieldOptionsBag $eventConfig)
    {
        if (strpos('boltforms.', $eventConfig->get('name')) === false) {
            $eventName = 'boltforms.' . $eventConfig->get('name');
        } else {
            $eventName = $eventConfig->get('name');
        }

        if (!$dispatcher->hasListeners($eventName)) {
            return null;
        }

        $eventParams = $eventConfig->get('params');
        $event = new CustomDataEvent($eventName, $eventParams);
        $dispatcher->dispatch($eventName, $event);

        return $event->getData();
    }
}
