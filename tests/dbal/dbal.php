<?php
/**
*
* @package testing
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

require_once 'test_framework/framework.php';
require_once '../phpBB/includes/functions.php';

class phpbb_dbal_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__).'/fixtures/three_users.xml');
	}

	public static function return_on_error_select_data()
	{
		return array(
			array('phpbb_users', "username_clean = 'bertie'", array(array('username_clean' => 'bertie'))),
			array('phpbb_users', "username_clean = 'phpBB'", array()),
			array('phpbb_users', 'username_clean syntax_error', false),
			array('phpbb_users', 'column_not_exists = 2', false),
			array('table_not_exists', 'column_not_exists = 2', false),
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

	public static function fetchrow_data()
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

	public static function fetchfield_data()
	{
		return array(
			array('', array('barfoo', 'foobar', 'bertie')),
			array('user_id = 2', array('foobar')),
			array("username_clean = 'bertie'", array('bertie')),
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

	public static function query_limit_data()
	{
		return array(
			array(0, 0, array(array('username_clean' => 'barfoo'),
				array('username_clean' => 'foobar'),
				array('username_clean' => 'bertie'))),
			array(0, 1, array(array('username_clean' => 'foobar'),
				array('username_clean' => 'bertie'))),
			array(1, 0, array(array('username_clean' => 'barfoo'))),
			array(1, 1, array(array('username_clean' => 'foobar'))),
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

	public static function like_expression_data()
	{
		// * = any_char; # = one_char
		return array(
			array('barfoo', array(array('username_clean' => 'barfoo'))),
			array('bar', array()),
			array('bar*', array(array('username_clean' => 'barfoo'))),
			array('*bar*', array(array('username_clean' => 'barfoo'),
				array('username_clean' => 'foobar'))),
			array('*b*', array(array('username_clean' => 'barfoo'),
				array('username_clean' => 'foobar'),
				array('username_clean' => 'bertie'))),
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

		$like_expression = str_replace('*', $db->any_char, $like_expression);
		$like_expression = str_replace('#', $db->one_char, $like_expression);
		$where = ($like_expression) ? 'username_clean ' . $db->sql_like_expression($like_expression) : '';

		$result = $db->sql_query('SELECT username_clean
			FROM phpbb_users
			' . (($where) ? ' WHERE ' . $where : '') . '
			ORDER BY user_id ASC');

		$this->assertEquals($expected, $db->sql_fetchrowset($result));

		$db->sql_freeresult($result);
	}

	public static function in_set_data()
	{
		return array(
			array('user_id', 3, false, false, array(array('username_clean' => 'bertie'))),
			array('user_id', 3, false, true, array(array('username_clean' => 'bertie'))),
			array('user_id', 3, true, false, array(array('username_clean' => 'barfoo'),
				array('username_clean' => 'foobar'))),
			array('user_id', 3, true, true, array(array('username_clean' => 'barfoo'),
				array('username_clean' => 'foobar'))),
			array('user_id', '3', false, false, array(array('username_clean' => 'bertie'))),
			array('user_id', '3', false, true, array(array('username_clean' => 'bertie'))),
			array('user_id', '3', true, false, array(array('username_clean' => 'barfoo'),
				array('username_clean' => 'foobar'))),
			array('user_id', '3', true, true, array(array('username_clean' => 'barfoo'),
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
			array('user_id', '', false, false, array()),
			array('user_id', '', false, true, array()),
			array('user_id', '', true, false, array(array('username_clean' => 'barfoo'),
				array('username_clean' => 'foobar'),
				array('username_clean' => 'bertie'))),
			array('user_id', '', true, true, array(array('username_clean' => 'barfoo'),
				array('username_clean' => 'foobar'),
				array('username_clean' => 'bertie'))),
			array('user_id', array(), false, true, array()),
			array('user_id', array(), true, true, array(array('username_clean' => 'barfoo'),
				array('username_clean' => 'foobar'),
				array('username_clean' => 'bertie'))),

			// These here would throw errors and therefor $result should be false.
			array('user_id', array(), false, false, false, true),
			array('user_id', array(), true, false, false, true),
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

	public static function build_array_data()
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

		$result = $db->sql_query('SELECT username_clean
			FROM phpbb_users
			WHERE ' . $db->sql_build_array('SELECT', $assoc_ary) . '
			ORDER BY user_id ASC');

		if ($catch_error)
		{
			$db->sql_return_on_error(false);
		}

		$this->assertEquals($expected, $db->sql_fetchrowset($result));

		$db->sql_freeresult($result);
	}
}

