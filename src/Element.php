<?php declare(strict_types=1);

/**
* @package   s9e\SweetDOM
* @copyright Copyright (c) 2019-2020 The s9e authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\SweetDOM;

use DOMElement;
use BadMethodCallException;

class Element extends DOMElement
{
	public function __call(string $name, array $arguments)
	{
		if (preg_match('(^append(Xsl\\w++)$)', $name, $m))
		{
			$callback = [$this->ownerDocument, 'create' . $m[1]];
			if (is_callable($callback))
			{
				$element = call_user_func_array($callback, $arguments);

				return $this->appendChild($element);
			}
		}

		throw new BadMethodCallException;
	}

	/**
	* Evaluate and return the result of a given XPath expression using this element as context
	*
	* @param  string $query XPath expression
	* @return mixed
	*/
	public function evaluate(string $query)
	{
		return $this->ownerDocument->evaluate($query, $this);
	}

	/**
	* Evaluate and return the first element of a given XPath query using this element as context
	*
	* @param  string $query XPath expression
	* @return mixed
	*/
	public function firstOf(string $query)
	{
		return $this->ownerDocument->firstOf($query, $this);
	}

	/**
	* Evaluate and return the result of a given XPath query using this element as context
	*
	* @param  string $query XPath expression
	* @return mixed
	*/
	public function query(string $query)
	{
		return $this->ownerDocument->query($query, $this);
	}

	public function remove(): self
	{
		return $this->parentNode->removeChild($this);
	}

	public function replace(DOMElement $element): self
	{
		return $this->parentNode->replaceChild($element, $this);
	}
}