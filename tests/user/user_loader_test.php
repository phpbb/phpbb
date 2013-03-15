<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

include_once(__DIR__ . '/../../phpBB/includes/utf/utf_tools.php');

class phpbb_user_loader_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/user_loader.xml');
	}

	public function test_user_loader()
	{
		$db = $this->new_dbal();

		$user_loader = new phpbb_user_loader($db, __DIR__ . '/../../phpBB/', 'php', 'phpbb_users');

		$user_loader->load_users(array(2));

		$user = $user_loader->get_user(1);
		$this->assertEquals(1, $user['user_id']);
		$this->assertEquals('Guest', $user['username']);

		$user = $user_loader->get_user(2);
		$this->assertEquals(2, $user['user_id']);
		$this->assertEquals('Admin', $user['username']);

		// Not loaded
		$user = $user_loader->get_user(3);
		$this->assertEquals(1, $user['user_id']);
		$this->assertEquals('Guest', $user['username']);

		$user = $user_loader->get_user(3, true);
		$this->assertEquals(3, $user['user_id']);
		$this->assertEquals('Test', $user['username']);

		$user_id = $user_loader->load_user_by_username('Test');
		$user = $user_loader->get_user($user_id);
		$this->assertEquals(3, $user['user_id']);
		$this->assertEquals('Test', $user['username']);
	}
}
