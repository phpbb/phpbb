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

class phpbb_auth_ticket17680_login_cooldown_test extends phpbb_database_test_case
{
	/** @var \phpbb\auth\provider\db */
	protected $provider;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\user */
	protected $user;

	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/login_cooldown.xml');
	}

	protected function setUp(): void
	{
		global $phpbb_root_path, $phpEx;

		parent::setUp();

		$this->db = $this->new_dbal();
		$this->config = new \phpbb\config\config(array(
			'ip_login_limit_max'			=> 0,
			'ip_login_limit_time'			=> 21600,
			'ip_login_limit_use_forwarded'	=> 0,
			'max_login_attempts'			=> 1,
			'login_cooldown_min'			=> 60,
			'login_cooldown_max'			=> 600,
			'captcha_plugin'				=> 'core.captcha.plugins.qa',
		));
		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$lang = new \phpbb\language\language($lang_loader);
		$this->user = new \phpbb\user($lang, '\phpbb\datetime');
		$this->user->ip = '127.0.0.1';

		$driver_helper = new \phpbb\passwords\driver\helper($this->config);
		$passwords_drivers = array(
			'passwords.driver.bcrypt_2y'	=> new \phpbb\passwords\driver\bcrypt_2y($this->config, $driver_helper),
			'passwords.driver.bcrypt'		=> new \phpbb\passwords\driver\bcrypt($this->config, $driver_helper),
			'passwords.driver.salted_md5'	=> new \phpbb\passwords\driver\salted_md5($this->config, $driver_helper),
			'passwords.driver.phpass'		=> new \phpbb\passwords\driver\phpass($this->config, $driver_helper),
		);
		$passwords_helper = new \phpbb\passwords\helper;
		$passwords_manager = new \phpbb\passwords\manager($this->config, $passwords_drivers, $passwords_helper, array_keys($passwords_drivers));

		// A captcha stub that is never solved, so captcha-gated attempts
		// return LOGIN_ERROR_ATTEMPTS instead of hitting a real plugin.
		$captcha = $this->createMock(\phpbb\captcha\plugins\plugin_interface::class);
		$captcha->method('validate')
			->willReturn(false);

		$phpbb_container = new phpbb_mock_container_builder();
		$plugins = new \phpbb\di\service_collection($phpbb_container);
		$plugins->add('core.captcha.plugins.qa');
		$phpbb_container->set(
			'captcha.factory',
			new \phpbb\captcha\factory($phpbb_container, $plugins)
		);
		$phpbb_container->set('core.captcha.plugins.qa', $captcha);
		/** @var \phpbb\captcha\factory $captcha_factory */
		$captcha_factory = $phpbb_container->get('captcha.factory');

		$this->provider = new \phpbb\auth\provider\db($captcha_factory, $this->config, $this->db, $passwords_manager, $this->user);
	}

	protected function get_attempt_count()
	{
		$sql = 'SELECT COUNT(*) AS attempts
			FROM ' . LOGIN_ATTEMPT_TABLE;
		$result = $this->db->sql_query($sql);
		$attempts = (int) $this->db->sql_fetchfield('attempts');
		$this->db->sql_freeresult($result);

		return $attempts;
	}

	public function test_login_cooldown()
	{
		// First failed attempt: threshold not reached yet, no cooldown.
		$login_return = $this->provider->login('foobar', 'wrong_password');
		$this->assertEquals(LOGIN_ERROR_PASSWORD, $login_return['status']);
		$this->assertEquals(1, $this->get_attempt_count());

		// Second attempt: the user threshold is exceeded and the previous
		// attempt from this IP is recent, so the cooldown rejects it.
		$login_return = $this->provider->login('foobar', 'wrong_password');
		$this->assertEquals(LOGIN_ERROR_COOLDOWN, $login_return['status']);
		$this->assertEquals('LOGIN_ERROR_COOLDOWN', $login_return['error_msg']);
		$this->assertGreaterThan(0, $login_return['cooldown_time']);
		$this->assertLessThanOrEqual(60, $login_return['cooldown_time']);

		// The correct password is rejected all the same while the cooldown
		// is active, and rejected attempts do not extend the cooldown.
		$login_return = $this->provider->login('foobar', 'example');
		$this->assertEquals(LOGIN_ERROR_COOLDOWN, $login_return['status']);
		$this->assertEquals(1, $this->get_attempt_count());

		// The cooldown is keyed to username + IP: from another IP address the
		// account owner is not in cooldown and gets the captcha gate instead.
		$this->user->ip = '192.168.1.2';
		$login_return = $this->provider->login('foobar', 'wrong_password');
		$this->assertEquals(LOGIN_ERROR_ATTEMPTS, $login_return['status']);
		$this->user->ip = '127.0.0.1';

		// The cooldown grows with every failed attempt beyond the threshold:
		// with 5 recorded attempts the cooldown is 5 * login_cooldown_min.
		$sql = 'UPDATE ' . USERS_TABLE . "
			SET user_login_attempts = 5
			WHERE username_clean = 'foobar'";
		$this->db->sql_query($sql);
		$login_return = $this->provider->login('foobar', 'wrong_password');
		$this->assertEquals(LOGIN_ERROR_COOLDOWN, $login_return['status']);
		$this->assertGreaterThan(60, $login_return['cooldown_time']);

		// An expired cooldown no longer rejects attempts: the captcha gate
		// takes over again.
		$sql = 'UPDATE ' . LOGIN_ATTEMPT_TABLE . '
			SET attempt_time = ' . (time() - 3600);
		$this->db->sql_query($sql);
		$login_return = $this->provider->login('foobar', 'wrong_password');
		$this->assertEquals(LOGIN_ERROR_ATTEMPTS, $login_return['status']);

		// Setting login_cooldown_min to 0 disables the cooldown entirely.
		$sql = 'DELETE FROM ' . LOGIN_ATTEMPT_TABLE;
		$this->db->sql_query($sql);
		$this->config['login_cooldown_min'] = 0;
		$login_return = $this->provider->login('foobar', 'wrong_password');
		$this->assertEquals(LOGIN_ERROR_ATTEMPTS, $login_return['status']);
		$login_return = $this->provider->login('foobar', 'wrong_password');
		$this->assertEquals(LOGIN_ERROR_ATTEMPTS, $login_return['status']);
	}

	public static function cooldown_message_data()
	{
		return array(
			array(45, '45 seconds'),
			array(1, '1 second'),
			array(60, '1 minute'),
			array(61, '1 minute and 1 second'),
			array(90, '1 minute and 30 seconds'),
			array(120, '2 minutes'),
			array(150, '2 minutes and 30 seconds'),
		);
	}

	/**
	 * @dataProvider cooldown_message_data
	 */
	public function test_cooldown_message_format($cooldown_time, $expected_wait)
	{
		global $phpbb_root_path, $phpEx;

		if (!function_exists('phpbb_format_login_cooldown_message'))
		{
			require_once $phpbb_root_path . 'includes/functions.' . $phpEx;
		}

		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$lang = new \phpbb\language\language($lang_loader);

		$this->assertStringContainsString(
			'wait ' . $expected_wait . ' before',
			phpbb_format_login_cooldown_message($lang, $cooldown_time)
		);
	}
}
