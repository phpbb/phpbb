<?php
/**
* @package testing
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

class phpbb_auth_provider_native_test extends phpbb_database_test_case
{
	private $config;
	private $db;
	private $user;

	protected function setUp()
	{
		parent::setUp();

		global $db, $user, $config;
		$this->db = $db = $this->new_dbal();

		$config = new phpbb_config(array(
			'allow_emailreuse'				=> true,
			'auth_provider_native_enabled'	=> true,
			'auth_provider_native_admin'	=> true,

			'email_check_mx'	=> false,

			'min_pass_chars'	=> 1,
			'max_pass_chars'	=> 64,

			'pass_complex'	=> 'PASS_TYPE_ANY',

			'rand_seed'				=> '',
			'rand_seed_last_update' => time() + 600,
		));
		$this->config = $config;

		$user = new phpbb_user();
		$user->lang = new phpbb_mock_lang();
		$this->user = $user;
		$this->user->data['user_email'] = '';
	}

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__).'/native_provider_user.xml');
	}

	public function test_registration()
	{
		$post = array(
			'auth_action'		=> 'register',
			'username'			=> 'phpbb_test_registration_user',
			'new_password'		=> 'password',
			'password_confirm'	=> 'password',
			'email'				=> 'example@example.com',
			'tz'				=> 'UTC',
			'lang'				=> 'en',
		);
		$request = new phpbb_mock_request(array(), $post);
		$native_provider = new phpbb_auth_provider_native($request, $this->db, $this->config);
		$native_provider->set_user($this->user);
		$native_provider->process();

		$sql = 'SELECT username, user_password, user_email, user_timezone
				FROM ' . USERS_TABLE . "
				WHERE username = 'phpbb_test_registration_user'";
		$result = $this->db->query($sql);
		$row = $this->db->fetchrow($result);
		$this->db->sql_freeresult($result);

		$this->assertTrue($row);
		$this->assertEquals('phpbb_test_registration_user', $row['username']);
		$this->assertEquals(phpbb_hash('password'), $row['user_password']);
		$this->assertEquals('example@example.com', $row['user_email']);
		$this->assertEquals('UTC', $row['user_timezone']);
	}

	public function test_login()
	{
		global $phpbb_extension_manager, $phpbb_root_path, $request;
		$phpbb_extension_manager = new phpbb_mock_extension_manager($phpbb_root_path);
		$post = array(
			'auth_action'		=> 'login',
			'username'			=> 'phpbb_test_user',
			'password'			=> 'password',
		);
		$request = new phpbb_mock_request(array(), $post);
		$native_provider = new phpbb_auth_provider_native($request, $this->db, $this->config);
		$native_provider->set_user($this->user);
		$native_provider->process(false);
	}
}
