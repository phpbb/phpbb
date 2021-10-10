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

use phpbb\attachment\attachment_category;

class add_video_files_attachment_group extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\phpbb\db\migration\data\v400\dev'];
	}

	public function update_data()
	{
		return [
			['custom', [[$this, 'add_video_files']]],
		];
	}

	public function add_video_files()
	{
		$sql = 'SELECT group_id
			FROM ' . $this->table_prefix . 'extension_groups
			WHERE ' . $this->db->sql_build_array('SELECT', ['group_name' => 'VIDEO_FILES']);
		$result = $this->db->sql_query($sql);
		$video_group_id = $this->db->sql_fetchfield('group_id');
		$this->db->sql_freeresult($result);

		if ($video_group_id === false)
		{
			$sql = 'INSERT INTO ' . $this->table_prefix . 'extension_groups ' . $this->db->sql_build_array('INSERT', [
				'group_name'		=> 'VIDEO_FILES',
				'cat_id'			=> attachment_category::VIDEO,
				'allow_group'		=> 0,
				'upload_icon'		=> '',
				'max_filesize'		=> 0,
				'allowed_forums'	=> '',
			]);
			$this->db->sql_query($sql);
			$video_group_id = $this->db->sql_nextid();
		}
		else
		{
			$sql = 'UPDATE ' . $this->table_prefix . 'extension_groups SET cat_id = ' . attachment_category::VIDEO . '
				WHERE ' . $this->db->sql_build_array('SELECT', ['group_id' => $video_group_id]);
			$this->db->sql_query($sql);
		}

		$video_extensions = ['mp4', 'ogg', 'webm'];

		foreach ($video_extensions as $video_extension)
		{
			$sql = 'SELECT group_id
				FROM ' . $this->table_prefix . 'extensions
				WHERE ' . $this->db->sql_build_array('SELECT', ['extension' => $video_extension]);
			$result = $this->db->sql_query($sql);
			$extension_group_id = $this->db->sql_fetchfield('group_id');
			$this->db->sql_freeresult($result);

			if ($extension_group_id === false)
			{
				$sql = 'INSERT INTO ' . $this->table_prefix . 'extensions ' . $this->db->sql_build_array('INSERT', [
					'group_id'	=> $video_group_id,
					'extension'	=> $video_extension,
				]);
				$this->db->sql_query($sql);
			}
			else if ($extension_group_id != $video_group_id)
			{
				$sql = 'UPDATE ' . $this->table_prefix . "extensions SET group_id = $video_group_id
					WHERE " . $this->db->sql_build_array('SELECT', ['extension' => $video_extension]);
				$this->db->sql_query($sql);
			}
		}
	}
}
