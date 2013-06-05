<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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
		$crawler = $this->request('GET', 'ucp.php?i=ucp_notifications&mode=notification_options');
		$this->assert_response_success();

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
}
