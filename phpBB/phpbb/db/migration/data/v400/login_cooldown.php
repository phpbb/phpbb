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

class login_cooldown extends migration
{
	public function effectively_installed(): bool
	{
		return $this->config->offsetExists('login_cooldown_min')
			&& $this->config->offsetExists('login_cooldown_max');
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
			['config.add', ['login_cooldown_min', 60]],
			['config.add', ['login_cooldown_max', 600]],
		];
	}

	public function revert_data(): array
	{
		return [
			['config.remove', ['login_cooldown_min']],
			['config.remove', ['login_cooldown_max']],
		];
	}
}
