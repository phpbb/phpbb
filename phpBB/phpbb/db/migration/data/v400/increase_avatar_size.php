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

class increase_avatar_size extends container_aware_migration
{
	public static function depends_on()
	{
		return ['\phpbb\db\migration\data\v400\dev'];
	}

	public function update_data()
	{
		return [
			['custom', [[$this, 'increase_size']]],
		];
	}

	public function increase_size(): void
	{
		$this->config->set('avatar_filesize', max(262144, $this->config['avatar_filesize'])); // Increase to 256 KiB
		$this->config->set('avatar_max_height', max('120', $this->config['avatar_max_height'])); // Increase to max 120px height
		$this->config->set('avatar_max_width', max('120', $this->config['avatar_max_width'])); // Increase to max 120px width
		$this->config->set('avatar_min_height', max('40', $this->config['avatar_min_height'])); // Increase to min 40px height
		$this->config->set('avatar_min_width', max('40', $this->config['avatar_min_width'])); // Increase to max 40px width
	}
}
