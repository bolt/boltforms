<?php

namespace Bolt\Extension\Bolt\Forms;

use Bolt;
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

    public function __construct(Silex\Application $app)
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
        $this->forms[$formname] = $this->app['form.factory']->createNamedBuilder($type, $formname, $data, $options);
    }

    /**
     * Add a field to the form
     *
     * @param string $formname - Name of the form
     * @param string $type
     * @param array  $options
     */
    public function addField($formname, $type, array $options)
    {
        $this->forms[$formname]->add($formname, $type, $options);
    }

    /**
     * Add an array of fields to the form
     *
     * @param string $formname - Name of the form
     * @param string $type
     * @param array  $fields   - Associative array where the key is the type, and values
     *                           are an array as would be passed to addField()
     */
    public function addFieldArray($formname, $type, array $fields)
    {
        foreach ($fields as $type => $options) {
            $this->forms[$formname]->add($formname, $type, $options);
        }
    }

    /**
     * Render our form into HTML
     *
     * @param  string       $formname
     * @param  array        $twigvalues
     * @return \Twig_Markup
     */
    public function renderForm($formname, $twigvalues)
    {
        // Get the form with all it's addtions
        $this->forms[$formname]->getForm();

        // Add the form object for use in the template
        $renderdata = array('form' => $this->forms[$formname]>createView());

        // Add out passed values to the array to be given to render()
        foreach ($twigvalues as $twigname => $data) {
            $renderdata[$twigname] = $data;
        }

        // Pray and do the render
        $html = $this->app['render']->render($formname, $renderdata);

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
