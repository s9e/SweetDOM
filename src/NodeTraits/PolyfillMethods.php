<?php declare(strict_types=1);

/**
* @package   s9e\SweetDOM
* @copyright Copyright (c) The s9e authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\SweetDOM\NodeTraits;

use DOMElement;
use DOMException;
use DOMNode;
use const DOM_SYNTAX_ERR;
use function preg_match, strtolower;

/**
* @method mixed magicMethodsCall(string $name, array $arguments)
*/
trait PolyfillMethods
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

		return $this->magicMethodsCall($name, $arguments);
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

	private function _insertAdjacentElement(string $where, DOMElement $element): DOMElement
	{
		$this->insertAdjacentNode($where, $element);

		return $element;
	}

	private function _insertAdjacentText(string $where, string $text): void
	{
		$node = $this->ownerDocument->createTextNode($text);
		$this->insertAdjacentNode($where, $node);
	}
}