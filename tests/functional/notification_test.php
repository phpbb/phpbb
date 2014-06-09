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

/**
* @group functional
*/
class phpbb_functional_notification_test extends phpbb_functional_test_case
{
	static public function user_subscription_data()
	{
		return array(
			// Rows inserted by phpBB/install/schemas/schema_data.sql
			// Also see PHPBB3-11460
			array('post_notification', true),
			array('topic_notification', true),
			array('post_email', true),
			array('topic_email', true),

			// Default behaviour for in-board notifications:
			// If user did not opt-out, in-board notifications are on.
			array('bookmark_notification', true),
			array('quote_notification', true),

			// Default behaviour for email notifications:
			// If user did not opt-in, email notifications are off.
			array('bookmark_email', false),
			array('quote_email', false),
		);
	}

	/**
	* @dataProvider user_subscription_data
	*/
	public function test_user_subscriptions($checkbox_name, $expected_status)
	{
		$this->login();
		$crawler = self::request('GET', 'ucp.php?i=ucp_notifications&mode=notification_options');

		$cplist = $crawler->filter('.table1');
		if ($expected_status)
		{
			$this->assert_checkbox_is_checked($cplist, $checkbox_name);
		}
		else
		{
			$this->assert_checkbox_is_unchecked($cplist, $checkbox_name);
		}
	}

	public function test_mark_notifications_read()
	{
		// Create a new standard user
		$this->create_user('notificationtestuser');
		$this->add_user_group('NEWLY_REGISTERED', array('notificationtestuser'));
		$this->login('notificationtestuser');

		// Post a new post that needs approval
		$this->create_post(2, 1, 'Re: Welcome to phpBB3', 'This is a test [b]post[/b] posted by notificationtestuser.', array(), 'POST_STORED_MOD');
		$crawler = self::request('GET', "viewtopic.php?t=1&sid={$this->sid}");
		$this->assertNotContains('This is a test post posted by notificationtestuser.', $crawler->filter('html')->text());

		// Login as admin
		$this->logout();
		$this->login();
		$this->add_lang('ucp');

		$crawler = self::request('GET', 'ucp.php?i=ucp_notifications');

		// At least one notification should exist
		$this->assertGreaterThan(0, $crawler->filter('#notification_list_button strong')->text());

		// Get form token
		$link = $crawler->selectLink($this->lang('NOTIFICATIONS_MARK_ALL_READ'))->link()->getUri();
		$crawler = self::request('GET', substr($link, strpos($link, 'ucp.')));
		$form = $crawler->selectButton($this->lang('YES'))->form();
		$crawler = self::submit($form);
		$this->assertEquals(0, $crawler->filter('#notification_list_button strong')->text());
	}
}
