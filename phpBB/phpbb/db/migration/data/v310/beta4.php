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

namespace phpbb\db\migration\data\v310;

class beta4 extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v310\beta3',
			'\phpbb\db\migration\data\v310\extensions_version_check_force_unstable',
			'\phpbb\db\migration\data\v310\reset_missing_captcha_plugin',
		);
	}

	public function update_data()
	{
		return array(
			array('config.update', array('version', '3.1.0-b4')),
		);
	}
}
