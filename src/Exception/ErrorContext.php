<?php

namespace Brouzie\XmlIterator\Exception;

final class ErrorContext
{
    private $context;

    private $line;

    private $column;

    public function __construct(string $context, int $line, int $column = 0)
    {
        $this->line = $line;
        $this->context = $context;
        $this->column = $column;
    }

    public function getContext(): string
    {
        return $this->context;
    }

    public function getLine(): int
    {
        return $this->line;
    }

    public function getColumn(): int
    {
        return $this->column;
    }
}
