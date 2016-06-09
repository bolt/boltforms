<?php

namespace Bolt\Extension\Bolt\BoltForms\Tests;

use Bolt\Extension\Bolt\BoltForms\BoltForms;
use Bolt\Extension\Bolt\BoltForms\Extension;
use Bolt\Extension\Bolt\BoltForms\FormData;
use Bolt\Tests\BoltUnitTest;
use Symfony\Component\HttpFoundation\Request;

/**
 * Base class for BoltForms testing.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
abstract class AbstractBoltFormsUnitTest extends BoltUnitTest
{
    /** \Bolt\Application */
    protected $app;

    protected function formNotificationConfig()
    {
        return [
            'enabled'       => true,
            'debug'         => false,
            'from_name'     => 'Gawain Lynch',
            'from_email'    => 'gawain@example.com',
            'replyto_name'  => 'Surprised Koala',
            'replyto_email' => 'surprised.koala@example.com',
            'to_name'       => 'Kenny Koala',
            'to_email'      => 'kenny.koala@example.com',
            'cc_name'       => 'Bob den Otter',
            'cc_email'      => 'bob@example.com',
            'bcc_name'      => 'Lodewijk Evers',
            'bcc_email'     => 'lodewijk@example.com',
            'attach_files'  => true,
        ];
    }

    protected function formProcessRequest($app)
    {
        $this->getExtension()->config['csrf'] = false;

        $app['request'] = Request::create('/');

        $app['boltforms']->makeForm('testing_form');

        $fields = $this->formFieldConfig();
        $app['boltforms']->addFieldArray('testing_form', $fields);

        $parameters = $this->formData();

        $app['request'] = Request::create('/', 'POST', $parameters);

        return $this->processor()->process('testing_form', $fields, ['success' => true]);
    }

    protected function getExtension()
    {
        if ($this->app === null) {
            $this->getApp();
        }

        return $this->app['extensions.BoltForms'];
    }

    protected function getApp($boot = true)
    {
        if ($this->app) {
            return $this->app;
        }

        $app = parent::getApp($boot);
        $extension = new Extension($app);

        $app['extensions']->register($extension);

        unset($app['extensions.BoltForms']->config['contact']);
        $app['extensions.BoltForms']->config['testing_form'] = $this->formFieldBaseConfig();

        return $this->app = $app;
    }

    protected function formFieldBaseConfig()
    {
        return [
            'notification' => [
                'enabled'       => false,
                'debug'         => false,
                'subject'       => 'Your message was submitted',
                'from_name'     => null,
                'from_email'    => null,
                'replyto_name'  => null,
                'replyto_email' => null,
                'to_name'       => null,
                'to_email'      => null,
                'cc_name'       => null,
                'cc_email'      => null,
                'bcc_name'      => null,
                'bcc_email'     => null,
                'attach_files'  => false,
            ],
            'feedback' => [
                'success'  => 'Form submission sucessful',
                'error'    => 'There are errors in the form, please fix before trying to resubmit',
                'redirect' => [
                    'target' => null,
                    'query'  => null,
                ],
            ],
            'database'  => null,
            'templates' => [
                'form'    => 'boltforms_form.twig',
                'subject' => 'boltforms_email_subject.twig',
                'email'   => 'boltforms_email.twig',
            ],
            'fields' => null,
        ];
    }

    protected function formFieldConfig()
    {
        return [
            'name' => [
                'type'    => 'text',
                'options' => [
                    'required' => true,
                    'label'    => 'Name',
                    'attr'     => [
                        'placeholder' => 'Your name...',
                    ],
                    'constraints' => [
                        'NotBlank',
                        [
                            'Length' => ['min' => 3],
                        ],
                    ],
                ],
            ],
            'email' => [
                'type'    => 'email',
                'options' => [
                    'required' => true,
                    'label'    => 'Email address',
                    'attr'     => [
                        'placeholder' => 'Your email...',
                    ],
                    'constraints' => 'Email',
                ],
            ],
            'message' => [
                'type'    => 'textarea',
                'options' => [
                    'required' => true,
                    'label'    => 'Your message',
                    'attr'     => [
                        'placeholder' => 'Your message...',
                        'class'       => 'myclass',
                    ],
                ],
            ],
            'array_index' => [
                'type'    => 'choice',
                'options' => [
                    'required' => false,
                    'label'    => 'Should this test pass',
                    'choices'  => ['Yes', 'No'],
                    'multiple' => false,
                ],
            ],
            'array_assoc' => [
                'type'    => 'choice',
                'options' => [
                    'required' => false,
                    'label'    => 'What is cutest',
                    'choices'  => [
                        'kittens' => 'Fluffy Kittens',
                        'puppies' => 'Cute Puppies',
                    ],
                    'multiple' => false,
                ],
            ],
            'lookup' => [
                'type'    => 'choice',
                'options' => [
                    'required' => false,
                    'label'    => 'Select a record',
                    'choices'  => 'contenttype::pages::title::slug',
                    'multiple' => false,
                ],
            ],
            'file' => [
                'type'    => 'file',
                'options' => [
                    'required' => false,
                    'label'    => 'Attach a file',
                ],
            ],
            'date' => [
                'type'    => 'datetime',
                'options' => [
                    'required'    => false,
                    'label'       => 'When should we call',
                    'constraints' => 'DateTime',
                ],
            ],
            'submit' => [
                'type'    => 'submit',
                'options' => [],
            ],
        ];
    }

    protected function formData()
    {
        return [
            'testing_form' => [
                'name'    => 'Gawain Lynch',
                'email'   => 'gawain.lynch@gmail.com',
                'message' => 'Hello',
                'date'    => [
                    'date' => [
                        'day'   => '23',
                        'month' => '10',
                        'year'  => '2010',
                    ],
                    'time' => [
                        'hour'   => '18',
                        'minute' => '15',
                    ],
                ],
            ],
        ];
    }

    /**
     * @return \Bolt\Extension\Bolt\BoltForms\Submission\Processor
     */
    protected function processor()
    {
        return $this->app['boltforms.processor'];
    }
}
