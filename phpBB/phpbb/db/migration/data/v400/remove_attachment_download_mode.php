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

class remove_attachment_download_mode extends \phpbb\db\migration\migration
{
	public function update_schema()
	{
		return array(
			'drop_columns'	=> array(
				$this->table_prefix . 'extension_groups'			=> array(
					'download_mode',
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'add_columns'	=> array(
				$this->table_prefix . 'extension_groups'			=> array(
					'download_mode'		=> array('BOOL', '1'),
				),
			),
		);
	}
}
