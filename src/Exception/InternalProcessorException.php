<?php
namespace Bolt\Extension\Bolt\BoltForms\Exception;

class InternalProcessorException extends \Exception implements BoltFormsException
{
    /** @var bool */
    private $abort;

    /**
     * Constructor.
     *
     * @param string          $message
     * @param int             $code
     * @param \Exception|null $previous
     * @param bool            $abort
     */
    public function __construct($message, $code = 0, \Exception $previous = null, $abort) // = false)
    {
        $this->abort = $abort;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return boolean
     */
    public function isAbort()
    {
        return $this->abort;
    }

    /**
     * @param boolean $abort
     */
    public function setAbort($abort)
    {
        $this->abort = $abort;
    }
}
