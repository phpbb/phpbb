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

class phpbb_profile_get_profile_value_test extends phpbb_test_case
{
	static public function get_profile_value_int_data()
	{
		return array(
			array('\phpbb\profilefields\type\type_int',	'10',	true,	10),
			array('\phpbb\profilefields\type\type_int',	'0',	true,	0),
			array('\phpbb\profilefields\type\type_int',	'',		true,	0),
			array('\phpbb\profilefields\type\type_int',	null,	true,	0),
			array('\phpbb\profilefields\type\type_int',	'10',	false,	10),
			array('\phpbb\profilefields\type\type_int',	'0',	false,	0),
			array('\phpbb\profilefields\type\type_int',	'',		false,	null),
			array('\phpbb\profilefields\type\type_int',	null,	false,	null),
		);
	}

	/**
	* @dataProvider get_profile_value_int_data
	*/
	public function test_get_profile_value_int($type, $value, $show_novalue, $expected)
	{
		$cp = new $type(
			$this->getMock('\phpbb\request\request'),
			$this->getMock('\phpbb\template\template'),
			$this->getMock('\phpbb\user')
		);

		$this->assertSame($expected, $cp->get_profile_value($value, array(
			'field_type'			=> $type,
			'field_show_novalue'	=> $show_novalue,
		)));
	}
}
