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

class add_disable_board_access_config extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return [
			'\phpbb\db\migration\data\v400\dev',
		];
	}

	public function update_data()
	{
		return [
			['config.add', ['board_disable_access', '2']],
		];
	}
}
