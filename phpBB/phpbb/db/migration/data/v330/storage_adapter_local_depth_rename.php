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

class storage_adapter_local_depth_rename extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v330\storage_adapter_local_depth',
		);
	}

	public function update_data()
	{
		return array(
			array('if', array(
				($this->config['storage\\attachment\\provider'] == \phpbb\storage\provider\local::class),
				array('config.delete', array('storage\\attachment\\config\\depth')),
				array('config.add', array('storage\\attachment\\config\\subfolders', '0')),
			)),
			array('if', array(
				($this->config['storage\\avatar\\provider'] == \phpbb\storage\provider\local::class),
				array('config.delete', array('storage\\avatar\\config\\depth')),
				array('config.add', array('storage\\avatar\\config\\subfolders', '0')),
			)),
			array('if', array(
				($this->config['storage\\backup\\provider'] == \phpbb\storage\provider\local::class),
				array('config.delete', array('storage\\backup\\config\\depth')),
				array('config.add', array('storage\\backup\\config\\subfolders', '0')),
			)),
		);
	}
}
