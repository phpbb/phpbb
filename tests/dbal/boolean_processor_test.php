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

class phpbb_boolean_processor_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__.'/fixtures/boolean_processor.xml');
	}

	public function test_single_not_like()
	{
		$db = $this->new_dbal();

		$db->sql_return_on_error(true);

		$sql_ary = array(
			'SELECT'	=> 'u.user_id',
			'FROM'		=> array(
				'phpbb_users'		=> 'u',
			),
			'WHERE'		=> array('u.username_clean', 'NOT_LIKE', 'gr' . $db->get_any_char()),
			'ORDER_BY'	=> 'u.user_id',
		);
		$sql = $db->sql_build_query('SELECT', $sql_ary);
		$result = $db->sql_query($sql);

		$db->sql_return_on_error(false);

		$this->assertEquals(array(
			array('user_id' => '1'),
			array('user_id' => '2'),
			array('user_id' => '3'),
			array('user_id' => '6'),
			), $db->sql_fetchrowset($result),
				($result === false) ?
				"SQL ERROR:<br>" . var_export($sql, true) . "<br>" . $db->sql_error() :
				var_export($sql, true) . '   ' . var_export($result, true)
			);
	}

	public function test_single_like()
	{
		$db = $this->new_dbal();

		$db->sql_return_on_error(true);

		$sql_ary = array(
			'SELECT'	=> 'u.user_id',
			'FROM'		=> array(
				'phpbb_users'		=> 'u',
			),
			'WHERE'		=> array('u.username_clean', 'LIKE', 'gr' . $db->get_any_char()),
			'ORDER_BY'	=> 'u.user_id',
		);
		$sql = $db->sql_build_query('SELECT', $sql_ary);
		$result = $db->sql_query($sql);

		$db->sql_return_on_error(false);

		$this->assertEquals(array(
			array('user_id' => '4'),
			array('user_id' => '5'),
			), $db->sql_fetchrowset($result),
				($result === false) ?
				"SQL ERROR:<br>" . var_export($sql, true) . "<br>" . $db->sql_error() :
				var_export($sql, true) . '   ' . var_export($result, true)
			);
	}

	public function test_single_not_in()
	{
		$db = $this->new_dbal();

		$db->sql_return_on_error(true);

		$sql_ary = array(
			'SELECT'	=> 'u.user_id',
			'FROM'		=> array(
				'phpbb_users'		=> 'u',
			),
			'WHERE'		=> array('u.user_id', 'NOT_IN', array(3,4,5)),
			'ORDER_BY'	=> 'u.user_id',
		);
		$sql = $db->sql_build_query('SELECT', $sql_ary);
		$result = $db->sql_query($sql);

		$db->sql_return_on_error(false);

		$this->assertEquals(array(
			array('user_id' => '1'),
			array('user_id' => '2'),
			array('user_id' => '6'),
			), $db->sql_fetchrowset($result),
				($result === false) ?
				"SQL ERROR:<br>" . var_export($sql, true) . "<br>" . $db->sql_error() :
				var_export($sql, true) . '   ' . var_export($result, true)
			);
	}

	public function test_single_in()
	{
		$db = $this->new_dbal();

		$db->sql_return_on_error(true);

		$sql_ary = array(
			'SELECT'	=> 'u.user_id',
			'FROM'		=> array(
				'phpbb_users'		=> 'u',
			),
			'WHERE'		=> array('u.user_id', 'IN', array(3,4,5)),
			'ORDER_BY'	=> 'u.user_id',
		);
		$sql = $db->sql_build_query('SELECT', $sql_ary);
		$result = $db->sql_query($sql);

		$db->sql_return_on_error(false);

		$this->assertEquals(array(
			array('user_id' => '3'),
			array('user_id' => '4'),
			array('user_id' => '5'),
			), $db->sql_fetchrowset($result),
				($result === false) ?
				"SQL ERROR:<br>" . var_export($sql, true) . "<br>" . $db->sql_error() :
				var_export($sql, true) . '   ' . var_export($result, true)
			);
	}

	public function test_and_of_or_of_and()
	{
		$db = $this->new_dbal();

		$db->sql_return_on_error(true);

		$sql_ary = array(
			'SELECT'	=> 'u.user_id',
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
			'WHERE'		=> array('AND',
				array(
					array('OR',
						array(
							array('AND',
								array(
									array('ug.user_id', 'IN', array(1, 2, 3, 4)),
									array('ug.group_id', '=', 2),
								),
							),
							array('AND',
								array(
									array('ug.group_id', '=', 1),
									array('b.ban_id', 'IS_NOT', NULL),
									array('b.ban_mode', '=', "'user'"),
								),
							),
						),
					),
					array('u.user_id', '=', 'ug.user_id'),
				),
			),
			'ORDER_BY'	=> 'u.user_id',
		);
		$sql = $db->sql_build_query('SELECT', $sql_ary);
		$result = $db->sql_query($sql);

		$db->sql_return_on_error(false);

		$this->assertEquals(array(
			array('user_id' => '2'),
			array('user_id' => '4'),
			), $db->sql_fetchrowset($result),
				($result === false) ?
				"SQL ERROR:<br>" . var_export($sql, true) . "<br>" . $db->sql_error() :
				var_export($sql, true) . '   ' . var_export($result, true)
			);
	}

	public function test_triple_and_with_in()
	{
		$db = $this->new_dbal();

		$db->sql_return_on_error(true);

		$sql_ary = array(
			'SELECT'	=> 'u.user_id',
			'FROM'		=> array(
				'phpbb_users'		=> 'u',
				'phpbb_user_group'	=> 'ug',
			),
			'WHERE'		=> array('AND',
				array(
					array('ug.user_id', 'IN', array(1, 2, 3, 4)),
					array('ug.group_id', '=', 1),
					array('u.user_id', '=', 'ug.user_id'),
				),
			),
			'ORDER_BY'	=> 'u.user_id',
		);
		$sql = $db->sql_build_query('SELECT', $sql_ary);
		$result = $db->sql_query($sql);

		$db->sql_return_on_error(false);

		$this->assertEquals(array(
			array('user_id' => '1'),
			array('user_id' => '2'),
			array('user_id' => '3'),
			), $db->sql_fetchrowset($result),
			($result === false) ?
				"SQL ERROR:<br>" . var_export($sql, true) . "<br>" . $db->sql_error() :
				var_export($sql, true) . '   ' . var_export($result, true)
		);

	}

	public function test_double_and_with_not_of_or()
	{
		$db = $this->new_dbal();

		$db->sql_return_on_error(true);

		$sql_ary = array(
			'SELECT'	=> 'u.user_id',
			'FROM'		=> array(
				'phpbb_users'		=> 'u',
				'phpbb_user_group'	=> 'ug',
			),
			'WHERE'		=> array('AND',
				array(
					array('NOT',
						array(
							array('OR',
								array(
									array('ug.group_id', '=', 1),
									array('ug.group_id', '=', 2),
								),
							),
						),
					),
					array('u.user_id', '=', 'ug.user_id'),
				),
			),
			'ORDER_BY'	=> 'u.user_id',
		);
		$sql = $db->sql_build_query('SELECT', $sql_ary);
		$result = $db->sql_query($sql);

		$db->sql_return_on_error(false);

		$this->assertEquals(array(), $db->sql_fetchrowset($result),
				($result === false) ?
				"SQL ERROR:<br>" . var_export($sql, true) . "<br>" . $db->sql_error() :
				var_export($sql, true) . '   ' . var_export($result, true)
			);
	}

	public function test_triple_and_with_is_null()
	{
		$db = $this->new_dbal();

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
			'WHERE'		=> array('AND',
				array(
					array('ug.group_id', '=', 1),
					array('u.user_id', '=', 'ug.user_id'),
					array('b.ban_id', 'IS', NULL),
				),
			),
			'ORDER_BY'	=> 'u.username',
		);
		$sql = $db->sql_build_query('SELECT', $sql_ary);
		$result = $db->sql_query($sql);

		$db->sql_return_on_error(false);

		$this->assertEquals(array(
			array('username' => 'helper'),
			array('username' => 'mass email'),
			), $db->sql_fetchrowset($result),
				($result === false) ?
				"SQL ERROR:<br>" . var_export($sql, true) . "<br>" . $db->sql_error() :
				var_export($sql, true) . '   ' . var_export($result, true)
			);
	}
}
