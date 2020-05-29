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

class font_awesome_5_update extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return [
			'\phpbb\db\migration\data\v330\v330',
			'\phpbb\db\migration\data\v32x\font_awesome_update_cdn',
		];
	}

	public function update_data()
	{
		return [
			['config.update', ['load_font_awesome_url', 'https://use.fontawesome.com/releases/v5.13.0/css/all.css']],
		];
	}
}
