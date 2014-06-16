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
* Abstract class for topic based feeds
*/
abstract class topic_base extends \phpbb\feed\attachments_base
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
		$item_row['link'] = $this->helper->append_sid('viewtopic.' . $this->phpEx, 't=' . $row['topic_id'] . '&amp;p=' . $row['post_id'] . '#p' . $row['post_id']);

		if ($this->config['feed_item_statistics'])
		{
			$item_row['statistics'] = $this->user->lang['POSTED'] . ' ' . $this->user->lang['POST_BY_AUTHOR'] . ' ' . $this->user_viewprofile($row)
				. ' ' . $this->separator_stats . ' ' . $this->user->format_date($row[$this->get('published')])
				. ' ' . $this->separator_stats . ' ' . $this->user->lang['REPLIES'] . ' ' . ($this->content_visibility->get_count('topic_posts', $row, $row['forum_id']) - 1)
				. ' ' . $this->separator_stats . ' ' . $this->user->lang['VIEWS'] . ' ' . $row['topic_views'];

			if ($this->is_moderator_approve_forum($row['forum_id']))
			{
				if ((int) $row['topic_visibility'] === ITEM_DELETED)
				{
					$item_row['statistics'] .= ' ' . $this->separator_stats . ' ' . $this->user->lang['TOPIC_DELETED'];
				}
				else if ((int) $row['topic_visibility'] === ITEM_UNAPPROVED)
				{
					$item_row['statistics'] .= ' ' . $this->separator_stats . ' ' . $this->user->lang['TOPIC_UNAPPROVED'];
				}
				else if ($row['topic_posts_unapproved'])
				{
					$item_row['statistics'] .= ' ' . $this->separator_stats . ' ' . $this->user->lang['POSTS_UNAPPROVED'];
				}
			}
		}
	}
}
