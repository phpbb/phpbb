<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/submit_post_base.php';

class phpbb_notification_submit_post_type_post_in_queue_test extends phpbb_notification_submit_post_base
{
	protected $item_type = 'post_in_queue';

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
					false,
					'm_approve',
					array(1, 0),
					array(
						1 => array(
							'm_approve' => array(3, 4, 6, 7, 8),
						),
					),
				),
				array(
					array(3, 4, 6, 7, 8),
					'f_read',
					1,
					array(
						1 => array(
							'f_read' => array(3, 6, 7, 8),
						),
					),
				),
			)));
	}

	/**
	* submit_post() Notifications test
	*
	* submit_post() $mode = 'reply'
	* Notification item_type = 'post_in_queue'
	*/
	public function submit_post_data()
	{
		return array(
			/**
			* Normal post
			*
			* No new notifications
			*/
			array(
				array(),
				array(
					array('user_id' => 6, 'item_id' => 1, 'item_parent_id' => 1),
				),
				array(
					array('user_id' => 6, 'item_id' => 1, 'item_parent_id' => 1),
				),
			),

			/**
			* Unapproved post
			*
			* User => State description
			*	2	=> Poster, should NOT receive a notification
			*	3	=> Moderator, should receive a notification
			*	4	=> Moderator, but unauthed to read, should NOT receive a notification
			*	5	=> Moderator, but unauthed to approve, should NOT receive a notification
			*	6	=> Moderator, but already notified, should STILL receive a new notification
			*	7	=> Moderator, but option disabled, should NOT receive a notification
			*	8	=> Moderator, option set to default, should receive a notification
			*/
			array(
				array('force_approved_state' => false),
				array(
					array('user_id' => 6, 'item_id' => 1, 'item_parent_id' => 1),
				),
				array(
					array('user_id' => 3, 'item_id' => 2, 'item_parent_id' => 1),
					array('user_id' => 6, 'item_id' => 1, 'item_parent_id' => 1),
					array('user_id' => 6, 'item_id' => 2, 'item_parent_id' => 1),
					array('user_id' => 8, 'item_id' => 2, 'item_parent_id' => 1),
				),
			),
		);
	}
}
