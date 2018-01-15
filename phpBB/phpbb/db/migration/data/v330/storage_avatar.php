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

class storage_avatar extends \phpbb\db\migration\migration
{
	public function update_data()
	{
		return array(
			array('config.add', array('storage\\avatar\\provider', \phpbb\storage\provider\local::class)),
			array('config.add', array('storage\\avatar\\config\\path', $this->config['avatar_path'])),
			array('config.remove', array('avatar_path')),
		);
	}
}
