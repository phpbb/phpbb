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

use phpbb\db\migration\container_aware_migration;

class remove_remote_upload extends container_aware_migration
{
	public static function depends_on()
	{
		return [
			'\phpbb\db\migration\data\v320\remote_upload_validation'
		];
	}

	public function update_data()
	{
		return [
			['config.remove', ['remote_upload_verify']],
		];
	}
}
