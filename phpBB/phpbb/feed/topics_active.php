<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Active Topics feed
*
* This will give you the last {$this->num_items} topics
* with replies made withing the last {$this->sort_days} days
* including the last post.
*
* @package phpBB3
*/
class phpbb_feed_topics_active extends phpbb_feed_topic_base
{
	var $sort_days = 7;

	function set_keys()
	{
		parent::set_keys();

		$this->set('author_id',	'topic_last_poster_id');
		$this->set('creator',	'topic_last_poster_name');
	}

	function get_sql()
	{
		$forum_ids_read = $this->get_readable_forums();
		if (empty($forum_ids_read))
		{
			return false;
		}

		$in_fid_ary = array_intersect($forum_ids_read, $this->get_forum_ids());
		$in_fid_ary = array_diff($in_fid_ary, $this->get_passworded_forums());
		if (empty($in_fid_ary))
		{
			return false;
		}

		// Search for topics in last X days
		$last_post_time_sql = ($this->sort_days) ? ' AND topic_last_post_time > ' . (time() - ($this->sort_days * 24 * 3600)) : '';

		// We really have to get the post ids first!
		$sql = 'SELECT topic_last_post_id, topic_last_post_time
			FROM ' . TOPICS_TABLE . '
			WHERE ' . $this->db->sql_in_set('forum_id', $in_fid_ary) . '
				AND topic_moved_id = 0
				AND topic_visibility = ' . ITEM_APPROVED . '
				' . $last_post_time_sql . '
			ORDER BY topic_last_post_time DESC';
		$result = $this->db->sql_query_limit($sql, $this->num_items);

		$post_ids = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$post_ids[] = (int) $row['topic_last_post_id'];
		}
		$this->db->sql_freeresult($result);

		if (empty($post_ids))
		{
			return false;
		}

		$this->sql = array(
			'SELECT'	=> 'f.forum_id, f.forum_name,
							t.topic_id, t.topic_title, t.topic_posts_approved, t.topic_posts_unapproved, t.topic_posts_softdeleted, t.topic_views,
							t.topic_last_poster_id, t.topic_last_poster_name, t.topic_last_post_time,
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

	function get_forum_ids()
	{
		static $forum_ids;

		$cache_name	= 'feed_topic_active_forum_ids';

		if (!isset($forum_ids) && ($forum_ids = $this->cache->get('_' . $cache_name)) === false)
		{
			$sql = 'SELECT forum_id
				FROM ' . FORUMS_TABLE . '
				WHERE forum_type = ' . FORUM_POST . '
					AND ' . $this->db->sql_bit_and('forum_options', FORUM_OPTION_FEED_EXCLUDE, '= 0') . '
					AND ' . $this->db->sql_bit_and('forum_flags', log(FORUM_FLAG_ACTIVE_TOPICS, 2), '<> 0');
			$result = $this->db->sql_query($sql);

			$forum_ids = array();
			while ($forum_id = (int) $this->db->sql_fetchfield('forum_id'))
			{
				$forum_ids[$forum_id] = $forum_id;
			}
			$this->db->sql_freeresult($result);

			$this->cache->put('_' . $cache_name, $forum_ids, 180);
		}

		return $forum_ids;
	}

	function adjust_item(&$item_row, &$row)
	{
		parent::adjust_item($item_row, $row);

		$item_row['title'] = (isset($row['forum_name']) && $row['forum_name'] !== '') ? $row['forum_name'] . ' ' . $this->separator . ' ' . $item_row['title'] : $item_row['title'];
	}
}
