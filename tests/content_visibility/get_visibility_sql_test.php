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

class phpbb_content_visibility_get_visibility_sql_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/get_visibility_sql.xml');
	}

	public function get_visibility_sql_data()
	{
		return array(
			// data set 0: display_unapproved_posts=false, moderator, can see all posts
			array(
				'phpbb_posts',
				0,
				false,
				'post', 1, '',
				array(
					array('m_approve', 1, true),
				),
				array(
					array('post_id' => 1),
					array('post_id' => 2),
					array('post_id' => 3),
					array('post_id' => 4),
				),
			),
			// data set 1: display_unapproved_posts=false, normal user, cannot see any unapproved posts
			array(
				'phpbb_posts',
				0,
				false,
				'post', 1, '',
				array(
				),
				array(
					array('post_id' => 2),
				),
			),
			// data set 2: display_unapproved_posts=false, moderator, can see all topics
			array(
				'phpbb_topics',
				0,
				false,
				'topic', 1, '',
				array(
					array('m_approve', 1, true),
				),
				array(
					array('topic_id' => 1),
					array('topic_id' => 2),
					array('topic_id' => 3),
					array('topic_id' => 4),
				),
			),
			// data set 3: display_unapproved_posts=false, normal user, cannot see unapproved posts topic
			array(
				'phpbb_topics',
				0,
				false,
				'topic', 1, '',
				array(),
				array(
					array('topic_id' => 2),
				),
			),
			// data set 4: display_unapproved_posts=true, guest user, cannot see unapproved posts
			array(
				'phpbb_posts',
				1,
				true,
				'post', 1, '',
				array(
				),
				array(
					array('post_id' => 2),
				),
			),
			// data set 5: display_unapproved_posts=true, guest user, cannot see unapproved posts topic
			array(
				'phpbb_topics',
				1,
				true,
				'topic', 1, '',
				array(),
				array(
					array('topic_id' => 2),
				),
			),
			// data set 6: display_unapproved_posts=true, normal user, can see own unapproved posts
			array(
				'phpbb_posts',
				0,
				true,
				'post', 1, '',
				array(),
				array(
					array('post_id' => 1),
					array('post_id' => 2),
				),
			),
			// data set 7: display_unapproved_posts=true, normal user, can see own unapproved posts topic
			array(
				'phpbb_topics',
				0,
				true,
				'topic', 1, '',
				array(),
				array(
					array('topic_id' => 1),
					array('topic_id' => 2),
				),
			),
		);
	}

	/**
	* @dataProvider get_visibility_sql_data
	*/
	public function test_get_visibility_sql($table, $user_id, $display_unapproved, $mode, $forum_id, $table_alias, $permissions, $expected)
	{
		global $cache, $db, $auth, $phpbb_root_path, $phpEx;

		$cache = new phpbb_mock_cache;
		$db = $this->new_dbal();

		// Create auth mock
		$auth = $this->createMock('\phpbb\auth\auth');
		$auth->expects($this->any())
			->method('acl_get')
			->with($this->stringContains('_'), $this->anything())
			->will($this->returnValueMap($permissions));
		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$lang = new \phpbb\language\language($lang_loader);
		$user = new \phpbb\user($lang, '\phpbb\datetime');
		$user->data['user_id'] = $user_id;
		$config = new \phpbb\config\config(array(
			'display_unapproved_posts'			=> $display_unapproved,
		));
		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();
		$content_visibility = new \phpbb\content_visibility($auth, $config, $phpbb_dispatcher, $db, $user, $phpbb_root_path, $phpEx, FORUMS_TABLE, POSTS_TABLE, TOPICS_TABLE, USERS_TABLE);

		$sql = 'SELECT ' . $mode . '_id
			FROM ' . $table . '
			WHERE ' . $content_visibility->get_visibility_sql($mode, $forum_id, $table_alias) . '
			ORDER BY ' . $mode . '_id ASC';
		$result = $db->sql_query($sql);

		$this->assertEquals($expected, $db->sql_fetchrowset($result));
		$db->sql_freeresult($result);
	}
}
