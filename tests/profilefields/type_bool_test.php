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

class phpbb_profilefield_type_bool_test extends phpbb_test_case
{
	protected $cp;
	protected $field_options = array();
	protected $options = array();

	/**
	* Sets up basic test objects
	*
	* @access public
	* @return void
	*/
	protected function setUp(): void
	{
		$db = $this->createMock('phpbb\\db\\driver\\driver');

		$user = $this->createMock('\phpbb\user');
		$user->expects($this->any())
			->method('lang')
			->will($this->returnCallback(array($this, 'return_callback_implode')));

		$lang = $this->getMockBuilder('\phpbb\profilefields\lang_helper')
			->setMethods(array('get_options_lang', 'is_set', 'get'))
			->setConstructorArgs(array($db, LANG_TABLE))
			->getMock();

		$lang->expects($this->any())
			->method('get_options_lang');

		$lang->expects($this->any())
			->method('is_set')
			->will($this->returnCallback(array($this, 'is_set_callback')));

		$lang->expects($this->any())
			->method('get')
			->will($this->returnCallback(array($this, 'get')));

		$request = $this->createMock('\phpbb\request\request');
		$template = $this->createMock('\phpbb\template\template');

		$this->cp = new \phpbb\profilefields\type\type_bool(
			$lang,
			$request,
			$template,
			$user
		);

		$this->field_options = array(
			'field_type'       => '\phpbb\profilefields\type\type_bool',
			'field_name' 	   => 'field',
			'field_id'	 	   => 1,
			'lang_id'	 	   => 1,
			'lang_name'        => 'field',
			'field_required'   => false,
			'field_default_value'	=> 1,
			'field_length'			=> 1,
			'field_show_novalue'	=> null,
			'field_novalue'			=> null,
		);

		$this->options = array(
			0 => 'Yes',
			1 => 'No',
		);
	}

	public function validate_profile_field_data()
	{
		return array(
			array(
				false,
				array('field_required' => true),
				'FIELD_REQUIRED-field',
				'Field should not accept empty values for required fields',
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
				false,
				array('field_show_novalue' => true),
				'No',
				'Field should output the default value',
			),
			array(
				false,
				array('field_show_novalue' => false, 'field_length' => 2),
				null,
				'Field should not show anything for empty value',
			),
			array(
				0,
				array(),
				'Yes',
				'Field should show the set value',
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
				'Field should return correct raw value',
			),
			array(
				'',
				array('field_show_novalue' => true),
				null,
				'Field should return correct raw value',
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
				null,
				'Field should return correct raw value',
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
		return isset($this->options[$field_value]);
	}

	public function get($field_id, $lang_id, $field_value)
	{
		return $this->options[$field_value];
	}

	public function return_callback_implode()
	{
		return implode('-', func_get_args());
	}
}
