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

namespace phpbb\db\migration\data\v400;

use phpbb\db\migration\migration;

class sphinx_to_sphinxql extends migration
{
	public function effectively_installed(): bool
	{
		return isset($this->config['fulltext_sphinx_rt_mem_limit'])
			&& !isset($this->config['fulltext_sphinx_indexer_mem_limit'])
			&& !$this->db_tools->sql_table_exists($this->table_prefix . 'sphinx');
	}

	public static function depends_on(): array
	{
		return [
			'\phpbb\db\migration\data\v400\v400a2',
		];
	}

	public function update_schema(): array
	{
		return [
			'drop_tables' => [
				$this->table_prefix . 'sphinx',
			],
		];
	}

	public function update_data(): array
	{
		return [
			['config.add', ['fulltext_sphinx_data_path', '']],
			['config.add', ['fulltext_sphinx_host', '']],
			['config.add', ['fulltext_sphinx_id', '']],
			['config.add', ['fulltext_sphinx_indexed', 0]],
			['config.add', ['fulltext_sphinx_port', '']],
			['config.add', ['fulltext_sphinx_rt_mem_limit', 128]],
			['custom', [[$this, 'migrate_legacy_sphinx_settings']]],
			['config.remove', ['fulltext_sphinx_indexer_mem_limit']],
			['config.remove', ['fulltext_sphinx_stopwords']],
		];
	}

	public function migrate_legacy_sphinx_settings(): void
	{
		// Reset legacy SphinxAPI binary protocol default port (9312) so SphinxQL uses default port 9306
		if (isset($this->config['fulltext_sphinx_port']) && (string) $this->config['fulltext_sphinx_port'] === '9312')
		{
			$this->config->set('fulltext_sphinx_port', '');
		}

		// Preserve any custom administrator memory limit (different from legacy default 512 MiB)
		if (isset($this->config['fulltext_sphinx_indexer_mem_limit']))
		{
			$legacy_mem_limit = (int) $this->config['fulltext_sphinx_indexer_mem_limit'];
			if ($legacy_mem_limit >= 8 && $legacy_mem_limit !== 512)
			{
				$this->config->set('fulltext_sphinx_rt_mem_limit', $legacy_mem_limit);
			}
		}
	}
}
