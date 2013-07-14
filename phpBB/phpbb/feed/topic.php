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
* Topic feed for a specific topic
*
* This will give you the last {$this->num_items} posts made within this topic.
*
* @package phpBB3
*/
class phpbb_feed_topic extends phpbb_feed_post_base
{
	var $topic_id		= 0;
	var $forum_id		= 0;
	var $topic_data		= array();

	/**
	* Set the Topic ID
	*
	* @param int	$topic_id			Topic ID
	* @return	phpbb_feed_topic
	*/
	public function set_topic_id($topic_id)
	{
		$this->topic_id = (int) $topic_id;

		return $this;
	}

	function open()
	{
		$sql = 'SELECT f.forum_options, f.forum_password, t.topic_id, t.forum_id, t.topic_visibility, t.topic_title, t.topic_time, t.topic_views, t.topic_replies, t.topic_type
			FROM ' . TOPICS_TABLE . ' t
			LEFT JOIN ' . FORUMS_TABLE . ' f
				ON (f.forum_id = t.forum_id)
			WHERE t.topic_id = ' . $this->topic_id;
		$result = $this->db->sql_query($sql);
		$this->topic_data = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (empty($this->topic_data))
		{
			trigger_error('NO_TOPIC');
		}

		$this->forum_id = (int) $this->topic_data['forum_id'];

		// Make sure topic is either approved or user authed
		if (!$this->topic_data['topic_approved'] && !$this->auth->acl_get('m_approve', $this->forum_id))
		{
			trigger_error('SORRY_AUTH_READ');
		}

		// Make sure forum is not excluded from feed
		if (phpbb_optionget(FORUM_OPTION_FEED_EXCLUDE, $this->topic_data['forum_options']))
		{
			trigger_error('NO_FEED');
		}

		// Make sure we can read this forum
		if (!$this->auth->acl_get('f_read', $this->forum_id))
		{
			trigger_error('SORRY_AUTH_READ');
		}

		// Make sure forum is not passworded or user is authed
		if ($this->topic_data['forum_password'])
		{
			$forum_ids_passworded = $this->get_passworded_forums();

			if (isset($forum_ids_passworded[$this->forum_id]))
			{
				trigger_error('SORRY_AUTH_READ');
			}

			unset($forum_ids_passworded);
		}
	}

	function get_sql()
	{
		$this->sql = array(
			'SELECT'	=>	'p.post_id, p.post_time, p.post_edit_time, p.post_visibility, p.post_subject, p.post_text, p.bbcode_bitfield, p.bbcode_uid, p.enable_bbcode, p.enable_smilies, p.enable_magic_url, ' .
							'u.username, u.user_id',
			'FROM'		=> array(
				POSTS_TABLE		=> 'p',
				USERS_TABLE		=> 'u',
			),
			'WHERE'		=> 'p.topic_id = ' . $this->topic_id . '
								AND ' . $this->content_visibility->get_visibility_sql('post', $this->forum_id, 'p.') . '
								AND p.poster_id = u.user_id',
			'ORDER_BY'	=> 'p.post_time DESC',
		);

		return true;
	}

	function get_item()
	{
		return ($row = parent::get_item()) ? array_merge($this->topic_data, $row) : $row;
	}
}
