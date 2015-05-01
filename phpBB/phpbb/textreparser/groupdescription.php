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

namespace phpbb\textreparser;

class groupdescription extends base
{
	/**
	* {@inheritdoc}
	*/
	public function get_max_id()
	{
		$sql = 'SELECT MAX(group_id) AS max_id FROM ' . GROUPS_TABLE;
		$result = $this->db->sql_query($sql);
		$max_id = (int) $this->db->sql_fetchfield('max_id');
		$this->db->sql_freeresult($result);

		return $max_id;
	}

	/**
	* {@inheritdoc}
	*/
	protected function get_records($min_id, $max_id)
	{
		$sql = 'SELECT group_id AS id, group_desc AS text, group_desc_uid AS bbcode_uid
			FROM ' . GROUPS_TABLE . '
			WHERE group_id BETWEEN ' . $min_id . ' AND ' . $max_id;
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			// Those fields are not saved to the database, we need to guess their original value
			$row['enable_bbcode']    = !empty($row['bbcode_uid']);
			$row['enable_smilies']   = (strpos($row['text'], '<!-- s') !== false);
			$row['enable_magic_url'] = (strpos($row['text'], '<!-- m -->') !== false);
		}
		$records = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $records;
	}

	/**
	* {@inheritdoc}
	*/
	protected function save_record(array $record)
	{
		$sql = 'UPDATE ' . GROUPS_TABLE . "
			SET group_desc = '" . $this->db->sql_escape($record['text']) . "'
			WHERE group_id = " . $record['id'];
		$this->db->sql_query($sql);
	}
}
