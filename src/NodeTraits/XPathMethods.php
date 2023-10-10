<?php declare(strict_types=1);

/**
* @package   s9e\SweetDOM
* @copyright Copyright (c) The s9e authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\SweetDOM\NodeTraits;

use DOMNode;
use DOMNodeList;

trait XPathMethods
{
	/**
	* Evaluate and return the result of a given XPath expression using this element as context node
	*/
	public function evaluate(string $expression): mixed
	{
		return $this->ownerDocument->evaluate($expression, $this);
	}

	/**
	* Evaluate and return the first element of a given XPath query using this element as context node
	*/
	public function firstOf(string $expression): ?DOMNode
	{
		return $this->ownerDocument->firstOf($expression, $this);
	}

	/**
	* Evaluate and return the result of a given XPath query using this element as context node
	*/
	public function query(string $expression): DOMNodeList
	{
		return $this->ownerDocument->query($expression, $this);
	}
}