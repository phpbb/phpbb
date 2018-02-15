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

class storage_track extends \phpbb\db\migration\migration
{
	public function update_schema()
	{
		return array(
			'add_tables' => array(
				$this->table_prefix . 'storage'	=> array(
					'COLUMNS' => array(
						'file_id'			=> array('UINT', null, 'auto_increment'),
						'file_path'			=> array('VCHAR', ''),
						'storage'			=> array('VCHAR', ''),
						'filesize'			=> array('UINT:20', 0),
					),
					'PRIMARY_KEY'	=> 'file_id',
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_tables'	=> array(
				$this->table_prefix . 'storage',
			),
		);
	}

	public function update_data()
	{
		return [
			['custom', [[$this, 'track_avatars']]],
			['custom', [[$this, 'track_attachments']]],
			['custom', [[$this, 'track_backups']]],
		];
	}

	public function track_avatars()
	{

	}

	public function track_attachments()
	{

	}

	public function track_backups()
	{

	}
}
