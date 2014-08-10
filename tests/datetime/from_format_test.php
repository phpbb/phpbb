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

require_once dirname(__FILE__) . '/../mock/lang.php';

class phpbb_datetime_from_format_test extends phpbb_test_case
{
	public function from_format_data()
	{
		return array(
			array(
				'UTC',
				'Y-m-d',
				'2012-06-08',
			),

			array(
				'Europe/Berlin',
				'Y-m-d H:i:s',
				'2012-06-08 14:01:02',
			),
		);
	}

	/**
	* @dataProvider from_format_data()
	*/
	public function test_from_format($timezone, $format, $expected)
	{
		global $user;

		$user = new \phpbb\user('\phpbb\datetime');
		$user->timezone = new DateTimeZone($timezone);
		$user->lang['datetime'] = array(
			'TODAY'		=> 'Today',
			'TOMORROW'	=> 'Tomorrow',
			'YESTERDAY'	=> 'Yesterday',
			'AGO'		=> array(
				0		=> 'less than a minute ago',
				1		=> '%d minute ago',
				2		=> '%d minutes ago',
			),
		);

		$timestamp = $user->get_timestamp_from_format($format, $expected, new DateTimeZone($timezone));
		$this->assertEquals($expected, $user->format_date($timestamp, $format, true));
	}
}
