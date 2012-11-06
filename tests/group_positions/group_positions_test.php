<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/


class phpbb_group_positions_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/group_positions.xml');
	}

	public function get_group_value_data()
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

		$test_class = new phpbb_group_positions($db, $field);
		$this->assertEquals($expected, $test_class->get_group_value($group_id));
	}

	public function get_group_count_data()
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

		$test_class = new phpbb_group_positions($db, $field);
		$this->assertEquals($expected, $test_class->get_group_count());
	}

	public function add_group_data()
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
		$test_class = new phpbb_group_positions($db, $field);
		$test_class->add_group($group_id);

		$result = $db->sql_query('SELECT group_id, group_teampage, group_legend
			FROM ' . GROUPS_TABLE . '
			ORDER BY group_id ASC');

		$this->assertEquals($expected, $db->sql_fetchrowset($result));
	}

	public function delete_group_data()
	{
		return array(
			array('teampage', 1, false, array(
				array('group_id' => 1, 'group_teampage' => 0, 'group_legend' => 0),
				array('group_id' => 2, 'group_teampage' => 1, 'group_legend' => 0),
				array('group_id' => 3, 'group_teampage' => 2, 'group_legend' => 1),
			)),
			array('teampage', 2, false, array(
				array('group_id' => 1, 'group_teampage' => 0, 'group_legend' => 0),
				array('group_id' => 2, 'group_teampage' => 0, 'group_legend' => 0),
				array('group_id' => 3, 'group_teampage' => 1, 'group_legend' => 1),
			)),
			array('teampage', 3, false, array(
				array('group_id' => 1, 'group_teampage' => 0, 'group_legend' => 0),
				array('group_id' => 2, 'group_teampage' => 1, 'group_legend' => 0),
				array('group_id' => 3, 'group_teampage' => 0, 'group_legend' => 1),
			)),
			array('teampage', 1, true, array(
				array('group_id' => 1, 'group_teampage' => 0, 'group_legend' => 0),
				array('group_id' => 2, 'group_teampage' => 1, 'group_legend' => 0),
				array('group_id' => 3, 'group_teampage' => 2, 'group_legend' => 1),
			)),
			array('teampage', 2, true, array(
				array('group_id' => 1, 'group_teampage' => 0, 'group_legend' => 0),
				array('group_id' => 2, 'group_teampage' => 1, 'group_legend' => 0),
				array('group_id' => 3, 'group_teampage' => 1, 'group_legend' => 1),
			)),
			array('teampage', 3, true, array(
				array('group_id' => 1, 'group_teampage' => 0, 'group_legend' => 0),
				array('group_id' => 2, 'group_teampage' => 1, 'group_legend' => 0),
				array('group_id' => 3, 'group_teampage' => 2, 'group_legend' => 1),
			)),
		);
	}

	/**
	* @dataProvider delete_group_data
	*/
	public function test_delete_group($field, $group_id, $skip_group, $expected)
	{
		global $db;

		$db = $this->new_dbal();
		$test_class = new phpbb_group_positions($db, $field);
		$test_class->delete_group($group_id, $skip_group);

		$result = $db->sql_query('SELECT group_id, group_teampage, group_legend
			FROM ' . GROUPS_TABLE . '
			ORDER BY group_id ASC');

		$this->assertEquals($expected, $db->sql_fetchrowset($result));
	}

	public function move_up_data()
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
		$test_class = new phpbb_group_positions($db, $field);
		$test_class->move_up($group_id);

		$result = $db->sql_query('SELECT group_id, group_teampage, group_legend
			FROM ' . GROUPS_TABLE . '
			ORDER BY group_id ASC');

		$this->assertEquals($expected, $db->sql_fetchrowset($result));
	}

	public function move_down_data()
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
		$test_class = new phpbb_group_positions($db, $field);
		$test_class->move_down($group_id);

		$result = $db->sql_query('SELECT group_id, group_teampage, group_legend
			FROM ' . GROUPS_TABLE . '
			ORDER BY group_id ASC');

		$this->assertEquals($expected, $db->sql_fetchrowset($result));
	}

	public function move_data()
	{
		return array(
			array('teampage', 1, 1, array(
				array('group_id' => 1, 'group_teampage' => 0, 'group_legend' => 0),
				array('group_id' => 2, 'group_teampage' => 1, 'group_legend' => 0),
				array('group_id' => 3, 'group_teampage' => 2, 'group_legend' => 1),
			)),
			array('teampage', 1, -1, array(
				array('group_id' => 1, 'group_teampage' => 0, 'group_legend' => 0),
				array('group_id' => 2, 'group_teampage' => 1, 'group_legend' => 0),
				array('group_id' => 3, 'group_teampage' => 2, 'group_legend' => 1),
			)),
			array('teampage', 3, 3, array(
				array('group_id' => 1, 'group_teampage' => 0, 'group_legend' => 0),
				array('group_id' => 2, 'group_teampage' => 2, 'group_legend' => 0),
				array('group_id' => 3, 'group_teampage' => 1, 'group_legend' => 1),
			)),
			array('teampage', 2, 0, array(
				array('group_id' => 1, 'group_teampage' => 0, 'group_legend' => 0),
				array('group_id' => 2, 'group_teampage' => 1, 'group_legend' => 0),
				array('group_id' => 3, 'group_teampage' => 2, 'group_legend' => 1),
			)),
			array('teampage', 2, -1, array(
				array('group_id' => 1, 'group_teampage' => 0, 'group_legend' => 0),
				array('group_id' => 2, 'group_teampage' => 2, 'group_legend' => 0),
				array('group_id' => 3, 'group_teampage' => 1, 'group_legend' => 1),
			)),
			array('teampage', 2, -3, array(
				array('group_id' => 1, 'group_teampage' => 0, 'group_legend' => 0),
				array('group_id' => 2, 'group_teampage' => 2, 'group_legend' => 0),
				array('group_id' => 3, 'group_teampage' => 1, 'group_legend' => 1),
			)),
			array('teampage', 3, -1, array(
				array('group_id' => 1, 'group_teampage' => 0, 'group_legend' => 0),
				array('group_id' => 2, 'group_teampage' => 1, 'group_legend' => 0),
				array('group_id' => 3, 'group_teampage' => 2, 'group_legend' => 1),
			)),
		);
	}

	/**
	* @dataProvider move_data
	*/
	public function test_move($field, $group_id, $increment, $expected)
	{
		global $db;

		$db = $this->new_dbal();
		$test_class = new phpbb_group_positions($db, $field);
		$test_class->move($group_id, $increment);

		$result = $db->sql_query('SELECT group_id, group_teampage, group_legend
			FROM ' . GROUPS_TABLE . '
			ORDER BY group_id ASC');

		$this->assertEquals($expected, $db->sql_fetchrowset($result));
	}
}

