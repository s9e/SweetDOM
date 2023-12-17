<?php declare(strict_types=1);

/**
* @package   s9e\SweetDOM
* @copyright Copyright (c) The s9e authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\SweetDOM\NodeTraits;

use DOMNode;
use function array_reverse;

trait ChildNodeWorkarounds
{
	// https://github.com/php/php-src/pull/11768 - fixed in ~8.1.23, ^8.2.10
	// https://github.com/php/php-src/pull/11905 - behaviour changed in 8.3.0
	public function after(...$nodes): void
	{
		if (isset($this->parentNode))
		{
			foreach (array_reverse($nodes) as $node)
			{
				parent::after($node);
			}
		}
	}

	// https://github.com/php/php-src/pull/11905
	public function before(...$nodes): void
	{
		if (isset($this->parentNode))
		{
			foreach ($nodes as $node)
			{
				parent::before($node);
			}
		}
	}

	// https://github.com/php/php-src/issues/11289 - introduced in 8.1.18, 8.2.6 - fixed in ~8.1.10, ^8.2.8
	// https://github.com/php/php-src/pull/11905
	public function replaceWith(...$nodes): void
	{
		if (isset($this->parentNode))
		{
			$this->before(...$nodes);
			$this->remove();
		}
	}
}