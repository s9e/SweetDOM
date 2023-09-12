<?php declare(strict_types=1);

/**
* @package   s9e\SweetDOM
* @copyright Copyright (c) The s9e authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\SweetDOM;

use DOMDocument;
use DOMNode;
use DOMNodeList;
use DOMXPath;
use function func_get_args;

abstract class AbstractDocument extends DOMDocument
{
	/**
	* @link https://www.php.net/manual/domdocument.construct.php
	*
	* @param string $version  Version number of the document
	* @param string $encoding Encoding of the document
	*/
	public function __construct(string $version = '1.0', string $encoding = 'utf-8')
	{
		parent::__construct($version, $encoding);

		$this->registerNodeClass('DOMElement', Element::class);
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
		return $this->xpath('evaluate', func_get_args());
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
		return $this->xpath('query', func_get_args())->item(0);
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
		return $this->xpath('query', func_get_args());
	}

	/**
	* Execute a DOMXPath method and return the result
	*
	* @param  string $methodName
	* @param  array  $args
	* @return mixed
	*/
	protected function xpath(string $methodName, array $args)
	{
		$xpath = new DOMXPath($this);
		$xpath->registerNamespace('xsl', 'http://www.w3.org/1999/XSL/Transform');
		$xpath->registerNodeNamespaces = true;

		return $xpath->$methodName(...$args);
	}
}