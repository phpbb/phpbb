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

use phpbb\search\backend\fulltext_native;
use phpbb\search\backend\fulltext_sphinxql;

class phpbb_migrations_remove_sphinx_legacy_migration_test extends phpbb_migration_test_base
{
	protected $migration_class = '\phpbb\db\migration\data\v400\remove_sphinx_legacy';
	protected $fixture = '/fixtures/migration_remove_sphinx_legacy.xml';

	protected function tearDown(): void
	{
		if ($this->db_tools && $this->db_tools->sql_table_exists('phpbb_sphinx'))
		{
			$this->db_tools->sql_table_drop('phpbb_sphinx');
		}

		parent::tearDown();
	}

	public function test_remove_sphinx_legacy_migration(): void
	{
		$this->db_tools->sql_create_table('phpbb_sphinx', [
			'COLUMNS'		=> [
				'counter_id'	=> ['UINT', 0],
				'max_doc_id'	=> ['UINT', 0],
			],
			'PRIMARY_KEY'	=> 'counter_id',
		]);
		$this->assertTrue($this->db_tools->sql_table_exists('phpbb_sphinx'));

		$this->apply_migration();

		$this->assertFalse($this->db_tools->sql_table_exists('phpbb_sphinx'));

		$removed_keys = [
			'fulltext_sphinx_data_path',
			'fulltext_sphinx_host',
			'fulltext_sphinx_id',
			'fulltext_sphinx_indexer_mem_limit',
			'fulltext_sphinx_port',
			'fulltext_sphinx_stopwords',
		];

		foreach ($removed_keys as $config_name)
		{
			$sql = "SELECT config_name
				FROM phpbb_config
				WHERE config_name = '" . $this->db->sql_escape($config_name) . "'";
			$result = $this->db->sql_query($sql);
			$this->assertFalse($this->db->sql_fetchfield('config_name'));
			$this->db->sql_freeresult($result);
		}

		$this->assertSame(fulltext_native::class, $this->config['search_type']);

		// Reverting and re-applying without phpbb_sphinx table and with fulltext_sphinxql active must preserve fulltext_sphinxql
		$this->revert_migration();
		$this->config->set('search_type', fulltext_sphinxql::class);
		$this->apply_migration();

		$this->assertSame(fulltext_sphinxql::class, $this->config['search_type']);
	}
}
