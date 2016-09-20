<?php

namespace Bolt\Extension\Bolt\BoltForms\Submission\Processor;

use Bolt\Extension\Bolt\BoltForms\Config\Config;
use Bolt\Extension\Bolt\BoltForms\Config\Form\FieldOptionsBag;
use Bolt\Extension\Bolt\BoltForms\Event\CustomDataEvent;
use Bolt\Extension\Bolt\BoltForms\Event\LifecycleEvent;
use Bolt\Extension\Bolt\BoltForms\Exception\FileUploadException;
use Bolt\Extension\Bolt\BoltForms\Submission\Handler\Upload;
use Bolt\Extension\Bolt\BoltForms\Submission\Processor;
use Pimple as Container;
use Psr\Log\LogLevel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Submission processor file value processing.
 *
 * Copyright (c) 2014-2016 Gawain Lynch
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
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   http://opensource.org/licenses/GPL-3.0 GNU Public License 3.0
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

        foreach ($formData->keys() as $fieldName) {
            $field = $formData->get($fieldName);

            // Handle file uploads
            if ($field instanceof UploadedFile) {
                $this->processFieldFile($fieldName, $lifeEvent, $field);
            }

            // Handle events for custom data
            $fieldConf = $formConfig->getFields()->get($fieldName);
            if ($fieldConf->getOptions()->has('event') && $fieldConf->getOptions()->get('event')->has('name')) {
                $formData->set($fieldName, $this->dispatchCustomDataEvent($dispatcher, $fieldConf->getOptions()->get('event')));
            }
        }
    }

    /**
     * @param string         $fieldName
     * @param LifecycleEvent $lifeEvent
     * @param UploadedFile   $field
     *
     * @throws FileUploadException
     */
    protected function processFieldFile($fieldName, LifecycleEvent $lifeEvent, UploadedFile $field)
    {
        if (!$field->isValid()) {
            throw new FileUploadException($field->getErrorMessage(), $field->getErrorMessage(), 0, null, false);
        }

        $formConfig = $lifeEvent->getFormConfig();
        $formData = $lifeEvent->getFormData();

        // Get the upload object
        /** @var Upload $fileHandler */
        $fileHandler = $this->handlers['upload']($formConfig, $field);
        $formData->set($fieldName, $fileHandler);

        if (!$this->config->getUploads()->get('enabled')) {
            $this->message('File upload skipped as the administrator has disabled uploads for all forms.',  Processor::FEEDBACK_DEBUG, LogLevel::ERROR);

            return;
        }

        $fileHandler->move();
        $this->message(sprintf('Moving uploaded file to %s', $fileHandler->fullPath()),  Processor::FEEDBACK_DEBUG, LogLevel::DEBUG);
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
