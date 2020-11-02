<?php

namespace Brouzie\XmlIterator\Internal;

use Brouzie\XmlIterator\Context;
use Brouzie\XmlIterator\Exception\FileParseError;
use Brouzie\XmlIterator\Exception\LineParseError;

/**
 * @internal
 */
class XpathFilterIterator implements \Iterator
{
    const DEFAULT_OPTIONS = \XMLReader::VALIDATE | \XMLReader::SUBST_ENTITIES | LIBXML_NOCDATA | LIBXML_BIGLINES;

    private $uri;

    private $xpath;

    private $targetElement;

    private $readerOptions;

    private $reader;

    private $doc;

    /**
     * @var ParsedNodeStack
     */
    private $stack;

    private $position;

    public function __construct(string $uri, string $xpath, int $readerOptions = self::DEFAULT_OPTIONS)
    {
        $this->uri = $uri;
        $this->xpath = $xpath;
        $this->targetElement = substr($this->xpath, strrpos($this->xpath, '/') + 1);
        $this->readerOptions = $readerOptions;

        $this->reader = new \XMLReader();
        $this->doc = new \DOMDocument();
    }

    /**
     * @throws LineParseError If XML is invalid.
     */
    public function current(): Context
    {
        $internalErrors = libxml_use_internal_errors(true);

        // Workaround for preventing error bubbling. We are handling errors below
        $node = @$this->reader->expand();

        if (false === $node) {
            if ($error = $this->getXmlError($internalErrors)) {
                //TODO: read piece of file and populate context
                throw LineParseError::fromLibXMLError($error);
            }

            // If we can't handle graceful
            throw new LineParseError('Unknown parse error.', null);
        }

        $simpleXMLElement = simplexml_import_dom($this->doc->importNode($node, true));

        //TODO: handle error from dom document

        libxml_use_internal_errors($internalErrors);
        libxml_clear_errors();

        //NB! there is an error in libxml, method getLineNo does not return values greater than 65535
        // https://bugs.php.net/bug.php?id=54138
        // this not working even with LIBXML_BIGLINES flag
        return new Context($simpleXMLElement, $simpleXMLElement->asXML(), $node->getLineNo(), 0);
    }

    public function next()
    {
        if ($this->reader->next($this->targetElement)) {
            ++$this->position;
        }
    }

    public function key()
    {
        return $this->position;
    }

    public function valid()
    {
        return \XMLReader::ELEMENT === $this->reader->nodeType
            && $this->reader->name === $this->targetElement
            && $this->stack->validateXpath($this->xpath);
    }

    public function rewind()
    {
        $this->position = 0;
        $this->stack = new ParsedNodeStack();

        if (!@$this->reader->open($this->uri, 'UTF-8', $this->readerOptions)) {
            throw new FileParseError(
                sprintf('File "%s" cannot bet opened.', $this->uri),
                null
            );
        }

        $internalErrors = libxml_use_internal_errors(true);

        // move to the first element
        while (@$this->reader->read()) {
            if (
                \XMLReader::ELEMENT === $this->reader->nodeType
                and !$this->reader->isEmptyElement || $this->reader->hasAttributes
            ) {
                $this->stack->push($this->reader);
            }

            if (\XMLReader::END_ELEMENT === $this->reader->nodeType) {
                $this->stack->pop();
            }

            if ($this->valid()) {
                break;
            }
        }

        if ($error = $this->getXmlError($internalErrors)) {
            //TODO: read piece of file and populate context
            throw FileParseError::fromLibXMLError($error);
        }
    }

    /**
     * @return \LibXMLError|null
     */
    private function getXmlError(bool $internalErrors)
    {
        $errors = libxml_get_errors();

        libxml_use_internal_errors($internalErrors);

        if (!$errors) {
            return null;
        }

        libxml_clear_errors();

        /** @var \LibXMLError $error */
        $error = reset($errors);

        return $error ?: null;
    }
}
