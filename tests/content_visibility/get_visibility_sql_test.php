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
			array(
				'phpbb_posts',
				'post', 1, '',
				array(
					array('m_approve', 1, true),
				),
				array(
					array('post_id' => 1),
					array('post_id' => 2),
					array('post_id' => 3),
				),
			),
			array(
				'phpbb_posts',
				'post', 1, '',
				array(
				),
				array(
					array('post_id' => 2),
				),
			),
			array(
				'phpbb_topics',
				'topic', 1, '',
				array(
					array('m_approve', 1, true),
				),
				array(
					array('topic_id' => 1),
					array('topic_id' => 2),
					array('topic_id' => 3),
				),
			),
			array(
				'phpbb_topics',
				'topic', 1, '',
				array(),
				array(
					array('topic_id' => 2),
				),
			),
		);
	}

	/**
	* @dataProvider get_visibility_sql_data
	*/
	public function test_get_visibility_sql($table, $mode, $forum_id, $table_alias, $permissions, $expected)
	{
		global $cache, $db, $auth, $phpbb_root_path, $phpEx;

		$cache = new phpbb_mock_cache;
		$db = $this->new_dbal();

		// Create auth mock
		$auth = $this->getMock('\phpbb\auth\auth');
		$auth->expects($this->any())
			->method('acl_get')
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
			WHERE ' . $content_visibility->get_visibility_sql($mode, $forum_id, $table_alias) . '
			ORDER BY ' . $mode . '_id ASC');

		$this->assertEquals($expected, $db->sql_fetchrowset($result));
	}
}
