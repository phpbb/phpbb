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

class phpbb_profilefield_type_int_test extends phpbb_test_case
{
	protected $cp;
	protected $field_options;

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

		$this->cp = new \phpbb\profilefields\type\type_int(
			$request,
			$template,
			$user
		);

		$this->field_options = array(
			'field_type'     => '\phpbb\profilefields\type\type_int',
			'field_name' 	 => 'field',
			'field_id'	 	 => 1,
			'lang_id'	 	 => 1,
			'lang_name'      => 'field',
			'field_required' => false,
		);
	}

	public function profile_value_data()
	{
		return array(
			array(
				'10',
				array('field_show_novalue' => true),
				10,
				'Field should output integer value of given input',
			),
			array(
				'0',
				array('field_show_novalue' => true),
				0,
				'Field should output integer value of given input',
			),
			array(
				'',
				array('field_show_novalue' => true),
				0,
				'Field should translate empty value to 0 as integer',
				false,
			),
			array(
				null,
				array('field_show_novalue' => true),
				0,
				'Field should translate null value to 0 as integer',
			),
			array(
				'10',
				array('field_show_novalue' => false),
				10,
				'Field should output integer value of given input',
			),
			array(
				'0',
				array('field_show_novalue' => false),
				0,
				'Field should output integer value of given input',
			),
			array(
				'',
				array('field_show_novalue' => false),
				null,
				'Field should leave empty value as is',
			),
			array(
				null,
				array('field_show_novalue' => false),
				null,
				'Field should leave empty value as is',
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

	public function validate_profile_field_data()
	{
		return array(
			array(
				'15',
				array('field_minlen' => 10, 'field_maxlen' => 20, 'field_required' => true),
				false,
				'Field should accept input of correct boundaries',
			),
			array(
				'556476',
				array('field_maxlen' => 50000, 'field_required' => true),
				'FIELD_TOO_LARGE-50000-field',
				'Field should reject value of greater value than max',
			),
			array(
				'9',
				array('field_minlen' => 10, 'field_required' => true),
				'FIELD_TOO_SMALL-10-field',
				'Field should reject value which is less than defined minimum',
			),
			array(
				true,
				array('field_maxlen' => 20),
				false,
				'Field should accept correct boolean value',
			),
			array(
				'string',
				array('field_maxlen' => 10, 'field_required' => true),
				false,
				'Field should accept correct string value',
			),
			array(
				null,
				array('field_minlen' => 1, 'field_maxlen' => 10, 'field_required' => true),
				'FIELD_TOO_SMALL-1-field',
				'Field should not accept an empty value',
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

	public function profile_value_raw_data()
	{
		return array(
			array(
				'10',
				array('field_show_novalue' => true),
				10,
				'Field should return the correct raw value',
			),
			array(
				'0',
				array('field_show_novalue' => true),
				0,
				'Field should return correct raw value',
			),
			array(
				'',
				array('field_show_novalue' => true),
				0,
				'Field should return correct raw value',
			),
			array(
				'10',
				array('field_show_novalue' => false),
				10,
				'Field should return the correct raw value',
			),
			array(
				'0',
				array('field_show_novalue' => false),
				0,
				'Field should return correct raw value',
			),
			array(
				'',
				array('field_show_novalue' => false),
				null,
				'Field should return correct raw value',
			),
			array(
				'string',
				array('field_show_novalue' => false),
				0,
				'Field should return int cast of passed string'
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

	public function return_callback_implode()
	{
		return implode('-', func_get_args());
	}
}
