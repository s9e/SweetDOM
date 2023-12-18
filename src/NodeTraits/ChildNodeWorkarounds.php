<?php declare(strict_types=1);

/**
* @package   s9e\SweetDOM
* @copyright Copyright (c) The s9e authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\SweetDOM\NodeTraits;

use DOMNode;
use function in_array, is_string;

trait ChildNodeWorkarounds
{
	// https://github.com/php/php-src/pull/11768 - fixed in ~8.1.23, ^8.2.10
	// https://github.com/php/php-src/pull/11905 - behaviour changed in 8.3.0
	public function after(...$nodes): void
	{
		$this->replaceWith($this, ...$nodes);
	}

	// https://github.com/php/php-src/pull/11905
	public function before(...$nodes): void
	{
		if (!in_array($this, $nodes, true))
		{
			$nodes[] = $this;
		}
		$this->replaceWith(...$nodes);
	}

	// https://github.com/php/php-src/issues/11289 - introduced in 8.1.18, 8.2.6 - fixed in ~8.1.10, ^8.2.8
	// https://github.com/php/php-src/pull/11888 - introduced in 8.2.8 - fixed in ^8.2.10
	// https://github.com/php/php-src/pull/11905
	public function replaceWith(...$nodes): void
	{
		if (!isset($this->parentNode))
		{
			return;
		}

		$contextNode = $this->ownerDocument->createTextNode('');
		$parentNode  = $this->parentNode;
		$parentNode->replaceChild($contextNode, $this);
		foreach ($nodes as $node)
		{
			if (is_string($node))
			{
				$node = $this->ownerDocument->createTextNode($node);
			}
			$parentNode->insertBefore($node, $contextNode);
		}
		$contextNode->remove();
	}
}