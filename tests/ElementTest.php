<?php declare(strict_types=1);

namespace s9e\SweetDOM\Tests;

use PHPUnit\Framework\TestCase;
use s9e\SweetDOM\Document;

/**
* @covers s9e\SweetDOM\Element
*/
class ElementTest extends TestCase
{
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

	/**
	* @dataProvider getMagicMethodsTests
	*/
	public function testMagicMethods(string $expected, string $methodName, array $args = [])
	{
		$dom = new Document;
		$dom->loadXML('<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform"><span><br/></span></p>');

		call_user_func_array([$dom->firstOf('//span'), $methodName], $args);

		$this->assertXmlStringEqualsXmlString($expected, $dom->saveXML($dom->documentElement));
	}

	public function getMagicMethodsTests()
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

	public function testRemove()
	{
		$dom = new Document;
		$dom->loadXML('<x><y><z/></y></x>');

		$dom->firstOf('//y')->remove();

		$this->assertXmlStringEqualsXmlString(
			'<x/>',
			$dom->saveXML()
		);
	}

	public function testReplaceWith()
	{
		$dom = new Document;
		$dom->loadXML('<x><y><z/></y></x>');

		$dom->firstOf('//y')->replaceWith($dom->createElement('X'), '?');

		$this->assertXmlStringEqualsXmlString(
			'<x><X/>?</x>',
			$dom->saveXML()
		);
	}

	public function testInsertAdjacentElementError()
	{
		$this->expectException('InvalidArgumentException');

		$dom = new Document;
		$dom->loadXML('<x/>');
		$dom->documentElement->insertAdjacentElement('idk', $dom->createXslIf('@foo'));
	}

	public function testInsertAdjacentElement()
	{
		$dom = new Document;
		$dom->loadXML('<x xmlns:xsl="http://www.w3.org/1999/XSL/Transform"/>');
		$dom->documentElement->insertAdjacentElement('aFteRbeGin', $dom->createXslText('...'));

		$this->assertXmlStringEqualsXmlString(
			'<x xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
				<xsl:text>...</xsl:text>
			</x>',
			$dom->saveXML()
		);
	}

	public function testInsertAdjacentText()
	{
		$dom = new Document;
		$dom->loadXML('<x><z/></x>');
		$dom->documentElement->insertAdjacentText('afterbegin', '<AT&T>\'"');

		$this->assertXmlStringEqualsXmlString(
			'<x>&lt;AT&amp;T&gt;\'"<z/></x>',
			$dom->saveXML()
		);
	}

	/**
	* @dataProvider getInsertAdjacentXMLTests
	*/
	public function testInsertAdjacentXML($original, $position, $xml, $expected)
	{
		$dom = new Document;
		$dom->loadXML($original);

		$dom->firstOf('//x')->insertAdjacentXML($position, $xml);

		$this->assertXmlStringEqualsXmlString($expected, $dom->saveXML());
	}

	public function getInsertAdjacentXMLTests()
	{
		return [
			[
				'<root><x/></root>',
				'beforebegin',
				'<foo/><bar/>',
				'<root><foo/><bar/><x/></root>'
			],
			[
				'<root><z/><x/></root>',
				'beforebegin',
				'<foo/><bar/>',
				'<root><z/><foo/><bar/><x/></root>'
			],
			[
				'<root><x/></root>',
				'afterbegin',
				'<foo/><bar/>',
				'<root><x><foo/><bar/></x></root>'
			],
			[
				'<root><x><z/></x></root>',
				'afterbegin',
				'<foo/><bar/>',
				'<root><x><foo/><bar/><z/></x></root>'
			],
			[
				'<root><x/></root>',
				'beforeend',
				'<foo/><bar/>',
				'<root><x><foo/><bar/></x></root>'
			],
			[
				'<root><x><z/></x></root>',
				'beforeend',
				'<foo/><bar/>',
				'<root><x><z/><foo/><bar/></x></root>'
			],
			[
				'<root><x/></root>',
				'afterend',
				'<foo/><bar/>',
				'<root><x/><foo/><bar/></root>'
			],
			[
				'<root><x/><z/></root>',
				'afterend',
				'<foo/><bar/>',
				'<root><x/><foo/><bar/><z/></root>'
			],
			[
				'<root xmlns:foo="urn:foo"><x/><z/></root>',
				'afterend',
				'<foo:bar xmlns:foo="urn:foo"/>',
				'<root xmlns:foo="urn:foo"><x/><foo:bar/><z/></root>'
			],
			[
				'<root xmlns:foo="urn:foo"><x/><z/></root>',
				'afterend',
				'<foo:bar/>',
				'<root xmlns:foo="urn:foo"><x/><foo:bar/><z/></root>'
			],
			[
				'<root xmlns:xsl="http://www.w3.org/1999/XSL/Transform"><x/></root>',
				'afterend',
				'<xsl:if test="@bar">...</xsl:if>',
				'<root xmlns:xsl="http://www.w3.org/1999/XSL/Transform"><x/><xsl:if test="@bar">...</xsl:if></root>'
			],
		];
	}

	public function testAppendElementNS()
	{
		$dom = new Document;
		$dom->loadXML('<x xmlns:xsl="http://www.w3.org/1999/XSL/Transform"/>');

		$element = $dom->documentElement->appendElement('xsl:x');

		$this->assertEquals('http://www.w3.org/1999/XSL/Transform', $element->namespaceURI);
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
}