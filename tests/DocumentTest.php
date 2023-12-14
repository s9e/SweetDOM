<?php declare(strict_types=1);

namespace s9e\SweetDOM\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use s9e\SweetDOM\Document;

#[CoversClass('s9e\SweetDOM\Document')]
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

	public function testNSQuery()
	{
		$dom = new Document;
		$dom->loadXML('<x xmlns:x="urn:x"><x:x/></x>');

		$nodes = $dom->query('//x:x');

		$this->assertEquals(1, $nodes->length);
		$this->assertEquals(
			'<x:x/>',
			$dom->saveXML($nodes->item(0))
		);
	}

	public function testQueryError()
	{
		$this->expectException('RuntimeException');
		$this->expectExceptionMessage('Invalid XPath query: Invalid expression');

		$dom = new Document;
		$dom->loadXML('<x/>');

		@$dom->query('x x');
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

	public function testFirstOfError()
	{
		$this->expectException('RuntimeException');
		$this->expectExceptionMessage('Invalid XPath query: Invalid expression');

		$dom = new Document;
		$dom->loadXML('<x/>');

		@$dom->firstOf('x x');
	}
}