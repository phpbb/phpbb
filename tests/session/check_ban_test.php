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
			array('IP Banned, should be banned',
			     false, '127.1.1.1', false, false, /* should be banned? -> */ true),
		);
	}

	public function setUp()
	{
		parent::setUp();
		// Get session here so that config is mocked correctly
		$this->session = $this->session_factory->get_session($this->db);
		global $cache, $config, $phpbb_root_path, $phpEx, $phpbb_filesystem;

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
