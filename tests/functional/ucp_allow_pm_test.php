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
class phpbb_functional_ucp_allow_pm_test extends phpbb_functional_test_case
{
	static protected $data = array();

	public function __construct()
	{
		parent::__construct();

		$this->backupStaticAttributesBlacklist += array(
			'phpbb_functional_ucp_allow_pm_test' => array('data'),
		);
	}

	// user A sends a PM to user B where B accepts PM
	public function test_enabled_pm_user_to_user()
	{
		// setup
		$this->create_user('test_ucp_allow_pm_sender');
		$this->login('test_ucp_allow_pm_sender');
		self::$data['recipient_id'] = $this->create_user('test_ucp_allow_pm_recipient');
		self::$data['pm_url'] = "ucp.php?i=pm&mode=compose&u=" . (int) self::$data['recipient_id'] . "&sid={$this->sid}";

		// the actual test
		$this->set_user_allow_pm(self::$data['recipient_id'], 1);
		$crawler = self::request('GET', self::$data['pm_url']);
		$this->assertNotContainsLang('PM_USERS_REMOVED_NO_PM', $crawler->filter('html')->text());
	}

	// user A sends a PM to user B where B does not accept PM
	public function test_disabled_pm_user_to_user()
	{
		$this->login('test_ucp_allow_pm_sender');
		$this->set_user_allow_pm(self::$data['recipient_id'], 0);
		$crawler = self::request('GET', self::$data['pm_url']);
		$this->assertContainsLang('PM_USERS_REMOVED_NO_PM', $crawler->filter('.error')->text());
	}


	// An admin sends a PM to user B where B does not accept PM, but cannot
	// ignore a PM from an admin
	public function test_disabled_pm_admin_to_user()
	{
		$this->login();
		$crawler = self::request('GET', self::$data['pm_url']);
		$this->assertNotContainsLang('PM_USERS_REMOVED_NO_PM', $crawler->filter('html')->text());
	}

	// enable or disable PM for a user, like from ucp
	protected function set_user_allow_pm($user_id, $allow)
	{
		$db = $this->get_db();
		$sql = 'UPDATE ' . USERS_TABLE . "
			SET user_allow_pm = " . $allow . "
			WHERE user_id = " . $user_id;
		$result = $db->sql_query($sql);
		$db->sql_freeresult($result);
	}
}
