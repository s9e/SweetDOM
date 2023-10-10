<?php declare(strict_types=1);

namespace s9e\SweetDOM\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use s9e\SweetDOM\CdataSection;
use s9e\SweetDOM\Document;
use s9e\SweetDOM\NodeCreator;

#[CoversClass('s9e\SweetDOM\CdataSection')]
#[CoversClass('s9e\SweetDOM\NodeTraits\MagicMethods')]
#[CoversClass('s9e\SweetDOM\NodeTraits\XPathMethods')]
class CdataSectionTest extends TestCase
{
	public function testUnknownMethod()
	{
		$this->expectException('BadMethodCallException');
		$this->expectExceptionMessage('Call to undefined method');

		$dom = new Document;
		$dom->loadXML('<x><![CDATA[..]]></x>');
		$dom->documentElement->firstChild->unknown();
	}

	#[DataProvider('getUnsupportedMethodsTests')]
	public function testUnsupportedMethods($message, string $methodName, ...$args)
	{
		$dom = new Document;
		$dom->loadXML('<x><![CDATA[..]]></x>');

		$node = $dom->documentElement->firstChild;

		$this->expectException('BadMethodCallException');
		$this->expectExceptionMessage($message);

		$node->$methodName(...$args);
	}

	public static function getUnsupportedMethodsTests(): array
	{
		return [
			[
				// DOMCdataSection does not support append()
				'Call to unsupported method ' . CdataSection::class . '::appendXslChoose() dependent of ' . CdataSection::class . '::append()',
				'appendXslChoose'
			],
			[
				'Call to unsupported method ' . CdataSection::class . '::prependElement() dependent of ' . CdataSection::class . '::prepend()',
				'prependElement', 'p'
			],
			[
				// NodeCreator does have a createSomething() method
				'Call to unsupported method ' . CdataSection::class . '::afterSomething() dependent of ' . NodeCreator::class . '::createSomething()',
				'afterSomething'
			],
		];
	}

	public function testEvaluate()
	{
		$dom = new Document;
		$dom->loadXML('<x><![CDATA[..]]><x id="z"/></x>');

		$this->assertEquals('z', $dom->firstOf('//text()')->evaluate('string(following-sibling::x/@id)'));
	}

	public function testFirstOf()
	{
		$dom = new Document;
		$dom->loadXML('<x><![CDATA[..]]><x id="z"/></x>');

		$this->assertEquals('z', $dom->firstOf('//text()/following-sibling::x')->getAttribute('id'));
	}

	public function testQuery()
	{
		$dom = new Document;
		$dom->loadXML('<x><![CDATA[..]]><x id="z"/></x>');

		$this->assertEquals('z', $dom->firstOf('//text()')->query('.//following-sibling::x')->item(0)->getAttribute('id'));
	}
}