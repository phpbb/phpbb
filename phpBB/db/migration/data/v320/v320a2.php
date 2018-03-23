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

class v320a2 extends \phpbb\db\migration\container_aware_migration
{
	public function effectively_installed()
	{
		return version_compare($this->config['version'], '3.2.0-a2', '>=');
	}

	static public function depends_on()
	{
		return 		array(
			'\phpbb\db\migration\data\v31x\v317rc1',
			'\phpbb\db\migration\data\v320\text_reparser',
			'\phpbb\db\migration\data\v320\v320a1',
		);
	}

	public function update_data()
	{
		return array(
			array('config.update', array('version', '3.2.0-a2')),
		);
	}
}
