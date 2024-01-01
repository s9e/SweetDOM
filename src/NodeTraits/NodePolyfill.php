<?php declare(strict_types=1);

/**
* @package   s9e\SweetDOM
* @copyright Copyright (c) The s9e authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\SweetDOM\NodeTraits;

use DOMAttr;
use DOMCharacterData;
use DOMElement;
use DOMNode;
use s9e\SweetDOM\NodeComparator;

trait NodePolyfill
{
	public function isEqualNode(?DOMNode $otherNode): bool
	{
		return NodeComparator::isEqualNode($this, $otherNode);
	}
}