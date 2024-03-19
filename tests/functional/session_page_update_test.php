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

		// Delete previous session info for admin user
		$sql = 'DELETE FROM ' . SESSIONS_TABLE . ' WHERE session_user_id = 2';
		$db->sql_query($sql);

		$this->login();
	}

	public function test_session_page_update()
	{
		$db = $this->get_db();

		// Request index page
		self::request('GET', 'index.php');
		$this->assertEquals(200, self::$client->getInternalResponse()->getStatusCode(), 'Failed asserting that status of index page is 200');

		$sql = 'SELECT session_page FROM ' . SESSIONS_TABLE . ' WHERE session_user_id = 2 ORDER BY session_time DESC';
		$db->sql_query_limit($sql, 1);
		$this->assertEquals('index.php', $db->sql_fetchfield('session_page'), 'Failed asserting that session_page is index.php for admin user');

		// Request non-existent url
		self::request('GET', 'nonexistent.jpg', [], false);
		$this->assertEquals(404, self::$client->getInternalResponse()->getStatusCode(), 'Failed asserting that status of non-existent image is 404');

		$db->sql_query_limit($sql, 1);
		// User page should not be updated to non-existent one
		$this->assertEquals('index.php', $db->sql_fetchfield('session_page'), 'Failed asserting that session page has not changed after 404');
	}
}
