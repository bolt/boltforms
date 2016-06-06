<?php
namespace Bolt\Extension\Bolt\BoltForms\Exception;

class FileUploadException extends \Exception implements BoltFormsException
{
    /** @var string */
    private $systemMessage;

    /**
     * Constructor.
     *
     * @param string          $message
     * @param string          $systemMessage
     * @param \Exception|null $previous
     */
    public function __construct($message, $systemMessage, \Exception $previous = null)
    {
        $code = $previous ? $previous->getCode() : 1;
        parent::__construct($message, $code, $previous);
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
