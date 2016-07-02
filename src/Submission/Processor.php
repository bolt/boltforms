<?php

namespace Bolt\Extension\Bolt\BoltForms\Submission;

use Bolt\Extension\Bolt\BoltForms\BoltForms;
use Bolt\Extension\Bolt\BoltForms\Config\Config;
use Bolt\Extension\Bolt\BoltForms\Config\FormConfig;
use Bolt\Extension\Bolt\BoltForms\Event\BoltFormsEvents;
use Bolt\Extension\Bolt\BoltForms\Event\BoltFormsProcessorEvent;
use Bolt\Extension\Bolt\BoltForms\Event\BoltFormsSubmissionLifecycleEvent as LifecycleEvent;
use Bolt\Extension\Bolt\BoltForms\Exception\FormValidationException;
use Bolt\Extension\Bolt\BoltForms\FormData;
use Bolt\Extension\Bolt\BoltForms\Submission\Processor\ProcessorInterface;
use Psr\Log\LoggerInterface;
use Silex\Application;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
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
    use FeedbackHandlerTrait;

    /** @var Application */
    private $app;
    /** @var Config */
    private $config;
    /** @var BoltForms */
    private $boltForms;
    /** @var EventDispatcherInterface */
    private $dispatcher;
    /** @var LoggerInterface */
    private $loggerSystem;

    /**
     * Constructor.
     *
     * @param Config                   $config
     * @param BoltForms                $boltForms
     * @param EventDispatcherInterface $dispatcher
     * @param LoggerInterface          $loggerSystem
     * @param Application              $app
     */
    public function __construct(
        Config $config,
        BoltForms $boltForms,
        EventDispatcherInterface $dispatcher,
        LoggerInterface $loggerSystem,
        Application $app
    ) {
        $this->app = $app;

        $this->config = $config;
        $this->boltForms = $boltForms;
        $this->dispatcher = $dispatcher;
        $this->loggerSystem = $loggerSystem;
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
        $processor = $this->app['boltforms.processors'][$key];
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
     *
     * @return boolean|array
     */
    public function process(FormConfig $formConfig, array $reCaptchaResponse, $returnData = false)
    {
        $formName = $formConfig->getName();
        /** @var FormData $formData */
        $formData = $this->handleRequest($formName);
        /** @var Form $form */
        $form = $this->boltForms->get($formName)->getForm();
        $formMetaData = $this->boltForms->get($formName)->getMeta();

        if ($formData !== null && $reCaptchaResponse['success']) {
            $lifeEvent = new LifecycleEvent($formConfig, $formData, $formMetaData, $form->getClickedButton());

            // Process
            $this->dispatch(BoltFormsEvents::SUBMISSION_PROCESS_FIELDS, $lifeEvent);
            $this->dispatch(BoltFormsEvents::SUBMISSION_PROCESS_CONTENTTYPE, $lifeEvent);
            $this->dispatch(BoltFormsEvents::SUBMISSION_PROCESS_DATABASE, $lifeEvent);
            $this->dispatch(BoltFormsEvents::SUBMISSION_PROCESS_EMAIL, $lifeEvent);

            // Post processing event
            $processorEvent = new BoltFormsProcessorEvent($formName, $formData->all());
            $this->dispatch(BoltFormsEvents::SUBMISSION_POST_PROCESSOR, $processorEvent);

            // Feedback notices
            $this->dispatch(BoltFormsEvents::SUBMISSION_PROCESS_FEEDBACK, $lifeEvent);

            // Redirect if a redirect is set and the page exists.
            $this->dispatch(BoltFormsEvents::SUBMISSION_PROCESS_REDIRECT, $lifeEvent);

            return $returnData ? $formData : true;
        }

        throw new FormValidationException($formConfig->getFeedback()->getError() ?: 'There are errors in the form, please fix before trying to resubmit');
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
                } catch (HttpException $e) {
                    throw $e;
                } catch (\Exception $e) {
                    $this->exception($e, false, 'An event dispatcher encountered an exception.');
                }
            }
        }
    }

    /**
     * Check reCaptcha, if enabled.
     *
     * @param Request $request
     *
     * @return array
     */
    public function reCaptchaResponse(Request $request)
    {
        // Check reCaptcha, if enabled.  If not just return true
        if ($this->config->getReCaptcha()->get('enabled') === false) {
            return [
                'success'    => true,
                'errorCodes' => null,
            ];
        }

        /** @var \ReCaptcha\ReCaptcha $reCaptcha */
        $reCaptcha = $this->app['recaptcha'];
        $reCaptchaResponse = $reCaptcha->verify($request->get('g-recaptcha-response'), $request->getClientIp());

        return [
            'success'    => $reCaptchaResponse->isSuccess(),
            'errorCodes' => $reCaptchaResponse->getErrorCodes(),
        ];
    }

    /**
     * Handle the request. Caller must test for POST.
     *
     * @param string  $formName The name of the form
     * @param Request $request
     *
     * @return FormData|null
     */
    protected function handleRequest($formName, $request = null)
    {
        if (!$request) {
            $request = $this->app['request_stack']->getCurrentRequest();
        }

        if (!$request->request->has($formName)) {
            return null;
        }

        /** @var Form $form */
        $form = $this->boltForms->get($formName)->getForm();
        // Handle the Request object to check if the data sent is valid
        $form->handleRequest($request);

        // Test if form, as submitted, passes validation
        if ($form->isValid()) {
            // Submitted data
            $data = $form->getData();

            $event = new BoltFormsProcessorEvent($formName, $data);
            $this->dispatch(BoltFormsEvents::SUBMISSION_PRE_PROCESSOR, $event);

            /** @deprecated will be removed in v4 */
            $this->dispatch(BoltFormsEvents::SUBMISSION_PROCESSOR, $event);

            return new FormData($event->getData());
        }

        return null;
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
        return $this->app['logger.system'];
    }

    /**
     * {@inheritdoc}
     */
    protected function getMailer()
    {
        return $this->app['mailer'];
    }
}
