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

require_once __DIR__ . '/../test_framework/phpbb_session_test_case.php';

class phpbb_session_check_ban_test extends phpbb_session_test_case
{
	protected $user_id = 4;
	protected $key_id = 4;
	/** @var \phpbb\session */
	protected $session;
	protected $backup_cache;

	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/sessions_banlist.xml');
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

	protected function setUp(): void
	{
		parent::setUp();
		// Get session here so that config is mocked correctly
		$this->session = $this->session_factory->get_session($this->db);
		$this->session->data['user_id'] = ANONYMOUS; // Don't get into the session_kill() procedure
		$this->session->lang = [
			'BOARD_BAN_TIME'	=> 'BOARD_BAN_TIME',
			'BOARD_BAN_PERM'	=> 'BOARD_BAN_PERM',
			'BOARD_BAN_REASON'	=> 'BOARD_BAN_REASON',
			'BAN_TRIGGERED_BY_EMAIL'	=> 'BAN_TRIGGERED_BY_EMAIL',
			'BAN_TRIGGERED_BY_IP'		=> 'BAN_TRIGGERED_BY_IP',
			'BAN_TRIGGERED_BY_USER'		=> 'BAN_TRIGGERED_BY_USER',
		];

		global $cache, $config, $phpbb_root_path, $phpEx, $phpbb_filesystem, $phpbb_container, $user;

		$language = new phpbb\language\language(new phpbb\language\language_file_loader($phpbb_root_path, $phpEx));
		$user = new \phpbb\user($language, '\phpbb\datetime');

		$phpbb_filesystem = new \phpbb\filesystem\filesystem();

		$this->backup_cache = $cache;

		// Event dispatcher
		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();

		// Change the global cache object for this test because
		// the mock cache object does not hit the database as is needed
		// for this test.
		$cache = new \phpbb\cache\service(
			new \phpbb\cache\driver\file(),
			$config,
			$this->db,
			$phpbb_dispatcher,
			$phpbb_root_path,
			$phpEx
		);

		$phpbb_container = new phpbb_mock_container_builder();
		$ban_type_email = new \phpbb\ban\type\email($this->db, 'phpbb_bans', 'phpbb_users', 'phpbb_sessions', 'phpbb_sessions_keys');
		$ban_type_user = new \phpbb\ban\type\user($this->db, 'phpbb_bans', 'phpbb_users', 'phpbb_sessions', 'phpbb_sessions_keys');
		$ban_type_ip = new \phpbb\ban\type\ip($this->db, 'phpbb_bans', 'phpbb_users', 'phpbb_sessions', 'phpbb_sessions_keys');
		$phpbb_container->set('ban.type.email', $ban_type_email);
		$phpbb_container->set('ban.type.user', $ban_type_user);
		$phpbb_container->set('ban.type.ip', $ban_type_ip);
		$collection = new \phpbb\di\service_collection($phpbb_container);
		$collection->add('ban.type.email');
		$collection->add('ban.type.user');
		$collection->add('ban.type.ip');
		$phpbb_log = new \phpbb\log\dummy();

		$ban_manager = new \phpbb\ban\manager($collection, $cache->get_driver(), $this->db, $language, $phpbb_log, $user, 'phpbb_bans', 'phpbb_users');
		$phpbb_container->set('ban.manager', $ban_manager);
	}

	protected function tearDown(): void
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
			$ban = $this->session->check_ban($user_id, $user_ips, $user_email, $return);
			$is_banned = !empty($ban);
		}
		catch (PHPUnit\Framework\Error\Notice $e)
		{
			// User error was triggered, user must have been banned
			$is_banned = true;
		}

		$this->assertEquals($should_be_banned, $is_banned, $test_msg);
	}
}
