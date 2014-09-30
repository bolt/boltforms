<?php

namespace Bolt\Extension\Bolt\Forms;

use Bolt;
use Bolt\Application;
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
        $this->forms[$formname] = $this->app['form.factory']->createNamedBuilder($formname, $type, $data, $options);
    }

    /**
     * Add a field to the form
     *
     * @param string                                     $formname - Name of the form
     * @param string|Symfony\Component\Form\AbstractType $type
     * @param array                                      $options
     */
    public function addField($formname, $type, array $options)
    {
        $this->forms[$formname]->add($formname, $type, $options);
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
            $this->addField($fieldname, $field['type'], $field['options']);
        }
    }

    /**
     * Render our form into HTML
     *
     * @param  string       $template   A Twig template file name in Twig's path
     * @param  string       $formname   Name of the form
     * @param  array        $twigvalues Associative array of key/value pairs to pass to Twig's render of $template
     * @return \Twig_Markup
     */
    public function renderForm($template, $formname, array $twigvalues = array())
    {
        // Add the form object for use in the template
        $renderdata = array('form' => $this->forms[$formname]->getForm()->createView());

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
     * @param string  $formname
     * @param Request $request
     * @return void
     */
    public function handleRequest($formname, Request $request)
    {
        //
        if(! $this->app['request']->request->has($formname)) {
            die();
        }

        $this->forms[$formname]->handleRequest($request);
    }

    /**
     *
     * @param string  $formname
     * @param Request $request
     * @return boolean
     */
    public function handleIsValid($formname, Request $request)
    {
        //
        if(! $this->app['request']->request->has($formname)) {
            die();
        }

        // Test if form, as submitted, passes validation
        if ($this->forms[$formname]->isValid()) {

            // Get the array of form response values
            $response = $request->get($formname);

            //$x = $response['x'];
            //$y = $response['y'];

            //simpleredirect($app['paths']['hosturl']);
            return true;
        }

        return false;
    }
}
