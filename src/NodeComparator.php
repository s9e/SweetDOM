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
use function substr;

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
		$classes = [
			'DOMElement',
			'DOMCharacterData',
			'DOMProcessingInstruction',
			'DOMAttr',
			'DOMDocument',
			'DOMDocumentFragment',
			'DOMDocumentType',
			'DOMEntityReference',
			'DOMEntity',
			'DOMNotation'
		];
		foreach ($classes as $className)
		{
			if ($node instanceof $className && $otherNode instanceof $className)
			{
				$methodName = 'isEqual' . substr($className, 3);

				return static::$methodName($node, $otherNode);
			}
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
		return static::getNamespaceDeclarations($element) == static::getNamespaceDeclarations($otherElement);
	}

	protected static function isEqualAttr(DOMAttr $node, DOMAttr $otherNode): bool
	{
		return $node->namespaceURI === $otherNode->namespaceURI
		    && $node->localName    === $otherNode->localName
		    && $node->value        === $otherNode->value;
	}

	protected static function isEqualCharacterData(DOMCharacterData $node, DOMCharacterData $otherNode): bool
	{
		// Covers DOMCdataSection, DOMComment, and DOMText
		return $node->data === $otherNode->data;
	}

	protected static function isEqualDocument(DOMDocument $node, DOMDocument $otherNode): bool
	{
		return static::isEqualNodeList($node->childNodes, $otherNode->childNodes);
	}

	protected static function isEqualDocumentFragment(DOMDocumentFragment $node, DOMDocumentFragment $otherNode): bool
	{
		return static::isEqualNodeList($node->childNodes, $otherNode->childNodes);
	}

	protected static function isEqualDocumentType(DOMDocumentType $node, DOMDocumentType $otherNode): bool
	{
		return $node->name     === $otherNode->name
		    && $node->publicId === $otherNode->publicId
		    && $node->systemId === $otherNode->systemId;
	}

	protected static function isEqualElement(DOMElement $element, DOMElement $otherElement): bool
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

		return static::isEqualNodeList($element->childNodes, $otherElement->childNodes)
		    && static::hasEqualNamespaceDeclarations($element, $otherElement);
	}

	protected static function isEqualEntity(DOMEntity $node, DOMEntity $otherNode): bool
	{
		return $node->nodeName === $otherNode->nodeName
		    && $node->publicId === $otherNode->publicId
		    && $node->systemId === $otherNode->systemId;
	}

	protected static function isEqualEntityReference(DOMEntityReference $node, DOMEntityReference $otherNode): bool
	{
		return $node->nodeName === $otherNode->nodeName;
	}

	protected static function isEqualNodeList(DOMNodeList $list, DOMNodeList $otherList): bool
	{
		if ($list->length !== $otherList->length)
		{
			return false;
		}
		foreach ($list as $i => $node)
		{
			if (!static::isEqualNode($node, $otherList->item($i)))
			{
				return false;
			}
		}

		return true;
	}

	protected static function isEqualNotation(DOMNotation $node, DOMNotation $otherNode): bool
	{
		return $node->nodeName === $otherNode->nodeName
		    && $node->publicId === $otherNode->publicId
		    && $node->systemId === $otherNode->systemId;
	}

	protected static function isEqualProcessingInstruction(DOMProcessingInstruction $node, DOMProcessingInstruction $otherNode): bool
	{
		return $node->target === $otherNode->target && $node->data === $otherNode->data;
	}
}