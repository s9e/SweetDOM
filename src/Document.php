<?php declare(strict_types=1);

/**
* @package   s9e\SweetDOM
* @copyright Copyright (c) The s9e authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\SweetDOM;

use DOMDocument;
use DOMDocumentFragment;
use DOMNode;
use DOMNodeList;
use DOMXPath;
use function func_get_args;

class Document extends DOMDocument
{
	public NodeCreator $nodeCreator;

	/**
	* @link https://www.php.net/manual/domdocument.construct.php
	*/
	public function __construct(string $version = '1.0', string $encoding = '')
	{
		parent::__construct($version, $encoding);

		$this->nodeCreator = new NodeCreator($this);

		$this->registerNodeClass('DOMAttr',             Attr::class);
		$this->registerNodeClass('DOMCdataSection',     CdataSection::class);
		$this->registerNodeClass('DOMComment',          Comment::class);
		$this->registerNodeClass('DOMDocumentFragment', DocumentFragment::class);
		$this->registerNodeClass('DOMElement',          Element::class);
		$this->registerNodeClass('DOMText',             Text::class);
	}

	/**
	* Evaluate and return the result of a given XPath expression
	*/
	public function evaluate(string $expression, ?DOMNode $contextNode = null, bool $registerNodeNS = true): mixed
	{
		return $this->xpath('evaluate', func_get_args());
	}

	/**
	* Evaluate and return the first element of a given XPath query
	*/
	public function firstOf(string $expression, ?DOMNode $contextNode = null, bool $registerNodeNS = true): ?DOMNode
	{
		return $this->xpath('query', func_get_args())->item(0);
	}

	/**
	* Evaluate and return the result of a given XPath query
	*/
	public function query(string $expression, ?DOMNode $contextNode = null, bool $registerNodeNS = true): DOMNodeList
	{
		return $this->xpath('query', func_get_args());
	}

	/**
	* Execute a DOMXPath method and return the result
	*/
	protected function xpath(string $methodName, array $args): mixed
	{
		$xpath = new DOMXPath($this);
		$xpath->registerNamespace('xsl', 'http://www.w3.org/1999/XSL/Transform');
		$xpath->registerNodeNamespaces = true;

		return $xpath->$methodName(...$args);
	}
}