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

require_once dirname(__FILE__) . '/../../phpBB/includes/utf/utf_tools.php';

class phpbb_utf_utf8_wordwrap_test extends phpbb_test_case
{
	public function test_utf8_wordwrap_ascii()
	{
		// if the input is all ascii it should work exactly like php's wordwrap

		$text = 'The quick brown fox jumped over the lazy dog.';

		$php_wordwrap = wordwrap($text, 20);
		$phpbb_utf8_wordwrap = utf8_wordwrap($text, 20);
		$this->assertEquals($php_wordwrap, $phpbb_utf8_wordwrap, "Checking ASCII standard behaviour with length 20");

		$php_wordwrap = wordwrap($text, 30, "<br />\n");
		$phpbb_utf8_wordwrap = utf8_wordwrap($text, 30, "<br />\n");
		$this->assertEquals($php_wordwrap, $phpbb_utf8_wordwrap, "Checking ASCII special break string with length 30");

		$text = 'A very long woooooooooooord.';

		$php_wordwrap = wordwrap($text, 8, "\n");
		$phpbb_utf8_wordwrap = utf8_wordwrap($text, 8, "\n");
		$this->assertEquals($php_wordwrap, $phpbb_utf8_wordwrap, 'Checking ASCII not cutting long words');

		$php_wordwrap = wordwrap($text, 8, "\n", true);
		$phpbb_utf8_wordwrap = utf8_wordwrap($text, 8, "\n", true);
		$this->assertEquals($php_wordwrap, $phpbb_utf8_wordwrap, 'Checking ASCII cutting long words');
	}

	/**
	* Helper function that generates meaningless greek text
	*/
	private function turn_into_greek($string)
	{
		$greek_chars = array("\xCE\x90", "\xCE\x91", "\xCE\x92", "\xCE\x93", "\xCE\x94", "\xCE\x95", "\xCE\x96", "\xCE\x97", "\xCE\x98", "\xCE\x99");

		$greek = '';
		for ($i = 0, $n = strlen($string); $i < $n; $i++)
		{
			// replace each number with the character from the array
			if (ctype_digit($string[$i]))
			{
				$greek .= $greek_chars[(int) $string[$i]];
			}
			else
			{
				$greek .= $string[$i];
			}
		}

		return $greek;
	}

	public function test_utf8_wordwrap_utf8()
	{
		$text = "0123456 0123 012345 01234";
		$greek = $this->turn_into_greek($text);

		$expected = $this->turn_into_greek(wordwrap($text, 10));
		$phpbb_utf8_wordwrap = utf8_wordwrap($greek, 10);
		$this->assertEquals($expected, $phpbb_utf8_wordwrap, 'Checking UTF-8 standard behaviour with length 10');
	}

	public function test_utf8_wordwrap_utf8_cut()
	{
		$text = "0123456 0123 012345 01234";
		$greek = $this->turn_into_greek($text);

		$expected = $this->turn_into_greek(wordwrap($text, 5, "\n", true));
		$phpbb_utf8_wordwrap = utf8_wordwrap($greek, 5, "\n", true);
		$this->assertEquals($expected, $phpbb_utf8_wordwrap, 'Checking UTF-8 cutting long words');
	}
}

