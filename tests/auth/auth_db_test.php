<?php
/**
*
* @package testing
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/auth/auth_db.php';

class phpbb_auth_db_test extends phpbb_database_test_case
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

	public function test_login_db()
	{
		global $config;

		$config->set('require_activation', USER_ACTIVATION_SELF);
		$returned_value = login_db('foo', 'foobar123');
		$this->assertEquals($returned_value['error_msg'], 'ACTIVE_ERROR_USER');
		
		$config->set('require_activation', USER_ACTIVATION_ADMIN);
		$returned_value = login_db('bar', 'barfoo123');
		$this->assertEquals($returned_value['error_msg'], 'ACTIVE_ERROR_ADMIN');
		
		$config->set('require_activation', USER_ACTIVATION_ADMIN);
		$returned_value = login_db('barfoo', 'barfoo123');
		$this->assertEquals($returned_value['error_msg'], 'ACTIVE_ERROR_USER_DEACTIVATED');
		
		$config->set('require_activation', USER_ACTIVATION_SELF);
		$returned_value = login_db('barfoo', 'barfoo123');
		$this->assertEquals($returned_value['error_msg'], 'ACTIVE_ERROR_USER_DEACTIVATED');
		
		$config->set('require_activation', USER_ACTIVATION_SELF);
		$returned_value = login_db('foofoo', 'foobar123');
		$this->assertEquals($returned_value['error_msg'], 'ACTIVE_ERROR_USER');
		
		$config->set('require_activation', USER_ACTIVATION_SELF);
		$returned_value = login_db('foobar', 'foobar123');
		$this->assertEquals($returned_value['error_msg'], 'ACTIVE_ERROR_USER');
		
		$config->set('require_activation', USER_ACTIVATION_SELF);
		$returned_value = login_db('foobarfoo', 'foobar123');
		$this->assertEquals($returned_value['error_msg'], 'ACTIVE_ERROR_USER');
		
		$config->set('require_activation', USER_ACTIVATION_SELF);
		$returned_value = login_db('barfoobar', 'foobar123');
		$this->assertEquals($returned_value['error_msg'], 'ACTIVE_ERROR_USER');
		
		$config->set('require_activation', USER_ACTIVATION_SELF);
		$returned_value = login_db('barbar', 'foobar123');
		$this->assertEquals($returned_value['error_msg'], false);
	}
}
