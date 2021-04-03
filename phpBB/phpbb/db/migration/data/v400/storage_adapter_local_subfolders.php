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

class storage_adapter_local_subfolders extends migration
{
	public function effectively_installed()
	{
		return $this->config->offsetExists('storage\\attachment\\config\\subfolders') ||
			$this->config->offsetExists('storage\\avatar\\config\\subfolders') ||
			$this->config->offsetExists('storage\\backup\\config\\subfolders');
	}

	public static function depends_on()
	{
		return [
			'\phpbb\db\migration\data\v400\storage_attachment',
			'\phpbb\db\migration\data\v400\storage_avatar',
			'\phpbb\db\migration\data\v400\storage_backup',
		];
	}

	public function update_data()
	{
		return [
			['if', [
				($this->config['storage\\attachment\\provider'] == local::class),
				['config.add', ['storage\\attachment\\config\\subfolders', '0']],
			]],
			['if', [
				($this->config['storage\\avatar\\provider'] == local::class),
				['config.add', ['storage\\avatar\\config\\subfolders', '0']],
			]],
			['if', [
				($this->config['storage\\backup\\provider'] == local::class),
				['config.add', ['storage\\backup\\config\\subfolders', '0']],
			]],
		];
	}
}
