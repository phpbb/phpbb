<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

class phpbb_tests_nestedset_item_forum_test extends phpbb_test_case
{
	public function test_item_forum_constructor()
	{
		$forum_data = array(
			'parent_id'		=> 1,
			'forum_id'		=> 5,
			'user_id'		=> 32,
			'left_id'		=> 2,
			'right_id'		=> 3,
			'forum_parents'	=> '',
		);

		$forum = new phpbb_nestedset_item_forum($forum_data);

		$this->assertEquals($forum->get_item_id(), $forum_data['forum_id']);
		$this->assertEquals($forum->get_left_id(), $forum_data['left_id']);
		$this->assertEquals($forum->get_right_id(), $forum_data['right_id']);
		$this->assertEquals($forum->get_parent_id(), $forum_data['parent_id']);
		$this->assertEquals($forum->get_item_parents_data(), $forum_data['forum_parents']);
	}
}
