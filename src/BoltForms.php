<?php

namespace Bolt\Extension\Bolt\BoltForms;

use Bolt;
use Bolt\Application;
use Bolt\Extension\Bolt\BoltForms\Choice\ArrayType;
use Bolt\Extension\Bolt\BoltForms\Choice\ContentType;
use Bolt\Extension\Bolt\BoltForms\Event\BoltFormsCustomDataEvent;
use Bolt\Extension\Bolt\BoltForms\Exception\FileUploadException;
use Bolt\Extension\Bolt\BoltForms\Exception\FormValidationException;
use Bolt\Extension\Bolt\BoltForms\Subscriber\BoltFormsSubscriber;
use Bolt\Helpers\Arr;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Bolt\Extension\Bolt\BoltForms\Config\FormConfig;

/**
 * Core API functions for BoltForms
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
class BoltForms
{
    /** @var Application */
    private $app;
    /** @var array */
    private $config;
    /** @var array */
    private $forms;

    /**
     * @param Bolt\Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->config = $app[Extension::CONTAINER]->config;
    }

    /**
     * Get a particular form
     *
     * @param string $formname
     *
     * @return Form
     */
    public function getForm($formname)
    {
        return $this->forms[$formname];
    }

    /**
     * Initial form object constructor
     *
     * @param string                   $formname
     * @param string|FormTypeInterface $type
     * @param mixed                    $data
     * @param array                    $options
     */
    public function makeForm($formname, $type = 'form', $data = null, $options = array())
    {
        $options['csrf_protection'] = $this->config['csrf'];
        $this->forms[$formname] = $this->app['form.factory']->createNamedBuilder($formname, $type, $data, $options)
                                                            ->addEventSubscriber(new BoltFormsSubscriber($this->app))
                                                            ->getForm();
    }

    /**
     * Add a field to the form
     *
     * @param string $formname Name of the form
     * @param string $type
     * @param array  $options
     */
    public function addField($formname, $fieldname, $type, array $options)
    {
        if (isset($options['constraints'])) {
            if (gettype($options['constraints']) == 'string') {
                $options['constraints'] = $this->getConstraint($formname, $options['constraints']);
            } else {
                foreach ($options['constraints'] as $key => $constraint) {
                    $options['constraints'][$key] = $this->getConstraint($formname, array($key => $constraint));
                }
            }
        }

        if ($type === 'choice') {
            if (is_string($options['choices']) && strpos($options['choices'], 'contenttype::') === 0) {
                $choice = new ContentType($this->app, $fieldname, $options['choices']);
            } else {
                $choice = new ArrayType($fieldname, $options['choices']);
            }

            $options['choices'] = $choice->getChoices();
        }

        $this->forms[$formname]->add($fieldname, $type, $options);
    }

    /**
     * Add an array of fields to the form
     *
     * @param string $formname Name of the form
     * @param array  $fields   Associative array keyed on field name => array('type' => '', 'options => array())
     *
     * @return void
     */
    public function addFieldArray($formname, array $fields)
    {
        foreach ($fields as $fieldname => $field) {
            $field['options'] = empty($field['options']) ? array() : $field['options'];
            $this->addField($formname, $fieldname, $field['type'], $field['options']);
        }
    }

    /**
     * Extract, expand and set & create validator instance array(s)
     *
     * @param string $formname
     * @param mixed  $input
     *
     * @return Symfony\Component\Validator\Constraint
     */
    private function getConstraint($formname, $input)
    {
        $params = null;

        $namespace = "\\Symfony\\Component\\Validator\\Constraints\\";

        if (gettype($input) === 'string') {
            $class = $namespace . $input;
        } elseif (gettype($input) === 'array') {
            $input = current($input);
            if (gettype($input) === 'string') {
                $class = $namespace . $input;
            } elseif (gettype($input) === 'array') {
                $class = $namespace . key($input);
                $params = array_pop($input);
            }
        }

        if (class_exists($class)) {
            return new $class($params);
        }

        $this->app['logger.system']->error("[BoltForms] The form '$formname' has an invalid field constraint: '$class'.", array('event' => 'extensions'));
    }

    /**
     * Render our form into HTML
     *
     * @param string $formname   Name of the form
     * @param string $template   A Twig template file name in Twig's path
     * @param array  $twigvalues Associative array of key/value pairs to pass to Twig's render of $template
     *
     * @return \Twig_Markup
     */
    public function renderForm($formname, $template = '', array $twigvalues = array())
    {
        if (empty($template)) {
            $template = $this->config['templates']['form'];
        }

        // Add the form object for use in the template
        $renderdata = array('form' => $this->forms[$formname]->createView());

        // Add out passed values to the array to be given to render()
        foreach ($twigvalues as $twigname => $data) {
            $renderdata[$twigname] = $data;
        }

        //
        $this->app['twig.loader.filesystem']->addPath(dirname(__DIR__) . '/assets');

        // Pray and do the render
        $html = $this->app['render']->render($template, $renderdata);

        // Return the result
        return new \Twig_Markup($html, 'UTF-8');
    }

    /**
     * Handle the request.  Caller must test for POST
     *
     * @param string  $formname The name of the form
     * @param Request $request
     *
     * @return FormData|null
     */
    public function handleRequest($formname, $request = null)
    {
        if (!$this->app['request']->request->has($formname)) {
            return;
        }

        if (!$request) {
            $request = $this->app['request'];
        }

        // Handle the Request object to check if the data sent is valid
        $this->forms[$formname]->handleRequest($request);

        // Test if form, as submitted, passes validation
        if ($this->forms[$formname]->isValid()) {

            // Submitted data
            $data = $this->forms[$formname]->getData();

            return new FormData($data);
        }

        return;
    }

    /**
     * Process a form's POST request.
     *
     * @param string  $formName
     * @param array   $recaptchaResponse
     * @param boolean $returnData
     *
     * @throws FormValidationException
     *
     * @return boolean|array
     */
    public function processRequest($formName, array $recaptchaResponse, $returnData = false)
    {
        /** @var FormData $formData */
        $formData = $this->handleRequest($formName);
        $formConfig = New FormConfig($this->config[$formName]);
        $sent = $this->getForm($formName)->isSubmitted();

        if ($sent && $formData !== null && $recaptchaResponse['success']) {
            $this->processFields($formName, $formConfig, $formData);

            // Write to a Contenttype
            if ($formConfig->getDatabase()->getContenttype() !== null) {
                $this->app['boltforms.database']->writeToContentype($formConfig->getDatabase()->getContenttype(), $formData);
            }

            // Write to a normal database table
            if ($formConfig->getDatabase()->getTable() !== null) {
                $this->app['boltforms.database']->writeToTable($formConfig->getDatabase()->getTable(), $formData);
            }

            // Send notification email
            if ($formConfig->getNotification()->getEnabled()) {
                $this->app['boltforms.email']->doNotification($formConfig, $formData);
            }

            // Redirect if a redirect is set and the page exists
            if ($formConfig->getFeedback()->redirect['target'] !== null) {
                $this->redirect($formName, $formData);
            }

            if ($returnData) {
                return $formData;
            }

            return true;
        }

        throw new FormValidationException($formConfig->getFeedback()->getError() ?: 'There are errors in the form, please fix before trying to resubmit');
    }

    /**
     * Process the fields to get usable data.
     *
     * @param string     $formName
     * @param FormConfig $formConfig
     * @param FormData   $formData
     *
     * @throws FileUploadException
     */
    protected function processFields($formName, FormConfig $formConfig, FormData $formData)
    {
        foreach ($formData->keys() as $fieldName) {
            $field = $formData->get($fieldName);

            // Handle file uploads
            if ($field instanceof UploadedFile) {
                if (! $field->isValid()) {
                    throw new FileUploadException($field->getErrorMessage());
                }

                // Get the upload object
                $formData->set($fieldName, new FileUpload($this->app, $formName, $field));

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
                $formData->set($fieldName, $this->dispatchCustomDataEvent($formName, $fieldConf['event']));
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
    public function getReCaptchaResponses(Request $request)
    {
        // Check reCaptcha, if enabled.  If not just return true
        if (!$this->config['recaptcha']['enabled']) {
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
     * Dispatch custom data events.
     *
     * @param string $formname
     * @param array  $eventConfig
     */
    protected function dispatchCustomDataEvent($formname, $eventConfig)
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
                $this->app['logger.system']->info("[BoltForms] $eventName subscriber had an error: " . $e->getMessage(), array('event' => 'extensions'));
            }
        }
    }

    /**
     * Do a redirect.
     *
     * @param string   $formname
     * @param FormData $formData
     */
    private function redirect($formname, FormData $formData)
    {
        $redirect = $this->config[$formname]['feedback']['redirect'];
        $query = $this->getRedirectQuery($redirect, $formData);

        $response = $this->getRedirectResponse($redirect, $query);
        if ($response instanceof RedirectResponse) {
            $response->send();
        }
    }

    /**
     * Build a GET query if required.
     *
     * @param array    $redirect
     * @param FormData $formData
     *
     * @return string
     */
    private function getRedirectQuery(array $redirect, FormData $formData)
    {
        if (!isset($redirect['query']) || empty($redirect['query'])) {
            return '';
        }

        $query = array();
        if (is_array($redirect['query'])) {
            if (Arr::isIndexedArray($redirect['query'])) {
                foreach ($redirect['query'] as $param) {
                    $query[$param] = $formData->get($param);
                }
            } else {
                foreach ($redirect['query'] as $id => $param) {
                    $query[$id] = $formData->get($param);
                }
            }
        } else {
            $param = $redirect['query'];
            $query[$param] = $formData->get($param);
        }

        return '?' . http_build_query($query);
    }

    /**
     * Get the redirect response object.
     *
     * @param array  $redirect
     * @param string $query
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|null
     */
    private function getRedirectResponse(array $redirect, $query)
    {
        if (strpos($redirect['target'], 'http') === 0) {
            return $this->app->redirect($redirect['target'] . $query);
        } else {
            try {
                $url = '/' . ltrim($redirect['target'], '/');
                $this->app['url_matcher']->match($url);

                return new RedirectResponse($url . $query);
            } catch (ResourceNotFoundException $e) {
                // No route found… Go home site admin, you're… um… putting a bad route in!
                return;
            }
        }
    }
}
