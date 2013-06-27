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
	protected $forum_id;
	protected $parent_id;
	protected $left_id;
	protected $right_id;
	protected $forum_parents;
	protected $forum_name;
	protected $forum_desc;
	protected $forum_desc_bitfield;
	protected $forum_desc_options;
	protected $forum_desc_uid;
	protected $forum_link;
	protected $forum_password;
	protected $forum_style;
	protected $forum_image;
	protected $forum_rules;
	protected $forum_rules_link;
	protected $forum_rules_bitfield;
	protected $forum_rules_options;
	protected $forum_rules_uid;
	protected $forum_topics_per_page;
	protected $forum_type;
	protected $forum_status;
	protected $forum_posts;
	protected $forum_topics;
	protected $forum_topics_real;
	protected $forum_last_post_id;
	protected $forum_last_poster_id;
	protected $forum_last_post_subject;
	protected $forum_last_post_time;
	protected $forum_last_poster_name;
	protected $forum_last_poster_colour;
	protected $forum_flags;
	protected $forum_options;
	protected $display_subforum_list;
	protected $display_on_index;
	protected $enable_indexing;
	protected $enable_icons;
	protected $enable_prune;
	protected $prune_next;
	protected $prune_days;
	protected $prune_viewed;
	protected $prune_freq;
	protected $subforums = null;

}
