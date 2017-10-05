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
 * Abstract class for post based feeds
 */
abstract class post_base extends attachments_base
{
	protected $num_items = 'feed_limit_post';

	/**
	 * {@inheritdoc}
	 */
	public function set_keys()
	{
		$this->set('title',		'post_subject');
		$this->set('title2',	'topic_title');

		$this->set('author_id',	'user_id');
		$this->set('creator',	'username');
		$this->set('published',	'post_time');
		$this->set('updated',	'post_edit_time');
		$this->set('text',		'post_text');

		$this->set('bitfield',	'bbcode_bitfield');
		$this->set('bbcode_uid','bbcode_uid');

		$this->set('enable_bbcode',		'enable_bbcode');
		$this->set('enable_smilies',	'enable_smilies');
		$this->set('enable_magic_url',	'enable_magic_url');
	}

	/**
	 * {@inheritdoc}
	 */
	public function adjust_item(&$item_row, &$row)
	{
		$item_row['link'] = $this->helper->append_sid('viewtopic.' . $this->phpEx, "t={$row['topic_id']}&amp;p={$row['post_id']}#p{$row['post_id']}");

		if ($this->config['feed_item_statistics'])
		{
			$item_row['statistics'] = $this->user->lang['POSTED'] . ' ' . $this->user->lang['POST_BY_AUTHOR'] . ' ' . $this->user_viewprofile($row)
				. ' ' . $this->separator_stats . ' ' . $this->user->format_date($row[$this->get('published')])
				. (($this->is_moderator_approve_forum($row['forum_id']) && (int) $row['post_visibility'] === ITEM_UNAPPROVED) ? ' ' . $this->separator_stats . ' ' . $this->user->lang['POST_UNAPPROVED'] : '')
				. (($this->is_moderator_approve_forum($row['forum_id']) && (int) $row['post_visibility'] === ITEM_DELETED) ? ' ' . $this->separator_stats . ' ' . $this->user->lang['POST_DELETED'] : '');
		}
	}
}
