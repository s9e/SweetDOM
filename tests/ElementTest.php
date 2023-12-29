<?php declare(strict_types=1);

namespace s9e\SweetDOM\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\WithoutErrorHandler;
use PHPUnit\Framework\TestCase;
use s9e\SweetDOM\Document;
use s9e\SweetDOM\Element;
use s9e\SweetDOM\NodeCreator;

#[CoversClass('s9e\SweetDOM\Element')]
#[CoversClass('s9e\SweetDOM\NodeTraits\ChildNodeForwardCompatibility')]
#[CoversClass('s9e\SweetDOM\NodeTraits\ChildNodeWorkarounds')]
#[CoversClass('s9e\SweetDOM\NodeTraits\DeprecatedMethods')]
#[CoversClass('s9e\SweetDOM\NodeTraits\MagicMethods')]
#[CoversClass('s9e\SweetDOM\NodeTraits\ParentNodePolyfill')]
#[CoversClass('s9e\SweetDOM\NodeTraits\ParentNodeWorkarounds')]
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

	public function testCustomNodeCreator()
	{
		$dom = new Document;
		$dom->loadXML('<html/>');
		$dom->nodeCreator = new class($dom) extends NodeCreator
		{
			public function createBr(): Element
			{
				return $this->ownerDocument->createElement('br');
			}
		};
		$dom->documentElement->appendBr();

		$this->assertXmlStringEqualsXmlString(
			'<html><br/></html>',
			$dom->saveXML()
		);
	}

	public function testDocumentFragmentSetup()
	{
		$dom = new Document;
		$dom->loadXML('<x/>');

		$x = $dom->firstOf('//x');
		$x->appendDocumentFragment(
			function ($fragment)
			{
				$fragment->appendElement('y');
				$fragment->appendElement('z');
			}
		);

		$this->assertXmlStringEqualsXmlString(
			'<x><y/><z/></x>',
			$dom->saveXML($x)
		);
	}

	#[DoesNotPerformAssertions]
	#[Group('workarounds')]
	public function testAfterNoParent()
	{
		$dom = new Document;
		$dom->loadXML('<x/>');
		$dom->createElement('x')->after('.');
	}

	#[Group('workarounds')]
	public function testAfterNothing()
	{
		$dom = new Document;
		$dom->loadXML('<p><span>.<br/>.</span></p>');
		$dom->firstOf('//br')->after();

		$this->assertXmlStringEqualsXmlString('<p><span>.<br/>.</span></p>', $dom->saveXML());
	}

	#[Group('workarounds')]
	public function testAfterSelf()
	{
		$dom = new Document;
		$dom->loadXML('<x><y/></x>');
		$dom->firstOf('//y')->after($dom->firstOf('//y'));

		$this->assertXmlStringEqualsXmlString('<x><y/></x>', $dom->saveXML());
	}

	#[Group('workarounds')]
	public function testAfterSelfAndOthers()
	{
		$dom = new Document;
		$dom->loadXML('<x><y/></x>');
		$dom->firstOf('//y')->after('x', $dom->firstOf('//y'), 'z');

		$this->assertXmlStringEqualsXmlString('<x>x<y/>z</x>', $dom->saveXML());
	}

	#[Group('workarounds')]
	public function testAppendNothing()
	{
		$dom = new Document;
		$dom->loadXML('<p><span>.<br/>.</span></p>');
		$dom->firstOf('//br')->append();

		$this->assertXmlStringEqualsXmlString('<p><span>.<br/>.</span></p>', $dom->saveXML());
	}

	#[Group('workarounds')]
	public function testAppendSelf()
	{
		$this->expectException('DOMException');
		$this->expectExceptionCode(DOM_HIERARCHY_REQUEST_ERR);

		$dom = new Document;
		$dom->loadXML('<x><y/></x>');
		$dom->firstOf('//y')->append($dom->firstOf('//y'));
	}

	#[Group('workarounds')]
	public function testAppendSelfAndOthers()
	{
		$this->expectException('DOMException');
		$this->expectExceptionCode(DOM_HIERARCHY_REQUEST_ERR);

		$dom = new Document;
		$dom->loadXML('<x><y/></x>');
		$dom->firstOf('//y')->append('x', $dom->firstOf('//y'), 'z');
	}

	#[Group('workarounds')]
	public function testAppendParent()
	{
		$this->expectException('DOMException');
		$this->expectExceptionCode(DOM_HIERARCHY_REQUEST_ERR);

		$dom = new Document;
		$dom->loadXML('<x><y/></x>');
		$dom->firstOf('//y')->append($dom->firstOf('//x'));
	}

	#[DoesNotPerformAssertions]
	#[Group('workarounds')]
	public function testBeforeNoParent()
	{
		$dom = new Document;
		$dom->loadXML('<x/>');
		$dom->createElement('x')->before('.');
	}

	#[Group('workarounds')]
	public function testBeforeNothing()
	{
		$dom = new Document;
		$dom->loadXML('<p><span>.<br/>.</span></p>');
		$dom->firstOf('//br')->before();

		$this->assertXmlStringEqualsXmlString('<p><span>.<br/>.</span></p>', $dom->saveXML());
	}

	#[Group('workarounds')]
	public function testBeforeSelf()
	{
		$dom = new Document;
		$dom->loadXML('<x><y/></x>');
		$dom->firstOf('//y')->before($dom->firstOf('//y'));

		$this->assertXmlStringEqualsXmlString('<x><y/></x>', $dom->saveXML());
	}

	#[Group('workarounds')]
	public function testBeforeSelfAndOthers()
	{
		$dom = new Document;
		$dom->loadXML('<x><y/></x>');
		$dom->firstOf('//y')->before('x', $dom->firstOf('//y'), 'z');

		$this->assertXmlStringEqualsXmlString('<x>x<y/>z</x>', $dom->saveXML());
	}

	#[Group('workarounds')]
	public function testPrependNothing()
	{
		$dom = new Document;
		$dom->loadXML('<p><span>.<br/>.</span></p>');
		$dom->firstOf('//br')->prepend();

		$this->assertXmlStringEqualsXmlString('<p><span>.<br/>.</span></p>', $dom->saveXML());
	}

	#[Group('workarounds')]
	public function testPrependSelf()
	{
		$this->expectException('DOMException');
		$this->expectExceptionCode(DOM_HIERARCHY_REQUEST_ERR);

		$dom = new Document;
		$dom->loadXML('<x><y/></x>');
		$dom->firstOf('//y')->prepend($dom->firstOf('//y'));
	}

	#[Group('workarounds')]
	public function testPrependSelfAndOthers()
	{
		$this->expectException('DOMException');
		$this->expectExceptionCode(DOM_HIERARCHY_REQUEST_ERR);

		$dom = new Document;
		$dom->loadXML('<x><y/></x>');
		$dom->firstOf('//y')->prepend('x', $dom->firstOf('//y'), 'z');
	}

	#[Group('workarounds')]
	public function testPrependParent()
	{
		$this->expectException('DOMException');
		$this->expectExceptionCode(DOM_HIERARCHY_REQUEST_ERR);

		$dom = new Document;
		$dom->loadXML('<x><y/></x>');
		$dom->firstOf('//y')->prepend($dom->firstOf('//x'));
	}

	#[Group('workarounds')]
	public function testReplaceWithText()
	{
		$dom = new Document;
		$dom->loadXML('<p><span>.<br/>.</span></p>');
		$dom->firstOf('//br')->replaceWith('??');

		$this->assertXmlStringEqualsXmlString('<p><span>.??.</span></p>', $dom->saveXML());
	}

	#[Group('workarounds')]
	public function testReplaceWithNothing()
	{
		$dom = new Document;
		$dom->loadXML('<p><span>.<br/>.</span></p>');
		$dom->firstOf('//br')->replaceWith();

		$this->assertXmlStringEqualsXmlString('<p><span>..</span></p>', $dom->saveXML());
	}

	#[Group('workarounds')]
	public function testReplaceWithNextSibling()
	{
		$dom = new Document;
		$dom->loadXML('<x><y/><z/></x>');
		$dom->firstOf('//y')->replaceWith($dom->firstOf('//z'));

		$this->assertXmlStringEqualsXmlString('<x><z/></x>', $dom->saveXML());
	}

	#[Group('workarounds')]
	public function testReplaceWithOnlySelf()
	{
		$dom = new Document;
		$dom->loadXML('<x><y/></x>');
		$dom->firstOf('//y')->replaceWith($dom->firstOf('//y'));

		$this->assertXmlStringEqualsXmlString('<x><y/></x>', $dom->saveXML());
	}

	#[Group('workarounds')]
	public function testReplaceWithSelfAndOthers()
	{
		$dom = new Document;
		$dom->loadXML('<x><y/></x>');
		$dom->firstOf('//y')->replaceWith('x', $dom->firstOf('//y'), 'z');

		$this->assertXmlStringEqualsXmlString('<x>x<y/>z</x>', $dom->saveXML());
	}

	#[Group('workarounds')]
	public function testReplaceWithParent()
	{
		$this->expectException('DOMException');
		$this->expectExceptionCode(DOM_HIERARCHY_REQUEST_ERR);

		$dom = new Document;
		$dom->loadXML('<x><y><z/></y></x>');
		$dom->firstOf('//z')->replaceWith($dom->firstOf('//y'));
	}

	#[Group('workarounds')]
	public function testAppendNamespace()
	{
		$dom = new Document;
		$dom->loadXML('<xsl:template xmlns:xsl="http://www.w3.org/1999/XSL/Transform"><xsl:if test="@foo"><span><xsl:attribute name="title"><xsl:value-of select="@foo"/></xsl:attribute><xsl:apply-templates/></span></xsl:if></xsl:template>');
		$dom->firstOf('//xsl:if')->append(...$dom->firstOf('//span')->childNodes);
		$xml = $dom->saveXML($dom->documentElement);

		$this->assertEquals(
			'<xsl:template xmlns:xsl="http://www.w3.org/1999/XSL/Transform"><xsl:if test="@foo"><span/><xsl:attribute name="title"><xsl:value-of select="@foo"/></xsl:attribute><xsl:apply-templates/></xsl:if></xsl:template>',
			$xml
		);
	}

	#[Group('workarounds')]
	public function testPrependNamespace()
	{
		$dom = new Document;
		$dom->loadXML('<xsl:template xmlns:xsl="http://www.w3.org/1999/XSL/Transform"><xsl:choose><xsl:when test="@foo"><span><xsl:attribute name="title"><xsl:value-of select="@foo"/></xsl:attribute><xsl:apply-templates/></span></xsl:when><xsl:otherwise><span><xsl:apply-templates/></span></xsl:otherwise></xsl:choose></xsl:template>');
		$dom->firstOf('//xsl:when')->prepend(...$dom->firstOf('//span')->childNodes);
		$xml = $dom->saveXML($dom->documentElement);

		$this->assertEquals(
			'<xsl:template xmlns:xsl="http://www.w3.org/1999/XSL/Transform"><xsl:choose><xsl:when test="@foo"><xsl:attribute name="title"><xsl:value-of select="@foo"/></xsl:attribute><xsl:apply-templates/><span/></xsl:when><xsl:otherwise><span><xsl:apply-templates/></span></xsl:otherwise></xsl:choose></xsl:template>',
			$xml
		);
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

	protected function runPolyfillTest(string $expected, string $methodName, callable $argumentsCallback): void
	{
		$dom = new Document;
		$dom->loadXML('<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform"><span><br/></span></p>');

		$dom->firstOf('//span')->$methodName(...$argumentsCallback($dom));
		$this->assertXmlStringEqualsXmlString($expected, $dom->saveXML($dom->documentElement));

	}

	#[DataProvider('getInsertAdjacentElementTests')]
	#[Group('polyfill')]
	public function testInsertAdjacentElement($position, $expected)
	{
		$this->runPolyfillTest(
			$expected,
			'insertAdjacentElement',
			fn($dom) => [$position, $dom->createElement('span', $position)]
		);
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
	public function testInsertAdjacentText($position, $data, $expected)
	{
		$this->runPolyfillTest($expected, 'insertAdjacentText', fn() => [$position, $data]);
	}

	public static function getInsertAdjacentTextTests()
	{
		return [
			[
				'afterbegin',
				'afterbegin',
				'<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
					<span>afterbegin<br/></span>
				</p>'
			],
			[
				'afterbegin',
				'after&<>\'"begin',
				'<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
					<span>after&amp;&lt;&gt;\'"begin<br/></span>
				</p>'
			],
			[
				'afterend',
				'afterend',
				'<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform"><span><br/></span>afterend</p>'
			],
			[
				'beforebegin',
				'beforebegin',
				'<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform">beforebegin<span><br/></span></p>'
			],
			[
				'beforeend',
				'beforeend',
				'<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
					<span><br/>beforeend</span>
				</p>'
			],
			[
				'BeforeEnd',
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

	#[DataProvider('getReplaceChildrenTests')]
	#[Group('polyfill')]
	public function testReplaceChildren(string $expected, array $arguments)
	{
		$this->runPolyfillTest($expected, 'replaceChildren', fn() => $arguments);
	}

	public static function getReplaceChildrenTests()
	{
		return [
			[
				'<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
					<span>...</span>
				</p>',
				['...']
			],
			[
				'<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
					<span/>
				</p>',
				[]
			],
		];
	}
}