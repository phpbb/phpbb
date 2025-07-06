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

		$this->assertTrue($this->tools['permission']->exists('a_jabber'));
		$this->assertTrue($this->tools['permission']->exists('u_sendim'));
		$this->assertTrue($this->tools['module']->exists('acp', 'ACP_CLIENT_COMMUNICATION', 'ACP_JABBER_SETTINGS'));

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

		$this->assertFalse($this->tools['permission']->exists('a_jabber'));
		$this->assertFalse($this->tools['permission']->exists('u_sendim'));
		$this->assertFalse($this->tools['module']->exists('acp', 'ACP_CLIENT_COMMUNICATION', 'ACP_JABBER_SETTINGS'));

		$this->revert_migration();

		$sql = "SELECT config_name FROM phpbb_config
			WHERE config_name = 'jab_enable'";
		$this->db->sql_query($sql);
		$this->assertEquals('jab_enable', $this->db->sql_fetchfield('config_name'));

		$this->assertTrue($this->tools['permission']->exists('a_jabber'));
		$this->assertTrue($this->tools['permission']->exists('u_sendim'));
		$this->assertTrue($this->tools['module']->exists('acp', 'ACP_CLIENT_COMMUNICATION', 'ACP_JABBER_SETTINGS'));
	}
}
