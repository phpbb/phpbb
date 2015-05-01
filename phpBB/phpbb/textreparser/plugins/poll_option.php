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
		return array(
			'id'   => 'poll_option_id',
			'text' => 'poll_option_text',
		);
	}

	/**
	* {@inheritdoc}
	*/
	protected function get_records_query($min_id, $max_id)
	{
		$sql = 'SELECT o.poll_option_id AS id, o.poll_option_text AS text, p.bbcode_uid
			FROM ' . POLL_OPTIONS_TABLE . ' o, ' . TOPICS_TABLE . ' t, ' . POSTS_TABLE . ' p
			WHERE o.poll_option_id BETWEEN ' . $min_id . ' AND ' . $max_id .'
				AND t.topic_id = o.topic_id
				AND p.post_id = t.topic_first_post_id';

		return $sql;
	}

	/**
	* {@inheritdoc}
	*/
	public function get_table_name()
	{
		return POLL_OPTIONS_TABLE;
	}
}
