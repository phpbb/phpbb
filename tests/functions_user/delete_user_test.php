<?php
/**
*
* @package testing
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/functions_user.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/utf/utf_tools.php';

class phpbb_functions_user_delete_user_test extends phpbb_database_test_case
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/delete_user.xml');
	}

	protected function setUp()
	{
		parent::setUp();

		global $cache, $config, $db, $phpbb_dispatcher, $phpbb_container;

		$db = $this->db = $this->new_dbal();
		$config = new \phpbb\config\config(array(
			'load_online_time'	=> 5,
			'search_type'		=> '\phpbb\search\fulltext_mysql',
		));
		$cache = new phpbb_mock_null_cache();
		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();
		$phpbb_container = new phpbb_mock_container_builder();
		$phpbb_container->set('notification_manager', new phpbb_mock_notification_manager());
		$phpbb_container->set(
			'auth.provider.db',
			new phpbb_mock_auth_provider()
		);
		$provider_collection = new \phpbb\auth\provider_collection($phpbb_container, $config);
		$provider_collection->add('auth.provider.db');
		$phpbb_container->set(
			'auth.provider_collection',
			$provider_collection
		);
	}

	 public function first_last_post_data()
	{
		return array(
			array(
				'retain', false,
				array(
					array('post_id' => 1, 'poster_id' => ANONYMOUS, 'post_username' => ''),
					array('post_id' => 2, 'poster_id' => ANONYMOUS, 'post_username' => 'Other'),
					array('post_id' => 3, 'poster_id' => ANONYMOUS, 'post_username' => ''),
					array('post_id' => 4, 'poster_id' => ANONYMOUS, 'post_username' => 'Other'),
				),
				array(
					array(
						'topic_id' => 1,
						'topic_poster' => ANONYMOUS, 'topic_first_poster_name' => '', 'topic_first_poster_colour' => '',
						'topic_last_poster_id' => ANONYMOUS, 'topic_last_poster_name' => '', 'topic_last_poster_colour' => '',
					),
					array(
						'topic_id' => 2,
						'topic_poster' => ANONYMOUS, 'topic_first_poster_name' => 'Other', 'topic_first_poster_colour' => '',
						'topic_last_poster_id' => ANONYMOUS, 'topic_last_poster_name' => 'Other', 'topic_last_poster_colour' => '',
					),
					array(
						'topic_id' => 3,
						'topic_poster' => ANONYMOUS, 'topic_first_poster_name' => '', 'topic_first_poster_colour' => '',
						'topic_last_poster_id' => ANONYMOUS, 'topic_last_poster_name' => '', 'topic_last_poster_colour' => '',
					),
					array(
						'topic_id' => 4,
						'topic_poster' => ANONYMOUS, 'topic_first_poster_name' => 'Other', 'topic_first_poster_colour' => '',
						'topic_last_poster_id' => ANONYMOUS, 'topic_last_poster_name' => 'Other', 'topic_last_poster_colour' => '',
					),
				),
				array(
					array('forum_id' => 1, 'forum_last_poster_id' => ANONYMOUS, 'forum_last_poster_name' => '', 'forum_last_poster_colour' => ''),
					array('forum_id' => 2, 'forum_last_poster_id' => ANONYMOUS, 'forum_last_poster_name' => 'Other', 'forum_last_poster_colour' => ''),
					array('forum_id' => 3, 'forum_last_poster_id' => ANONYMOUS, 'forum_last_poster_name' => '', 'forum_last_poster_colour' => ''),
					array('forum_id' => 4, 'forum_last_poster_id' => ANONYMOUS, 'forum_last_poster_name' => 'Other', 'forum_last_poster_colour' => ''),
				),
			),
			array(
				'remove', false,
				array(
					array('post_id' => 2, 'poster_id' => ANONYMOUS, 'post_username' => 'Other'),
					array('post_id' => 4, 'poster_id' => ANONYMOUS, 'post_username' => 'Other'),
				),
				array(
					array(
						'topic_id' => 2,
						'topic_poster' => ANONYMOUS, 'topic_first_poster_name' => 'Other', 'topic_first_poster_colour' => '',
						'topic_last_poster_id' => ANONYMOUS, 'topic_last_poster_name' => 'Other', 'topic_last_poster_colour' => '',
					),
					array(
						'topic_id' => 4,
						'topic_poster' => ANONYMOUS, 'topic_first_poster_name' => 'Other', 'topic_first_poster_colour' => '',
						'topic_last_poster_id' => ANONYMOUS, 'topic_last_poster_name' => 'Other', 'topic_last_poster_colour' => '',
					),
				),
				array(
					array('forum_id' => 1, 'forum_last_poster_id' => 0, 'forum_last_poster_name' => '', 'forum_last_poster_colour' => ''),
					array('forum_id' => 2, 'forum_last_poster_id' => ANONYMOUS, 'forum_last_poster_name' => 'Other', 'forum_last_poster_colour' => ''),
					array('forum_id' => 3, 'forum_last_poster_id' => 0, 'forum_last_poster_name' => '', 'forum_last_poster_colour' => ''),
					array('forum_id' => 4, 'forum_last_poster_id' => ANONYMOUS, 'forum_last_poster_name' => 'Other', 'forum_last_poster_colour' => ''),
				),
			),
			array(
				'retain', true,
				array(
					array('post_id' => 1, 'poster_id' => ANONYMOUS, 'post_username' => 'Foobar'),
					array('post_id' => 2, 'poster_id' => ANONYMOUS, 'post_username' => 'Other'),
					array('post_id' => 3, 'poster_id' => ANONYMOUS, 'post_username' => 'Foobar'),
					array('post_id' => 4, 'poster_id' => ANONYMOUS, 'post_username' => 'Other'),
				),
				array(
					array(
						'topic_id' => 1,
						'topic_poster' => ANONYMOUS, 'topic_first_poster_name' => 'Foobar', 'topic_first_poster_colour' => '',
						'topic_last_poster_id' => ANONYMOUS, 'topic_last_poster_name' => 'Foobar', 'topic_last_poster_colour' => '',
					),
					array(
						'topic_id' => 2,
						'topic_poster' => ANONYMOUS, 'topic_first_poster_name' => 'Other', 'topic_first_poster_colour' => '',
						'topic_last_poster_id' => ANONYMOUS, 'topic_last_poster_name' => 'Other', 'topic_last_poster_colour' => '',
					),
					array(
						'topic_id' => 3,
						'topic_poster' => ANONYMOUS, 'topic_first_poster_name' => 'Foobar', 'topic_first_poster_colour' => '',
						'topic_last_poster_id' => ANONYMOUS, 'topic_last_poster_name' => 'Foobar', 'topic_last_poster_colour' => '',
					),
					array(
						'topic_id' => 4,
						'topic_poster' => ANONYMOUS, 'topic_first_poster_name' => 'Other', 'topic_first_poster_colour' => '',
						'topic_last_poster_id' => ANONYMOUS, 'topic_last_poster_name' => 'Other', 'topic_last_poster_colour' => '',
					),
				),
				array(
					array('forum_id' => 1, 'forum_last_poster_id' => ANONYMOUS, 'forum_last_poster_name' => 'Foobar', 'forum_last_poster_colour' => ''),
					array('forum_id' => 2, 'forum_last_poster_id' => ANONYMOUS, 'forum_last_poster_name' => 'Other', 'forum_last_poster_colour' => ''),
					array('forum_id' => 3, 'forum_last_poster_id' => ANONYMOUS, 'forum_last_poster_name' => 'Foobar', 'forum_last_poster_colour' => ''),
					array('forum_id' => 4, 'forum_last_poster_id' => ANONYMOUS, 'forum_last_poster_name' => 'Other', 'forum_last_poster_colour' => ''),
				),
			),
			array(
				'remove', true,
				array(
					array('post_id' => 2, 'poster_id' => ANONYMOUS, 'post_username' => 'Other'),
					array('post_id' => 4, 'poster_id' => ANONYMOUS, 'post_username' => 'Other'),
				),
				array(
					array(
						'topic_id' => 2,
						'topic_poster' => ANONYMOUS, 'topic_first_poster_name' => 'Other', 'topic_first_poster_colour' => '',
						'topic_last_poster_id' => ANONYMOUS, 'topic_last_poster_name' => 'Other', 'topic_last_poster_colour' => '',
					),
					array(
						'topic_id' => 4,
						'topic_poster' => ANONYMOUS, 'topic_first_poster_name' => 'Other', 'topic_first_poster_colour' => '',
						'topic_last_poster_id' => ANONYMOUS, 'topic_last_poster_name' => 'Other', 'topic_last_poster_colour' => '',
					),
				),
				array(
					array('forum_id' => 1, 'forum_last_poster_id' => 0, 'forum_last_poster_name' => '', 'forum_last_poster_colour' => ''),
					array('forum_id' => 2, 'forum_last_poster_id' => ANONYMOUS, 'forum_last_poster_name' => 'Other', 'forum_last_poster_colour' => ''),
					array('forum_id' => 3, 'forum_last_poster_id' => 0, 'forum_last_poster_name' => '', 'forum_last_poster_colour' => ''),
					array('forum_id' => 4, 'forum_last_poster_id' => ANONYMOUS, 'forum_last_poster_name' => 'Other', 'forum_last_poster_colour' => ''),
				),
			),
		);
	}

	/**
	* @dataProvider first_last_post_data
	*/
	public function test_first_last_post_info($mode, $retain_username, $expected_posts, $expected_topics, $expected_forums)
	{
		$this->assertFalse(user_delete($mode, 2, $retain_username));

		$sql = 'SELECT post_id, poster_id, post_username
			FROM ' . POSTS_TABLE . '
			ORDER BY post_id ASC';
		$result = $this->db->sql_query($sql);
		$this->assertEquals($expected_posts, $this->db->sql_fetchrowset($result), 'Post table poster info is mismatching after deleting a user.');
		$this->db->sql_freeresult($result);

		$sql = 'SELECT topic_id, topic_poster, topic_first_poster_name, topic_first_poster_colour, topic_last_poster_id, topic_last_poster_name, topic_last_poster_colour
			FROM ' . TOPICS_TABLE . '
			ORDER BY topic_id ASC';
		$result = $this->db->sql_query($sql);
		$this->assertEquals($expected_topics, $this->db->sql_fetchrowset($result), 'Topic table first/last poster info is mismatching after deleting a user.');
		$this->db->sql_freeresult($result);

		$sql = 'SELECT forum_id, forum_last_poster_id, forum_last_poster_name, forum_last_poster_colour
			FROM ' . FORUMS_TABLE . '
			ORDER BY forum_id ASC';
		$result = $this->db->sql_query($sql);
		$this->assertEquals($expected_forums, $this->db->sql_fetchrowset($result), 'Forum table last poster info is mismatching after deleting a user.');
		$this->db->sql_freeresult($result);
	}

	 public function report_attachment_data()
	{
		return array(
			array(
				'retain',
				array(
					array('post_id' => 1, 'post_reported' => 1, 'post_edit_user' => 1, 'post_delete_user' => 1),
					array('post_id' => 2, 'post_reported' => 1, 'post_edit_user' => 1, 'post_delete_user' => 1),
					array('post_id' => 3, 'post_reported' => 0, 'post_edit_user' => 1, 'post_delete_user' => 1),
					array('post_id' => 4, 'post_reported' => 0, 'post_edit_user' => 1, 'post_delete_user' => 1),
				),
				array(
					array('report_id' => 1, 'post_id' => 1, 'user_id' => 1),
					array('report_id' => 3, 'post_id' => 2, 'user_id' => 1),
				),
				array(
					array('topic_id' => 1, 'topic_reported' => 1, 'topic_delete_user' => 1),
					array('topic_id' => 2, 'topic_reported' => 1, 'topic_delete_user' => 1),
					array('topic_id' => 3, 'topic_reported' => 0, 'topic_delete_user' => 1),
					array('topic_id' => 4, 'topic_reported' => 0, 'topic_delete_user' => 1),
				),
				array(
					array('attach_id' => 1, 'post_msg_id' => 1, 'poster_id' => 1),
					array('attach_id' => 2, 'post_msg_id' => 2, 'poster_id' => 1),
					array('attach_id' => 3, 'post_msg_id' => 0, 'poster_id' => 1), // TODO should be deleted: PHPBB3-13089
				),
			),
			array(
				'remove',
				array(
					array('post_id' => 2, 'post_reported' => 1, 'post_edit_user' => 1, 'post_delete_user' => 1),
					array('post_id' => 4, 'post_reported' => 0, 'post_edit_user' => 1, 'post_delete_user' => 1),
				),
				array(
					array('report_id' => 3, 'post_id' => 2, 'user_id' => 1),
				),
				array(
					array('topic_id' => 2, 'topic_reported' => 1, 'topic_delete_user' => 1),
					array('topic_id' => 4, 'topic_reported' => 0, 'topic_delete_user' => 1),
				),
				array(
					array('attach_id' => 2, 'post_msg_id' => 2, 'poster_id' => 1),
					array('attach_id' => 3, 'post_msg_id' => 0, 'poster_id' => 2), // TODO should be deleted: PHPBB3-13089
				),
			),
		);
	}

	/**
	* @dataProvider report_attachment_data
	*/
	public function test_report_attachment_info($mode, $expected_posts, $expected_reports, $expected_topics, $expected_attach)
	{
		$this->assertFalse(user_delete($mode, 2));

		$sql = 'SELECT post_id, post_reported, post_edit_user, post_delete_user
			FROM ' . POSTS_TABLE . '
			ORDER BY post_id ASC';
		$result = $this->db->sql_query($sql);
		$this->assertEquals($expected_posts, $this->db->sql_fetchrowset($result), 'Post report status content is mismatching after deleting a user.');
		$this->db->sql_freeresult($result);

		$sql = 'SELECT report_id, post_id, user_id
			FROM ' . REPORTS_TABLE . '
			ORDER BY report_id ASC';
		$result = $this->db->sql_query($sql);
		$this->assertEquals($expected_reports, $this->db->sql_fetchrowset($result), 'Report table content is mismatching after deleting a user.');
		$this->db->sql_freeresult($result);

		$sql = 'SELECT topic_id, topic_reported, topic_delete_user
			FROM ' . TOPICS_TABLE . '
			ORDER BY topic_id ASC';
		$result = $this->db->sql_query($sql);
		$this->assertEquals($expected_topics, $this->db->sql_fetchrowset($result), 'Topic report status is mismatching after deleting a user.');
		$this->db->sql_freeresult($result);

		$sql = 'SELECT attach_id, post_msg_id, poster_id
			FROM ' . ATTACHMENTS_TABLE . '
			ORDER BY attach_id ASC';
		$result = $this->db->sql_query($sql);
		$this->assertEquals($expected_attach, $this->db->sql_fetchrowset($result), 'Attachment table content is mismatching after deleting a user.');
		$this->db->sql_freeresult($result);
	}

	 public function delete_data()
	{
		return array(
			array(
				'retain',
				array(array('user_id' => 1, 'user_posts' => 4)),
				array(array('user_id' => 1, 'zebra_id' => 3)),
				array(array('ban_id' => 2), array('ban_id' => 3)),
				array(array('session_id' => '12345678901234567890123456789013')),
				array(
					array('log_id' => 2, 'user_id' => 1, 'reportee_id' => 1),
					array('log_id' => 3, 'user_id' => 1, 'reportee_id' => 1),
				),
				array(
					array('msg_id' => 1, 'author_id' => 3, 'message_edit_user' => 3),
					array('msg_id' => 2, 'author_id' => 1, 'message_edit_user' => 1),
				),
			),
			array(
				'remove',
				array(array('user_id' => 1, 'user_posts' => 2)),
				array(array('user_id' => 1, 'zebra_id' => 3)),
				array(array('ban_id' => 2), array('ban_id' => 3)),
				array(array('session_id' => '12345678901234567890123456789013')),
				array(
					array('log_id' => 2, 'user_id' => 1, 'reportee_id' => 1),
					array('log_id' => 3, 'user_id' => 1, 'reportee_id' => 1),
				),
				array(
					array('msg_id' => 1, 'author_id' => 3, 'message_edit_user' => 3),
					array('msg_id' => 2, 'author_id' => 1, 'message_edit_user' => 1),
				),
			),
		);
	}

	/**
	* @dataProvider delete_data
	*/
	public function test_delete_data($mode, $expected_users, $expected_zebra, $expected_ban, $expected_sessions, $expected_logs, $expected_pms)
	{
		$this->assertFalse(user_delete($mode, 2));

		$sql = 'SELECT user_id, user_posts
			FROM ' . USERS_TABLE . '
			ORDER BY user_id ASC';
		$result = $this->db->sql_query($sql);
		$this->assertEquals($expected_users, $this->db->sql_fetchrowset($result), 'User table content is mismatching after deleting a user.');
		$this->db->sql_freeresult($result);

		$sql = 'SELECT user_id, zebra_id
			FROM ' . ZEBRA_TABLE . '
			ORDER BY user_id ASC, zebra_id ASC';
		$result = $this->db->sql_query($sql);
		$this->assertEquals($expected_zebra, $this->db->sql_fetchrowset($result), 'Zebra table content is mismatching after deleting a user.');
		$this->db->sql_freeresult($result);

		$sql = 'SELECT ban_id
			FROM ' . BANLIST_TABLE . '
			ORDER BY ban_id ASC';
		$result = $this->db->sql_query($sql);
		$this->assertEquals($expected_ban, $this->db->sql_fetchrowset($result), 'Ban table content is mismatching after deleting a user.');
		$this->db->sql_freeresult($result);

		$sql = 'SELECT session_id
			FROM ' . SESSIONS_TABLE . '
			ORDER BY session_id ASC';
		$result = $this->db->sql_query($sql);
		$this->assertEquals($expected_sessions, $this->db->sql_fetchrowset($result), 'Session table content is mismatching after deleting a user.');
		$this->db->sql_freeresult($result);

		$sql = 'SELECT log_id, user_id, reportee_id
			FROM ' . LOG_TABLE . '
			ORDER BY log_id ASC';
		$result = $this->db->sql_query($sql);
		$this->assertEquals($expected_logs, $this->db->sql_fetchrowset($result), 'Log table content is mismatching after deleting a user.');
		$this->db->sql_freeresult($result);

		$sql = 'SELECT msg_id, author_id, message_edit_user
			FROM ' . PRIVMSGS_TABLE . '
			ORDER BY msg_id ASC';
		$result = $this->db->sql_query($sql);
		$this->assertEquals($expected_pms, $this->db->sql_fetchrowset($result), 'Private messages table content is mismatching after deleting a user.');
		$this->db->sql_freeresult($result);
	}

	 public function delete_user_id_data()
	{
		return array(
			array(
				'retain',
				array(
					USER_GROUP_TABLE,
					TOPICS_WATCH_TABLE,
					FORUMS_WATCH_TABLE,
					ACL_USERS_TABLE,
					TOPICS_TRACK_TABLE,
					TOPICS_POSTED_TABLE,
					FORUMS_TRACK_TABLE,
					PROFILE_FIELDS_DATA_TABLE,
					MODERATOR_CACHE_TABLE,
					DRAFTS_TABLE,
					BOOKMARKS_TABLE,
					SESSIONS_KEYS_TABLE,
					PRIVMSGS_FOLDER_TABLE,
					PRIVMSGS_RULES_TABLE,
				),
			),
			array(
				'remove',
				array(
					USER_GROUP_TABLE,
					TOPICS_WATCH_TABLE,
					FORUMS_WATCH_TABLE,
					ACL_USERS_TABLE,
					TOPICS_TRACK_TABLE,
					TOPICS_POSTED_TABLE,
					FORUMS_TRACK_TABLE,
					PROFILE_FIELDS_DATA_TABLE,
					MODERATOR_CACHE_TABLE,
					DRAFTS_TABLE,
					BOOKMARKS_TABLE,
					SESSIONS_KEYS_TABLE,
					PRIVMSGS_FOLDER_TABLE,
					PRIVMSGS_RULES_TABLE,
				),
			),
		);
	}

	/**
	* @dataProvider delete_user_id_data
	*/
	public function test_delete_user_id_data($mode, $cleaned_tables)
	{
		$this->assertFalse(user_delete($mode, 2));

		foreach ($cleaned_tables as $table)
		{
			$sql = 'SELECT user_id
				FROM ' . $table . '
				WHERE user_id = 2';
			$result = $this->db->sql_query($sql);
			$this->assertFalse($this->db->sql_fetchfield('user_id'), 'Found data for deleted user in table: ' . $table);
			$this->db->sql_freeresult($result);

			$sql = 'SELECT user_id
				FROM ' . $table . '
				WHERE user_id = 3';
			$result = $this->db->sql_query($sql);
			$this->assertEquals(3, $this->db->sql_fetchfield('user_id'), 'Missing data for user in table: ' . $table);
			$this->db->sql_freeresult($result);
		}
	}
}
