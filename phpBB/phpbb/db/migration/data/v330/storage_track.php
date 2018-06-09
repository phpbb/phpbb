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

use phpbb\storage\storage;

class storage_track extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v330\storage_attachment',
			'\phpbb\db\migration\data\v330\storage_avatar',
			'\phpbb\db\migration\data\v330\storage_backup',
		);
	}

	public function update_schema()
	{
		return array(
			'add_tables' => array(
				STORAGE_TABLE	=> array(
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
		/** @var storage $storage */
		$storage = $this->container->get('storage.avatar');

		$sql = 'SELECT user_avatar
			FROM ' . USERS_TABLE . "
			WHERE user_avatar_type = 'avatar.driver.upload'";

		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$storage->track_file($row['user_avatar']);
		}
		$this->db->sql_freeresult($result);
	}

	public function track_attachments()
	{
		/** @var storage $storage */
		$storage = $this->container->get('storage.attachment');

		$sql = 'SELECT physical_filename, thumbnail
			FROM ' . ATTACHMENTS_TABLE;

		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$storage->track_file($row['physical_filename']);

			if($row['thumbnail'] == 1)
			{
				$storage->track_file('thumb_' . $row['physical_filename']);
			}
		}
		$this->db->sql_freeresult($result);
	}

	public function track_backups()
	{
		/** @var storage $storage */
		$storage = $this->container->get('storage.backup');

		$sql = 'SELECT filename
			FROM ' . BACKUPS_TABLE;

		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$storage->track_file($row['filename']);
		}
		$this->db->sql_freeresult($result);
	}
}
