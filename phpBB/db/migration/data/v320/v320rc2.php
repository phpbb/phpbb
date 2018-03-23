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

namespace phpbb\db\migration\data\v320;

use phpbb\db\migration\migration;

class v320rc2 extends migration
{
	public function effectively_installed()
	{
		return version_compare($this->config['version'], '3.2.0-RC2', '>=');
	}

	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v31x\remove_duplicate_migrations',
			'\phpbb\db\migration\data\v31x\add_log_time_index',
			'\phpbb\db\migration\data\v320\add_help_phpbb',
		);
	}

	public function update_data()
	{
		return array(
			array('config.update', array('version', '3.2.0-RC2')),
		);
	}
}
