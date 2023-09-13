<?php declare(strict_types=1);

namespace s9e\SweetDOM\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use s9e\SweetDOM\Document;

/**
* @covers s9e\SweetDOM\NodeCreator
*/
class NodeCreatorTest extends TestCase
{
	public function testCreateElement()
	{
		$dom = new Document;
		$dom->loadXML('<x/>');

		$element = $dom->nodeCreator->createElement('foo');
		$this->assertEquals('<foo/>', $dom->saveXML($element));
	}

	public function testCreateElementPrefixed()
	{
		$dom = new Document;
		$dom->loadXML('<x xmlns:foo="urn:foo"/>');

		$dom->documentElement->append($dom->nodeCreator->createElement('foo:bar'));
		$this->assertXmlStringEqualsXmlString('<x xmlns:foo="urn:foo"><foo:bar/></x>', $dom->saveXML());
	}

	public function testCreateElementUnknownPrefix()
	{
		$this->expectException('DOMException');
		$this->expectExceptionCode(\DOM_NAMESPACE_ERR);

		$dom = new Document;
		$dom->loadXML('<x/>');
		$dom->nodeCreator->createElement('foo:bar');
	}

	public function testCreateElementContent()
	{
		$dom = new Document;
		$dom->loadXML('<x xmlns:foo="urn:foo"/>');

		$dom->documentElement->append(
			$dom->nodeCreator->createElement('foo',     '<>&amp;"\''),
			$dom->nodeCreator->createElement('foo:bar', '<>&amp;"\'')
		);
		$this->assertXmlStringEqualsXmlString(
			'<x xmlns:foo="urn:foo">
				    <foo>&lt;&gt;&amp;amp;"\'</foo>
				<foo:bar>&lt;&gt;&amp;amp;"\'</foo:bar>
			</x>',
			$dom->saveXML()
		);
	}

	#[DataProvider('getCreateTestXslCases')]
	public function testCreateXsl(string $expected, string $methodName, array $args = [])
	{
		$dom = new Document;
		$dom->loadXML('<x xmlns:xsl="http://www.w3.org/1999/XSL/Transform"/>');

		$element = $dom->nodeCreator->$methodName(...$args);
		$dom->documentElement->append($element);

		$this->assertEquals($expected, $dom->saveXML($element));
	}

	public static function getCreateTestXslCases()
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
				'<xsl:attribute name="foo">&lt;bar&gt; &amp;amp;</xsl:attribute>',
				'createXslAttribute',
				['foo', '<bar> &amp;']
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
				'<xsl:if test="@foo">&lt;X&gt;</xsl:if>',
				'createXslIf',
				['@foo', '<X>']
			],
			[
				'<xsl:otherwise/>',
				'createXslOtherwise'
			],
			[
				'<xsl:otherwise>&lt;X&gt;</xsl:otherwise>',
				'createXslOtherwise',
				['<X>']
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
				'<xsl:value-of select="\'&amp;amp;\'"/>',
				'createXslValueOf',
				["'&amp;'"]
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
			[
				'<xsl:when test="@foo">bar</xsl:when>',
				'createXslWhen',
				['@foo', 'bar']
			],
		];
	}
}