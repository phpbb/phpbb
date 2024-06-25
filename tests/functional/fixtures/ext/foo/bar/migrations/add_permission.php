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

class add_permission extends \phpbb\db\migration\container_aware_migration
{
	public function update_data()
	{
		// Add global permission
		return [
			['permission.add', ['u_foo', true]],
		];
	}
}
