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

use phpbb\json\sanitizer as json_sanitizer;

class phpbb_json_sanitizer_test extends phpbb_test_case
{
	public function data_decode()
	{
		return [
			[false, []],
			['', []],
			['{ "name": "phpbb/phpbb-style-prosilver"}', ['name' => 'phpbb/phpbb-style-prosilver']],
			['{ "name":[[ "phpbb/phpbb-style-prosilver"}', []],
		];
	}

	/**
	 * @dataProvider data_decode
	 */
	public function test_decode_data($input, $output)
	{
		$this->assertEquals($output, json_sanitizer::decode($input));
	}
}
