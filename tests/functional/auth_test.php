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
		$this->assertStringContainsString($this->lang('LOGOUT', 'admin'), $crawler->filter('.navbar')->text());
	}

	public function test_login_other()
	{
		$this->create_user('anothertestuser');
		$this->login('anothertestuser');
		$crawler = self::request('GET', 'index.php');
		$this->assertStringContainsString('anothertestuser', $crawler->filter('#username_logged_in')->text());
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
		$this->assertStringContainsString('anothertestuser', $crawler->filter('#username_logged_in')->text());
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

		$this->logout();
	}

	public function test_acp_login()
	{
		$this->login();
		$this->admin_login();

		// check that we are logged in
		$crawler = self::request('GET', 'adm/index.php?sid=' . $this->sid);
		$this->assertStringContainsString($this->lang('ADMIN_PANEL'), $crawler->filter('h1')->text());
	}

	public function test_board_auth_oauth_setting()
	{
		$this->login();
		$this->admin_login();
		$this->add_lang(['ucp', 'acp/board']);

		$crawler = self::request('GET', 'adm/index.php?i=acp_board&mode=auth&sid=' . $this->sid);
		$this->assertStringContainsString($this->lang('AUTH_METHOD'), $crawler->filter('label[for="auth_method"]')->text());

		// Set OAuth authentication method for Google with random keys
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form([
			'config[auth_method]' => 'oauth',
			'config[auth_oauth_google_key]' => '123456',
			'config[auth_oauth_google_secret]' => '123456',
		]);
		$crawler = self::submit($form);
		$this->assertContainsLang('CONFIG_UPDATED', $crawler->filter('div[class="successbox"] > p')->text());

		// Test OAuth linking via UCP
		$crawler = self::request('GET', 'ucp.php?i=ucp_auth_link&mode=auth_link&sid=' . $this->sid);
		$this->assertStringContainsString($this->lang('AUTH_PROVIDER_OAUTH_SERVICE_GOOGLE'), $crawler->filter('h3')->text());
		$form = $crawler->selectButton($this->lang('UCP_AUTH_LINK_LINK'))->form();
		$crawler = self::submit($form);
		$this->assertStringContainsString('accounts.google.com', $crawler->filter('base')->attr('href'));

		// Test OAuth linking for registration
		$this->logout();
		$crawler = self::request('GET', 'ucp.php?mode=register');
		$this->assertContainsLang('REGISTRATION', $crawler->filter('div.content h2')->text());
		$form = $crawler->selectButton('I agree to these terms')->form();
		$crawler = self::submit($form);
		$this->assertContainsLang('AUTH_PROVIDER_OAUTH_SERVICE_GOOGLE', $crawler->filter('a.button1')->text());
		$crawler = self::request('GET', 'ucp.php?mode=login&login=external&oauth_service=google');
		$this->assertStringContainsString('accounts.google.com', $crawler->filter('base')->attr('href'));

		// Restore default auth method, but unset random keys first
		// Restart webclient as we were redirected to external site before
		self::$client->restart();

		$this->login();
		$this->admin_login();

		$crawler = self::request('GET', 'adm/index.php?i=acp_board&mode=auth&sid=' . $this->sid);
		$this->assertStringContainsString($this->lang('AUTH_METHOD'), $crawler->filter('label[for="auth_method"]')->text());
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form([
			'config[auth_oauth_google_key]' => '',
			'config[auth_oauth_google_secret]' => '',
		]);
		$crawler = self::submit($form);
		$this->assertContainsLang('CONFIG_UPDATED', $crawler->filter('div[class="successbox"] > p')->text());
		$crawler = self::request('GET', 'adm/index.php?i=acp_board&mode=auth&sid=' . $this->sid);
		$this->assertStringContainsString($this->lang('AUTH_METHOD'), $crawler->filter('label[for="auth_method"]')->text());
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form([
			'config[auth_method]' => 'db',
		]);
		$crawler = self::submit($form);
		$this->assertContainsLang('CONFIG_UPDATED', $crawler->filter('div[class="successbox"] > p')->text());
	}
}
