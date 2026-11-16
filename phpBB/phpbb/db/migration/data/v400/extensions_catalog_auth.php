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

class extensions_catalog_auth extends \phpbb\db\migration\migration
{
	public static function depends_on(): array
	{
		return [
			'\phpbb\db\migration\data\v400\extensions_composer',
			'\phpbb\db\migration\data\v400\extensions_composer_2',
			'\phpbb\db\migration\data\v400\extensions_composer_3',
			'\phpbb\db\migration\data\v400\extensions_composer_4',
		];
	}

	public function update_data(): array
	{
		return [
			['custom', [[$this, 'update_module_auth']]],
		];
	}

	public function revert_data(): array
	{
		return [
			['custom', [[$this, 'revert_module_auth']]],
		];
	}

	public function update_module_auth(): void
	{
		$sql = 'UPDATE ' . $this->table_prefix . "modules
			SET module_auth = 'acl_a_extensions && diparam_extensions.enable_catalog'
			WHERE module_class = 'acp'
				AND module_basename = 'acp_extensions'
				AND module_mode = 'catalog'";
		$this->sql_query($sql);
	}

	public function revert_module_auth(): void
	{
		$sql = 'UPDATE ' . $this->table_prefix . "modules
			SET module_auth = 'acl_a_extensions'
			WHERE module_class = 'acp'
				AND module_basename = 'acp_extensions'
				AND module_mode = 'catalog'";
		$this->sql_query($sql);
	}
}
