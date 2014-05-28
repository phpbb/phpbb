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

class phpbb_update_rows_avoiding_duplicates_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__).'/fixtures/bookmarks_duplicates.xml');
	}

	public static function fixture_data()
	{
		return array(
			// description
			// from array
			// to value
			// expected count with to value post update
			array(
				'trivial',
				array(1),
				10,
				1,
			),
			array(
				'no conflict',
				array(2),
				3,
				2,
			),
			array(
				'conflict',
				array(4),
				5,
				1,
			),
			array(
				'conflict and no conflict',
				array(6),
				7,
				2,
			),
		);
	}

	/**
	* @dataProvider fixture_data
	*/
	public function test_update($description, $from, $to, $expected_result_count)
	{
		$db = $this->new_dbal();

		phpbb_update_rows_avoiding_duplicates($db, BOOKMARKS_TABLE, 'topic_id', $from, $to);

		$sql = 'SELECT COUNT(*) AS remaining_rows
			FROM ' . BOOKMARKS_TABLE . '
			WHERE topic_id = ' . (int) $to;
		$result = $db->sql_query($sql);
		$result_count = $db->sql_fetchfield('remaining_rows');
		$db->sql_freeresult($result);

		$this->assertEquals($expected_result_count, $result_count);
	}
}
