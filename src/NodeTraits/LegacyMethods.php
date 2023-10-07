<?php declare(strict_types=1);

/**
* @package   s9e\SweetDOM
* @copyright Copyright (c) The s9e authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\SweetDOM\NodeTraits;

use BadMethodCallException;
use DOMElement;
use DOMException;
use DOMNode;
use DOMNodeList;
use DOMText;
use const DOM_SYNTAX_ERR, ENT_NOQUOTES, ENT_XML1;
use function array_flip, call_user_func_array, htmlspecialchars, is_callable, preg_match, preg_match_all, preg_replace_callback, strpos, strtolower, substr, ucfirst;

trait LegacyMethods
{
	use MagicMethods
	{
		MagicMethods::__call as magicMethodsCall;
	}

	public function __call(string $name, array $arguments)
	{
		if (preg_match('(^insertAdjacent(?:Element|Text)$)i', $name))
		{
			$methodName = '_' . $name;

			return $this->$methodName(...$arguments);
		}
		if (preg_match('(^(ap|pre)pendText(Sibling|)$)i', $name, $m))
		{
			$methodName = [
				'ap'         => 'append',
				'pre'        => 'prepend',
				'apsibling'  => 'after',
				'presibling' => 'before'
			][strtolower($m[1] . $m[2])];

			return $this->$methodName(...$arguments);
		}
		if (preg_match('(^(ap|pre)pend(\\w+)Sibling$)i', $name, $m))
		{
			$name = ['ap' => 'after', 'pre' => 'before'][$m[1]] . $m[2];
		}

		return $this->magicMethodsCall($name, $arguments);
	}

	private function _insertAdjacentElement(string $where, self $element): self
	{
		$this->insertAdjacentNode($where, $element);

		return $element;
	}

	private function _insertAdjacentText(string $where, string $text): void
	{
		$this->insertText($where, $text);
	}

	/**
	* @deprecated
	*/
	public function insertAdjacentXML(string $where, string $xml): void
	{
		$fragment = $this->ownerDocument->createDocumentFragment();
		$fragment->appendXML($this->addMissingNamespaceDeclarations($xml));

		$this->insertAdjacentNode($where, $fragment);
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
		match (strtolower($where))
		{
			'beforebegin' => $this->parentNode?->insertBefore($node, $this),
			'beforeend'   => $this->appendChild($node),
			'afterend'    => $this->parentNode?->insertBefore($node, $this->nextSibling),
			'afterbegin'  => $this->insertBefore($node, $this->firstChild),
			default       => throw new DOMException("'$where' is not one of 'beforebegin', 'afterbegin', 'beforeend', or 'afterend'", DOM_SYNTAX_ERR)
		};
	}

	/**
	* Insert given text relative to this element's position
	*
	* @param  string  $where One of 'beforebegin', 'afterbegin', 'beforeend', 'afterend'
	* @param  string  $text
	* @return DOMText
	*/
	protected function insertText(string $where, string $text): DOMText
	{
		$node = $this->ownerDocument->createTextNode($text);
		$this->insertAdjacentNode($where, $node);

		return $node;
	}
}