<?php
/**
 *
 * This file is part of the phpBB Forum Software package.
 *
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 * For full copyright and license information, please see
 * the docs/CREDITS.txt file.
 *
 */

use PHPUnit\Framework\TestCase;

class phpbb_functions_content_get_context_test extends TestCase
{
	/**
	 * Data provider for get_context test cases.
	 *
	 * @return array
	 */
	public function data_get_context(): array
	{
		return [
			'text contains words and length greater than text' => [
				'text' => 'This is a sample text containing several words, including sample, text, and words.',
				'words' => ['sample', 'words'],
				'length' => 100,
				'expected' => 'This is a sample text containing several words, including sample, text, and words.',
			],
			'text contains words and length less than text' => [
				'text' => 'This is a sample text containing several words, including sample, text, and words.',
				'words' => ['sample', 'words'],
				'length' => 50,
				'expected' => 'This is a sample text containing several words ...',
			],
			'text does not contain words' => [
				'text' => 'This is a sample text containing several words, but none of them match the given words.',
				'words' => ['nonexistent'],
				'length' => 50,
				'expected' => 'This is a sample text containing several words ...',
			],
			'desired length equal to text length' => [
				'text' => 'Exact length text.',
				'words' => ['Exact', 'text'],
				'length' => 18,
				'expected' => 'Exact length text.',
			],
			'text with html entities' => [
				'text' => 'This is a sample text containing &amp; and &lt; and &gt; entities.',
				'words' => ['sample', 'containing'],
				'length' => 50,
				'expected' => 'This is a sample text containing &amp; and &lt; and ...',
			],
			'text with html entities and contains last word' => [
				'text' => 'This is a sample text containing &amp; and &lt; and &gt; entities.',
				'words' => ['sample', 'entities'],
				'length' => 50,
				'expected' => 'This is a sample text ... and &lt; and &gt; entities.',
			],
			'text with multiple spaces and special characters' => [
				'text' => 'This    is    a   sample   text containing    several   words.',
				'words' => ['sample', 'several'],
				'length' => 50,
				'expected' => 'This is a sample text containing several words.',
			],
			'empty text' => [
				'text' => '',
				'words' => ['sample', 'words'],
				'length' => 50,
				'expected' => '',
			],
			'empty words array' => [
				'text' => 'This is a sample text containing several words.',
				'words' => [],
				'length' => 50,
				'expected' => 'This is a sample text containing several words.',
			],
			'zero length' => [
				'text' => 'This is a sample text.',
				'words' => ['sample'],
				'length' => 0,
				'expected' => '...',
			],
			'negative length' => [
				'text' => 'This is a sample text.',
				'words' => ['sample'],
				'length' => -10,
				'expected' => '...',
			],
			'ellipses_beginning' => [
				'text' => 'foo foo foo foo foo foo foo foo bar',
				'words' => ['bar'],
				'length' => 10,
				'expected' => '... foo foo bar',
			],
			'ellipsis_end' => [
				'text' => 'bar foo foo foo foo foo foo foo foo',
				'words' => ['bar'],
				'length' => 10,
				'expected' => 'bar foo foo ...',
			],
			'ellipsis_middle' => [
				'text' => 'foo word1 foo foo foo foo foo foo foo foo foo word2 foo',
				'words' => ['word1', 'word2'],
				'length' => 10,
				'expected' => '... word1 ... word2 ...',
			],
		];
	}

	/**
	 * @dataProvider data_get_context
	 */
	public function test_get_context($text, $words, $length, $expected)
	{
		$this->assertEquals($expected, get_context($text, $words, $length));
	}

}
