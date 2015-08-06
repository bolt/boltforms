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
use ReCaptcha\ReCaptcha;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

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
        $this->app = $this->config = $app;
        $this->config = $app[Extension::CONTAINER]->config;
    }

    /**
     * Get a particular form
     *
     * @param string $formname
     *
     * @return FormConfigInterface
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
                $options['constraints'] = $this->getConstraint($options['constraints']);
            } else {
                foreach ($options['constraints'] as $key => $constraint) {
                    $options['constraints'][$key] = $this->getConstraint(array($key => $constraint));
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
     * Extract, expand and set & create validator instance array(s)
     *
     * @param mixed $input
     *
     * @return Symfony\Component\Validator\Constraint
     */
    private function getConstraint($input)
    {
        $params = null;

        $namespace = "\\Symfony\\Component\\Validator\\Constraints\\";

        if (gettype($input) == 'string') {
            $class = $namespace . $input;
        } elseif (gettype($input) == 'array') {
            $input = current($input);
            if (gettype($input) == 'string') {
                $class = $namespace . $input;
            } elseif (gettype($input) == 'array') {
                $class = $namespace . key($input);
                $params = array_pop($input);
            }
        }

        return new $class($params);
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
     * @param string   $formname  The name of the form
     * @param Request  $request
     * @param callable $callback  A PHP callable to call on success
     * @param mixed    $arguments Arguments to pass to the PHP callable
     *
     * @return mixed Success - Submitted form parameters, or passed callback function return value
     *               Failure - false
     */
    public function handleRequest($formname, $request = null, $callback = null, $arguments = array())
    {

        //
        if (!$this->app['request']->request->has($formname)) {
            return false;
        }

        if (!$request) {
            $request = $this->app['request'];
        }

        //
        $this->forms[$formname]->handleRequest($request);

        // Test if form, as submitted, passes validation
        if ($this->forms[$formname]->isValid()) {

            // Submitted data
            $data = $this->forms[$formname]->getData();

            // If passed a callback, call it.  Else return the form data
            if (is_callable($callback)) {
                $arguments[] = $data;

                return call_user_func_array($callback, $arguments);
            } else {
                return $data;
            }
        }

        return false;
    }

    /**
     * Process a form's POST request.
     *
     * @param string $formname
     * @param array  $recaptchaResponse
     *
     * @return boolean
     */
    public function processRequest($formname, array $recaptchaResponse)
    {
        $formdata = $this->handleRequest($formname);
        $sent = $this->getForm($formname)->isSubmitted();

        if ($sent && $formdata && $recaptchaResponse['success']) {
            $formdata = $this->processFields($formname, $formdata);
            $conf = $this->config[$formname];

            // Don't keep token data around where not needed
            unset($formdata['_token']);

            // Write to a Contenttype
            if (isset($conf['database']['contenttype']) && $conf['database']['contenttype']) {
                $this->app['boltforms.database']->writeToContentype($conf['database']['contenttype'], $formdata);
            }

            // Write to a normal database table
            if (isset($conf['database']['table']) && $conf['database']['table']) {
                $this->app['boltforms.database']->writeToTable($conf['database']['table'], $formdata);
            }

            // Send notification email
            if (isset($conf['notification']['enabled']) && $conf['notification']['enabled']) {
                $this->app['boltforms.email']->doNotification($formname, $conf, $formdata);
            }

            // Redirect if a redirect is set and the page exists
            if (isset($conf['feedback']['redirect']) && is_array($conf['feedback']['redirect'])) {
                $this->redirect($formname, $formdata);
            }

            return true;
        }

        throw new FormValidationException(isset($this->config[$formname]['feedback']['error']) ? $this->config[$formname]['feedback']['error'] : 'There are errors in the form, please fix before trying to resubmit');
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
     * Process the fields to get usable data.
     *
     * @param array $formdata
     *
     * @return array
     */
    protected function processFields($formname, array $formdata)
    {
        foreach ($formdata as $field => $value) {
            // Handle dates
            if ($value instanceof \DateTime) {
                $formdata[$field] = $value->format('c');
            }

            // Handle file uploads
            if ($value instanceof UploadedFile) {
                if (!$value->isValid()) {
                    throw new FileUploadException($value->getErrorMessage());
                }

                // Get the upload object
                $formdata[$field] = new FileUpload($this->app, $formname, $value);

                if (!$this->config['uploads']['enabled']) {
                    $this->app['logger.system']->debug('[BoltForms] File upload skipped as the administrator has disabled uploads for all forms.', array('event' => 'extensions'));
                    continue;
                }

                // Take configured actions on the file
                $formdata[$field]->move();
            }

            // Handle events for custom data
            if (isset($this->config[$formname]['fields'][$field]['event']['name'])) {
                $formdata[$field] = $this->dispatchCustomDataEvent($formname, $field, $value);
            }
        }

        return $formdata;
    }

    protected function dispatchCustomDataEvent($formname, $field, $value)
    {
        $fieldConfig = $this->config[$formname]['fields'][$field];
        if (strpos('boltforms.', $fieldConfig['event']['name']) === false) {
            $eventName = 'boltforms.' . $fieldConfig['event']['name'];
        } else {
            $eventName = $fieldConfig['event']['name'];
        }

        if ($this->app['dispatcher']->hasListeners($eventName)) {
            $eventParams = isset($fieldConfig['event']['params']) ? $fieldConfig['event']['params'] : null;
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
     * @param string $formname
     * @param array  $formdata
     */
    private function redirect($formname, array $formdata)
    {
        $redirect = $this->config[$formname]['feedback']['redirect'];
        $query = $this->getRedirectQuery($redirect, $formdata);

        $response = $this->getRedirectResponse($redirect, $query);
        if ($response instanceof RedirectResponse) {
            $response->send();
        }
    }

    /**
     * Build a GET query if required.
     *
     * @param array $redirect
     * @param array $formdata
     */
    private function getRedirectQuery(array $redirect, $formdata)
    {
        $query = array();
        if (Arr::isIndexedArray($redirect['query'])) {
            foreach ($redirect['query'] as $param) {
                $query[$param] = $formdata[$param];
            }
        } else {
            $query = $redirect['query'];
        }

        return '?' . http_build_query($query);
    }

    /**
     * Get the redirect response object.
     *
     * @param array  $redirect
     * @param string $query
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    private function getRedirectResponse(array $redirect, $query)
    {
        if (strpos($redirect['target'], 'http') === 0) {
            return $this->app->redirect($redirect['target'] . $query);
        } elseif ($redirectpage = $this->app['storage']->getContent($redirect['target'])) {
            return new RedirectResponse($redirectpage->link() . $query);
        }

        // No route found
        return;
    }
}
