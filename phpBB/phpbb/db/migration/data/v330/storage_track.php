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

class storage_track extends \phpbb\db\migration\container_aware_migration
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
		/** @var storage $storage */
		$storage = $this->container->get('storage.avatar');

		$sql = 'SELECT user_avatar
			FROM ' . USERS_TABLE . "
			WHERE user_avatar_type = 'avatar.driver.upload'";

		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$avatar_group = false;
			$filename = $row['user_avatar'];

			if (isset($filename[0]) && $filename[0] === 'g')
			{
				$avatar_group = true;
				$filename = substr($filename, 1);
			}

			$ext		= substr(strrchr($filename, '.'), 1);
			$filename	= (int) $filename;

			try
			{
				$storage->track_file($this->config['avatar_salt'] . '_' . ($avatar_group ? 'g' : '') . $filename . '.' . $ext);
			}
			catch (\phpbb\storage\exception\exception $e)
			{
				// If file don't exist, don't track it
			}
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
			try
			{
				$storage->track_file($row['physical_filename']);
			}
			catch (\phpbb\storage\exception\exception $e)
			{
				// If file don't exist, don't track it
			}

			if ($row['thumbnail'] == 1)
			{
				try
				{
					$storage->track_file('thumb_' . $row['physical_filename']);
				}
				catch (\phpbb\storage\exception\exception $e)
				{
					// If file don't exist, don't track it
				}
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
			try
			{
				$storage->track_file($row['filename']);
			}
			catch (\phpbb\storage\exception\exception $e)
			{
				// If file don't exist, don't track it
			}
		}

		$this->db->sql_freeresult($result);
	}
}
