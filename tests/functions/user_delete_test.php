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

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/functions_user.php';

class phpbb_functions_user_delete_test extends phpbb_database_test_case
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/user_delete.xml');
	}

	protected function setUp()
	{
		parent::setUp();

		global $cache, $config, $db, $phpbb_container, $phpbb_dispatcher, $user, $phpbb_root_path, $phpEx;

		$this->db = $db = $this->new_dbal();
		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$lang = new \phpbb\language\language($lang_loader);
		$user = new \phpbb\user($lang, '\phpbb\datetime');
		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();
		$phpbb_container = new phpbb_mock_container_builder();
		$config = new \phpbb\config\config(array(
			'auth_method' => 'oauth',
			'auth_oauth_google_key'	=> 'foo',
			'auth_oauth_google_secret'	=> 'bar',
		));
		$cache = new \phpbb\cache\driver\dummy();
		$request = new phpbb_mock_request();
		$notification_manager = new phpbb_mock_notification_manager();
		$provider_collection =  new \phpbb\auth\provider_collection($phpbb_container, $config);
		$oauth_provider_google = new \phpbb\auth\provider\oauth\service\google($config, $request);
		$oauth_provider_collection = new \phpbb\di\service_collection($phpbb_container);
		$oauth_provider_collection->offsetSet('auth.provider.oauth.service.google', $oauth_provider_google);

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

		$oauth_provider = new \phpbb\auth\provider\oauth\oauth(
			$db,
			$config,
			$passwords_manager,
			$request,
			$user,
			'phpbb_oauth_tokens',
			'phpbb_oauth_accounts',
			$oauth_provider_collection,
			'phpbb_users',
			$phpbb_container,
			$this->phpbb_root_path,
			$this->php_ext
		);
		$provider_collection->offsetSet('auth.provider.oauth', $oauth_provider);

		$phpbb_container->set('auth.provider.oauth', $oauth_provider);
		$phpbb_container->set('auth.provider.oauth.service.google', $oauth_provider_google);
		$phpbb_container->set('auth.provider_collection', $provider_collection);
		$phpbb_container->set('notification_manager', $notification_manager);
	}

	public function test_user_delete()
	{
		// Check that user is linked
		$sql = 'SELECT ot.user_id AS user_id
			FROM phpbb_oauth_accounts oa, phpbb_oauth_tokens ot
			WHERE oa.user_id = 2
				AND ot.user_id = oa.user_id';
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$this->assertEquals(array('user_id' => '2'), $row);

		// user_delete() should return false
		$this->assertFalse(user_delete('remove', array(2)));

		// Make sure user link was removed
		$sql = 'SELECT ot.user_id AS user_id
			FROM phpbb_oauth_accounts oa, phpbb_oauth_tokens ot
			WHERE oa.user_id = 2
				AND ot.user_id = oa.user_id';
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$this->assertEmpty($row);
	}
}
