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

class poll_option extends \phpbb\textreparser\row_based_plugin
{
	/**
	* {@inheritdoc}
	*/
	public function get_columns()
	{
		return [
			'id'   => 'topic_id',
			'text' => 'poll_option_text',
		];
	}

	/**
	* {@inheritdoc}
	*/
	protected function get_records_sql(array $config): string
	{
		$sql = 'SELECT o.topic_id, o.poll_option_id, o.poll_option_text AS text, p.enable_bbcode, p.enable_smilies, p.enable_magic_url, p.bbcode_uid
			FROM ' . POLL_OPTIONS_TABLE . ' o, ' . TOPICS_TABLE . ' t, ' . POSTS_TABLE . ' p
			WHERE t.topic_id = o.topic_id
				AND p.post_id = t.topic_first_post_id';

		$where = $this->get_where_clauses($config, 'o.topic_id', 'o.poll_option_text');
		if (!empty($where))
		{
			$sql .= "\nAND " . implode("\nAND ", $where);
		}

		return $sql;
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
