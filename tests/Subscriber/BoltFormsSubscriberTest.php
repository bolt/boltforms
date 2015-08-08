<?php

namespace Bolt\Extension\Bolt\BoltForms\Tests;

use Bolt\Extension\Bolt\BoltForms\BoltForms;
use Bolt\Extension\Bolt\BoltForms\Event\BoltFormsEvents;

class BoltFormsSubscriberTest extends AbstractBoltFormsUnitTest
{
    public function validCallable()
    {
        echo 'Valid Dispatch';
    }

    public function invalidCallable()
    {
        echo 'Dispatch Exception';
        throw new \RuntimeException();
    }

    public function testPreSetDataValid()
    {
        $app = $this->getApp(false);
        $app['dispatcher']->addListener(BoltFormsEvents::PRE_SET_DATA,  array($this, 'validCallable'));
        $app->boot();

        $boltforms = new BoltForms($app);
        $boltforms->makeForm('testing_form');

        $this->expectOutputString('Valid Dispatch');
    }

    public function testPreSetDataException()
    {
        $app = $this->getApp(false);
        $app['dispatcher']->addListener(BoltFormsEvents::PRE_SET_DATA,  array($this, 'invalidCallable'));
        $app->boot();

        $boltforms = new BoltForms($app);
        $boltforms->makeForm('testing_form');

        $this->expectOutputString('Dispatch Exception');
    }

    public function testPostSetDataValid()
    {
        $app = $this->getApp(false);
        $app['dispatcher']->addListener(BoltFormsEvents::POST_SET_DATA,  array($this, 'validCallable'));
        $app->boot();

        $boltforms = new BoltForms($app);
        $boltforms->makeForm('testing_form');

        $this->expectOutputString('Valid Dispatch');
    }

    public function testPostSetDataException()
    {
        $app = $this->getApp(false);
        $app['dispatcher']->addListener(BoltFormsEvents::POST_SET_DATA,  array($this, 'invalidCallable'));
        $app->boot();

        $boltforms = new BoltForms($app);
        $boltforms->makeForm('testing_form');

        $this->expectOutputString('Dispatch Exception');
    }

    public function testPreSubmitValid()
    {
        $app = $this->getApp(false);
        $app['dispatcher']->addListener(BoltFormsEvents::PRE_SUBMIT,  array($this, 'validCallable'));
        $app->boot();

        $this->formProcessRequest($app);

        $this->expectOutputString('Valid Dispatch');
    }

    public function testPreSubmitException()
    {
        $app = $this->getApp(false);
        $app['dispatcher']->addListener(BoltFormsEvents::PRE_SUBMIT,  array($this, 'invalidCallable'));
        $app->boot();

        $this->formProcessRequest($app);

        $this->expectOutputString('Dispatch Exception');
    }

    public function testSubmitValid()
    {
        $app = $this->getApp(false);
        $app['dispatcher']->addListener(BoltFormsEvents::SUBMIT,  array($this, 'validCallable'));
        $app->boot();

        $this->formProcessRequest($app);

        $this->expectOutputString('Valid Dispatch');
    }

    public function testSubmitException()
    {
        $app = $this->getApp(false);
        $app['dispatcher']->addListener(BoltFormsEvents::SUBMIT,  array($this, 'invalidCallable'));
        $app->boot();

        $boltforms = new BoltForms($app);
        $boltforms->makeForm('testing_form');

        $this->formProcessRequest($app);

        $this->expectOutputString('Dispatch Exception');
    }

    public function testPostSubmitValid()
    {
        $app = $this->getApp(false);
        $app['dispatcher']->addListener(BoltFormsEvents::POST_SUBMIT,  array($this, 'validCallable'));
        $app->boot();

        $this->formProcessRequest($app);

        $this->expectOutputString('Valid Dispatch');
    }

    public function testPostSubmitException()
    {
        $app = $this->getApp(false);
        $app['dispatcher']->addListener(BoltFormsEvents::POST_SUBMIT,  array($this, 'invalidCallable'));
        $app->boot();

        $this->formProcessRequest($app);

        $this->expectOutputString('Dispatch Exception');
    }
}
