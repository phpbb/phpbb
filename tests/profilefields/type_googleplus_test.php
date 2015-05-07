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

require_once __DIR__ . '/../../phpBB/includes/utf/utf_tools.php';

class phpbb_profilefield_type_googleplus_test extends phpbb_test_case
{
	protected  $field;

	public function setUp()
	{
		parent::setUp();

		global $phpbb_root_path, $phpEx;

		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$lang = new \phpbb\language\language($lang_loader);
		$user = new \phpbb\user($lang, '\phpbb\datetime');
		$user->add_lang('ucp');
		$request = $this->getMock('\phpbb\request\request');
		$template = $this->getMock('\phpbb\template\template');

		$this->field = new \phpbb\profilefields\type\type_googleplus(
			$request,
			$template,
			$user
		);
	}
	public function get_profile_contact_value_data()
	{
		return array(
			array(
				'112010191010100',
				array(),
				'112010191010100',
				'Field should return a numerical Google+ ID as is',
			),
			array(
				'TestUsername',
				array(),
				'+TestUsername',
				'Field should return a string Google+ ID with a + prefixed',
			),
		);
	}

	/**
	* @dataProvider get_profile_contact_value_data
	*/
	public function test_get_profile_contact_value($value, $field_options, $expected, $description)
	{
		$default_field_options = array(
			'field_type'       => '\phpbb\profilefields\type\type_googleplus',
			'field_name' 	   => 'field',
			'field_id'	 	   => 1,
			'lang_id'	 	   => 1,
			'lang_name'        => 'field',
			'field_required'   => false,
			'field_validation' => '[\w]+',
		);
		$field_options = array_merge($default_field_options, $field_options);

		$this->assertSame($expected, $this->field->get_profile_contact_value($value, $field_options), $description);
	}

	public function data_validate_googleplus()
	{
		return array(
			array('foobar', false),
			array('2342340929304', false),
			array('foo<bar', 'The field “googleplus” has invalid characters.'),
			array('klkd.klkl', false),
			array('kl+', 'The field “googleplus” has invalid characters.'),
			array('foo=bar', 'The field “googleplus” has invalid characters.'),
			array('..foo', 'The field “googleplus” has invalid characters.'),
			array('foo..bar', 'The field “googleplus” has invalid characters.'),
		);
	}

	/**
	* @dataProvider data_validate_googleplus
	*/
	public function test_validate_googleplus($input, $expected)
	{
		$field_data = array_merge(array('lang_name' => 'googleplus'), $this->field->get_default_option_values());
		$this->assertSame($expected, $this->field->validate_string_profile_field('string', $input, $field_data));
	}
}
