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

class add_sphinxql_search_settings extends \phpbb\db\migration\migration
{
	public function effectively_installed(): bool
	{
		return isset($this->config['fulltext_sphinxql_rt_mem_limit']);
	}

	public static function depends_on(): array
	{
		return [
			'\phpbb\db\migration\data\v400\v400a2',
		];
	}

	public function update_data(): array
	{
		return [
			['config.add', ['fulltext_sphinxql_data_path', '']],
			['config.add', ['fulltext_sphinxql_host', '']],
			['config.add', ['fulltext_sphinxql_id', '']],
			['config.add', ['fulltext_sphinxql_indexed', 0]],
			['config.add', ['fulltext_sphinxql_port', '']],
			['config.add', ['fulltext_sphinxql_rt_mem_limit', 128]],
		];
	}
}
