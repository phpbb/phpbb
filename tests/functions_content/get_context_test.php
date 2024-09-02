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
				'expected' => 'This is a sample text.',
			],
			'negative length' => [
				'text' => 'This is a sample text.',
				'words' => ['sample'],
				'length' => -10,
				'expected' => 'This is a sample text.',
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
			'ellipsis_middle2' => [
				'text' => 'word1 foo foo foo foo foo foo foo foo foo word2',
				'words' => ['word1', 'word2'],
				'length' => 10,
				'expected' => 'word1 ... word2',
			],
		];
	}

	/**
	 * Data provider for unicode get_context test cases.
	 *
	 * @return array
	 */
	public function data_get_context_unicode(): array
	{
		return [
			'text contains words and length greater than text' => [
				'text' => 'Это пример текста, содержащего разнообразные слова, включая пример, текст и слова.',
				'words' => ['пример', 'слова'],
				'length' => 100,
				'expected' => 'Это пример текста, содержащего разнообразные слова, включая пример, текст и слова.',
			],
			'text contains words and length less than text' => [
				'text' => 'Это пример текста, содержащего разнообразные слова, включая шаблон, текст и слова.',
				'words' => ['пример', 'слова'],
				'length' => 50,
				'expected' => 'Это пример текста, содержащего разнообразные слова ...',
			],
			'text does not contain words' => [
				'text' => 'Это пример текста, содержащего разнообразные слова, но ни одно из них не совпадает с искомыми.',
				'words' => ['nonexistent'],
				'length' => 50,
				'expected' => 'Это пример текста, содержащего разнообразные слова ...',
			],
			'desired length equal to text length' => [
				'text' => 'Текст точной длины.',
				'words' => ['Текст', 'точной'],
				'length' => 19,
				'expected' => 'Текст точной длины.',
			],
			'text with html entities' => [
				'text' => 'Это пример текста, содержащего &amp; и &lt; и &gt; лексемы.',
				'words' => ['пример', 'содержащего'],
				'length' => 40,
				'expected' => 'Это пример текста, содержащего &amp; и &lt; и ...',
			],
			'text with html entities and contains last word' => [
				'text' => 'Это пример текста, содержащего &amp; и &lt; и &gt; лексемы.',
				'words' => ['пример', 'лексемы'],
				'length' => 40,
				'expected' => 'Это пример текста ... и &lt; и &gt; лексемы.',
			],
			'text with multiple spaces and special characters' => [
				'text' => 'Это    пример   текста, содержащего    разнообразные   слова.',
				'words' => ['пример', 'разнообразные'],
				'length' => 50,
				'expected' => 'Это пример текста, содержащего разнообразные слова.',
			],
			'empty text' => [
				'text' => '',
				'words' => ['пример', 'слова'],
				'length' => 50,
				'expected' => '',
			],
			'empty words array' => [
				'text' => 'Это пример текста, содержащего разнообразные слова.',
				'words' => [],
				'length' => 50,
				'expected' => 'Это пример текста, содержащего разнообразные слова.',
			],
			'zero length' => [
				'text' => 'Это пример текста.',
				'words' => ['пример'],
				'length' => 0,
				'expected' => 'Это пример текста.',
			],
			'negative length' => [
				'text' => 'Это пример текста.',
				'words' => ['sample'],
				'length' => -10,
				'expected' => 'Это пример текста.',
			],
			'ellipses_beginning' => [
				'text' => 'раз раз раз раз раз раз раз раз два',
				'words' => ['два'],
				'length' => 10,
				'expected' => '... раз раз два',
			],
			'ellipsis_end' => [
				'text' => 'два раз раз раз раз раз раз раз раз',
				'words' => ['два'],
				'length' => 10,
				'expected' => 'два раз раз ...',
			],
			'ellipsis_middle' => [
				'text' => 'раз слово1 раз раз раз раз раз раз раз раз раз слово2 раз',
				'words' => ['слово1', 'слово2'],
				'length' => 15,
				'expected' => '... слово1 ... слово2 ...',
			],
			'ellipsis_middle2' => [
				'text' => 'слово1 foo foo foo foo foo foo foo foo foo слово2',
				'words' => ['слово1', 'слово2'],
				'length' => 10,
				'expected' => 'слово1 ... слово2',
			],
			'fruits_spanish' => [
				'text' => 'Manzana,plátano,naranja,fresa,mango,uva,piña,pera,kiwi,cereza,sandía,melón,papaya,arándano,durazno',
				'words' => ['piña'],
				'length' => 20,
				'expected' => '... uva,piña,pera ...',
			]
		];
	}

	/**
	 * @dataProvider data_get_context
	 * @dataProvider data_get_context_unicode
	 */
	public function test_get_context($text, $words, $length, $expected)
	{
		$this->assertEquals($expected, get_context($text, $words, $length));
	}

}
