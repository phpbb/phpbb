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
		global $db;
		$this->db = $db = $this->new_dbal();
		$this->config = new phpbb_config(array(
			'auth_provider_native_enabled'	=> true,
			'auth_provider_native_admin'	=> true,
		));
		$this->user = new phpbb_user();
	}

	public function getDataSet()
    {
        return $this->createXMLDataSet(dirname(__FILE__).'/provider_native_user.xml');
    }

	public function test_registration()
	{
		$post = array(
			'auth_action'		=> 'register',
			'username'			=> 'phpbb_test_registration_user',
			'new_password'		=> 'password',
			'password_confrim'	=> 'password',
			'email'				=> 'example@example.com',
			'tz'				=> 'UTC',
		);
		$request = new phpbb_mock_request(array(), $post);
		$native_provider = new phpbb_auth_provider_native($request, $this->db, $this->config);
		$native_provider->set_user($this->user);
		$native_provider->process();

		$sql = 'SELECT username, user_password, user_email, user_timezone
				FROM ' . USERS_TABLE . '
				WHERE username = `phpbb_test_user`';
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
		$request = new phpbb_mock_request();
		$native_provider = new phpbb_auth_provider_native($request, $this->db, $this->config);
		$native_provider->set_user($this->user);
		$native_provider->process_install_login('phpbb_test_user', 'password');
	}
}
