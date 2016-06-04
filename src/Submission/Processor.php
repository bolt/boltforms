<?php
namespace Bolt\Extension\Bolt\BoltForms\Submission;

use Bolt\Extension\Bolt\BoltForms\BoltFormsExtension;
use Bolt\Extension\Bolt\BoltForms\Event\BoltFormsCustomDataEvent;
use Bolt\Extension\Bolt\BoltForms\Event\BoltFormsEvents;
use Bolt\Extension\Bolt\BoltForms\Event\BoltFormsProcessorEvent;
use Bolt\Extension\Bolt\BoltForms\Event\BoltFormsSubmissionLifecycleEvent as LifecycleEvent;
use Bolt\Extension\Bolt\BoltForms\Exception\FileUploadException;
use Bolt\Extension\Bolt\BoltForms\Exception\FormValidationException;
use Bolt\Extension\Bolt\BoltForms\FileUpload;
use Bolt\Extension\Bolt\BoltForms\FormData;
use Silex\Application;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

/**
 * Request processing functions for BoltForms
 *
 * Copyright (C) 2014-2015 Gawain Lynch
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
class Processor implements EventSubscriberInterface
{
    /** @var Application */
    private $app;
    /** @var array */
    private $config;

    /**
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        /** @var BoltFormsExtension $extension */
        $extension = $app['extensions']->get('Bolt/BoltForms');
        $this->config = $extension->getConfig();
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
     * @param string  $formName
     * @param null    $formDefinition    @deprecated — To be removed in 4.0
     * @param array   $recaptchaResponse
     * @param boolean $returnData
     *
     * @throws FormValidationException
     *
     * @return boolean|array
     */
    public function process($formName, $formDefinition = null, array $recaptchaResponse, $returnData = false)
    {
        /** @var FormData $formData */
        $formData = $this->getRequestData($formName);
        $formConfig = $this->app['boltforms']->getFormConfig($formName);
        $form = $this->app['boltforms']->getForm($formName);
        $complete = $form->isSubmitted() && $form->isValid();

        if ($complete && $formData !== null && $recaptchaResponse['success']) {
            /** @var EventDispatcherInterface $dispatcher */
            $dispatcher = $this->app['dispatcher'];
            $lifeEvent = new LifecycleEvent($formConfig, $formData, $form->getClickedButton());

            // Process
            $dispatcher->dispatch(BoltFormsEvents::SUBMISSION_PROCESS_FIELDS, $lifeEvent);
            $dispatcher->dispatch(BoltFormsEvents::SUBMISSION_PROCESS_DATABASE, $lifeEvent);
            $dispatcher->dispatch(BoltFormsEvents::SUBMISSION_PROCESS_EMAIL, $lifeEvent);

            // Post processing event
            $processorEvent = new BoltFormsProcessorEvent($formName, $formData->all());
            $dispatcher->dispatch(BoltFormsEvents::SUBMISSION_POST_PROCESSOR, $processorEvent);

            // Feedback notices
            $dispatcher->dispatch(BoltFormsEvents::SUBMISSION_PROCESS_FEEDBACK, $lifeEvent);

            // Redirect if a redirect is set and the page exists.
            $dispatcher->dispatch(BoltFormsEvents::SUBMISSION_PROCESS_REDIRECT, $lifeEvent);

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
        if ($this->config['recaptcha']['enabled'] === false) {
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
        $form = $this->app['boltforms']->getForm($formName);
        // Handle the Request object to check if the data sent is valid
        $form->handleRequest($request);

        // Test if form, as submitted, passes validation
        if ($form->isValid()) {
            // Submitted data
            $data = $form->getData();

            $event = new BoltFormsProcessorEvent($formName, $data);
            $this->app['dispatcher']->dispatch(BoltFormsEvents::SUBMISSION_PRE_PROCESSOR, $event);

            /** @deprecated will be removed in v4 */
            $this->app['dispatcher']->dispatch(BoltFormsEvents::SUBMISSION_PROCESSOR, $event);

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
                if (!$field->isValid()) {
                    throw new FileUploadException($field->getErrorMessage());
                }

                // Get the upload object
                $formData->set($fieldName, new FileUpload($this->app, $formConfig->getName(), $field));

                if (!$this->config['uploads']['enabled']) {
                    $message = '[BoltForms] File upload skipped as the administrator has disabled uploads for all forms.';
                    $this->app['boltforms.feedback']->add('debug', $message);
                    $this->app['logger.system']->debug($message, ['event' => 'extensions']);
                    continue;
                }

                // Take configured actions on the file
                $formData->get($fieldName)->move();
            }

            // Handle events for custom data
            $fieldConf = $formConfig->getFields()->{$fieldName}();
            if (isset($fieldConf['event']['name'])) {
                $formData->set($fieldName, $this->dispatchCustomDataEvent($fieldConf['event']));
            }
        }
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
     * @param LifecycleEvent $lifeEvent
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

        $request = $this->app['request_stack']->getCurrentRequest();
        $redirect->refresh($request);
    }

    /**
     * Dispatch custom data events.
     *
     * @param array $eventConfig
     *
     * @return mixed
     */
    protected function dispatchCustomDataEvent($eventConfig)
    {
        if (strpos('boltforms.', $eventConfig['name']) === false) {
            $eventName = 'boltforms.' . $eventConfig['name'];
        } else {
            $eventName = $eventConfig['name'];
        }

        if (!$this->app['dispatcher']->hasListeners($eventName)) {
            $eventParams = isset($eventConfig['params']) ? $eventConfig['params'] : null;
            $event = new BoltFormsCustomDataEvent($eventName, $eventParams);
            try {
                $this->app['dispatcher']->dispatch($eventName, $event);

                return $event->getData();
            } catch (\Exception $e) {
                $message = sprintf('[BoltForms] %s subscriber had an error: %s', $eventName, $e->getMessage());
                $this->app['boltforms.feedback']->add('debug', $message);
                $this->app['logger.system']->error($message, ['event' => 'extensions']);
            }
        }

        return null;
    }

    /**
     * Enable handling of form specific templates.
     *
     * @param array $formDefinition
     *
     * @return array
     */
    protected function getFormTemplates(array $formDefinition)
    {
        if (isset($formDefinition['templates']) && is_array($formDefinition['templates'])) {
            array_merge($this->config['templates'], $formDefinition['templates']);
        } else {
            $formDefinition['templates'] = $this->config['templates'];
        }

        return $formDefinition;
    }
}
