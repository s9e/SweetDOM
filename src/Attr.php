<?php declare(strict_types=1);

/**
* @package   s9e\SweetDOM
* @copyright Copyright (c) The s9e authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\SweetDOM;

use DOMAttr;
use s9e\SweetDOM\NodeTraits\XPathMethods;

/**
* @property ?Document $ownerDocument
* @property ?Element $ownerElement
* @property ?Element $parentElement
*/
class Attr extends DOMAttr
{
	use XPathMethods;
}