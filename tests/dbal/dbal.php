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

	public function test_select_row()
	{
		$db = $this->new_dbal();

		$result = $db->sql_query('SELECT username_clean
			FROM phpbb_users
			WHERE user_id = 2');
		$row = $db->sql_fetchrow($result);

		$this->assertEquals(array('username_clean' => 'foobar'), $row);
	}

	public function test_select_field()
	{
		$db = $this->new_dbal();

		$result = $db->sql_query('SELECT username_clean
			FROM phpbb_users
			WHERE user_id = 2');

		$this->assertEquals('foobar', $db->sql_fetchfield('username_clean'));
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
			ORDER BY user_id', $total, $offset);

		$ary = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$ary[] = $row;
		}
		$db->sql_freeresult($result);

		$this->assertEquals($expected, $ary);
	}
}

