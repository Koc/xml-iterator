<?php

namespace Brouzie\XmlIterator\Tests\Internal;

use Brouzie\XmlIterator\Exception\FileParseError;
use Brouzie\XmlIterator\Exception\LineParseError;
use Brouzie\XmlIterator\Internal\XpathFilterIterator;
use Brouzie\XmlIterator\XmlIterator;
use PHPUnit\Framework\TestCase;

class XmlIteratorTest extends TestCase
{
    const FIXTURE_UNEXPECTED_EOF = __DIR__.'/../Fixtures/unexpected_eof.yml.xml';

    const XPATH_YML_ITEMS = '/yml_catalog/shop/offers/offer';

    public function testUnexpectedEof()
    {
        $this->expectException(FileParseError::class);
        $this->expectExceptionMessage('Recursion error in line');

        $iterator = XmlIterator::fromUri(
            self::FIXTURE_UNEXPECTED_EOF,
            self::XPATH_YML_ITEMS,
            XpathFilterIterator::DEFAULT_OPTIONS & ~\XMLReader::VALIDATE
        );
        $iterator->rewind();

        $lineErrorCount = 0;
        while ($iterator->hasNextItem()) {
            try {
                $item = $iterator->getNextItem();
            } catch (LineParseError $error) {
                $this->assertLessThanOrEqual(XpathFilterIterator::LINE_ERROR_LIMIT, $lineErrorCount++);
            }
        }
    }
}