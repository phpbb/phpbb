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
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/get_visibility_sql.xml');
	}

	public function get_visibility_sql_data()
	{
		return array(
			// data set 0: allow_drafts=0, moderator, can see all posts except drafts
			array(
				'phpbb_posts',
				0,
				0,
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
			// data set 1: allow_drafts=0, normal user, cannot see any draft posts
			array(
				'phpbb_posts',
				0,
				0,
				'post', 1, '',
				array(
				),
				array(
					array('post_id' => 2),
				),
			),
			// data set 2: allow_drafts=0, moderator, can see all topics except draft
			array(
				'phpbb_topics',
				0,
				0,
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
			// data set 3: allow_drafts=0, normal user, cannot see draft posts topic
			array(
				'phpbb_topics',
				0,
				0,
				'topic', 1, '',
				array(),
				array(
					array('topic_id' => 2),
				),
			),
			// data set 4: allow_drafts=1, guest user, cannot see draft posts
			array(
				'phpbb_posts',
				1,
				1,
				'post', 1, '',
				array(
				),
				array(
					array('post_id' => 2),
				),
			),
			// data set 5: allow_drafts=1, guest user, cannot see draft posts topic
			array(
				'phpbb_topics',
				1,
				1,
				'topic', 1, '',
				array(),
				array(
					array('topic_id' => 2),
				),
			),
			// data set 6: allow_drafts=0, normal user, does not see own draft posts
			array(
				'phpbb_posts',
				0,
				0,
				'post', 1, '',
				array(),
				array(
					array('post_id' => 2),
				),
			),
			// data set 7: allow_drafts=1, normal user, can see own draft topic
			array(
				'phpbb_topics',
				0,
				1,
				'topic', 1, '',
				array(),
				array(
					array('topic_id' => 2),
					array('topic_id' => 5),
				),
			),
			// data set 8: allow_drafts=1, normal user, can see own draft posts
			array(
				'phpbb_posts',
				0,
				1,
				'post', 1, '',
				array(),
				array(
					array('post_id' => 2),
					array('post_id' => 5),
					array('post_id' => 6),
				),
			),
		);
	}

	/**
	* @dataProvider get_visibility_sql_data
	*/
	public function test_get_visibility_sql($table, $user_id, $allow_drafts, $mode, $forum_id, $table_alias, $permissions, $expected)
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
		$config = new phpbb\config\config(array());
		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();
		$content_visibility = new \phpbb\content_visibility($auth, $config, $phpbb_dispatcher, $db, $user, $phpbb_root_path, $phpEx, FORUMS_TABLE, POSTS_TABLE, TOPICS_TABLE, USERS_TABLE);

		$sql = 'SELECT ' . $mode . '_id
			FROM ' . $table . '
			WHERE ' . $content_visibility->get_visibility_sql($mode, $forum_id, $table_alias, $allow_drafts) . '
			ORDER BY ' . $mode . '_id ASC';
		$result = $db->sql_query($sql);

		$this->assertEquals($expected, $db->sql_fetchrowset($result));
		$db->sql_freeresult($result);
	}
}
