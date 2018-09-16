<?php

namespace Brouzie\XmlIterator\Internal;

/**
 * @internal
 */
class NodeInfo
{
    /**
     * @see \XMLReader Constants
     *
     * @var int
     */
    private $type;

    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $counter = 1;

    public function __construct(int $type, string $name)
    {
        $this->type = $type;
        $this->name = $name;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCounter(): int
    {
        return $this->counter;
    }

    public function incrementCounter(): void
    {
        $this->counter++;
    }
}
