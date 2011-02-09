<?php
/**
*
* @package testing
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/


class phpbb_group_positions_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/group_positions.xml');
	}

	public static function get_group_value_data()
	{
		return array(
			array('teampage', 1, 0),
			array('teampage', 2, 1),
			array('legend', 1, 0),
			array('legend', 3, 1),
		);
	}

	/**
	* @dataProvider get_group_value_data
	*/
	public function test_get_group_value($field, $group_id, $expected)
	{
		global $db;

		$db = $this->new_dbal();

		$this->assertEquals($expected, phpbb_group_positions::get_group_value($field, $group_id));
	}

	public static function get_group_count_data()
	{
		return array(
			array('teampage', 2),
			array('legend', 1),
		);
	}

	/**
	* @dataProvider get_group_count_data
	*/
	public function test_get_group_count($field, $expected)
	{
		global $db;

		$db = $this->new_dbal();

		$this->assertEquals($expected, phpbb_group_positions::get_group_count($field));
	}

	public static function add_group_data()
	{
		return array(
			array('teampage', 1, array(
				array('group_id' => 1, 'group_teampage' => 3, 'group_legend' => 0),
				array('group_id' => 2, 'group_teampage' => 1, 'group_legend' => 0),
				array('group_id' => 3, 'group_teampage' => 2, 'group_legend' => 1),
			)),
			array('teampage', 2, array(
				array('group_id' => 1, 'group_teampage' => 0, 'group_legend' => 0),
				array('group_id' => 2, 'group_teampage' => 1, 'group_legend' => 0),
				array('group_id' => 3, 'group_teampage' => 2, 'group_legend' => 1),
			)),
		);
	}

	/**
	* @dataProvider add_group_data
	*/
	public function test_add_group($field, $group_id, $expected)
	{
		global $db;

		$db = $this->new_dbal();
		phpbb_group_positions::add_group($field, $group_id);

		$result = $db->sql_query('SELECT group_id, group_teampage, group_legend
			FROM ' . GROUPS_TABLE . '
			ORDER BY group_id ASC');

		$this->assertEquals($expected, $db->sql_fetchrowset($result));
	}

	public static function delete_group_data()
	{
		return array(
			array('teampage', 1, array(
				array('group_id' => 1, 'group_teampage' => 0, 'group_legend' => 0),
				array('group_id' => 2, 'group_teampage' => 1, 'group_legend' => 0),
				array('group_id' => 3, 'group_teampage' => 2, 'group_legend' => 1),
			)),
			array('teampage', 2, array(
				array('group_id' => 1, 'group_teampage' => 0, 'group_legend' => 0),
				array('group_id' => 2, 'group_teampage' => 0, 'group_legend' => 0),
				array('group_id' => 3, 'group_teampage' => 1, 'group_legend' => 1),
			)),
			array('teampage', 3, array(
				array('group_id' => 1, 'group_teampage' => 0, 'group_legend' => 0),
				array('group_id' => 2, 'group_teampage' => 1, 'group_legend' => 0),
				array('group_id' => 3, 'group_teampage' => 0, 'group_legend' => 1),
			)),
		);
	}

	/**
	* @dataProvider delete_group_data
	*/
	public function test_delete_group($field, $group_id, $expected)
	{
		global $db;

		$db = $this->new_dbal();
		phpbb_group_positions::delete_group($field, $group_id);

		$result = $db->sql_query('SELECT group_id, group_teampage, group_legend
			FROM ' . GROUPS_TABLE . '
			ORDER BY group_id ASC');

		$this->assertEquals($expected, $db->sql_fetchrowset($result));
	}

	public static function move_up_data()
	{
		return array(
			array('teampage', 1, array(
				array('group_id' => 1, 'group_teampage' => 0, 'group_legend' => 0),
				array('group_id' => 2, 'group_teampage' => 1, 'group_legend' => 0),
				array('group_id' => 3, 'group_teampage' => 2, 'group_legend' => 1),
			)),
			array('teampage', 2, array(
				array('group_id' => 1, 'group_teampage' => 0, 'group_legend' => 0),
				array('group_id' => 2, 'group_teampage' => 1, 'group_legend' => 0),
				array('group_id' => 3, 'group_teampage' => 2, 'group_legend' => 1),
			)),
			array('teampage', 3, array(
				array('group_id' => 1, 'group_teampage' => 0, 'group_legend' => 0),
				array('group_id' => 2, 'group_teampage' => 2, 'group_legend' => 0),
				array('group_id' => 3, 'group_teampage' => 1, 'group_legend' => 1),
			)),
		);
	}

	/**
	* @dataProvider move_up_data
	*/
	public function test_move_up($field, $group_id, $expected)
	{
		global $db;

		$db = $this->new_dbal();
		phpbb_group_positions::move_up($field, $group_id);

		$result = $db->sql_query('SELECT group_id, group_teampage, group_legend
			FROM ' . GROUPS_TABLE . '
			ORDER BY group_id ASC');

		$this->assertEquals($expected, $db->sql_fetchrowset($result));
	}

	public static function move_down_data()
	{
		return array(
			array('teampage', 1, array(
				array('group_id' => 1, 'group_teampage' => 0, 'group_legend' => 0),
				array('group_id' => 2, 'group_teampage' => 1, 'group_legend' => 0),
				array('group_id' => 3, 'group_teampage' => 2, 'group_legend' => 1),
			)),
			array('teampage', 2, array(
				array('group_id' => 1, 'group_teampage' => 0, 'group_legend' => 0),
				array('group_id' => 2, 'group_teampage' => 2, 'group_legend' => 0),
				array('group_id' => 3, 'group_teampage' => 1, 'group_legend' => 1),
			)),
			array('teampage', 3, array(
				array('group_id' => 1, 'group_teampage' => 0, 'group_legend' => 0),
				array('group_id' => 2, 'group_teampage' => 1, 'group_legend' => 0),
				array('group_id' => 3, 'group_teampage' => 2, 'group_legend' => 1),
			)),
		);
	}

	/**
	* @dataProvider move_down_data
	*/
	public function test_move_down($field, $group_id, $expected)
	{
		global $db;

		$db = $this->new_dbal();
		phpbb_group_positions::move_down($field, $group_id);

		$result = $db->sql_query('SELECT group_id, group_teampage, group_legend
			FROM ' . GROUPS_TABLE . '
			ORDER BY group_id ASC');

		$this->assertEquals($expected, $db->sql_fetchrowset($result));
	}
}

