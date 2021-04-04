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
use phpbb\storage\provider\local;

class storage_avatar extends migration
{
	public function effectively_installed()
	{
		return $this->config->offsetExists('storage\\avatar\\provider');
	}

	public static function depends_on()
	{
		return [
			'\phpbb\db\migration\data\v400\dev',
		];
	}

	public function update_data()
	{
		return [
			['config.add', ['storage\\avatar\\provider', local::class]],
			['config.add', ['storage\\avatar\\config\\path', $this->config['avatar_path']]],
			['config.remove', ['avatar_path']],
		];
	}
}
