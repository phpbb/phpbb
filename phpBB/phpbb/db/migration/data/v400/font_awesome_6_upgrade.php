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

class font_awesome_6_upgrade extends migration
{
	public static function depends_on(): array
	{
		return [
			'\phpbb\db\migration\data\v400\dev',
			'\phpbb\db\migration\data\v33x\font_awesome_5_rollback',
		];
	}

	public function update_data(): array
	{
		return [
			['config.update', ['load_font_awesome_url', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css']],
		];
	}
}
