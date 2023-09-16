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

class add_ucp_module extends \phpbb\db\migration\container_aware_migration
{
	public function update_data()
	{
		// Add UCP module
		return [
			['module.add', ['ucp', 'UCP_MAIN', 'UCP_FOOBAR']],
			['module.add', ['ucp', 'UCP_FOOBAR', [
				'module_basename'	=> '\foo\bar\ucp\main_module',
				'module_langname'	=> 'UCP_FOOBAR_TITLE',
				'module_mode'		=> 'mode',
				'module_auth'		=> '',
			]]],
		];
	}
}
