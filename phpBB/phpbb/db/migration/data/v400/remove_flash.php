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

class remove_flash extends migration
{
	public function effectively_installed()
	{
		return !$this->config->offsetExists('allow_post_flash');
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
			['config.remove', ['auth_flash_pm']],
			['config.remove', ['allow_post_flash']],
			['config.remove', ['allow_sig_flash']],

			['permission.remove', ['f_flash', false]],
			['permission.remove', ['u_pm_flash']],
		];
	}
}
