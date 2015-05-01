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

class post_text extends base
{
	/**
	* {@inheritdoc}
	*/
	public function get_max_id()
	{
		$sql = 'SELECT MAX(post_id) AS max_id FROM ' . POSTS_TABLE;
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
		$sql = 'SELECT post_id AS id, enable_bbcode, enable_smilies, enable_magic_url, post_text AS text, bbcode_uid
			FROM ' . POSTS_TABLE . '
			WHERE post_id BETWEEN ' . $min_id . ' AND ' . $max_id;
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
		$sql = 'UPDATE ' . POSTS_TABLE . "
			SET post_text = '" . $this->db->sql_escape($record['text']) . "'
			WHERE post_id = " . $record['id'];
		$this->db->sql_query($sql);
	}
}
