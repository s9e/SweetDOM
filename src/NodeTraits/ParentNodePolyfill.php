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
use function strtolower;

trait ParentNodePolyfill
{
	public function insertAdjacentElement(string $where, DOMElement $element): ?DOMElement
	{
		$this->insertAdjacentNode($where, $element);

		return $element;
	}

	public function insertAdjacentText(string $where, string $data): void
	{
		$node = $this->ownerDocument->createTextNode($data);
		$this->insertAdjacentNode($where, $node);
	}

	public function replaceChildren(...$nodes): void
	{
		while (isset($this->lastChild))
		{
			$this->lastChild->remove();
		}
		$this->append(...$nodes);
	}

	/**
	* Insert given node relative to this element's position
	*
	* @param  string  $where One of 'beforebegin', 'afterbegin', 'beforeend', 'afterend'
	* @param  DOMNode $node
	* @return void
	*/
	private function insertAdjacentNode(string $where, DOMNode $node): void
	{
		match (strtolower($where))
		{
			'afterbegin'  => $this->prepend($node),
			'afterend'    => $this->after($node),
			'beforebegin' => $this->before($node),
			'beforeend'   => $this->appendChild($node),
			default       => throw new DOMException("'$where' is not one of 'beforebegin', 'afterbegin', 'beforeend', or 'afterend'", DOM_SYNTAX_ERR)
		};
	}
}