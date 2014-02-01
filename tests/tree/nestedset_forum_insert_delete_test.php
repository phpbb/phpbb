<?php
/**
*
* @package tree
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/nestedset_forum_base.php';

class phpbb_tests_tree_nestedset_forum_add_remove_test extends phpbb_tests_tree_nestedset_forum_base
{
	public function delete_data()
	{
		return array(
			array(1, array(1, 2, 3), array(
				array('forum_id' => 4, 'parent_id' => 0, 'left_id' => 1, 'right_id' => 6),
				array('forum_id' => 5, 'parent_id' => 4, 'left_id' => 2, 'right_id' => 5),
				array('forum_id' => 6, 'parent_id' => 5, 'left_id' => 3, 'right_id' => 4),
				array('forum_id' => 7, 'parent_id' => 0, 'left_id' => 7, 'right_id' => 16),
				array('forum_id' => 8, 'parent_id' => 7, 'left_id' => 8, 'right_id' => 9),
				array('forum_id' => 9, 'parent_id' => 7, 'left_id' => 10, 'right_id' => 13),
				array('forum_id' => 10, 'parent_id' => 9, 'left_id' => 11, 'right_id' => 12),
				array('forum_id' => 11, 'parent_id' => 7, 'left_id' => 14, 'right_id' => 15),
			)),
			array(2, array(2), array(
				array('forum_id' => 1, 'parent_id' => 0, 'left_id' => 1, 'right_id' => 4),
				array('forum_id' => 3, 'parent_id' => 1, 'left_id' => 2, 'right_id' => 3),
				array('forum_id' => 4, 'parent_id' => 0, 'left_id' => 5, 'right_id' => 10),
				array('forum_id' => 5, 'parent_id' => 4, 'left_id' => 6, 'right_id' => 9),
				array('forum_id' => 6, 'parent_id' => 5, 'left_id' => 7, 'right_id' => 8),
				array('forum_id' => 7, 'parent_id' => 0, 'left_id' => 11, 'right_id' => 20),
				array('forum_id' => 8, 'parent_id' => 7, 'left_id' => 12, 'right_id' => 13),
				array('forum_id' => 9, 'parent_id' => 7, 'left_id' => 14, 'right_id' => 17),
				array('forum_id' => 10, 'parent_id' => 9, 'left_id' => 15, 'right_id' => 16),
				array('forum_id' => 11, 'parent_id' => 7, 'left_id' => 18, 'right_id' => 19),
			)),
		);
	}

	/**
	* @dataProvider delete_data
	*/
	public function test_delete($forum_id, $expected_deleted, $expected)
	{
		$this->assertEquals($expected_deleted, $this->set->delete($forum_id));

		$result = $this->db->sql_query("SELECT forum_id, parent_id, left_id, right_id
			FROM phpbb_forums
			ORDER BY left_id, forum_id ASC");
		$this->assertEquals($expected, $this->db->sql_fetchrowset($result));
	}

	public function delete_throws_data()
	{
		return array(
			array('Not an item', 0),
			array('Item does not exist', 200),
		);
	}

	/**
	* @dataProvider delete_throws_data
	*
	* @expectedException			OutOfBoundsException
	* @expectedExceptionMessage		FORUM_NESTEDSET_INVALID_ITEM
	*/
	public function test_delete_throws($explain, $forum_id)
	{
		$this->set->delete($forum_id);
	}

	public function insert_data()
	{
		return array(
			array(array(
				'forum_desc'	=> '',
				'forum_rules'	=> '',
				'forum_id'		=> 12,
				'parent_id'		=> 0,
				'left_id'		=> 23,
				'right_id'		=> 24,
				'forum_parents'	=> '',
			), array(
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

				array('forum_id' => 12, 'parent_id' => 0, 'left_id' => 23, 'right_id' => 24),
			)),
		);
	}

	/**
	* @dataProvider insert_data
	*/
	public function test_insert($expected_data, $expected)
	{
		$this->assertEquals($expected_data, $this->set->insert(array(
			'forum_desc'	=> '',
			'forum_rules'	=> '',
		)));

		$result = $this->db->sql_query('SELECT forum_id, parent_id, left_id, right_id
			FROM phpbb_forums
			ORDER BY left_id, forum_id ASC');
		$this->assertEquals($expected, $this->db->sql_fetchrowset($result));
	}
}
