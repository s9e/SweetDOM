<?php declare(strict_types=1);

/**
* @package   s9e\SweetDOM
* @copyright Copyright (c) The s9e authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\SweetDOM\NodeTraits;

use DOMNode;

trait NodePolyfill
{
	public function isEqualNode(?DOMNode $otherNode): bool
	{
		if ($this->isSameNode($otherNode))
		{
			return true;
		}

		if ($this->nodeType !== $otherNode?->nodeType
		 || $this->nodeName !== $otherNode->nodeName)
		{
		}
	}
}