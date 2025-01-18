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

namespace phpbb\db\migration\data\v400;

use phpbb\db\migration\container_aware_migration;
use phpbb\storage\exception\storage_exception;
use phpbb\storage\file_tracker;

class storage_track extends container_aware_migration
{
	private const BATCH_SIZE = 100;

	public function effectively_installed()
	{
		return $this->db_tools->sql_table_exists($this->tables['storage']);
	}

	public static function depends_on()
	{
		return [
			'\phpbb\db\migration\data\v400\storage_attachment',
			'\phpbb\db\migration\data\v400\storage_avatar',
			'\phpbb\db\migration\data\v400\storage_backup',
			'\phpbb\db\migration\data\v400\storage_backup_data',
		];
	}

	public function update_schema()
	{
		return [
			'add_tables' => [
				$this->table_prefix . 'storage'	=> [
					'COLUMNS' => [
						'file_id'			=> ['UINT', null, 'auto_increment'],
						'file_path'			=> ['VCHAR', ''],
						'storage'			=> ['VCHAR', ''],
						'filesize'			=> ['UINT:20', 0],
					],
					'PRIMARY_KEY'	=> 'file_id',
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_tables'	=> [
				$this->table_prefix . 'storage',
			],
		];
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
		/** @var file_tracker $file_tracker */
		$file_tracker = $this->container->get('storage.file_tracker');

		$sql = 'SELECT user_avatar
			FROM ' . USERS_TABLE . "
			WHERE user_avatar_type = 'avatar.driver.upload'";
		$result = $this->db->sql_query($sql);

		$files = [];
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

			$filename = $this->config['avatar_salt'] . '_' . ($avatar_group ? 'g' : '') . $filename . '.' . $ext;
			$files[] = [
				'file_path' => $filename,
				'filesize' => filesize($this->phpbb_root_path . $this->config['storage\\avatar\\config\\path'] . '/' . $filename),
			];

			if (count($files) >= self::BATCH_SIZE)
			{
				$file_tracker->track_files('avatar', $files);
				$files = [];
			}
		}

		if (!empty($files))
		{
			$file_tracker->track_files('avatar', $files);
		}

		$this->db->sql_freeresult($result);
	}

	public function track_attachments()
	{
		/** @var file_tracker $file_tracker */
		$file_tracker = $this->container->get('storage.file_tracker');

		$sql = 'SELECT physical_filename, thumbnail
			FROM ' . ATTACHMENTS_TABLE;
		$result = $this->db->sql_query($sql);

		$files = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$files[] = [
				'file_path' => $row['physical_filename'],
				'filesize' => filesize($this->phpbb_root_path . $this->config['storage\\attachment\\config\\path'] . '/' . $row['physical_filename']),
			];

			if ($row['thumbnail'] == 1)
			{
				$files[] = [
					'file_path' => 'thumb_' . $row['physical_filename'],
					'filesize' => filesize($this->phpbb_root_path . $this->config['storage\\attachment\\config\\path'] . '/thumb_' . $row['physical_filename']),
				];
			}

			if (count($files) >= self::BATCH_SIZE)
			{
				$file_tracker->track_files('attachment', $files);
				$files = [];
			}
		}

		if (!empty($files))
		{
			$file_tracker->track_files('attachment', $files);
		}

		$this->db->sql_freeresult($result);
	}

	public function track_backups()
	{
		/** @var file_tracker $file_tracker */
		$file_tracker = $this->container->get('storage.file_tracker');

		$sql = 'SELECT filename
			FROM ' . BACKUPS_TABLE;
		$result = $this->db->sql_query($sql);

		$files = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$files[] = [
				'file_path' => $row['filename'],
				'filesize' => filesize($this->phpbb_root_path . $this->config['storage\\backup\\config\\path'] . '/' . $row['filename']),
			];

			if (count($files) >= self::BATCH_SIZE)
			{
				$file_tracker->track_files('backup', $files);
				$files = [];
			}
		}

		if (!empty($files))
		{
			$file_tracker->track_files('backup', $files);
		}

		$this->db->sql_freeresult($result);
	}
}
