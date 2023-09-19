<?php declare(strict_types=1);

/**
* @package   s9e\SweetDOM
* @copyright Copyright (c) The s9e authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\SweetDOM;

use DOMDocumentFragment;
use s9e\SweetDOM\NodeTraits\MagicMethods;
use s9e\SweetDOM\NodeTraits\XPathMethods;

/**
*  
*/
class DocumentFragment extends DOMDocumentFragment
{
	use MagicMethods;
	use XPathMethods;
}