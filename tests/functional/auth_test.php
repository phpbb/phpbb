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
class phpbb_functional_auth_test extends phpbb_functional_test_case
{
	public function test_login()
	{
		$this->login();

		// check for logout link
		$crawler = self::request('GET', 'index.php');
		$this->assertContains($this->lang('LOGOUT', 'admin'), $crawler->filter('.navbar')->text());
	}

	public function test_login_other()
	{
		$this->create_user('anothertestuser');
		$this->login('anothertestuser');
		$crawler = self::request('GET', 'index.php');
		$this->assertContains('anothertestuser', $crawler->filter('#username_logged_in')->text());
	}

	/**
	 * @dependsOn test_login_other
	 */
	public function test_login_ucp_other_auth_provider()
	{
		global $cache, $config;
		$cache = new phpbb_mock_null_cache;
		$db = $this->get_db();
		$sql = 'UPDATE ' . CONFIG_TABLE . " SET config_value = 'foobar' WHERE config_name = 'auth_method'";
		$db->sql_query($sql);
		$config['auth_method'] = 'foobar';
		$this->login('anothertestuser');
		$crawler = self::request('GET', 'index.php');
		$this->assertContains('anothertestuser', $crawler->filter('#username_logged_in')->text());
		$sql = 'UPDATE ' . CONFIG_TABLE . " SET config_value = 'db' WHERE config_name =  'auth_method'";
		$db->sql_query($sql);
		$config['auth_method'] = 'db';
	}

	/**
	* @depends test_login
	*/
	public function test_logout()
	{
		$this->login();
		$this->add_lang('ucp');

		// logout
		$crawler = self::request('GET', 'ucp.php?sid=' . $this->sid . '&mode=logout');

		// look for a register link, which should be visible only when logged out
		$crawler = self::request('GET', 'index.php');
		$this->assertContains($this->lang('REGISTER'), $crawler->filter('.navbar')->text());
	}

	public function test_acp_login()
	{
		$this->login();
		$this->admin_login();

		// check that we are logged in
		$crawler = self::request('GET', 'adm/index.php?sid=' . $this->sid);
		$this->assertContains($this->lang('ADMIN_PANEL'), $crawler->filter('h1')->text());
	}
}
