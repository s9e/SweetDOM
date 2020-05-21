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

	public function testAppendXslElement()
	{
		$dom = new Document;
		$dom->loadXML('<x xmlns:xsl="http://www.w3.org/1999/XSL/Transform"><z/></x>');

		$dom->documentElement->appendXslText('foo');

		$this->assertXmlStringEqualsXmlString(
			'<x xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
				<z/>
				<xsl:text>foo</xsl:text>
			</x>',
			$dom->saveXML()
		);
	}

	public function testPrependXslElement()
	{
		$dom = new Document;
		$dom->loadXML('<x xmlns:xsl="http://www.w3.org/1999/XSL/Transform"><z/></x>');

		$dom->documentElement->prependXslComment('foo');

		$this->assertXmlStringEqualsXmlString(
			'<x xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
				<xsl:comment>foo</xsl:comment>
				<z/>
			</x>',
			$dom->saveXML()
		);
	}

	public function testRemove()
	{
		$dom = new Document;
		$dom->loadXML('<x><y><z/></y></x>');

		$node = $dom->firstOf('//y')->remove();

		$this->assertXmlStringEqualsXmlString(
			'<y><z/></y>',
			$dom->saveXML($node)
		);
		$this->assertXmlStringEqualsXmlString(
			'<x/>',
			$dom->saveXML()
		);
	}

	public function testReplace()
	{
		$dom = new Document;
		$dom->loadXML('<x><y><z/></y></x>');

		$node = $dom->firstOf('//y')->replace($dom->createElement('X'));

		$this->assertXmlStringEqualsXmlString(
			'<y><z/></y>',
			$dom->saveXML($node)
		);
		$this->assertXmlStringEqualsXmlString(
			'<x><X/></x>',
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
		$dom->documentElement->insertAdjacentElement('afterbegin', $dom->createXslText('...'));

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