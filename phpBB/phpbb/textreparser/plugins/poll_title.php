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

class poll_title extends \phpbb\textreparser\row_based_plugin
{
	/**
	* {@inheritdoc}
	*/
	public function get_columns()
	{
		return array(
			'id'   => 'topic_id',
			'text' => 'poll_title',
		);
	}

	/**
	* {@inheritdoc}
	*/
	protected function get_records_by_range_query($min_id, $max_id)
	{
		$sql = 'SELECT t.topic_id AS id, t.poll_title AS text, p.enable_bbcode, p.enable_smilies, p.enable_magic_url, p.bbcode_uid
			FROM ' . TOPICS_TABLE . ' t, ' . POSTS_TABLE . ' p
			WHERE t.topic_id BETWEEN ' . $min_id . ' AND ' . $max_id .'
				AND t.poll_start > 0
				AND p.post_id = t.topic_first_post_id';

		return $sql;
	}
}
