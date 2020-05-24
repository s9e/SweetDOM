## Overview

s9e\\SweetDOM is a library that extends [PHP's DOM extension](https://www.php.net/manual/en/book.dom.php) with a set of methods designed to simplify and facilitate the manipulation of XSLT 1.0 templates.

[![Build Status](https://travis-ci.org/s9e/SweetDOM.svg?branch=master)](https://travis-ci.org/s9e/SweetDOM)
[![Code Coverage](https://scrutinizer-ci.com/g/s9e/SweetDOM/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/s9e/SweetDOM/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/s9e/SweetDOM/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/s9e/SweetDOM/?branch=master)


## Installation

```bash
composer require s9e/sweetdom
```


## API

The `s9e\SweetDOM\Document` class extends `DOMDocument` to provide a set of methods to create XSL elements:
```php
Element createXslApplyTemplates(string $select = null)
Element createXslAttribute(string $name, string $text = '')
Element createXslChoose()
Element createXslComment(string $text = '')
Element createXslCopyOf(string $select)
Element createXslIf(string $test, string $text = '')
Element createXslOtherwise(string $text = '')
Element createXslText(string $text = '')
Element createXslValueOf(string $select)
Element createXslVariable(string $name, string $select = null)
Element createXslWhen(string $test, string $text = '')
```

It also provides quick access to DOMXPath's `evaluate` and `query` methods. The `firstOf` method evaluates the XPath query and returns the first node of the list, or `null` if the list is empty.
```php
mixed       evaluate(string $expr, DOMNode $node = null, bool $registerNodeNS = true)
?DOMNode    firstOf(string $expr, DOMNode $node = null, bool $registerNodeNS = true)
DOMNodeList query(string $expr, DOMNode $node = null, bool $registerNodeNS = true)
```

The `s9e\SweetDOM\Element` class extends `DOMElement` and provides a matching set of methods to simultaneously create an XSL element and insert it relative to the element. For each method from the `s9e\SweetDOM\Document` class that creates an XSL element, exist 4 corresponding methods.

For instance, the `createXslText` method from `s9e\SweetDOM\Document` is declined into the `appendXslText`, `appendXslTextSibling`, `prependXslText`, `prependXslTextSibling` methods in `s9e\SweetDOM\Element`. The following example illustrates where each `xsl:text` element is inserted relative to the `span` element from which they are created.

```php
$xsl = '<xsl:template xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <p><span><br/></span></p>
</xsl:template>';

$dom = new s9e\SweetDOM\Document;
$dom->formatOutput = true;
$dom->preserveWhiteSpace = false;
$dom->loadXML($xsl);

$span    = $dom->firstOf('//span');
$methods = ['appendXslText', 'appendXslTextSibling', 'prependXslText', 'prependXslTextSibling'];
foreach ($methods as $methodName)
{
	$span->$methodName($methodName);
}
echo $dom->saveXML($dom->documentElement);
```
```xsl
<xsl:template xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <p>
    <xsl:text>prependXslTextSibling</xsl:text>
    <span>
      <xsl:text>prependXslText</xsl:text>
      <br/>
      <xsl:text>appendXslText</xsl:text>
    </span>
    <xsl:text>appendXslTextSibling</xsl:text>
  </p>
</xsl:template>
```

The XPath methods are also accessible at the element level and use the element as context node:
```php
$dom = new s9e\SweetDOM\Document;
$dom->loadXML('<x id="1"><x id="2"/></x>');

var_dump($dom->firstOf('//x')->getAttribute('id'));
var_dump($dom->firstOf('//x')->firstOf('x')->getAttribute('id'));
```
```
string(1) "1"
string(1) "2"
```

In addition, it provides a set of methods modeled after the modern DOM's [insertAdjacentElement](https://developer.mozilla.org/en-US/docs/Web/API/Element/insertAdjacentElement) API.

```php
self insertAdjacentElement(string $where, self $element)
void insertAdjacentText(string $where, string $text)
void insertAdjacentXML(string $where, string $xml)
```
