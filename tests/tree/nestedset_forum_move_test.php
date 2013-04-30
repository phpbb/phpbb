<?php
/**
*
* @package tree
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/nestedset_forum_base.php';

class phpbb_tests_tree_nestedset_forum_move_test extends phpbb_tests_tree_nestedset_forum_base
{
	public function move_data()
	{
		return array(
			array('Move first item up',
				1, 1, false, array(
				array('forum_id' => 1, 'parent_id' => 0, 'left_id' => 1, 'right_id' => 6),
				array('forum_id' => 2, 'parent_id' => 1, 'left_id' => 2, 'right_id' => 3),
				array('forum_id' => 3, 'parent_id' => 1, 'left_id' => 4, 'right_id' => 5),
				array('forum_id' => 4, 'parent_id' => 0, 'left_id' => 7, 'right_id' => 12),
				array('forum_id' => 5, 'parent_id' => 4, 'left_id' => 8, 'right_id' => 11),
				array('forum_id' => 6, 'parent_id' => 5, 'left_id' => 9, 'right_id' => 10),
				array('forum_id' => 7, 'parent_id' => 0, 'left_id' => 13, 'right_id' => 22),
				array('forum_id' => 8, 'parent_id' => 7, 'left_id' => 14, 'right_id' => 15),
				array('forum_id' => 9, 'parent_id' => 7, 'left_id' => 16, 'right_id' => 19),
				array('forum_id' => 10, 'parent_id' => 9, 'left_id' => 17, 'right_id' => 18),
				array('forum_id' => 11, 'parent_id' => 7, 'left_id' => 20, 'right_id' => 21),
			)),
			array('Move last item down',
				7, -1, false, array(
				array('forum_id' => 1, 'parent_id' => 0, 'left_id' => 1, 'right_id' => 6),
				array('forum_id' => 2, 'parent_id' => 1, 'left_id' => 2, 'right_id' => 3),
				array('forum_id' => 3, 'parent_id' => 1, 'left_id' => 4, 'right_id' => 5),
				array('forum_id' => 4, 'parent_id' => 0, 'left_id' => 7, 'right_id' => 12),
				array('forum_id' => 5, 'parent_id' => 4, 'left_id' => 8, 'right_id' => 11),
				array('forum_id' => 6, 'parent_id' => 5, 'left_id' => 9, 'right_id' => 10),
				array('forum_id' => 7, 'parent_id' => 0, 'left_id' => 13, 'right_id' => 22),
				array('forum_id' => 8, 'parent_id' => 7, 'left_id' => 14, 'right_id' => 15),
				array('forum_id' => 9, 'parent_id' => 7, 'left_id' => 16, 'right_id' => 19),
				array('forum_id' => 10, 'parent_id' => 9, 'left_id' => 17, 'right_id' => 18),
				array('forum_id' => 11, 'parent_id' => 7, 'left_id' => 20, 'right_id' => 21),
			)),
			array('Move first item down',
				1, -1, true, array(
				array('forum_id' => 4, 'parent_id' => 0, 'left_id' => 1, 'right_id' => 6),
				array('forum_id' => 5, 'parent_id' => 4, 'left_id' => 2, 'right_id' => 5),
				array('forum_id' => 6, 'parent_id' => 5, 'left_id' => 3, 'right_id' => 4),
				array('forum_id' => 1, 'parent_id' => 0, 'left_id' => 7, 'right_id' => 12),
				array('forum_id' => 2, 'parent_id' => 1, 'left_id' => 8, 'right_id' => 9),
				array('forum_id' => 3, 'parent_id' => 1, 'left_id' => 10, 'right_id' => 11),
				array('forum_id' => 7, 'parent_id' => 0, 'left_id' => 13, 'right_id' => 22),
				array('forum_id' => 8, 'parent_id' => 7, 'left_id' => 14, 'right_id' => 15),
				array('forum_id' => 9, 'parent_id' => 7, 'left_id' => 16, 'right_id' => 19),
				array('forum_id' => 10, 'parent_id' => 9, 'left_id' => 17, 'right_id' => 18),
				array('forum_id' => 11, 'parent_id' => 7, 'left_id' => 20, 'right_id' => 21),
			)),
			array('Move second item up',
				4, 1, true, array(
				array('forum_id' => 4, 'parent_id' => 0, 'left_id' => 1, 'right_id' => 6),
				array('forum_id' => 5, 'parent_id' => 4, 'left_id' => 2, 'right_id' => 5),
				array('forum_id' => 6, 'parent_id' => 5, 'left_id' => 3, 'right_id' => 4),
				array('forum_id' => 1, 'parent_id' => 0, 'left_id' => 7, 'right_id' => 12),
				array('forum_id' => 2, 'parent_id' => 1, 'left_id' => 8, 'right_id' => 9),
				array('forum_id' => 3, 'parent_id' => 1, 'left_id' => 10, 'right_id' => 11),
				array('forum_id' => 7, 'parent_id' => 0, 'left_id' => 13, 'right_id' => 22),
				array('forum_id' => 8, 'parent_id' => 7, 'left_id' => 14, 'right_id' => 15),
				array('forum_id' => 9, 'parent_id' => 7, 'left_id' => 16, 'right_id' => 19),
				array('forum_id' => 10, 'parent_id' => 9, 'left_id' => 17, 'right_id' => 18),
				array('forum_id' => 11, 'parent_id' => 7, 'left_id' => 20, 'right_id' => 21),
			)),
			array('Move last item up',
				7, 1, true, array(
				array('forum_id' => 1, 'parent_id' => 0, 'left_id' => 1, 'right_id' => 6),
				array('forum_id' => 2, 'parent_id' => 1, 'left_id' => 2, 'right_id' => 3),
				array('forum_id' => 3, 'parent_id' => 1, 'left_id' => 4, 'right_id' => 5),
				array('forum_id' => 7, 'parent_id' => 0, 'left_id' => 7, 'right_id' => 16),
				array('forum_id' => 8, 'parent_id' => 7, 'left_id' => 8, 'right_id' => 9),
				array('forum_id' => 9, 'parent_id' => 7, 'left_id' => 10, 'right_id' => 13),
				array('forum_id' => 10, 'parent_id' => 9, 'left_id' => 11, 'right_id' => 12),
				array('forum_id' => 11, 'parent_id' => 7, 'left_id' => 14, 'right_id' => 15),
				array('forum_id' => 4, 'parent_id' => 0, 'left_id' => 17, 'right_id' => 22),
				array('forum_id' => 5, 'parent_id' => 4, 'left_id' => 18, 'right_id' => 21),
				array('forum_id' => 6, 'parent_id' => 5, 'left_id' => 19, 'right_id' => 20),
			)),
			array('Move last item up by 2',
				7, 2, true, array(
				array('forum_id' => 7, 'parent_id' => 0, 'left_id' => 1, 'right_id' => 10),
				array('forum_id' => 8, 'parent_id' => 7, 'left_id' => 2, 'right_id' => 3),
				array('forum_id' => 9, 'parent_id' => 7, 'left_id' => 4, 'right_id' => 7),
				array('forum_id' => 10, 'parent_id' => 9, 'left_id' => 5, 'right_id' => 6),
				array('forum_id' => 11, 'parent_id' => 7, 'left_id' => 8, 'right_id' => 9),
				array('forum_id' => 1, 'parent_id' => 0, 'left_id' => 11, 'right_id' => 16),
				array('forum_id' => 2, 'parent_id' => 1, 'left_id' => 12, 'right_id' => 13),
				array('forum_id' => 3, 'parent_id' => 1, 'left_id' => 14, 'right_id' => 15),
				array('forum_id' => 4, 'parent_id' => 0, 'left_id' => 17, 'right_id' => 22),
				array('forum_id' => 5, 'parent_id' => 4, 'left_id' => 18, 'right_id' => 21),
				array('forum_id' => 6, 'parent_id' => 5, 'left_id' => 19, 'right_id' => 20),
			)),
			array('Move last item up by 100',
				7, 100, true, array(
				array('forum_id' => 7, 'parent_id' => 0, 'left_id' => 1, 'right_id' => 10),
				array('forum_id' => 8, 'parent_id' => 7, 'left_id' => 2, 'right_id' => 3),
				array('forum_id' => 9, 'parent_id' => 7, 'left_id' => 4, 'right_id' => 7),
				array('forum_id' => 10, 'parent_id' => 9, 'left_id' => 5, 'right_id' => 6),
				array('forum_id' => 11, 'parent_id' => 7, 'left_id' => 8, 'right_id' => 9),
				array('forum_id' => 1, 'parent_id' => 0, 'left_id' => 11, 'right_id' => 16),
				array('forum_id' => 2, 'parent_id' => 1, 'left_id' => 12, 'right_id' => 13),
				array('forum_id' => 3, 'parent_id' => 1, 'left_id' => 14, 'right_id' => 15),
				array('forum_id' => 4, 'parent_id' => 0, 'left_id' => 17, 'right_id' => 22),
				array('forum_id' => 5, 'parent_id' => 4, 'left_id' => 18, 'right_id' => 21),
				array('forum_id' => 6, 'parent_id' => 5, 'left_id' => 19, 'right_id' => 20),
			)),
		);
	}

	/**
	* @dataProvider move_data
	*/
	public function test_move($explain, $forum_id, $delta, $expected_moved, $expected)
	{
		$this->assertEquals($expected_moved, $this->set->move($forum_id, $delta));

		$result = $this->db->sql_query("SELECT forum_id, parent_id, left_id, right_id
			FROM phpbb_forums
			ORDER BY left_id, forum_id ASC");
		$this->assertEquals($expected, $this->db->sql_fetchrowset($result));
	}

	public function move_down_data()
	{
		return array(
			array('Move last item down',
				7, false, array(
				array('forum_id' => 1, 'parent_id' => 0, 'left_id' => 1, 'right_id' => 6),
				array('forum_id' => 2, 'parent_id' => 1, 'left_id' => 2, 'right_id' => 3),
				array('forum_id' => 3, 'parent_id' => 1, 'left_id' => 4, 'right_id' => 5),
				array('forum_id' => 4, 'parent_id' => 0, 'left_id' => 7, 'right_id' => 12),
				array('forum_id' => 5, 'parent_id' => 4, 'left_id' => 8, 'right_id' => 11),
				array('forum_id' => 6, 'parent_id' => 5, 'left_id' => 9, 'right_id' => 10),
				array('forum_id' => 7, 'parent_id' => 0, 'left_id' => 13, 'right_id' => 22),
				array('forum_id' => 8, 'parent_id' => 7, 'left_id' => 14, 'right_id' => 15),
				array('forum_id' => 9, 'parent_id' => 7, 'left_id' => 16, 'right_id' => 19),
				array('forum_id' => 10, 'parent_id' => 9, 'left_id' => 17, 'right_id' => 18),
				array('forum_id' => 11, 'parent_id' => 7, 'left_id' => 20, 'right_id' => 21),
			)),
			array('Move first item down',
				1, true, array(
				array('forum_id' => 4, 'parent_id' => 0, 'left_id' => 1, 'right_id' => 6),
				array('forum_id' => 5, 'parent_id' => 4, 'left_id' => 2, 'right_id' => 5),
				array('forum_id' => 6, 'parent_id' => 5, 'left_id' => 3, 'right_id' => 4),
				array('forum_id' => 1, 'parent_id' => 0, 'left_id' => 7, 'right_id' => 12),
				array('forum_id' => 2, 'parent_id' => 1, 'left_id' => 8, 'right_id' => 9),
				array('forum_id' => 3, 'parent_id' => 1, 'left_id' => 10, 'right_id' => 11),
				array('forum_id' => 7, 'parent_id' => 0, 'left_id' => 13, 'right_id' => 22),
				array('forum_id' => 8, 'parent_id' => 7, 'left_id' => 14, 'right_id' => 15),
				array('forum_id' => 9, 'parent_id' => 7, 'left_id' => 16, 'right_id' => 19),
				array('forum_id' => 10, 'parent_id' => 9, 'left_id' => 17, 'right_id' => 18),
				array('forum_id' => 11, 'parent_id' => 7, 'left_id' => 20, 'right_id' => 21),
			)),
		);
	}

	/**
	* @dataProvider move_down_data
	*/
	public function test_move_down($explain, $forum_id, $expected_moved, $expected)
	{
		$this->assertEquals($expected_moved, $this->set->move_down($forum_id));

		$result = $this->db->sql_query("SELECT forum_id, parent_id, left_id, right_id
			FROM phpbb_forums
			ORDER BY left_id, forum_id ASC");
		$this->assertEquals($expected, $this->db->sql_fetchrowset($result));
	}

	public function move_up_data()
	{
		return array(
			array('Move first item up',
				1, false, array(
				array('forum_id' => 1, 'parent_id' => 0, 'left_id' => 1, 'right_id' => 6),
				array('forum_id' => 2, 'parent_id' => 1, 'left_id' => 2, 'right_id' => 3),
				array('forum_id' => 3, 'parent_id' => 1, 'left_id' => 4, 'right_id' => 5),
				array('forum_id' => 4, 'parent_id' => 0, 'left_id' => 7, 'right_id' => 12),
				array('forum_id' => 5, 'parent_id' => 4, 'left_id' => 8, 'right_id' => 11),
				array('forum_id' => 6, 'parent_id' => 5, 'left_id' => 9, 'right_id' => 10),
				array('forum_id' => 7, 'parent_id' => 0, 'left_id' => 13, 'right_id' => 22),
				array('forum_id' => 8, 'parent_id' => 7, 'left_id' => 14, 'right_id' => 15),
				array('forum_id' => 9, 'parent_id' => 7, 'left_id' => 16, 'right_id' => 19),
				array('forum_id' => 10, 'parent_id' => 9, 'left_id' => 17, 'right_id' => 18),
				array('forum_id' => 11, 'parent_id' => 7, 'left_id' => 20, 'right_id' => 21),
			)),
			array('Move second item up',
				4, true, array(
				array('forum_id' => 4, 'parent_id' => 0, 'left_id' => 1, 'right_id' => 6),
				array('forum_id' => 5, 'parent_id' => 4, 'left_id' => 2, 'right_id' => 5),
				array('forum_id' => 6, 'parent_id' => 5, 'left_id' => 3, 'right_id' => 4),
				array('forum_id' => 1, 'parent_id' => 0, 'left_id' => 7, 'right_id' => 12),
				array('forum_id' => 2, 'parent_id' => 1, 'left_id' => 8, 'right_id' => 9),
				array('forum_id' => 3, 'parent_id' => 1, 'left_id' => 10, 'right_id' => 11),
				array('forum_id' => 7, 'parent_id' => 0, 'left_id' => 13, 'right_id' => 22),
				array('forum_id' => 8, 'parent_id' => 7, 'left_id' => 14, 'right_id' => 15),
				array('forum_id' => 9, 'parent_id' => 7, 'left_id' => 16, 'right_id' => 19),
				array('forum_id' => 10, 'parent_id' => 9, 'left_id' => 17, 'right_id' => 18),
				array('forum_id' => 11, 'parent_id' => 7, 'left_id' => 20, 'right_id' => 21),
			)),
		);
	}

	/**
	* @dataProvider move_up_data
	*/
	public function test_move_up($explain, $forum_id, $expected_moved, $expected)
	{
		$this->assertEquals($expected_moved, $this->set->move_up($forum_id));

		$result = $this->db->sql_query("SELECT forum_id, parent_id, left_id, right_id
			FROM phpbb_forums
			ORDER BY left_id, forum_id ASC");
		$this->assertEquals($expected, $this->db->sql_fetchrowset($result));
	}

	public function move_children_data()
	{
		return array(
			array('Item has no children',
				2, 1, false, array(
				array('forum_id' => 1, 'parent_id' => 0, 'left_id' => 1, 'right_id' => 6, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 2, 'parent_id' => 1, 'left_id' => 2, 'right_id' => 3, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 3, 'parent_id' => 1, 'left_id' => 4, 'right_id' => 5, 'forum_parents' => 'a:0:{}'),

				array('forum_id' => 4, 'parent_id' => 0, 'left_id' => 7, 'right_id' => 12, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 5, 'parent_id' => 4, 'left_id' => 8, 'right_id' => 11, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 6, 'parent_id' => 5, 'left_id' => 9, 'right_id' => 10, 'forum_parents' => 'a:0:{}'),

				array('forum_id' => 7, 'parent_id' => 0, 'left_id' => 13, 'right_id' => 22, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 8, 'parent_id' => 7, 'left_id' => 14, 'right_id' => 15, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 9, 'parent_id' => 7, 'left_id' => 16, 'right_id' => 19, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 10, 'parent_id' => 9, 'left_id' => 17, 'right_id' => 18, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 11, 'parent_id' => 7, 'left_id' => 20, 'right_id' => 21, 'forum_parents' => 'a:0:{}'),
			)),
			array('Move to same parent',
				4, 4, false, array(
				array('forum_id' => 1, 'parent_id' => 0, 'left_id' => 1, 'right_id' => 6, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 2, 'parent_id' => 1, 'left_id' => 2, 'right_id' => 3, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 3, 'parent_id' => 1, 'left_id' => 4, 'right_id' => 5, 'forum_parents' => 'a:0:{}'),

				array('forum_id' => 4, 'parent_id' => 0, 'left_id' => 7, 'right_id' => 12, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 5, 'parent_id' => 4, 'left_id' => 8, 'right_id' => 11, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 6, 'parent_id' => 5, 'left_id' => 9, 'right_id' => 10, 'forum_parents' => 'a:0:{}'),

				array('forum_id' => 7, 'parent_id' => 0, 'left_id' => 13, 'right_id' => 22, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 8, 'parent_id' => 7, 'left_id' => 14, 'right_id' => 15, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 9, 'parent_id' => 7, 'left_id' => 16, 'right_id' => 19, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 10, 'parent_id' => 9, 'left_id' => 17, 'right_id' => 18, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 11, 'parent_id' => 7, 'left_id' => 20, 'right_id' => 21, 'forum_parents' => 'a:0:{}'),
			)),
			array('Move single child up',
				5, 1, true, array(
				array('forum_id' => 1, 'parent_id' => 0, 'left_id' => 1, 'right_id' => 8, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 2, 'parent_id' => 1, 'left_id' => 2, 'right_id' => 3, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 3, 'parent_id' => 1, 'left_id' => 4, 'right_id' => 5, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 6, 'parent_id' => 1, 'left_id' => 6, 'right_id' => 7, 'forum_parents' => ''),

				array('forum_id' => 4, 'parent_id' => 0, 'left_id' => 9, 'right_id' => 12, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 5, 'parent_id' => 4, 'left_id' => 10, 'right_id' => 11, 'forum_parents' => 'a:0:{}'),

				array('forum_id' => 7, 'parent_id' => 0, 'left_id' => 13, 'right_id' => 22, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 8, 'parent_id' => 7, 'left_id' => 14, 'right_id' => 15, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 9, 'parent_id' => 7, 'left_id' => 16, 'right_id' => 19, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 10, 'parent_id' => 9, 'left_id' => 17, 'right_id' => 18, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 11, 'parent_id' => 7, 'left_id' => 20, 'right_id' => 21, 'forum_parents' => 'a:0:{}'),
			)),
			array('Move nested children up',
				4, 1, true, array(
				array('forum_id' => 1, 'parent_id' => 0, 'left_id' => 1, 'right_id' => 10, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 2, 'parent_id' => 1, 'left_id' => 2, 'right_id' => 3, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 3, 'parent_id' => 1, 'left_id' => 4, 'right_id' => 5, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 5, 'parent_id' => 1, 'left_id' => 6, 'right_id' => 9, 'forum_parents' => ''),
				array('forum_id' => 6, 'parent_id' => 5, 'left_id' => 7, 'right_id' => 8, 'forum_parents' => ''),

				array('forum_id' => 4, 'parent_id' => 0, 'left_id' => 11, 'right_id' => 12, 'forum_parents' => 'a:0:{}'),

				array('forum_id' => 7, 'parent_id' => 0, 'left_id' => 13, 'right_id' => 22, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 8, 'parent_id' => 7, 'left_id' => 14, 'right_id' => 15, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 9, 'parent_id' => 7, 'left_id' => 16, 'right_id' => 19, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 10, 'parent_id' => 9, 'left_id' => 17, 'right_id' => 18, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 11, 'parent_id' => 7, 'left_id' => 20, 'right_id' => 21, 'forum_parents' => 'a:0:{}'),
			)),
			array('Move single child down',
				5, 7, true, array(
				array('forum_id' => 1, 'parent_id' => 0, 'left_id' => 1, 'right_id' => 6, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 2, 'parent_id' => 1, 'left_id' => 2, 'right_id' => 3, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 3, 'parent_id' => 1, 'left_id' => 4, 'right_id' => 5, 'forum_parents' => 'a:0:{}'),

				array('forum_id' => 4, 'parent_id' => 0, 'left_id' => 7, 'right_id' => 10, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 5, 'parent_id' => 4, 'left_id' => 8, 'right_id' => 9, 'forum_parents' => 'a:0:{}'),

				array('forum_id' => 7, 'parent_id' => 0, 'left_id' => 11, 'right_id' => 22, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 8, 'parent_id' => 7, 'left_id' => 12, 'right_id' => 13, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 9, 'parent_id' => 7, 'left_id' => 14, 'right_id' => 17, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 10, 'parent_id' => 9, 'left_id' => 15, 'right_id' => 16, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 11, 'parent_id' => 7, 'left_id' => 18, 'right_id' => 19, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 6, 'parent_id' => 7, 'left_id' => 20, 'right_id' => 21, 'forum_parents' => ''),

			)),
			array('Move nested children down',
				4, 7, true, array(
				array('forum_id' => 1, 'parent_id' => 0, 'left_id' => 1, 'right_id' => 6, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 2, 'parent_id' => 1, 'left_id' => 2, 'right_id' => 3, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 3, 'parent_id' => 1, 'left_id' => 4, 'right_id' => 5, 'forum_parents' => 'a:0:{}'),

				array('forum_id' => 4, 'parent_id' => 0, 'left_id' => 7, 'right_id' => 8, 'forum_parents' => 'a:0:{}'),

				array('forum_id' => 7, 'parent_id' => 0, 'left_id' => 9, 'right_id' => 22, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 8, 'parent_id' => 7, 'left_id' => 10, 'right_id' => 11, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 9, 'parent_id' => 7, 'left_id' => 12, 'right_id' => 15, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 10, 'parent_id' => 9, 'left_id' => 13, 'right_id' => 14, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 11, 'parent_id' => 7, 'left_id' => 16, 'right_id' => 17, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 5, 'parent_id' => 7, 'left_id' => 18, 'right_id' => 21, 'forum_parents' => ''),
				array('forum_id' => 6, 'parent_id' => 5, 'left_id' => 19, 'right_id' => 20, 'forum_parents' => ''),
			)),
			array('Move single child to parent 0',
				5, 0, true, array(
				array('forum_id' => 1, 'parent_id' => 0, 'left_id' => 1, 'right_id' => 6, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 2, 'parent_id' => 1, 'left_id' => 2, 'right_id' => 3, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 3, 'parent_id' => 1, 'left_id' => 4, 'right_id' => 5, 'forum_parents' => 'a:0:{}'),

				array('forum_id' => 4, 'parent_id' => 0, 'left_id' => 7, 'right_id' => 10, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 5, 'parent_id' => 4, 'left_id' => 8, 'right_id' => 9, 'forum_parents' => 'a:0:{}'),

				array('forum_id' => 7, 'parent_id' => 0, 'left_id' => 11, 'right_id' => 20, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 8, 'parent_id' => 7, 'left_id' => 12, 'right_id' => 13, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 9, 'parent_id' => 7, 'left_id' => 14, 'right_id' => 17, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 10, 'parent_id' => 9, 'left_id' => 15, 'right_id' => 16, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 11, 'parent_id' => 7, 'left_id' => 18, 'right_id' => 19, 'forum_parents' => 'a:0:{}'),

				array('forum_id' => 6, 'parent_id' => 0, 'left_id' => 21, 'right_id' => 22, 'forum_parents' => ''),
			)),
			array('Move nested children to parent 0',
				4, 0, true, array(
				array('forum_id' => 1, 'parent_id' => 0, 'left_id' => 1, 'right_id' => 6, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 2, 'parent_id' => 1, 'left_id' => 2, 'right_id' => 3, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 3, 'parent_id' => 1, 'left_id' => 4, 'right_id' => 5, 'forum_parents' => 'a:0:{}'),

				array('forum_id' => 4, 'parent_id' => 0, 'left_id' => 7, 'right_id' => 8, 'forum_parents' => 'a:0:{}'),

				array('forum_id' => 7, 'parent_id' => 0, 'left_id' => 9, 'right_id' => 18, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 8, 'parent_id' => 7, 'left_id' => 10, 'right_id' => 11, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 9, 'parent_id' => 7, 'left_id' => 12, 'right_id' => 15, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 10, 'parent_id' => 9, 'left_id' => 13, 'right_id' => 14, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 11, 'parent_id' => 7, 'left_id' => 16, 'right_id' => 17, 'forum_parents' => 'a:0:{}'),

				array('forum_id' => 5, 'parent_id' => 0, 'left_id' => 19, 'right_id' => 22, 'forum_parents' => ''),
				array('forum_id' => 6, 'parent_id' => 5, 'left_id' => 20, 'right_id' => 21, 'forum_parents' => ''),
			)),
		);
	}

	/**
	* @dataProvider move_children_data
	*/
	public function test_move_children($explain, $forum_id, $target_id, $expected_moved, $expected)
	{
		$this->assertEquals($expected_moved, $this->set->move_children($forum_id, $target_id));

		$result = $this->db->sql_query("SELECT forum_id, parent_id, left_id, right_id, forum_parents
			FROM phpbb_forums
			ORDER BY left_id, forum_id ASC");
		$this->assertEquals($expected, $this->db->sql_fetchrowset($result));
	}

	public function move_children_throws_item_data()
	{
		return array(
			array('Item 0 does not exist', 0, 5),
			array('Item does not exist', 200, 5),
		);
	}

	/**
	* @dataProvider move_children_throws_item_data
	*
	* @expectedException			OutOfBoundsException
	* @expectedExceptionMessage		FORUM_NESTEDSET_INVALID_ITEM
	*/
	public function test_move_children_throws_item($explain, $forum_id, $target_id)
	{
		$this->set->move_children($forum_id, $target_id);
	}

	public function move_children_throws_parent_data()
	{
		return array(
			array('New parent is child', 4, 5),
			array('New parent is child 2', 7, 9),
			array('New parent does not exist', 1, 200),
		);
	}

	/**
	* @dataProvider move_children_throws_parent_data
	*
	* @expectedException			OutOfBoundsException
	* @expectedExceptionMessage		FORUM_NESTEDSET_INVALID_PARENT
	*/
	public function test_move_children_throws_parent($explain, $forum_id, $target_id)
	{
		$this->set->move_children($forum_id, $target_id);
	}

	public function change_parent_data()
	{
		return array(
			array('Move single child up',
				6, 1, true, array(
				array('forum_id' => 1, 'parent_id' => 0, 'left_id' => 1, 'right_id' => 8, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 2, 'parent_id' => 1, 'left_id' => 2, 'right_id' => 3, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 3, 'parent_id' => 1, 'left_id' => 4, 'right_id' => 5, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 6, 'parent_id' => 1, 'left_id' => 6, 'right_id' => 7, 'forum_parents' => ''),

				array('forum_id' => 4, 'parent_id' => 0, 'left_id' => 9, 'right_id' => 12, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 5, 'parent_id' => 4, 'left_id' => 10, 'right_id' => 11, 'forum_parents' => 'a:0:{}'),

				array('forum_id' => 7, 'parent_id' => 0, 'left_id' => 13, 'right_id' => 22, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 8, 'parent_id' => 7, 'left_id' => 14, 'right_id' => 15, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 9, 'parent_id' => 7, 'left_id' => 16, 'right_id' => 19, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 10, 'parent_id' => 9, 'left_id' => 17, 'right_id' => 18, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 11, 'parent_id' => 7, 'left_id' => 20, 'right_id' => 21, 'forum_parents' => 'a:0:{}'),
			)),
			array('Move nested children up',
				5, 1, true, array(
				array('forum_id' => 1, 'parent_id' => 0, 'left_id' => 1, 'right_id' => 10, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 2, 'parent_id' => 1, 'left_id' => 2, 'right_id' => 3, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 3, 'parent_id' => 1, 'left_id' => 4, 'right_id' => 5, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 5, 'parent_id' => 1, 'left_id' => 6, 'right_id' => 9, 'forum_parents' => ''),
				array('forum_id' => 6, 'parent_id' => 5, 'left_id' => 7, 'right_id' => 8, 'forum_parents' => ''),

				array('forum_id' => 4, 'parent_id' => 0, 'left_id' => 11, 'right_id' => 12, 'forum_parents' => 'a:0:{}'),

				array('forum_id' => 7, 'parent_id' => 0, 'left_id' => 13, 'right_id' => 22, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 8, 'parent_id' => 7, 'left_id' => 14, 'right_id' => 15, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 9, 'parent_id' => 7, 'left_id' => 16, 'right_id' => 19, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 10, 'parent_id' => 9, 'left_id' => 17, 'right_id' => 18, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 11, 'parent_id' => 7, 'left_id' => 20, 'right_id' => 21, 'forum_parents' => 'a:0:{}'),
			)),
			array('Move single child down',
				6, 7, true, array(
				array('forum_id' => 1, 'parent_id' => 0, 'left_id' => 1, 'right_id' => 6, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 2, 'parent_id' => 1, 'left_id' => 2, 'right_id' => 3, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 3, 'parent_id' => 1, 'left_id' => 4, 'right_id' => 5, 'forum_parents' => 'a:0:{}'),

				array('forum_id' => 4, 'parent_id' => 0, 'left_id' => 7, 'right_id' => 10, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 5, 'parent_id' => 4, 'left_id' => 8, 'right_id' => 9, 'forum_parents' => 'a:0:{}'),

				array('forum_id' => 7, 'parent_id' => 0, 'left_id' => 11, 'right_id' => 22, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 8, 'parent_id' => 7, 'left_id' => 12, 'right_id' => 13, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 9, 'parent_id' => 7, 'left_id' => 14, 'right_id' => 17, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 10, 'parent_id' => 9, 'left_id' => 15, 'right_id' => 16, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 11, 'parent_id' => 7, 'left_id' => 18, 'right_id' => 19, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 6, 'parent_id' => 7, 'left_id' => 20, 'right_id' => 21, 'forum_parents' => ''),
			)),
			array('Move nested children down',
				5, 7, true, array(
				array('forum_id' => 1, 'parent_id' => 0, 'left_id' => 1, 'right_id' => 6, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 2, 'parent_id' => 1, 'left_id' => 2, 'right_id' => 3, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 3, 'parent_id' => 1, 'left_id' => 4, 'right_id' => 5, 'forum_parents' => 'a:0:{}'),

				array('forum_id' => 4, 'parent_id' => 0, 'left_id' => 7, 'right_id' => 8, 'forum_parents' => 'a:0:{}'),

				array('forum_id' => 7, 'parent_id' => 0, 'left_id' => 9, 'right_id' => 22, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 8, 'parent_id' => 7, 'left_id' => 10, 'right_id' => 11, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 9, 'parent_id' => 7, 'left_id' => 12, 'right_id' => 15, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 10, 'parent_id' => 9, 'left_id' => 13, 'right_id' => 14, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 11, 'parent_id' => 7, 'left_id' => 16, 'right_id' => 17, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 5, 'parent_id' => 7, 'left_id' => 18, 'right_id' => 21, 'forum_parents' => ''),
				array('forum_id' => 6, 'parent_id' => 5, 'left_id' => 19, 'right_id' => 20, 'forum_parents' => ''),
			)),
			array('Move single child to parent 0',
				6, 0, true, array(
				array('forum_id' => 1, 'parent_id' => 0, 'left_id' => 1, 'right_id' => 6, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 2, 'parent_id' => 1, 'left_id' => 2, 'right_id' => 3, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 3, 'parent_id' => 1, 'left_id' => 4, 'right_id' => 5, 'forum_parents' => 'a:0:{}'),

				array('forum_id' => 4, 'parent_id' => 0, 'left_id' => 7, 'right_id' => 10, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 5, 'parent_id' => 4, 'left_id' => 8, 'right_id' => 9, 'forum_parents' => 'a:0:{}'),

				array('forum_id' => 7, 'parent_id' => 0, 'left_id' => 11, 'right_id' => 20, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 8, 'parent_id' => 7, 'left_id' => 12, 'right_id' => 13, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 9, 'parent_id' => 7, 'left_id' => 14, 'right_id' => 17, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 10, 'parent_id' => 9, 'left_id' => 15, 'right_id' => 16, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 11, 'parent_id' => 7, 'left_id' => 18, 'right_id' => 19, 'forum_parents' => 'a:0:{}'),

				array('forum_id' => 6, 'parent_id' => 0, 'left_id' => 21, 'right_id' => 22, 'forum_parents' => ''),
			)),
			array('Move nested children to parent 0',
				5, 0, true, array(
				array('forum_id' => 1, 'parent_id' => 0, 'left_id' => 1, 'right_id' => 6, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 2, 'parent_id' => 1, 'left_id' => 2, 'right_id' => 3, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 3, 'parent_id' => 1, 'left_id' => 4, 'right_id' => 5, 'forum_parents' => 'a:0:{}'),

				array('forum_id' => 4, 'parent_id' => 0, 'left_id' => 7, 'right_id' => 8, 'forum_parents' => 'a:0:{}'),

				array('forum_id' => 7, 'parent_id' => 0, 'left_id' => 9, 'right_id' => 18, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 8, 'parent_id' => 7, 'left_id' => 10, 'right_id' => 11, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 9, 'parent_id' => 7, 'left_id' => 12, 'right_id' => 15, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 10, 'parent_id' => 9, 'left_id' => 13, 'right_id' => 14, 'forum_parents' => 'a:0:{}'),
				array('forum_id' => 11, 'parent_id' => 7, 'left_id' => 16, 'right_id' => 17, 'forum_parents' => 'a:0:{}'),

				array('forum_id' => 5, 'parent_id' => 0, 'left_id' => 19, 'right_id' => 22, 'forum_parents' => ''),
				array('forum_id' => 6, 'parent_id' => 5, 'left_id' => 20, 'right_id' => 21, 'forum_parents' => ''),
			)),
		);
	}

	/**
	* @dataProvider change_parent_data
	*/
	public function test_change_parent($explain, $forum_id, $target_id, $expected_moved, $expected)
	{
		$this->assertEquals($expected_moved, $this->set->change_parent($forum_id, $target_id));

		$result = $this->db->sql_query("SELECT forum_id, parent_id, left_id, right_id, forum_parents
			FROM phpbb_forums
			ORDER BY left_id, forum_id ASC");
		$this->assertEquals($expected, $this->db->sql_fetchrowset($result));
	}

	public function change_parent_throws_item_data()
	{
		return array(
			array('Item 0 does not exist', 0, 5),
			array('Item does not exist', 200, 5),
		);
	}

	/**
	* @dataProvider change_parent_throws_item_data
	*
	* @expectedException			OutOfBoundsException
	* @expectedExceptionMessage		FORUM_NESTEDSET_INVALID_ITEM
	*/
	public function test_change_parent_throws_item($explain, $forum_id, $target_id)
	{
		$this->set->change_parent($forum_id, $target_id);
	}

	public function change_parent_throws_parent_data()
	{
		return array(
			array('New parent is child', 4, 5),
			array('New parent is child 2', 7, 9),
			array('New parent does not exist', 1, 200),
		);
	}

	/**
	* @dataProvider change_parent_throws_parent_data
	*
	* @expectedException			OutOfBoundsException
	* @expectedExceptionMessage		FORUM_NESTEDSET_INVALID_PARENT
	*/
	public function test_change_parent_throws_parent($explain, $forum_id, $target_id)
	{
		$this->set->change_parent($forum_id, $target_id);
	}
}
