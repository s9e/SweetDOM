## Overview

s9e\\SweetDOM is a library that extends [PHP's DOM extension](https://www.php.net/manual/en/book.dom.php) to make DOM manipulation easier, with a particular emphasis on XSLT 1.0 templates. It adds syntactic sugar for the most common DOM operations, [improves compatibility](#backward-and-forward-compatibility-with-older-and-future-versions-of-php) across PHP versions, and implements polyfills for some of the newer methods.

[![Code Coverage](https://scrutinizer-ci.com/g/s9e/SweetDOM/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/s9e/SweetDOM/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/s9e/SweetDOM/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/s9e/SweetDOM/?branch=master)


## Installation

```bash
composer require s9e/sweetdom
```


## API

#### s9e\SweetDOM\Document

The `s9e\SweetDOM\Document` class extends `DOMDocument` and provides quick access to DOMXPath's `evaluate` and `query` methods. The `firstOf` method evaluates the XPath query and returns the first node of the list, or `null` if the list is empty.

```php
mixed       evaluate(string $expression, ?DOMNode $contextNode = null, bool $registerNodeNS = true)
?DOMNode    firstOf(string $expression, ?DOMNode $contextNode = null, bool $registerNodeNS = true)
DOMNodeList query(string $expression, ?DOMNode $contextNode = null, bool $registerNodeNS = true)
```

The `s9e\SweetDOM\Document` class has a `$nodeCreator` property that provides a set of methods to create elements with an emphasis on XSL elements commonly used in templates. See `s9e\SweetDOM\NodeCreator` for the full content.

```php
Comment createComment(string $data)
Element createElement(string $nodeName, string $textContent = '')
Element createElementNS(?string $namespace, string $nodeName, string $textContent = '')
Element createXslApplyTemplates(string $select = null, string $mode = null)
Element createXslAttribute(string $name, string $textContent = '', string $namespace = null)
Element createXslChoose()
Element createXslComment(string $textContent = '')
Element createXslCopyOf(string $select)
Element createXslElement(string $name, string $namespace = null, string $useAttributeSets = null)
Element createXslIf(string $test, string $textContent = '')
Element createXslOtherwise(string $textContent = '')
Element createXslText(string $textContent = '', string $disableOutputEscaping = null)
Element createXslValueOf(string $select, string $disableOutputEscaping = null)
Element createXslVariable(string $name, string $select = null)
Element createXslWhen(string $test, string $textContent = '')
```


#### s9e\SweetDOM\Element

The `s9e\SweetDOM\Element` class extends `DOMElement` and provides a set of magic methods to simultaneously create a node and insert it relative to the element. For each method from the `s9e\SweetDOM\NodeCreator` class, exist five corresponding methods on the `s9e\SweetDOM\Element`.

For instance, the `createXslText` method from `s9e\SweetDOM\NodeCreator` is declined into the `afterXslText`, `appendXslText`, `beforeXslText`, `prependXslText`, and `replaceWithXslText` methods in `s9e\SweetDOM\Element`. Each method creates a node, performs the DOM action, then returns the node. The following example illustrates where each `xsl:text` element is inserted relative to the `span` element from which they are created, then replaces the `br` element.

```php
$xsl = '<xsl:template xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <p><span><br/></span></p>
</xsl:template>';

$dom                     = new s9e\SweetDOM\Document;
$dom->formatOutput       = true;
$dom->preserveWhiteSpace = false;
$dom->loadXML($xsl);

$span    = $dom->firstOf('//span');
$methods = ['afterXslText', 'appendXslText', 'beforeXslText', 'prependXslText'];
foreach ($methods as $methodName)
{
	$span->$methodName($methodName);
}
$dom->firstOf('//br')->replaceWithXslText('replaceWithXslText');
echo $dom->saveXML($dom->documentElement);
```
```xsl
<xsl:template xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <p>
    <xsl:text>beforeXslText</xsl:text>
    <span>
      <xsl:text>prependXslText</xsl:text>
      <xsl:text>replaceWithXslText</xsl:text>
      <xsl:text>appendXslText</xsl:text>
    </span>
    <xsl:text>afterXslText</xsl:text>
  </p>
</xsl:template>
```

XPath methods are also accessible at the element level and use the element itself as context node:

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

Elements can be easily created and added relative to the context node via the following API:
```php
Element afterElement(string $nodeName, string $textContent = '')
Element appendElement(string $nodeName, string $textContent = '')
Element beforeElement(string $nodeName, string $textContent = '')
Element prependElement(string $nodeName, string $textContent = '')
```

```php
$dom                     = new s9e\SweetDOM\Document;
$dom->formatOutput       = true;
$dom->preserveWhiteSpace = false;
$dom->loadXML('<p><span><br/></span></p>');

$span    = $dom->firstOf('//span');
$methods = ['afterElement', 'appendElement', 'beforeElement', 'prependElement'];
foreach ($methods as $methodName)
{
	$span->$methodName('i', $methodName);
}
echo $dom->saveXML($dom->documentElement);
```
```xml
<p>
  <i>beforeElement</i>
  <span>
    <i>prependElement</i>
    <br/>
    <i>appendElement</i>
  </span>
  <i>afterElement</i>
</p>
```

Document fragments can be used to batch operations, or insert XML in a DOM.

```php
$dom = new s9e\SweetDOM\Document;
$dom->loadXML('<x/>');

$x = $dom->firstOf('//x');
$x->appendDocumentFragment(
	// The callback will be executed before the fragment is appended
	fn($fragment) => $fragment->appendXML('<y/><z/>')
);

echo $dom->saveXML($x);
```
```xml
<x><y/><z/></x>
```


#### Other extended nodes

The following DOM nodes are automatically extended and augmented with XPath methods as well as whichever magic methods are supported by the node type, usually via the [`DOMChildNode`](https://www.php.net/manual/class.domchildnode.php) and [`DOMParentNode`](https://www.php.net/manual/class.domparentnode.php) interfaces.

 - `s9e\SweetDOM\Attr` extends `DOMAttr`
 - `s9e\SweetDOM\CdataSection` extends `DOMCdataSection`
 - `s9e\SweetDOM\Comment` extends `DOMComment`
 - `s9e\SweetDOM\DocumentFragment` extends `DOMDocumentFragment`
 - `s9e\SweetDOM\Element` extends `DOMElement`
 - `s9e\SweetDOM\Text` extends `DOMText`


## Backward and forward compatibility with older and future versions of PHP

In order to improve compatibility with older versions of PHP as well as future versions of PHP, this library uses a different set of classes to implement node types depending on the PHP version. The base classes are those listed above, in the `s9e\SweetDOM` namespace, and only those classes should be used when checking for class types.


#### Backward compatibility with older versions of PHP

Polyfills for the following methods are provided for PHP < 8.3:

 - `Node::isEqualNode`
 - `ParentNode::insertAdjacentElement`
 - `ParentNode::insertAdjacentText`
 - `ParentNode::replaceChildren`

On PHP older than 8.1.23, and on PHP versions from 8.2.0 to 8.2.9, the following methods are emulated:

 - `ChildNode::after`
 - `ChildNode::before`
 - `ChildNode::replaceWith`
 - `ParentNode::append`
 - `ParentNode::prepend`


#### Forward compatibility with future versions of PHP

The following methods have been modified to match PHP 8.3's behaviour with regards to disconnected nodes (nodes with no parent) and align with the DOM specification.

 - `ChildNode::after`
 - `ChildNode::before`
 - `ChildNode::replaceWith`
