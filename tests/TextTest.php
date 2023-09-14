<?php declare(strict_types=1);

namespace s9e\SweetDOM\Tests;

use DOMDocument;
use Exception;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use s9e\SweetDOM\Document;

/**
* @covers s9e\SweetDOM\Text
* @covers s9e\SweetDOM\NodeTraits\MagicMethods
* @covers s9e\SweetDOM\NodeTraits\XPathMethods
*/
class TextTest extends TestCase
{
	public function testUnknownMethod()
	{
		$this->expectException('BadMethodCallException');
		$this->expectExceptionMessage('Call to undefined method');

		$dom = new Document;
		$dom->loadXML('<x>.</x>');
		$dom->documentElement->firstChild->unknown();
	}

	#[DataProvider('getUnsupportedMethodsTests')]
	public function testUnsupportedMethods(string $methodName, ...$args)
	{
		$dom = new Document;
		$dom->loadXML('<x>.</x>');

		$node = $dom->documentElement->firstChild;

		$this->expectException('BadMethodCallException');
		$this->expectExceptionMessage('Call to undefined method ' . $node::class . '::' . preg_replace('([A-Z].*)', '', $methodName) . '()');

		$node->$methodName(...$args);
	}

	public static function getUnsupportedMethodsTests(): array
	{
		return [
			['appendXslChoose'],
			['prependElement', 'p']
		];
	}

	public function testEvaluate()
	{
		$dom = new Document;
		$dom->loadXML('<x><x id="z"/></x>');

		$this->assertEquals('z', $dom->firstOf('/x')->evaluate('string(x/@id)'));
	}

	public function testFirstOf()
	{
		$dom = new Document;
		$dom->loadXML('<x><x id="z"/></x>');

		$this->assertEquals('z', $dom->firstOf('/x')->firstOf('x')->getAttribute('id'));
	}

	public function testQuery()
	{
		$dom = new Document;
		$dom->loadXML('<x><x id="z"/></x>');

		$this->assertEquals('z', $dom->firstOf('/x')->query('.//x')->item(0)->getAttribute('id'));
	}

	public function testNSQuery()
	{
		$dom = new Document;
		$dom->loadXML('<x xmlns:x="urn:x"><y><x:x/></y></x>');

		$nodes = $dom->firstOf('//y')->query('x:x');

		$this->assertEquals(1, $nodes->length);
		$this->assertEquals(
			'<x:x/>',
			$dom->saveXML($nodes->item(0))
		);
	}
}