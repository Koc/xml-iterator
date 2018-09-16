<?php

namespace Brouzie\XmlIterator\Tests\Internal;

use Brouzie\XmlIterator\Internal\XpathFilterIterator;
use Brouzie\XmlIterator\Tests\XmlAssertions;
use PHPUnit\Framework\TestCase;

/**
 * @group performance
 */
class XmlIteratorPerformanceTest extends TestCase
{
    use XmlAssertions;

    const MEMORY_10_MB = 10 * 1024 * 1024;

    public function testBigFile()
    {
        $fh = tmpfile();
        $filepath = stream_get_meta_data($fh)['uri'];

        $iterations = 50000;
        $this->createLargeXmlFeed($fh, $iterations);

        $initialMemoryUsed = memory_get_usage();

        $iterator = new XpathFilterIterator($filepath, XpathFilterIteratorTest::XPATH_ITEMS);
        $iterator->rewind();

        $this->assertLessThan(self::MEMORY_10_MB, memory_get_usage() - $initialMemoryUsed);

        $i = 0;
        while ($iterator->valid()) {
            $itemContext = $iterator->current();
            $xml = $itemContext->getXml();

            $this->assertInstanceOf(\SimpleXMLElement::class, $xml);
            $this->assertSimpleXmlElementContainsArray(['id' => $i], $xml);

            $this->assertLessThan(self::MEMORY_10_MB, memory_get_usage() - $initialMemoryUsed);

            $iterator->next();
            $i++;
        }

        $this->assertSame($iterations, $i);
    }

    /**
     * @param resource $fh
     */
    private function createLargeXmlFeed($fh, int $itemsCount)
    {
        $feed = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<price>
    <date>2018-05-30 07:17</date>
    <firmName>small</firmName>
    <firmId>000</firmId>
    <items>
XML;

        fwrite($fh, $feed);

        $description = str_repeat(
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. ',
            100
        );

        for ($i = 0; $i < $itemsCount; $i++) {
            $feed = <<<XML
        <item>
            <id>{$i}</id>
            <description>{$description}</description>
        </item>
XML;

            fwrite($fh, $feed);
        }


        $feed = <<<'XML'
    </items>
</price>
XML;

        fwrite($fh, $feed);
    }
}
