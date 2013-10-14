<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\feed;

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Board wide feed (aka overall feed)
*
* This will give you the newest {$this->num_items} posts
* from the whole board.
*
* @package phpBB3
*/
class overall extends \phpbb\feed\post_base
{
	function get_sql()
	{
		$forum_ids = array_diff($this->get_readable_forums(), $this->get_excluded_forums(), $this->get_passworded_forums());
		if (empty($forum_ids))
		{
			return false;
		}

		// Determine topics with recent activity
		$sql = 'SELECT topic_id, topic_last_post_time
			FROM ' . TOPICS_TABLE . '
			WHERE topic_moved_id = 0
				AND ' . $this->content_visibility->get_forums_visibility_sql('topic', $forum_ids) . '
			ORDER BY topic_last_post_time DESC';
		$result = $this->db->sql_query_limit($sql, $this->num_items);

		$topic_ids = array();
		$min_post_time = 0;
		while ($row = $this->db->sql_fetchrow())
		{
			$topic_ids[] = (int) $row['topic_id'];

			$min_post_time = (int) $row['topic_last_post_time'];
		}
		$this->db->sql_freeresult($result);

		if (empty($topic_ids))
		{
			return false;
		}

		// Get the actual data
		$this->sql = array(
			'SELECT'	=>	'f.forum_id, f.forum_name, ' .
							'p.post_id, p.topic_id, p.post_time, p.post_edit_time, p.post_visibility, p.post_subject, p.post_text, p.bbcode_bitfield, p.bbcode_uid, p.enable_bbcode, p.enable_smilies, p.enable_magic_url, ' .
							'u.username, u.user_id',
			'FROM'		=> array(
				USERS_TABLE		=> 'u',
				POSTS_TABLE		=> 'p',
			),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(FORUMS_TABLE	=> 'f'),
					'ON'	=> 'f.forum_id = p.forum_id',
				),
			),
			'WHERE'		=> $this->db->sql_in_set('p.topic_id', $topic_ids) . '
							AND ' . $this->content_visibility->get_forums_visibility_sql('post', $forum_ids, 'p.') . '
							AND p.post_time >= ' . $min_post_time . '
							AND u.user_id = p.poster_id',
			'ORDER_BY'	=> 'p.post_time DESC',
		);

		return true;
	}

	function adjust_item(&$item_row, &$row)
	{
		parent::adjust_item($item_row, $row);

		$item_row['title'] = (isset($row['forum_name']) && $row['forum_name'] !== '') ? $row['forum_name'] . ' ' . $this->separator . ' ' . $item_row['title'] : $item_row['title'];
	}
}
