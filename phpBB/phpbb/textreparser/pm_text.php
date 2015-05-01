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

class pm_text extends base
{
	/**
	* {@inheritdoc}
	*/
	public function get_max_id()
	{
		$sql = 'SELECT MAX(msg_id) AS max_id FROM ' . PRIVMSGS_TABLE;
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
		$sql = 'SELECT msg_id AS id, enable_bbcode, enable_smilies, enable_magic_url, message_text AS text, bbcode_uid
			FROM ' . PRIVMSGS_TABLE . '
			WHERE msg_id BETWEEN ' . $min_id . ' AND ' . $max_id;
		$result = $this->db->sql_query($sql);
		$records = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $records;
	}

	/**
	* {@inheritdoc}
	*/
	protected function save_record(array $record)
	{
		$sql = 'UPDATE ' . PRIVMSGS_TABLE . "
			SET message_text = '" . $this->db->sql_escape($record['text']) . "'
			WHERE msg_id = " . $record['id'];
		$this->db->sql_query($sql);
	}
}
