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

class phpbb_dbal_oracle_numeric_rewrite_test extends phpbb_test_case
{
	public function numeric_where_data()
	{
		return array(
			array('user_id = 2', 'user_id = 2'),
			array('score = -12.5', 'score = -12.5'),
			array('score >= 2.5 AND user_id <> -1', 'score >= 2.5 AND user_id <> -1'),
			array('user_id IN(1, -2, 3.5)', 'user_id IN(1, -2, 3.5)'),
			array('user_id NOT IN(-1, 2.5)', 'user_id NOT IN(-1, 2.5)'),
			array("username = 'a-b.c'", "username = 'a-b.c'"),
			array("username = 'a''b'", "username = 'a''b'"),
			array("username = ''", 'username is NULL'),
			array("username <> ''", 'username is NOT NULL'),
			array("username IN('a-b.c', '')", "(username IN('a-b.c') OR username is NULL)"),
		);
	}

	/**
	 * @dataProvider numeric_where_data
	 */
	public function test_numeric_where_rewrite($where, $expected)
	{
		$db = new \phpbb\db\driver\oracle();
		$this->assertSame($expected, trim($db->_rewrite_where($where)));
	}
}
