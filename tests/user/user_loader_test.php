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

include_once(__DIR__ . '/../../phpBB/includes/utf/utf_tools.php');

class phpbb_user_loader_test extends phpbb_database_test_case
{
	protected $db;
	protected $user_loader;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/user_loader.xml');
	}

	public function setUp()
	{
		parent::setUp();

		$this->db = $this->new_dbal();
		$this->user_loader = new \phpbb\user_loader($this->db, __DIR__ . '/../../phpBB/', 'php', 'phpbb_users');
	}

	public function test_load_get()
	{
		$this->user_loader->load_users(array(2));

		$user = $this->user_loader->get_user(1);
		$this->assertEquals(1, $user['user_id']);
		$this->assertEquals('Guest', $user['username']);

		$user = $this->user_loader->get_user(2);
		$this->assertEquals(2, $user['user_id']);
		$this->assertEquals('Admin', $user['username']);
	}

	public function test_load_get_unloaded()
	{
		$this->user_loader->load_users(array(2));

		$user = $this->user_loader->get_user(3);
		$this->assertEquals(1, $user['user_id']);
		$this->assertEquals('Guest', $user['username']);

		$user = $this->user_loader->get_user(3, true);
		$this->assertEquals(3, $user['user_id']);
		$this->assertEquals('Test', $user['username']);
	}

	public function test_load_user_by_username()
	{
		$user_id = $this->user_loader->load_user_by_username('Test');
		$user = $this->user_loader->get_user($user_id);
		$this->assertEquals(3, $user['user_id']);
		$this->assertEquals('Test', $user['username']);
	}
}
