<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/utf/utf_tools.php';

class phpbb_auth_provider_db_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		if ((version_compare(PHP_VERSION, '5.3.7', '<')))
		{
			return $this->createXMLDataSet(dirname(__FILE__).'/fixtures/user_533.xml');
		}
		else
		{
			return $this->createXMLDataSet(dirname(__FILE__).'/fixtures/user.xml');
		}
	}

	public function test_login()
	{
		global $phpbb_root_path, $phpEx;

		$db = $this->new_dbal();
		$config = new \phpbb\config\config(array(
			'ip_login_limit_max'			=> 0,
			'ip_login_limit_use_forwarded' 	=> 0,
			'max_login_attempts' 			=> 0,
			));
		$request = $this->getMock('\phpbb\request\request');
		$user = $this->getMock('\phpbb\user');
		$driver_helper = new \phpbb\passwords\driver\helper($config);
		$passwords_drivers = array(
			'passwords.driver.bcrypt_2y'	=> new \phpbb\passwords\driver\bcrypt_2y($config, $driver_helper),
			'passwords.driver.bcrypt'		=> new \phpbb\passwords\driver\bcrypt($config, $driver_helper),
			'passwords.driver.salted_md5'	=> new \phpbb\passwords\driver\salted_md5($config, $driver_helper),
			'passwords.driver.phpass'		=> new \phpbb\passwords\driver\phpass($config, $driver_helper),
		);

		$passwords_helper = new \phpbb\passwords\helper;
		// Set up passwords manager
		$passwords_manager = new \phpbb\passwords\manager($config, $passwords_drivers, $passwords_helper, array_keys($passwords_drivers));

		$provider = new \phpbb\auth\provider\db($db, $config, $passwords_manager, $request, $user, $phpbb_root_path, $phpEx);
		if (version_compare(PHP_VERSION, '5.3.7', '<'))
		{
			$password_hash = '$2a$10$e01Syh9PbJjUkio66eFuUu4FhCE2nRgG7QPc1JACalsPXcIuG2bbi';
		}
		else
		{
			$password_hash = '$2y$10$4RmpyVu2y8Yf/lP3.yQBquKvE54TCUuEDEBJYY6FDDFN3LcbCGz9i';
		}

		$expected = array(
			'status'		=> LOGIN_SUCCESS,
			'error_msg'		=> false,
			'user_row'		=> array(
				'user_id' 				=> '1',
				'username' 				=> 'foobar',
				'user_password'			=> $password_hash,
				'user_passchg' 			=> '0',
				'user_pass_convert' 	=> '0',
				'user_email' 			=> 'example@example.com',
				'user_type' 			=> '0',
				'user_login_attempts' 	=> '0',
				),
		);

		$this->assertEquals($expected, $provider->login('foobar', 'example'));

		// Check if convert works
		$login_return = $provider->login('foobar2', 'example');
		$password_start = (version_compare(PHP_VERSION, '5.3.7', '<')) ? '$2a$10$' : '$2y$10$';
		$this->assertStringStartsWith($password_start, $login_return['user_row']['user_password']);
	}
}
