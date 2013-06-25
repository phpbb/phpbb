<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__).'/../../phpBB/includes/functions.php';

class phpbb_auth_provider_apache_test extends phpbb_database_test_case
{
	protected $provider;
	protected $user;
	protected $request;

	protected function setup()
	{
		parent::setUp();

		global $phpbb_root_path, $phpEx;

		$db = $this->new_dbal();
		$config = new phpbb_config(array());
		$this->request = $this->getMock('phpbb_request');
		$this->user = $this->getMock('phpbb_user');

		$this->provider = new phpbb_auth_provider_apache($db, $config, $this->request, $this->user, $phpbb_root_path, $phpEx);
	}

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__).'/fixtures/user.xml');
	}

	/**
	 * Test to see if a user is identified to Apache. Expects false if they are.
	 */
	public function test_init()
	{
		$this->user->data['username'] = 'foobar';
		$this->request->overwrite('PHP_AUTH_USER', 'foobar', phpbb_request_interface::SERVER);

		$this->assertFalse($this->provider->init());
	}

	public function test_login()
	{
		$username = 'foobar';
		$password = 'example';

		$this->request->overwrite('PHP_AUTH_USER', $username, phpbb_request_interface::SERVER);
		$this->request->overwrite('PHP_AUTH_PW', $password, phpbb_request_interface::SERVER);

		$expected = array(
			'status'		=> LOGIN_SUCCESS,
			'error_msg'		=> false,
			'user_row'		=> array(
				'user_id' 				=> '1',
				'username' 				=> 'foobar',
				'user_password'			=> '$H$9E45lK6J8nLTSm9oJE5aNCSTFK9wqa/',
				'user_passchg' 			=> '0',
				'user_email' 			=> 'example@example.com',
				'user_type' 			=> '0',
				),
		);

		$this->assertEquals($expected, $this->provider->login($username, $password));
	}

	public function test_validate_session()
	{
		$this->markTestIncomplete();
	}
}
