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
class phpbb_functional_ticket17680_login_cooldown_test extends phpbb_functional_test_case
{
	protected function submit_login($username, $password)
	{
		$crawler = self::request('GET', 'ucp.php?mode=login');
		$form = $crawler->selectButton($this->lang('LOGIN'))->form();

		return self::submit($form, array('username' => $username, 'password' => $password));
	}

	public function test_login_cooldown_message()
	{
		$this->add_lang('ucp');

		// Use a dedicated user, so the shared admin account is not affected.
		$username = 'cooldown test user';
		$this->create_user($username);

		// Lower the user attempt threshold to trigger the cooldown quickly,
		// remembering the original value to restore it afterwards.
		$result = $this->db->sql_query('SELECT config_value FROM ' . CONFIG_TABLE . "
			WHERE config_name = 'max_login_attempts'");
		$original_max_attempts = $this->db->sql_fetchfield('config_value');
		$this->db->sql_freeresult($result);
		$this->db->sql_query('UPDATE ' . CONFIG_TABLE . "
			SET config_value = '1'
			WHERE config_name = 'max_login_attempts'");
		$this->purge_cache();

		// First failed attempt: the threshold is not exceeded yet, so the
		// normal incorrect-password message is shown.
		$crawler = $this->submit_login($username, 'wrong_password');
		$this->assertStringContainsString('You have specified an incorrect password.', $crawler->filter('html')->text());

		// Second failed attempt: the threshold is exceeded and the previous
		// attempt is recent, so the login cooldown rejects the attempt.
		$crawler = $this->submit_login($username, 'wrong_password');
		$this->assertStringContainsString('before trying to log in again', $crawler->filter('html')->text());

		// The cooldown also rejects the correct password.
		$crawler = $this->submit_login($username, $username . $username);
		$this->assertStringContainsString('before trying to log in again', $crawler->filter('html')->text());

		// Restore the shared board state directly: the attempt threshold,
		// the recorded attempts and the user's failed-attempt counter.
		$this->db->sql_query('UPDATE ' . CONFIG_TABLE . "
			SET config_value = '" . $this->db->sql_escape($original_max_attempts) . "'
			WHERE config_name = 'max_login_attempts'");
		$this->db->sql_query('DELETE FROM ' . LOGIN_ATTEMPT_TABLE . "
			WHERE username_clean = '" . $this->db->sql_escape(utf8_clean_string($username)) . "'");
		$this->db->sql_query('UPDATE ' . USERS_TABLE . "
			SET user_login_attempts = 0
			WHERE username_clean = '" . $this->db->sql_escape(utf8_clean_string($username)) . "'");
		$this->purge_cache();
	}
}
