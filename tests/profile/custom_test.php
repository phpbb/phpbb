<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

class phpbb_profile_custom_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/profile_fields.xml');
	}

	static public function dropdown_fields()
	{
		return array(
			// note, there is an offset of 1 between option_id (0-indexed)
			// in the database and values (1-indexed) to avoid problems with
			// transmitting 0 in an HTML form
			//    required, value, expected
			array(1,        '0',   'FIELD_INVALID_VALUE-field',	'Required field should throw error for out-of-range value'),
			array(1,        '1',   'FIELD_REQUIRED-field',		'Required field should throw error for default value'),
			array(1,        '2',   false,						'Required field should accept non-default value'),
			array(0,        '0',   'FIELD_INVALID_VALUE-field', 'Optional field should throw error for out-of-range value'),
			array(0,        '1',   false,						'Optional field should accept default value'),
			array(0,        '2',   false,						'Optional field should accept non-default value'),
		);
	}

	/**
	* @dataProvider dropdown_fields
	*/
	public function test_dropdown_validate($field_required, $field_value, $expected, $description)
	{
		global $db, $table_prefix;
		$db = $this->new_dbal();

		$field_data = array(
			'field_id'			=> 1,
			'lang_id'			=> 1,
			'lang_name'			=> 'field',
			'field_novalue'		=> 1,
			'field_required'	=> $field_required,
		);
		$user = $this->getMock('\phpbb\user');
		$user->expects($this->any())
			->method('lang')
			->will($this->returnCallback(array($this, 'return_callback_implode')));

		$request = $this->getMock('\phpbb\request\request');
		$template = $this->getMock('\phpbb\template\template');

		$cp = new \phpbb\profilefields\type\type_dropdown(
			new \phpbb\profilefields\lang_helper($db, $table_prefix . 'profile_fields_lang'),
			$request,
			$template,
			$user
		);
		$result = $cp->validate_profile_field($field_value, $field_data);

		$this->assertEquals($expected, $result, $description);
	}

	public function return_callback_implode()
	{
		return implode('-', func_get_args());
	}
}
