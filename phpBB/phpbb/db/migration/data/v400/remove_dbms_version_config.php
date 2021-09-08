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

class remove_dbms_version_config extends migration
{
	public function effectively_installed()
	{
		return !$this->config->offsetExists('dbms_version');
	}

	public function update_data()
	{
		return [
			['config.remove', ['dbms_version']],
		];
	}

	public static function depends_on()
	{
		return [
			'\phpbb\db\migration\data\v30x\release_3_0_3_rc1',
		];
	}
}
