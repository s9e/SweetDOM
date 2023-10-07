<?php declare(strict_types=1);

namespace s9e\SweetDOM\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use s9e\SweetDOM\Document;

/**
* @covers s9e\SweetDOM\Element
* @covers s9e\SweetDOM\NodeTraits\MagicMethods
* @covers s9e\SweetDOM\NodeTraits\XPathMethods
*/
class ElementTest extends TestCase
{
	protected function assertExceptionsMatch(Exception $expected, Exception $actual)
	{
		$this->assertInstanceOf(get_class($expected), $actual);
		$this->assertEquals($expected->getMessage(), $actual->getMessage());
		$this->assertEquals($expected->getCode(), $actual->getCode());

		if ($expected->getPrevious())
		{
			$this->assertNotNull($actual->getPrevious());
			$this->assertExceptionsMatch($expected->getPrevious(), $actual->getPrevious());
		}
	}

	public function testUnknownMethod()
	{
		$this->expectException('BadMethodCallException');

		$dom = new Document;
		$dom->loadXML('<x/>');
		$dom->documentElement->unknown();
	}

	public function testUnknownXslMethod()
	{
		$this->expectException('BadMethodCallException');

		$dom = new Document;
		$dom->loadXML('<x/>');
		$dom->documentElement->appendXslUnknown();
	}

	#[DataProvider('getMagicMethodsTests')]
	public function testMagicMethods(string $expected, string $methodName, array $args = [])
	{
		$dom = new Document;
		$dom->loadXML('<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform"><span><br/></span></p>');

		$dom->firstOf('//span')->$methodName(...$args);

		$this->assertXmlStringEqualsXmlString($expected, $dom->saveXML($dom->documentElement));
	}

	public static function getMagicMethodsTests(): array
	{
		return [
			[
				'<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
					<xsl:text>beforeXslText</xsl:text>
					<span>
						<br/>
					</span>
				</p>',
				'beforeXslText',
				['beforeXslText']
			],
			[
				'<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
					<span>
						<xsl:text>prependXslText</xsl:text>
						<br/>
					</span>
				</p>',
				'prependXslText',
				['prependXslText']
			],
			[
				'<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
					<span>
						<br/>
						<xsl:text>appendXslText</xsl:text>
					</span>
				</p>',
				'appendXslText',
				['appendXslText']
			],
			[
				'<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
					<span>
						<br/>
					</span>
					<xsl:text>afterXslText</xsl:text>
				</p>',
				'afterXslText',
				['afterXslText']
			],
			[
				'<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
					<span>
						<br/>
					</span>
					<xsl:text>afterElement</xsl:text>
				</p>',
				'afterElement',
				['xsl:text', 'afterElement']
			],
			[
				'<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
					<span>
						<text>prependElement</text>
						<br/>
					</span>
				</p>',
				'prependElement',
				['text', 'prependElement']
			],
			[
				'<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
					<span>
						<br/>
						<text>appendElement</text>
					</span>
				</p>',
				'appendElement',
				['text', 'appendElement']
			],
			[
				'<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
					<span>
						<br/>
					</span>
					<text>afterElement</text>
				</p>',
				'afterElement',
				['text', 'afterElement']
			],
			[
				'<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
					<span>
						<br/>
						<text>AT&amp;amp;T</text>
					</span>
				</p>',
				'appendElement',
				['text', 'AT&amp;T']
			],
			[
				'<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
					<p>replaceWithElement</p>
				</p>',
				'replaceWithElement',
				['p', 'replaceWithElement']
			],
			[
				'<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
					<xsl:comment>text goes here</xsl:comment>
				</p>',
				'replaceWithXslComment',
				['text goes here']
			],
		];
	}

	#[DataProvider('getLegacyMethodsTests')]
	public function testLegacyMethods(string $expected, string $methodName, array $args = []): void
	{
		$this->testMagicMethods($expected, $methodName, $args);
	}

	public static function getLegacyMethodsTests(): array
	{
		return [
			[
				'<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
					<xsl:text>prependXslTextSibling</xsl:text>
					<span>
						<br/>
					</span>
				</p>',
				'prependXslTextSibling',
				['prependXslTextSibling']
			],
			[
				'<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
					<xsl:text>prependxsltextsibling</xsl:text>
					<span>
						<br/>
					</span>
				</p>',
				'prependxsltextsibling',
				['prependxsltextsibling']
			],
			[
				'<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
					<span>
						<br/>
					</span>
					<xsl:text>appendXslTextSibling</xsl:text>
				</p>',
				'appendXslTextSibling',
				['appendXslTextSibling']
			],
			[
				'<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
					<span>
						<br/>
					</span>
					<xsl:text>appendxsltextsibling</xsl:text>
				</p>',
				'appendxsltextsibling',
				['appendxsltextsibling']
			],
			[
				'<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
					<span>
						<br/>
					</span>
					<xsl:text>appendxsltextsibling</xsl:text>
				</p>',
				'appendElementSibling',
				['xsl:text', 'appendxsltextsibling']
			],
			[
				'<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
					<before>beforetext</before>
					<span>
						<br/>
					</span>
				</p>',
				'prependelementsibling',
				['before', 'beforetext']
			],
			[
				'<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
					<span>
						<text>prependElement</text>
						<br/>
					</span>
				</p>',
				'prependElement',
				['text', 'prependElement']
			],
			[
				'<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
					<span>
						<br/>
						<text>appendElement</text>
					</span>
				</p>',
				'appendElement',
				['text', 'appendElement']
			],
			[
				'<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
					<span>
						<br/>
					</span>
					<text>appendElementSibling</text>
				</p>',
				'appendElementSibling',
				['text', 'appendElementSibling']
			],
			[
				'<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
					<span>
						<br/>
						<text>AT&amp;amp;T</text>
					</span>
				</p>',
				'appendElement',
				['text', 'AT&amp;T']
			],
			[
				'<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform">before<span><br/></span></p>',
				'prependtextsibling',
				['before']
			],
			[
				'<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
					<span>prependText<br/></span>
				</p>',
				'prependText',
				['prependText']
			],
			[
				'<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
					<span><br/>appendText</span>
				</p>',
				'appendText',
				['appendText']
			],
			[
				'<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform"><span><br/></span>after</p>',
				'appendTextSibling',
				['after']
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