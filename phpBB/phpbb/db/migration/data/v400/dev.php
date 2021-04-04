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

class dev extends migration
{
	public function effectively_installed()
	{
		return version_compare($this->config['version'], '4.0.0-dev', '>=');
	}

	public static function depends_on()
	{
		return ['\phpbb\db\migration\data\v330\v330rc1'];
	}

	public function update_data()
	{
		return [
			['config.update', ['version', '4.0.0-dev']],
		];
	}
}
