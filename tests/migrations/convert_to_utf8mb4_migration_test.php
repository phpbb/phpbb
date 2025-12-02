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

class phpbb_migrations_convert_to_utf8mb4_migration_test extends phpbb_migration_test_base
{

	protected $migration_class = '\phpbb\db\migration\data\v400\convert_to_utf8mb4';
	protected $fixture = '/fixtures/migration_convert_to_utf8mb4.xml';

	protected function setUp(): void
	{
		$this->tables = [
			'phpbb_migrations',
			'phpbb_config',
			'phpbb_config_text',
			'phpbb_oauth_accounts',
			'phpbb_oauth_tokens',
			'phpbb_oauth_states',
			'phpbb_ext',
			'phpbb_notification_types',
			'phpbb_search_wordlist',
			'phpbb_storage',
			'phpbb_styles',
			'phpbb_users',
			'phpbb_groups',
			'phpbb_login_attempts',
			'phpbb_posts',
		];
		parent::setUp();
		
	}

	public function test_convert_to_utf8mb4_migration()
	{
		if (($sql_layer = $this->db->get_sql_layer()) !== 'mysqli') // This test runs on MySQL/MariaDB only
		{
			$this->markTestSkipped($sql_layer . ': utf8mb4 charset only applies to MySQL/MariaDB DBMS');
		}

		$this->apply_migration();

		$short_table_name = \phpbb\db\doctrine\table_helper::generate_shortname('ext');
		$index_data_row = $this->db_tools->sql_get_table_index_data('phpbb_ext');
		$index = $short_table_name . '_ext_name';
		$this->assertEquals(['ext_name'], $index_data_row[$index]['columns']);
		$this->asserttrue($index_data_row[$index]['is_unique']);
		$this->assertFalse($index_data_row[$index]['is_primary']);
		$this->assertEquals(191, $index_data_row[$index]['options']['lengths'][0]);

		$this->revert_migration();

		$this->assertEquals(null, $index_data_row[$index]['options']['lengths'][0]);

		// Apply migration back
		$this->apply_migration();
	}
}
