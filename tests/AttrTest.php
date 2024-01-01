<?php declare(strict_types=1);

namespace s9e\SweetDOM\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use s9e\SweetDOM\Document;

#[CoversClass('s9e\SweetDOM\Attr')]
#[CoversClass('s9e\SweetDOM\NodeTraits\NodePolyfill')]
class AttrTest extends TestCase
{
	public function testIsEqualNode()
	{
		$dom = new Document;
		$dom->loadXML('<x><y a="" b="b" c="c"/><z a="a" b="b" c=""/></x>');

		$this->assertFalse($dom->firstOf('//y/@a')->isEqualNode($dom->firstOf('//z/@a')));
		$this->assertFalse($dom->firstOf('//y/@a')->isEqualNode($dom->firstOf('//z/@b')));
		$this->assertFalse($dom->firstOf('//y/@a')->isEqualNode($dom->firstOf('//z/@c')));
		$this->assertFalse($dom->firstOf('//y/@b')->isEqualNode($dom->firstOf('//z/@a')));
		$this->assertTrue($dom->firstOf('//y/@b')->isEqualNode($dom->firstOf('//z/@b')));
		$this->assertFalse($dom->firstOf('//y/@b')->isEqualNode($dom->firstOf('//z/@c')));
		$this->assertFalse($dom->firstOf('//y/@c')->isEqualNode($dom->firstOf('//z/@a')));
		$this->assertFalse($dom->firstOf('//y/@c')->isEqualNode($dom->firstOf('//z/@b')));
		$this->assertFalse($dom->firstOf('//y/@c')->isEqualNode($dom->firstOf('//z/@c')));
	}
}