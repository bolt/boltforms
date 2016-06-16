<?php

namespace Bolt\Extension\Bolt\BoltForms\Submission;

use Bolt\Extension\Bolt\BoltForms\BoltForms;
use Bolt\Extension\Bolt\BoltForms\Config\Config;
use Bolt\Extension\Bolt\BoltForms\Config\FormConfig;
use Bolt\Extension\Bolt\BoltForms\Config\FormConfigSection;
use Bolt\Extension\Bolt\BoltForms\Event\BoltFormsCustomDataEvent;
use Bolt\Extension\Bolt\BoltForms\Event\BoltFormsEvents;
use Bolt\Extension\Bolt\BoltForms\Event\BoltFormsProcessorEvent;
use Bolt\Extension\Bolt\BoltForms\Event\BoltFormsSubmissionLifecycleEvent as LifecycleEvent;
use Bolt\Extension\Bolt\BoltForms\Exception\FileUploadException;
use Bolt\Extension\Bolt\BoltForms\Exception\FormValidationException;
use Bolt\Extension\Bolt\BoltForms\FormData;
use Bolt\Extension\Bolt\BoltForms\UploadedFileHandler;
use Psr\Log\LoggerInterface;
use Silex\Application;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            BoltFormsEvents::SUBMISSION_PROCESS_FIELDS   => ['processFields', 0],
            BoltFormsEvents::SUBMISSION_PROCESS_DATABASE => ['processDatabase', 0],
            BoltFormsEvents::SUBMISSION_PROCESS_EMAIL    => ['processEmailNotification', 0],
            BoltFormsEvents::SUBMISSION_PROCESS_FEEDBACK => ['processFeedback', 0],
            BoltFormsEvents::SUBMISSION_PROCESS_REDIRECT => ['processRedirect', 0],
        ];
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
        $formData = $this->getRequestData($formName);
        /** @var Form $form */
        $form = $this->boltForms->getForm($formName);
        $complete = $form->isSubmitted() && $form->isValid();

        if ($complete && $formData !== null && $reCaptchaResponse['success']) {
            $lifeEvent = new LifecycleEvent($formConfig, $formData, $form->getClickedButton());

            // Process
            $this->dispatcher->dispatch(BoltFormsEvents::SUBMISSION_PROCESS_FIELDS, $lifeEvent);
            $this->dispatcher->dispatch(BoltFormsEvents::SUBMISSION_PROCESS_DATABASE, $lifeEvent);
            $this->dispatcher->dispatch(BoltFormsEvents::SUBMISSION_PROCESS_EMAIL, $lifeEvent);

            // Post processing event
            $processorEvent = new BoltFormsProcessorEvent($formName, $formData->all());
            $this->dispatcher->dispatch(BoltFormsEvents::SUBMISSION_POST_PROCESSOR, $processorEvent);

            // Feedback notices
            $this->dispatcher->dispatch(BoltFormsEvents::SUBMISSION_PROCESS_FEEDBACK, $lifeEvent);

            // Redirect if a redirect is set and the page exists.
            $this->dispatcher->dispatch(BoltFormsEvents::SUBMISSION_PROCESS_REDIRECT, $lifeEvent);

            return $returnData ? $formData : true;
        }

        throw new FormValidationException($formConfig->getFeedback()->getError() ?: 'There are errors in the form, please fix before trying to resubmit');
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
    protected function getRequestData($formName, $request = null)
    {
        if (!$request) {
            $request = $this->app['request_stack']->getCurrentRequest();
        }

        if (!$request->request->has($formName)) {
            return null;
        }

        /** @var Form $form */
        $form = $this->boltForms->getForm($formName);
        // Handle the Request object to check if the data sent is valid
        $form->handleRequest($request);

        // Test if form, as submitted, passes validation
        if ($form->isValid()) {
            // Submitted data
            $data = $form->getData();

            $event = new BoltFormsProcessorEvent($formName, $data);
            $this->dispatcher->dispatch(BoltFormsEvents::SUBMISSION_PRE_PROCESSOR, $event);

            /** @deprecated will be removed in v4 */
            $this->dispatcher->dispatch(BoltFormsEvents::SUBMISSION_PROCESSOR, $event);

            return new FormData($event->getData());
        }

        return null;
    }

    /**
     * Process the fields to get usable data.
     *
     * @param LifecycleEvent $lifeEvent
     *
     * @throws FileUploadException
     */
    public function processFields(LifecycleEvent $lifeEvent)
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
            $fieldConf = $formConfig->getFields()->{$fieldName}();
            if (isset($fieldConf['event']['name'])) {
                $formData->set($fieldName, $this->dispatchCustomDataEvent($fieldConf['event']));
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
            throw new FileUploadException($field->getErrorMessage(), $field->getErrorMessage());
        }

        $formConfig = $lifeEvent->getFormConfig();
        $formData = $lifeEvent->getFormData();

        // Get the upload object
        /** @var UploadedFileHandler $fileHandler */
        $fileHandler = new UploadedFileHandler($this->config, $formConfig, $field);
        $formData->set($fieldName, $fileHandler);

        if (!$this->config->getUploads()->get('enabled')) {
            $message = '[BoltForms] File upload skipped as the administrator has disabled uploads for all forms.';
            $this->app['boltforms.feedback']->add('error', $message);
            $this->loggerSystem->debug($message, ['event' => 'extensions']);

            return;
        }

        $fileHandler->move();

        $message = '[BoltForms] Moving uploaded file to ' . $fileHandler->fullPath() . '.';
        $this->app['boltforms.feedback']->add('debug', $message);
        $this->loggerSystem->debug($message, ['event' => 'extensions']);
    }

    /**
     * Commit submitted data to the database if configured.
     *
     * @param LifecycleEvent $lifeEvent
     */
    public function processDatabase(LifecycleEvent $lifeEvent)
    {
        $formConfig = $lifeEvent->getFormConfig();
        $formData = $lifeEvent->getFormData();

        // Write to a Contenttype
        if ($formConfig->getDatabase()->getContentType() !== null) {
            $this->app['boltforms.database']->writeToContenType($formConfig->getDatabase()->getContentType(), $formData);
        }

        // Write to a normal database table
        if ($formConfig->getDatabase()->getTable() !== null) {
            $this->app['boltforms.database']->writeToTable($formConfig->getDatabase()->getTable(), $formData);
        }
    }

    /**
     * Send email notifications if configured.
     *
     * @param LifecycleEvent $lifeEvent
     */
    public function processEmailNotification(LifecycleEvent $lifeEvent)
    {
        $formConfig = $lifeEvent->getFormConfig();
        $formData = $lifeEvent->getFormData();

        if ($formConfig->getNotification()->getEnabled()) {
            $this->app['boltforms.email']->doNotification($formConfig, $formData);
        }
    }

    /**
     * Set feedback notices.
     *
     * @param LifecycleEvent $lifeEvent
     */
    public function processFeedback(LifecycleEvent $lifeEvent)
    {
        $formConfig = $lifeEvent->getFormConfig();

        $this->app['boltforms.feedback']->add('info', $formConfig->getFeedback()->getSuccess());
        $this->app['session']->set(sprintf('boltforms_submit_%s', $formConfig->getName()), true);
        $this->app['session']->save();
    }

    /**
     * Redirect if a redirect is set and the page exists.
     *
     *
     * @param LifecycleEvent $lifeEvent
     *
     * @throws HttpException
     */
    public function processRedirect(LifecycleEvent $lifeEvent)
    {
        $formConfig = $lifeEvent->getFormConfig();
        $formData = $lifeEvent->getFormData();

        if ($formConfig->getSubmission()->getAjax()) {
            return;
        }

        $redirect = new RedirectHandler($this->app['url_matcher']);
        if ($formConfig->getFeedback()->getRedirect()->getTarget() !== null) {
            $redirect->redirect($formConfig, $formData);
        }

        // Do a get on the page as it was probably POSTed
        $request = $this->app['request_stack']->getCurrentRequest();
        $redirect->refresh($request);

        throw new HttpException(Response::HTTP_OK, '', null, []);
    }

    /**
     * Dispatch custom data events.
     *
     * @param FormConfigSection $eventConfig
     *
     * @return mixed
     */
    protected function dispatchCustomDataEvent(FormConfigSection $eventConfig)
    {
        if (strpos('boltforms.', $eventConfig->get('name')) === false) {
            $eventName = 'boltforms.' . $eventConfig->get('name');
        } else {
            $eventName = $eventConfig->get('name');
        }

        if (!$this->dispatcher->hasListeners($eventName)) {
            return null;
        }

        $eventParams = $eventConfig->get('params');
        $event = new BoltFormsCustomDataEvent($eventName, $eventParams);
        try {
            $this->dispatcher->dispatch($eventName, $event);

            return $event->getData();
        } catch (\Exception $e) {
            $message = sprintf('[BoltForms] %s subscriber had an error: %s', $eventName, $e->getMessage());
            $this->app['boltforms.feedback']->add('debug', $message);
            $this->loggerSystem->error($message, ['event' => 'extensions']);
        }

        return null;
    }
}
