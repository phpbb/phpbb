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

class phpbb_type_cast_helper_test extends phpbb_test_case
{
	private $type_cast_helper;

	protected function setUp(): void
	{
		$this->type_cast_helper = new \phpbb\request\type_cast_helper();
	}

	public function type_cast_helper_test_data()
	{
		return [
			[
				'eviL<3',
				'eviL&lt;3',
				'',
				true,
			],
			[
				['eviL<3'],
				['eviL&lt;3'],
				[0 => ''],
				true,
			],
			[
				" eviL<3\t\t",
				" eviL&lt;3\t\t",
				'',
				true,
				false,
			],
			[
				[" eviL<3\t\t"],
				[" eviL&lt;3\t\t"],
				[0 => ''],
				true,
				false,
			],
			// Test multiline unicode vars
			[
				"  

Тест ",
				"  

Тест ",
				'',
				true,
				false,
			],
			[
				"  

Тест ",
				"Тест",
				'',
				true,
				true,
			],
			[
				["  

Тест "],
				["  

Тест "],
				[0 => ''],
				true,
				false,
			],
			[
				["  

Тест "],
				["Тест"],
				[0 => ''],
				true,
				true,
			],
		];
	}

	/**
	 * @dataProvider type_cast_helper_test_data
	 */
	public function test_recursive_set_var($var, $expected, $default, $multibyte, $trim = true)
	{
		$this->type_cast_helper->recursive_set_var($var, $default, $multibyte, $trim);

		$this->assertEquals($expected, $var);
	}
}
