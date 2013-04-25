<?php
/**
*
* @package Nested Set Forum
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/nestedset_forum_base.php';

class phpbb_tests_tree_nestedset_forum_get_data_test extends phpbb_tests_tree_nestedset_forum_base
{
	public function get_full_branch_data_data()
	{
		return array(
			array(1, true, true, array(1, 2, 3)),
			array(1, true, false, array(2, 3)),
			array(1, false, true, array(3, 2, 1)),
			array(1, false, false, array(3, 2)),

			array(2, true, true, array(1, 2)),
			array(2, true, false, array(1)),
			array(2, false, true, array(2, 1)),
			array(2, false, false, array(1)),

			array(5, true, true, array(4, 5, 6)),
			array(5, true, false, array(4, 6)),
			array(5, false, true, array(6, 5, 4)),
			array(5, false, false, array(6, 4)),
		);
	}

	/**
	* @dataProvider get_full_branch_data_data
	*/
	public function test_get_full_branch_data($forum_id, $order_desc, $include_item, $expected)
	{
		$this->assertEquals($expected, array_keys($this->set->get_full_branch_data($forum_id, $order_desc, $include_item)));
	}

	public function get_parent_branch_data_data()
	{
		return array(
			array(1, true, true, array(1)),
			array(1, true, false, array()),
			array(1, false, true, array(1)),
			array(1, false, false, array()),

			array(2, true, true, array(1, 2)),
			array(2, true, false, array(1)),
			array(2, false, true, array(2, 1)),
			array(2, false, false, array(1)),

			array(5, true, true, array(4, 5)),
			array(5, true, false, array(4)),
			array(5, false, true, array(5, 4)),
			array(5, false, false, array(4)),
		);
	}

	/**
	* @dataProvider get_parent_branch_data_data
	*/
	public function test_get_parent_branch_data($forum_id, $order_desc, $include_item, $expected)
	{
		$this->assertEquals($expected, array_keys($this->set->get_parent_branch_data($forum_id, $order_desc, $include_item)));
	}

	public function get_children_branch_data_data()
	{
		return array(
			array(1, true, true, array(1, 2, 3)),
			array(1, true, false, array(2, 3)),
			array(1, false, true, array(3, 2, 1)),
			array(1, false, false, array(3, 2)),

			array(2, true, true, array(2)),
			array(2, true, false, array()),
			array(2, false, true, array(2)),
			array(2, false, false, array()),

			array(5, true, true, array(5, 6)),
			array(5, true, false, array(6)),
			array(5, false, true, array(6, 5)),
			array(5, false, false, array(6)),
		);
	}

	/**
	* @dataProvider get_children_branch_data_data
	*/
	public function test_get_children_branch_data($forum_id, $order_desc, $include_item, $expected)
	{
		$this->assertEquals($expected, array_keys($this->set->get_children_branch_data($forum_id, $order_desc, $include_item)));
	}

	public function get_parent_data_data()
	{
		return array(
			array(1, array(), array()),
			array(1, array('forum_parents' => serialize(array())), array()),
			array(2, array(), array(1)),
			array(2, array('forum_parents' => serialize(array(1 => array()))), array(1)),
			array(10, array(), array(7, 9)),
			array(10, array('forum_parents' => serialize(array(7 => array(), 9 => array()))), array(7, 9)),
		);
	}

	/**
	* @dataProvider get_parent_data_data
	*/
	public function test_get_parent_data($forum_id, $forum_data, $expected)
	{
		$this->assertEquals($expected, array_keys($this->set->get_parent_data(array_merge($this->forum_data[$forum_id], $forum_data))));
	}
}
