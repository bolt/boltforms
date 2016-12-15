<?php

namespace Bolt\Extension\Bolt\BoltForms\Submission;

use Bolt\Extension\Bolt\BoltForms\BoltForms;
use \Bolt\Extension\Bolt\BoltForms\Config\Config;
use Bolt\Extension\Bolt\BoltForms\Config\FormConfig;
use Bolt\Extension\Bolt\BoltForms\Event\BoltFormsEvents;
use Bolt\Extension\Bolt\BoltForms\Event\LifecycleEvent;
use Bolt\Extension\Bolt\BoltForms\Event\ProcessorEvent;
use Bolt\Extension\Bolt\BoltForms\Exception\FileUploadException;
use Bolt\Extension\Bolt\BoltForms\Exception\FormValidationException;
use Bolt\Extension\Bolt\BoltForms\Exception\InternalProcessorException;
use Bolt\Extension\Bolt\BoltForms\FormData;
use Bolt\Extension\Bolt\BoltForms\Submission\Processor\ProcessorInterface;
use Pimple as Container;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Request processing functions for BoltForms
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
class Processor implements EventSubscriberInterface
{
    use FeedbackTrait;

    const FEEDBACK_INFO = 'info';
    const FEEDBACK_ERROR = 'error';
    const FEEDBACK_DEBUG = 'debug';

    /** @var BoltForms */
    private $boltForms;
    /** @var Container */
    private $handlers;
    /** @var Container */
    private $processors;
    /** @var EventDispatcherInterface */
    private $dispatcher;
    /** @var LoggerInterface */
    private $loggerSystem;
    /** @var FlashBagInterface */
    private $feedback;
    /** @var Config */
    private $config;

    /**
     * Constructor.
     *
     * @param BoltForms                $boltForms
     * @param Config                   $config
     * @param Container                $processors
     * @param Container                $handlers
     * @param EventDispatcherInterface $dispatcher
     * @param LoggerInterface          $loggerSystem
     * @param FlashBagInterface        $feedback
     */
    public function __construct(
        BoltForms $boltForms,
        Config $config,
        Container $processors,
        Container $handlers,
        EventDispatcherInterface $dispatcher,
        LoggerInterface $loggerSystem,
        FlashBagInterface $feedback
    ) {
        $this->boltForms = $boltForms;
        $this->config = $config;
        $this->processors = $processors;
        $this->handlers = $handlers;
        $this->dispatcher = $dispatcher;
        $this->loggerSystem = $loggerSystem;
        $this->feedback = $feedback;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            BoltFormsEvents::SUBMISSION_PROCESS_FIELDS      => ['onProcessLifecycleEvent', 0],
            BoltFormsEvents::SUBMISSION_PROCESS_UPLOADS     => ['onProcessLifecycleEvent', 0],
            BoltFormsEvents::SUBMISSION_PROCESS_CONTENTTYPE => ['onProcessLifecycleEvent', 0],
            BoltFormsEvents::SUBMISSION_PROCESS_DATABASE    => ['onProcessLifecycleEvent', 0],
            BoltFormsEvents::SUBMISSION_PROCESS_EMAIL       => ['onProcessLifecycleEvent', 0],
            BoltFormsEvents::SUBMISSION_PROCESS_FEEDBACK    => ['onProcessLifecycleEvent', 0],
            BoltFormsEvents::SUBMISSION_PROCESS_REDIRECT    => ['onProcessLifecycleEvent', 0],
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
        $map = [
            BoltFormsEvents::SUBMISSION_PROCESS_FIELDS      => 'fields',
            BoltFormsEvents::SUBMISSION_PROCESS_UPLOADS     => 'uploads',
            BoltFormsEvents::SUBMISSION_PROCESS_CONTENTTYPE => 'content',
            BoltFormsEvents::SUBMISSION_PROCESS_DATABASE    => 'database',
            BoltFormsEvents::SUBMISSION_PROCESS_EMAIL       => 'email',
            BoltFormsEvents::SUBMISSION_PROCESS_FEEDBACK    => 'feedback',
            BoltFormsEvents::SUBMISSION_PROCESS_REDIRECT    => 'redirect',
        ];
        $key = $map[$eventName];

        /** @var ProcessorInterface $processor */
        $processor = $this->processors[$key];
        try {
            $processor->process($lifeEvent, $eventName, $dispatcher);
        } catch (InternalProcessorException $e) {
            $this->message('An internal processing error has occurred, and form submission has failed!', static::FEEDBACK_ERROR, LogLevel::ERROR);
            $this->handleInternalProcessorException($e);
        }

        // Move any messages generated
        foreach ($processor->getMessages() as $message) {
            $this->message($message[0], $message[1], $message[2]);
        }
    }

    /**
     * Process a form's POST request.
     *
     * @param FormConfig $formConfig
     * @param array      $reCaptchaResponse
     * @param boolean    $returnData
     *
     * @throws FormValidationException
     * @throws \Exception
     *
     * @return FormData|bool
     */
    public function process(FormConfig $formConfig, array $reCaptchaResponse, $returnData = false)
    {
        $formName = $formConfig->getName();
        /** @var Handler\PostRequest $requestHandler*/
        $requestHandler = $this->handlers['request'];
        /** @var FormData $formData */
        $formData = $requestHandler->handle($formName, $this->boltForms, $this->dispatcher);

        if ($formData !== null && $reCaptchaResponse['success']) {
            $this->dispatchProcessors($formConfig, $formData);

            return $returnData ? $formData : true;
        }

        throw new FormValidationException($formConfig->getFeedback()->getErrorMessage());
    }

    /**
     * Handle an internal exception.
     *
     * @param InternalProcessorException $e
     *
     * @throws \Exception
     */
    protected function handleInternalProcessorException(InternalProcessorException $e)
    {
        $previous = $e->getPrevious() ?: $e;
        if ($e instanceof FileUploadException) {
            $this->message($e->getMessage());
            $this->exception($previous, $e->isAbort(), $e->getSystemMessage());
        } else {
            $this->exception($previous, $e->isAbort(), $e->getMessage());
        }
    }

    /**
     * Dispatch all the processing events.
     *
     * @param FormConfig $formConfig
     * @param FormData   $formData
     *
     * @throws \Exception
     */
    protected function dispatchProcessors(FormConfig $formConfig, FormData $formData)
    {
        $formName = $formConfig->getName();
        /** @var Form $form */
        $form = $this->boltForms->get($formName)->getForm();
        $formMetaData = $this->boltForms->get($formName)->getMeta();

        $lifeEvent = new LifecycleEvent($formConfig, $formData, $formMetaData, $form->getClickedButton());

        // Prepare fields
        $this->dispatch(BoltFormsEvents::SUBMISSION_PROCESS_FIELDS, $lifeEvent);
        $this->dispatch(BoltFormsEvents::SUBMISSION_PROCESS_UPLOADS, $lifeEvent);

        // Process
        if ($formConfig->getDatabase()->getContentType()) {
            $this->dispatch(BoltFormsEvents::SUBMISSION_PROCESS_CONTENTTYPE, $lifeEvent);
        }
        if ($formConfig->getDatabase()->getTable()) {
            $this->dispatch(BoltFormsEvents::SUBMISSION_PROCESS_DATABASE, $lifeEvent);
        }
        if ($formConfig->getNotification()->isEnabled()) {
            $this->dispatch(BoltFormsEvents::SUBMISSION_PROCESS_EMAIL, $lifeEvent);
        }

        // Post processing event
        $processorEvent = new ProcessorEvent($formName, $formData->all());
        $this->dispatch(BoltFormsEvents::SUBMISSION_POST_PROCESSOR, $processorEvent);

        // Feedback notices
        $this->dispatch(BoltFormsEvents::SUBMISSION_PROCESS_FEEDBACK, $lifeEvent);

        // Redirect if a redirect is set and the page exists.
        $this->dispatch(BoltFormsEvents::SUBMISSION_PROCESS_REDIRECT, $lifeEvent);
    }

    /**
     * Dispatch an event.
     *
     * @param string                $eventName
     * @param EventDispatcher\Event $event
     *
     * @throws \Exception
     */
    protected function dispatch($eventName, EventDispatcher\Event $event)
    {
        if ($listeners = $this->dispatcher->getListeners($eventName)) {
            foreach ($listeners as $listener) {
                if ($event->isPropagationStopped()) {
                    break;
                }
                try {
                    call_user_func($listener, $event, $eventName, $this->dispatcher);
                } catch (InternalProcessorException $e) {
                    throw $e;
                } catch (HttpException $e) {
                    throw $e;
                } catch (\Exception $e) {
                    $this->exception($e, $this->config->isDebug(), 'An event dispatcher encountered an exception.');
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getFeedback()
    {
        return $this->feedback;
    }

    /**
     * {@inheritdoc}
     */
    protected function getLogger()
    {
        return $this->loggerSystem;
    }
}
