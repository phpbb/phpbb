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

require_once dirname(__FILE__) . '/../../phpBB/includes/functions_acp.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/utf/utf_tools.php';

class phpbb_functions_acp_validate_config_vars_test extends phpbb_test_case
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

	public function data_validate_path_linux()
	{
		return array(
			array('/usr/bin', 'absolute_path', true),
			array('/usr/bin/', 'absolute_path:50:200', true),
			array('/usr/bin/which', 'absolute_path', 'DIRECTORY_NOT_DIR'),
			array('/foo/bar', 'absolute_path', 'DIRECTORY_DOES_NOT_EXIST'),
			array('C:\Windows', 'absolute_path', 'DIRECTORY_DOES_NOT_EXIST'),
			array('.', 'absolute_path', true),
			array('', 'absolute_path', true),
			array('mkdir /foo/bar', 'absolute_path', 'DIRECTORY_DOES_NOT_EXIST'),
			// Make sure above command didn't do anything
			array('/foo/bar', 'absolute_path', 'DIRECTORY_DOES_NOT_EXIST'),
		);
	}

	/**
	 * @dataProvider data_validate_path_linux
	 */
	public function test_validate_path_linux($path, $validation_type, $expected)
	{
		if (strtolower(substr(PHP_OS, 0, 5)) !== 'linux')
		{
			$this->markTestSkipped('Unable to test linux specific paths on other OS.');
		}

		$error = array();
		$config_ary = array(
			'path' => $path,
		);

		validate_config_vars(array(
				'path'	=> array('lang' => 'FOOBAR', 'validate' => $validation_type),
			),
			$config_ary,
			$error
		);

		if ($expected === true)
		{
			$this->assertEmpty($error);
		}
		else
		{
			$this->assertEquals(array($expected), $error);
		}
	}

	public function data_validate_path_windows()
	{
		return array(
			array('C:\Windows', 'absolute_path', true),
			array('C:\Windows\\', 'absolute_path:50:200', true),
			array('C:\Windows\explorer.exe', 'absolute_path', 'DIRECTORY_NOT_DIR'),
			array('C:\foobar', 'absolute_path', 'DIRECTORY_DOES_NOT_EXIST'),
			array('/usr/bin', 'absolute_path', 'DIRECTORY_DOES_NOT_EXIST'),
			array('.', 'absolute_path', true),
			array('', 'absolute_path', true),
			array('mkdir C:\Windows\foobar', 'absolute_path', 'DIRECTORY_DOES_NOT_EXIST'),
			// Make sure above command didn't do anything
			array('C:\Windows\foobar', 'absolute_path', 'DIRECTORY_DOES_NOT_EXIST'),
		);
	}

	/**
	 * @dataProvider data_validate_path_windows
	 */
	public function test_validate_path_windows($path, $validation_type, $expected)
	{
		if (strtolower(substr(PHP_OS, 0, 3)) !== 'win')
		{
			$this->markTestSkipped('Unable to test windows specific paths on other OS.');
		}

		$error = array();
		$config_ary = array(
			'path' => $path,
		);

		validate_config_vars(array(
			'path'	=> array('lang' => 'FOOBAR', 'validate' => $validation_type),
		),
			$config_ary,
			$error
		);

		if ($expected === true)
		{
			$this->assertEmpty($error);
		}
		else
		{
			$this->assertEquals(array($expected), $error);
		}
	}
}
