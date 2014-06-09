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

class phpbb_utf_utf8_clean_string_test extends phpbb_test_case
{
	public function cleanable_strings()
	{
		return array(
			array('MiXed CaSe', 'mixed case', 'Checking case folding'),
			array('  many   spaces   ', 'many spaces', 'Checking whitespace reduction'),
			array("we\xC2\xA1rd\xE1\x9A\x80ch\xCE\xB1r\xC2\xADacters", 'weird characters', 'Checking confusables replacement'),
		);
	}

	/**
	* @dataProvider cleanable_strings
	*/
	public function test_utf8_clean_string($input, $output, $label)
	{
		$this->assertEquals($output, utf8_clean_string($input), $label);
	}
}

