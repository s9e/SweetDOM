<?php declare(strict_types=1);

namespace s9e\SweetDOM\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
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

	#[DataProvider('getClassMapsTestCases')]
	public function testClassMaps(string $phpVersion, array $expected): void
	{
		$dom = new class(phpVersion: $phpVersion) extends Document
		{
			public function __construct(string $version = '1.0', string $encoding = '', public string $phpVersion = '')
			{
				parent::__construct();
			}
			public function getExtendedClassMap(): array
			{
				return parent::getExtendedClassMap();
			}
			public function getExtendedNamespace(string $phpVersion): string
			{
				return parent::getExtendedNamespace($this->phpVersion);
			}
		};

		$this->assertEquals($expected, $dom->getExtendedClassMap());
	}

	public static function getClassMapsTestCases(): array
	{
		return [
			[
				'8.1.2',
				[
					'DOMAttr'             => 's9e\SweetDOM\PatchedNodes\Attr',
					'DOMCdataSection'     => 's9e\SweetDOM\PatchedNodes\CdataSection',
					'DOMComment'          => 's9e\SweetDOM\PatchedNodes\Comment',
					'DOMDocumentFragment' => 's9e\SweetDOM\PatchedNodes\DocumentFragment',
					'DOMElement'          => 's9e\SweetDOM\PatchedNodes\Element',
					'DOMText'             => 's9e\SweetDOM\PatchedNodes\Text'
				]
			],
			[
				'8.1.22',
				[
					'DOMAttr'             => 's9e\SweetDOM\PatchedNodes\Attr',
					'DOMCdataSection'     => 's9e\SweetDOM\PatchedNodes\CdataSection',
					'DOMComment'          => 's9e\SweetDOM\PatchedNodes\Comment',
					'DOMDocumentFragment' => 's9e\SweetDOM\PatchedNodes\DocumentFragment',
					'DOMElement'          => 's9e\SweetDOM\PatchedNodes\Element',
					'DOMText'             => 's9e\SweetDOM\PatchedNodes\Text'
				]
			],
			[
				'8.1.23',
				[
					'DOMAttr'             => 's9e\SweetDOM\ForwardCompatibleNodes\Attr',
					'DOMCdataSection'     => 's9e\SweetDOM\ForwardCompatibleNodes\CdataSection',
					'DOMComment'          => 's9e\SweetDOM\ForwardCompatibleNodes\Comment',
					'DOMDocumentFragment' => 's9e\SweetDOM\ForwardCompatibleNodes\DocumentFragment',
					'DOMElement'          => 's9e\SweetDOM\ForwardCompatibleNodes\Element',
					'DOMText'             => 's9e\SweetDOM\ForwardCompatibleNodes\Text'
				]
			],
			[
				'8.2.0',
				[
					'DOMAttr'             => 's9e\SweetDOM\PatchedNodes\Attr',
					'DOMCdataSection'     => 's9e\SweetDOM\PatchedNodes\CdataSection',
					'DOMComment'          => 's9e\SweetDOM\PatchedNodes\Comment',
					'DOMDocumentFragment' => 's9e\SweetDOM\PatchedNodes\DocumentFragment',
					'DOMElement'          => 's9e\SweetDOM\PatchedNodes\Element',
					'DOMText'             => 's9e\SweetDOM\PatchedNodes\Text'
				]
			],
			[
				'8.2.9',
				[
					'DOMAttr'             => 's9e\SweetDOM\PatchedNodes\Attr',
					'DOMCdataSection'     => 's9e\SweetDOM\PatchedNodes\CdataSection',
					'DOMComment'          => 's9e\SweetDOM\PatchedNodes\Comment',
					'DOMDocumentFragment' => 's9e\SweetDOM\PatchedNodes\DocumentFragment',
					'DOMElement'          => 's9e\SweetDOM\PatchedNodes\Element',
					'DOMText'             => 's9e\SweetDOM\PatchedNodes\Text'
				]
			],
			[
				'8.2.10',
				[
					'DOMAttr'             => 's9e\SweetDOM\ForwardCompatibleNodes\Attr',
					'DOMCdataSection'     => 's9e\SweetDOM\ForwardCompatibleNodes\CdataSection',
					'DOMComment'          => 's9e\SweetDOM\ForwardCompatibleNodes\Comment',
					'DOMDocumentFragment' => 's9e\SweetDOM\ForwardCompatibleNodes\DocumentFragment',
					'DOMElement'          => 's9e\SweetDOM\ForwardCompatibleNodes\Element',
					'DOMText'             => 's9e\SweetDOM\ForwardCompatibleNodes\Text'
				]
			],
			[
				'8.3.0',
				[
					'DOMAttr'             => 's9e\SweetDOM\Attr',
					'DOMCdataSection'     => 's9e\SweetDOM\CdataSection',
					'DOMComment'          => 's9e\SweetDOM\Comment',
					'DOMDocumentFragment' => 's9e\SweetDOM\DocumentFragment',
					'DOMElement'          => 's9e\SweetDOM\Element',
					'DOMText'             => 's9e\SweetDOM\Text'
				]
			],
			[
				'8.4.0',
				[
					'DOMAttr'             => 's9e\SweetDOM\Attr',
					'DOMCdataSection'     => 's9e\SweetDOM\CdataSection',
					'DOMComment'          => 's9e\SweetDOM\Comment',
					'DOMDocumentFragment' => 's9e\SweetDOM\DocumentFragment',
					'DOMElement'          => 's9e\SweetDOM\Element',
					'DOMText'             => 's9e\SweetDOM\Text'
				]
			],
		];
	}

	public function testIsEqualNode()
	{
		$dom1 = new Document;
		$dom1->loadXML('<x foo="123"/>');
		$dom2 = new Document;
		$dom2->loadXML('<x foo="123"/>');
		$this->assertTrue($dom1->isEqualNode($dom2));
		$dom2->loadXML('<x/>');
		$this->assertFalse($dom1->isEqualNode($dom2));
	}
}