<?php declare(strict_types=1);

/**
* @package   s9e\SweetDOM
* @copyright Copyright (c) The s9e authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\SweetDOM\PatchedNodes;

use s9e\SweetDOM\Element as ParentClass;
use s9e\SweetDOM\NodeTraits\ChildNodeWorkarounds;
use s9e\SweetDOM\NodeTraits\NodePolyfill;
use s9e\SweetDOM\NodeTraits\ParentNodePolyfill;
use s9e\SweetDOM\NodeTraits\ParentNodeWorkarounds;

class Element extends ParentClass
{
	use ChildNodeWorkarounds;
	use NodePolyfill;
	use ParentNodePolyfill;
	use ParentNodeWorkarounds;
}