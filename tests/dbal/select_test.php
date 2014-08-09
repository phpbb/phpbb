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
require_once dirname(__FILE__) . '/../../phpBB/includes/utf/utf_tools.php';

class phpbb_dbal_select_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__).'/fixtures/three_users.xml');
	}

	public function return_on_error_select_data()
	{
		return array(
			array('phpbb_users', "username_clean = 'bertie'", array(array('username_clean' => 'bertie'))),
			array('phpbb_users', 'username_clean syntax_error', false),
		);
	}

	/**
	* @dataProvider return_on_error_select_data
	*/
	public function test_return_on_error_select($table, $where, $expected)
	{
		$db = $this->new_dbal();

		$db->sql_return_on_error(true);

		$result = $db->sql_query('SELECT username_clean
			FROM ' . $table . '
			WHERE ' . $where . '
			ORDER BY user_id ASC');

		$db->sql_return_on_error(false);

		$this->assertEquals($expected, $db->sql_fetchrowset($result));
	}

	public function fetchrow_data()
	{
		return array(
			array('', array(array('username_clean' => 'barfoo'),
				array('username_clean' => 'foobar'),
				array('username_clean' => 'bertie'))),
			array('user_id = 2', array(array('username_clean' => 'foobar'))),
			array("username_clean = 'bertie'", array(array('username_clean' => 'bertie'))),
			array("username_clean = 'phpBB'", array()),
		);
	}

	/**
	* @dataProvider fetchrow_data
	*/
	public function test_fetchrow($where, $expected)
	{
		$db = $this->new_dbal();

		$result = $db->sql_query('SELECT username_clean
			FROM phpbb_users
			' . (($where) ? ' WHERE ' . $where : '') . '
			ORDER BY user_id ASC');

		$ary = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$ary[] = $row;
		}
		$db->sql_freeresult($result);

		$this->assertEquals($expected, $ary);
	}

	/**
	* @dataProvider fetchrow_data
	*/
	public function test_fetchrowset($where, $expected)
	{
		$db = $this->new_dbal();

		$result = $db->sql_query('SELECT username_clean
			FROM phpbb_users
			' . (($where) ? ' WHERE ' . $where : '') . '
			ORDER BY user_id ASC');

		$this->assertEquals($expected, $db->sql_fetchrowset($result));

		$db->sql_freeresult($result);
	}

	public function fetchfield_data()
	{
		return array(
			array('', array('barfoo', 'foobar', 'bertie')),
			array('user_id = 2', array('foobar')),
		);
	}

	/**
	* @dataProvider fetchfield_data
	*/
	public function test_fetchfield($where, $expected)
	{
		$db = $this->new_dbal();

		$result = $db->sql_query('SELECT username_clean
			FROM phpbb_users
			' . (($where) ? ' WHERE ' . $where : '') . '
			ORDER BY user_id ASC');

		$ary = array();
		while ($row = $db->sql_fetchfield('username_clean'))
		{
			$ary[] = $row;
		}
		$db->sql_freeresult($result);

		$this->assertEquals($expected, $ary);
	}

	static public function fetchfield_seek_data()
	{
		return array(
			array(1, 'foobar'),
			array(0, 'barfoo'),
			array(2, 'bertie'),
		);
	}

	/**
	* @dataProvider fetchfield_seek_data
	*/
	public function test_fetchfield_seek($rownum, $expected)
	{
		$db = $this->new_dbal();

		$result = $db->sql_query('SELECT username_clean
			FROM phpbb_users
			ORDER BY user_id ASC');

		$field = $db->sql_fetchfield('username_clean', $rownum, $result);
		$db->sql_freeresult($result);

		$this->assertEquals($expected, $field);
	}

	static public function query_limit_data()
	{
		return array(
			array(0, 0, array(array('username_clean' => 'barfoo'),
				array('username_clean' => 'foobar'),
				array('username_clean' => 'bertie'))),
			array(0, 1, array(array('username_clean' => 'foobar'),
				array('username_clean' => 'bertie'))),
			array(1, 0, array(array('username_clean' => 'barfoo'))),
			array(1, 2, array(array('username_clean' => 'bertie'))),
			array(2, 0, array(array('username_clean' => 'barfoo'),
				array('username_clean' => 'foobar'))),
			array(2, 2, array(array('username_clean' => 'bertie'))),
			array(2, 5, array()),
			array(10, 1, array(array('username_clean' => 'foobar'),
				array('username_clean' => 'bertie'))),
			array(10, 5, array()),
		);
	}

	/**
	* @dataProvider query_limit_data
	*/
	public function test_query_limit($total, $offset, $expected)
	{
		$db = $this->new_dbal();

		$result = $db->sql_query_limit('SELECT username_clean
			FROM phpbb_users
			ORDER BY user_id ASC', $total, $offset);

		$ary = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$ary[] = $row;
		}
		$db->sql_freeresult($result);

		$this->assertEquals($expected, $ary);
	}

	public function like_expression_data()
	{
		// * = any_char; # = one_char
		return array(
			array('barfoo', array(array('username_clean' => 'barfoo'))),
			array('bar', array()),
			array('bar*', array(array('username_clean' => 'barfoo'))),
			array('*bar*', array(array('username_clean' => 'barfoo'),
				array('username_clean' => 'foobar'))),
			array('b*r', array()),
			array('b*e', array(array('username_clean' => 'bertie'))),
			array('#b*e', array()),
			array('b####e', array(array('username_clean' => 'bertie'))),
		);
	}

	/**
	* @dataProvider like_expression_data
	*/
	public function test_like_expression($like_expression, $expected)
	{
		$db = $this->new_dbal();

		$like_expression = str_replace('*', $db->get_any_char(), $like_expression);
		$like_expression = str_replace('#', $db->get_one_char(), $like_expression);
		$where = ($like_expression) ? 'username_clean ' . $db->sql_like_expression($like_expression) : '';

		$result = $db->sql_query('SELECT username_clean
			FROM phpbb_users
			' . (($where) ? ' WHERE ' . $where : '') . '
			ORDER BY user_id ASC');

		$this->assertEquals($expected, $db->sql_fetchrowset($result));

		$db->sql_freeresult($result);
	}

	public function not_like_expression_data()
	{
		// * = any_char; # = one_char
		return array(
			array('barfoo', array(
				array('username_clean' => 'foobar'),
				array('username_clean' => 'bertie')
			)),
			array('bar', array(
				array('username_clean' => 'barfoo'),
				array('username_clean' => 'foobar'),
				array('username_clean' => 'bertie'),
			)),
			array('bar*', array(
				array('username_clean' => 'foobar'),
				array('username_clean' => 'bertie'))
			),
			array('*bar*', array(array('username_clean' => 'bertie'))),
			array('b*r', array(
				array('username_clean' => 'barfoo'),
				array('username_clean' => 'foobar'),
				array('username_clean' => 'bertie')
			)),
			array('b*e', array(
				array('username_clean' => 'barfoo'),
				array('username_clean' => 'foobar')
			)),
			array('#b*e', array(
				array('username_clean' => 'barfoo'),
				array('username_clean' => 'foobar'),
				array('username_clean' => 'bertie')
			)),
			array('b####e', array(
				array('username_clean' => 'barfoo'),
				array('username_clean' => 'foobar')
			)),
		);
	}

	/**
	* @dataProvider not_like_expression_data
	*/
	public function test_not_like_expression($like_expression, $expected)
	{
		$db = $this->new_dbal();

		$like_expression = str_replace('*', $db->get_any_char(), $like_expression);
		$like_expression = str_replace('#', $db->get_one_char(), $like_expression);
		$where = ($like_expression) ? 'username_clean ' . $db->sql_not_like_expression($like_expression) : '';

		$result = $db->sql_query('SELECT username_clean
			FROM phpbb_users
			' . (($where) ? ' WHERE ' . $where : '') . '
			ORDER BY user_id ASC');

		$this->assertEquals($expected, $db->sql_fetchrowset($result));

		$db->sql_freeresult($result);
	}

	public function in_set_data()
	{
		return array(
			array('user_id', 3, false, false, array(array('username_clean' => 'bertie'))),
			array('user_id', 3, false, true, array(array('username_clean' => 'bertie'))),
			array('user_id', 3, true, false, array(array('username_clean' => 'barfoo'),
				array('username_clean' => 'foobar'))),
			array('user_id', 3, true, true, array(array('username_clean' => 'barfoo'),
				array('username_clean' => 'foobar'))),
			array('username_clean', 'bertie', false, false, array(array('username_clean' => 'bertie'))),
			array('username_clean', 'bertie', false, true, array(array('username_clean' => 'bertie'))),
			array('username_clean', 'bertie', true, false, array(array('username_clean' => 'barfoo'),
				array('username_clean' => 'foobar'))),
			array('username_clean', 'bertie', true, true, array(array('username_clean' => 'barfoo'),
				array('username_clean' => 'foobar'))),
			array('user_id', array(3), false, false, array(array('username_clean' => 'bertie'))),
			array('user_id', array(3), false, true, array(array('username_clean' => 'bertie'))),
			array('user_id', array(3), true, false, array(array('username_clean' => 'barfoo'),
				array('username_clean' => 'foobar'))),
			array('user_id', array(3), true, true, array(array('username_clean' => 'barfoo'),
				array('username_clean' => 'foobar'))),
			array('user_id', array(1, 3), false, false, array(array('username_clean' => 'barfoo'),
				array('username_clean' => 'bertie'))),
			array('user_id', array(1, 3), false, true, array(array('username_clean' => 'barfoo'),
				array('username_clean' => 'bertie'))),
			array('user_id', array(1, 3), true, false, array(array('username_clean' => 'foobar'))),
			array('user_id', array(1, 3), true, true, array(array('username_clean' => 'foobar'))),
			array('username_clean', '', false, false, array()),
			array('username_clean', '', false, true, array()),
			array('username_clean', '', true, false, array(array('username_clean' => 'barfoo'),
				array('username_clean' => 'foobar'),
				array('username_clean' => 'bertie'))),
			array('username_clean', '', true, true, array(array('username_clean' => 'barfoo'),
				array('username_clean' => 'foobar'),
				array('username_clean' => 'bertie'))),
			array('user_id', array(), false, true, array()),
			array('user_id', array(), true, true, array(array('username_clean' => 'barfoo'),
				array('username_clean' => 'foobar'),
				array('username_clean' => 'bertie'))),

			// These here would throw errors and therefor $result should be false.
			// Removing for now because SQLite accepts empty IN() syntax
			/*array('user_id', array(), false, false, false, true),
			array('user_id', array(), true, false, false, true),*/
		);
	}

	/**
	* @dataProvider in_set_data
	*/
	public function test_in_set($field, $array, $negate, $allow_empty_set, $expected, $catch_error = false)
	{
		$db = $this->new_dbal();

		if ($catch_error)
		{
			$db->sql_return_on_error(true);
		}

		$result = $db->sql_query('SELECT username_clean
			FROM phpbb_users
			WHERE ' . $db->sql_in_set($field, $array, $negate, $allow_empty_set) . '
			ORDER BY user_id ASC');

		if ($catch_error)
		{
			$db->sql_return_on_error(false);
		}

		$this->assertEquals($expected, $db->sql_fetchrowset($result));

		$db->sql_freeresult($result);
	}

	public function build_array_data()
	{
		return array(
			array(array('username_clean' => 'barfoo'), array(array('username_clean' => 'barfoo'))),
			array(array('username_clean' => 'barfoo', 'user_id' => 1), array(array('username_clean' => 'barfoo'))),
			array(array('username_clean' => 'barfoo', 'user_id' => 2), array()),

			// These here would throw errors and therefor $result should be false.
			array(array(), false, true),
			array('no_array', false, true),
			array(0, false, true),
		);
	}

	/**
	* @dataProvider build_array_data
	*/
	public function test_build_array($assoc_ary, $expected, $catch_error = false)
	{
		$db = $this->new_dbal();

		if ($catch_error)
		{
			$db->sql_return_on_error(true);
		}

		$sql = 'SELECT username_clean
			FROM phpbb_users
			WHERE ' . $db->sql_build_array('SELECT', $assoc_ary) . '
			ORDER BY user_id ASC';
		$result = $db->sql_query($sql);

		if ($catch_error)
		{
			$db->sql_return_on_error(false);
		}

		$this->assertEquals($expected, $db->sql_fetchrowset($result));

		$db->sql_freeresult($result);
	}

	public function test_nested_transactions()
	{
		$db = $this->new_dbal();

		// nested transactions should work on systems that do not require
		// buffering of nested transactions, so ignore the ones that need
		// buffering
		if ($db->sql_buffer_nested_transactions())
		{
			return;
		}

		$sql = 'SELECT user_id FROM phpbb_users ORDER BY user_id ASC';
		$result1 = $db->sql_query($sql);

		$db->sql_transaction('begin');
		$result2 = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result2);
		$db->sql_transaction('commit');

		$this->assertEquals('1', $row['user_id']);
	}

	/**
	 * fix for PHPBB3-10307
	 */
	public function test_sql_fetchrow_returns_false_when_empty()
	{
		$db = $this->new_dbal();

		$sql = 'SELECT user_id
			FROM phpbb_users
			WHERE 1 = 0';
		$result = $db->sql_query($sql);

		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		$this->assertSame(false, $row);
	}

	public function test_get_row_count()
	{
		$this->assertSame(
			3,
			(int) $this->new_dbal()->get_row_count('phpbb_users'),
			"Failed asserting that user table has exactly 3 rows."
		);
	}

	public function test_get_estimated_row_count()
	{
		$actual = $this->new_dbal()->get_estimated_row_count('phpbb_users');

		if (is_string($actual) && isset($actual[0]) && $actual[0] === '~')
		{
			$actual = substr($actual, 1);
		}

		$this->assertGreaterThan(
			1,
			$actual,
			"Failed asserting that estimated row count of user table is greater than 1."
		);
	}
}
