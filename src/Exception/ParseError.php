<?php

namespace Brouzie\XmlIterator\Exception;

abstract class ParseError extends \RuntimeException
{
    private $context;

    /**
     * @return static
     */
    public static function fromLibXMLError(\LibXMLError $error): ParseError
    {
        return new static($error->message, new ErrorContext('', $error->line, $error->column));
    }

    public function __construct(string $message, ?ErrorContext $context, \Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);

        $this->context = $context;
    }

    public function getContext(): ?ErrorContext
    {
        return $this->context;
    }
}
