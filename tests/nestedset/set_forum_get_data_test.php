<?php
/**
*
* @package Nested Set
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/set_forum_base.php';

class phpbb_tests_nestedset_set_forum_get_data_test extends phpbb_tests_nestedset_set_forum_base
{
	public function get_branch_data_data()
	{
		return array(
			array(1, 'all', true, true, array(1, 2, 3)),
			array(1, 'all', true, false, array(2, 3)),
			array(1, 'all', false, true, array(3, 2, 1)),
			array(1, 'all', false, false, array(3, 2)),

			array(1, 'parents', true, true, array(1)),
			array(1, 'parents', true, false, array()),
			array(1, 'parents', false, true, array(1)),
			array(1, 'parents', false, false, array()),

			array(1, 'children', true, true, array(1, 2, 3)),
			array(1, 'children', true, false, array(2, 3)),
			array(1, 'children', false, true, array(3, 2, 1)),
			array(1, 'children', false, false, array(3, 2)),

			array(2, 'all', true, true, array(1, 2)),
			array(2, 'all', true, false, array(1)),
			array(2, 'all', false, true, array(2, 1)),
			array(2, 'all', false, false, array(1)),

			array(2, 'parents', true, true, array(1, 2)),
			array(2, 'parents', true, false, array(1)),
			array(2, 'parents', false, true, array(2, 1)),
			array(2, 'parents', false, false, array(1)),

			array(2, 'children', true, true, array(2)),
			array(2, 'children', true, false, array()),
			array(2, 'children', false, true, array(2)),
			array(2, 'children', false, false, array()),

			array(5, 'all', true, true, array(4, 5, 6)),
			array(5, 'all', true, false, array(4, 6)),
			array(5, 'all', false, true, array(6, 5, 4)),
			array(5, 'all', false, false, array(6, 4)),

			array(5, 'parents', true, true, array(4, 5)),
			array(5, 'parents', true, false, array(4)),
			array(5, 'parents', false, true, array(5, 4)),
			array(5, 'parents', false, false, array(4)),

			array(5, 'children', true, true, array(5, 6)),
			array(5, 'children', true, false, array(6)),
			array(5, 'children', false, true, array(6, 5)),
			array(5, 'children', false, false, array(6)),
		);
	}

	/**
	* @dataProvider get_branch_data_data
	*/
	public function test_get_branch_data($forum_id, $type, $order_desc, $include_item, $expected)
	{
		$this->assertEquals($expected, array_keys($this->set->get_branch_data($forum_id, $type, $order_desc, $include_item)));
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
