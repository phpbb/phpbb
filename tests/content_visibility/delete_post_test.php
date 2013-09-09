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
require_once dirname(__FILE__) . '/../mock/search.php';

class phpbb_content_visibility_delete_post_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/delete_post.xml');
	}

	public function delete_post_data()
	{
		$info_data = array(
			'topic_first_post_id'	=> 1,
			'topic_last_post_id'	=> 3,
			'topic_posts_approved'		=> 3,
			'topic_posts_unapproved'	=> 0,
			'topic_posts_softdeleted'	=> 0,
			'topic_visibility'		=> ITEM_APPROVED,
			'post_time'				=> 2,
			'post_visibility'		=> ITEM_APPROVED,
			'post_postcount'		=> true,
			'poster_id'				=> 1,
			'post_reported'			=> false,
		);

		return array(
			array(
				1, 1, 2,
				array_merge($info_data, array(
					'post_time'				=> 2,
				)),
				false, 'harddelete',
				array(
					array('post_id' => 1, 'post_visibility' => ITEM_APPROVED, 'post_delete_reason' => ''),
					//array('post_id' => 2, 'post_visibility' => ITEM_APPROVED, 'post_delete_reason' => ''),
					array('post_id' => 3, 'post_visibility' => ITEM_APPROVED, 'post_delete_reason' => ''),
				),
				array(
					array(
						'topic_visibility'		=> ITEM_APPROVED,
						'topic_first_post_id'	=> 1,
						'topic_last_post_id'	=> 3,
						'topic_posts_approved'		=> 2,
						'topic_posts_unapproved'	=> 0,
						'topic_posts_softdeleted'	=> 0,
						'topic_delete_reason'	=> '',
					),
				),
				array(
					array('forum_posts_approved' => 2, 'forum_posts_unapproved' => 0, 'forum_posts_softdeleted' => 0, 'forum_topics_approved' => 1, 'forum_topics_unapproved' => 0, 'forum_topics_softdeleted' => 0, 'forum_last_post_id' => 3),
				),
			),
			array(
				1, 1, 1,
				array_merge($info_data, array(
					'post_time'				=> 1,
				)),
				false, 'harddelete',
				array(
					//array('post_id' => 1, 'post_visibility' => ITEM_APPROVED, 'post_delete_reason' => ''),
					array('post_id' => 2, 'post_visibility' => ITEM_APPROVED, 'post_delete_reason' => ''),
					array('post_id' => 3, 'post_visibility' => ITEM_APPROVED, 'post_delete_reason' => ''),
				),
				array(
					array(
						'topic_visibility'		=> ITEM_APPROVED,
						'topic_first_post_id'	=> 2,
						'topic_last_post_id'	=> 3,
						'topic_posts_approved'		=> 2,
						'topic_posts_unapproved'	=> 0,
						'topic_posts_softdeleted'	=> 0,
						'topic_delete_reason'	=> '',
					),
				),
				array(
					array('forum_posts_approved' => 2, 'forum_posts_unapproved' => 0, 'forum_posts_softdeleted' => 0, 'forum_topics_approved' => 1, 'forum_topics_unapproved' => 0, 'forum_topics_softdeleted' => 0, 'forum_last_post_id' => 3),
				),
			),
			array(
				1, 1, 3,
				array_merge($info_data, array(
					'post_time'				=> 3,
				)),
				false, 'harddelete',
				array(
					array('post_id' => 1, 'post_visibility' => ITEM_APPROVED, 'post_delete_reason' => ''),
					array('post_id' => 2, 'post_visibility' => ITEM_APPROVED, 'post_delete_reason' => ''),
					//array('post_id' => 3, 'post_visibility' => ITEM_APPROVED, 'post_delete_reason' => ''),
				),
				array(
					array(
						'topic_visibility'		=> ITEM_APPROVED,
						'topic_first_post_id'	=> 1,
						'topic_last_post_id'	=> 2,
						'topic_posts_approved'		=> 2,
						'topic_posts_unapproved'	=> 0,
						'topic_posts_softdeleted'	=> 0,
						'topic_delete_reason'	=> '',
					),
				),
				array(
					array('forum_posts_approved' => 2, 'forum_posts_unapproved' => 0, 'forum_posts_softdeleted' => 0, 'forum_topics_approved' => 1, 'forum_topics_unapproved' => 0, 'forum_topics_softdeleted' => 0, 'forum_last_post_id' => 2),
				),
			),
			array(
				1, 1, 2,
				array_merge($info_data, array(
					'post_time'				=> 2,
				)),
				true, 'soft delete',
				array(
					array('post_id' => 1, 'post_visibility' => ITEM_APPROVED, 'post_delete_reason' => ''),
					array('post_id' => 2, 'post_visibility' => ITEM_DELETED, 'post_delete_reason' => 'soft delete'),
					array('post_id' => 3, 'post_visibility' => ITEM_APPROVED, 'post_delete_reason' => ''),
				),
				array(
					array(
						'topic_visibility'		=> ITEM_APPROVED,
						'topic_first_post_id'	=> 1,
						'topic_last_post_id'	=> 3,
						'topic_posts_approved'		=> 2,
						'topic_posts_unapproved'	=> 0,
						'topic_posts_softdeleted'	=> 1,
						'topic_delete_reason'	=> '',
					),
				),
				array(
					array('forum_posts_approved' => 2, 'forum_posts_unapproved' => 0, 'forum_posts_softdeleted' => 1, 'forum_topics_approved' => 1, 'forum_topics_unapproved' => 0, 'forum_topics_softdeleted' => 0, 'forum_last_post_id' => 3),
				),
			),
			array(
				1, 1, 1,
				array_merge($info_data, array(
					'post_time'				=> 1,
				)),
				true, 'soft delete',
				array(
					array('post_id' => 1, 'post_visibility' => ITEM_DELETED, 'post_delete_reason' => 'soft delete'),
					array('post_id' => 2, 'post_visibility' => ITEM_APPROVED, 'post_delete_reason' => ''),
					array('post_id' => 3, 'post_visibility' => ITEM_APPROVED, 'post_delete_reason' => ''),
				),
				array(
					array(
						'topic_visibility'		=> ITEM_APPROVED,
						'topic_first_post_id'	=> 2,
						'topic_last_post_id'	=> 3,
						'topic_posts_approved'		=> 2,
						'topic_posts_unapproved'	=> 0,
						'topic_posts_softdeleted'	=> 1,
						'topic_delete_reason'	=> '',
					),
				),
				array(
					array('forum_posts_approved' => 2, 'forum_posts_unapproved' => 0, 'forum_posts_softdeleted' => 1, 'forum_topics_approved' => 1, 'forum_topics_unapproved' => 0, 'forum_topics_softdeleted' => 0, 'forum_last_post_id' => 3),
				),
			),
			array(
				1, 1, 3,
				array_merge($info_data, array(
					'post_time'				=> 3,
				)),
				true, 'soft delete',
				array(
					array('post_id' => 1, 'post_visibility' => ITEM_APPROVED, 'post_delete_reason' => ''),
					array('post_id' => 2, 'post_visibility' => ITEM_APPROVED, 'post_delete_reason' => ''),
					array('post_id' => 3, 'post_visibility' => ITEM_DELETED, 'post_delete_reason' => 'soft delete'),
				),
				array(
					array(
						'topic_visibility'		=> ITEM_APPROVED,
						'topic_first_post_id'	=> 1,
						'topic_last_post_id'	=> 2,
						'topic_posts_approved'		=> 2,
						'topic_posts_unapproved'	=> 0,
						'topic_posts_softdeleted'	=> 1,
						'topic_delete_reason'	=> '',
					),
				),
				array(
					array('forum_posts_approved' => 2, 'forum_posts_unapproved' => 0, 'forum_posts_softdeleted' => 1, 'forum_topics_approved' => 1, 'forum_topics_unapproved' => 0, 'forum_topics_softdeleted' => 0, 'forum_last_post_id' => 2),
				),
			),

			array(
				2, 2, 4,
				array(
					'topic_first_post_id'	=> 4,
					'topic_last_post_id'	=> 4,
					'topic_posts_approved'		=> 1,
					'topic_posts_unapproved'	=> 0,
					'topic_posts_softdeleted'	=> 0,
					'topic_visibility'		=> ITEM_APPROVED,
					'post_time'				=> 4,
					'post_visibility'		=> ITEM_APPROVED,
					'post_postcount'		=> true,
					'poster_id'				=> 1,
					'post_reported'			=> false,
				),
				false, 'harddelete',
				array(
				),
				array(
				),
				array(
					array('forum_posts_approved' => 0, 'forum_posts_unapproved' => 0, 'forum_posts_softdeleted' => 0, 'forum_topics_approved' => 0, 'forum_topics_unapproved' => 0, 'forum_topics_softdeleted' => 0, 'forum_last_post_id' => 0),
				),
			),

			array(
				2, 2, 4,
				array(
					'topic_first_post_id'	=> 4,
					'topic_last_post_id'	=> 4,
					'topic_posts_approved'		=> 1,
					'topic_posts_unapproved'	=> 0,
					'topic_posts_softdeleted'	=> 0,
					'topic_visibility'		=> ITEM_APPROVED,
					'post_time'				=> 4,
					'post_visibility'		=> ITEM_APPROVED,
					'post_postcount'		=> true,
					'poster_id'				=> 1,
					'post_reported'			=> false,
				),
				true, 'soft delete',
				array(
					array('post_id' => 4, 'post_visibility' => ITEM_DELETED, 'post_delete_reason' => ''),
				),
				array(
					array(
						'topic_visibility'		=> ITEM_DELETED,
						'topic_first_post_id'	=> 4,
						'topic_last_post_id'	=> 4,
						'topic_posts_approved'		=> 0,
						'topic_posts_unapproved'	=> 0,
						'topic_posts_softdeleted'	=> 1,
						'topic_delete_reason'	=> 'soft delete',
					),
				),
				array(
					array('forum_posts_approved' => 0, 'forum_posts_unapproved' => 0, 'forum_posts_softdeleted' => 1, 'forum_topics_approved' => 0, 'forum_topics_unapproved' => 0, 'forum_topics_softdeleted' => 1, 'forum_last_post_id' => 0),
				),
			),
		);
	}

	/**
	* @dataProvider delete_post_data
	*/
	public function test_delete_post($forum_id, $topic_id, $post_id, $data, $is_soft, $reason, $expected_posts, $expected_topic, $expected_forum)
	{
		global $auth, $cache, $config, $db, $phpbb_root_path, $phpEx;

		$config['search_type'] = 'phpbb_mock_search';
		$cache = new phpbb_mock_cache;
		$db = $this->new_dbal();
		set_config_count(null, null, null, new phpbb_config(array('num_posts' => 3, 'num_topics' => 1)));

		// Create auth mock
		$auth = $this->getMock('phpbb_auth');
		$auth->expects($this->any())
			->method('acl_get')
			->with($this->stringContains('_'), $this->anything())
			->will($this->returnValueMap(array(
				array('m_approve', 1, true),
			)));
		$user = $this->getMock('phpbb_user');

		$this->phpbb_container->set('notification_manager', new phpbb_mock_notification_manager());
		$this->phpbb_container->set('content.visibility', new phpbb_content_visibility($auth, $db, $user, $phpbb_root_path, $phpEx, FORUMS_TABLE, POSTS_TABLE, TOPICS_TABLE, USERS_TABLE));

		delete_post($forum_id, $topic_id, $post_id, $data, $is_soft, $reason);

		$result = $db->sql_query('SELECT post_id, post_visibility, post_delete_reason
			FROM phpbb_posts
			WHERE topic_id = ' . $topic_id . '
			ORDER BY post_id ASC');

		$this->assertEquals($expected_posts, $db->sql_fetchrowset($result));
		$db->sql_freeresult($result);

		$result = $db->sql_query('SELECT topic_visibility, topic_first_post_id, topic_last_post_id, topic_posts_approved, topic_posts_unapproved, topic_posts_softdeleted, topic_delete_reason
			FROM phpbb_topics
			WHERE topic_id = ' . $topic_id);

		$this->assertEquals($expected_topic, $db->sql_fetchrowset($result));
		$db->sql_freeresult($result);

		$result = $db->sql_query('SELECT forum_posts_approved, forum_posts_unapproved, forum_posts_softdeleted, forum_topics_approved, forum_topics_unapproved, forum_topics_softdeleted, forum_last_post_id
			FROM phpbb_forums
			WHERE forum_id = ' . $forum_id);

		$this->assertEquals($expected_forum, $db->sql_fetchrowset($result));
		$db->sql_freeresult($result);
	}
}
