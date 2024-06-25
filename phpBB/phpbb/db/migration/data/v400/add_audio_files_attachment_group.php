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

class add_audio_files_attachment_group extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\phpbb\db\migration\data\v400\dev'];
	}

	public function update_data()
	{
		return [
			['custom', [[$this, 'add_audio_files']]],
		];
	}

	public function add_audio_files()
	{
		$sql = 'SELECT group_id
			FROM ' . $this->table_prefix . 'extension_groups
			WHERE ' . $this->db->sql_build_array('SELECT', ['group_name' => 'AUDIO_FILES']);
		$result = $this->db->sql_query($sql);
		$audio_group_id = $this->db->sql_fetchfield('group_id');
		$this->db->sql_freeresult($result);

		if ($audio_group_id === false)
		{
			$sql = 'INSERT INTO ' . $this->table_prefix . 'extension_groups ' . $this->db->sql_build_array('INSERT', [
				'group_name'		=> 'AUDIO_FILES',
				'cat_id'			=> attachment_category::AUDIO,
				'allow_group'		=> 0,
				'upload_icon'		=> '',
				'max_filesize'		=> 0,
				'allowed_forums'	=> '',
			]);
			$this->db->sql_query($sql);
			$audio_group_id = $this->db->sql_nextid();
		}
		else
		{
			$sql = 'UPDATE ' . $this->table_prefix . 'extension_groups SET cat_id = ' . attachment_category::AUDIO . '
				WHERE ' . $this->db->sql_build_array('SELECT', ['group_id' => $audio_group_id]);
			$this->db->sql_query($sql);
		}

		$audio_extensions = ['mp3', 'wav', 'm4a', 'ogg', 'webm'];

		foreach ($audio_extensions as $audio_extension)
		{
			$sql = 'SELECT group_id
				FROM ' . $this->table_prefix . 'extensions
				WHERE ' . $this->db->sql_build_array('SELECT', ['extension' => $audio_extension]);
			$result = $this->db->sql_query($sql);
			$extension_group_id = $this->db->sql_fetchfield('group_id');
			$this->db->sql_freeresult($result);

			if ($extension_group_id === false)
			{
				$sql = 'INSERT INTO ' . $this->table_prefix . 'extensions ' . $this->db->sql_build_array('INSERT', [
					'group_id'	=> $audio_group_id,
					'extension'	=> $audio_extension,
				]);
				$this->db->sql_query($sql);
			}
			else if ($extension_group_id != $audio_group_id)
			{
				$sql = 'UPDATE ' . $this->table_prefix . "extensions SET group_id = $audio_group_id
					WHERE " . $this->db->sql_build_array('SELECT', ['extension' => $audio_extension]);
				$this->db->sql_query($sql);
			}
		}
	}
}
