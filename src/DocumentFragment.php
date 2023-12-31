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
* @method Comment appendComment(string $data)
* @method mixed appendDocumentFragment(?callable $callback = null)
* @method Element appendElement(string $nodeName, string $textContent = '')
* @method Element appendElementNS(?string $namespace, string $nodeName, string $textContent = '')
* @method Element appendXslApplyTemplates(?string $select = null, ?string $mode = null)
* @method Element appendXslAttribute(string $name, string $textContent = '', ?string $namespace = null)
* @method Element appendXslChoose()
* @method Element appendXslComment(string $textContent = '')
* @method Element appendXslCopyOf(string $select)
* @method Element appendXslElement(string $name, ?string $namespace = null, ?string $useAttributeSets = null)
* @method Element appendXslIf(string $test, string $textContent = '')
* @method Element appendXslOtherwise(string $textContent = '')
* @method Element appendXslText(string $textContent = '', ?string $disableOutputEscaping = null)
* @method Element appendXslValueOf(string $select, ?string $disableOutputEscaping = null)
* @method Element appendXslVariable(string $name, ?string $select = null)
* @method Element appendXslWhen(string $test, string $textContent = '')
* @method Comment prependComment(string $data)
* @method mixed prependDocumentFragment(?callable $callback = null)
* @method Element prependElement(string $nodeName, string $textContent = '')
* @method Element prependElementNS(?string $namespace, string $nodeName, string $textContent = '')
* @method Element prependXslApplyTemplates(?string $select = null, ?string $mode = null)
* @method Element prependXslAttribute(string $name, string $textContent = '', ?string $namespace = null)
* @method Element prependXslChoose()
* @method Element prependXslComment(string $textContent = '')
* @method Element prependXslCopyOf(string $select)
* @method Element prependXslElement(string $name, ?string $namespace = null, ?string $useAttributeSets = null)
* @method Element prependXslIf(string $test, string $textContent = '')
* @method Element prependXslOtherwise(string $textContent = '')
* @method Element prependXslText(string $textContent = '', ?string $disableOutputEscaping = null)
* @method Element prependXslValueOf(string $select, ?string $disableOutputEscaping = null)
* @method Element prependXslVariable(string $name, ?string $select = null)
* @method Element prependXslWhen(string $test, string $textContent = '')
* @property ?Element $firstElementChild
* @property ?Element $lastElementChild
* @property ?Document $ownerDocument
* @property ?Element $parentElement
*/
class DocumentFragment extends DOMDocumentFragment
{
	use MagicMethods;
	use XPathMethods;
}