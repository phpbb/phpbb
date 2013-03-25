<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/submit_post_base.php';

class phpbb_notification_submit_post_type_quote_test extends phpbb_notification_submit_post_base
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
					array('3', '4', '5', '6', '7'),
					'f_read',
					1,
					array(
						1 => array(
							'f_read' => array(3, 5, 6, 7),
						),
					),
				),
			)));
	}

	/**
	* submit_post() Notifications test
	*
	* submit_post() $mode = 'reply'
	* Notification item_type = 'quote'
	*
	* User => State description
	*	2	=> Poster, should NOT receive a notification
	*	3	=> Quoted, should receive a notification
	*	4	=> Quoted, but unauthed to read, should NOT receive a notification
	*	5	=> Quoted, but already notified, should NOT receive a new notification
	*	6	=> Quoted, but option disabled, should NOT receive a notification
	*	7	=> Quoted, option set to default, should receive a notification
	*/
	public function test_type_quote()
	{
		$sql = 'SELECT user_id, item_id, item_parent_id
			FROM ' . NOTIFICATIONS_TABLE . "
			WHERE item_type = 'quote'
			ORDER BY user_id, item_id ASC";
		$result = $this->db->sql_query($sql);
		$this->assertEquals(array(
			array('user_id' => 5, 'item_id' => 1, 'item_parent_id' => 1),
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
			'message'			=> implode(' ', array(
				'[quote=&quot;poster&quot;:uid]poster should not be notified[/quote:uid]',
				'[quote=&quot;test&quot;:uid]test should be notified[/quote:uid]',
				'[quote=&quot;unauthorized&quot;:uid]unauthorized to read, should not receive a notification[/quote:uid]',
				'[quote=&quot;notified&quot;:uid]already notified, should not receive a new notification[/quote:uid]',
				'[quote=&quot;disabled&quot;:uid]option disabled, should not receive a notification[/quote:uid]',
				'[quote=&quot;default&quot;:uid]option set to default, should receive a notification[/quote:uid]',
				'[quote=&quot;doesn\'t exist&quot;:uid]user does not exist, should not receive a notification[/quote:uid]',
			)),
			'message_md5'		=> '',
			'attachment_data'	=> array(),
			'bbcode_bitfield'	=> '',
			'bbcode_uid'		=> 'uid',
			'post_edit_locked'	=> false,
			//'force_approved_state'	=> 1,
		);

		submit_post('reply', '', 'poster-name', POST_NORMAL, $poll, $data, false, false);

		$sql = 'SELECT user_id, item_id, item_parent_id
			FROM ' . NOTIFICATIONS_TABLE . "
			WHERE item_type = 'quote'
			ORDER BY user_id ASC, item_id ASC";
		$result = $this->db->sql_query($sql);
		$this->assertEquals(array(
			array('user_id' => 3, 'item_id' => 2, 'item_parent_id' => 1),
			array('user_id' => 5, 'item_id' => 1, 'item_parent_id' => 1),
			array('user_id' => 7, 'item_id' => 2, 'item_parent_id' => 1),
		), $this->db->sql_fetchrowset($result));
		$this->db->sql_freeresult($result);
	}
}
