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

namespace phpbb\db\migration\data\v330;

class v330 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return version_compare($this->config['version'], '3.3.0', '>=');
	}

	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v32x\v329',
			'\phpbb\db\migration\data\v330\v330rc1',
		);
	}

	public function update_data()
	{
		return array(
			array('config.update', array('version', '3.3.0')),
		);
	}
}
