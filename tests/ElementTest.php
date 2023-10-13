<?php declare(strict_types=1);

namespace s9e\SweetDOM\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\WithoutErrorHandler;
use PHPUnit\Framework\TestCase;
use s9e\SweetDOM\Document;

#[CoversClass('s9e\SweetDOM\Element')]
#[CoversClass('s9e\SweetDOM\NodeTraits\DeprecatedMethods')]
#[CoversClass('s9e\SweetDOM\NodeTraits\MagicMethods')]
#[CoversClass('s9e\SweetDOM\NodeTraits\XPathMethods')]
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

	#[DataProvider('getDeprecatedMethodsTests')]
	#[Group('deprecated')]
	#[WithoutErrorHandler()]
	public function testDeprecatedMethods(string $expected, string $methodName, array $args = [], string $newMethodName = null): void
	{
		$actualError = '';
		$expectedError = 'Deprecated: ' . $methodName . '() calls should be replaced with ' . $newMethodName . '(). See https://github.com/s9e/SweetDOM/blob/master/UPGRADING.md#from-2x-to-30';

		set_error_handler(
			function (int $errno, string $errstr) use (&$actualError)
			{
				$actualError = $errstr;
			},
			E_USER_DEPRECATED
		);
		$this->testMagicMethods($expected, $methodName, $args);
		restore_error_handler();
		$this->assertEquals($expectedError, $actualError);
	}

	public static function getDeprecatedMethodsTests(): array
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
				['prependXslTextSibling'],
				'beforeXslText'
			],
			[
				'<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
					<xsl:text>prependxsltextsibling</xsl:text>
					<span>
						<br/>
					</span>
				</p>',
				'prependxsltextsibling',
				['prependxsltextsibling'],
				'beforexsltext'
			],
			[
				'<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
					<span>
						<br/>
					</span>
					<xsl:text>appendXslTextSibling</xsl:text>
				</p>',
				'appendXslTextSibling',
				['appendXslTextSibling'],
				'afterXslText'
			],
			[
				'<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
					<span>
						<br/>
					</span>
					<xsl:text>appendxsltextsibling</xsl:text>
				</p>',
				'appendxsltextsibling',
				['appendxsltextsibling'],
				'afterxsltext'
			],
			[
				'<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
					<span>
						<br/>
					</span>
					<xsl:text>appendxsltextsibling</xsl:text>
				</p>',
				'appendElementSibling',
				['xsl:text', 'appendxsltextsibling'],
				'afterElement'
			],
			[
				'<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
					<before>beforetext</before>
					<span>
						<br/>
					</span>
				</p>',
				'prependelementsibling',
				['before', 'beforetext'],
				'beforeelement'
			],
			[
				'<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
					<span>
						<br/>
					</span>
					<text>appendElementSibling</text>
				</p>',
				'appendElementSibling',
				['text', 'appendElementSibling'],
				'afterElement'
			],
			[
				'<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform">before<span><br/></span></p>',
				'prependtextsibling',
				['before'],
				'before'
			],
			[
				'<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
					<span>prependText<br/></span>
				</p>',
				'prependText',
				['prependText'],
				'prepend'
			],
			[
				'<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
					<span><br/>appendText</span>
				</p>',
				'appendText',
				['appendText'],
				'append'
			],
			[
				'<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform"><span><br/></span>after</p>',
				'appendTextSibling',
				['after'],
				'after'
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

	#[DataProvider('getInsertAdjacentElementTests')]
	#[Group('polyfill')]
	public function testInsertAdjacentElement($position, $expected)
	{
		$dom = new Document;
		$dom->loadXML('<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform"><span><br/></span></p>');

		$element = $dom->createElement('span', $position);

		$dom->firstOf('//span')->__call('insertAdjacentElement', [$position, $element]);
		$this->assertXmlStringEqualsXmlString($expected, $dom->saveXML($dom->documentElement));
	}

	public static function getInsertAdjacentElementTests()
	{
		return [
			[
				'afterbegin',
				'<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
					<span>
						<span>afterbegin</span>
						<br/>
					</span>
				</p>'
			],
			[
				'afterend',
				'<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
					<span>
						<br/>
					</span>
					<span>afterend</span>
				</p>'
			],
			[
				'beforebegin',
				'<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
					<span>beforebegin</span>
					<span>
						<br/>
					</span>
				</p>'
			],
			[
				'beforeend',
				'<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
					<span>
						<br/>
						<span>beforeend</span>
					</span>
				</p>'
			],
			[
				'BeforeEnd',
				'<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
					<span>
						<br/>
						<span>BeforeEnd</span>
					</span>
				</p>'
			],
		];
	}

	#[DataProvider('getInsertAdjacentTextTests')]
	#[Group('polyfill')]
	public function testInsertAdjacentText($position, $expected)
	{
		$dom = new Document;
		$dom->loadXML('<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform"><span><br/></span></p>');

		$dom->firstOf('//span')->__call('insertAdjacentText', [$position, $position]);
		$this->assertXmlStringEqualsXmlString($expected, $dom->saveXML($dom->documentElement));
	}

	public static function getInsertAdjacentTextTests()
	{
		return [
			[
				'afterbegin',
				'<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
					<span>afterbegin<br/></span>
				</p>'
			],
			[
				'afterend',
				'<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform"><span><br/></span>afterend</p>'
			],
			[
				'beforebegin',
				'<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform">beforebegin<span><br/></span></p>'
			],
			[
				'beforeend',
				'<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
					<span><br/>beforeend</span>
				</p>'
			],
			[
				'BeforeEnd',
				'<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
					<span><br/>BeforeEnd</span>
				</p>'
			],
		];
	}

	#[DataProvider('getInsertAdjacentXMLTests')]
	#[Group('deprecated')]
	#[WithoutErrorHandler()]
	public function testInsertAdjacentXML($original, $position, $xml, $expected)
	{
		$actualError   = '';
		$expectedError = 'Deprecated: insertAdjacentXML() is deprecated. See https://github.com/s9e/SweetDOM/blob/master/UPGRADING.md#from-2x-to-30';

		set_error_handler(
			function (int $errno, string $errstr) use (&$actualError)
			{
				$actualError = $errstr;
			},
			E_USER_DEPRECATED
		);
		$dom = new Document;
		$dom->loadXML($original);
		$dom->firstOf('//x')->insertAdjacentXML($position, $xml);

		$this->assertXmlStringEqualsXmlString($expected, $dom->saveXML());

		restore_error_handler();
		$this->assertEquals($expectedError, $actualError);
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
}