<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
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
			//    novalue, required, value, expected
			array(1,       1,        '0',   'FIELD_INVALID_VALUE'),
			array(1,       1,        '1',   'FIELD_REQUIRED'),
			array(1,       1,        '2',   false),
			array(1,       0,        '0',   'FIELD_INVALID_VALUE'),
			array(1,       0,        '1',   false),
			array(1,       0,        '2',   false),
		);
	}

	/**
	* @dataProvider dropdownFields
	*/
	public function test_dropdown_validate($field_novalue, $field_required, $field_value, $expected)
	{
		global $db;
		$db = $this->new_dbal();

		$field_data = array(
			'field_id'			=> 1,
			'lang_id'			=> 1,
			'field_novalue'		=> $field_novalue,
			'field_required'	=> $field_required,
		);

		$cp = new custom_profile;
		$result = $cp->validate_profile_field(FIELD_DROPDOWN, &$field_value, $field_data);

		$this->assertEquals($expected, $result);
	}
}
