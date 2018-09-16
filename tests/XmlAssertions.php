<?php

namespace Brouzie\XmlIterator\Tests;

use PHPUnit\Framework\TestCase;

trait XmlAssertions
{
    public function assertSimpleXmlElementContainsArray(array $expected, \SimpleXMLElement $xml)
    {
        if (!$this instanceof TestCase) {
            throw new \LogicException('Expected to be used in PHPUnit test case.');
        }

        $this->assertArraySubset($expected, $this->xml2array($xml));
    }

    private function xml2array(\SimpleXMLElement $xml): array
    {
        $result = [];
        foreach ((array)$xml as $index => $node) {
            $result[$index] = is_object($node) ? $this->xml2array($node) : $node;
        }

        return $result;
    }
}
