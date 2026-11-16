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

require_once dirname(__FILE__) . '/../../phpBB/includes/acp/acp_main.php';

class phpbb_acp_main_is_effectively_utf8_test extends phpbb_test_case
{
	public static function is_effectively_utf8_data()
	{
		return [
			[null, true],
			['', true],
			['  ', true],
			['UTF-8', true],
			['utf-8', true],
			['utf8', true],
			[' UTF-8 ', true],
			['pass', false],
			['auto', false],
			['SJIS', false],
			['ISO-8859-1', false],
		];
	}

	/**
	* @dataProvider is_effectively_utf8_data
	*/
	public function test_is_effectively_utf8($value, $expected)
	{
		$acp_main = new acp_main();
		$method = new ReflectionMethod($acp_main, 'is_effectively_utf8');
		$method->setAccessible(true);

		$this->assertSame($expected, $method->invoke($acp_main, $value));
	}
}
