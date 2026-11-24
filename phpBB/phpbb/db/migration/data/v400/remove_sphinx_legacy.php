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
use phpbb\search\backend\fulltext_native;

class remove_sphinx_legacy extends migration
{
	public function effectively_installed(): bool
	{
		return !isset($this->config['fulltext_sphinx_indexer_mem_limit'])
			&& !$this->db_tools->sql_table_exists($this->table_prefix . 'sphinx');
	}

	public static function depends_on(): array
	{
		return [
			'\phpbb\db\migration\data\v400\v400a2',
			'\phpbb\db\migration\data\v400\add_sphinxql_search_settings',
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
			['config.remove', ['fulltext_sphinx_data_path']],
			['config.remove', ['fulltext_sphinx_host']],
			['config.remove', ['fulltext_sphinx_id']],
			['config.remove', ['fulltext_sphinx_indexer_mem_limit']],
			['config.remove', ['fulltext_sphinx_port']],
			['config.remove', ['fulltext_sphinx_stopwords']],
			['custom', [[$this, 'replace_legacy_sphinx_search_backend']]],
		];
	}

	public function replace_legacy_sphinx_search_backend(): void
	{
		if (in_array($this->config['search_type'], ['\\phpbb\\search\\fulltext_sphinx', 'phpbb\\search\\backend\\fulltext_sphinx'], true))
		{
			$this->config->set('search_type', fulltext_native::class);
		}
	}
}
