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

        // Paranoia
        unset ($this->formdata['_token']);
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
     * Set the form config
     */
    public function setFormConfig($formconfig)
    {
        $this->formconfig = $formconfig;
    }

    /**
     * Return the form data
     */
    public function getFormData()
    {
        return $this->formdata;
    }

    /**
     * Set the form data
     */
    public function setFormData($formdata)
    {
        $this->formdata = $formdata;
    }
}
