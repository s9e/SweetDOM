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
* @method self appendXslApplyTemplates(string $select = null)
* @method self appendXslApplyTemplatesSibling(string $select = null)
* @method self appendXslAttribute(string $name, string $namespace = null)
* @method self appendXslAttributeSibling(string $name, string $namespace = null)
* @method self appendXslChoose()
* @method self appendXslChooseSibling()
* @method self appendXslComment(string $text = '')
* @method self appendXslCommentSibling(string $text = '')
* @method self appendXslCopyOf(string $select)
* @method self appendXslCopyOfSibling(string $select)
* @method self appendXslIf(string $test)
* @method self appendXslIfSibling(string $test)
* @method self appendXslOtherwise()
* @method self appendXslOtherwiseSibling()
* @method self appendXslText(string $text = '')
* @method self appendXslTextSibling(string $text = '')
* @method self appendXslValueOf(string $select)
* @method self appendXslValueOfSibling(string $select)
* @method self appendXslVariable(string $name, string $select = null)
* @method self appendXslVariableSibling(string $name, string $select = null)
* @method self appendXslWhen(string $test)
* @method self appendXslWhenSibling(string $test)
* @method self prependXslApplyTemplates(string $select = null)
* @method self prependXslApplyTemplatesSibling(string $select = null)
* @method self prependXslAttribute(string $name, string $namespace = null)
* @method self prependXslAttributeSibling(string $name, string $namespace = null)
* @method self prependXslChoose()
* @method self prependXslChooseSibling()
* @method self prependXslComment(string $text = '')
* @method self prependXslCommentSibling(string $text = '')
* @method self prependXslCopyOf(string $select)
* @method self prependXslCopyOfSibling(string $select)
* @method self prependXslIf(string $test)
* @method self prependXslIfSibling(string $test)
* @method self prependXslOtherwise()
* @method self prependXslOtherwiseSibling()
* @method self prependXslText(string $text = '')
* @method self prependXslTextSibling(string $text = '')
* @method self prependXslValueOf(string $select)
* @method self prependXslValueOfSibling(string $select)
* @method self prependXslVariable(string $name, string $select = null)
* @method self prependXslVariableSibling(string $name, string $select = null)
* @method self prependXslWhen(string $test)
* @method self prependXslWhenSibling(string $test)
*/
class Element extends DOMElement
{
	public function __call(string $name, array $arguments)
	{
		if (preg_match('(^(append|prepend)(xsl\\w+?)(sibling|)$)', strtolower($name), $m))
		{
			$callback = [$this->ownerDocument, 'create' . $m[2]];
			if (is_callable($callback))
			{
				$element = call_user_func_array($callback, $arguments);
				$where   = [
					'append'         => 'beforeend',
					'appendsibling'  => 'afterend',
					'prepend'        => 'afterbegin',
					'prependsibling' => 'beforebegin'
				];

				return $this->insertAdjacentElement($where[$m[1] . $m[3]], $element);
			}
		}

		throw new BadMethodCallException;
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
		$this->insertAdjacentXML($where, htmlspecialchars($text, ENT_XML1));
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
	* @return self This element
	*/
	public function remove(): self
	{
		return $this->parentNode->removeChild($this);
	}

	/**
	* Replace this element with given element
	*
	* @param  self $element Replacement element
	* @return self          This element
	*/
	public function replace(self $element): self
	{
		return $this->parentNode->replaceChild($element, $this);
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