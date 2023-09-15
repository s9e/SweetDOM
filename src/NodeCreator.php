<?php declare(strict_types=1);

/**
* @package   s9e\SweetDOM
* @copyright Copyright (c) The s9e authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\SweetDOM;

use DOMException;
use const DOM_NAMESPACE_ERR, ENT_XML1, false;
use function htmlspecialchars, strpos, substr;

class NodeCreator
{
	public function __construct(protected Document $ownerDocument)
	{
	}

	/**
	* Create and return an element
	*/
	public function createElement(string $nodeName, string $textContent = ''): Element
	{
		$value = htmlspecialchars($textContent, ENT_XML1);

		$pos = strpos($nodeName, ':');
		if ($pos === false)
		{
			return $this->ownerDocument->createElement($nodeName, $value);
		}

		$prefix = substr($nodeName, 0, $pos);
		$nsURI  = $this->ownerDocument->lookupNamespaceURI($prefix);
		if ($nsURI === null)
		{
			throw new DOMException('Undefined namespace prefix', DOM_NAMESPACE_ERR);
		}

		return $this->ownerDocument->createElementNS($nsURI, $nodeName, $value);
	}

	/**
	* Create and return an XSL element
	*/
	protected function createElementXSL(string $localName, string $textContent = '', array $attributes = []): Element
	{
		$element = $this->ownerDocument->createElementNS(
			'http://www.w3.org/1999/XSL/Transform',
			'xsl:' . $localName,
			htmlspecialchars($textContent, ENT_XML1)
		);
		foreach ($attributes as $attrName => $attrValue)
		{
			if (isset($attrValue))
			{
				$element->setAttribute($attrName, $attrValue);
			}
		}

		return $element;
	}

	/**
	* Create and return an xsl:apply-templates element
	*/
	public function createXslApplyTemplates(string $select = null, string $mode = null): Element
	{
		return $this->createElementXSL('apply-templates', '', ['select' => $select, 'mode' => $mode]);
	}

	/**
	* Create and return an xsl:attribute element
	*/
	public function createXslAttribute(string $name, string $textContent = ''): Element
	{
		return $this->createElementXSL('attribute', $textContent, ['name' => $name]);
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
	* @param  string  $textContent Text content for the comment
	* @return Element
	*/
	public function createXslComment(string $textContent = ''): Element
	{
		return $this->createElementXSL('comment', $textContent);
	}

	/**
	* Create and return an xsl:copy-of element
	*
	* @param  string  $select XPath expression for the "select" attribute
	* @return Element
	*/
	public function createXslCopyOf(string $select): Element
	{
		return $this->createElementXSL('copy-of', '', ['select' => $select]);
	}

	/**
	* Create and return an xsl:if element
	*
	* @param  string  $test XPath expression for the "test" attribute
	* @param  string  $textContent Text content for the element
	* @return Element
	*/
	public function createXslIf(string $test, string $textContent = ''): Element
	{
		return $this->createElementXSL('if', $textContent, ['test' => $test]);
	}

	/**
	* Create and return an xsl:otherwise element
	*
	* @param  string  $textContent Text content for the element
	* @return Element
	*/
	public function createXslOtherwise(string $textContent = ''): Element
	{
		return $this->createElementXSL('otherwise', $textContent);
	}

	/**
	* Create and return an xsl:text element
	*/
	public function createXslText(string $textContent = '', string $disableOutputEscaping = null): Element
	{
		return $this->createElementXSL('text', $textContent, ['disable-output-escaping' => $disableOutputEscaping]);
	}

	/**
	* Create and return an xsl:value-of element
	*/
	public function createXslValueOf(string $select): Element
	{
		return $this->createElementXSL('value-of', '', ['select' => $select]);

	}

	/**
	* Create and return an xsl:variable element
	*/
	public function createXslVariable(string $name, string $select = null): Element
	{
		return $this->createElementXSL('variable', '', ['name' => $name, 'select' => $select]);
	}

	/**
	* Create and return an xsl:when element
	*/
	public function createXslWhen(string $test, string $textContent = ''): Element
	{
		return $this->createElementXSL('when', $textContent, ['test' => $test]);
	}
}