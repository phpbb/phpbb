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
require_once dirname(__FILE__) . '/../../phpBB/includes/functions_acp.php';

class phpbb_functions_acp_validate_range_test extends phpbb_test_case
{
	protected function setUp()
	{
		parent::setUp();

		global $user;

		$user = new phpbb_mock_user();
		$user->lang = new phpbb_mock_lang();
	}

	/**
	* Data sets that don't throw an error.
	*/
	public function validate_range_data_fit()
	{
		return array(
			array(array(array('column_type' => 'BOOL', 'lang' => 'TEST', 'value' => 0))),
			array(array(array('column_type' => 'BOOL', 'lang' => 'TEST', 'value' => 1))),

			array(array(array('column_type' => 'USINT', 'lang' => 'TEST', 'value' => 0))),
			array(array(array('column_type' => 'USINT', 'lang' => 'TEST', 'value' => 65535))),
			array(array(array('column_type' => 'USINT:32:128', 'lang' => 'TEST', 'value' => 35))),

			array(array(array('column_type' => 'UINT', 'lang' => 'TEST', 'value' => 0))),
			array(array(array('column_type' => 'UINT', 'lang' => 'TEST', 'value' => (int) 0x7fffffff))),
			array(array(array('column_type' => 'UINT:32:128', 'lang' => 'TEST', 'value' => 35))),

			array(array(array('column_type' => 'INT', 'lang' => 'TEST', 'value' => (int) -2147483648))),
			array(array(array('column_type' => 'INT', 'lang' => 'TEST', 'value' => (int) 0x7fffffff))),
			array(array(array('column_type' => 'INT:-32:128', 'lang' => 'TEST', 'value' => -28))),
			array(array(array('column_type' => 'INT:-32:128', 'lang' => 'TEST', 'value' => 35))),

			array(array(array('column_type' => 'TINT', 'lang' => 'TEST', 'value' => -128))),
			array(array(array('column_type' => 'TINT', 'lang' => 'TEST', 'value' => 127))),
			array(array(array('column_type' => 'TINT:-32:64', 'lang' => 'TEST', 'value' => -16))),
			array(array(array('column_type' => 'TINT:-32:64', 'lang' => 'TEST', 'value' => 16))),

			array(array(array('column_type' => 'VCHAR', 'lang' => 'TEST', 'value' => ''))),
			array(array(array('column_type' => 'VCHAR', 'lang' => 'TEST', 'value' => str_repeat('a', 255)))),
			array(array(array('column_type' => 'VCHAR', 'lang' => 'TEST', 'value' => str_repeat("\xC3\x84", 255)))),
			array(array(array('column_type' => 'VCHAR:128', 'lang' => 'TEST', 'value' => str_repeat('a', 128)))),
			array(array(array('column_type' => 'VCHAR:128', 'lang' => 'TEST', 'value' => str_repeat("\xC3\x84", 128)))),
		);
	}

	/**
	* @dataProvider validate_range_data_fit
	*/
	public function test_validate_range_fit($test_data)
	{
		$phpbb_error = array();
		validate_range($test_data, $phpbb_error);

		$this->assertEquals(array(), $phpbb_error);
	}

	/**
	* Data sets that throw the SETTING_TOO_LOW-error.
	*/
	public function validate_range_data_too_low()
	{
		return array(
			array(array(array('column_type' => 'BOOL', 'lang' => 'TEST', 'value' => -1))),

			array(array(array('column_type' => 'USINT', 'lang' => 'TEST', 'value' => -1))),
			array(array(array('column_type' => 'USINT:32:128', 'lang' => 'TEST', 'value' => 31))),

			array(array(array('column_type' => 'UINT', 'lang' => 'TEST', 'value' => -1))),
			array(array(array('column_type' => 'UINT:32:128', 'lang' => 'TEST', 'value' => 31))),

			array(array(array('column_type' => 'INT', 'lang' => 'TEST', 'value' => ((int) -2147483648) - 1))),
			array(array(array('column_type' => 'INT:32:128', 'lang' => 'TEST', 'value' => 31))),
			array(array(array('column_type' => 'INT:-32:128', 'lang' => 'TEST', 'value' => -33))),

			array(array(array('column_type' => 'TINT', 'lang' => 'TEST', 'value' => -129))),
			array(array(array('column_type' => 'TINT:32:64', 'lang' => 'TEST', 'value' => 31))),
			array(array(array('column_type' => 'TINT:-32:64', 'lang' => 'TEST', 'value' => -33))),
		);
	}

	/**
	* @dataProvider validate_range_data_too_low
	*/
	public function test_validate_range_too_low($test_data)
	{
		$phpbb_error = array();
		validate_range($test_data, $phpbb_error);

		$this->assertEquals(array('SETTING_TOO_LOW'), $phpbb_error);
	}

	/**
	* Data sets that throw the SETTING_TOO_BIG-error.
	*/
	public function validate_range_data_too_big()
	{
		return array(
			array(array(array('column_type' => 'BOOL', 'lang' => 'TEST', 'value' => 2))),

			array(array(array('column_type' => 'USINT', 'lang' => 'TEST', 'value' => 65536))),
			array(array(array('column_type' => 'USINT:32:128', 'lang' => 'TEST', 'value' => 129))),

			array(array(array('column_type' => 'UINT', 'lang' => 'TEST', 'value' => ((int) 0x7fffffff) + 1))),
			array(array(array('column_type' => 'UINT:32:128', 'lang' => 'TEST', 'value' => 129))),

			array(array(array('column_type' => 'INT', 'lang' => 'TEST', 'value' => ((int) 0x7fffffff) + 1))),
			array(array(array('column_type' => 'INT:-32:-16', 'lang' => 'TEST', 'value' => -15))),
			array(array(array('column_type' => 'INT:-32:128', 'lang' => 'TEST', 'value' => 129))),

			array(array(array('column_type' => 'TINT', 'lang' => 'TEST', 'value' => 128))),
			array(array(array('column_type' => 'TINT:-32:-16', 'lang' => 'TEST', 'value' => -15))),
			array(array(array('column_type' => 'TINT:-32:64', 'lang' => 'TEST', 'value' => 65))),
		);
	}

	/**
	* @dataProvider validate_range_data_too_big
	*/
	public function test_validate_range_too_big($test_data)
	{
		$phpbb_error = array();
		validate_range($test_data, $phpbb_error);

		$this->assertEquals(array('SETTING_TOO_BIG'), $phpbb_error);
	}

	/**
	* Data sets that throw the SETTING_TOO_LONG-error.
	*/
	public function validate_range_data_too_long()
	{
		return array(
			array(array(array('column_type' => 'VCHAR', 'lang' => 'TEST', 'value' => str_repeat('a', 256)))),
			array(array(array('column_type' => 'VCHAR', 'lang' => 'TEST', 'value' => str_repeat("\xC3\x84", 256)))),
			array(array(array('column_type' => 'VCHAR:128', 'lang' => 'TEST', 'value' => str_repeat('a', 129)))),
			array(array(array('column_type' => 'VCHAR:128', 'lang' => 'TEST', 'value' => str_repeat("\xC3\x84", 129)))),
		);
	}

	/**
	* @dataProvider validate_range_data_too_long
	*/
	public function test_validate_range_too_long($test_data)
	{
		$phpbb_error = array();
		validate_range($test_data, $phpbb_error);

		$this->assertEquals(array('SETTING_TOO_LONG'), $phpbb_error);
	}
}
