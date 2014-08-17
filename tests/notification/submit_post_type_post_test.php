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

require_once dirname(__FILE__) . '/submit_post_base.php';

class phpbb_notification_submit_post_type_post_test extends phpbb_notification_submit_post_base
{
	protected $item_type = 'notification.type.post';

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
					array(3, 4, 5, 6, 7, 8),
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
	*/
	public function submit_post_data()
	{
		return array(
			/**
			* Normal post
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
			array(
				array(),
				array(
					array('user_id' => 5, 'item_id' => 1, 'item_parent_id' => 1),
					array('user_id' => 8, 'item_id' => 1, 'item_parent_id' => 1),
				),
				array(
					array('user_id' => 3, 'item_id' => 2, 'item_parent_id' => 1),
					array('user_id' => 5, 'item_id' => 1, 'item_parent_id' => 1),
					array('user_id' => 6, 'item_id' => 2, 'item_parent_id' => 1),
					array('user_id' => 7, 'item_id' => 2, 'item_parent_id' => 1),
					array('user_id' => 8, 'item_id' => 1, 'item_parent_id' => 1),
				),
			),

			/**
			* Unapproved post
			*
			* No new notifications
			*/
			array(
				array('force_approved_state' => false),
				array(
					array('user_id' => 5, 'item_id' => 1, 'item_parent_id' => 1),
					array('user_id' => 8, 'item_id' => 1, 'item_parent_id' => 1),
				),
				array(
					array('user_id' => 5, 'item_id' => 1, 'item_parent_id' => 1),
					array('user_id' => 8, 'item_id' => 1, 'item_parent_id' => 1),
				),
			),
		);
	}
}
