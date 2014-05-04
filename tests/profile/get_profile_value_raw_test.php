<?php
/**
*
* @package testing
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

class phpbb_profile_get_profile_value_raw_test extends phpbb_test_case
{
	static public function get_profile_value_raw_data()
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
			array('\phpbb\profilefields\type\type_url',	'http://www.test.com/',	false,	'http://www.test.com/'),
			array('\phpbb\profilefields\type\type_url',	'http://www.test.com/',	true,	'http://www.test.com/'),
			array('\phpbb\profilefields\type\type_text',	'[b]bbcode test[/b]',	false,	'[b]bbcode test[/b]'),
			array('\phpbb\profilefields\type\type_text',	'[b]bbcode test[/b]',	true,	'[b]bbcode test[/b]'),
			/* array('\phpbb\profilefields\type\type_dropdown',	'5',	false,	'5'),
			array('\phpbb\profilefields\type\type_dropdown',	'5',	true,	'5'),
			array('\phpbb\profilefields\type\type_dropdown',	'',	false,	''),
			array('\phpbb\profilefields\type\type_dropdown',	'',	true,	''),
			array('\phpbb\profilefields\type\type_dropdown',	null,	false,	null),
			array('\phpbb\profilefields\type\type_dropdown',	null,	true,	null), */
		);
	}

	/**
	* @dataProvider get_profile_value_raw_data
	*/
	public function test_get_profile_value_raw($type, $value, $show_novalue, $expected)
	{
		$cp = new $type(
			$this->getMock('\phpbb\request\request'),
			$this->getMock('\phpbb\template\template'),
			$this->getMock('\phpbb\user')
		);

		$this->assertSame($expected, $cp->get_profile_value_raw($value, array(
			'field_type'			=> $type,
			'field_show_novalue'	=> $show_novalue,
		)));
	}
}
