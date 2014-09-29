<?php

namespace Bolt\Extension\Bolt\Forms;

use Bolt;

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

    public function __construct(Silex\Application $app)
    {
        $this->app = $this->config = $app;
        $this->config = $app[Extension::CONTAINER]->config;
    }

    public function makeForm($name, $options, $data)
    {
        //
        $formdata = new Form();
        $optiondata = array('csrf_protection' => $this->config['csrf']);
        $this->form = $app['form.factory']->createBuilder(new FormType(), $formdata, $optiondata)
                                          ->getForm();

    }

    public function renderForm()
    {
        //
    }

    public function addField($name, $type, $attr, $constraints)
    {
/*
Field Options
    class
    data_class
    em
    group_by
    property
    query_builder

Overridden Options
    choice_list
    choices

Inherited Options
    empty_value
    expanded
    multiple
    preferred_choices
    data
    disabled
    empty_data
    error_bubbling
    error_mapping
    label
    label_attr
    mapped
    read_only
    required
 */
        //
        $this->form->add('body',   'textarea', array(
            'label' => false,
            'attr'  => array(
                'style' => 'height: 150px;'),
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Length(array('min' => 2))
            )));
    }
}