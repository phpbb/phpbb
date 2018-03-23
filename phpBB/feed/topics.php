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

namespace phpbb\feed;

/**
 * New Topics feed
 *
 * This will give you the last {$this->num_items} created topics
 * including the first post.
 */
class topics extends topic_base
{
	/**
	 * {@inheritdoc}
	 */
	protected function get_sql()
	{
		$forum_ids_read = $this->get_readable_forums();
		if (empty($forum_ids_read))
		{
			return false;
		}

		$in_fid_ary = array_diff($forum_ids_read, $this->get_excluded_forums(), $this->get_passworded_forums());
		if (empty($in_fid_ary))
		{
			return false;
		}

		// We really have to get the post ids first!
		$sql = 'SELECT topic_first_post_id, topic_time
			FROM ' . TOPICS_TABLE . '
			WHERE  topic_moved_id = 0
				AND ' . $this->content_visibility->get_forums_visibility_sql('topic', $in_fid_ary) . '
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

		parent::fetch_attachments($post_ids);

		$this->sql = array(
			'SELECT'	=> 'f.forum_id, f.forum_name,
							t.topic_id, t.topic_title, t.topic_poster, t.topic_first_poster_name, t.topic_posts_approved, t.topic_posts_unapproved, t.topic_posts_softdeleted, t.topic_views, t.topic_time, t.topic_last_post_time,
							p.post_id, p.post_time, p.post_edit_time, p.post_text, p.bbcode_bitfield, p.bbcode_uid, p.enable_bbcode, p.enable_smilies, p.enable_magic_url, p.post_attachment, t.topic_visibility',
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
			'ORDER_BY'	=> 'p.post_time DESC, p.post_id DESC',
		);

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function adjust_item(&$item_row, &$row)
	{
		parent::adjust_item($item_row, $row);

		$item_row['title'] = (isset($row['forum_name']) && $row['forum_name'] !== '') ? $row['forum_name'] . ' ' . $this->separator . ' ' . $item_row['title'] : $item_row['title'];
	}
}
