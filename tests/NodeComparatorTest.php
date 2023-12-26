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
	public function testIsEqualNode(bool $expected, string $xml1, string $xpath1, string $xml2, string $xpath2): void
	{
		$node      = $this->getNodeFromXML($xml1, $xpath1);
		$otherNode = $this->getNodeFromXML($xml2, $xpath2);

		$this->assertSame($expected, NodeComparator::isEqualNode($node, $otherNode));
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
				'<x xmlns="urn:x"/>',
				'//*'
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
				'//x'
			],
		];
	}
}