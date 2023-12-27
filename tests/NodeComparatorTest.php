<?php declare(strict_types=1);

namespace s9e\SweetDOM\Tests;

use DOMDocument;
use DOMNode;
use DOMXPath;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use s9e\SweetDOM\NodeComparator;

#[CoversClass('s9e\SweetDOM\NodeComparator')]
class NodeComparatorTest extends TestCase
{
	#[DataProvider('getIsEqualNodeCases')]
	public function testIsEqualNode(bool $expected, string $xml1, string $xpath1, string $xml2, string $xpath2, string $phpVersion = '8.3.0'): void
	{
		$node      = $this->getNodeFromXML($xml1, $xpath1);
		$otherNode = $this->getNodeFromXML($xml2, $xpath2);

		$this->assertSame($expected, NodeComparator::isEqualNode($node, $otherNode));

		if (version_compare(PHP_VERSION, $phpVersion, '>='))
		{
			$this->assertSame($expected, $node->isEqualNode($otherNode), 'Does not match ext/dom');
		}
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
				'8.3.2'
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
				'8.3.2'
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
}