<?php

namespace Bolt\Extension\Bolt\BoltForms\Tests;

use Bolt\Extension\Bolt\BoltForms\BoltForms;
use Bolt\Extension\Bolt\BoltForms\Extension;
use Bolt\Tests\BoltUnitTest;
use Symfony\Component\HttpFoundation\Request;

/**
 * Base class for BoltForms testing.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
abstract class AbstractBoltFormsUnitTest extends BoltUnitTest
{
    public function getApp($boot = true)
    {
        $app = parent::getApp($boot);
        $extension = new Extension($app);
        $app['extensions']->register($extension);

        return $app;
    }

    public function getExtension($app = null)
    {
        if ($app === null) {
            $app = $this->getApp();
        }

        return $app["extensions.BoltForms"];
    }

    protected function formValues()
    {
        return array(
            'name' => array(
                'type'    => 'text',
                'options' => array(
                    'required' => true,
                    'label'    => 'Name',
                    'attr'     => array(
                        'placeholder' => 'Your name...'
                    ),
                    'constraints' => array(
                        'NotBlank',
                        array(
                            'Length' => array('min' => 3)
                        )
                    ),
                ),
            ),
            'email' => array(
                'type'    => 'email',
                'options' => array(
                    'required' => true,
                    'label'    => 'Email address',
                    'attr'     => array(
                        'placeholder' => 'Your email...'
                    ),
                    'constraints' => 'Email',
                ),
            ),
            'message' => array(
                'type'    => 'textarea',
                'options' => array(
                    'required' => true,
                    'label'    => 'Your message',
                    'attr'     => array(
                        'placeholder' => 'Your message...',
                        'class'       => 'myclass'
                    ),
                ),
            ),
            'array_index' => array(
                'type'    => 'choice',
                'options' => array(
                    'required' => false,
                    'label'    => 'Should this test pass',
                    'choices'  => array('Yes', 'No'),
                    'multiple' => false
                ),
            ),
            'array_assoc' => array(
                'type'    => 'choice',
                'options' => array(
                    'required' => false,
                    'label'    => 'What is cutest',
                    'choices'  => array(
                        'kittens' => 'Fluffy Kittens',
                        'puppies' => 'Cute Puppies'
                    ),
                    'multiple' => false
                ),
            ),
            'lookup' => array(
                'type'    => 'choice',
                'options' => array(
                    'required' => false,
                    'label'    => 'Select a record',
                    'choices'  => 'contenttype::pages::title::slug',
                    'multiple' => false
                ),
            ),
            'file' => array(
                'type'    => 'file',
                'options' => array(
                    'required' => false,
                    'label'    => 'Attach a file',
                ),
            ),
            'date' => array(
                'type'    => 'datetime',
                'options' => array(
                    'required'    => false,
                    'label'       => 'When should we call',
                    'constraints' => 'DateTime',
                ),
            ),
            'submit' => array(
                'type'    => 'submit',
                'options' => array()
            ),
        );
    }

    protected function formProcessRequest($app)
    {
        $this->getExtension($app)->config['csrf'] = false;

        $app['request'] = Request::create('/');

        $boltforms = new BoltForms($app);
        $boltforms->makeForm('testing_form');

        $fields = $this->formValues();
        $boltforms->addFieldArray('testing_form', $fields);

        $parameters = array(
            'testing_form' => array(
                'name'    => 'Gawain Lynch',
                'email'   => 'gawain.lynch@gmail.com',
                'message' => 'Hello'
            )
        );

        $app['request'] = Request::create('/', 'POST', $parameters);

        return $boltforms->processRequest('testing_form', array('success' => true));
    }
}
