<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/functions_admin.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/functions_content.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/functions_posting.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/utf/utf_tools.php';

class phpbb_content_visibility_set_topic_visibility_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/set_topic_visibility.xml');
	}

	public function set_topic_visibility_data()
	{
		return array(
			array(
				ITEM_DELETED, 1, 1,
				2, time(), 'delete', false,
				array(
					array('post_id' => 1, 'post_visibility' => 2, 'post_delete_reason' => ''),
					array('post_id' => 2, 'post_visibility' => 2, 'post_delete_reason' => 'manually'),
					array('post_id' => 3, 'post_visibility' => 0, 'post_delete_reason' => ''),
				),
				array(
					array('topic_visibility' => 2, 'topic_first_post_id' => 1, 'topic_last_post_id' => 3, 'topic_delete_reason' => 'delete'),
				),
			),
			array(
				ITEM_DELETED, 1, 1,
				2, time(), 'delete-forced', true,
				array(
					array('post_id' => 1, 'post_visibility' => 2, 'post_delete_reason' => ''),
					array('post_id' => 2, 'post_visibility' => 2, 'post_delete_reason' => ''),
					array('post_id' => 3, 'post_visibility' => 2, 'post_delete_reason' => ''),
				),
				array(
					array('topic_visibility' => 2, 'topic_first_post_id' => 1, 'topic_last_post_id' => 3, 'topic_delete_reason' => 'delete-forced'),
				),
			),
			array(
				ITEM_APPROVED, 2, 1,
				2, time(), 'approved', false,
				array(
					array('post_id' => 4, 'post_visibility' => 1, 'post_delete_reason' => ''),
					array('post_id' => 5, 'post_visibility' => 2, 'post_delete_reason' => 'manually'),
					array('post_id' => 6, 'post_visibility' => 0, 'post_delete_reason' => ''),
				),
				array(
					array('topic_visibility' => 1, 'topic_first_post_id' => 4, 'topic_last_post_id' => 4, 'topic_delete_reason' => 'approved'),
				),
			),
			array(
				ITEM_APPROVED, 2, 1,
				2, time(), 'approved-forced', true,
				array(
					array('post_id' => 4, 'post_visibility' => 1, 'post_delete_reason' => ''),
					array('post_id' => 5, 'post_visibility' => 1, 'post_delete_reason' => ''),
					array('post_id' => 6, 'post_visibility' => 1, 'post_delete_reason' => ''),
				),
				array(
					array('topic_visibility' => 1, 'topic_first_post_id' => 4, 'topic_last_post_id' => 6, 'topic_delete_reason' => 'approved-forced'),
				),
			),
		);
	}

	/**
	* @dataProvider set_topic_visibility_data
	*/
	public function test_set_topic_visibility($visibility, $topic_id, $forum_id, $user_id, $time, $reason, $force_update_all, $expected_posts, $expected_topic)
	{
		global $cache, $db, $auth, $phpbb_root_path, $phpEx;

		$cache = new phpbb_mock_cache;
		$db = $this->new_dbal();
		$auth = $this->getMock('\phpbb\auth\auth');
		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$lang = new \phpbb\language\language($lang_loader);
		$user = new \phpbb\user($lang, '\phpbb\datetime');
		$config = new phpbb\config\config(array());
		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();
		$content_visibility = new \phpbb\content_visibility($auth, $config, $phpbb_dispatcher, $db, $user, $phpbb_root_path, $phpEx, FORUMS_TABLE, POSTS_TABLE, TOPICS_TABLE, USERS_TABLE);

		$content_visibility->set_topic_visibility($visibility, $topic_id, $forum_id, $user_id, $time, $reason, $force_update_all);

		$result = $db->sql_query('SELECT post_id, post_visibility, post_delete_reason
			FROM phpbb_posts
			WHERE topic_id = ' . $topic_id . '
			ORDER BY post_id ASC');

		$this->assertEquals($expected_posts, $db->sql_fetchrowset($result));
		$db->sql_freeresult($result);

		$result = $db->sql_query('SELECT topic_visibility, topic_first_post_id, topic_last_post_id, topic_delete_reason
			FROM phpbb_topics
			WHERE topic_id = ' . $topic_id);

		$this->assertEquals($expected_topic, $db->sql_fetchrowset($result));
		$db->sql_freeresult($result);
	}
}
