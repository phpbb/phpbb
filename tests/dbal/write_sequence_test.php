<?php
/**
*
* @package testing
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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

		$sql = 'INSERT INTO phpbb_users ' . $db->sql_build_array('INSERT', array(
			'username'			=> $username,
			'username_clean'	=> $username,
			'user_permissions'	=> '',
			'user_sig'			=> '',
			'user_occ'			=> '',
			'user_interests'	=> '',
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
