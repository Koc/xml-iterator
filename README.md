# XmlIterator

Fast and memory efficient XML iterator based on XmlReader.

[![Build Status](https://travis-ci.org/Koc/xml-iterator.svg?branch=master)](https://travis-ci.org/Koc/xml-iterator)

## Installation

Run

```
$ composer require brouzie/xml-iterator
```

## Usage

```php
<?php

require_once 'vendor/autoload.php';

$iterator = \Brouzie\XmlIterator\XmlIterator::fromUri('path/to/file.xml', '/my/xpath');

try {
    $iterator->rewind();
} catch (\Brouzie\XmlIterator\Exception\FileParseError $error) {
    var_dump($error->getMessage(), $error->getContext()->getLine(), $error->getContext()->getColumn());
}

while ($iterator->hasNextItem()) {
    try {
        $item = $iterator->getNextItem();
        
        var_dump(
            $item->getXml(), // \SimpleXmlElement
            $item->getContext(),
            $item->getLine(),
            $item->getColumn()
        );
    } catch (\Brouzie\XmlIterator\Exception\LineParseError $error) {
        if ($error->getContext()) {
            var_dump($error->getMessage(), $error->getContext()->getLine(), $error->getContext()->getColumn());
        } else {
            var_dump($error->getMessage());
        }
    }    
}

```
