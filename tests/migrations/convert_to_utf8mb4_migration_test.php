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

	public function test_convert_to_utf8mb4_migration()
	{
		if (($sql_layer = $this->db->get_sql_layer()) !== 'mysqli') // This test runs on MySQL/MariaDB only
		{
			$this->markTestSkipped($sql_layer . ': utf8mb4 charset only applies to MySQL/MariaDB DBMS');
		}

		$this->apply_migration();

		$index_data_row = $this->db_tools->sql_get_table_index_data('phpbb_ext');
		$index_name = $this->db_tools->generate_index_name('ext_name', 'phpbb_ext');
		$this->assertEquals(['ext_name'], $index_data_row[$index_name]['columns']);
		$this->asserttrue($index_data_row[$index_name]['is_unique']);
		$this->assertFalse($index_data_row[$index_name]['is_primary']);
		$this->assertEquals(191, $index_data_row[$index_name]['options']['lengths'][0]);

		$this->revert_migration();

		$index_data_row = $this->db_tools->sql_get_table_index_data('phpbb_ext');
		$this->assertEquals(null, $index_data_row[$index_name]['options']['lengths'][0]);

		// Apply migration back
		$this->apply_migration();
	}
}
