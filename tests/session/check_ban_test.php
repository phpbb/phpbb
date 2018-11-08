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

require_once dirname(__FILE__) . '/../test_framework/phpbb_session_test_case.php';

class phpbb_session_check_ban_test extends phpbb_session_test_case
{
	protected $user_id = 4;
	protected $key_id = 4;
	protected $session;
	protected $backup_cache;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/sessions_banlist.xml');
	}

	static function check_banned_data()
	{
		return array(
		    array('All false values, should not be banned',
				 false, false, false, false, /* should be banned? -> */ false),
			array('Matching values in the database, should be banned',
				 4, '127.0.0.1', 'bar@example.org', true, /* should be banned? -> */ true),
		);
	}

	public function setUp()
	{
		parent::setUp();
		// Get session here so that config is mocked correctly
		$this->session = $this->session_factory->get_session($this->db);
		global $cache, $config, $phpbb_root_path, $phpEx, $phpbb_filesystem, $phpbb_container;

		$phpbb_filesystem = new \phpbb\filesystem\filesystem();

		$this->backup_cache = $cache;
		// Change the global cache object for this test because
		// the mock cache object does not hit the database as is needed
		// for this test.
		$cache = new \phpbb\cache\service(
			new \phpbb\cache\driver\file(),
			$config,
			$this->db,
			$phpbb_root_path,
			$phpEx
		);

		$phpbb_container = new phpbb_mock_container_builder();
		$ban_type_email = new \phpbb\ban\type\email($this->db, 'phpbb_users', 'phpbb_sessions', 'phpbb_sessions_keys');
		$ban_type_user = new \phpbb\ban\type\user($this->db, 'phpbb_users', 'phpbb_sessions', 'phpbb_sessions_keys');
		$phpbb_container->set('ban.type.email', $ban_type_email);
		$phpbb_container->set('ban.type.user', $ban_type_user);
		$collection = new \phpbb\di\service_collection($phpbb_container);
		$collection->add('ban.type.email');
		$collection->add('ban.type.user');

		$ban_manager = new \phpbb\ban\manager($collection, $cache, $this->db, 'phpbb_bans', 'phpbb_users');
		$phpbb_container->set('ban.manager', $ban_manager);
	}

	public function tearDown()
	{
		parent::tearDown();
		// Set cache back to what it was before the test changed it
		global $cache;
		$cache = $this->backup_cache;
	}

	/** @dataProvider check_banned_data */
	public function test_check_is_banned($test_msg, $user_id, $user_ips, $user_email, $return, $should_be_banned)
	{
		try
		{
			$is_banned = $this->session->check_ban($user_id, $user_ips, $user_email, $return);
		}
		catch (PHPUnit_Framework_Error_Notice $e)
		{
			// User error was triggered, user must have been banned
			$is_banned = true;
		}

		$this->assertEquals($should_be_banned, $is_banned, $test_msg);
	}
}
