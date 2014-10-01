<?php

namespace Bolt\Extension\Bolt\Forms;

use Bolt;
use Bolt\Application;
use Bolt\Extension\Bolt\Forms\Event\FormsEvent;
use Symfony\Component\HttpFoundation\Request;

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
     * Get a particular form
     *
     * @param string               $formname
     * @return FormConfigInterface
     */
    public function getForm($formname)
    {
        return $this->forms[$formname];
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
                $options['constraints'] = $this->getConstraint($options['constraints']);
            } else {
                foreach ($options['constraints'] as $key => $constraint) {
                    $options['constraints'][$key] = $this->getConstraint(array($key => $constraint));
                }
            }
        }
        $this->forms[$formname]->add($fieldname, $type, $options);
    }

    /**
     * Extract, expand and set & create validator instance array(s)
     *
     * @param  mixed                                  $input
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
     * Handle the request.  Caller must test for POST
     *
     * @param  string   $formname  The name of the form
     * @param  Request  $request
     * @param  callable $callback  A PHP callable to call on success
     * @param  mixed    $arguments Arguments to pass to the PHP callable
     * @return mixed    Success - Submitted form parameters, or passed callback function return value
     *                  Failure - false
     */
    public function handleRequest($formname, $request = null, $callback = null, $arguments = array())
    {
        //
        if (! $this->app['request']->request->has($formname)) {
            return false;
        }

        if (! $request) {
            $request = Request::createFromGlobals();
        }

        //
        $this->forms[$formname]->handleRequest($request);

        // Test if form, as submitted, passes validation
        if ($this->forms[$formname]->isValid()) {

            // Submitted data
            $data = $this->app['request']->request->get($formname);

            // Form submission event dispatcher
            if ($this->app['dispatcher']->hasListeners('boltforms.FormSubmission')) {
                $event = new FormsEvent($formname, $this->config[$formname], $data);
                try {
                    $this->app['dispatcher']->dispatch('boltforms.FormSubmission', $event);
                } catch (\Exception $e) {
                    // Log the error
                    $this->app['log']->add('Dispatcher error for boltforms.FormSubmission: ' . $e->getMessage(), 2);
                }
            }

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
}
