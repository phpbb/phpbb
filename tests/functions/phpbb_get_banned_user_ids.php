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

require_once __DIR__ . '/../../phpBB/includes/functions_user.php';

class phpbb_get_banned_user_ids_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/banned_users.xml');
	}

	protected function setUp(): void
	{
		global $db, $phpbb_container, $phpbb_root_path, $phpEx;

		$db = $this->new_dbal();

		parent::setUp();

		$phpbb_container = new phpbb_mock_container_builder();
		$config = new \phpbb\config\config([]);
		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();
		$language = new phpbb\language\language(new phpbb\language\language_file_loader($phpbb_root_path, $phpEx));
		$user = new phpbb\user($language, '\phpbb\datetime');
		$user->data['user_email'] = '';

		$cache = new \phpbb\cache\service(
			new \phpbb\cache\driver\dummy(),
			$config,
			$db,
			$phpbb_dispatcher,
			$phpbb_root_path,
			$phpEx
		);
		$cache->get_driver()->purge();

		$ban_type_email = new \phpbb\ban\type\email($db, 'phpbb_bans', 'phpbb_users', 'phpbb_sessions', 'phpbb_sessions_keys');
		$ban_type_user = new \phpbb\ban\type\user($db, 'phpbb_bans', 'phpbb_users', 'phpbb_sessions', 'phpbb_sessions_keys');
		$ban_type_ip = new \phpbb\ban\type\ip($db, 'phpbb_bans', 'phpbb_users', 'phpbb_sessions', 'phpbb_sessions_keys');
		$phpbb_container->set('ban.type.email', $ban_type_email);
		$phpbb_container->set('ban.type.user', $ban_type_user);
		$phpbb_container->set('ban.type.ip', $ban_type_ip);
		$collection = new \phpbb\di\service_collection($phpbb_container);
		$collection->add('ban.type.email');
		$collection->add('ban.type.user');
		$collection->add('ban.type.ip');
		$phpbb_log = new \phpbb\log\dummy();

		$ban_manager = new \phpbb\ban\manager($collection, $cache->get_driver(), $db, $language, $phpbb_log, $user, 'phpbb_bans', 'phpbb_users');
		$phpbb_container->set('ban.manager', $ban_manager);
	}

	public function phpbb_get_banned_user_ids_data()
	{
		return array(
			// Input to phpbb_get_banned_user_ids (user_id list, ban_end)
			// Expected output
			array(
				// True to get users currently banned
				array(array(1, 2, 4, 5, 6), true),
				array(2 => 2, 5 => 5),
			),
			array(
				// False to only get permanently banned users
				array(array(1, 2, 4, 5, 6), false),
				array(2 => 2),
			),
			array(
				// True to get users currently banned, but should only return passed user IDs
				array(array(5, 6, 7), true),
				array(5 => 5),
			),
			array(
				// Unix timestamp to get users banned until that time
				array(array(1, 2, 4, 5, 6), 2),
				array(2 => 2, 5 => 5, 6 => 6),
			),
		);
	}

	/**
	* @dataProvider phpbb_get_banned_user_ids_data
	*/
	public function test_phpbb_get_banned_user_ids($input, $expected)
	{
		$this->assertEquals($expected, call_user_func_array('phpbb_get_banned_user_ids', $input));
	}
}
