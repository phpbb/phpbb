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
class phpbb_functional_user_password_reset_test extends phpbb_functional_test_case
{
	protected $user_data;

	public function test_password_reset()
	{
		$this->add_lang('ucp');
		$user_id = $this->create_user('reset-password-test-user');

		$crawler = self::request('GET', "ucp.php?mode=sendpassword&sid={$this->sid}");
		$form = $crawler->selectButton('submit')->form(array(
			'username'	=> 'reset-password-test-user',
		));
		$crawler = self::submit($form);
		$this->assertContainsLang('NO_EMAIL_USER', $crawler->text());

		$crawler = self::request('GET', "ucp.php?mode=sendpassword&sid={$this->sid}");
		$form = $crawler->selectButton('submit')->form(array(
			'username'	=> 'reset-password-test-user',
			'email'		=> 'nobody@example.com',
		));
		$crawler = self::submit($form);
		$this->assertContainsLang('PASSWORD_UPDATED', $crawler->text());

		// Check if columns in database were updated for password reset
		$this->get_user_data();
		$this->assertNotNull($this->user_data['user_actkey']);
		$this->assertNotNull($this->user_data['user_newpasswd']);

		// Make sure we know the password
		$db = $this->get_db();
		$this->passwords_manager = $this->get_passwords_manager();
		$sql = 'UPDATE ' . USERS_TABLE . "
			SET user_newpasswd = '" . $db->sql_escape($this->passwords_manager->hash('reset-password-test-user')) . "'
			WHERE user_id = " . $user_id;
		$db->sql_query($sql);
	}

	public function test_login_after_reset()
	{
		$this->login('reset-password-test-user');
	}

	public function data_activate_new_password()
	{
		return array(
			array('WRONG_ACTIVATION', false, 'FOOBAR'),
			array('ALREADY_ACTIVATED', 2, 'FOOBAR'),
			array('PASSWORD_ACTIVATED', false, false),
			array('ALREADY_ACTIVATED', false, false),
		);
	}

	/**
	* @dataProvider data_activate_new_password
	*/
	public function test_activate_new_password($expected, $user_id, $act_key)
	{
		$this->add_lang('ucp');
		$this->get_user_data();
		$user_id = (!$user_id) ? $this->user_data['user_id'] : $user_id;
		$act_key = (!$act_key) ? $this->user_data['user_actkey'] : $act_key;

		$crawler = self::request('GET', "ucp.php?mode=activate&u=$user_id&k=$act_key&sid={$this->sid}");
		$this->assertContainsLang($expected, $crawler->text());
	}

	public function test_login()
	{
		$this->add_lang('ucp');
		$crawler = self::request('GET', 'ucp.php');
		$this->assertContains($this->lang('LOGIN_EXPLAIN_UCP'), $crawler->filter('html')->text());

		$form = $crawler->selectButton($this->lang('LOGIN'))->form();
		$crawler = self::submit($form, array('username' => 'reset-password-test-user', 'password' => 'reset-password-test-user'));
		$this->assertNotContains($this->lang('LOGIN'), $crawler->filter('.navbar')->text());

		$cookies = self::$cookieJar->all();

		// The session id is stored in a cookie that ends with _sid - we assume there is only one such cookie
		foreach ($cookies as $cookie);
		{
			if (substr($cookie->getName(), -4) == '_sid')
			{
				$this->sid = $cookie->getValue();
			}
		}

		$this->logout();

		$crawler = self::request('GET', 'ucp.php');
		$this->assertContains($this->lang('LOGIN_EXPLAIN_UCP'), $crawler->filter('html')->text());

		$form = $crawler->selectButton($this->lang('LOGIN'))->form();
		// Try logging in with the old password
		$crawler = self::submit($form, array('username' => 'reset-password-test-user', 'password' => 'reset-password-test-userreset-password-test-user'));
		$this->assertContains($this->lang('LOGIN_ERROR_PASSWORD', '', ''), $crawler->filter('html')->text());
	}

	protected function get_user_data()
	{
		$db = $this->get_db();
		$sql = 'SELECT user_id, username, user_type, user_email, user_newpasswd, user_lang, user_notify_type, user_actkey, user_inactive_reason
			FROM ' . USERS_TABLE . "
			WHERE username = 'reset-password-test-user'";
		$result = $db->sql_query($sql);
		$this->user_data = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
	}
}
