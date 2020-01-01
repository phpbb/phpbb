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

class storage_attachment extends \phpbb\db\migration\migration
{
	public function update_data()
	{
		return array(
			array('config.add', array('storage\\attachment\\provider', \phpbb\storage\provider\local::class)),
			array('config.add', array('storage\\attachment\\config\\path', $this->config['upload_path'])),
			array('config.remove', array('upload_path')),
		);
	}
}
