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
* News feed
*
* This will give you {$this->num_items} first posts
* of all topics in the selected news forums.
*
* @package phpBB3
*/
class news extends \phpbb\feed\topic_base
{
	function get_news_forums()
	{
		static $forum_ids;

		// Matches acp/acp_board.php
		$cache_name	= 'feed_news_forum_ids';

		if (!isset($forum_ids) && ($forum_ids = $this->cache->get('_' . $cache_name)) === false)
		{
			$sql = 'SELECT forum_id
				FROM ' . FORUMS_TABLE . '
				WHERE ' . $this->db->sql_bit_and('forum_options', FORUM_OPTION_FEED_NEWS, '<> 0');
			$result = $this->db->sql_query($sql);

			$forum_ids = array();
			while ($forum_id = (int) $this->db->sql_fetchfield('forum_id'))
			{
				$forum_ids[$forum_id] = $forum_id;
			}
			$this->db->sql_freeresult($result);

			$this->cache->put('_' . $cache_name, $forum_ids);
		}

		return $forum_ids;
	}

	function get_sql()
	{
		// Determine forum ids
		$in_fid_ary = array_intersect($this->get_news_forums(), $this->get_readable_forums());
		if (empty($in_fid_ary))
		{
			return false;
		}

		$in_fid_ary = array_diff($in_fid_ary, $this->get_passworded_forums());
		if (empty($in_fid_ary))
		{
			return false;
		}

		// We really have to get the post ids first!
		$sql = 'SELECT topic_first_post_id, topic_time
			FROM ' . TOPICS_TABLE . '
			WHERE ' . $this->db->sql_in_set('forum_id', $in_fid_ary) . '
				AND topic_moved_id = 0
				AND topic_visibility = ' . ITEM_APPROVED . '
			ORDER BY topic_time DESC';
		$result = $this->db->sql_query_limit($sql, $this->num_items);

		$post_ids = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$post_ids[] = (int) $row['topic_first_post_id'];
		}
		$this->db->sql_freeresult($result);

		if (empty($post_ids))
		{
			return false;
		}

		$this->sql = array(
			'SELECT'	=> 'f.forum_id, f.forum_name,
							t.topic_id, t.topic_title, t.topic_poster, t.topic_first_poster_name, t.topic_posts_approved, t.topic_posts_unapproved, t.topic_posts_softdeleted, t.topic_views, t.topic_time, t.topic_last_post_time,
							p.post_id, p.post_time, p.post_edit_time, p.post_text, p.bbcode_bitfield, p.bbcode_uid, p.enable_bbcode, p.enable_smilies, p.enable_magic_url',
			'FROM'		=> array(
				TOPICS_TABLE	=> 't',
				POSTS_TABLE		=> 'p',
			),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(FORUMS_TABLE => 'f'),
					'ON'	=> 'p.forum_id = f.forum_id',
				),
			),
			'WHERE'		=> 'p.topic_id = t.topic_id
							AND ' . $this->db->sql_in_set('p.post_id', $post_ids),
			'ORDER_BY'	=> 'p.post_time DESC',
		);

		return true;
	}
}
