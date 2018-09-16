<?php

namespace Brouzie\XmlIterator\Tests\Internal;

use Brouzie\XmlIterator\Context;
use Brouzie\XmlIterator\Exception\LineParseError;
use Brouzie\XmlIterator\Internal\XpathFilterIterator;
use Brouzie\XmlIterator\Tests\XmlAssertions;
use PHPUnit\Framework\TestCase;

class XpathFilterIteratorTest extends TestCase
{
    use XmlAssertions;

    //TODO: добавить тесты на неправильные кодировки и символы за пределами юникода
    const FIXTURE_NORMAL_PRICE = __DIR__.'/../Fixtures/small.xml';

    const FIXTURE_NORMAL_WIN1251 = __DIR__.'/../Fixtures/small-win-1251.xml';

    const FIXTURE_MULTIPLE_TAG_OCCURRENCES = __DIR__.'/../Fixtures/multiple-tag-ocurrencies.xml';

    const FIXTURE_SELF_CLOSED_TAG = __DIR__.'/../Fixtures/self-closed-tag.xml';

    const FIXTURE_UNESCAPED_ENTITY = __DIR__.'/../Fixtures/invalid-unescaped-entity.xml';

    const FIXTURE_NON_CLOSED_TAG = __DIR__.'/../Fixtures/invalid-non-closed-tag.xml';

    const FIXTURE_HTML_ENTITY_AND_CDATA = __DIR__.'/../Fixtures/html-entity-and-cdata.xml';

    const XPATH_CATEGORY = '/price/categories/category';

    const XPATH_ITEMS = '/price/items/item';

    /**
     * @dataProvider validFilesProvider
     */
    public function testValidFile(string $path)
    {
        $iterator = new XpathFilterIterator($path, self::XPATH_CATEGORY);

        $iterator->rewind();
        $this->assertTrue($iterator->valid());

        $categoryXml = <<<'XML'
<category>
    <id>1</id>
    <name>Смартфоны, ТВ, электроника</name>
</category>
XML;
        $itemContext = $iterator->current();
        $this->assertParseContextLine(7, $itemContext);
        $this->assertParseContextContent($categoryXml, $itemContext);

        $iterator->next();
        $this->assertTrue($iterator->valid());

        $categoryXml = <<<'XML'
<category>
    <id>2</id>
    <parentId>1</parentId>
    <name>Телефоны</name>
</category>
XML;
        $itemContext = $iterator->current();
        $this->assertParseContextLine(11, $itemContext);
        $this->assertParseContextContent($categoryXml, $itemContext);

        $iterator->next();
        $this->assertTrue($iterator->valid());

        $categoryXml = <<<'XML'
<category>
    <id>3</id>
    <parentId>1</parentId>
    <name>Фото</name>
</category>
XML;
        $itemContext = $iterator->current();
        $this->assertParseContextLine(16, $itemContext);
        $this->assertParseContextContent($categoryXml, $itemContext);

        $iterator->next();
        $this->assertTrue($iterator->valid());

        $categoryXml = <<<'XML'
<category>
    <id>4</id>
    <parentId>3</parentId>
    <name>Фотоаппараты</name>
</category>
XML;
        $itemContext = $iterator->current();
        $this->assertParseContextLine(21, $itemContext);
        $this->assertParseContextContent($categoryXml, $itemContext);

        $iterator->next();
        $this->assertTrue($iterator->valid());

        $categoryXml = <<<'XML'
<category>
    <id>5</id>
    <parentId>3</parentId>
    <name>Объективы</name>
</category>
XML;
        $itemContext = $iterator->current();
        $this->assertParseContextLine(26, $itemContext);
        $this->assertParseContextContent($categoryXml, $itemContext);

        $iterator->next();
        $this->assertFalse($iterator->valid());
    }

    public function validFilesProvider()
    {
        yield 'standard file' => [self::FIXTURE_NORMAL_PRICE];

        yield 'standard file encoded in Windows-1251 charset' => [self::FIXTURE_NORMAL_WIN1251];
    }

    public function testFileWithMultipleOccurrences()
    {
        $iterator = new XpathFilterIterator(self::FIXTURE_MULTIPLE_TAG_OCCURRENCES, self::XPATH_CATEGORY);

        $iterator->rewind();
        $this->assertTrue($iterator->valid());

        $categoryXml = <<<'XML'
<category>
    <id>1</id>
    <name>Смартфоны, ТВ, электроника</name>
</category>
XML;
        $itemContext = $iterator->current();
        $this->assertParseContextLine(8, $itemContext);
        $this->assertParseContextContent($categoryXml, $itemContext);

        $iterator->next();
        $this->assertTrue($iterator->valid());

        $category = [
            'id' => 2,
            'parentId' => 1,
            'name' => 'Телефоны',
        ];

        $itemContext = $iterator->current();
        $this->assertParseContextLine(12, $itemContext);
        $this->assertSimpleXmlElementContainsArray($category, $itemContext->getXml());

        $iterator->next();
        $this->assertFalse($iterator->valid());
    }

    public function testFileWithSelfClosingTag()
    {
        $iterator = new XpathFilterIterator(self::FIXTURE_SELF_CLOSED_TAG, self::XPATH_CATEGORY);

        $iterator->rewind();
        $this->assertTrue($iterator->valid());

        $categoryXml = <<<'XML'
<category>
    <id>1</id>
    <parentId />
    <name>Смартфоны, ТВ, электроника</name>
</category>
XML;
        $category = [
            'id' => 1,
            'parentId' => null,
            'name' => 'Смартфоны, ТВ, электроника',
        ];

        $itemContext = $iterator->current();
        $this->assertParseContextLine(7, $itemContext);
        $this->assertParseContextContent($categoryXml, $itemContext);
        $this->assertSimpleXmlElementContainsArray($category, $itemContext->getXml());

        $iterator->next();
        $this->assertTrue($iterator->valid());

        $categoryXml = <<<'XML'
<category>
    <id>3</id>
    <parentId>1</parentId>
    <name>Фото</name>
</category>
XML;
        $category = [
            'id' => 3,
            'parentId' => 1,
            'name' => 'Фото',
        ];

        $itemContext = $iterator->current();
        $this->assertParseContextLine(12, $itemContext);
        $this->assertParseContextContent($categoryXml, $itemContext);
        $this->assertSimpleXmlElementContainsArray($category, $itemContext->getXml());

        $iterator->next();
        $this->assertTrue($iterator->valid());

        $categoryXml = <<<'XML'
<category>
    <id>100</id>
    <parentId></parentId>
    <name>Корневая</name>
</category>
XML;
        $category = [
            'id' => 100,
            'parentId' => null,
            'name' => 'Корневая',
        ];

        $itemContext = $iterator->current();
        $this->assertParseContextLine(17, $itemContext);
        $this->assertParseContextContent($categoryXml, $itemContext);
        $this->assertSimpleXmlElementContainsArray($category, $itemContext->getXml());

        $iterator->next();
        $this->assertFalse($iterator->valid());
    }

    public function testFileWithUnescapedEntity()
    {
        $iterator = new XpathFilterIterator(self::FIXTURE_UNESCAPED_ENTITY, self::XPATH_CATEGORY);
        $iterator->rewind();

        $this->assertTrue($iterator->valid());
        $node = $iterator->current()->getXml();
        $this->assertEquals('1', (string)$node->id);
        $this->assertEquals('Смартфоны, ТВ, электроника', (string)$node->name);
        $iterator->next();

        try {
            $iterator->current();

            $this->fail('Expected exception');
        } catch (LineParseError $error) {
            $this->assertSame(14, $error->getContext()->getLine());
            $this->assertSame(26, $error->getContext()->getColumn());
        }

        $iterator->next();

        $this->assertTrue($iterator->valid());
        $node = $iterator->current()->getXml();
        $this->assertEquals('3', (string)$node->id);
        $this->assertEquals('1', (string)$node->parentId);
        $this->assertEquals('Фото', (string)$node->name);
        $iterator->next();

        $this->assertFalse($iterator->valid());
    }

    /**
     * @expectedException \Brouzie\XmlIterator\Exception\FileParseError
     * @expectedExceptionMessage Opening and ending tag mismatch: name line 14 and category
     */
    public function testFileWithNonClosedTag()
    {
        $iterator = new XpathFilterIterator(self::FIXTURE_NON_CLOSED_TAG, self::XPATH_CATEGORY);
        $iterator->rewind();
    }

    public function testFileWithHtmlEntityAndCdata()
    {
        $iterator = new XpathFilterIterator(self::FIXTURE_HTML_ENTITY_AND_CDATA, self::XPATH_ITEMS);
        $iterator->rewind();

        $this->assertTrue($iterator->valid());
        $itemContext = $iterator->current();

        $this->assertParseContextLine(7, $itemContext);

        $expected = [
            'id' => 1,
            'description' => 'Simple description &',
        ];
        $this->assertSimpleXmlElementContainsArray($expected, $itemContext->getXml());
        $iterator->next();

        $longDescription = <<<'TEXT'
Within this Character Data block I can
use double dashes as much as I want (along with <, &, ', and ")
*and* %MyParamEntity; will be expanded to the text
"Has been expanded" ... however, I can't use
the CEND sequence. If I need to use CEND I must escape one of the
brackets or the greater-than sign using concatenated CDATA sections.
TEXT;
        $expected = [
            'id' => 2,
            'description' => $longDescription,
        ];

        $this->assertTrue($iterator->valid());
        $itemContext = $iterator->current();
        $this->assertSimpleXmlElementContainsArray($expected, $itemContext->getXml());
        $iterator->next();

        $this->assertFalse($iterator->valid());
    }

    public function assertParseContextLine(int $expectedLine, Context $parseContext)
    {
        $this->assertSame($expectedLine, $parseContext->getLine());
    }

    public function assertParseContextContent(string $expectedContent, Context $parseContext)
    {
        $xml = $parseContext->getXml();
        $this->assertInstanceOf(\SimpleXMLElement::class, $xml);
        $this->assertXmlStringEqualsXmlString($expectedContent, $parseContext->getContext());
        $this->assertXmlStringEqualsXmlString($expectedContent, $xml->asXml());
    }
}
