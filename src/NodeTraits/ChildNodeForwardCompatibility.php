<?php declare(strict_types=1);

/**
* @package   s9e\SweetDOM
* @copyright Copyright (c) The s9e authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\SweetDOM\NodeTraits;

trait ChildNodeForwardCompatibility
{
	// https://github.com/php/php-src/pull/11905 - behaviour changed in 8.3.0
	public function after(...$nodes): void
	{
		if (isset($this->parentNode))
		{
			parent::after(...$nodes);
		}
	}

	// https://github.com/php/php-src/pull/11905
	public function before(...$nodes): void
	{
		if (isset($this->parentNode))
		{
			parent::before(...$nodes);
		}
	}

	// https://github.com/php/php-src/pull/11905
	public function replaceWith(...$nodes): void
	{
		if (isset($this->parentNode))
		{
			parent::replaceWith(...$nodes);
		}
	}
}