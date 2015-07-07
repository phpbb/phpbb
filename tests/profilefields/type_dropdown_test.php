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

class phpbb_profilefield_type_dropdown_test extends phpbb_test_case
{
	protected $cp;
	protected $field_options = array();
	protected $dropdown_options = array();

	/**
	* Sets up basic test objects
	*
	* @access public
	* @return null
	*/
	public function setUp()
	{
		global $phpbb_root_path, $phpEx;

		$user = $this->getMock('\phpbb\user', array(), array(
			new \phpbb\language\language(new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx)),
			'\phpbb\datetime'
		));
		$user->expects($this->any())
			->method('lang')
			->will($this->returnCallback(array($this, 'return_callback_implode')));

		$request = $this->getMock('\phpbb\request\request');
		$template = $this->getMock('\phpbb\template\template');

		$lang = $this->getMock('\phpbb\profilefields\lang_helper', array(), array(null, null));

		$lang->expects($this->any())
			 ->method('get_options_lang');

		$lang->expects($this->any())
			 ->method('is_set')
			 ->will($this->returnCallback(array($this, 'is_set_callback')));

		$lang->expects($this->any())
			 ->method('get')
			 ->will($this->returnCallback(array($this, 'get')));

		$this->cp = new \phpbb\profilefields\type\type_dropdown(
			$lang,
			$request,
			$template,
			$user
		);

		$this->field_options = array(
			'field_type'       => '\phpbb\profilefields\type\type_dropdown',
			'field_name' 	   => 'field',
			'field_id'	 	   => 1,
			'lang_id'	 	   => 1,
			'lang_name'        => 'field',
			'field_required'   => false,
			'field_validation' => '.*',
			'field_novalue'    => 0,
		);

		$this->dropdown_options = array(
			0 => '<No Value>',
			1 => 'Option 1',
			2 => 'Option 2',
			3 => 'Option 3',
			4 => 'Option 4',
		);
	}

	public function validate_profile_field_data()
	{
		return array(
			array(
				7,
				array(),
				'FIELD_INVALID_VALUE-field',
				'Invalid value should throw error',
			),
			array(
				true,
				array('field_required' => true),
				false,
				'Boolean would evaluate to 1 and hence correct value',
			),
			array(
				'string',
				array('field_required' => true),
				'FIELD_REQUIRED-field',
				'String should be rejected for value',
			),
			array(
				2,
				array(),
				false,
				'Valid value should not throw error'
			),
			array(
				0,
				array(),
				false,
				'Empty value should be acceptible',
			),
			array(
				0,
				array('field_required' => true),
				'FIELD_REQUIRED-field',
				'Required field should not accept empty value',
			),
		);
	}

	/**
	* @dataProvider validate_profile_field_data
	*/
	public function test_validate_profile_field($value, $field_options, $expected, $description)
	{
		$field_options = array_merge($this->field_options, $field_options);

		$result = $this->cp->validate_profile_field($value, $field_options);

		$this->assertSame($expected, $result, $description);
	}

	public function profile_value_data()
	{
		return array(
			array(
				1,
				array('field_show_novalue' => true),
				'Option 1',
				'Field should output the given value',
			),
			array(
				4,
				array('field_show_novalue' => false),
				'Option 4',
				'Field should output the given value',
			),
			array(
				'',
				array('field_show_novalue' => true),
				'<No Value>',
				'Field should output nothing for empty value',
			),
			array(
				'',
				array('field_show_novalue' => false),
				null,
				'Field should simply output null for empty value',
			),
		);
	}


	/**
	* @dataProvider profile_value_data
	*/
	public function test_get_profile_value($value, $field_options, $expected, $description)
	{
		$field_options = array_merge($this->field_options, $field_options);

		$result = $this->cp->get_profile_value($value, $field_options);

		$this->assertSame($expected, $result, $description);
	}

	public function profile_value_raw_data()
	{
		return array(
			array(
				'4',
				array('field_show_novalue' => true),
				'4',
				'Field should return the correct raw value',
			),
			array(
				'',
				array('field_show_novalue' => false),
				null,
				'Field should null for empty value without show_novalue',
			),
			array(
				'',
				array('field_show_novalue' => true),
				0,
				'Field should return 0 for empty value with show_novalue',
			),
			array(
				null,
				array('field_show_novalue' => false),
				null,
				'Field should return correct raw value',
			),
			array(
				null,
				array('field_show_novalue' => true),
				0,
				'Field should return 0 for empty value with show_novalue',
			),
		);
	}

	/**
	* @dataProvider profile_value_raw_data
	*/
	public function test_get_profile_value_raw($value, $field_options, $expected, $description)
	{
		$field_options = array_merge($this->field_options, $field_options);

		$result = $this->cp->get_profile_value_raw($value, $field_options);

		$this->assertSame($expected, $result, $description);
	}

	public function is_set_callback($field_id, $lang_id, $field_value)
	{
		return isset($this->dropdown_options[$field_value]);
	}

	public function get($field_id, $lang_id, $field_value)
	{
		return $this->dropdown_options[$field_value];
	}

	public function return_callback_implode()
	{
		return implode('-', func_get_args());
	}
}
