<?php

namespace Bolt\Extension\Bolt\Forms;

use Bolt;
use Bolt\Application;
use Bolt\Extension\Bolt\Forms\FormsEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

class Forms
{

    /**
     * @var Application
     */
    private $app;

    /**
     * @var array
     */
    private $config;

    /**
     * @var array  FormBuilder - The form builder for each form
     */
    private $forms;

    /**
     *
     * @param Bolt\Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $this->config = $app;
        $this->config = $app[Extension::CONTAINER]->config;
    }

    /**
     *
     * @param string                   $formname
     * @param string|FormTypeInterface $type
     * @param mixed                    $data
     * @param array                    $options
     */
    public function makeForm($formname, $type = 'form', $data = null, $options = array())
    {
        // FormBuilder	The form builder

        $options['csrf_protection'] = $this->config['csrf'];
        $this->forms[$formname] = $this->app['form.factory']->createNamedBuilder($formname, $type, $data, $options)
                                                            ->getForm();
    }

    /**
     * Add a field to the form
     *
     * @param string $formname - Name of the form
     * @param string $type
     * @param array  $options
     */
    public function addField($formname, $fieldname, $type, array $options)
    {
        if (isset($options['constraints'])) {
            if (gettype($options['constraints']) == 'string') {
                $options['constraints'] = new $options['constraints'];
            } else {
                foreach ($options['constraints'] as $key => $constraint) {
                    $options['constraints'][$key] = new $constraint;
                }
            }
        }
        $this->forms[$formname]->add($fieldname, $type, $options);
    }

    /**
     * Add an array of fields to the form
     *
     * @param  string $formname - Name of the form
     * @param  array  $fields   - Associative array keyed on field name => array('type' => '', 'options => array())
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
     * @param  string       $formname   Name of the form
     * @param  string       $template   A Twig template file name in Twig's path
     * @param  array        $twigvalues Associative array of key/value pairs to pass to Twig's render of $template
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
     *
     * @param  string  $formname
     * @param  Request $request
     * @return void
     */
    public function handleRequest($formname, $request = null)
    {
        //
        if (! $this->app['request']->request->has($formname)) {
            die();
        }

        if (! $request) {
            $request = Request::createFromGlobals();
        }

        $this->forms[$formname]->handleRequest($request);
    }

    /**
     *
     * @param  string   $formname  The name of the form
     * @param  callable $callback  A PHP callable to call on success
     * @param  mixed    $arguments Arguments to pass to the PHP callable
     * @return boolean
     */
    public function handleIsValid($formname, $callback = null, $arguments = array())
    {
        //
        if (! $this->app['request']->request->has($formname)) {
            die();
        }

        // Test if form, as submitted, passes validation
        if ($this->forms[$formname]->isValid()) {
            // Pre-processing event dispatcher
            if ($this->app['dispatcher']->hasListeners('boltforms.FormSubmission')) {
                $event = new FormsEvent($formname, $formconfig, $data);
                try {
                    $this->app['dispatcher']->dispatch('boltforms.FormSubmission', $event);
                } catch (\Exception $e) {
                }
            }

            if (is_callable($callback)) {
                return call_user_func_array($callback, $arguments);
            } else {
                return true;
            }
        }

        return false;
    }
}
