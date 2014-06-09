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

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';

class phpbb_dbal_write_sequence_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/three_users.xml');
	}

	static public function write_sequence_data()
	{
		return array(
			array(
				'ticket/11219',
				4,
			),
		);
	}

	/**
	* @dataProvider write_sequence_data
	*/
	public function test_write_sequence($username, $expected)
	{
		$db = $this->new_dbal();

		// dbal uses cache
		global $cache;
		$cache = new phpbb_mock_cache();

		$sql = 'INSERT INTO phpbb_users ' . $db->sql_build_array('INSERT', array(
			'username'			=> $username,
			'username_clean'	=> $username,
			'user_permissions'	=> '',
			'user_sig'			=> '',
		));
		$db->sql_query($sql);

		$this->assertEquals($expected, $db->sql_nextid());

		$sql = "SELECT user_id
			FROM phpbb_users
			WHERE username_clean = '" . $db->sql_escape($username) . "'";
		$result = $db->sql_query_limit($sql, 1);

		$this->assertEquals($expected, $db->sql_fetchfield('user_id'));
	}
}
