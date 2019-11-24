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

class phpbb_content_visibility_get_global_visibility_sql_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/get_forums_visibility_sql.xml');
	}

	public function get_global_visibility_sql_data()
	{
		return array(
			// data set 0: moderator, can see all topics except draft
			array(
				'phpbb_topics',
				'topic', 1, array(), '',
				array(
					array('m_approve', true, array(1 => true, 2 => true, 3 => true)),
				),
				array(
					array('topic_id' => 1),
					array('topic_id' => 2),
					array('topic_id' => 3),
					array('topic_id' => 4),
					array('topic_id' => 5),
					array('topic_id' => 6),
					array('topic_id' => 7),
					array('topic_id' => 8),
					array('topic_id' => 9),
				),
			),
			// data set 1: moderator, can see all topics, except draft
			array(
				'phpbb_topics',
				'topic', 1, array(3), '',
				array(
					array('m_approve', true, array(1 => true, 2 => true, 3 => true)),
				),
				array(
					array('topic_id' => 1),
					array('topic_id' => 2),
					array('topic_id' => 3),
					array('topic_id' => 4),
					array('topic_id' => 5),
					array('topic_id' => 6),
				),
			),
			// data set 2: moderator, can see all topics, except draft
			array(
				'phpbb_topics',
				'topic', 1, array(), '',
				array(
					array('m_approve', true, array(2 => true)),
				),
				array(
					array('topic_id' => 2),
					array('topic_id' => 4),
					array('topic_id' => 5),
					array('topic_id' => 6),
					array('topic_id' => 8),
				),
			),
			// data set 3: moderator, can see all posts except draft
			array(
				'phpbb_posts',
				'post', 1, array(), '',
				array(
					array('m_approve', true, array(1 => true, 2 => true, 3 => true)),
				),
				array(
					array('post_id' => 1),
					array('post_id' => 2),
					array('post_id' => 3),
					array('post_id' => 4),
					array('post_id' => 5),
					array('post_id' => 6),
					array('post_id' => 7),
					array('post_id' => 8),
					array('post_id' => 9),
					array('post_id' => 12),
				),
			),
			// data set 4: moderator, can see all posts except draft
			array(
				'phpbb_posts',
				'post', 1, array(3), '',
				array(
					array('m_approve', true, array(1 => true, 2 => true, 3 => true)),
				),
				array(
					array('post_id' => 1),
					array('post_id' => 2),
					array('post_id' => 3),
					array('post_id' => 4),
					array('post_id' => 5),
					array('post_id' => 6),
					array('post_id' => 12),
				),
			),
			// data set 5: moderator, can see all posts except draft
			array(
				'phpbb_posts',
				'post', 1, array(), '',
				array(
					array('m_approve', true, array(2 => true)),
				),
				array(
					array('post_id' => 2),
					array('post_id' => 4),
					array('post_id' => 5),
					array('post_id' => 6),
					array('post_id' => 8),
				),
			),
			// data set 6: moderator, can see only own draft posts
			array(
				'phpbb_posts',
				'post', 4, array(), '',
				array(
					array('m_approve', true, array(2 => true)),
				),
				array(
					array('post_id' => 10),
					array('post_id' => 11),
				),
			),
			// data set 7: moderator, can see only own draft topics
			array(
				'phpbb_topics',
				'topic', 4, array(), '',
				array(
					array('m_approve', true, array(1 => true, 2 => true, 3 => true)),
				),
				array(
					array('topic_id' => 10),
				),
			),
			// data set 8: normal user, can see all approved topics
			array(
				'phpbb_topics',
				'topic', 1, array(), '',
				array(
					array('m_approve', true, array()),
				),
				array(
					array('topic_id' => 2),
					array('topic_id' => 5),
					array('topic_id' => 8),
				),
			),
			// data set 9: normal user, can see all approved posts except forum 3
			array(
				'phpbb_posts',
				'post', 1, array(3), '',
				array(
					array('m_approve', true, array()),
				),
				array(
					array('post_id' => 2),
					array('post_id' => 5),
				),
			),
			// data set 10: normal user, can see only own draft posts
			array(
				'phpbb_posts',
				'post', 4, array(), '',
				array(
					array('m_approve', true, array()),
				),
				array(
					array('post_id' => 10),
					array('post_id' => 11),
				),
			),
			// data set 15: normal user, can see only own draft topics
			array(
				'phpbb_topics',
				'topic', 4, array(), '',
				array(
					array('m_approve', true, array()),
				),
				array(
					array('topic_id' => 10),
				),
			),
		);
	}

	/**
	* @dataProvider get_global_visibility_sql_data
	*/
	public function test_get_global_visibility_sql($table, $mode, $target_visibility, $forum_ids, $table_alias, $permissions, $expected)
	{
		global $cache, $db, $auth, $phpbb_root_path, $phpEx;

		$cache = new phpbb_mock_cache;
		$db = $this->new_dbal();

		// Create auth mock
		$auth = $this->createMock('\phpbb\auth\auth');
		$auth->expects($this->any())
			->method('acl_getf')
			->with($this->stringContains('_'), $this->anything())
			->will($this->returnValueMap($permissions));
		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$lang = new \phpbb\language\language($lang_loader);
		$user = new \phpbb\user($lang, '\phpbb\datetime');
		$config = new phpbb\config\config(array());
		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();
		$content_visibility = new \phpbb\content_visibility($auth, $config, $phpbb_dispatcher, $db, $user, $phpbb_root_path, $phpEx, FORUMS_TABLE, POSTS_TABLE, TOPICS_TABLE, USERS_TABLE);

		$result = $db->sql_query('SELECT ' . $mode . '_id
			FROM ' . $table . '
			WHERE ' . $content_visibility->get_global_visibility_sql($mode, $forum_ids, $table_alias, $target_visibility) . '
			ORDER BY ' . $mode . '_id ASC');

		$this->assertEquals($expected, $db->sql_fetchrowset($result));
	}
}
