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

namespace phpbb\db\migration\data\v33x;

use phpbb\db\migration\migration;

class add_version_check_cron extends migration
{
	public function effectively_installed(): bool
	{
		return $this->config->offsetExists('version_check_last_cron');
	}

	public function update_data(): array
	{
		return [
			['config.add', ['version_check_interval', 60]], // 60 minutes
			['config.add', ['version_check_last_cron', 0]], // Last run timestamp
		];
	}
}
