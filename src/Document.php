<?php declare(strict_types=1);

/**
* @package   s9e\SweetDOM
* @copyright Copyright (c) 2019-2020 The s9e authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\SweetDOM;

use DOMDocument;
use DOMNode;
use DOMNodeList;
use DOMXPath;

class Document extends DOMDocument
{
	/**
	* @link https://www.php.net/manual/domdocument.construct.php
	*
	* @param  string $version  Version number of the document
	* @param  string $encoding Encoding of the document
	* @return void
	*/
	public function __construct(string $version = '1.0', string $encoding = 'utf-8')
	{
		parent::__construct($version, $encoding);

		$this->registerNodeClass('DOMElement', Element::class);
	}

	/**
	* Create and return an xsl:apply-templates element
	*
	* @param  string  $select XPath expression for the "select" attribute
	* @return Element
	*/
	public function createXslApplyTemplates(string $select = null): Element
	{
		$element = $this->createElementNS('http://www.w3.org/1999/XSL/Transform', 'xsl:apply-templates');
		if (isset($select))
		{
			$element->setAttribute('select', $select);
		}

		return $element;
	}

	/**
	* Create and return an xsl:choose element
	*
	* @return Element
	*/
	public function createXslChoose(): Element
	{
		return $this->createElementNS('http://www.w3.org/1999/XSL/Transform', 'xsl:choose');
	}

	/**
	* Create and return an xsl:copy-of element
	*
	* @param  string  $select XPath expression for the "select" attribute
	* @return Element
	*/
	public function createXslCopyOf(string $select): Element
	{
		$element = $this->createElementNS('http://www.w3.org/1999/XSL/Transform', 'xsl:copy-of');
		$element->setAttribute('select', $select);

		return $element;
	}

	/**
	* Create and return an xsl:if element
	*
	* @param  string  $test XPath expression for the "test" attribute
	* @return Element
	*/
	public function createXslIf(string $test): Element
	{
		$element = $this->createElementNS('http://www.w3.org/1999/XSL/Transform', 'xsl:if');
		$element->setAttribute('test', $test);

		return $element;
	}

	/**
	* Create and return an xsl:otherwise element
	*
	* @return Element
	*/
	public function createXslOtherwise(): Element
	{
		return $this->createElementNS('http://www.w3.org/1999/XSL/Transform', 'xsl:otherwise');
	}

	/**
	* Create and return an xsl:param element
	*
	* @param  string  $name   Name of the parameter
	* @param  string  $select XPath expression
	* @return Element
	*/
	public function createXslParam(string $name, string $select = null): Element
	{
		$element = $this->createElementNS('http://www.w3.org/1999/XSL/Transform', 'xsl:param');
		$element->setAttribute('name', $name);
		if (isset($select))
		{
			$element->setAttribute('select', $select);
		}

		return $element;
	}

	/**
	* Create and return an xsl:text element
	*
	* @param  string  $text Text content for the element
	* @return Element
	*/
	public function createXslText(string $text = ''): Element
	{
		return $this->createElementNS('http://www.w3.org/1999/XSL/Transform', 'xsl:text', $text);
	}

	/**
	* Create and return an xsl:value-of element
	*
	* @param  string  $select XPath expression for the "select" attribute
	* @return Element
	*/
	public function createXslValueOf(string $select): Element
	{
		$element = $this->createElementNS('http://www.w3.org/1999/XSL/Transform', 'xsl:value-of');
		$element->setAttribute('select', $select);

		return $element;
	}

	/**
	* Create and return an xsl:variable element
	*
	* @param  string  $name   Name of the variable
	* @param  string  $select XPath expression
	* @return Element
	*/
	public function createXslVariable(string $name, string $select = null): Element
	{
		$element = $this->createElementNS('http://www.w3.org/1999/XSL/Transform', 'xsl:variable');
		$element->setAttribute('name', $name);
		if (isset($select))
		{
			$element->setAttribute('select', $select);
		}

		return $element;
	}

	/**
	* Create and return an xsl:when element
	*
	* @param  string  $test XPath expression for the "test" attribute
	* @return Element
	*/
	public function createXslWhen(string $test): Element
	{
		$element = $this->createElementNS('http://www.w3.org/1999/XSL/Transform', 'xsl:when');
		$element->setAttribute('test', $test);

		return $element;
	}

	/**
	* Evaluate and return the result of a given XPath expression
	*
	* @param  string  $expr           XPath expression
	* @param  DOMNode $node           Context node
	* @param  bool    $registerNodeNS Whether to register the node's namespace
	* @return mixed
	*/
	public function evaluate(string $expr, DOMNode $node = null, bool $registerNodeNS = true)
	{
		return call_user_func_array([$this->xpath(), 'evaluate'], func_get_args());
	}

	/**
	* Evaluate and return the first element of a given XPath query
	*
	* @param  string      $expr           XPath expression
	* @param  DOMNode     $node           Context node
	* @param  bool        $registerNodeNS Whether to register the node's namespace
	* @return DOMNode|null
	*/
	public function firstOf(string $expr, DOMNode $node = null, bool $registerNodeNS = true): ?DOMNode
	{
		return call_user_func_array([$this, 'query'], func_get_args())->item(0);
	}

	/**
	* Evaluate and return the result of a given XPath query
	*
	* @param  string      $expr           XPath expression
	* @param  DOMNode     $node           Context node
	* @param  bool        $registerNodeNS Whether to register the node's namespace
	* @return DOMNodeList
	*/
	public function query(string $expr, DOMNode $node = null, bool $registerNodeNS = true): DOMNodeList
	{
		return call_user_func_array([$this->xpath(), 'query'], func_get_args());
	}

	protected function xpath(): DOMXPath
	{
		$xpath = new DOMXPath($this);
		$xpath->registerNamespace('xsl', 'http://www.w3.org/1999/XSL/Transform');

		return $xpath;
	}
}