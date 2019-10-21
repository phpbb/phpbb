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

class v330b1 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return version_compare($this->config['version'], '3.3.0-b1', '>=');
	}

	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v330\jquery_update',
			'\phpbb\db\migration\data\v330\reset_password',
			'\phpbb\db\migration\data\v330\remove_attachment_flash',
			'\phpbb\db\migration\data\v330\remove_max_pass_chars',
			'\phpbb\db\migration\data\v32x\v328',
			'\phpbb\db\migration\data\v330\dev',
		);
	}

	public function update_data()
	{
		return array(
			array('config.update', array('version', '3.3.0-b1')),
		);
	}
}
