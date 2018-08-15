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

class storage_attachment_rename extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v330\storage_attachment',
		);
	}

	public function update_data()
	{
		return [
			['config.add', ['storage_salt', unique_id()]],
			['custom', [[$this, 'rename_attachments']]],
		];
	}

	protected function clean_filename($filename, $prefix = '')
	{
		// Replace any chars which may cause us problems with _
		$bad_chars = array("'", "\\", ' ', '/', ':', '*', '?', '"', '<', '>', '|');

		$filename = rawurlencode(str_replace($bad_chars, '_', $filename));
		$filename = preg_replace("/%(\w{2})/", '_', $filename);

		$filename = $prefix . pathinfo($filename, PATHINFO_FILENAME) . '_' . gen_rand_string() . '.' . pathinfo($filename, PATHINFO_EXTENSION);

		return $filename;
	}

	public function rename_attachments()
	{
		if (!function_exists('gen_rand_string'))
		{
			require($this->phpbb_root_path . 'includes/functions.' . $this->php_ext);
		}

		$sql = 'SELECT attach_id, real_filename, physical_filename, thumbnail
			FROM ' . ATTACHMENTS_TABLE;

		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$old_filename = $row['physical_filename'];
			$new_filename = $this->clean_filename($row['real_filename']);
			$new_filesystem_filename = $this->config['storage_salt'] . '_' . md5($new_filename);

			rename($this->phpbb_root_path . 'files/' . $old_filename, $this->phpbb_root_path . 'files/' . $new_filesystem_filename);

			$sql = 'UPDATE ' . ATTACHMENTS_TABLE . "
				SET physical_filename = '" . $this->db->sql_escape($new_filename) . "'
				WHERE attach_id = " . $row['attach_id'];
			$this->db->sql_query($sql);

			$sql = 'UPDATE ' . STORAGE_TABLE . "
				SET file_path = '" . $this->db->sql_escape($new_filename) . "'
				WHERE file_path = '" . $old_filename . "' AND storage = 'attachment'";
			$this->db->sql_query($sql);

			if ($row['thumbnail'] == 1)
			{
				$old_filename = 'thumb_' . $row['physical_filename'];
				$new_filename = 'thumb_' . $new_filename;
				$new_filesystem_filename = $this->config['storage_salt'] . '_' . md5($new_filename);

				rename($this->phpbb_root_path . 'files/' . $old_filename, $this->phpbb_root_path . 'files/' . $new_filesystem_filename);

				$sql = 'UPDATE ' . STORAGE_TABLE . "
					SET file_path = '" . $this->db->sql_escape($new_filename) . "'
					WHERE file_path = '" . $this->db->sql_escape($old_filename) . "' AND storage = 'attachment'";
				$this->db->sql_query($sql);
			}
		}

		$this->db->sql_freeresult($result);
	}

}
