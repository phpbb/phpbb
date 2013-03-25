<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/submit_post_base.php';

class phpbb_notification_submit_post_type_post_test extends phpbb_notification_submit_post_base
{
	public function setUp()
	{
		parent::setUp();

		global $auth;

		// Add additional permissions
		$auth->expects($this->any())
			->method('acl_get_list')
			->with($this->anything(),
				$this->stringContains('_'),
				$this->greaterThan(0))
			->will($this->returnValueMap(array(
				array(
					array('3', '4', '5', '6', '7', '8'),
					'f_read',
					1,
					array(
						1 => array(
							'f_read' => array(3, 5, 6, 7, 8),
						),
					),
				),
			)));
	}

	/**
	* submit_post() Notifications test
	*
	* submit_post() $mode = 'reply'
	* Notification item_type = 'post'
	*
	* User => State description
	*	2	=> Poster, should NOT receive a notification
	*	3	=> Topic subscribed, should receive a notification
	*	4	=> Topic subscribed, but unauthed to read, should NOT receive a notification
	*	5	=> Topic subscribed, but already notified, should NOT receive a new notification
	*	6	=> Topic and forum subscribed, should receive ONE notification
	*	7	=> Forum subscribed, should receive a notification
	*	8	=> Forum subscribed, but already notified, should NOT receive a new notification
	*/
	public function test_type_post()
	{
		$sql = 'SELECT user_id, item_id, item_parent_id
			FROM ' . NOTIFICATIONS_TABLE . "
			WHERE item_type = 'post'
			ORDER BY user_id, item_id ASC";
		$result = $this->db->sql_query($sql);
		$this->assertEquals(array(
			array('user_id' => 5, 'item_id' => 1, 'item_parent_id' => 1),
			array('user_id' => 8, 'item_id' => 1, 'item_parent_id' => 1),
		), $this->db->sql_fetchrowset($result));
		$this->db->sql_freeresult($result);

		$poll = array();
		$data = array(
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

		submit_post('reply', '', 'poster-name', POST_NORMAL, $poll, $data, false, false);

		$sql = 'SELECT user_id, item_id, item_parent_id
			FROM ' . NOTIFICATIONS_TABLE . "
			WHERE item_type = 'post'
			ORDER BY user_id ASC, item_id ASC";
		$result = $this->db->sql_query($sql);
		$this->assertEquals(array(
			array('user_id' => 3, 'item_id' => 1, 'item_parent_id' => 1),
			array('user_id' => 5, 'item_id' => 1, 'item_parent_id' => 1),
			array('user_id' => 6, 'item_id' => 1, 'item_parent_id' => 1),
			array('user_id' => 7, 'item_id' => 1, 'item_parent_id' => 1),
			array('user_id' => 8, 'item_id' => 1, 'item_parent_id' => 1),
		), $this->db->sql_fetchrowset($result));
		$this->db->sql_freeresult($result);
	}
}
