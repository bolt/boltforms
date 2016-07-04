<?php
namespace Bolt\Extension\Bolt\BoltForms\Exception;

class FileUploadException extends InternalProcessorException implements BoltFormsException
{
    /** @var string */
    private $systemMessage;

    /**
     * Constructor.
     *
     * @param string          $message
     * @param string          $systemMessage
     * @param int             $code
     * @param \Exception|null $previous
     * @param bool            $abort
     */
    public function __construct($message, $systemMessage, $code = 0, \Exception $previous = null, $abort)
    {
        $code = $previous ? $previous->getCode() : 1;
        parent::__construct($message, $code, $previous, $abort);
        $this->systemMessage = $systemMessage;
    }

    /**
     * @return string
     */
    public function getSystemMessage()
    {
        return $this->systemMessage;
    }
}
