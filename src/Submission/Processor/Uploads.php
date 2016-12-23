<?php

namespace Bolt\Extension\Bolt\BoltForms\Submission\Processor;

use Bolt\Extension\Bolt\BoltForms\Config\Config;
use Bolt\Extension\Bolt\BoltForms\Event\LifecycleEvent;
use Bolt\Extension\Bolt\BoltForms\Exception\FileUploadException;
use Bolt\Extension\Bolt\BoltForms\Submission\Handler\Upload;
use Bolt\Extension\Bolt\BoltForms\Submission\Processor;
use Pimple as Container;
use Psr\Log\LogLevel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Submission processor final redirection.
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
class Uploads extends AbstractProcessor
{
    /** @var Config */
    private $config;
    /** @var SessionInterface */
    private $session;

    /**
     * Constructor.
     *
     * @param Container        $handlers
     * @param Config           $config
     * @param SessionInterface $session
     */
    public function __construct(Container $handlers, Config $config, SessionInterface $session)
    {
        parent::__construct($handlers);
        $this->config = $config;
        $this->session = $session;
    }

    /**
     * {@inheritdoc}
     */
    public function process(LifecycleEvent $lifeEvent, $eventName, EventDispatcherInterface $dispatcher)
    {
        $formConfig = $lifeEvent->getFormConfig();
        foreach ($formConfig->getFields()->all() as $fieldName => $fieldConfig) {
            if ($fieldConfig['type'] !== 'file') {
                continue;
            }
            $this->processFileField($lifeEvent, $fieldName, $fieldConfig);
        }
    }

    /**
     * @param LifecycleEvent $lifeEvent
     * @param string         $fieldName
     * @param array          $fieldConfig
     */
    protected function processFileField(LifecycleEvent $lifeEvent, $fieldName, array $fieldConfig)
    {
        if (!$this->config->getUploads()->get('enabled')) {
            $this->message('File upload skipped as the administrator has disabled uploads for all forms.',  Processor::FEEDBACK_DEBUG, LogLevel::ERROR);

            return;
        }

        $formConfig = $lifeEvent->getFormConfig();
        $formData = $lifeEvent->getFormData();
        $field = $formData->get($fieldName);
        if ($field instanceof UploadedFile) {
            $this->processFileFieldSingle($fieldName, $lifeEvent, $field);

            return;
        }

        $isMultiple = $formConfig->getFields()->get($fieldName)->get('options')->getBoolean('multiple');
        if (is_array($field) && $isMultiple === false) {
            throw new \RuntimeException('Multiple uploads submitted, but disallowed in form configuration.');
        }

        $this->processFileFieldMultiple($fieldName, $lifeEvent, (array) $field);
    }

    /**
     * @param string         $fieldName
     * @param LifecycleEvent $lifeEvent
     * @param UploadedFile   $field
     */
    protected function processFileFieldSingle($fieldName, LifecycleEvent $lifeEvent, UploadedFile $field)
    {
        $formData = $lifeEvent->getFormData();

        $newField = $this->processFileUploadField($lifeEvent, $field);

        // Update the stored for data, depending on how we started
        $formData->set($fieldName, $newField);
    }

    /**
     * @param string         $fieldName
     * @param LifecycleEvent $lifeEvent
     * @param UploadedFile[] $fields
     */
    protected function processFileFieldMultiple($fieldName, LifecycleEvent $lifeEvent, array $fields)
    {
        $formData = $lifeEvent->getFormData();
        $newField = [];

        foreach ($fields as $field) {
            $newField[] = $this->processFileUploadField($lifeEvent, $field);
        }

        // Update the stored for data, depending on how we started
        $formData->set($fieldName, $newField);
    }

    /**
     * @param LifecycleEvent $lifeEvent
     * @param UploadedFile   $field
     *
     * @throws FileUploadException
     *
     * @return \Symfony\Component\HttpFoundation\File\File
     */
    protected function processFileUploadField(LifecycleEvent $lifeEvent, UploadedFile $field)
    {
        if (!$field->isValid()) {
            throw new FileUploadException($field->getErrorMessage(), $field->getErrorMessage(), 0, null, false);
        }

        $formConfig = $lifeEvent->getFormConfig();
        $handlerFactory = $this->handlers['upload'];
        /** @var Upload $fileHandler */
        $fileHandler = $handlerFactory($formConfig, $field);
        /** @var FlashBagInterface $flashBag */
        $flashBag = $this->session->getBag('boltforms');

        // Get the upload object
        $file = $fileHandler->handle($flashBag);
        $this->message(sprintf('Moving uploaded file to %s', $fileHandler->fullPath()), Processor::FEEDBACK_DEBUG, LogLevel::DEBUG);

        return $file;
    }
}
