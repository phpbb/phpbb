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

class storage_adapter_local_subfolders extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v330\storage_attachment',
			'\phpbb\db\migration\data\v330\storage_avatar',
			'\phpbb\db\migration\data\v330\storage_backup',
		);
	}

	public function update_data()
	{
		return array(
			array('if', array(
				($this->config['storage\\attachment\\provider'] == \phpbb\storage\provider\local::class),
				array('config.add', array('storage\\attachment\\config\\subfolders', '0')),
			)),
			array('if', array(
				($this->config['storage\\avatar\\provider'] == \phpbb\storage\provider\local::class),
				array('config.add', array('storage\\avatar\\config\\subfolders', '0')),
			)),
			array('if', array(
				($this->config['storage\\backup\\provider'] == \phpbb\storage\provider\local::class),
				array('config.add', array('storage\\backup\\config\\subfolders', '0')),
			)),
		);
	}
}
