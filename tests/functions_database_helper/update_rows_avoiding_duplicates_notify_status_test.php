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

require_once dirname(__FILE__) . '/../../phpBB/includes/functions_database_helper.php';

class phpbb_update_rows_avoiding_duplicates_notify_status_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__).'/fixtures/topics_watch_duplicates.xml');
	}

	public static function fixture_data()
	{
		return array(
			// description
			// from array
			// to value
			// expected count with to value post update
			// expected notify_status values
			array(
				'trivial',
				array(1),
				1000,
				1,
				1,
			),
			array(
				'no conflict',
				array(2),
				3,
				2,
				1,
			),
			array(
				'conflict, same notify status',
				array(4),
				5,
				1,
				1,
			),
			array(
				'conflict, notify status 0 into 1',
				array(6),
				7,
				1,
				0,
			),
			array(
				'conflict, notify status 1 into 0',
				array(8),
				9,
				1,
				0,
			),
			array(
				'conflict and no conflict',
				array(10),
				11,
				2,
				0,
			),
		);
	}

	/**
	* @dataProvider fixture_data
	*/
	public function test_update($description, $from, $to, $expected_result_count, $expected_notify_status)
	{
		$db = $this->new_dbal();

		phpbb_update_rows_avoiding_duplicates_notify_status($db, TOPICS_WATCH_TABLE, 'topic_id', $from, $to);

		$sql = 'SELECT COUNT(*) AS remaining_rows
			FROM ' . TOPICS_WATCH_TABLE . '
			WHERE topic_id = ' . (int) $to;
		$result = $db->sql_query($sql);
		$result_count = $db->sql_fetchfield('remaining_rows');
		$db->sql_freeresult($result);

		$this->assertEquals($expected_result_count, $result_count);

		// user id of 1 is the user being updated
		$sql = 'SELECT notify_status
			FROM ' . TOPICS_WATCH_TABLE . '
			WHERE topic_id = ' . (int) $to . '
			AND user_id = 1';
		$result = $db->sql_query($sql);
		$notify_status = $db->sql_fetchfield('notify_status');
		$db->sql_freeresult($result);

		$this->assertEquals($expected_notify_status, $notify_status);
	}
}
