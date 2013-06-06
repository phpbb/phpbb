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
* 'All Forums' feed
*
* This will give you a list of all postable forums where feeds are enabled
* including forum description, topic stats and post stats
*
* @package phpBB3
*/
class phpbb_feed_forums extends phpbb_feed_base
{
	var $num_items	= 0;

	function set_keys()
	{
		$this->set('title',		'forum_name');
		$this->set('text',		'forum_desc');
		$this->set('bitfield',	'forum_desc_bitfield');
		$this->set('bbcode_uid','forum_desc_uid');
		$this->set('updated',	'forum_last_post_time');
		$this->set('options',	'forum_desc_options');
	}

	function get_sql()
	{
		$in_fid_ary = array_diff($this->get_readable_forums(), $this->get_excluded_forums());
		if (empty($in_fid_ary))
		{
			return false;
		}

		// Build SQL Query
		$this->sql = array(
			'SELECT'	=> 'f.forum_id, f.left_id, f.forum_name, f.forum_last_post_time,
							f.forum_desc, f.forum_desc_bitfield, f.forum_desc_uid, f.forum_desc_options,
							f.forum_topics, f.forum_posts',
			'FROM'		=> array(FORUMS_TABLE => 'f'),
			'WHERE'		=> 'f.forum_type = ' . FORUM_POST . '
							AND ' . $this->db->sql_in_set('f.forum_id', $in_fid_ary),
			'ORDER_BY'	=> 'f.left_id ASC',
		);

		return true;
	}

	function adjust_item(&$item_row, &$row)
	{
		$item_row['link'] = $this->helper->append_sid('/viewforum.' . $this->phpEx, 'f=' . $row['forum_id']);

		if ($this->config['feed_item_statistics'])
		{
			$item_row['statistics'] = $this->user->lang('TOTAL_TOPICS', (int) $row['forum_topics'])
				. ' ' . $this->separator_stats . ' ' . $this->user->lang('TOTAL_POSTS_COUNT', (int) $row['forum_posts']);
		}
	}
}
