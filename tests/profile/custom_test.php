<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions_profile_fields.php';

class phpbb_profile_custom_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__).'/fixtures/profile_fields.xml');
	}

	static public function dropdownFields()
	{
		return array(
			// note, there is an offset of 1 between option_id (0-indexed)
			// in the database and values (1-indexed) to avoid problems with
			// transmitting 0 in an HTML form
			//    required, value, expected
			array(1,        '0',   'FIELD_INVALID_VALUE', 'Required field should throw error for out-of-range value'),
			array(1,        '1',   'FIELD_REQUIRED',      'Required field should throw error for default value'),
			array(1,        '2',   false,                 'Required field should accept non-default value'),
			array(0,        '0',   'FIELD_INVALID_VALUE', 'Optional field should throw error for out-of-range value'),
			array(0,        '1',   false,                 'Optional field should accept default value'),
			array(0,        '2',   false,                 'Optional field should accept non-default value'),
		);
	}

	/**
	* @dataProvider dropdownFields
	*/
	public function test_dropdown_validate($field_required, $field_value, $expected, $description)
	{
		global $db;
		$db = $this->new_dbal();

		$field_data = array(
			'field_id'			=> 1,
			'lang_id'			=> 1,
			'field_novalue'		=> 1,
			'field_required'	=> $field_required,
		);

		$cp = new custom_profile;
		$result = $cp->validate_profile_field(FIELD_DROPDOWN, $field_value, $field_data);

		$this->assertEquals($expected, $result, $description);
	}
}
