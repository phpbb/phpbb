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
class phpbb_functional_ucp_profile_test extends phpbb_functional_test_case
{
	public function test_submitting_profile_info()
	{
		$this->add_lang('ucp');
		$this->add_lang('memberlist');
		$this->login();

		$crawler = self::request('GET', 'ucp.php?i=ucp_profile&mode=profile_info');
		$this->assertContainsLang('UCP_PROFILE_PROFILE_INFO', $crawler->filter('#cp-main h2')->text());

		$form = $crawler->selectButton('Submit')->form(array(
			'pf_phpbb_facebook'	=> 'phpbb',
			'pf_phpbb_location'	=> 'Bertie´s Empire',
			'pf_phpbb_skype'	=> 'phpbb.skype.account',
			'pf_phpbb_twitter'	=> 'phpbb_twitter',
			'pf_phpbb_youtube' => 'user/phpbb.youtube',
		));

		$crawler = self::submit($form);
		$this->assertContainsLang('PROFILE_UPDATED', $crawler->filter('#message')->text());

		$crawler = self::request('GET', 'ucp.php?i=ucp_profile&mode=profile_info');
		$form = $crawler->selectButton('Submit')->form();

		$this->assertEquals('phpbb', $form->get('pf_phpbb_facebook')->getValue());
		$this->assertEquals('Bertie´s Empire', $form->get('pf_phpbb_location')->getValue());
		$this->assertEquals('phpbb.skype.account', $form->get('pf_phpbb_skype')->getValue());
		$this->assertEquals('phpbb_twitter', $form->get('pf_phpbb_twitter')->getValue());
		$this->assertEquals('user/phpbb.youtube', $form->get('pf_phpbb_youtube')->getValue());

		$crawler = self::request('GET', 'memberlist.php?mode=viewprofile&un=admin');
		$link = $crawler->selectLink($this->lang('VIEW_YOUTUBE_PROFILE'));
		$this->assertSame('https://youtube.com/user/phpbb.youtube', $link->attr('href'));
	}

	public function test_submitting_emoji()
	{
		$this->add_lang('ucp');
		$this->login();

		$crawler = self::request('GET', 'ucp.php?i=ucp_profile&mode=profile_info');
		$this->assertContainsLang('UCP_PROFILE_PROFILE_INFO', $crawler->filter('#cp-main h2')->text());

		$form = $crawler->selectButton('Submit')->form([
			'pf_phpbb_location' => '😁', // grinning face with smiling eyes Emoji
		]);
		$crawler = self::submit($form);
		$this->assertContainsLang('PROFILE_UPDATED', $crawler->filter('#message')->text());

		$crawler = self::request('GET', 'ucp.php?i=ucp_profile&mode=profile_info');
		$form = $crawler->selectButton('Submit')->form();
		$this->assertEquals('😁', $form->get('pf_phpbb_location')->getValue());
	}

	public function test_changing_password_sends_email()
	{
		$this->add_lang('ucp');
		$this->login();

		// Capture the original password hash. The board's password-complexity
		// rules do not allow changing back to the install default through the
		// UCP form, so it is restored directly afterwards to keep the shared
		// admin account usable by the rest of the suite.
		$sql = 'SELECT user_id, user_password
			FROM ' . USERS_TABLE . "
			WHERE username_clean = 'admin'";
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		// Change the password. Board email is enabled in the test board, so
		// this exercises the new password-changed confirmation email send.
		$crawler = self::request('GET', 'ucp.php?i=ucp_profile&mode=reg_details');
		$this->assertContainsLang('UCP_PROFILE_REG_DETAILS', $crawler->filter('#cp-main h2')->text());

		$form = $crawler->selectButton('submit')->form(array(
			'cur_password'		=> 'adminadmin',
			'new_password'		=> 'AdminAdmin1',
			'password_confirm'	=> 'AdminAdmin1',
		));
		$crawler = self::submit($form);
		$this->assertContainsLang('PROFILE_UPDATED', $crawler->filter('#message')->text());

		// Restore the original password hash for the rest of the suite.
		$sql = 'UPDATE ' . USERS_TABLE . "
			SET user_password = '" . $this->db->sql_escape($row['user_password']) . "'
			WHERE user_id = " . (int) $row['user_id'];
		$this->db->sql_query($sql);
	}

	public function test_changing_email_notifies_old_address()
	{
		$this->add_lang('ucp');
		$this->login();

		// Disable the MX-record check so the test does not depend on DNS, and
		// remember the original value to restore it afterwards.
		$result = $this->db->sql_query('SELECT config_value FROM ' . CONFIG_TABLE . "
			WHERE config_name = 'email_check_mx'");
		$original_check_mx = $this->db->sql_fetchfield('config_value');
		$this->db->sql_freeresult($result);
		$this->db->sql_query('UPDATE ' . CONFIG_TABLE . "
			SET config_value = '0'
			WHERE config_name = 'email_check_mx'");
		$this->purge_cache();

		// The default board require_activation is "none", so the email change
		// applies immediately and the email-changed notice is sent to the old
		// address. Capture the original email to restore it afterwards.
		$crawler = self::request('GET', 'ucp.php?i=ucp_profile&mode=reg_details');
		$form = $crawler->selectButton('submit')->form();
		$original_email = $form->get('email')->getValue();

		$form['cur_password'] = 'adminadmin';
		$form['email'] = 'changed.address@example.com';
		$crawler = self::submit($form);
		$this->assertContainsLang('PROFILE_UPDATED', $crawler->filter('#message')->text());

		// Restore the original email directly. Restoring it through the UCP
		// form would fail with EMAIL_TAKEN whenever earlier tests in the suite
		// have created users through create_user(), whose default address is
		// the same nobody@example.com the admin account is installed with.
		$sql = 'UPDATE ' . USERS_TABLE . "
			SET user_email = '" . $this->db->sql_escape($original_email) . "'
			WHERE username_clean = 'admin'";
		$this->db->sql_query($sql);

		$this->db->sql_query('UPDATE ' . CONFIG_TABLE . "
			SET config_value = '" . $this->db->sql_escape($original_check_mx) . "'
			WHERE config_name = 'email_check_mx'");
		$this->purge_cache();
	}

	public function test_autologin_keys_manage()
	{
		$this->add_lang('ucp');
		$this->login('admin', true);

		$crawler = self::request('GET', 'ucp.php?i=ucp_profile&mode=autologin_keys');
		$this->assertContainsLang('UCP_PROFILE_AUTOLOGIN_KEYS', $crawler->filter('#cp-main h2')->text());

		$profile_url = $crawler->filter('a[title="Profile"]')->attr('href');
		$user_id = $this->get_parameter_from_link($profile_url, 'u');

		$sql_ary = [
			'SELECT'	=> 'sk.key_id',
			'FROM'		=> [SESSIONS_KEYS_TABLE	=> 'sk'],
			'WHERE'		=> 'sk.user_id = ' . (int) $user_id,
			'ORDER_BY'	=> 'sk.last_login ASC',
		];
		$result = $this->db->sql_query_limit($this->db->sql_build_query('SELECT', $sql_ary), 1);
		$key_id = substr($this->db->sql_fetchfield('key_id'), 0, 8);
		$this->db->sql_freeresult($result);

		$this->assertStringContainsString($key_id, $crawler->filter('label[for="' . $key_id . '"]')->text());

		$form = $crawler->selectButton('submit')->form();
		foreach ($form['keys'] as $key)
		{
			$key->tick();
		}
		$crawler = self::submit($form);
		$this->assertStringContainsString($this->lang('AUTOLOGIN_SESSION_KEYS_DELETED'), $crawler->filter('html')->text());

		$crawler = self::request('GET', 'ucp.php?i=ucp_profile&mode=autologin_keys');
		$this->assertStringContainsString($this->lang('PROFILE_NO_AUTOLOGIN_KEYS'), $crawler->filter('tbody > tr > td[class="bg1"]')->text());
	}
}
