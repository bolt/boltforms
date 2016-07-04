<?php

namespace Bolt\Extension\Bolt\BoltForms\Submission;

use Bolt\Extension\Bolt\BoltForms\BoltForms;
use Bolt\Extension\Bolt\BoltForms\Config\Config;
use Bolt\Extension\Bolt\BoltForms\Config\FormConfig;
use Bolt\Extension\Bolt\BoltForms\Event\BoltFormsEvents;
use Bolt\Extension\Bolt\BoltForms\Event\BoltFormsProcessorEvent;
use Bolt\Extension\Bolt\BoltForms\Event\BoltFormsSubmissionLifecycleEvent as LifecycleEvent;
use Bolt\Extension\Bolt\BoltForms\Exception\FormValidationException;
use Bolt\Extension\Bolt\BoltForms\Exception\InternalProcessorException;
use Bolt\Extension\Bolt\BoltForms\FormData;
use Bolt\Extension\Bolt\BoltForms\Submission\Processor\ProcessorInterface;
use Pimple as Container;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Silex\Application;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Request processing functions for BoltForms
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
class Processor implements EventSubscriberInterface
{
    use FeedbackTrait;

    /** @var Application */
    private $app;
    /** @var Config */
    private $config;
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

    /**
     * Constructor.
     *
     * @param Config                   $config
     * @param BoltForms                $boltForms
     * @param Container                $processors
     * @param Container                $handlers
     * @param EventDispatcherInterface $dispatcher
     * @param LoggerInterface          $loggerSystem
     * @param Application              $app
     */
    public function __construct(
        Config $config,
        BoltForms $boltForms,
        Container $processors,
        Container $handlers,
        EventDispatcherInterface $dispatcher,
        LoggerInterface $loggerSystem,
        Application $app
    ) {
        $this->config = $config;
        $this->boltForms = $boltForms;
        $this->processors = $processors;
        $this->handlers = $handlers;
        $this->dispatcher = $dispatcher;
        $this->loggerSystem = $loggerSystem;

        $this->app = $app;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            BoltFormsEvents::SUBMISSION_PROCESS_FIELDS      => ['onProcessLifecycleEvent', 0],
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
            BoltFormsEvents::SUBMISSION_PROCESS_CONTENTTYPE => 'content',
            BoltFormsEvents::SUBMISSION_PROCESS_DATABASE    => 'database',
            BoltFormsEvents::SUBMISSION_PROCESS_EMAIL       => 'email',
            BoltFormsEvents::SUBMISSION_PROCESS_FEEDBACK    => 'feedback',
            BoltFormsEvents::SUBMISSION_PROCESS_REDIRECT    => 'redirect',
        ];
        $key = $map[$eventName];

        /** @var ProcessorInterface $processor */
        $processor = $this->processors[$key];
        $processor->process($lifeEvent, $eventName, $dispatcher);

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
     * @return array|bool
     */
    public function process(FormConfig $formConfig, array $reCaptchaResponse, $returnData = false)
    {
        $formName = $formConfig->getName();
        /** @var Handler\Request $requestHandler*/
        $requestHandler = $this->handlers['request'];
        /** @var FormData $formData */
        $formData = $requestHandler->handle($formName, $this->boltForms, $this->dispatcher);


        if ($formData !== null && $reCaptchaResponse['success']) {
            try {
                $this->dispatchProcessors($formConfig, $formData);
            } catch (InternalProcessorException $e) {
                $this->message('An internal processing error has occurred, and form submission has failed!', 'error', LogLevel::ERROR);

                return false;
            }

            return $returnData ? $formData : true;
        }

        throw new FormValidationException($formConfig->getFeedback()->getError() ?: 'There are errors in the form, please fix before trying to resubmit');
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

        // Process
        if ($formConfig->getDatabase()->get('contenttype', false)) {
            $this->dispatch(BoltFormsEvents::SUBMISSION_PROCESS_CONTENTTYPE, $lifeEvent);
        }
        if ($formConfig->getDatabase()->get('table', false)) {
            $this->dispatch(BoltFormsEvents::SUBMISSION_PROCESS_DATABASE, $lifeEvent);
        }
        if ($formConfig->getNotification()->getBoolean('enabled')) {
            $this->dispatch(BoltFormsEvents::SUBMISSION_PROCESS_EMAIL, $lifeEvent);
        }

        // Post processing event
        $processorEvent = new BoltFormsProcessorEvent($formName, $formData->all());
        $this->dispatch(BoltFormsEvents::SUBMISSION_POST_PROCESSOR, $processorEvent);

        // Feedback notices
        $this->dispatch(BoltFormsEvents::SUBMISSION_PROCESS_FEEDBACK, $lifeEvent);

        // Redirect if a redirect is set and the page exists.
        $this->dispatch(BoltFormsEvents::SUBMISSION_PROCESS_REDIRECT, $lifeEvent);
    }

    /**
     * Dispatch an event.
     *
     * @param string $eventName
     * @param Event  $event
     *
     * @throws \Exception
     */
    protected function dispatch($eventName, Event $event)
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
                    $this->exception($e, false, 'An event dispatcher encountered an exception.');
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getFeedback()
    {
        return $this->app['boltforms.feedback'];
    }

    /**
     * {@inheritdoc}
     */
    protected function getLogger()
    {
        return $this->loggerSystem;
    }

    /**
     * {@inheritdoc}
     */
    protected function getMailer()
    {
        return $this->app['mailer'];
    }
}
