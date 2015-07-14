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

class phpbb_notification_submit_post_type_quote_test extends phpbb_notification_submit_post_base
{
	protected $item_type = 'notification.type.quote';

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
					array(3, 4, 5, 6, 7),
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
	*/
	public function submit_post_data()
	{
		// The new mock container is needed because the data providers may be executed before phpunit call setUp()
		$parser = $this->get_test_case_helpers()->set_s9e_services(new phpbb_mock_container_builder())->get('text_formatter.parser');

		return array(
			/**
			* Normal post
			*
			* User => State description
			*	2	=> Poster, should NOT receive a notification
			*	3	=> Quoted, should receive a notification
			*	4	=> Quoted, but unauthed to read, should NOT receive a notification
			*	5	=> Quoted, but already notified, should STILL receive a new notification
			*	6	=> Quoted, but option disabled, should NOT receive a notification
			*	7	=> Quoted, option set to default, should receive a notification
			*/
			array(
				array(
					'message'			=> $parser->parse(implode(' ', array(
						'[quote="poster"]poster should not be notified[/quote]',
						'[quote="test"]test should be notified[/quote]',
						'[quote="unauthorized"]unauthorized to read, should not receive a notification[/quote]',
						'[quote="notified"]already notified, should not receive a new notification[/quote]',
						'[quote="disabled"]option disabled, should not receive a notification[/quote]',
						'[quote="default"]option set to default, should receive a notification[/quote]',
						'[quote="doesn\'t exist"]user does not exist, should not receive a notification[/quote]',
					))),
					'bbcode_uid'		=> 'uid',
				),
				array(
					array('user_id' => 5, 'item_id' => 1, 'item_parent_id' => 1),
				),
				array(
					array('user_id' => 3, 'item_id' => 2, 'item_parent_id' => 1),
					array('user_id' => 5, 'item_id' => 1, 'item_parent_id' => 1),
					array('user_id' => 5, 'item_id' => 2, 'item_parent_id' => 1),
					array('user_id' => 7, 'item_id' => 2, 'item_parent_id' => 1),
				),
			),

			/**
			* Unapproved post
			*
			* No new notifications
			*/
			array(
				array(
					'message'			=> $parser->parse(implode(' ', array(
						'[quote="poster"]poster should not be notified[/quote]',
						'[quote="test"]test should be notified[/quote]',
						'[quote="unauthorized"]unauthorized to read, should not receive a notification[/quote]',
						'[quote="notified"]already notified, should not receive a new notification[/quote]',
						'[quote="disabled"]option disabled, should not receive a notification[/quote]',
						'[quote="default"]option set to default, should receive a notification[/quote]',
						'[quote="doesn\'t exist"]user does not exist, should not receive a notification[/quote]',
					))),
					'bbcode_uid'		=> 'uid',
					'force_approved_state' => false,
				),
				array(
					array('user_id' => 5, 'item_id' => 1, 'item_parent_id' => 1),
				),
				array(
					array('user_id' => 5, 'item_id' => 1, 'item_parent_id' => 1),
				),
			),
		);
	}
}
