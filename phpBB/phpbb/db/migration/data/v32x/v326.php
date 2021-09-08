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

class v326 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return phpbb_version_compare($this->config['version'], '3.2.6', '>=');
	}

	public static function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v32x\v326rc1',
			'\phpbb\db\migration\data\v32x\disable_remote_avatar',
			'\phpbb\db\migration\data\v32x\smtp_dynamic_data',
		);

	}

	public function update_data()
	{
		return array(
			array('config.update', array('version', '3.2.6')),
		);
	}
}
