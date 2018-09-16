<?php

namespace Brouzie\XmlIterator;

use Brouzie\XmlIterator\Exception\FileParseError;
use Brouzie\XmlIterator\Exception\LineParseError;
use Brouzie\XmlIterator\Internal\XpathFilterIterator;

final class XmlIterator
{
    private $iterator;

    public function __construct(XpathFilterIterator $iterator)
    {
        $this->iterator = $iterator;
    }

    public static function fromUri(
        string $uri,
        string $xpath,
        int $readerOptions = XpathFilterIterator::DEFAILT_OPTIONS
    ): self {
        return new self(new XpathFilterIterator($uri, $xpath, $readerOptions));
    }

    /**
     * @throws FileParseError
     */
    public function rewind(): void
    {
        $this->iterator->rewind();
    }

    public function hasNextItem(): bool
    {
        return $this->iterator->valid();
    }

    /**
     * @throws LineParseError
     */
    public function getNextItem(): Context
    {
        try {
            return $this->iterator->current();
        } finally {
            $this->iterator->next();
        }
    }
}
