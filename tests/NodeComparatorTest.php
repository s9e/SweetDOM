<?php declare(strict_types=1);

namespace s9e\SweetDOM\Tests;

use DOMDocument;
use DOMEntityReference;
use DOMNode;
use DOMXPath;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use s9e\SweetDOM\NodeComparator;

#[CoversClass('s9e\SweetDOM\NodeComparator')]
class NodeComparatorTest extends TestCase
{
	protected function assertIsEqualNode(bool $expected, DOMNode $node, DOMNode $otherNode, bool $compareNative = null): void
	{
		$this->assertSame($expected, NodeComparator::isEqualNode($node, $otherNode));
		if ($compareNative ?? version_compare(PHP_VERSION, '8.3.0', '>='))
		{
			$this->assertSame($expected, $node->isEqualNode($otherNode), 'Does not match ext/dom');
		}
	}

	#[DataProvider('getIsEqualNodeCases')]
	public function testIsEqualNode(bool $expected, string $xml1, string $xpath1, string $xml2, string $xpath2, bool $compareNative = null): void
	{
		$node      = $this->getNodeFromXML($xml1, $xpath1);
		$otherNode = $this->getNodeFromXML($xml2, $xpath2);

		$this->assertIsEqualNode($expected, $node, $otherNode, $compareNative);
	}

	protected function getNodeFromXML(string $xml, string $query): DOMNode
	{
		$dom = new DOMDocument;
		$dom->loadXML($xml);

		return (new DOMXPath($dom))->query($query)->item(0);
	}

	public static function getIsEqualNodeCases(): array
	{
		return [
			[
				true,
				'<x/>',
				'//x',
				'<x/>',
				'//x'
			],
			[
				false,
				'<x/>',
				'//x',
				'<y/>',
				'//y'
			],
			[
				false,
				'<x/>',
				'//x',
				'<X/>',
				'//X'
			],
			[
				true,
				'<x a="0"/>',
				'//@a',
				'<x a="0"/>',
				'//@a'
			],
			[
				false,
				'<x a="0"/>',
				'//@a',
				'<x a=""/>',
				'//@a'
			],
			[
				false,
				'<x a=""/>',
				'//@a',
				'<x b=""/>',
				'//@b'
			],
			[
				false,
				'<x/>',
				'//x',
				'<x xmlns="urn:x"/>',
				'//*'
			],
			[
				false,
				'<x/>',
				'//x',
				'<x xmlns:x="urn:x"/>',
				'//x'
			],
			[
				false,
				'<x xmlns:xx="urn:xx"/>',
				'//x',
				'<x xmlns:x="urn:x"/>',
				'//x'
			],
			[
				true,
				'<x xmlns:a="urn:a" xmlns:b="urn:b"/>',
				'//x',
				'<x xmlns:a="urn:a" xmlns:b="urn:b"/>',
				'//x'
			],
			[
				true,
				'<x xmlns:a="urn:a" xmlns:b="urn:b"/>',
				'//x',
				'<x xmlns:b="urn:b" xmlns:a="urn:a"/>',
				'//x',
				version_compare(PHP_VERSION, '8.3.2', '>=') && PHP_VERSION !== '8.4.0-dev (f5f44bb22ddf2390892c2a61e872c5c8bd3f5cf6)'
			],
			[
				false,
				'<x a=""/>',
				'//x',
				'<x/>',
				'//x'
			],
			[
				false,
				'<x/>',
				'//x',
				'<x a=""/>',
				'//x'
			],
			[
				false,
				'<x b=""/>',
				'//x',
				'<x a=""/>',
				'//x'
			],
			[
				false,
				'<x a="0"/>',
				'//x',
				'<x a=""/>',
				'//x'
			],
			[
				true,
				'<x a="" b=""/>',
				'//x',
				'<x a="" b=""/>',
				'//x'
			],
			[
				true,
				'<x a="" b=""/>',
				'//x',
				'<x b="" a=""/>',
				'//x',
				version_compare(PHP_VERSION, '8.3.2', '>=') && PHP_VERSION !== '8.4.0-dev (f5f44bb22ddf2390892c2a61e872c5c8bd3f5cf6)'
			],
			[
				false,
				'<x><y/></x>',
				'//y',
				'<x xmlns="urn:x"><y/></x>',
				'//*/*'
			],
			[
				true,
				'<x><y/></x>',
				'//y',
				'<x xmlns:x="urn:x"><y/></x>',
				'//*/*'
			],
			[
				true,
				'<x><y/></x>',
				'//x',
				'<x><y/></x>',
				'//x'
			],
			[
				true,
				'<x>.<y/>.</x>',
				'//x',
				'<x>.<y/>.</x>',
				'//x'
			],
			[
				true,
				'<x>.<y>.</y>.</x>',
				'//x',
				'<x>.<y>.</y>.</x>',
				'//x'
			],
			[
				true,
				'<x><y/></x>',
				'//x',
				'<x><y></y></x>',
				'//x'
			],
			[
				false,
				'<x>.<y>.</y>.</x>',
				'//x',
				'<x>.<y>_</y>.</x>',
				'//x'
			],
			[
				false,
				'<x>.<y>.</y>.</x>',
				'//x',
				'<x><y>.</y>.</x>',
				'//x'
			],
			[
				false,
				'<x>..<y/></x>',
				'//x',
				'<x>.<y/>.</x>',
				'//x'
			],
			[
				true,
				'<x><!-- x --></x>',
				'//x',
				'<x><!-- x --></x>',
				'//x'
			],
			[
				true,
				'<x><!-- x --></x>',
				'//comment()',
				'<x><!-- x --></x>',
				'//comment()'
			],
			[
				false,
				'<x><!-- x --></x>',
				'//x',
				'<x><!--x--></x>',
				'//x'
			],
			[
				true,
				'<x><?x?></x>',
				'//x',
				'<x><?x?></x>',
				'//x'
			],
			[
				false,
				'<x><?x?></x>',
				'//x',
				'<x><?xx?></x>',
				'//x'
			],
			[
				true,
				'<x><?x a="a"?></x>',
				'//x',
				'<x><?x a="a"?></x>',
				'//x'
			],
			[
				false,
				'<x><?x a="a"?></x>',
				'//x',
				'<x><?x a="a" ?></x>',
				'//x'
			],
			[
				false,
				'<x>..</x>',
				'//x',
				'<x><![CDATA[..]]></x>',
				'//x'
			],
			[
				false,
				'<x x=""/>',
				'//x',
				'<x x=""/>',
				'//@x'
			],
		];
	}

	// https://github.com/php/php-src/blob/master/ext/dom/tests/DOMNode_isEqualNode.phpt
	public function testIsEqualDocumentNode()
	{
		$this->assertIsEqualNode(true, new DOMDocument, new DOMDocument);
	}

	public function testIsEqualDocumentNodeClone()
	{
		$dom1 = new DOMDocument;
		$dom1->loadXML(<<<'EOT'
			<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd" [
				<!ENTITY bar '<bar>bartext</bar>'>
				<!ENTITY foo '<foo/>'>
				<!NOTATION myNotation SYSTEM "test.dtd">
			]>
			<html>
				<body>
					<p>...</p>
				</body>
			</html>
		EOT);
		$this->assertIsEqualNode(true, $dom1, clone $dom1);
	}

	public function testIsEqualDocumentTypeNode()
	{
		$dom1 = new DOMDocument;
		$dom1->loadXML(<<<'EOT'
			<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd" [
				<!ENTITY bar '<bar>bartext</bar>'>
				<!ENTITY foo '<foo/>'>
				<!NOTATION myNotation SYSTEM "test.dtd">
			]>
			<html>
				<body>
					<p>...</p>
				</body>
			</html>
		EOT);
		$this->assertIsEqualNode(true, $dom1->doctype, $dom1->doctype);

		$dom2 = new DOMDocument;
		$dom2->loadXML('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/x.dtd"><html/>');
		$this->assertIsEqualNode(false, $dom1->doctype, $dom2->doctype);
		$dom2->loadXML('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN2" "http://www.w3.org/TR/html4/strict.dtd"><html/>');
		$this->assertIsEqualNode(false, $dom1->doctype, $dom2->doctype);
		$dom2->loadXML('<!DOCTYPE HTML2 PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd"><html/>');
		$this->assertIsEqualNode(false, $dom1->doctype, $dom2->doctype);
		$dom2->loadXML('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd"><html/>');
		$this->assertIsEqualNode(true, $dom1->doctype, $dom2->doctype);
	}

	public function testIsEqualEntityReference()
	{
		$this->assertIsEqualNode(false, new DOMEntityReference('ref'), new DOMEntityReference('ref2'));
		$this->assertIsEqualNode(true, new DOMEntityReference('ref'), new DOMEntityReference('ref'));
	}

	public function testIsEqualEntityDeclarationNode()
	{
		$dom1 = new DOMDocument;
		$dom1->loadXML(<<<'EOT'
			<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd" [
				<!ENTITY bar '<bar>bartext</bar>'>
				<!ENTITY foo '<foo/>'>
				<!NOTATION myNotation SYSTEM "test.dtd">
			]>
			<html>
				<body>
					<p>...</p>
				</body>
			</html>
		EOT);

		$dom2 = new DOMDocument;
		$dom2->loadXML(<<<'EOT'
			<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd" [
				<!ENTITY barbar '<bar>bartext</bar>'>
				<!ENTITY foo '<foo2/>'>
				<!ENTITY bar '<bar>bartext</bar>'>
			]>
			<html>
				<body>
					<p>...</p>
				</body>
			</html>
		EOT);

		$this->assertIsEqualNode(true, $dom1->doctype->entities->getNamedItem('bar'), $dom2->doctype->entities->getNamedItem('bar'));
		$this->assertIsEqualNode(false, $dom1->doctype->entities->getNamedItem('bar'), $dom2->doctype->entities->getNamedItem('barbar'));
		$this->assertIsEqualNode(false, $dom1->doctype->entities->getNamedItem('bar'), $dom2->doctype->entities->getNamedItem('foo'));
		$this->assertIsEqualNode(false, $dom1->doctype->entities->getNamedItem('foo'), $dom2->doctype->entities->getNamedItem('bar'));
		$this->assertIsEqualNode(false, $dom1->doctype->entities->getNamedItem('foo'), $dom2->doctype->entities->getNamedItem('barbar'));
		$this->assertIsEqualNode(true, $dom1->doctype->entities->getNamedItem('foo'), $dom2->doctype->entities->getNamedItem('foo'));
	}

	public function testIsEqualEntityNotationNode()
	{
		$dom1 = new DOMDocument;
		$dom1->loadXML(<<<'EOT'
			<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd" [
				<!ENTITY bar '<bar>bartext</bar>'>
				<!ENTITY foo '<foo/>'>
				<!NOTATION myNotation SYSTEM "test.dtd">
			]>
			<html>
				<body>
					<p>...</p>
				</body>
			</html>
		EOT);

		$dom2 = new DOMDocument;
		$dom2->loadXML(<<<'EOT'
			<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd" [
				<!NOTATION myNotation SYSTEM "test.dtd">
				<!NOTATION myNotation2 SYSTEM "test2.dtd">
				<!NOTATION myNotation3 SYSTEM "test.dtd">
			]>
			<html><body><p>...</p></body></html>
		EOT);

		$this->assertIsEqualNode(true, $dom1->doctype->notations->getNamedItem('myNotation'), $dom2->doctype->notations->getNamedItem('myNotation'));
		$this->assertIsEqualNode(false, $dom1->doctype->notations->getNamedItem('myNotation'), $dom2->doctype->notations->getNamedItem('myNotation2'));
		$this->assertIsEqualNode(false, $dom1->doctype->notations->getNamedItem('myNotation'), $dom2->doctype->notations->getNamedItem('myNotation3'));
	}

	public function testIsEqualDocumentFragmentNode()
	{
		$xml = '<x><y/></x><z/>';

		$dom   = new DOMDocument;
		$frag1 = $dom->createDocumentFragment();
		$frag2 = $dom->createDocumentFragment();
		$frag3 = $dom->createDocumentFragment();

		$frag1->appendXML($xml);
		$frag2->appendXML($xml);
		$frag3->appendXML('<x/><z/>');

		$this->assertIsEqualNode(true, $frag1, $frag2);
		$this->assertIsEqualNode(false, $frag1, $frag3);
	}
}