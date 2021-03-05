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

require_once __DIR__ . '/../../phpBB/includes/functions_acp.php';

class phpbb_functions_acp_validate_config_vars_test extends phpbb_test_case
{
	protected function setUp(): void
	{
		parent::setUp();

		global $language, $user;

		$user = new phpbb_mock_user();
		$user->lang = new phpbb_mock_lang();
		$language = $user->lang;
	}

	/**
	* Data sets that don't throw an error.
	*/
	public function validate_config_vars_fit_data()
	{
		return array(
			array(
				array(
					'test_bool'				=> array('lang' => 'TEST_BOOL',			'validate' => 'bool'),
					'test_string'			=> array('lang' => 'TEST_STRING',		'validate' => 'string'),
					'test_string'			=> array('lang' => 'TEST_STRING',		'validate' => 'string'),
					'test_string_128'		=> array('lang' => 'TEST_STRING_128',	'validate' => 'string:128'),
					'test_string_128'		=> array('lang' => 'TEST_STRING_128',	'validate' => 'string:128'),
					'test_string_32_64'		=> array('lang' => 'TEST_STRING_32_64',	'validate' => 'string:32:64'),
					'test_string_32_64'		=> array('lang' => 'TEST_STRING_32_64',	'validate' => 'string:32:64'),
					'test_int'				=> array('lang' => 'TEST_INT',			'validate' => 'int'),
					'test_int_32'			=> array('lang' => 'TEST_INT',			'validate' => 'int:32'),
					'test_int_32_64'		=> array('lang' => 'TEST_INT',			'validate' => 'int:32:64'),
					'test_lang'				=> array('lang' => 'TEST_LANG',			'validate' => 'lang'),
					'test_url'				=> array('lang' => 'TEST_URL',			'validate' => 'url'),
					/*
					'test_sp'				=> array('lang' => 'TEST_SP',			'validate' => 'script_path'),
					'test_rpath'			=> array('lang' => 'TEST_RPATH',		'validate' => 'rpath'),
					'test_rwpath'			=> array('lang' => 'TEST_RWPATH',		'validate' => 'rwpath'),
					'test_path'				=> array('lang' => 'TEST_PATH',			'validate' => 'path'),
					'test_wpath'			=> array('lang' => 'TEST_WPATH',		'validate' => 'wpath'),
					*/
				),
				array(
					'test_bool'			=> true,
					'test_string'		=> str_repeat('a', 255),
					'test_string'		=> str_repeat("\xC3\x84", 255),
					'test_string_128'	=> str_repeat('a', 128),
					'test_string_128'	=> str_repeat("\xC3\x84", 128),
					'test_string_32_64'	=> str_repeat('a', 48),
					'test_string_32_64'	=> str_repeat("\xC3\x84", 48),
					'test_int'			=> 128,
					'test_int_32'		=> 32,
					'test_int_32_64'	=> 48,
					'test_lang'			=> 'en',
					'test_url'			=> 'http://foobar.com',
				),
			),
		);
	}

	/**
	* @dataProvider validate_config_vars_fit_data
	*/
	public function test_validate_config_vars_fit($test_data, $cfg_array)
	{
		$phpbb_error = array();
		validate_config_vars($test_data, $cfg_array, $phpbb_error);

		$this->assertEquals(array(), $phpbb_error);
	}

	/**
	* Data sets that throw the error.
	*/
	public function validate_config_vars_error_data()
	{
		return array(
			array(
				array('test_string_32_64'		=> array('lang' => 'TEST_STRING_32_64',	'validate' => 'string:32:64')),
				array('test_string_32_64'	=> str_repeat('a', 20)),
				array('SETTING_TOO_SHORT'),
			),
			array(
				array('test_string_32_64'		=> array('lang' => 'TEST_STRING_32_64',	'validate' => 'string:32:64')),
				array('test_string_32_64'	=> str_repeat("\xC3\x84", 20)),
				array('SETTING_TOO_SHORT'),
			),
			array(
				array('test_string'		=> array('lang' => 'TEST_STRING',	'validate' => 'string')),
				array('test_string'		=> str_repeat('a', 256)),
				array('SETTING_TOO_LONG'),
			),
			array(
				array('test_string'		=> array('lang' => 'TEST_STRING',	'validate' => 'string')),
				array('test_string'		=> str_repeat("\xC3\x84", 256)),
				array('SETTING_TOO_LONG'),
			),
			array(
				array('test_string_32_64'	=> array('lang' => 'TEST_STRING_32_64',	'validate' => 'string:32:64')),
				array('test_string_32_64'	=> str_repeat('a', 65)),
				array('SETTING_TOO_LONG'),
			),
			array(
				array('test_string_32_64'	=> array('lang' => 'TEST_STRING_32_64',	'validate' => 'string:32:64')),
				array('test_string_32_64'	=> str_repeat("\xC3\x84", 65)),
				array('SETTING_TOO_LONG'),
			),

			array(
				array('test_int_32'		=> array('lang' => 'TEST_INT',			'validate' => 'int:32')),
				array('test_int_32'		=> 31),
				array('SETTING_TOO_LOW'),
			),
			array(
				array('test_int_32_64'	=> array('lang' => 'TEST_INT',			'validate' => 'int:32:64')),
				array('test_int_32_64'	=> 31),
				array('SETTING_TOO_LOW'),
			),
			array(
				array('test_int_32_64'	=> array('lang' => 'TEST_INT',			'validate' => 'int:32:64')),
				array('test_int_32_64'	=> 65),
				array('SETTING_TOO_BIG'),
			),
			array(
				array(
					'test_int_min'	=> array('lang' => 'TEST_INT_MIN',		'validate' => 'int:32:64'),
					'test_int_max'	=> array('lang' => 'TEST_INT_MAX',		'validate' => 'int:32:64'),
				),
				array(
					'test_int_min'	=> 52,
					'test_int_max'	=> 48,
				),
				array('SETTING_TOO_LOW'),
			),
			array(
				array('test_lang'		=> array('lang' => 'TEST_LANG',			'validate' => 'lang')),
				array('test_lang'		=> 'this_is_no_language'),
				array('WRONG_DATA_LANG'),
			),
			array(
				array('test_url'		=> array('lang' => 'TEST_URL',			'validate' => 'url')),
				array('test_url'		=> 'javascript://foobar.com'),
				array('URL_INVALID TEST_URL'),
			),
		);
	}

	/**
	* @dataProvider validate_config_vars_error_data
	*/
	public function test_validate_config_vars_error($test_data, $cfg_array, $expected)
	{
		$phpbb_error = array();
		validate_config_vars($test_data, $cfg_array, $phpbb_error);

		$this->assertEquals($expected, $phpbb_error);
	}
}
