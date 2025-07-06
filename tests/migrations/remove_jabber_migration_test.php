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

require_once __DIR__ . '/migration_test_base.php';

class phpbb_migrations_remove_jabber_migration_test extends phpbb_migration_test_base
{

	protected $migration_class = '\phpbb\db\migration\data\v400\remove_jabber';
	protected $fixture = '/fixtures/migration_remove_jabber.xml';

	public function test_remove_jabber_migration()
	{
		$sql = "SELECT id FROM phpbb_user_notifications
			WHERE method = 'notification.method.jabber'";
		$result = $this->db->sql_query($sql);
		$rowset = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);
		$this->assertEquals(14, count($rowset));

		$sql = "SELECT config_name FROM phpbb_config
			WHERE config_name = 'jab_enable'";
		$this->assertNotFalse($this->db->sql_query($sql));

		$sql = "SELECT auth_option FROM phpbb_acl_options
			WHERE auth_option = 'a_jabber'";
		$this->assertNotFalse($this->db->sql_query($sql));

		$this->apply_migration();

		$sql = "SELECT id FROM phpbb_user_notifications
			WHERE method = 'notification.method.jabber'";
		$this->db->sql_query($sql);
		$this->assertFalse($this->db->sql_fetchfield('id'));
		
		$sql = "SELECT id FROM phpbb_user_notifications
			WHERE method = 'notification.method.email'";
		$result = $this->db->sql_query($sql);
		$rowset = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);
		$this->assertEquals(14, count($rowset));

		$sql = "SELECT config_name FROM phpbb_config
			WHERE config_name = 'jab_enable'";
		$this->db->sql_query($sql);
		$this->assertFalse($this->db->sql_fetchfield('config_name'));

		$sql = "SELECT auth_option FROM phpbb_acl_options
			WHERE auth_option = 'a_jabber'";
		$this->db->sql_query($sql);
		$this->assertFalse($this->db->sql_fetchfield('auth_option'));

		$this->revert_migration();

		$sql = "SELECT config_name FROM phpbb_config
			WHERE config_name = 'jab_enable'";
		$this->db->sql_query($sql);
		$this->assertEquals('jab_enable', $this->db->sql_fetchfield('config_name'));

		$sql = "SELECT auth_option FROM phpbb_acl_options
			WHERE auth_option = 'a_jabber'";
		$this->db->sql_query($sql);
		$this->assertEquals('a_jabber', $this->db->sql_fetchfield('auth_option'));
	}
}
