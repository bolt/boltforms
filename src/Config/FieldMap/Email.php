<?php

namespace Bolt\Extension\Bolt\BoltForms\Config\FieldMap;

use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Field map for email fields.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Email extends ParameterBag
{
    /** @var string */
    protected $config = 'config';
    /** @var string */
    protected $data = 'data';
    /** @var string */
    protected $fields = 'fields';
    /** @var string */
    protected $subject = 'subject';

    /**
     * @return string
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param string $config
     *
     * @return Email
     */
    public function setConfig($config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string $data
     *
     * @return Email
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return string
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param string $fields
     *
     * @return Email
     */
    public function setFields($fields)
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     *
     * @return Email
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }
}
