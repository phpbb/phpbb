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
* Abstract class for post based feeds
*
* @package phpBB3
*/
abstract class post_base extends \phpbb\feed\base
{
	var $num_items = 'feed_limit_post';
	var $attachments = array();

	function set_keys()
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

	function adjust_item(&$item_row, &$row)
	{
		$item_row['link'] = $this->helper->append_sid('viewtopic.' . $this->phpEx, "t={$row['topic_id']}&amp;p={$row['post_id']}#p{$row['post_id']}");

		if ($this->config['feed_item_statistics'])
		{
			$item_row['statistics'] = $this->user->lang['POSTED'] . ' ' . $this->user->lang['POST_BY_AUTHOR'] . ' ' . $this->user_viewprofile($row)
				. ' ' . $this->separator_stats . ' ' . $this->user->format_date($row[$this->get('published')])
				. (($this->is_moderator_approve_forum($row['forum_id']) && $row['post_visibility'] !== ITEM_APPROVED) ? ' ' . $this->separator_stats . ' ' . $this->user->lang['POST_UNAPPROVED'] : '');
		}
	}

	function fetch_attachments()
	{
		global $db;

		$sql_array = array(
						'SELECT'	=> 'a.*',
						'FROM'		=> array(
							ATTACHMENTS_TABLE	=>	'a'
						),
						'WHERE'		=> 'a.in_message = 0 ',
						'ORDER_BY'	=> 'a.filetime DESC, a.post_msg_id ASC'
					);

		if (isset($this->topic_id))
		{
			$sql_array['WHERE'] .= 'AND a.topic_id = ' . (int) $this->topic_id;
		}
		else if (isset($this->forum_id))
		{
			$sql_array['LEFT_JOIN'] = array(
											array(
												'FROM'  => array(TOPICS_TABLE => 't'),
												'ON'    => 'a.topic_id = t.topic_id'
											)
										);
			$sql_array['WHERE'] .= 'AND t.forum_id = ' . (int) $this->forum_id;
		}

		$sql = $db->sql_build_query('SELECT', $sql_array);
		$result = $db->sql_query($sql);

		// Set attachments in feed items
		while ($row = $db->sql_fetchrow($result))
		{
			$this->attachments[$row['post_msg_id']][] = $row;
		}
		$db->sql_freeresult($result);
	}
}
