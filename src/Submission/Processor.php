<?php
namespace Bolt\Extension\Bolt\BoltForms\Submission;

use Bolt;
use Bolt\Application;
use Bolt\Extension\Bolt\BoltForms\Config\FormConfig;
use Bolt\Extension\Bolt\BoltForms\Event\BoltFormsCustomDataEvent;
use Bolt\Extension\Bolt\BoltForms\Event\BoltFormsEvents;
use Bolt\Extension\Bolt\BoltForms\Event\BoltFormsProcessorEvent;
use Bolt\Extension\Bolt\BoltForms\Exception\FileUploadException;
use Bolt\Extension\Bolt\BoltForms\Exception\FormValidationException;
use Bolt\Extension\Bolt\BoltForms\Extension;
use Bolt\Extension\Bolt\BoltForms\FileUpload;
use Bolt\Extension\Bolt\BoltForms\FormData;
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
class Processor
{
    /** @var Application */
    private $app;
    /** @var array */
    private $config;

    /**
     * @param Bolt\Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->config = $app[Extension::CONTAINER]->config;
    }

    /**
     * Process a form's POST request.
     *
     * @param string  $formName
     * @param array   $formDefinition
     * @param array   $recaptchaResponse
     * @param boolean $returnData
     *
     * @throws FormValidationException
     *
     * @return boolean|array
     */
    public function process($formName, array $formDefinition, array $recaptchaResponse, $returnData = false)
    {
        /** @var FormData $formData */
        $formData = $this->getRequestData($formName);
        $formConfig = new FormConfig($formName, $formDefinition);
        $sent = $this->app['boltforms']->getForm($formName)->isSubmitted();

        if ($sent && $formData !== null && $recaptchaResponse['success']) {
            $this->processFields($formConfig, $formData);
            $this->processDatabase($formConfig, $formData);
            $this->processEmailNotification($formConfig, $formData);
            $this->processRedirect($formConfig, $formData);

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
            return array(
                'success'    => true,
                'errorCodes' => null
            );
        }

        $reCaptchaResponse = $this->app['recaptcha']->verify($request->get('g-recaptcha-response'), $request->getClientIp());

        return array(
            'success'    => $reCaptchaResponse->isSuccess(),
            'errorCodes' => $reCaptchaResponse->getErrorCodes()
        );
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
        if (!$this->app['request']->request->has($formName)) {
            return;
        }

        if (!$request) {
            $request = $this->app['request'];
        }

        // Handle the Request object to check if the data sent is valid
        $this->app['boltforms']->getForm($formName)->handleRequest($request);

        // Test if form, as submitted, passes validation
        if ($this->app['boltforms']->getForm($formName)->isValid()) {

            // Submitted data
            $data = $this->app['boltforms']->getForm($formName)->getData();

            $event = new BoltFormsProcessorEvent($formName, $data);
            $this->app['dispatcher']->dispatch(BoltFormsEvents::SUBMISSION_PROCESSOR, $event);

            return new FormData($event->getData());
        }

        return;
    }

    /**
     * Process the fields to get usable data.
     *
     * @param FormConfig $formConfig
     * @param FormData   $formData
     *
     * @throws FileUploadException
     */
    protected function processFields(FormConfig $formConfig, FormData $formData)
    {
        foreach ($formData->keys() as $fieldName) {
            $field = $formData->get($fieldName);

            // Handle file uploads
            if ($field instanceof UploadedFile) {
                if (! $field->isValid()) {
                    throw new FileUploadException($field->getErrorMessage());
                }

                // Get the upload object
                $formData->set($fieldName, new FileUpload($this->app, $formConfig->getName(), $field));

                if (!$this->config['uploads']['enabled']) {
                    $this->app['logger.system']->debug('[BoltForms] File upload skipped as the administrator has disabled uploads for all forms.', array('event' => 'extensions'));
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
     * @param FormConfig $formConfig
     * @param FormData   $formData
     */
    protected function processDatabase(FormConfig $formConfig, FormData $formData)
    {
        // Write to a Contenttype
        if ($formConfig->getDatabase()->getContenttype() !== null) {
            $this->app['boltforms.database']->writeToContentype($formConfig->getDatabase()->getContenttype(), $formData);
        }

        // Write to a normal database table
        if ($formConfig->getDatabase()->getTable() !== null) {
            $this->app['boltforms.database']->writeToTable($formConfig->getDatabase()->getTable(), $formData);
        }
    }

    /**
     * Send email notifications if configured.
     *
     * @param FormConfig $formConfig
     * @param FormData   $formData
     */
    protected function processEmailNotification(FormConfig $formConfig, FormData $formData)
    {
        if ($formConfig->getNotification()->getEnabled()) {
            $this->app['boltforms.email']->doNotification($formConfig, $formData);
        }
    }

    /**
     * Redirect if a redirect is set and the page exists
     *
     * @param FormConfig $formConfig
     * @param FormData   $formData
     */
    protected function processRedirect(FormConfig $formConfig, FormData $formData)
    {
        if ($formConfig->getFeedback()->redirect['target'] !== null) {
            $redirect = new RedirectHandler($this->app['url_matcher']);
            $redirect->redirect($formConfig, $formData);
        }
    }

    /**
     * Dispatch custom data events.
     *
     * @param array $eventConfig
     */
    protected function dispatchCustomDataEvent($eventConfig)
    {
        if (strpos('boltforms.', $eventConfig['name']) === false) {
            $eventName = 'boltforms.' . $eventConfig['name'];
        } else {
            $eventName = $eventConfig['name'];
        }

        if ($this->app['dispatcher']->hasListeners($eventName)) {
            $eventParams = isset($eventConfig['params']) ? $eventConfig['params'] : null;
            $event = new BoltFormsCustomDataEvent($eventName, $eventParams);
            try {
                $this->app['dispatcher']->dispatch($eventName, $event);

                return $event->getData();
            } catch (\Exception $e) {
                $this->app['logger.system']->error("[BoltForms] $eventName subscriber had an error: " . $e->getMessage(), array('event' => 'extensions'));
            }
        }
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
