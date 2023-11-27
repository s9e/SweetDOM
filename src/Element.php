<?php declare(strict_types=1);

/**
* @package   s9e\SweetDOM
* @copyright Copyright (c) The s9e authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\SweetDOM;

use DOMElement;
use s9e\SweetDOM\NodeTraits\DeprecatedMethods;
use s9e\SweetDOM\NodeTraits\XPathMethods;

/**
* @method Comment afterComment(string $data)
* @method mixed afterDocumentFragment(?callable $callback = null)
* @method Element afterElement(string $nodeName, string $textContent = '')
* @method Element afterElementNS(?string $namespace, string $nodeName, string $textContent = '')
* @method Element afterXslApplyTemplates(?string $select = null, ?string $mode = null)
* @method Element afterXslAttribute(string $name, string $textContent = '', ?string $namespace = null)
* @method Element afterXslChoose()
* @method Element afterXslComment(string $textContent = '')
* @method Element afterXslCopyOf(string $select)
* @method Element afterXslElement(string $name, ?string $namespace = null, ?string $useAttributeSets = null)
* @method Element afterXslIf(string $test, string $textContent = '')
* @method Element afterXslOtherwise(string $textContent = '')
* @method Element afterXslText(string $textContent = '', ?string $disableOutputEscaping = null)
* @method Element afterXslValueOf(string $select, ?string $disableOutputEscaping = null)
* @method Element afterXslVariable(string $name, ?string $select = null)
* @method Element afterXslWhen(string $test, string $textContent = '')
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
* @method Comment beforeComment(string $data)
* @method mixed beforeDocumentFragment(?callable $callback = null)
* @method Element beforeElement(string $nodeName, string $textContent = '')
* @method Element beforeElementNS(?string $namespace, string $nodeName, string $textContent = '')
* @method Element beforeXslApplyTemplates(?string $select = null, ?string $mode = null)
* @method Element beforeXslAttribute(string $name, string $textContent = '', ?string $namespace = null)
* @method Element beforeXslChoose()
* @method Element beforeXslComment(string $textContent = '')
* @method Element beforeXslCopyOf(string $select)
* @method Element beforeXslElement(string $name, ?string $namespace = null, ?string $useAttributeSets = null)
* @method Element beforeXslIf(string $test, string $textContent = '')
* @method Element beforeXslOtherwise(string $textContent = '')
* @method Element beforeXslText(string $textContent = '', ?string $disableOutputEscaping = null)
* @method Element beforeXslValueOf(string $select, ?string $disableOutputEscaping = null)
* @method Element beforeXslVariable(string $name, ?string $select = null)
* @method Element beforeXslWhen(string $test, string $textContent = '')
* @method ?Element insertAdjacentElement(string $where, DOMElement $element)
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
* @method Comment replaceWithComment(string $data)
* @method mixed replaceWithDocumentFragment(?callable $callback = null)
* @method Element replaceWithElement(string $nodeName, string $textContent = '')
* @method Element replaceWithElementNS(?string $namespace, string $nodeName, string $textContent = '')
* @method Element replaceWithXslApplyTemplates(?string $select = null, ?string $mode = null)
* @method Element replaceWithXslAttribute(string $name, string $textContent = '', ?string $namespace = null)
* @method Element replaceWithXslChoose()
* @method Element replaceWithXslComment(string $textContent = '')
* @method Element replaceWithXslCopyOf(string $select)
* @method Element replaceWithXslElement(string $name, ?string $namespace = null, ?string $useAttributeSets = null)
* @method Element replaceWithXslIf(string $test, string $textContent = '')
* @method Element replaceWithXslOtherwise(string $textContent = '')
* @method Element replaceWithXslText(string $textContent = '', ?string $disableOutputEscaping = null)
* @method Element replaceWithXslValueOf(string $select, ?string $disableOutputEscaping = null)
* @method Element replaceWithXslVariable(string $name, ?string $select = null)
* @method Element replaceWithXslWhen(string $test, string $textContent = '')
* @property ?Element $firstElementChild
* @property ?Element $lastElementChild
* @property ?Element $nextElementSibling
* @property ?Document $ownerDocument
* @property ?Element $parentElement
* @property ?Element $previousElementSibling
*/
class Element extends DOMElement
{
	use DeprecatedMethods;
	use XPathMethods;
}