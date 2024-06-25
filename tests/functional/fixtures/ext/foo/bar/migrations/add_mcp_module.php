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

namespace foo\bar\migrations;

class add_mcp_module extends \phpbb\db\migration\container_aware_migration
{
	public function update_data()
	{
		// Add MCP module
		return [
			['module.add', ['mcp', 'MCP_MAIN', 'MCP_FOOBAR']],
			['module.add', ['mcp', 'MCP_FOOBAR', [
				'module_basename'	=> '\foo\bar\mcp\main_module',
				'module_langname'	=> 'MCP_FOOBAR_TITLE',
				'module_mode'		=> 'mode',
				'module_auth'		=> '',
			]]],
		];
	}
}
