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

use phpbb\search\backend\fulltext_sphinx;

class phpbb_migrations_sphinx_to_sphinxql_migration_test extends phpbb_migration_test_base
{
	protected $migration_class = '\phpbb\db\migration\data\v400\sphinx_to_sphinxql';
	protected $fixture = '/fixtures/migration_sphinx_to_sphinxql.xml';

	protected function tearDown(): void
	{
		if ($this->db_tools && $this->db_tools->sql_table_exists('phpbb_sphinx'))
		{
			$this->db_tools->sql_table_drop('phpbb_sphinx');
		}

		parent::tearDown();
	}

	public function test_transparent_sphinx_to_sphinxql_migration(): void
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

		// Legacy phpbb_sphinx counter table dropped
		$this->assertFalse($this->db_tools->sql_table_exists('phpbb_sphinx'));

		// Active backend, board ID, data path, and host preserved transparently
		$this->assertSame(fulltext_sphinx::class, $this->config['search_type']);
		$this->assertSame('/var/lib/sphinx/', $this->config['fulltext_sphinx_data_path']);
		$this->assertSame('10.0.0.15', $this->config['fulltext_sphinx_host']);
		$this->assertSame('existingid99', $this->config['fulltext_sphinx_id']);

		// Legacy SphinxAPI port 9312 reset to empty (uses SphinxQL default 9306); custom 256 MiB limit preserved
		$this->assertSame('', (string) $this->config['fulltext_sphinx_port']);
		$this->assertSame(256, (int) $this->config['fulltext_sphinx_rt_mem_limit']);
		$this->assertSame(0, (int) $this->config['fulltext_sphinx_indexed']);

		// Obsolete legacy config keys removed
		foreach (['fulltext_sphinx_indexer_mem_limit', 'fulltext_sphinx_stopwords'] as $removed_key)
		{
			$sql = "SELECT config_name
				FROM phpbb_config
				WHERE config_name = '" . $this->db->sql_escape($removed_key) . "'";
			$result = $this->db->sql_query($sql);
			$this->assertFalse($this->db->sql_fetchfield('config_name'));
			$this->db->sql_freeresult($result);
		}

		// Reverting and re-applying with custom non-9312 port and default 512 indexer limit preserves custom port and uses 128 MiB RT default
		$this->revert_migration();
		$this->config->set('fulltext_sphinx_port', '9307');
		$this->config->set('fulltext_sphinx_indexer_mem_limit', 512);
		$this->apply_migration();

		$this->assertSame('9307', (string) $this->config['fulltext_sphinx_port']);
		$this->assertSame(128, (int) $this->config['fulltext_sphinx_rt_mem_limit']);
	}
}
