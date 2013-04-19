<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/auth/auth_db.php';

class phpbb_auth_login_db_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__).'/fixtures/unactivated_users.xml');
	}

	public function setUp()
	{
		global $db, $config, $request;

		parent::setUp();

		$db = $this->new_dbal();
		$config = new phpbb_config(array());
		$request = new phpbb_mock_request();
	}

	public function login_db_data()
	{
		return array(
			    array('not_yet_activated_usr1', 'foobar123', USER_ACTIVATION_SELF, 'ACTIVE_ERROR_USER'),
			    array('not_yet_activated_usr2', 'barfoo123', USER_ACTIVATION_ADMIN, 'ACTIVE_ERROR_ADMIN'),
			    array('deactivated_usr', 'foobar123', USER_ACTIVATION_SELF, 'ACTIVE_ERROR_USER_DEACTIVATED'),
			    array('deactivated_usr', 'foobar123', USER_ACTIVATION_ADMIN, 'ACTIVE_ERROR_USER_DEACTIVATED'),
			    array('activated_usr', 'foobar123', USER_ACTIVATION_SELF, false),
			    array('activated_usr', 'foobar123', USER_ACTIVATION_ADMIN, false),
			    array('not_yet_activated_usr1', 'foobar123', USER_ACTIVATION_NONE, 'ACTIVE_ERROR_USER'),
			    array('not_yet_activated_usr2', 'barfoo123', USER_ACTIVATION_DISABLE, 'ACTIVE_ERROR_USER'),
			    array('not_yet_activated_usr1', 'foobar123', USER_ACTIVATION_NONE, 'ACTIVE_ERROR_ADMIN'),
			    array('not_yet_activated_usr2', 'barfoo123', USER_ACTIVATION_DISABLE, 'ACTIVE_ERROR_ADMIN'),
			    array('deactivated_usr', 'foobar123', USER_ACTIVATION_NONE, 'ACTIVE_ERROR_USER_DEACTIVATED'),
			    array('deactivated_usr', 'foobar123', USER_ACTIVATION_DISABLE, 'ACTIVE_ERROR_USER_DEACTIVATED'),
			    array('activated_usr', 'foobar123', USER_ACTIVATION_NONE, false),
			    array('activated_usr', 'foobar123', USER_ACTIVATION_DISABLE, false),
	    );
	}

	/**
	 * @dataProvider login_db_data
	 */
	public function test_login_db($username, $password, $activation_method, $expected)
	{
		global $config;

		$config->set('require_activation', $activation_method);
		$returned_value = login_db($username, $password);
		$this->assertEquals($returned_value['error_msg'], $expected);
	}
}
