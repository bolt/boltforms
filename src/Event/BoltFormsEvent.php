<?php

namespace Bolt\Extension\Bolt\BoltForms\Event;

use Bolt;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Form\FormInterface;

class BoltFormsEvent extends Event
{
    /**
     * @var Symfony\Component\Form\FormInterface
     */
    private $form;

    public function __construct(FormInterface $form)
    {
        $this->form = $form;
    }

    public function getForm()
    {
        return $this->form;
    }
}