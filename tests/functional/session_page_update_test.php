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
	public function setUp(): void
	{
		parent::setUp();

		global $db;

		$db = $this->db;

		$this->login();
	}

	public function test_session_page_update()
	{
		$db = $this->get_db();

		// Sleep for 2 seconds to ensure we don't have session time race condition
		sleep(2);

		// Request index page
		self::request('GET', 'index.php');
		$this->assertEquals(200, self::$client->getResponse()->getStatus());

		$sql = 'SELECT session_page FROM ' . SESSIONS_TABLE . ' WHERE session_user_id = 2 ORDER BY session_time DESC';
		$db->sql_query_limit($sql, 1);
		$this->assertEquals('index.php', $db->sql_fetchfield('session_page'), 'Failed asserting that session_page is index.php for admin user');

		// Request non-existent url
		self::request('GET', 'nonexistent.jpg', [], false);
		$this->assertEquals(404, self::$client->getResponse()->getStatus(), 'Failed asserting that status of non-existent image is 404');

		$db->sql_query_limit($sql, 1);
		// User page should not be updated to non-existent one
		$this->assertEquals('index.php', $db->sql_fetchfield('session_page'), 'Failed asserting that session page has not changed after 404');
	}
}
