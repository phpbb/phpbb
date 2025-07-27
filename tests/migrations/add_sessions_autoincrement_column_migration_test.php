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

	protected $migration_class = '\phpbb\db\migration\data\v400\add_sessions_autoincrement_column_migration_test';
	protected $fixture = '/fixtures/migration_add_sessions_autoincrement_column.xml';

	public function test_add_sessions_autoincrement_column_migration()
	{
		$phpbb_sessions_table_data = [
			'COLUMNS'	=> [
				'session_id' => ['CHAR:32', ''],
				'session_user_id' => ['UINT', 0],
				'session_last_visit' => ['TIMESTAMP', 0],
				'session_start' => ['TIMESTAMP', 0],
				'session_time' => ['TIMESTAMP', 0],
				'session_ip' => ['VCHAR:40', ''],
				'session_browser' => ['VCHAR:150', ''],
				'session_forwarded_for' => ['VCHAR:255', ''],
				'session_page' => ['VCHAR_UNI', ''],
				'session_viewonline' => ['BOOL', 1],
				'session_autologin' => ['BOOL', 0],
				'session_admin' => ['BOOL', 0],
				'session_forum_id' => ['UINT', 0],
			],
			'PRIMARY_KEY' => 'session_id',
			'KEYS' => [
				'session_time' => ['INDEX', 'session_time'],
				'session_user_id' => ['INDEX', 'session_user_id'],
				'session_forum_id' => ['INDEX', 'session_forum_id'],
			],
		];
		$this->tools->sql_create_table('phpbb_sessions', $phpbb_sessions_table_data);

		$this->assertTrue($this->tools->sql_table_exists('phpbb_sessions'));
		$this->assertTrue($this->tools->sql_column_exists('phpbb_sessions', 'session_id'));
		$this->assertTrue($this->tools->sql_index_exists('phpbb_sessions', 'PRIMARY'));

		$this->apply_migration();

		$schema = $this->doctrine_db->createSchemaManager()->introspectSchema();
		$table = $schema->getTable('phpbb_sessions');

		$primary_key = $table->getPrimaryKey();
		$this->assertTrue($primary_key instanceof \Doctrine\DBAL\Schema\Index);

		$columns = $primary_key->getColumns();
		$this->assertEquals($columns[0], 'id');
		$this->assertTrue($this->tools->sql_index_exists('phpbb_sessions', 'session_id'));

		$this->revert_migration();

		$this->assertFalse($this->tools->sql_column_exists('phpbb_sessions', 'id'));
		$this->assertFalse($this->tools->sql_index_exists('phpbb_sessions', 'session_id'));

		$schema = $this->doctrine_db->createSchemaManager()->introspectSchema();
		$table = $schema->getTable('phpbb_sessions');

		$primary_key = $table->getPrimaryKey();
		$this->assertTrue($primary_key instanceof \Doctrine\DBAL\Schema\Index);

		$columns = $primary_key->getColumns();
		$this->assertEquals($columns[0], 'session_id');

		// Apply migration back
		$this->apply_migration();
	}
}
