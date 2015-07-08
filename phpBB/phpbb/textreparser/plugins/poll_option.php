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

namespace phpbb\textreparser\plugins;

class poll_option extends \phpbb\textreparser\base
{
	/**
	* @var \phpbb\db\driver\driver_interface
	*/
	protected $db;

	/**
	* Constructor
	*
	* @param \phpbb\db\driver\driver_interface $db Database connection
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db)
	{
		$this->db = $db;
	}

	/**
	* {@inheritdoc}
	*/
	public function get_max_id()
	{
		$sql = 'SELECT MAX(topic_id) AS max_id FROM ' . POLL_OPTIONS_TABLE;
		$result = $this->db->sql_query($sql);
		$max_id = (int) $this->db->sql_fetchfield('max_id');
		$this->db->sql_freeresult($result);

		return $max_id;
	}

	/**
	* {@inheritdoc}
	*/
	protected function get_records_by_range($min_id, $max_id)
	{
		$sql = 'SELECT o.topic_id, o.poll_option_id, o.poll_option_text AS text, p.enable_bbcode, p.enable_smilies, p.enable_magic_url, p.bbcode_uid
			FROM ' . POLL_OPTIONS_TABLE . ' o, ' . TOPICS_TABLE . ' t, ' . POSTS_TABLE . ' p
			WHERE o.topic_id BETWEEN ' . $min_id . ' AND ' . $max_id .'
				AND t.topic_id = o.topic_id
				AND p.post_id = t.topic_first_post_id';
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
		$sql = 'UPDATE ' . POLL_OPTIONS_TABLE . "
			SET poll_option_text = '" . $this->db->sql_escape($record['text']) . "'
			WHERE topic_id = " . $record['topic_id'] . '
				AND poll_option_id = ' . $record['poll_option_id'];
		$this->db->sql_query($sql);
	}
}
