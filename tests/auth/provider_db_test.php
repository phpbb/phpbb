<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__).'/../../phpBB/includes/functions.php';

class phpbb_auth_provider_db_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__).'/fixtures/user.xml');
	}

	public function test_login()
	{
		global $phpbb_root_path, $phpEx;

		$db = $this->new_dbal();
		$config = new phpbb_config(array(
			'ip_login_limit_max'			=> 0,
			'ip_login_limit_use_forwarded' 	=> 0,
			'max_login_attempts' 			=> 0,
			));
		$request = $this->getMock('phpbb_request');
		$user = $this->getMock('phpbb_user');
		$provider = new phpbb_auth_provider_db($db, $config, $request, $user, $phpbb_root_path, $phpEx);

		$expected = array(
			'status'		=> LOGIN_SUCCESS,
			'error_msg'		=> false,
			'user_row'		=> array(
				'user_id' 				=> '1',
				'username' 				=> 'foobar',
				'user_password'			=> '$H$9E45lK6J8nLTSm9oJE5aNCSTFK9wqa/',
				'user_passchg' 			=> '0',
				'user_pass_convert' 	=> '0',
				'user_email' 			=> 'example@example.com',
				'user_type' 			=> '0',
				'user_login_attempts' 	=> '0',
				),
		);

		$this->assertEquals($expected, $provider->login('foobar', 'example'));
	}
}
