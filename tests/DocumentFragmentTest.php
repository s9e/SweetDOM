<?php declare(strict_types=1);

namespace s9e\SweetDOM\Tests;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use s9e\SweetDOM\Document;

#[CoversClass('s9e\SweetDOM\DocumentFragment')]
#[CoversClass('s9e\SweetDOM\NodeTraits\MagicMethods')]
#[CoversClass('s9e\SweetDOM\NodeTraits\XPathMethods')]
class DocumentFragmentTest extends TestCase
{
	#[DataProvider('getMagicMethodsTests')]
	public function testMagicMethods(string $expected, string $methodName, array $args = [])
	{
		$dom = new Document;
		$dom->loadXML('<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform"/>');

		$fragment = $dom->createDocumentFragment();
		$fragment->append($dom->createElement('br'));
		$fragment->$methodName(...$args);
		$dom->firstChild->append($fragment);

		$this->assertXmlStringEqualsXmlString($expected, $dom->saveXML());
	}

	public static function getMagicMethodsTests()
	{
		return [
			[
				'<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
					<br/>
					<xsl:text>..</xsl:text>
				</p>',
				'appendXslText',
				['..']
			],
			[
				'<p xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
					<xsl:text>..</xsl:text>
					<br/>
				</p>',
				'prependXslText',
				['..']
			],
		];
	}

	public function testEvaluate()
	{
		$dom = new Document;
		$dom->loadXML('<x/>');

		$fragment = $dom->createDocumentFragment();
		$fragment->appendElement('z')->setAttribute('value', 'xx');

		$this->assertEquals('xx', $fragment->evaluate('string(z/@value)'));
	}

	public function testFirstOf()
	{
		$dom = new Document;
		$dom->loadXML('<x/>');

		$fragment = $dom->createDocumentFragment();
		$fragment->appendElement('x')->setAttribute('value', 'xx');

		$this->assertEquals('xx', $fragment->firstOf('x')->getAttribute('value'));
		$this->assertEquals('', $fragment->firstOf('//x')->getAttribute('value'));
		$this->assertEquals('xx', $fragment->firstOf('.//x')->getAttribute('value'));
	}

	public function testQuery()
	{
		$dom = new Document;
		$dom->loadXML('<x/>');

		$fragment = $dom->createDocumentFragment();
		$fragment->appendElement('x')->setAttribute('value', 'xx');

		$this->assertEquals('xx', $fragment->query('x')->item(0)->getAttribute('value'));
	}
}