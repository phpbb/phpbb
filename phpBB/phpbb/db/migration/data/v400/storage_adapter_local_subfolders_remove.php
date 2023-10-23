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

class storage_adapter_local_subfolders_remove extends migration
{
	public function effectively_installed()
	{
		return !$this->config->offsetExists('storage\\attachment\\config\\subfolders') &&
			!$this->config->offsetExists('storage\\avatar\\config\\subfolders') &&
			!$this->config->offsetExists('storage\\backup\\config\\subfolders');
	}

	public static function depends_on()
	{
		return [
			'\phpbb\db\migration\data\v400\storage_adapter_local_subfolders',
		];
	}

	public function update_data()
	{
		return [
			['config.remove', ['storage\\attachment\\config\\subfolders']],
			['config.remove', ['storage\\avatar\\config\\subfolders']],
			['config.remove', ['storage\\backup\\config\\subfolders']],
		];
	}
}
