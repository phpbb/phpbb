<?php
/**
*
* @package tree
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/nestedset_forum_base.php';

class phpbb_tests_tree_nestedset_forum_regenerate_test extends phpbb_tests_tree_nestedset_forum_base
{
	protected $fixed_set = array(
		array('forum_id' => 1, 'parent_id' => 0, 'left_id' => 1, 'right_id' => 6, 'forum_parents' => ''),
		array('forum_id' => 2, 'parent_id' => 1, 'left_id' => 2, 'right_id' => 3, 'forum_parents' => ''),
		array('forum_id' => 3, 'parent_id' => 1, 'left_id' => 4, 'right_id' => 5, 'forum_parents' => ''),

		array('forum_id' => 4, 'parent_id' => 0, 'left_id' => 7, 'right_id' => 12, 'forum_parents' => ''),
		array('forum_id' => 5, 'parent_id' => 4, 'left_id' => 8, 'right_id' => 11, 'forum_parents' => ''),
		array('forum_id' => 6, 'parent_id' => 5, 'left_id' => 9, 'right_id' => 10, 'forum_parents' => ''),

		array('forum_id' => 7, 'parent_id' => 0, 'left_id' => 13, 'right_id' => 22, 'forum_parents' => ''),
		array('forum_id' => 8, 'parent_id' => 7, 'left_id' => 14, 'right_id' => 15, 'forum_parents' => ''),
		array('forum_id' => 9, 'parent_id' => 7, 'left_id' => 16, 'right_id' => 19, 'forum_parents' => ''),
		array('forum_id' => 10, 'parent_id' => 9, 'left_id' => 17, 'right_id' => 18, 'forum_parents' => ''),
		array('forum_id' => 11, 'parent_id' => 7, 'left_id' => 20, 'right_id' => 21, 'forum_parents' => ''),
	);

	public function regenerate_left_right_ids_data()
	{
		return array(
			array('UPDATE phpbb_forums
				SET left_id = 0,
					right_id = 0', false),
			array('UPDATE phpbb_forums
				SET left_id = 28,
					right_id = 28
				WHERE left_id > 12', false),
			array('UPDATE phpbb_forums
				SET left_id = left_id * 2,
					right_id = right_id * 2', false),
			array('UPDATE phpbb_forums
				SET left_id = left_id * 2,
					right_id = right_id * 2
				WHERE left_id > 12', false),
			array('UPDATE phpbb_forums
				SET left_id = left_id - 4,
					right_id = right_id * 4
				WHERE left_id > 4', false),
			array('UPDATE phpbb_forums
				SET left_id = 0,
					right_id = 0
				WHERE left_id > 12', true),
		);
	}

	/**
	* @dataProvider regenerate_left_right_ids_data
	*/
	public function test_regenerate_left_right_ids($breaking_query, $reset_ids)
	{
		$result = $this->db->sql_query($breaking_query);

		$this->assertEquals(23, $this->set->regenerate_left_right_ids(1, 0, $reset_ids));

		$result = $this->db->sql_query('SELECT forum_id, parent_id, left_id, right_id, forum_parents
			FROM phpbb_forums
			ORDER BY left_id, forum_id ASC');
		$this->assertEquals($this->fixed_set, $this->db->sql_fetchrowset($result));
	}
}
