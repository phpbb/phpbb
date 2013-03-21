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
			array('post_notification', true),
			array('topic_notification', true),

			// PHPBB3-11460
			array('post_email', true),
			array('topic_email', true),
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

		$cplist = $crawler->filter('.cplist');
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
