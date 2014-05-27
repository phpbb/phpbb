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

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/functions_user.php';

class phpbb_password_complexity_test extends phpbb_test_case
{
	public function password_complexity_test_data_positive()
	{
		return array(
			array('12345', 'PASS_TYPE_ANY'),
			array('qwerty', 'PASS_TYPE_ANY'),
			array('QWERTY', 'PASS_TYPE_ANY'),
			array('QwerTY', 'PASS_TYPE_ANY'),
			array('q$erty', 'PASS_TYPE_ANY'),
			array('qW$rty', 'PASS_TYPE_ANY'),

			array('QwerTY', 'PASS_TYPE_CASE'),
			array('QwerTY123', 'PASS_TYPE_ALPHA'),
			array('QwerTY123$&', 'PASS_TYPE_SYMBOL'),

			array('', 'PASS_TYPE_ANY'),
		);
	}

	public function password_complexity_test_data_negative()
	{
		return array(
			array('qwerty', 'PASS_TYPE_CASE'),
			array('QWERTY', 'PASS_TYPE_CASE'),
			array('123456', 'PASS_TYPE_CASE'),
			array('#$&', 'PASS_TYPE_CASE'),
			array('QTY123$', 'PASS_TYPE_CASE'),

			array('qwerty', 'PASS_TYPE_ALPHA'),
			array('QWERTY', 'PASS_TYPE_ALPHA'),
			array('123456', 'PASS_TYPE_ALPHA'),
			array('QwertY', 'PASS_TYPE_ALPHA'),
			array('qwerty123', 'PASS_TYPE_ALPHA'),
			array('QWERTY123', 'PASS_TYPE_ALPHA'),
			array('#$&', 'PASS_TYPE_ALPHA'),
			array('QTY123$', 'PASS_TYPE_ALPHA'),

			array('qwerty', 'PASS_TYPE_SYMBOL'),
			array('QWERTY', 'PASS_TYPE_SYMBOL'),
			array('123456', 'PASS_TYPE_SYMBOL'),
			array('QwertY', 'PASS_TYPE_SYMBOL'),
			array('qwerty123', 'PASS_TYPE_SYMBOL'),
			array('QWERTY123', 'PASS_TYPE_SYMBOL'),
			array('#$&', 'PASS_TYPE_SYMBOL'),
			array('qwerty123$', 'PASS_TYPE_SYMBOL'),
			array('QWERTY123$', 'PASS_TYPE_SYMBOL'),
		);
	}

	/**
	* @dataProvider password_complexity_test_data_positive
	*/
	public function test_password_complexity_positive($password, $mode)
	{
		global $config;
		$config['pass_complex'] = $mode;
		$this->assertFalse(validate_password($password));
	}

	/**
	* @dataProvider password_complexity_test_data_negative
	*/
	public function test_password_complexity_negative($password, $mode)
	{
		global $config;
		$config['pass_complex'] = $mode;
		$this->assertEquals('INVALID_CHARS', validate_password($password));
	}
}
