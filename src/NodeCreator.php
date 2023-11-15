<?php declare(strict_types=1);

/**
* @package   s9e\SweetDOM
* @copyright Copyright (c) The s9e authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\SweetDOM;

use DOMException;
use const DOM_NAMESPACE_ERR, DOM_SYNTAX_ERR, ENT_XML1;
use function htmlspecialchars, str_contains, strpos, substr;

class NodeCreator
{
	public function __construct(protected Document $ownerDocument)
	{
	}

	public function createComment(string $data): Comment
	{
		if (str_contains($data, '--'))
		{
			throw new DOMException('Double hyphen within comment: ' . $data, DOM_SYNTAX_ERR);
		}

		return $this->ownerDocument->createComment($data);
	}

	/**
	* Create a DocumentFragment and execute given callback on it before returning it
	*/
	public function createDocumentFragment(callable $callback = null): DocumentFragment
	{
		$fragment = $this->ownerDocument->createDocumentFragment();
		if (isset($callback))
		{
			$callback($fragment);
		}

		return $fragment;
	}

	/**
	* Create and return an element
	*
	* @param  string $nodeName    Full qualified name for the node
	* @param  string $textContent Text content, will be automatically escaped
	* @return Element
	*/
	public function createElement(string $nodeName, string $textContent = ''): Element
	{
		$pos = strpos($nodeName, ':');
		if ($pos === false)
		{
			$namespace = null;
		}
		else
		{
			$prefix    = substr($nodeName, 0, $pos);
			$namespace = $this->ownerDocument->lookupNamespaceURI($prefix);
			if ($namespace === null)
			{
				throw new DOMException('Undefined namespace prefix', DOM_NAMESPACE_ERR);
			}
		}

		return $this->createElementNS($namespace, $nodeName, $textContent);
	}

	public function createElementNS(?string $namespace, string $nodeName, string $textContent = ''): Element
	{
		$value   = htmlspecialchars($textContent, ENT_XML1);
		$element = $this->ownerDocument->createElementNS($namespace, $nodeName, $value);
		if ($element instanceof Element)
		{
			return $element;
		}

		throw new DOMException;
	}

	/**
	* Create and return an xsl:apply-templates element
	*/
	public function createXslApplyTemplates(string $select = null, string $mode = null): Element
	{
		return $this->createXslElementByName('apply-templates', '', ['mode' => $mode, 'select' => $select]);
	}

	/**
	* Create and return an xsl:attribute element
	*/
	public function createXslAttribute(string $name, string $textContent = '', string $namespace = null): Element
	{
		return $this->createXslElementByName('attribute', $textContent, ['name' => $name, 'namespace' => $namespace]);
	}

	/**
	* Create and return an xsl:choose element
	*/
	public function createXslChoose(): Element
	{
		return $this->createXslElementByName('choose');
	}

	/**
	* Create and return an xsl:comment element
	*/
	public function createXslComment(string $textContent = ''): Element
	{
		return $this->createXslElementByName('comment', $textContent);
	}

	/**
	* Create and return an xsl:copy-of element
	*/
	public function createXslCopyOf(string $select): Element
	{
		return $this->createXslElementByName('copy-of', '', ['select' => $select]);
	}

	/**
	* Create and return an xsl:element element
	*/
	public function createXslElement(string $name, string $namespace = null, string $useAttributeSets = null): Element
	{
		return $this->createXslElementByName('element', '', ['name' => $name, 'namespace' => $namespace, 'use-attribute-sets' => $useAttributeSets]);
	}

	/**
	* Create and return an XSL element
	*/
	protected function createXslElementByName(string $localName, string $textContent = '', array $attributes = []): Element
	{
		$element = $this->createElementNS(
			'http://www.w3.org/1999/XSL/Transform',
			'xsl:' . $localName,
			$textContent
		);
		foreach ($attributes as $attrName => $attrValue)
		{
			// Skip attributes with a NULL value
			if (isset($attrValue))
			{
				$element->setAttribute($attrName, $attrValue);
			}
		}

		return $element;
	}

	/**
	* Create and return an xsl:if element
	*/
	public function createXslIf(string $test, string $textContent = ''): Element
	{
		return $this->createXslElementByName('if', $textContent, ['test' => $test]);
	}

	/**
	* Create and return an xsl:otherwise element
	*/
	public function createXslOtherwise(string $textContent = ''): Element
	{
		return $this->createXslElementByName('otherwise', $textContent);
	}

	/**
	* Create and return an xsl:text element
	*/
	public function createXslText(string $textContent = '', string $disableOutputEscaping = null): Element
	{
		return $this->createXslElementByName('text', $textContent, ['disable-output-escaping' => $disableOutputEscaping]);
	}

	/**
	* Create and return an xsl:value-of element
	*/
	public function createXslValueOf(string $select, string $disableOutputEscaping = null): Element
	{
		return $this->createXslElementByName('value-of', '', ['disable-output-escaping' => $disableOutputEscaping, 'select' => $select]);
	}

	/**
	* Create and return an xsl:variable element
	*/
	public function createXslVariable(string $name, string $select = null): Element
	{
		return $this->createXslElementByName('variable', '', ['name' => $name, 'select' => $select]);
	}

	/**
	* Create and return an xsl:when element
	*/
	public function createXslWhen(string $test, string $textContent = ''): Element
	{
		return $this->createXslElementByName('when', $textContent, ['test' => $test]);
	}
}