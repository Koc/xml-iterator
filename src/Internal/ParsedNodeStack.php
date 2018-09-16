<?php

namespace Brouzie\XmlIterator\Internal;

/**
 * @internal
 */
class ParsedNodeStack
{
    /**
     * @var NodeInfo[]
     */
    private $stack = [];

    /**
     * @var NodeInfo
     */
    private $currentNode;

    public function push(\XMLReader $reader): void
    {
        $this->currentNode = new NodeInfo($reader->nodeType, $reader->name);
        $this->stack[] = $this->currentNode;

        //TODO: increment counter if current element is same as prev
//        $this->currentNode->incrementCounter();
    }

    public function pop(): NodeInfo
    {
        $node = array_pop($this->stack);
        $this->currentNode = end($this->stack);

        return $node;
    }

    public function validateXpath(string $xpath): bool
    {
        //TODO: provide support of xpath expressions like: [position() <= 2]
        return $this->getXpath() === $xpath;
    }

    private function getXpath(): string
    {
        $xpath = '';

        foreach ($this->stack as $nodeInfo) {
            switch ($nodeInfo->getType()) {
                case \XMLReader::ELEMENT:
                    $xpath .= '/'.$nodeInfo->getName();
                    break;

                case \XMLReader::TEXT:
                case \XMLReader::CDATA:
                    $xpath .= '/text()';
                    break;

                case \XMLReader::COMMENT:
                    $xpath .= '/comment()';
                    break;

                case \XMLReader::ATTRIBUTE:
                    $xpath .= "[@{$nodeInfo->getName()}]";
                    break;
            }
        }

        return $xpath;
    }
}
