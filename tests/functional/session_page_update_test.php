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

/**
* @group functional
*/

class phpbb_functional_session_page_update_test extends phpbb_functional_test_case
{
	protected function test_session_page_update()
	{
		$this->login();
		$db = $this->get_db();

		if (!function_exists('utf_clean_string'))
		{
			require_once(__DIR__ . '/../../phpBB/includes/utf/utf_tools.php');
		}
		if (!function_exists('user_get_id_name'))
		{
			require_once(__DIR__ . '/../../phpBB/includes/functions_user.php');
		}

		$user_ids = [];
		$username = [$this->get_logged_in_user()];
		user_get_id_name($user_ids, $username);
		$user_id = (int) $user_ids[0];

		// Request index page
		self::request('GET', 'index.php');
		$this->assertEquals(200, self::$client->getResponse()->getStatus());

		sleep(3); // Let SQL do its job
		$sql = 'SELECT session_page FROM ' . SESSIONS_TABLE . ' WHERE session_user_id = ' . $user_id . ' ORDER BY session_time DESC';
		$db->sql_query_limit($sql, 1);
		$this->assertEquals('index.php', $db->sql_fetchfield('session_page'));

		// Request non-existent url
		self::request('GET', 'nonexistent.jpg');
		$this->assertEquals(404, self::$client->getResponse()->getStatus());

		sleep(3); // Let SQL do its job
		$db->sql_query_limit($sql, 1);
		// User page should not be updated to non-existent one
		$this->assertEquals('index.php', $db->sql_fetchfield('session_page'));
	}
}
