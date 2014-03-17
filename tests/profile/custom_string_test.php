<?php
/**
*
* @package testing
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/utf/utf_tools.php';

class phpbb_profile_custom_string_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/profile_fields.xml');
	}

	static public function string_fields()
	{
		return array(
			// note, there is an offset of 1 between option_id (0-indexed)
			// in the database and values (1-indexed) to avoid problems with
			// transmitting 0 in an HTML form
			//    required, value, validation, expected, description
			array(
					1,
					'H3110',
					'[0-9]+',
					'FIELD_INVALID_CHARS_NUMBERS_ONLY-field',
					'Required field should reject characters in a numbers-only field',
			),
			array(
					1,
					'This string is too long',
					'.*',
					'FIELD_TOO_LONG-10-field',
					'Required field should reject a field too long',
			),
			array(
					0,
					'&lt;&gt;&quot;&amp;%&amp;&gt;&lt;&gt;',
					'.*',
					false,
					'Optional field should accept html entities',
			),
			array(
					1,
					'ö ä ü ß',
					'.*',
					false,
					'Required field should accept UTF-8 string',
			),
			array(
					1,
					'This ö ä string has to b',
					'.*',
					'FIELD_TOO_LONG-10-field',
					'Required field should reject an UTF-8 string which is too long',
			),
			array(
					1,
					'ö äö äö ä',
					'[\w]+',
					'FIELD_INVALID_CHARS_ALPHA_ONLY-field',
					'Required field should reject UTF-8 in alpha only field',
			),
			array(
					1,
					'Hello',
					'[\w]+',
					false,
					'Required field should accept a characters only field',
			),
		);
	}

	/**
	* @dataProvider string_fields
	*/
	public function test_string_validate($field_required, $field_value, $field_validation, $expected, $description)
	{
		$db = $this->new_dbal();

		$field_data = array(
			'field_id'          => 1,
			'lang_id'			=> 1,
			'lang_name'			=> 'field',
			'field_novalue'		=> 1,
			'field_required'    => $field_required,
			'field_maxlen'      => 10,
			'field_validation'  => $field_validation,
		);
		$user = $this->getMock('\phpbb\user');
		$user->expects($this->any())
			->method('lang')
			->will($this->returnCallback(array($this, 'return_callback_implode')));

		$request = $this->getMock('\phpbb\request\request');
		$template = $this->getMock('\phpbb\template\template');

		$cp = new \phpbb\profilefields\type\type_string(
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
