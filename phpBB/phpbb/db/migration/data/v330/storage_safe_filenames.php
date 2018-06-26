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

class storage_safe_filenames extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v330\storage_track',
		);
	}

	public function update_schema()
	{
		return array(
			'add_columns'	=> array(
				$this->table_prefix . 'storage'			=> array(
					'safe_filename'		=> array('VCHAR', ''),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_columns'	=> array(
				$this->table_prefix . 'storage'			=> array(
					'safe_filename',
				),
			),
		);
	}

	public function update_data()
	{
		return [
			array('if', array(
				($this->config['storage\\attachment\\provider'] == \phpbb\storage\provider\local::class),
				array('config.add', array('storage\\attachment\\config\\safe_filename', '1')),
			)),
			array('if', array(
				($this->config['storage\\avatar\\provider'] == \phpbb\storage\provider\local::class),
				array('config.add', array('storage\\avatar\\config\\safe_filename', '0')),
			)),
			array('if', array(
				($this->config['storage\\backup\\provider'] == \phpbb\storage\provider\local::class),
				array('config.add', array('storage\\backup\\config\\safe_filename', '0')),
			)),
			['custom', [[$this, 'update_attachments']]],
			['custom', [[$this, 'fill_storage']]],
		];
	}

	protected function convert_physical_filename($filename)
	{
		if (!function_exists('unique_id'))
		{
			include_once($this->phpbb_root_path . 'includes/functions.' . $this->php_ext);
		}

		$parts = pathinfo($filename);

		return $parts['filename'] . '_' . md5(unique_id()) . '.' . $parts['extension'];
	}

	public function update_attachments()
	{
		$sql = 'SELECT attach_id, real_filename, physical_filename
			FROM ' . $this->table_prefix . 'attachments';

		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$new_physical_filename = $this->convert_physical_filename($row['real_filename']);

			$sql = 'UPDATE ' . $this->table_prefix . "attachments
				SET physical_filename = '" . $this->db->sql_escape($new_physical_filename) . "'
				WHERE attach_id = " . $row['attach_id'];
			$this->db->sql_query($sql);

			$sql = 'UPDATE ' . $this->table_prefix . "storage
				SET file_path = '" . $this->db->sql_escape($new_physical_filename) . "', safe_filename = '" . $this->db->sql_escape($row['physical_filename']) . "'
				WHERE file_path = '" . $this->db->sql_escape($row['physical_filename']) . "'
					AND storage = 'attachment'";
			$this->db->sql_query($sql);
		}
		$this->db->sql_freeresult($result);
	}

	public function fill_storage()
	{
		if (!function_exists('unique_id'))
		{
			include_once($this->phpbb_root_path . 'includes/functions.' . $this->php_ext);
		}

		$sql = 'SELECT file_id
			FROM ' . $this->table_prefix . "storage
			WHERE safe_filename = ''";

		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$sql = 'UPDATE ' . $this->table_prefix . "storage
				SET safe_filename = '" . $this->db->sql_escape(md5(unique_id())) . "'
				WHERE file_id = " . $row['file_id'];
			$this->db->sql_query($sql);
		}
		$this->db->sql_freeresult($result);
	}
}
