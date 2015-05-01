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

class forum_description extends base
{
	/**
	* {@inheritdoc}
	*/
	public function get_max_id()
	{
		$sql = 'SELECT MAX(forum_id) AS max_id FROM ' . FORUMS_TABLE;
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
		$sql = 'SELECT forum_id AS id, forum_desc AS text, forum_desc_uid AS bbcode_uid
			FROM ' . FORUMS_TABLE . '
			WHERE forum_id BETWEEN ' . $min_id . ' AND ' . $max_id;
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
		$sql = 'UPDATE ' . FORUMS_TABLE . "
			SET forum_desc = '" . $this->db->sql_escape($record['text']) . "'
			WHERE forum_id = " . $record['id'];
		$this->db->sql_query($sql);
	}
}
