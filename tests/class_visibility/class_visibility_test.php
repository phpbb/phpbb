<?php
/**
*
* @package testing
* @version $Id$
* @copyright (c) 2010 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

require_once 'test_framework/framework.php';
require_once 'class_visibility/auth_mock.php';
require_once 'class_visibility/user_mock.php';

require_once '../phpBB/includes/class_content_visibility.php';
require_once '../phpBB/includes/db/mysqli.php';
require_once '../phpBB/includes/functions.php';
require_once '../phpBB/includes/constants.php';

$GLOBALS['db'] = new dbal_mysqli();

class phpbb_class_visibility_test extends PHPUnit_Framework_TestCase
{
	public function test_get_visibility_sql()
	{
		$GLOBALS['auth'] = new phpbb_acl_mock_none;

		$sql1 = phpbb_content_visibility::get_visibility_sql('topic', 1, '');
		$this->assertEquals('topic_visibility = 1', $sql1);

		$sql2 = phpbb_content_visibility::get_visibility_sql('post', 1, '');
		$this->assertEquals('post_visibility = 1', $sql2);

		$GLOBALS['auth'] = new phpbb_acl_mock_founder;

		$sql3 = phpbb_content_visibility::get_visibility_sql('topic', 1, '');
		$this->assertEquals('topic_visibility IN (1, 0, 2)', $sql3);

		$sql4 = phpbb_content_visibility::get_visibility_sql('post', 1, '');
		$this->assertEquals('post_visibility IN (1, 0, 2)', $sql4);

		$GLOBALS['auth'] = new phpbb_acl_mock_user;
		$GLOBALS['user'] = new phpbb_user_mock;
		$GLOBALS['user']->data['user_id'] = 2;

		$sql1 = phpbb_content_visibility::get_visibility_sql('topic', 1, '');
		$this->assertEquals('(topic_visibility = 1
				OR (topic_visibility = 2
					AND topic_poster = 2))', $sql1);

		$sql2 = phpbb_content_visibility::get_visibility_sql('post', 1, '');
		$this->assertEquals('(post_visibility = 1
				OR (post_visibility = 2
					AND poster_id = 2))', $sql2);
	}

	public function test_get_visibility_sql_global()
	{
		$GLOBALS['auth'] = new phpbb_acl_mock_none;

		$sql1 = phpbb_content_visibility::get_visibility_sql_global('topic', array(), '');
		$this->assertEquals('(topic_visibility = 1)', $sql1);

		$sql2 = phpbb_content_visibility::get_visibility_sql_global('post', array(), '');
		$this->assertEquals('(post_visibility = 1)', $sql2);

		$sql3 = phpbb_content_visibility::get_visibility_sql_global('post', range(2, 15), '');
		$this->assertEquals('(post_visibility = 1)', $sql3);

		$GLOBALS['auth'] = new phpbb_acl_mock_founder;

		$sql1 = phpbb_content_visibility::get_visibility_sql_global('topic', array(), '');
		$this->assertEquals('(topic_visibility = 1 OR (topic_visibility = 0
				AND forum_id IN (1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14)) OR (topic_visibility = 2
				AND forum_id IN (1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14)))', $sql1);

		$sql2 = phpbb_content_visibility::get_visibility_sql_global('post', array(), '');
		$this->assertEquals('(post_visibility = 1 OR (post_visibility = 0
				AND forum_id IN (1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14)) OR (post_visibility = 2
				AND forum_id IN (1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14)))', $sql2);

		$sql3 = phpbb_content_visibility::get_visibility_sql_global('post', range(2, 14), '');
		$this->assertEquals('(post_visibility = 1 OR (post_visibility = 0
				AND forum_id = 1) OR (post_visibility = 2
				AND forum_id = 1))', $sql3);

		$GLOBALS['auth'] = new phpbb_acl_mock_user;
		$GLOBALS['user'] = new phpbb_user_mock;
		$GLOBALS['user']->data['user_id'] = 2;

		$sql1 = phpbb_content_visibility::get_visibility_sql_global('topic', array(), '');
		$this->assertEquals('(topic_visibility = 1 OR (topic_poster = 2
				AND topic_visibility = 2
				AND forum_id IN (1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14)))', $sql1);

		$sql2 = phpbb_content_visibility::get_visibility_sql_global('post', array(), '');
		$this->assertEquals('(post_visibility = 1 OR (poster_id = 2
				AND post_visibility = 2
				AND forum_id IN (1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14)))', $sql2);

		$sql3 = phpbb_content_visibility::get_visibility_sql_global('post', range(2, 14), '');
		$this->assertEquals('(post_visibility = 1 OR (poster_id = 2
				AND post_visibility = 2
				AND forum_id = 1))', $sql3);
	}

	public function test_can_soft_delete()
	{
		$GLOBALS['user'] = new phpbb_user_mock;
		$GLOBALS['user']->data['user_id'] = 2;

		$GLOBALS['auth'] = new phpbb_acl_mock_founder;
		$result = phpbb_content_visibility::can_soft_delete(1, 4, true);
		$this->assertEquals(true, $result);

		$result = phpbb_content_visibility::can_soft_delete(1, 2, false);
		$this->assertEquals(true, $result);

		$GLOBALS['auth'] = new phpbb_acl_mock_none;
		$result = phpbb_content_visibility::can_soft_delete(1, 4, true);
		$this->assertEquals(false, $result);

		$result = phpbb_content_visibility::can_soft_delete(1, 2, false);
		$this->assertEquals(false, $result);

		$GLOBALS['auth'] = new phpbb_acl_mock_user;	
		$result = phpbb_content_visibility::can_soft_delete(1, 4, true);
		$this->assertEquals(false, $result);

		$result = phpbb_content_visibility::can_soft_delete(1, 2, false);
		$this->assertEquals(true, $result);

		$result = phpbb_content_visibility::can_soft_delete(1, 2, true);
		$this->assertEquals(false, $result);
	}

	public function test_can_restore()
	{
		$GLOBALS['user'] = new phpbb_user_mock;
		$GLOBALS['user']->data['user_id'] = 2;

		$GLOBALS['auth'] = new phpbb_acl_mock_founder;
		$result = phpbb_content_visibility::can_restore(1, 4, true);
		$this->assertEquals(true, $result);

		$result = phpbb_content_visibility::can_restore(1, 2, false);
		$this->assertEquals(true, $result);

		$GLOBALS['auth'] = new phpbb_acl_mock_none;
		$result = phpbb_content_visibility::can_restore(1, 4, true);
		$this->assertEquals(false, $result);

		$result = phpbb_content_visibility::can_restore(1, 2, false);
		$this->assertEquals(false, $result);

		$GLOBALS['auth'] = new phpbb_acl_mock_user;
		$result = phpbb_content_visibility::can_restore(1, 4, true);
		$this->assertEquals(false, $result);

		$result = phpbb_content_visibility::can_restore(1, 2, false);
		$this->assertEquals(true, $result);

		$result = phpbb_content_visibility::can_restore(1, 2, true);
		$this->assertEquals(false, $result);
	}

	public function test_hide_topic()
	{
		$GLOBALS['auth'] = new phpbb_acl_mock_founder;

		$topic_row = array('topic_replies' => 3);
		$sql_data = array();
		phpbb_content_visibility::hide_topic(4, 2, $topic_row, $sql_data);
		$this->assertEquals(
			array(FORUMS_TABLE => 'forum_topics = forum_topics - 1, forum_posts = forum_posts - 4', USERS_TABLE => 'user_posts = user_posts - 1'),
			$sql_data);
	}

	public function test_hide_post()
	{
		$GLOBALS['auth'] = new phpbb_acl_mock_founder;

		$sql_data = array();
		phpbb_content_visibility::hide_post(4, 111122211, array('topic_replies' => 1), $sql_data);
		$this->assertEquals(
			array(FORUMS_TABLE => 'forum_posts = forum_posts - 1',
				TOPICS_TABLE => 'topic_replies = topic_replies - 1, topic_last_view_time = 111122211',
				USERS_TABLE => 'user_posts = user_posts - 1'),
			$sql_data);

		$sql_data = array();
		phpbb_content_visibility::hide_post(4, 111122211, array('topic_replies' => 0), $sql_data);
		$this->assertEquals(
			array(FORUMS_TABLE => 'forum_posts = forum_posts - 1',
				TOPICS_TABLE => 'topic_last_view_time = 111122211',
				USERS_TABLE => 'user_posts = user_posts - 1'),
			$sql_data);
	}
}
