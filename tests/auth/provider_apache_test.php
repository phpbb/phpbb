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
		$this->markTestIncomplete();
	}

	public function test_validate_session()
	{
		$this->markTestIncomplete();
	}
}
