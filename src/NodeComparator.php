<?php declare(strict_types=1);

/**
* @package   s9e\SweetDOM
* @copyright Copyright (c) The s9e authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\SweetDOM;

use DOMAttr;
use DOMCharacterData;
use DOMElement;
use DOMNode;
use DOMProcessingInstruction;
use DOMXPath;

class NodeComparator
{
	// https://dom.spec.whatwg.org/#concept-node-equals
	// https://github.com/php/php-src/blob/master/ext/dom/node.c
	public static function isEqualNode(?DOMNode $node, ?DOMNode $otherNode): bool
	{
		if (!isset($node, $otherNode) || $node->nodeType !== $otherNode->nodeType)
		{
			return false;
		}
		if ($node instanceof DOMElement && $otherNode instanceof DOMElement)
		{
			return self::isEqualElementNode($node, $otherNode);
		}
		if ($node instanceof DOMCharacterData && $otherNode instanceof DOMCharacterData)
		{
			return $node->data === $otherNode->data;
		}
		if ($node instanceof DOMProcessingInstruction && $otherNode instanceof DOMProcessingInstruction)
		{
			return $node->target === $otherNode->target && $node->data === $otherNode->data;
		}
		if ($node instanceof DOMAttr && $otherNode instanceof DOMAttr)
		{
			return $node->namespaceURI === $otherNode->namespaceURI
			    && $node->localName    === $otherNode->localName
			    && $node->value        === $otherNode->value;
		}

		// TODO: test that CdataSection is not equal Text

		return false;
	}

	/**
	* @return array<string, string>
	*/
	protected static function getNamespaceDeclarations(DOMElement $element): array
	{
		$namespaces = [];
		$xpath      = new DOMXPath($element->ownerDocument);
		foreach ($xpath->query('namespace::*', $element) as $node)
		{
			if ($element->hasAttribute($node->nodeName))
			{
				$namespaces[$node->nodeName] = $node->nodeValue;
			}
		}

		return $namespaces;
	}

	protected static function hasEqualNamespaceDeclarations(DOMElement $element, DOMElement $otherElement): bool
	{
		return self::getNamespaceDeclarations($element) == self::getNamespaceDeclarations($otherElement);
	}

	protected static function isEqualElementNode(DOMElement $element, DOMElement $otherElement): bool
	{
		if ($element->namespaceURI       !== $otherElement->namespaceURI
		 || $element->nodeName           !== $otherElement->nodeName
		 || $element->attributes->length !== $otherElement->attributes->length
		 || $element->childNodes->length !== $otherElement->childNodes->length)
		{
			return false;
		}

		foreach ($element->attributes as $attribute)
		{
			if ($attribute->value !== $otherElement->attributes[$attribute->name]?->value)
			{
				return false;
			}
		}

		foreach ($element->childNodes as $i => $childNode)
		{
			if (!self::isEqualNode($childNode, $otherElement->childNodes[$i]))
			{
				return false;
			}
		}

		return self::hasEqualNamespaceDeclarations($element, $otherElement);
	}
}