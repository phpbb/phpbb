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

class phpbb_text_processing_strip_bbcode_test extends phpbb_test_case
{

	public function data_strip_bbcode()
	{
		return [
			['[b:20m4ill1]bold[/b:20m4ill1]', ' bold '],
			['<r><B><s>[b]</s>bold<e>[/b]</e></B></r>', ' bold '],
			['[b:20m4ill1]bo &amp; ld[/b:20m4ill1]', ' bo &amp; ld '],
			['<r><B><s>[b]</s>bo &amp; ld<e>[/b]</e></B></r>', ' bo &amp; ld ']
		];
	}

	/**
	 * @dataProvider data_strip_bbcode
	 */
	public function test_strip_bbcode($input, $expected)
	{
		$phpbb_container = $this->get_test_case_helpers()->set_s9e_services();

		strip_bbcode($input);

		$this->assertSame($expected, $input);
	}
}
