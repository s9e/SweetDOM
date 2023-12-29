<?php declare(strict_types=1);

/**
* @package   s9e\SweetDOM
* @copyright Copyright (c) The s9e authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\SweetDOM\ForwardCompatibleNodes;

use s9e\SweetDOM\Comment as ParentClass;
use s9e\SweetDOM\NodeTraits\ChildNodeForwardCompatibility;
use s9e\SweetDOM\NodeTraits\NodePolyfill;

class Comment extends ParentClass
{
	use ChildNodeForwardCompatibility;
	use NodePolyfill;
}