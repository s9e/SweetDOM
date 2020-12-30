<?php declare(strict_types=1);

/**
* @package   s9e\SweetDOM
* @copyright Copyright (c) 2019-2020 The s9e authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\SweetDOM;

use BadMethodCallException;
use DOMElement;
use DOMNode;
use DOMNodeList;
use InvalidArgumentException;

/**
* @method self appendElement(string $nodeName, $text = '')
* @method self appendElementSibling(string $nodeName, $text = '')
* @method void appendText(string $text)
* @method void appendTextSibling(string $text)
* @method self appendXslApplyTemplates(string $select = null)
* @method self appendXslApplyTemplatesSibling(string $select = null)
* @method self appendXslAttribute(string $name, string $text = '')
* @method self appendXslAttributeSibling(string $name, string $text = '')
* @method self appendXslChoose()
* @method self appendXslChooseSibling()
* @method self appendXslComment(string $text = '')
* @method self appendXslCommentSibling(string $text = '')
* @method self appendXslCopyOf(string $select)
* @method self appendXslCopyOfSibling(string $select)
* @method self appendXslIf(string $test, string $text = '')
* @method self appendXslIfSibling(string $test, string $text = '')
* @method self appendXslOtherwise(string $text = '')
* @method self appendXslOtherwiseSibling(string $text = '')
* @method self appendXslText(string $text = '')
* @method self appendXslTextSibling(string $text = '')
* @method self appendXslValueOf(string $select)
* @method self appendXslValueOfSibling(string $select)
* @method self appendXslVariable(string $name, string $select = null)
* @method self appendXslVariableSibling(string $name, string $select = null)
* @method self appendXslWhen(string $test, string $text = '')
* @method self appendXslWhenSibling(string $test, string $text = '')
* @method self prependElement(string $nodeName, $text = '')
* @method self prependElementSibling(string $nodeName, $text = '')
* @method void prependText(string $text)
* @method void prependTextSibling(string $text)
* @method self prependXslApplyTemplates(string $select = null)
* @method self prependXslApplyTemplatesSibling(string $select = null)
* @method self prependXslAttribute(string $name, string $text = '')
* @method self prependXslAttributeSibling(string $name, string $text = '')
* @method self prependXslChoose()
* @method self prependXslChooseSibling()
* @method self prependXslComment(string $text = '')
* @method self prependXslCommentSibling(string $text = '')
* @method self prependXslCopyOf(string $select)
* @method self prependXslCopyOfSibling(string $select)
* @method self prependXslIf(string $test, string $text = '')
* @method self prependXslIfSibling(string $test, string $text = '')
* @method self prependXslOtherwise(string $text = '')
* @method self prependXslOtherwiseSibling(string $text = '')
* @method self prependXslText(string $text = '')
* @method self prependXslTextSibling(string $text = '')
* @method self prependXslValueOf(string $select)
* @method self prependXslValueOfSibling(string $select)
* @method self prependXslVariable(string $name, string $select = null)
* @method self prependXslVariableSibling(string $name, string $select = null)
* @method self prependXslWhen(string $test, string $text = '')
* @method self prependXslWhenSibling(string $test, string $text = '')
*/
class Element extends DOMElement
{
	public function __call(string $name, array $arguments)
	{
		$name      = strtolower($name);
		$positions = [
			'append'         => 'beforeend',
			'appendsibling'  => 'afterend',
			'prepend'        => 'afterbegin',
			'prependsibling' => 'beforebegin'
		];

		if (preg_match('(^(append|prepend)(xsl\\w+?)(sibling|)$)', $name, $m))
		{
			$localName = $m[2];
			$where     = $positions[$m[1] . $m[3]];

			return $this->insertXslElement($localName, $where, $arguments);
		}
		if (preg_match('(^(append|prepend)element(sibling|)$)', $name, $m))
		{
			$nodeName = $arguments[0];
			$text     = $arguments[1] ?? '';
			$where    = $positions[$m[1] . $m[2]];

			return $this->insertElement($nodeName, $where, $text);
		}
		if (preg_match('(^(append|prepend)text(sibling|)$)', $name, $m))
		{
			$text  = $arguments[0];
			$where = $positions[$m[1] . $m[2]];

			$this->insertAdjacentText($where, $text);

			return;
		}

		throw new BadMethodCallException;
	}

	/**
	* Create and insert an element at given position
	*
	* @param  string $nodeName Element's nodeName
	* @param  string $where    One of 'beforebegin', 'afterbegin', 'beforeend', 'afterend'
	* @param  string $text     Text content
	* @return self
	*/
	protected function insertElement(string $nodeName, string $where, string $text): self
	{
		$text = htmlspecialchars($text, ENT_NOQUOTES);
		$pos  = strpos($nodeName, ':');
		if ($pos === false)
		{
			$element = $this->ownerDocument->createElement($nodeName, $text);
		}
		else
		{
			$prefix       = substr($nodeName, 0, $pos);
			$namespaceURI = $this->ownerDocument->lookupNamespaceURI($prefix);
			$element      = $this->ownerDocument->createElementNS($namespaceURI, $nodeName, $text);
		}

		return $this->insertAdjacentElement($where, $element);
	}

	/**
	* Create and insert an XSL element at given position
	*
	* @param  string $localName Element's localName
	* @param  string $where     One of 'beforebegin', 'afterbegin', 'beforeend', 'afterend'
	* @param  array  $arguments Arguments passed to the Document::create* function
	* @return self
	*/
	protected function insertXslElement(string $localName, string $where, array $arguments): self
	{
		$callback = [$this->ownerDocument, 'create' . $localName];
		if (!is_callable($callback))
		{
			throw new BadMethodCallException;
		}

		$element = call_user_func_array($callback, $arguments);

		return $this->insertAdjacentElement($where, $element);
	}

	/**
	* Evaluate and return the result of a given XPath expression using this element as context node
	*
	* @param  string  $expr XPath expression
	* @return mixed
	*/
	public function evaluate(string $expr)
	{
		return $this->ownerDocument->evaluate($expr, $this);
	}

	/**
	* Evaluate and return the first element of a given XPath query using this element as context node
	*
	* @param  string       $expr XPath expression
	* @return DOMNode|null
	*/
	public function firstOf(string $expr): ?DOMNode
	{
		return $this->ownerDocument->firstOf($expr, $this);
	}

	/**
	* Insert given element relative to this element's position
	*
	* @param  string $where   One of 'beforebegin', 'afterbegin', 'beforeend', 'afterend'
	* @param  self   $element
	* @return self
	*/
	public function insertAdjacentElement(string $where, self $element): self
	{
		$this->insertAdjacentNode($where, $element);

		return $element;
	}

	/**
	* Insert given text relative to this element's position
	*
	* @param  string $where One of 'beforebegin', 'afterbegin', 'beforeend', 'afterend'
	* @param  string $text
	* @return void
	*/
	public function insertAdjacentText(string $where, string $text): void
	{
		$this->insertAdjacentNode($where, $this->ownerDocument->createTextNode($text));
	}

	/**
	* Insert given XML relative to this element's position
	*
	* @param  string $where One of 'beforebegin', 'afterbegin', 'beforeend', 'afterend'
	* @param  string $xml
	* @return void
	*/
	public function insertAdjacentXML(string $where, string $xml): void
	{
		$fragment = $this->ownerDocument->createDocumentFragment();
		$fragment->appendXML($this->addMissingNamespaceDeclarations($xml));

		$this->insertAdjacentNode($where, $fragment);
	}

	/**
	* Evaluate and return the result of a given XPath query using this element as context node
	*
	* @param  string      $expr XPath expression
	* @return DOMNodeList
	*/
	public function query(string $expr): DOMNodeList
	{
		return $this->ownerDocument->query($expr, $this);
	}

	/**
	* Remove this element from the document
	*
	* @return void
	*/
	public function remove(): void
	{
		$this->parentNode->removeChild($this);
	}

	/**
	* Replace this element with given nodes/text
	*
	* @param  DOMNode|string $nodes
	* @return void
	*/
	public function replaceWith(...$nodes): void
	{
		foreach ($nodes as $node)
		{
			if (!($node instanceof DOMNode))
			{
				$node = $this->ownerDocument->createTextNode((string) $node);
			}
			$this->parentNode->insertBefore($node, $this);
		}
		$this->parentNode->removeChild($this);
	}

	/**
	* Add namespace declarations that may be missing in given XML
	*
	* @param  string $xml Original XML
	* @return string      Modified XML
	*/
	protected function addMissingNamespaceDeclarations(string $xml): string
	{
		preg_match_all('(xmlns:\\K[-\\w]++(?==))', $xml, $m);
		$prefixes = array_flip($m[0]);

		return preg_replace_callback(
			'(<([-\\w]++):[^>]*?\\K\\s*/?>)',
			function ($m) use ($prefixes)
			{
				$return = $m[0];
				$prefix = $m[1];
				if (!isset($prefixes[$prefix]))
				{
					$nsURI  = $this->lookupNamespaceURI($prefix);
					$return = ' xmlns:' . $prefix . '="' . htmlspecialchars($nsURI, ENT_XML1) . '"' . $return;
				}

				return $return;
			},
			$xml
		);
	}

	/**
	* Insert given node relative to this element's position
	*
	* @param  string  $where One of 'beforebegin', 'afterbegin', 'beforeend', 'afterend'
	* @param  DOMNode $node
	* @return void
	*/
	protected function insertAdjacentNode(string $where, DOMNode $node): void
	{
		$where = strtolower($where);
		if ($where === 'beforebegin')
		{
			$this->parentNode->insertBefore($node, $this);
		}
		elseif ($where === 'beforeend')
		{
			$this->appendChild($node);
		}
		elseif ($where === 'afterend')
		{
			$this->parentNode->insertBefore($node, $this->nextSibling);
		}
		elseif ($where === 'afterbegin')
		{
			$this->insertBefore($node, $this->firstChild);
		}
		else
		{
			throw new InvalidArgumentException;
		}
	}
}