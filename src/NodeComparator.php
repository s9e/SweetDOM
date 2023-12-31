<?php declare(strict_types=1);

/**
* @package   s9e\SweetDOM
* @copyright Copyright (c) The s9e authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\SweetDOM;

use DOMAttr;
use DOMCharacterData;
use DOMDocument;
use DOMDocumentFragment;
use DOMDocumentType;
use DOMElement;
use DOMEntity;
use DOMEntityReference;
use DOMNode;
use DOMNodeList;
use DOMNotation;
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
			// Covers DOMCdataSection, DOMComment, and DOMText
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
		if (($node instanceof DOMDocument         && $otherNode instanceof DOMDocument)
		 || ($node instanceof DOMDocumentFragment && $otherNode instanceof DOMDocumentFragment))
		{
			return self::isEqualNodeList($node->childNodes, $otherNode->childNodes);
		}
		if ($node instanceof DOMDocumentType && $otherNode instanceof DOMDocumentType)
		{
			return $node->name     === $otherNode->name
			    && $node->publicId === $otherNode->publicId
			    && $node->systemId === $otherNode->systemId;
		}
		if ($node instanceof DOMEntityReference && $otherNode instanceof DOMEntityReference)
		{
			return $node->nodeName === $otherNode->nodeName;
		}
		if (($node instanceof DOMEntity   && $otherNode instanceof DOMEntity)
		 || ($node instanceof DOMNotation && $otherNode instanceof DOMNotation))
		{
			return $node->nodeName === $otherNode->nodeName
			    && $node->publicId === $otherNode->publicId
			    && $node->systemId === $otherNode->systemId;
		}

		// @codeCoverageIgnoreStart
		return $node->isSameNode($otherNode);
		// @codeCoverageIgnoreEnd
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
			if ($attribute->value !== $otherElement->attributes->getNamedItem($attribute->name)?->value)
			{
				return false;
			}
		}

		return self::isEqualNodeList($element->childNodes, $otherElement->childNodes)
		    && self::hasEqualNamespaceDeclarations($element, $otherElement);
	}

	protected static function isEqualNodeList(DOMNodeList $list, DOMNodeList $otherList): bool
	{
		if ($list->length !== $otherList->length)
		{
			return false;
		}
		foreach ($list as $i => $node)
		{
			if (!self::isEqualNode($node, $otherList->item($i)))
			{
				return false;
			}
		}

		return true;
	}
}