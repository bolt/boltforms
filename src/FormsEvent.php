<?php

namespace Bolt\Extension\Bolt\Forms;

use Bolt;
use Symfony\Component\EventDispatcher\Event;

class FormsEvent extends Event
{
    /**
     * @var string
     */
    private $formname;

    /**
     * @var array
     */
    private $formconfig;

    /**
     * @var array
     */
    private $formdata;

    /**
     *
     * @param string $formname
     * @param array  $formconfig
     * @param array  $data
     */
    public function __construct($formname, $formconfig, $formdata)
    {
        $this->formname   = $formname;
        $this->formconfig = $formconfig;
        $this->formdata   = $formdata;
    }

    /**
     * Return the form name
     */
    public function getFormName()
    {
        return $this->formname;
    }

    /**
     * Return the form config
     */
    public function getFormConfig()
    {
        return $this->formconfig;
    }

    /**
     * Return the form data
     */
    public function getFormData()
    {
        return $this->formdata;
    }
}
