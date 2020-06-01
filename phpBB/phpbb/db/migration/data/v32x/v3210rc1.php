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

namespace phpbb\db\migration\data\v32x;

class v3210rc1 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return phpbb_version_compare($this->config['version'], '3.2.10-RC1', '>=');
	}

	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v32x\v329',
			'\phpbb\db\migration\data\v32x\add_plupload_config',
			'\phpbb\db\migration\data\v32x\font_awesome_update_cdn',
		);
	}

	public function update_data()
	{
		return array(
			array('config.update', array('version', '3.2.10-RC1')),
		);
	}
}
