<?php declare(strict_types=1);

/**
* @package   s9e\SweetDOM
* @copyright Copyright (c) The s9e authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\SweetDOM;

use DOMException;
use const DOM_NAMESPACE_ERR, false;
use function strpos, substr;

class NodeCreator
{
	public function __construct(protected Document $ownerDocument)
	{
	}

	/**
	* Create and return an xsl:apply-templates element
	*
	* @param  string  $select XPath expression for the "select" attribute
	* @return Element
	*/
	public function createXslApplyTemplates(string $select = null): Element
	{
		$element = $this->createElementXSL('apply-templates');
		if (isset($select))
		{
			$element->setAttribute('select', $select);
		}

		return $element;
	}

	/**
	* Create and return an xsl:attribute element
	*
	* @param  string  $name Attribute's name
	* @param  string  $text Text content for the element
	* @return Element
	*/
	public function createXslAttribute(string $name, string $text = ''): Element
	{
		$element = $this->createElementXSL('attribute', $text);
		$element->setAttribute('name', $name);

		return $element;
	}

	/**
	* Create and return an xsl:choose element
	*
	* @return Element
	*/
	public function createXslChoose(): Element
	{
		return $this->createElementXSL('choose');
	}

	/**
	* Create and return an xsl:comment element
	*
	* @param  string  $text Text content for the comment
	* @return Element
	*/
	public function createXslComment(string $text = ''): Element
	{
		return $this->createElementXSL('comment', $text);
	}

	/**
	* Create and return an xsl:copy-of element
	*
	* @param  string  $select XPath expression for the "select" attribute
	* @return Element
	*/
	public function createXslCopyOf(string $select): Element
	{
		$element = $this->createElementXSL('copy-of');
		$element->setAttribute('select', $select);

		return $element;
	}

	/**
	* Create and return an xsl:if element
	*
	* @param  string  $test XPath expression for the "test" attribute
	* @param  string  $text Text content for the element
	* @return Element
	*/
	public function createXslIf(string $test, string $text = ''): Element
	{
		$element = $this->createElementXSL('if', $text);
		$element->setAttribute('test', $test);

		return $element;
	}

	/**
	* Create and return an xsl:otherwise element
	*
	* @param  string  $text Text content for the element
	* @return Element
	*/
	public function createXslOtherwise(string $text = ''): Element
	{
		return $this->createElementXSL('otherwise', $text);
	}

	/**
	* Create and return an xsl:text element
	*
	* @param  string  $text Text content for the element
	* @return Element
	*/
	public function createXslText(string $text = ''): Element
	{
		return $this->createElementXSL('text', $text);
	}

	/**
	* Create and return an xsl:value-of element
	*
	* @param  string  $select XPath expression for the "select" attribute
	* @return Element
	*/
	public function createXslValueOf(string $select): Element
	{
		$element = $this->createElementXSL('value-of');
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
		$element = $this->createElementXSL('variable');
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
	* @param  string  $text Text content for the element
	* @return Element
	*/
	public function createXslWhen(string $test, string $text = ''): Element
	{
		$element = $this->createElementXSL('when', $text);
		$element->setAttribute('test', $test);

		return $element;
	}

	/**
	* Create and return an element
	*
	* @param  string  $nodeName Element's node name
	* @param  string  $text     Text content for the element
	* @return Element
	*/
	public function createElement(string $nodeName, string $text = ''): Element
	{
		$pos = strpos($nodeName, ':');
		if ($pos === false)
		{
			return $this->createElement($nodeName, $text);
		}
		else
		{
			$prefix = substr($nodeName, 0, $pos);
			$nsURI  = $this->lookupNamespaceURI($prefix);
			if ($nsURI === null)
			{
				throw new DOMException('Undefined namespace prefix', DOM_NAMESPACE_ERR);
			}

			return $this->ownerDocument->createElementNS($nsURI, $nodeName, $text);
		}
	}
}