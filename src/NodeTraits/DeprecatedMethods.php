<?php declare(strict_types=1);

/**
* @package   s9e\SweetDOM
* @copyright Copyright (c) The s9e authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\SweetDOM\NodeTraits;

use const ENT_COMPAT, ENT_XML1, E_USER_DEPRECATED;
use function array_flip, htmlspecialchars, preg_match, preg_match_all, preg_replace_callback, strtolower, trigger_error;

/**
* @method mixed polyfillMethodsCall(string $name, array $arguments)
*/
trait DeprecatedMethods
{
	use PolyfillMethods
	{
		PolyfillMethods::__call as polyfillMethodsCall;
	}

	public function __call(string $name, array $arguments)
	{
		if (preg_match('(^(ap|pre)pendText(Sibling|)$)i', $name, $m))
		{
			$methodName = [
				'ap'         => 'append',
				'pre'        => 'prepend',
				'apsibling'  => 'after',
				'presibling' => 'before'
			][strtolower($m[1] . $m[2])];

			trigger_error('Deprecated: ' . $name . '() calls should be replaced with ' . $methodName . '(). See https://github.com/s9e/SweetDOM/blob/master/UPGRADING.md#from-2x-to-30', E_USER_DEPRECATED);

			return $this->$methodName(...$arguments);
		}
		if (preg_match('(^(ap|pre)pend(\\w+)Sibling$)i', $name, $m))
		{
			$methodName = ['ap' => 'after', 'pre' => 'before'][strtolower($m[1])] . $m[2];

			trigger_error('Deprecated: ' . $name . '() calls should be replaced with ' . $methodName . '(). See https://github.com/s9e/SweetDOM/blob/master/UPGRADING.md#from-2x-to-30', E_USER_DEPRECATED);

			$name = $methodName;

		}

		return $this->polyfillMethodsCall($name, $arguments);
	}

	/**
	* @deprecated
	*/
	public function insertAdjacentXML(string $where, string $xml): void
	{
		trigger_error('Deprecated: insertAdjacentXML() is deprecated. See https://github.com/s9e/SweetDOM/blob/master/UPGRADING.md#from-2x-to-30', E_USER_DEPRECATED);

		$fragment = $this->ownerDocument->createDocumentFragment();
		$fragment->appendXML($this->addMissingNamespaceDeclarations($xml));

		$this->insertAdjacentNode($where, $fragment);
	}

	/**
	* Add namespace declarations that may be missing in given XML
	*
	* @param  string $xml Original XML
	* @return string      Modified XML
	*/
	protected function addMissingNamespaceDeclarations(string $xml): string
	{
		preg_match_all('(xmlns:\\K[-\\w]++(?==))', $xml, $m);
		$prefixes = array_flip($m[0]);

		return preg_replace_callback(
			'(<([-\\w]++):[^>]*?\\K\\s*/?>)',
			function ($m) use ($prefixes)
			{
				$return = $m[0];
				$prefix = $m[1];
				if (!isset($prefixes[$prefix]))
				{
					$nsURI  = $this->lookupNamespaceURI($prefix);
					$return = ' xmlns:' . $prefix . '="' . htmlspecialchars($nsURI, ENT_COMPAT | ENT_XML1) . '"' . $return;
				}

				return $return;
			},
			$xml
		);
	}
}