<?php

namespace Bolt\Extension\Bolt\BoltForms\Subscriber;

use Bolt;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BoltFormsSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA  => 'preSetData',
            FormEvents::POST_SET_DATA => 'postSetData',
            FormEvents::PRE_SUBMIT    => 'preSubmit',
            FormEvents::SUBMIT        => 'submit',
            FormEvents::POST_SUBMIT   => 'postSubmit'
        );
    }

    public function preSetData(FormEvent $event)
    {
//         $data = $event->getData();
//         $form = $event->getForm();
    }

    public function postSetData(FormEvent $event)
    {
//         $data = $event->getData();
//         $form = $event->getForm();
    }

    /**
     * Form pre submission event
     *
     * To modify data on the fly, this is the point to do it
     * using:
     *  $data = $event->getData();
     *  $event->setData($data);
     *
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event)
    {
//         $data = $event->getData();
//         $form = $event->getForm();

    }

    public function submit(FormEvent $event)
    {
//         $data = $event->getData();
//         $form = $event->getForm();
    }

    public function postSubmit(FormEvent $event)
    {
//         $data = $event->getData();
//         $form = $event->getForm();
    }
}
