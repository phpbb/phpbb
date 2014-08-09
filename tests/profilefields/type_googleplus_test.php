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

class phpbb_profilefield_type_googleplus_test extends phpbb_test_case
{
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
		$user = new \phpbb\user(new \phpbb\config\config(array()));
		$request = $this->getMock('\phpbb\request\request');
		$template = $this->getMock('\phpbb\template\template');

		$field = new \phpbb\profilefields\type\type_googleplus(
			$request,
			$template,
			$user
		);

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

		$this->assertSame($expected, $field->get_profile_contact_value($value, $field_options), $description);
	}
}
