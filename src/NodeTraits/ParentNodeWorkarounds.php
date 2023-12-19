<?php declare(strict_types=1);

/**
* @package   s9e\SweetDOM
* @copyright Copyright (c) The s9e authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\SweetDOM\NodeTraits;

use DOMNode;
use function array_reverse, is_string;

trait ParentNodeWorkarounds
{
	// https://github.com/php/php-src/pull/11768 - fixed in ~8.1.23, ^8.2.10
	// https://github.com/php/php-src/pull/12308 - not sure why this mitigates this issue
	public function append(...$nodes): void
	{
		foreach ($nodes as $node)
		{
			$this->appendChild(is_string($node) ? $this->ownerDocument->createTextNode($node) : $node);
		}
	}

	// https://github.com/php/php-src/pull/11768
	public function prepend(...$nodes): void
	{
		foreach (array_reverse($nodes) as $node)
		{
			$this->insertBefore(is_string($node) ? $this->ownerDocument->createTextNode($node) : $node, $this->firstChild);
		}
	}
}