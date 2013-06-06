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
* Abstract class for topic based feeds
*
* @package phpBB3
*/
abstract class phpbb_feed_topic_base extends phpbb_feed_base
{
	var $num_items = 'feed_limit_topic';

	function set_keys()
	{
		$this->set('title',		'topic_title');
		$this->set('title2',	'forum_name');

		$this->set('author_id',	'topic_poster');
		$this->set('creator',	'topic_first_poster_name');
		$this->set('published',	'post_time');
		$this->set('updated',	'post_edit_time');
		$this->set('text',		'post_text');

		$this->set('bitfield',	'bbcode_bitfield');
		$this->set('bbcode_uid','bbcode_uid');

		$this->set('enable_bbcode',		'enable_bbcode');
		$this->set('enable_smilies',	'enable_smilies');
		$this->set('enable_magic_url',	'enable_magic_url');
	}

	function adjust_item(&$item_row, &$row)
	{
		global $phpEx, $config, $user;

		$item_row['link'] = feed_append_sid('/viewtopic.' . $phpEx, 't=' . $row['topic_id'] . '&amp;p=' . $row['post_id'] . '#p' . $row['post_id']);

		if ($config['feed_item_statistics'])
		{
			$item_row['statistics'] = $user->lang['POSTED'] . ' ' . $user->lang['POST_BY_AUTHOR'] . ' ' . $this->user_viewprofile($row)
				. ' ' . $this->separator_stats . ' ' . $user->format_date($row[$this->get('published')])
				. ' ' . $this->separator_stats . ' ' . $user->lang['REPLIES'] . ' ' . (($this->is_moderator_approve_forum($row['forum_id'])) ? $row['topic_replies_real'] : $row['topic_replies'])
				. ' ' . $this->separator_stats . ' ' . $user->lang['VIEWS'] . ' ' . $row['topic_views']
				. (($this->is_moderator_approve_forum($row['forum_id']) && ($row['topic_replies_real'] != $row['topic_replies'])) ? ' ' . $this->separator_stats . ' ' . $user->lang['POSTS_UNAPPROVED'] : '');
		}
	}
}
