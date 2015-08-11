<?php
namespace Bolt\Extension\Bolt\BoltForms\Tests;

use Bolt\Extension\Bolt\BoltForms\BoltForms;
use Bolt\Extension\Bolt\BoltForms\Event\BoltFormsEvent;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\Request;

/**
 * BoltFormsEvent class tests.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class BoltFormsEventTest extends AbstractBoltFormsUnitTest
{
    public function testEventValidSetData()
    {
        $app = $this->getApp();
        $app['request'] = Request::create('/');
        $boltforms = new BoltForms($app);

        $boltforms->makeForm('testing_form');
        $fields = $this->formFieldConfig();
        $boltforms->addFieldArray('testing_form', $fields);

        $evt = new FormEvent($boltforms->getForm('testing_form'), array('koala' => 'leaves'));
        $event = new BoltFormsEvent($evt, FormEvents::PRE_SUBMIT);

        $this->assertInstanceOf('Symfony\Component\Form\FormEvent', $event->getEvent());
        $this->assertInstanceOf('Symfony\Component\Form\FormInterface', $event->getForm());
        $this->assertSame(array('koala' => 'leaves'), $event->getData());

        $event->setData('fresh');
    }

    public function testEventInvalidSetData()
    {
        $app = $this->getApp();
        $app['request'] = Request::create('/');
        $boltforms = new BoltForms($app);

        $boltforms->makeForm('testing_form');
        $fields = $this->formFieldConfig();
        $boltforms->addFieldArray('testing_form', $fields);

        $evt = new FormEvent($boltforms->getForm('testing_form'), array('koala' => 'leaves'));
        $event = new BoltFormsEvent($evt, FormEvents::POST_SUBMIT);

        $this->assertInstanceOf('Symfony\Component\Form\FormEvent', $event->getEvent());
        $this->assertInstanceOf('Symfony\Component\Form\FormInterface', $event->getForm());
        $this->assertSame(array('koala' => 'leaves'), $event->getData());

        $this->setExpectedException('\RuntimeException');
        $event->getEvent()->setName(FormEvents::POST_SUBMIT);
        $event->setData('fail');
    }
}
