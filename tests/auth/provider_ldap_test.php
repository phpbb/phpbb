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
 * @group slow
 */
class phpbb_auth_provider_ldap_test extends phpbb_database_test_case
{
	/** @var \phpbb\auth\provider\ldap */
	protected $provider;

	/** @var \phpbb\user */
	protected $user;

	protected function setUp() : void
	{
		parent::setUp();

		global $phpbb_root_path, $phpEx;

		$db = $this->new_dbal();
		$config = new \phpbb\config\config([
			'ldap_server'	=> 'localhost',
			'ldap_port'		=> 3389,
			'ldap_base_dn'	=> 'dc=example,dc=com',
			'ldap_uid'		=> 'uid',
			'ldap_email'	=> 'mail',
		]);
		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$lang = new \phpbb\language\language($lang_loader);
		$this->user = new \phpbb\user($lang, '\phpbb\datetime');
		$this->user->data['username'] = 'admin';

		$this->provider = new \phpbb\auth\provider\ldap($config, $db, $lang, $this->user);
	}

	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/user.xml');
	}

	/**
	 * Test to see if a user is identified to Apache. Expects false if they are.
	 */
	public function test_init()
	{
		if (!extension_loaded('ldap'))
		{
			$this->markTestSkipped('LDAP extension not available.');
		}

		$this->assertFalse($this->provider->init());
	}

	public function test_login()
	{
		if (!extension_loaded('ldap'))
		{
			$this->markTestSkipped('LDAP extension not available.');
		}

		$username = 'admin';
		$password = 'adminadmin';

		$expected = array(
			'status'		=> LOGIN_SUCCESS_CREATE_PROFILE, // successful login and user created
			'error_msg'		=> false,
			'user_row'		=> array(
				'username' 				=> 'admin',
				'user_password'			=> '',
				'user_email' 			=> 'admin@example.com',
				'user_type' 			=> 0,
				'group_id'				=> 1,
				'user_new'				=> 0,
				'user_ip'				=> '',
			),
		);

		$this->assertEquals($expected, $this->provider->login($username, $password));
	}

	public function test_autologin()
	{
		$this->assertNull($this->provider->autologin());
	}

	public function test_validate_session()
	{
		$user = array(
			'username'	=> 'admin',
		);

		$this->assertNull($this->provider->validate_session($user));
	}
}
