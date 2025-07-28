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

class add_sessions_autoincrement_column_migration_test extends phpbb_migration_test_base
{

	protected $migration_class = '\phpbb\db\migration\data\v400\add_sessions_autoincrement_column';
	protected $fixture = '/fixtures/migration_add_sessions_autoincrement_column.xml';

	public function test_add_sessions_autoincrement_column_migration()
	{
		// First, drop id column as it was already loaded from the schema during recreating the database
		$this->assertTrue($this->db_tools->sql_column_remove('phpbb_sessions', 'id'));
		// Re-create 'old' primary key
		$this->assertTrue($this->db_tools->sql_create_primary_key('phpbb_sessions', 'session_id'));

		$this->assertTrue($this->db_tools->sql_column_exists('phpbb_sessions', 'session_id'));
		$this->assertFalse($this->db_tools->sql_column_exists('phpbb_sessions', 'id'));
		
		$table = $this->get_schema()->getTable('phpbb_sessions');
		$primary_key = $table->getPrimaryKey();
		$this->assertTrue($primary_key instanceof \Doctrine\DBAL\Schema\Index);
		$columns = $primary_key->getColumns();
		$this->assertEquals('session_id', $columns[0]);

		$this->apply_migration();

		$table = $this->get_schema()->getTable('phpbb_sessions');
		$primary_key = $table->getPrimaryKey();
		$this->assertTrue($primary_key instanceof \Doctrine\DBAL\Schema\Index);
		$columns = $primary_key->getColumns();
		$this->assertEquals('id', $columns[0]);
		$this->assertTrue($this->db_tools->sql_index_exists('phpbb_sessions', 'session_id'));

		$this->revert_migration();

		$this->assertFalse($this->db_tools->sql_column_exists('phpbb_sessions', 'id'));
		$this->assertFalse($this->db_tools->sql_index_exists('phpbb_sessions', 'session_id'));

		$table = $this->get_schema()->getTable('phpbb_sessions');
		$primary_key = $table->getPrimaryKey();
		$this->assertTrue($primary_key instanceof \Doctrine\DBAL\Schema\Index);
		$columns = $primary_key->getColumns();
		$this->assertEquals('session_id', $columns[0]);

		// Apply migration back
		$this->apply_migration();
	}
}
