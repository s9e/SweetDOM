<?php declare(strict_types=1);

namespace s9e\SweetDOM\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use s9e\SweetDOM\Comment;
use s9e\SweetDOM\Document;
use s9e\SweetDOM\NodeCreator;

#[CoversClass('s9e\SweetDOM\Comment')]
#[CoversClass('s9e\SweetDOM\NodeTraits\MagicMethods')]
#[CoversClass('s9e\SweetDOM\NodeTraits\XPathMethods')]
class CommentTest extends TestCase
{
	public function testUnknownMethod()
	{
		$this->expectException('BadMethodCallException');
		$this->expectExceptionMessage('Call to undefined method');

		$dom = new Document;
		$dom->loadXML('<x><!-- .. --></x>');
		$dom->documentElement->firstChild->unknown();
	}

	#[DataProvider('getUnsupportedMethodsTests')]
	public function testUnsupportedMethods($message, string $methodName, ...$args)
	{
		$dom = new Document;
		$dom->loadXML('<x><!-- .. --></x>');

		$node = $dom->documentElement->firstChild;

		$this->expectException('BadMethodCallException');
		$this->expectExceptionMessage($message);

		$node->$methodName(...$args);
	}

	public static function getUnsupportedMethodsTests(): array
	{
		return [
			[
				// DOMComment does not support append()
				'Call to unsupported method ' . Comment::class . '::appendXslChoose() dependent of ' . Comment::class . '::append()',
				'appendXslChoose'
			],
			[
				'Call to unsupported method ' . Comment::class . '::prependElement() dependent of ' . Comment::class . '::prepend()',
				'prependElement', 'p'
			],
			[
				// NodeCreator does have a createSomething() method
				'Call to unsupported method ' . Comment::class . '::afterSomething() dependent of ' . NodeCreator::class . '::createSomething()',
				'afterSomething'
			],
		];
	}

	public function testEvaluate()
	{
		$dom = new Document;
		$dom->loadXML('<x><!-- .. --><x id="z"/></x>');

		$this->assertEquals('z', $dom->firstOf('//comment()')->evaluate('string(following-sibling::x/@id)'));
	}

	public function testFirstOf()
	{
		$dom = new Document;
		$dom->loadXML('<x><!-- .. --><x id="z"/></x>');

		$this->assertEquals('z', $dom->firstOf('//comment()/following-sibling::x')->getAttribute('id'));
	}

	public function testQuery()
	{
		$dom = new Document;
		$dom->loadXML('<x><!-- .. --><x id="z"/></x>');

		$this->assertEquals('z', $dom->firstOf('//comment()')->query('.//following-sibling::x')->item(0)->getAttribute('id'));
	}
}