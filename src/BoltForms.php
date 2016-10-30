<?php
namespace Bolt\Extension\Bolt\BoltForms;

use Bolt;
use Bolt\Application;
use Bolt\Extension\Bolt\BoltForms\Subscriber\BoltFormsSubscriber;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormTypeInterface;

/**
 * Core API functions for BoltForms
 *
 * Copyright (C) 2014-2015 Gawain Lynch
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License or GNU Lesser
 * General Public License as published by the Free Software Foundation,
 * either version 3 of the Licenses, or (at your option) any later version.
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
 * @license   http://opensource.org/licenses/LGPL-3.0 GNU Lesser General Public License 3.0
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
        /** @var BoltFormsExtension $extension */
        $extension = $app['extensions']->get('Bolt/BoltForms');
        $this->config = $extension->getConfig();
    }

    /**
     * Get a particular form
     *
     * @param string $formName
     *
     * @return Form
     */
    public function getForm($formName)
    {
        return $this->forms[$formName];
    }

    /**
     * Initial form object constructor
     *
     * @param string                   $formName
     * @param string|FormTypeInterface $type
     * @param mixed                    $data
     * @param array                    $options
     */
    public function makeForm($formName, $type = FormType::class, $data = null, $options = [])
    {
        $options['csrf_protection'] = $this->config['csrf'];
        $this->forms[$formName] = $this->app['form.factory']
            ->createNamedBuilder($formName, $type, $data, $options)
            ->addEventSubscriber(new BoltFormsSubscriber($this->app))
            ->getForm()
        ;
    }

    /**
     * Add a field to the form
     *
     * @param string $formName  Name of the form
     * @param string $fieldName
     * @param string $type
     * @param array  $options
     */
    public function addField($formName, $fieldName, $type, array $options)
    {
        $em = $this->app['storage'];
        $fieldOptions = new FieldOptions($formName, $fieldName, $type, $options, $em, $this->app['logger.system']);

        $this->getForm($formName)->add($fieldName, $type, $fieldOptions->toArray());
    }

    /**
     * Add an array of fields to the form
     *
     * @param string $formName Name of the form
     * @param array  $fields   Associative array keyed on field name => array('type' => '', 'options => array())
     *
     * @return void
     */
    public function addFieldArray($formName, array $fields)
    {
        foreach ($fields as $fieldName => $field) {
            $field['options'] = empty($field['options']) ? [] : $field['options'];
            $this->addField($formName, $fieldName, $field['type'], $field['options']);
        }
    }

    /**
     * Render our form into HTML
     *
     * @param string $formName   Name of the form
     * @param string $template   A Twig template file name in Twig's path
     * @param array  $twigValues Associative array of key/value pairs to pass to Twig's render of $template
     *
     * @return \Twig_Markup
     */
    public function renderForm($formName, $template = '', array $twigValues = [])
    {
        if (empty($template)) {
            $template = $this->config['templates']['form'];
        }

        // Add the form object for use in the template
        $context = ['form' => $this->getForm($formName)->createView()];

        // Add out passed values to the array to be given to render()
        foreach ($twigValues as $twigName => $data) {
            $context[$twigName] = $data;
        }

        // Pray and do the render
        $html = $this->app['twig']->render($template, $context);

        // Return the result
        return new \Twig_Markup($html, 'UTF-8');
    }
}
