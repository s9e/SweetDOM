<?php declare(strict_types=1);

namespace s9e\SweetDOM\Tests;

use Exception;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use s9e\SweetDOM\Document;

/**
* @covers s9e\SweetDOM\DocumentFragment
* @covers s9e\SweetDOM\NodeTraits\MagicMethods
* @covers s9e\SweetDOM\NodeTraits\XPathMethods
*/
class DocumentFragmentTest extends TestCase
{
	#[DataProvider('getMagicMethodsTests')]
	public function testMagicMethods(string $expected, string $methodName, array $args = [])
	{
		$dom = new Document;
		$dom->loadXML('<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform"/>');

		$fragment = $dom->createDocumentFragment();
		$fragment->append($dom->createElement('br'));
		$fragment->$methodName(...$args);
		$dom->firstChild->append($fragment);

		$this->assertXmlStringEqualsXmlString($expected, $dom->saveXML());
	}

	public static function getMagicMethodsTests()
	{
		return [
			[
				'<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
					<br/>
					<xsl:text>..</xsl:text>
				</p>',
				'appendXslText',
				['..']
			],
			[
				'<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
					<xsl:text>..</xsl:text>
					<br/>
				</p>',
				'prependXslText',
				['..']
			],
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