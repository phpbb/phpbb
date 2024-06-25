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

class phpbb_dbal_cross_join_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__.'/fixtures/massmail_crossjoin.xml');
	}

	public function test_cross_join()
	{
		$db = $this->new_dbal();

		// http://tracker.phpbb.com/browse/PHPBB3-10296
		// Test CROSS JOIN with INNER JOIN
		// Failed on Postgres, MSSQL and Oracle
		$db->sql_return_on_error(true);

		$sql_ary = array(
			'SELECT'	=> 'u.username',
			'FROM'		=> array(
				'phpbb_users'		=> 'u',
				'phpbb_user_group'	=> 'ug',
			),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(
						'phpbb_bans'	=> 'b',
					),
					'ON'	=> 'b.ban_item = ' . $db->cast_expr_to_string('u.user_id'),
				),
			),
			'WHERE'		=> 'ug.group_id = 1
				AND u.user_id = ug.user_id
				AND b.ban_id IS NULL',
			'ORDER_BY'	=> 'u.username',
		);
		$sql = $db->sql_build_query('SELECT', $sql_ary);
		$result = $db->sql_query($sql);

		$db->sql_return_on_error(false);

		$this->assertEquals(array(array('username' => 'mass email')), $db->sql_fetchrowset($result));
	}
}
