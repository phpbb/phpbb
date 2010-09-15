<?php
/**
*
* @package testing
* @copyright (c) 2010 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

require_once 'test_framework/framework.php';
require_once 'functions_acp/user_mock.php';
require_once '../phpBB/includes/functions_acp.php';

class phpbb_functions_acp_validate_range_test extends phpbb_test_case
{
/*		'BOOL'	=> array('php_type' => 'int', 		'min' => 0, 				'max' => 1),
		'USINT'	=> array('php_type' => 'int',		'min' => 0, 				'max' => 65535),
		'UINT'	=> array('php_type' => 'int', 		'min' => 0, 				'max' => (int) 0x7fffffff),
		'INT'	=> array('php_type' => 'int', 		'min' => (int) 0x80000000, 	'max' => (int) 0x7fffffff),
		'TINT'	=> array('php_type' => 'int',		'min' => -128,				'max' => 127),

		'VCHAR'	=> array('php_type' => 'string', 	'min' => 0, 				'max' => 255),
*/
	static public function return_string($length)
	{
		$string = '';
		for ($i = 0; $i < $length; $i++)
		{
			$string .= 'a';
		}
		return $string;
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

			array(array(array('column_type' => 'INT', 'lang' => 'TEST', 'value' => (int) 0x80000000))),
			array(array(array('column_type' => 'INT', 'lang' => 'TEST', 'value' => (int) 0x7fffffff))),
			array(array(array('column_type' => 'INT:-32:128', 'lang' => 'TEST', 'value' => -28))),
			array(array(array('column_type' => 'INT:-32:128', 'lang' => 'TEST', 'value' => 35))),

			array(array(array('column_type' => 'TINT', 'lang' => 'TEST', 'value' => -128))),
			array(array(array('column_type' => 'TINT', 'lang' => 'TEST', 'value' => 127))),
			array(array(array('column_type' => 'TINT:-32:64', 'lang' => 'TEST', 'value' => -16))),
			array(array(array('column_type' => 'TINT:-32:64', 'lang' => 'TEST', 'value' => 16))),

			array(array(array('column_type' => 'VCHAR', 'lang' => 'TEST', 'value' => ''))),
			array(array(array('column_type' => 'VCHAR', 'lang' => 'TEST', 'value' => phpbb_functions_acp_validate_range_test::return_string(255)))),
			array(array(array('column_type' => 'VCHAR:128', 'lang' => 'TEST', 'value' => phpbb_functions_acp_validate_range_test::return_string(128)))),
		);
	}

	/**
	* @dataProvider validate_range_data_fit
	*/
	public function test_validate_range_fit($test_data)
	{
		global $user;

		$user->lang = new phpbb_mock_lang();

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

			array(array(array('column_type' => 'INT', 'lang' => 'TEST', 'value' => ((int) 0x80000000) - 1))),
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
		global $user;

		$user->lang = new phpbb_mock_lang();

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
		global $user;

		$user->lang = new phpbb_mock_lang();

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
			array(array(array('column_type' => 'VCHAR', 'lang' => 'TEST', 'value' => phpbb_functions_acp_validate_range_test::return_string(256)))),
			array(array(array('column_type' => 'VCHAR:128', 'lang' => 'TEST', 'value' => phpbb_functions_acp_validate_range_test::return_string(129)))),
		);
	}

	/**
	* @dataProvider validate_range_data_too_long
	*/
	public function test_validate_range_too_long($test_data)
	{
		global $user;

		$user->lang = new phpbb_mock_lang();

		$phpbb_error = array();
		validate_range($test_data, $phpbb_error);

		$this->assertEquals(array('SETTING_TOO_LONG'), $phpbb_error);
	}
}
