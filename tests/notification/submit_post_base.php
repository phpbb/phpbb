<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/functions_content.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/functions_posting.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/utf/utf_tools.php';

class phpbb_notification_submit_post_base extends phpbb_database_test_case
{
	protected $notifications, $db, $container, $user, $config, $auth, $cache;

	protected $item_type = '';

	protected $poll_data = array();
	protected $post_data = array(
		'forum_id'		=> 1,
		'topic_id'		=> 1,
		'topic_title'	=> 'topic_title',
		'icon_id'		=> 0,
		'enable_bbcode'		=> 0,
		'enable_smilies'	=> 0,
		'enable_urls'		=> 0,
		'enable_sig'		=> 0,
		'message'			=> '',
		'message_md5'		=> '',
		'attachment_data'	=> array(),
		'bbcode_bitfield'	=> '',
		'bbcode_uid'		=> '',
		'post_edit_locked'	=> false,
		//'force_approved_state'	=> 1,
	);

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/submit_post_' . $this->item_type . '.xml');
	}

	public function setUp()
	{
		parent::setUp();

		global $auth, $cache, $config, $db, $phpbb_container, $phpbb_dispatcher, $user, $request, $phpEx, $phpbb_root_path;

		// Database
		$this->db = $this->new_dbal();
		$db = $this->db;

		// Auth
		$auth = $this->getMock('phpbb_auth');
		$auth->expects($this->any())
			->method('acl_get')
			->with($this->stringContains('_'),
				$this->anything())
			->will($this->returnValueMap(array(
				array('f_noapprove', 1, true),
				array('f_postcount', 1, true),
				array('m_edit', 1, false),
			)));

		// Config
		$config = new phpbb_config(array('num_topics' => 1,'num_posts' => 1,));
		set_config(null, null, null, $config);
		set_config_count(null, null, null, $config);

		$cache = new phpbb_cache_service(
			new phpbb_cache_driver_null(),
			$config,
			$db,
			$phpbb_root_path,
			$phpEx
		);

		// Event dispatcher
		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();

		// User
		$user = $this->getMock('phpbb_user');
		$user->ip = '';
		$user->data = array(
			'user_id'		=> 2,
			'username'		=> 'user-name',
			'is_registered'	=> true,
			'user_colour'	=> '',
		);

		// Request
		$type_cast_helper = $this->getMock('phpbb_request_type_cast_helper_interface');
		$request = $this->getMock('phpbb_request');

		// Container
		$phpbb_container = new phpbb_mock_container_builder();

		$user_loader = new phpbb_user_loader($db, $phpbb_root_path, $phpEx, USERS_TABLE);

		// Notification Types
		$notification_types = array('quote', 'bookmark', 'post', 'post_in_queue', 'topic', 'approve_topic', 'approve_post');
		$notification_types_array = array();
		foreach ($notification_types as $type)
		{
			$class_name = 'phpbb_notification_type_' . $type;
			$class = new $class_name(
				$user_loader, $db, $cache->get_driver(), $user, $auth, $config,
				$phpbb_root_path, $phpEx,
				NOTIFICATION_TYPES_TABLE, NOTIFICATIONS_TABLE, USER_NOTIFICATIONS_TABLE);

			$phpbb_container->set('notification.type.' . $type, $class);

			$notification_types_array['notification.type.' . $type] = $class;
		}

		// Notification Manager
		$phpbb_notifications = new phpbb_notification_manager($notification_types_array, array(),
			$phpbb_container, $user_loader, $db, $cache, $user,
			$phpbb_root_path, $phpEx,
			NOTIFICATION_TYPES_TABLE, NOTIFICATIONS_TABLE, USER_NOTIFICATIONS_TABLE);
		$phpbb_container->set('notification_manager', $phpbb_notifications);
	}

	/**
	* @dataProvider submit_post_data
	*/
	public function test_submit_post($additional_post_data, $expected_before, $expected_after)
	{
		$sql = 'SELECT user_id, item_id, item_parent_id
			FROM ' . NOTIFICATIONS_TABLE . ' n, ' . NOTIFICATION_TYPES_TABLE . " nt
			WHERE nt.notification_type_name = '" . $this->item_type . "'
				AND n.notification_type_id = nt.notification_type_id
			ORDER BY user_id, item_id ASC";
		$result = $this->db->sql_query($sql);
		$this->assertEquals($expected_before, $this->db->sql_fetchrowset($result));
		$this->db->sql_freeresult($result);

		$poll_data = $this->poll_data;
		$post_data = array_merge($this->post_data, $additional_post_data);
		submit_post('reply', '', 'poster-name', POST_NORMAL, $poll_data, $post_data, false, false);

		$sql = 'SELECT user_id, item_id, item_parent_id
			FROM ' . NOTIFICATIONS_TABLE . ' n, ' . NOTIFICATION_TYPES_TABLE . " nt
			WHERE nt.notification_type_name = '" . $this->item_type . "'
				AND n.notification_type_id = nt.notification_type_id
			ORDER BY user_id ASC, item_id ASC";
		$result = $this->db->sql_query($sql);
		$this->assertEquals($expected_after, $this->db->sql_fetchrowset($result));
		$this->db->sql_freeresult($result);
	}
}
