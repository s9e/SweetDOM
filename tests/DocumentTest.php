<?php declare(strict_types=1);

namespace s9e\SweetDOM\Tests;

use PHPUnit\Framework\TestCase;
use s9e\SweetDOM\Document;

/**
* @covers s9e\SweetDOM\Document
*/
class DocumentTest extends TestCase
{
	public function testEvaluate()
	{
		$dom = new Document;
		$dom->loadXML('<x foo="123"/>');

		$this->assertSame('123', $dom->evaluate('string(//x/@foo)'));
	}

	public function testEvaluateContext()
	{
		$dom = new Document;
		$dom->loadXML('<x foo="123"><x foo="456"/></x>');

		$this->assertSame('123', $dom->evaluate('string(.//@foo)'));
		$this->assertSame('456', $dom->evaluate('string(.//@foo)', $dom->documentElement->firstChild));
	}

	public function testQuery()
	{
		$dom = new Document;
		$dom->loadXML('<root><x id="123"/><x id="456"/><z/></root>');

		$nodes = $dom->query('.//x');

		$this->assertEquals(2, $nodes->length);
		$this->assertXmlStringEqualsXmlString(
			'<x id="123"/>',
			$dom->saveXML($nodes->item(0))
		);
		$this->assertXmlStringEqualsXmlString(
			'<x id="456"/>',
			$dom->saveXML($nodes->item(1))
		);
	}

	public function testQueryContext()
	{
		$dom = new Document;
		$dom->loadXML('<root><x id="123"/><x id="456"/><z><x id="789"/></z></root>');

		$nodes = $dom->query('.//x', $dom->documentElement->lastChild);

		$this->assertEquals(1, $nodes->length);
		$this->assertXmlStringEqualsXmlString(
			'<x id="789"/>',
			$dom->saveXML($nodes->item(0))
		);
	}

	public function testFirstOf()
	{
		$dom = new Document;
		$dom->loadXML('<root><x id="123"/><x id="456"/><z/></root>');

		$node = $dom->firstOf('.//x');

		$this->assertXmlStringEqualsXmlString(
			'<x id="123"/>',
			$dom->saveXML($node)
		);
	}

	public function testFirstOfContext()
	{
		$dom = new Document;
		$dom->loadXML('<root><x id="123"/><x id="456"/><z><x id="789"/></z></root>');

		$node = $dom->firstOf('.//x', $dom->documentElement->lastChild);

		$this->assertXmlStringEqualsXmlString(
			'<x id="789"/>',
			$dom->saveXML($node)
		);
	}

	public function testFirstOfNone()
	{
		$dom = new Document;
		$dom->loadXML('<root><x id="123"/></root>');

		$this->assertNull($dom->firstOf('.//z'));
	}

	/**
	* @dataProvider getCreateTestCases
	*/
	public function testCreateXsl(string $expected, string $methodName, array $args = [])
	{
		$dom = new Document;
		$dom->loadXML('<x xmlns:xsl="http://www.w3.org/1999/XSL/Transform"/>');

		$element = call_user_func_array([$dom, $methodName], $args);
		$dom->documentElement->appendChild($element);

		$this->assertXmlStringEqualsXmlString($expected, $dom->saveXML($element));
	}

	public function getCreateTestCases()
	{
		return [
			[
				'<xsl:apply-templates/>',
				'createXslApplyTemplates'
			],
			[
				'<xsl:apply-templates select="foo | bar"/>',
				'createXslApplyTemplates',
				['foo | bar']
			],
			[
				'<xsl:attribute name="foo"/>',
				'createXslAttribute',
				['foo']
			],
			[
				'<xsl:attribute name="foo" namespace="urn:foo"/>',
				'createXslAttribute',
				['foo', 'urn:foo']
			],
			[
				'<xsl:choose/>',
				'createXslChoose'
			],
			[
				'<xsl:comment/>',
				'createXslComment'
			],
			[
				'<xsl:comment>&lt;AT&amp;T&gt;</xsl:comment>',
				'createXslComment',
				['<AT&T>']
			],
			[
				'<xsl:copy-of select="@foo"/>',
				'createXslCopyOf',
				['@foo']
			],
			[
				'<xsl:if test="@foo"/>',
				'createXslIf',
				['@foo']
			],
			[
				'<xsl:otherwise/>',
				'createXslOtherwise'
			],
			[
				'<xsl:text/>',
				'createXslText'
			],
			[
				'<xsl:text>&lt;AT&amp;T&gt;</xsl:text>',
				'createXslText',
				['<AT&T>']
			],
			[
				'<xsl:value-of select="@foo"/>',
				'createXslValueOf',
				['@foo']
			],
			[
				'<xsl:variable name="foo"/>',
				'createXslVariable',
				['foo']
			],
			[
				'<xsl:variable name="foo" select="@bar"/>',
				'createXslVariable',
				['foo', '@bar']
			],
			[
				'<xsl:when test="@foo"/>',
				'createXslWhen',
				['@foo']
			],
		];
	}
}