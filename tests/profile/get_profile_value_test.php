<?php
/**
*
* @package testing
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions_profile_fields.php';

class phpbb_profile_get_profile_value_test extends phpbb_test_case
{
	static public function get_profile_value_int_data()
	{
		return array(
			array(FIELD_INT,	'10',	true,	10),
			array(FIELD_INT,	'0',	true,	0),
			array(FIELD_INT,	'',		true,	0),
			array(FIELD_INT,	null,	true,	0),
			array(FIELD_INT,	'10',	false,	10),
			array(FIELD_INT,	'0',	false,	0),
			array(FIELD_INT,	'',		false,	null),
			array(FIELD_INT,	null,	false,	null),
		);
	}

	/**
	* @dataProvider get_profile_value_int_data
	*/
	public function test_get_profile_value_int($type, $value, $show_novalue, $expected)
	{
		$cp = new custom_profile;
		$this->assertSame($expected, $cp->get_profile_value(array(
			'value'	=> $value,
			'data'	=> array(
				'field_type'			=> $type,
				'field_show_novalue'	=> $show_novalue,
			),
		)));
	}
}
