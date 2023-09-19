<?php declare(strict_types=1);

/**
* @package   s9e\SweetDOM
* @copyright Copyright (c) The s9e authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\SweetDOM;

use DOMText;
use s9e\SweetDOM\NodeTraits\MagicMethods;
use s9e\SweetDOM\NodeTraits\XPathMethods;

/**
* @method Comment afterComment(string $data)
* @method Comment beforeComment(string $data)
* @method Comment replaceWithComment(string $data)
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
*/
class Text extends DOMText
{
	use MagicMethods;
	use XPathMethods;
}