<?php declare(strict_types=1);

/**
* @package   s9e\SweetDOM
* @copyright Copyright (c) The s9e authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\SweetDOM\NodeTraits;

use BadMethodCallException;
use DOMNode;
use function is_callable, method_exists, preg_match;

trait MagicMethods
{
	public function __call(string $name, array $arguments): DOMNode
	{
		if (!preg_match('(^(after|append|before|prepend|replaceWith)(\\w+)$)D', $name, $m))
		{
			// Use is_callable() to set $callableName
			is_callable([$this, $name], true, $callableName);

			throw new BadMethodCallException('Call to undefined method ' . $callableName . '()');
		}

		$action         = $m[1];
		$actionCallback = [$this, $action];
		$nodeCallback   = [$this->ownerDocument->nodeCreator, 'create' . $m[2]];
		if (!method_exists($this, $action))
		{
			is_callable([$this, $name],  true, $callableName);
			is_callable($actionCallback, true, $dependentName);

			throw new BadMethodCallException('Call to unsupported method ' . $callableName . '() dependent of ' . $dependentName . '()');
		}
		if (!is_callable($nodeCallback, false, $dependentName))
		{
			is_callable([$this, $name], true, $callableName);

			throw new BadMethodCallException('Call to unsupported method ' . $callableName . '() dependent of ' . $dependentName . '()');
		}

		$node = $nodeCallback(...$arguments);
		$actionCallback($node);

		return match ($action)
		{
			'after'       => $this->nextSibling,
			'append'      => $this->lastChild,
			'before'      => $this->previousSibling,
			'prepend'     => $this->firstChild,
			'replaceWith' => $node
		};
	}
}