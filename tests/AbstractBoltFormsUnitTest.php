<?php

namespace Bolt\Extension\Bolt\BoltForms\Tests;

use Bolt\Extension\Bolt\BoltForms\Extension;
use Bolt\Tests\BoltUnitTest;

/**
 * Base class for BoltForms testing.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
abstract class AbstractBoltFormsUnitTest extends BoltUnitTest
{
    public function getApp()
    {
        $app = parent::getApp();
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
            'submit' => array(
                'type'    => 'submit',
                'options' => array()
            ),
        );
    }
}
