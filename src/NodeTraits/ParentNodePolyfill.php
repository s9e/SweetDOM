<?php declare(strict_types=1);

/**
* @package   s9e\SweetDOM
* @copyright Copyright (c) The s9e authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\SweetDOM\NodeTraits;

use DOMElement;

trait ParentNodePolyfill
{
	public function insertAdjacentElement(string $where, DOMElement $element): ?DOMElement
	{
	}

	public function insertAdjacentText(string $where, string $data): void
	{
	}

	public function replaceChildren(...$nodes): void
	{
		while (isset($this->lastChild))
		{
			$this->lastChild->remove();
		}
		$this->append(...$nodes);
	}
}