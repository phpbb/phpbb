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

use phpbb\filesystem\helper as filesystem_helper;

class phpbb_filesystem_helper_is_absolute_test extends phpbb_test_case
{

	protected function setUp(): void
	{
		parent::setUp();
	}

	static public function is_absolute_data()
	{
		// Empty
		yield ['', false];

		// Absolute unix style
		yield ['/etc/phpbb', true];
		// Unix does not support \ so that is not an absolute path
		yield ['\etc\phpbb', false];

		// Absolute windows style
		yield ['c:\windows', true];
		yield ['C:\Windows', true];
		yield ['c:/windows', true];
		yield ['C:/Windows', true];

		// Executable
		yield ['etc/phpbb', false];
		yield ['explorer.exe', false];

		// Relative subdir
		yield ['Windows\System32', false];
		yield ['Windows\System32\explorer.exe', false];
		yield ['Windows/System32', false];
		yield ['Windows/System32/explorer.exe', false];

		// Relative updir
		yield ['..\Windows\System32', false];
		yield ['..\Windows\System32\explorer.exe', false];
		yield ['../Windows/System32', false];
		yield ['../Windows/System32/explorer.exe', false];
	}

	/**
	 * @dataProvider is_absolute_data
	 */
	public function test_is_absolute($path, $expected)
	{
		$this->assertEquals($expected, filesystem_helper::is_absolute_path($path));
	}
}
