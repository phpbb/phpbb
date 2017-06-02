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

use phpbb\feed\exception\no_feed_exception;
use phpbb\feed\exception\no_topic_exception;
use phpbb\feed\exception\unauthorized_forum_exception;
use phpbb\feed\exception\unauthorized_topic_exception;

/**
 * Topic feed for a specific topic
 *
 * This will give you the last {$this->num_items} posts made within this topic.
 */
class topic extends post_base
{
	protected $topic_id		= 0;
	protected $forum_id		= 0;
	protected $topic_data	= array();

	/**
	 * Set the Topic ID
	 *
	 * @param int	$topic_id			Topic ID
	 * @return	\phpbb\feed\topic
	 */
	public function set_topic_id($topic_id)
	{
		$this->topic_id = (int) $topic_id;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function open()
	{
		$sql = 'SELECT f.forum_options, f.forum_password, t.topic_id, t.forum_id, t.topic_visibility, t.topic_title, t.topic_time, t.topic_views, t.topic_posts_approved, t.topic_type
			FROM ' . TOPICS_TABLE . ' t
			LEFT JOIN ' . FORUMS_TABLE . ' f
				ON (f.forum_id = t.forum_id)
			WHERE t.topic_id = ' . $this->topic_id;
		$result = $this->db->sql_query($sql);
		$this->topic_data = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (empty($this->topic_data))
		{
			throw new no_topic_exception($this->topic_id);
		}

		$this->forum_id = (int) $this->topic_data['forum_id'];

		// Make sure topic is either approved or user authed
		if ($this->topic_data['topic_visibility'] != ITEM_APPROVED && !$this->auth->acl_get('m_approve', $this->forum_id))
		{
			if ($this->user->data['user_id'] != ANONYMOUS)
			{
				send_status_line(403, 'Forbidden');
			}
			else
			{
				send_status_line(401, 'Unauthorized');
			}
			throw new unauthorized_topic_exception($this->topic_id);
		}

		// Make sure forum is not excluded from feed
		if (phpbb_optionget(FORUM_OPTION_FEED_EXCLUDE, $this->topic_data['forum_options']))
		{
			throw new no_feed_exception();
		}

		// Make sure we can read this forum
		if (!$this->auth->acl_get('f_read', $this->forum_id))
		{
			if ($this->user->data['user_id'] != ANONYMOUS)
			{
				send_status_line(403, 'Forbidden');
			}
			else
			{
				send_status_line(401, 'Unauthorized');
			}
			throw new unauthorized_forum_exception($this->forum_id);
		}

		// Make sure forum is not passworded or user is authed
		if ($this->topic_data['forum_password'])
		{
			$forum_ids_passworded = $this->get_passworded_forums();

			if (isset($forum_ids_passworded[$this->forum_id]))
			{
				if ($this->user->data['user_id'] != ANONYMOUS)
				{
					send_status_line(403, 'Forbidden');
				}
				else
				{
					send_status_line(401, 'Unauthorized');
				}
				throw new unauthorized_forum_exception($this->forum_id);
			}

			unset($forum_ids_passworded);
		}

		parent::open();
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_sql()
	{
		parent::fetch_attachments();

		$this->sql = array(
			'SELECT'	=>	'p.post_id, p.post_time, p.post_edit_time, p.post_visibility, p.post_subject, p.post_text, p.bbcode_bitfield, p.bbcode_uid, p.enable_bbcode, p.enable_smilies, p.enable_magic_url, p.post_attachment, ' .
				'u.username, u.user_id',
			'FROM'		=> array(
				POSTS_TABLE		=> 'p',
				USERS_TABLE		=> 'u',
			),
			'WHERE'		=> 'p.topic_id = ' . $this->topic_id . '
								AND ' . $this->content_visibility->get_visibility_sql('post', $this->forum_id, 'p.') . '
								AND p.poster_id = u.user_id',
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

		$item_row['forum_id'] = $this->forum_id;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_item()
	{
		return ($row = parent::get_item()) ? array_merge($this->topic_data, $row) : $row;
	}
}
