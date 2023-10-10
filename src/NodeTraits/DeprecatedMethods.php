<?php declare(strict_types=1);

/**
* @package   s9e\SweetDOM
* @copyright Copyright (c) The s9e authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\SweetDOM\NodeTraits;

use DOMException;
use const ENT_COMPAT, ENT_XML1;
use function array_flip, htmlspecialchars, preg_match, preg_match_all, preg_replace_callback, strtolower;

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

			return $this->$methodName(...$arguments);
		}
		if (preg_match('(^(ap|pre)pend(\\w+)Sibling$)i', $name, $m))
		{
			$name = ['ap' => 'after', 'pre' => 'before'][strtolower($m[1])] . $m[2];
		}

		return $this->polyfillMethodsCall($name, $arguments);
	}

	/**
	* @deprecated
	*/
	public function insertAdjacentXML(string $where, string $xml): void
	{
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