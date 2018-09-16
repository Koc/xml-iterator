<?php

namespace Brouzie\XmlIterator;

final class Context
{
    private $xml;

    private $context;

    private $line;

    private $column;

    public function __construct(\SimpleXMLElement $xml, string $context, int $line, int $column)
    {
        $this->line = $line;
        $this->context = $context;
        $this->xml = $xml;
        $this->column = $column;
    }

    public function getXml(): \SimpleXMLElement
    {
        return $this->xml;
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
