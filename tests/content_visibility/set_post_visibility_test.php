<?php
/**
*
* @package testing
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/functions_admin.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/functions_content.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/functions_posting.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/utf/utf_tools.php';

class phpbb_content_visibility_set_post_visibility_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/set_post_visibility.xml');
	}

	public function set_post_visibility_data()
	{
		return array(
			array(
				ITEM_APPROVED,
				1, 1, 1,
				2, time(), 'approve',
				true, false,
				array(
					array('post_id' => 1, 'post_visibility' => 1, 'post_delete_reason' => 'approve'),
					array('post_id' => 2, 'post_visibility' => 1, 'post_delete_reason' => ''),
					array('post_id' => 3, 'post_visibility' => 2, 'post_delete_reason' => ''),
				),
				array(
					array('topic_visibility' => 1, 'topic_first_post_id' => 1, 'topic_last_post_id' => 2),
				),
			),
			array(
				ITEM_APPROVED,
				3, 1, 1,
				2, time(), 'approve',
				false, true,
				array(
					array('post_id' => 1, 'post_visibility' => 0, 'post_delete_reason' => ''),
					array('post_id' => 2, 'post_visibility' => 1, 'post_delete_reason' => ''),
					array('post_id' => 3, 'post_visibility' => 1, 'post_delete_reason' => 'approve'),
				),
				array(
					array('topic_visibility' => 1, 'topic_first_post_id' => 2, 'topic_last_post_id' => 3),
				),
			),
			array(
				ITEM_DELETED,
				2, 1, 1,
				2, time(), 'deleted',
				true, true,
				array(
					array('post_id' => 1, 'post_visibility' => 0, 'post_delete_reason' => ''),
					array('post_id' => 2, 'post_visibility' => 2, 'post_delete_reason' => 'deleted'),
					array('post_id' => 3, 'post_visibility' => 2, 'post_delete_reason' => ''),
				),
				array(
					array('topic_visibility' => 2, 'topic_first_post_id' => 1, 'topic_last_post_id' => 3),
				),
			),
			array(
				ITEM_DELETED,
				5, 2, 1,
				2, time(), 'deleted',
				true, false,
				array(
					array('post_id' => 4, 'post_visibility' => 0, 'post_delete_reason' => ''),
					array('post_id' => 5, 'post_visibility' => 2, 'post_delete_reason' => 'deleted'),
					array('post_id' => 6, 'post_visibility' => 1, 'post_delete_reason' => ''),
					array('post_id' => 7, 'post_visibility' => 2, 'post_delete_reason' => ''),
				),
				array(
					array('topic_visibility' => 1, 'topic_first_post_id' => 6, 'topic_last_post_id' => 6),
				),
			),
			array(
				ITEM_DELETED,
				6, 2, 1,
				2, time(), 'deleted',
				false, true,
				array(
					array('post_id' => 4, 'post_visibility' => 0, 'post_delete_reason' => ''),
					array('post_id' => 5, 'post_visibility' => 1, 'post_delete_reason' => ''),
					array('post_id' => 6, 'post_visibility' => 2, 'post_delete_reason' => 'deleted'),
					array('post_id' => 7, 'post_visibility' => 2, 'post_delete_reason' => ''),
				),
				array(
					array('topic_visibility' => 1, 'topic_first_post_id' => 5, 'topic_last_post_id' => 5),
				),
			),
			array(
				ITEM_DELETED,
				8, 3, 1,
				2, time(), 'deleted',
				true, true,
				array(
					array('post_id' => 8, 'post_visibility' => 2, 'post_delete_reason' => 'deleted'),
				),
				array(
					array('topic_visibility' => 2, 'topic_first_post_id' => 8, 'topic_last_post_id' => 8),
				),
			),
		);
	}

	/**
	* @dataProvider set_post_visibility_data
	*/
	public function test_set_post_visibility($visibility, $post_id, $topic_id, $forum_id, $user_id, $time, $reason, $is_starter, $is_latest, $expected, $expected_topic)
	{
		global $cache, $db, $auth, $phpbb_root_path, $phpEx;

		$cache = new phpbb_mock_cache;
		$db = $this->new_dbal();
		$auth = $this->getMock('phpbb_auth');
		$user = $this->getMock('phpbb_user');
		$content_visibility = new phpbb_content_visibility($auth, $db, $user, $phpbb_root_path, $phpEx, FORUMS_TABLE, POSTS_TABLE, TOPICS_TABLE, USERS_TABLE);

		$content_visibility->set_post_visibility($visibility, $post_id, $topic_id, $forum_id, $user_id, $time, $reason, $is_starter, $is_latest);

		$result = $db->sql_query('SELECT post_id, post_visibility, post_delete_reason
			FROM phpbb_posts
			WHERE topic_id = ' . $topic_id . '
			ORDER BY post_id ASC');

		$this->assertEquals($expected, $db->sql_fetchrowset($result));
		$db->sql_freeresult($result);

		$result = $db->sql_query('SELECT topic_visibility, topic_first_post_id, topic_last_post_id
			FROM phpbb_topics
			WHERE topic_id = ' . $topic_id);

		$this->assertEquals($expected_topic, $db->sql_fetchrowset($result));
		$db->sql_freeresult($result);
	}
}
