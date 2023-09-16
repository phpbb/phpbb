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

class add_acp_module extends \phpbb\db\migration\migration
{
	public function update_data()
	{
		// Add ACP module
		return [
			['module.add', ['acp', 'ACP_CAT_DOT_MODS', 'ACP_FOOBAR']],
			['module.add', ['acp', 'ACP_FOOBAR', [
					'module_basename'	=> '\foo\bar\acp\main_module',
					'module_langname'	=> 'ACP_FOOBAR_TITLE',
					'module_mode'		=> 'mode',
					'module_auth'		=> '',
			]]],
		];
	}
}
