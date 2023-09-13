<?php declare(strict_types=1);

namespace s9e\SweetDOM\Tests;

use DOMDocument;
use Exception;
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

	public static function getMagicMethodsTests()
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

	public function testInsertAdjacentElementError()
	{
		$this->expectException('DOMException');

		$dom = new Document;
		$dom->loadXML('<x/>');
		$dom->documentElement->insertAdjacentElement('idk', $dom->nodeCreator->createXslIf('@foo'));
	}

	/**
	* @doesNotPerformAssertions
	*/
	public function testInsertAdjacentElementAfterEndNoParent()
	{
		$dom = new Document;
		$dom->loadXML('<x><y/></x>');
		$y = $dom->firstOf('//y');
		$y->remove();

		$y->insertAdjacentElement('afterend', $dom->nodeCreator->createXslIf('@foo'));
	}

	/**
	* @doesNotPerformAssertions
	*/
	public function testInsertAdjacentElementBeforeBeginNoParent()
	{
		$dom = new Document;
		$dom->loadXML('<x><y/></x>');
		$y = $dom->firstOf('//y');
		$y->remove();

		$y->insertAdjacentElement('beforebegin', $dom->nodeCreator->createXslIf('@foo'));
	}

	public function testInsertAdjacentElement()
	{
		$dom = new Document;
		$dom->loadXML('<x xmlns:xsl="http://www.w3.org/1999/XSL/Transform"/>');
		$dom->documentElement->insertAdjacentElement('aFteRbeGin', $dom->nodeCreator->createXslText('...'));

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

	public static function getInsertAdjacentXMLTests()
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

	public function testAppendText()
	{
		$dom = new Document;
		$dom->loadXML('<x/>');

		$dom->documentElement->appendText('xx')->appendData('!');

		$this->assertEquals('xx!', $dom->documentElement->textContent);
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