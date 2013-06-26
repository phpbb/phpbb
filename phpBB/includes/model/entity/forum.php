<?php
/**
 *
 * @package entity
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
 * Forum enitity
 * @package phpBB3
 */
class phpbb_model_entity_forum extends phpbb_model_entity
{
	public $forum_id;
	public $parent_id;
	public $left_id;
	public $right_id;
	public $forum_parents;
	public $forum_name;
	public $forum_desc;
	public $forum_desc_bitfield;
	public $forum_desc_options;
	public $forum_desc_uid;
	public $forum_link;
	public $forum_password;
	public $forum_style;
	public $forum_image;
	public $forum_rules;
	public $forum_rules_link;
	public $forum_rules_bitfield;
	public $forum_rules_options;
	public $forum_rules_uid;
	public $forum_topics_per_page;
	public $forum_type;
	public $forum_status;
	public $forum_posts;
	public $forum_topics;
	public $forum_topics_real;
	public $forum_last_post_id;
	public $forum_last_poster_id;
	public $forum_last_post_subject;
	public $forum_last_post_time;
	public $forum_last_poster_name;
	public $forum_last_poster_colour;
	public $forum_flags;
	public $forum_options;
	public $display_subforum_list;
	public $display_on_index;
	public $enable_indexing;
	public $enable_icons;
	public $enable_prune;
	public $prune_next;
	public $prune_days;
	public $prune_viewed;
	public $prune_freq;
	public $subforums = null;

}
