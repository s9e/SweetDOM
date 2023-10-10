<?php declare(strict_types=1);

namespace s9e\SweetDOM\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use s9e\SweetDOM\Document;

#[CoversClass('s9e\SweetDOM\NodeCreator')]
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

	public function testCreateIllegalComment()
	{
		$this->expectException('DOMException');
		$this->expectExceptionCode(\DOM_SYNTAX_ERR);

		$dom = new Document;
		$dom->loadXML('<x/>');
		$dom->nodeCreator->createComment("can't use -- in comments");
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
				'<xsl:apply-templates mode="text" select="//text()"/>',
				'createXslApplyTemplates',
				['mode' => 'text', 'select' => '//text()']
			],
			[
				'<xsl:apply-templates mode="text" select="//text()"/>',
				'createXslApplyTemplates',
				['select' => '//text()', 'mode' => 'text']
			],
			[
				'<xsl:apply-templates select="//text()"/>',
				'createXslApplyTemplates',
				['mode' => null, 'select' => '//text()']
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
				'<xsl:attribute name="bar" namespace="urn:bar"/>',
				'createXslAttribute',
				['namespace' => 'urn:bar', 'name' => 'bar']
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
				'<xsl:element name="hr"/>',
				'createXslElement',
				['hr']
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
				'<xsl:text disable-output-escaping="yes">&amp;custom;</xsl:text>',
				'createXslText',
				['textContent' => '&custom;', 'disableOutputEscaping' => 'yes']
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
			[
				'<!-- text goes here -->',
				'createComment',
				[' text goes here ']
			],
			[
				'<foo:bar xmlns:foo="urn:foo"/>',
				'createElementNS',
				['urn:foo', 'foo:bar']
			],
		];
	}
}