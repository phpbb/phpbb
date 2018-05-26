<?php
/***************************************************************************
 *                               viewforum.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id: viewforum.php,v 1.1 2010/10/10 15:01:18 orynider Exp $
 *
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

/**
* @ignore
*/
define("IN_VIEWFORUM", true);

define('IN_PHPBB', true);
$phpbb_root_path = './';
include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'common.'.$phpEx);

//
// Start initial var setup
//
$forum_id	= request_var('f', 0);
$mark_read	= request_var('mark', '');
$start		= request_var('start', 0);
$start = ($start < 0) ? 0 : $start;
//
// End initial var setup
//

/* @var $pagination */
$pagination = new cache();

$default_sort_days	= (!empty($user->data['user_topic_show_days'])) ? $user->data['user_topic_show_days'] : 0;
$default_sort_key	= (!empty($user->data['user_topic_sortby_type'])) ? $user->data['user_topic_sortby_type'] : 't';
$default_sort_dir	= (!empty($user->data['user_topic_sortby_dir'])) ? $user->data['user_topic_sortby_dir'] : 'd';

$sort_days	= request_var('st', $default_sort_days);
$sort_key	= request_var('sk', $default_sort_key);
$sort_dir	= request_var('sd', $default_sort_dir);

//
// Check if the user has actually sent a forum ID with his/her request
// If not give them a nice error page.
//
if ( !empty($forum_id) )
{
	$sql = "SELECT f.*, f.cat_id as forum_type
		FROM " . FORUMS_TABLE . " f
		WHERE f.forum_id = $forum_id";
	if ( !($result = $db->sql_query($sql)) )
	{
		message_die(GENERAL_ERROR, 'Could not obtain forums information', '', __LINE__, __FILE__, $sql);
	}
}
else
{
	message_die(GENERAL_MESSAGE, 'Forum_not_exist');
}

//
// If the query doesn't return any rows this isn't a valid forum. Inform
// the user.
//
if ( !($forum_row = $db->sql_fetchrow($result)) )
{
	message_die(GENERAL_MESSAGE, 'Forum_not_exist');
}

//
// Start session management
//
$userdata = session_pagestart($user_ip, $forum_id);
init_userprefs($userdata);
//
// End session management
//


//
// Start auth check
//
$is_auth = array();
$is_auth = auth(AUTH_ALL, $forum_id, $user->data, $forum_row);

if ( !$is_auth['auth_read'] || !$is_auth['auth_view'] )
{
	if ( !$user->data['session_logged_in'] )
	{
		$redirect = POST_FORUM_URL . "=$forum_id" . ( ( isset($start) ) ? "&start=$start" : '' );
		redirect(append_sid("login.$phpEx?redirect=viewforum.$phpEx&$redirect", true));
	}
	//
	// The user is not authed to read this forum ...
	//
	$message = ( !$is_auth['auth_view'] ) ? $lang['Forum_not_exist'] : sprintf($lang['Sorry_auth_read'], $is_auth['auth_read_type']);

	message_die(GENERAL_MESSAGE, $message);
}
//
// End of auth check
//

//
// Handle marking posts
//
if ( $mark_read == 'topics' )
{
	// Begin Simple Subforums MOD
	$mark_list = ( isset($_GET['mark_list']) ) ? explode(',', $_GET['mark_list']) : array($forum_id);
	$old_forum_id = $forum_id;
	// End Simple Subforums MOD
	if ( $user->data['session_logged_in'] )
	{
		$sql = "SELECT MAX(post_time) AS last_post 
			FROM " . POSTS_TABLE . " 
			WHERE forum_id = $forum_id";
		if ( !($result = $db->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, 'Could not obtain forums information', '', __LINE__, __FILE__, $sql);
		}

		if ( $row = $db->sql_fetchrow($result) )
		{
			$tracking_forums = ( isset($_COOKIE[$board_config['cookie_name'] . '_f']) ) ? unserialize($_COOKIE[$board_config['cookie_name'] . '_f']) : array();
			$tracking_topics = ( isset($_COOKIE[$board_config['cookie_name'] . '_t']) ) ? unserialize($_COOKIE[$board_config['cookie_name'] . '_t']) : array();

			if ( ( count($tracking_forums) + count($tracking_topics) ) >= 150 && empty($tracking_forums[$forum_id]) )
			{
				asort($tracking_forums);
				unset($tracking_forums[key($tracking_forums)]);
			}

			if ( $row['last_post'] > $user->data['user_lastvisit'] )
			{
				$tracking_forums[$forum_id] = time();

				// Begin Simple Subforums MOD
				$set_cookie = true;
				if( isset($_COOKIE[$board_config['cookie_name'] . '_f']) )
				{
					$_COOKIE[$board_config['cookie_name'] . '_f'] = serialize($tracking_forums);
				}
				// End Simple Subforums MOD
			}
		}
		// Begin Simple Subforums MOD
		if( $set_cookie )
		{
			setcookie($board_config['cookie_name'] . '_f', serialize($tracking_forums), 0, $board_config['cookie_path'], $board_config['cookie_domain'], $board_config['cookie_secure']);
		}
		$forum_id = $old_forum_id;
		// End Simple Subforums MOD
		$template->assign_vars(array(
			'META' => '<meta http-equiv="refresh" content="3;url=' . append_sid("viewforum.$phpEx?" . POST_FORUM_URL . "=$forum_id") . '">')
		);
	}

	$message = $lang['Topics_marked_read'] . '<br /><br />' . sprintf($lang['Click_return_forum'], '<a href="' . append_sid("viewforum.$phpEx?" . POST_FORUM_URL . "=$forum_id") . '">', '</a> ');
	message_die(GENERAL_MESSAGE, $message);
}
//
// End handle marking posts
//

$tracking_topics = ( isset($_COOKIE[$board_config['cookie_name'] . '_t']) ) ? unserialize($_COOKIE[$board_config['cookie_name'] . '_t']) : '';
$tracking_forums = ( isset($_COOKIE[$board_config['cookie_name'] . '_f']) ) ? unserialize($_COOKIE[$board_config['cookie_name'] . '_f']) : '';

//
// Do the forum Prune
//
if ( $is_auth['auth_mod'] && $board_config['prune_enable'] )
{
	if ( $forum_row['prune_next'] < time() && $forum_row['prune_enable'] )
	{
		include($phpbb_root_path . 'includes/prune.'.$phpEx);
		require($phpbb_root_path . 'includes/functions_admin.'.$phpEx);
		auto_prune($forum_id);
	}
}
//
// End of forum prune
//

//
// Obtain list of moderators of each forum
// First users, then groups ... broken into two queries
//
$sql = "SELECT u.user_id, u.username 
	FROM " . AUTH_ACCESS_TABLE . " aa, " . USER_GROUP_TABLE . " ug, " . GROUPS_TABLE . " g, " . USERS_TABLE . " u
	WHERE aa.forum_id = $forum_id 
		AND aa.auth_mod = " . TRUE . " 
		AND g.group_single_user = 1
		AND ug.group_id = aa.group_id 
		AND g.group_id = aa.group_id 
		AND u.user_id = ug.user_id 
	GROUP BY u.user_id, u.username  
	ORDER BY u.user_id";
if ( !($result = $db->sql_query($sql)) )
{
	message_die(GENERAL_ERROR, 'Could not query forum moderator information', '', __LINE__, __FILE__, $sql);
}

$moderators = array();
while( $row = $db->sql_fetchrow($result) )
{
	$moderators[] = '<a href="' . append_sid("profile.$phpEx?mode=viewprofile&amp;" . POST_USERS_URL . "=" . $row['user_id']) . '">' . $row['username'] . '</a>';
}

$sql = "SELECT g.group_id, g.group_name 
	FROM " . AUTH_ACCESS_TABLE . " aa, " . USER_GROUP_TABLE . " ug, " . GROUPS_TABLE . " g 
	WHERE aa.forum_id = $forum_id
		AND aa.auth_mod = " . TRUE . " 
		AND g.group_single_user = 0
		AND g.group_type <> ". GROUP_HIDDEN ."
		AND ug.group_id = aa.group_id 
		AND g.group_id = aa.group_id 
	GROUP BY g.group_id, g.group_name  
	ORDER BY g.group_id";
if ( !($result = $db->sql_query($sql)) )
{
	message_die(GENERAL_ERROR, 'Could not query forum moderator information', '', __LINE__, __FILE__, $sql);
}

while( $row = $db->sql_fetchrow($result) )
{
	$moderators[] = '<a href="' . append_sid("groupcp.$phpEx?" . POST_GROUPS_URL . "=" . $row['group_id']) . '">' . $row['group_name'] . '</a>';
}
	
$l_moderators = ( count($moderators) == 1 ) ? $lang['Moderator'] : $lang['Moderators'];
$forum_moderators = ( count($moderators) ) ? implode(', ', $moderators) : $lang['None'];
include_once($phpbb_root_path . 'includes/functions_post.'.$phpEx);
$s_forum_rules = '';
if (isset($forum_row['forum_rules']))
{
	$forum_row['forum_rules'] = generate_text_for_display($forum_row['forum_rules'], $user->data['user_sig_bbcode_uid'], $user->default_bitfield(), false);
}

if (!isset($forum_row['forum_rules']) && !isset($forum_row['forum_rules_link']))
{
	$forum_row['forum_rules'] = '';
	$forum_row['forum_rules_link'] = 'faq.'.$phpEx;
}

$template->assign_vars(array(
	'S_FORUM_RULES'	=> true,
	'FORUM_DESC'	=> generate_text_for_display($forum_row['forum_desc'], $user->data['user_sig_bbcode_uid'], $user->default_bitfield(), false),	
	'U_FORUM_RULES'	=> $forum_row['forum_rules_link'],
	'FORUM_RULES'	=> $forum_row['forum_rules'])
);

//
// Generate a 'Show topics in previous x days' select box. If the topicsdays var is sent
// then get it's value, find the number of topics with dates newer than it (to properly
// handle pagination) and alter the main query
//
$s_limit_days = $s_sort_key = $s_sort_dir = $u_sort_param = '';
$previous_days = array(0, 1, 7, 14, 30, 90, 180, 364);
// Topic ordering options
$limit_days = array(0 => $user->lang['All_Topics'], 1 => $user->lang['1_Day'], 7 => $user->lang['7_Days'], 14 => $user->lang['2_Weeks'], 30 => $user->lang['1_Month'], 90 => $user->lang['3_Months'], 180 => $user->lang['6_Months'], 365 => $user->lang['1_Year']);
$previous_days_text = array($lang['All_Topics'], $lang['1_Day'], $lang['7_Days'], $lang['2_Weeks'], $lang['1_Month'], $lang['3_Months'], $lang['6_Months'], $lang['1_Year']);
$sort_by_text = array('a' => $user->lang['Author'], 't' => $user->lang('POST_TIME'), 'r' => $user->lang['Replies'], 's' => $user->lang['Subject'], 'v' => $user->lang['Views']);
$sort_by_sql = array('a' => 't.topic_first_poster_name', 't' => array('t.topic_last_post_time', 't.topic_last_post_id'), 'r' => (($auth->acl_get('m_approve', $forum_id)) ? 't.topic_posts_approved + t.topic_posts_unapproved + t.topic_posts_softdeleted' : 't.topic_posts_approved'), 's' => 'LOWER(t.topic_title)', 'v' => 't.topic_views');
gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param, $default_sort_days, $default_sort_key, $default_sort_dir);

if ( !empty($_POST['topicdays']) || !empty($_GET['topicdays']) )
{
	$topic_days = ( !empty($_POST['topicdays']) ) ? intval($_POST['topicdays']) : intval($_GET['topicdays']);
	$min_topic_time = time() - ($topic_days * 86400);

	$sql = "SELECT COUNT(t.topic_id) AS forum_topics 
		FROM " . TOPICS_TABLE . " t, " . POSTS_TABLE . " p 
		WHERE t.forum_id = $forum_id 
			AND p.post_id = t.topic_last_post_id
			AND p.post_time >= $min_topic_time"; 

	if ( !($result = $db->sql_query($sql)) )
	{
		message_die(GENERAL_ERROR, 'Could not obtain limited topics count information', '', __LINE__, __FILE__, $sql);
	}
	$row = $db->sql_fetchrow($result);

	$topics_count = ( $row['forum_topics'] ) ? $row['forum_topics'] : 1;
	$limit_topics_time = "AND p.post_time >= $min_topic_time";

	if ( !empty($_POST['topicdays']) )
	{
		$start = 0;
	}
}
else
{
	$topics_count = ( $forum_row['forum_topics'] ) ? $forum_row['forum_topics'] : 1;

	$limit_topics_time = '';
	$topic_days = 0;
}	

$select_topic_days = '<select name="topicdays">';
for($i = 0; $i < count($previous_days); $i++)
{
	$selected = ($topic_days == $previous_days[$i]) ? ' selected="selected"' : '';
	$select_topic_days .= '<option value="' . $previous_days[$i] . '"' . $selected . '>' . $previous_days_text[$i] . '</option>';
}
$select_topic_days .= '</select>';

//
// Again this will be handled by the templating
// code at some point
//
$row_color = ( !($i % 2) ) ? $theme['td_color1'] : $theme['td_color2'];
$row_class = ( !($i % 2) ) ? $theme['td_class1'] : $theme['td_class2'];

//
// All announcement data, this keeps announcements
// on each viewforum page ...
//
$sql = "SELECT t.*, u.username, u.user_id, u2.username as user2, u2.user_id as id2, p.post_time, p.post_username
	FROM " . TOPICS_TABLE . " t, " . USERS_TABLE . " u, " . POSTS_TABLE . " p, " . USERS_TABLE . " u2
	WHERE t.forum_id = $forum_id 
		AND t.topic_poster = u.user_id
		AND p.post_id = t.topic_last_post_id
		AND p.poster_id = u2.user_id
		AND t.topic_type = " . POST_ANNOUNCE . " 
	ORDER BY t.topic_last_post_id DESC ";
if ( !($result = $db->sql_query($sql)) )
{
   message_die(GENERAL_ERROR, 'Could not obtain topic information', '', __LINE__, __FILE__, $sql);
}

$topic_rowset = array();
$total_announcements = 0;
while( $row = $db->sql_fetchrow($result) )
{
	$topic_rowset[] = $row;
	$total_announcements++;
}

$db->sql_freeresult($result);

//
// Grab all the basic data (all topics except announcements)
// for this forum
//
$sql = "SELECT t.*, u.username, u.user_id, u2.username as user2, u2.user_id as id2, p.post_username, p2.post_username AS post_username2, p2.post_time 
	FROM " . TOPICS_TABLE . " t, " . USERS_TABLE . " u, " . POSTS_TABLE . " p, " . POSTS_TABLE . " p2, " . USERS_TABLE . " u2
	WHERE t.forum_id = $forum_id
		AND t.topic_poster = u.user_id
		AND p.post_id = t.topic_first_post_id
		AND p2.post_id = t.topic_last_post_id
		AND u2.user_id = p2.poster_id 
		AND t.topic_type <> " . POST_ANNOUNCE . " 
		$limit_topics_time
	ORDER BY t.topic_type DESC, t.topic_last_post_id DESC 
	LIMIT $start, ".$board_config['topics_per_page'];
if ( !($result = $db->sql_query($sql)) )
{
   message_die(GENERAL_ERROR, 'Could not obtain topic information', '', __LINE__, __FILE__, $sql);
}

$total_topics = 0;
while( $row = $db->sql_fetchrow($result) )
{
	$topic_rowset[] = $row;
	$total_topics++;
}

$db->sql_freeresult($result);

//
// Total topics ...
//
$total_topics += $total_announcements;

// Get folder img, topic status/type related information
$folder_img = $folder_alt = $topic_type = '';

$last_post = $lang['No_Posts'];	
$last_post_sub = '<img src="' . $images['icon_minipost'] . '" border="0" alt="' . $lang['No_Posts'] . '" title="' . $lang['No_Posts'] . '" />';
$last_post_time = '';
$l_moderators = '&nbsp;';
$moderator_list = '';							

//
// Define censored word matches
//
$orig_word = array();
$replacement_word = array();
obtain_word_list($orig_word, $replacement_word);

if ( $forum_row['forum_status'] == FORUM_LOCKED )
{
	$folder_image = $images['forum_locked']; 
	$folder_alt = $lang['Forum_locked'];
	
	// Begin Simple Subforums MOD
	$unread_topics = false;
	$folder_images = array(
		'default'	=> $folder_image,
		'new'		=> $images['forum_locked'],
		'sub'		=> ( isset($images['forums_locked']) ) ? $images['forums_locked'] : $images['forum_locked'],
		'subnew'	=> ( isset($images['forums_locked']) ) ? $images['forums_locked'] : $images['forum_locked'],
		'subalt'	=> $lang['Forum_locked'],
		'subaltnew'	=> $lang['Forum_locked'],
	);
	// End Simple Subforums MOD								
}
else
{
	$unread_topics = false;
	if ( $user->data['session_logged_in'] )
	{
		if ( !empty($new_topic_data[$forum_id]) )
		{
			$forum_last_post_time = 0;
				
			while( list($check_topic_id, $check_post_time) = @each($new_topic_data[$forum_id]) )
			{
				if ( empty($tracking_topics[$check_topic_id]) )
				{
					$unread_topics = true;
					$forum_last_post_time = max($check_post_time, $forum_last_post_time);
				}
				else
				{
					if ( $tracking_topics[$check_topic_id] < $check_post_time )
					{
						$unread_topics = true;
						$forum_last_post_time = max($check_post_time, $forum_last_post_time);
					}
				}
			}
				
			if ( !empty($tracking_forums[$forum_id]) )
			{
				if ( $tracking_forums[$forum_id] > $forum_last_post_time )
				{
					$unread_topics = false;
				}
			}
				
			if ( isset($_COOKIE[$board_config['cookie_name'] . '_f_all']) )
			{
				if ( $_COOKIE[$board_config['cookie_name'] . '_f_all'] > $forum_last_post_time )
				{
					$unread_topics = false;
				}
			}
		}
	}
	
	$folder_image = isset($unread_topics) ? $images['forum_new'] : $images['forum']; 
	$folder_alt = isset($unread_topics) ? $lang['New_posts'] : $lang['No_new_posts'];
								
	// Begin Simple Subforums MOD
	$folder_images = array(
		'default'	=> $folder_image,
		'new'		=> $images['forum_new'],
		'sub'		=> ( isset($images['forums']) ) ? $images['forums'] : $images['forum'],
		'subnew'	=> ( isset($images['forums_new']) ) ? $images['forums_new'] : $images['forum_new'],
		'subalt'	=> $lang['No_new_posts'],
		'subaltnew'	=> $lang['New_posts'],
	);
	// End Simple Subforums MOD								
}

//
// Forum/Topic states
//
@define('FORUM_CAT', 0);
@define('FORUM_POST', 1);
@define('FORUM_LINK', 2);
@define('ITEM_UNLOCKED', 0);
@define('ITEM_LOCKED', 1);
@define('ITEM_MOVED', 2);
	
$posts = $forum_row['forum_posts'];
$topics = $forum_row['forum_topics'];

if ($row['topic_status'] == ITEM_MOVED)
{
	$topic_id = $row['topic_moved_id'];
	$unread_topic = false;
}
else
{
	$unread_topic = ($row['topic_last_post_time'] > $row['post_time']) ? true : false;
}
	
$row_color = ( !($i % 2) ) ? $theme['td_color1'] : $theme['td_color2'];
$row_class = ( !($i % 2) ) ? $theme['td_class1'] : $theme['td_class2'];
							
						
//Begin  assign template vars for simple phpBB3 templates 
// Empty category
if ($forum_row['forum_parent'] == $forum_row['forum_id'] && $forum_row['forum_type'] == FORUM_CAT)
{
	$template->assign_block_vars('forumrow', array(
		'S_IS_CAT'				=> true,
		'S_TOPIC_ICONS'			=> true,
		'FORUM_ID'				=> $forum_row['forum_id'],
		'FORUM_NAME'			=> $forum_row['forum_name'],
		'FORUM_DESC'			=> generate_text_for_display($forum_row['forum_desc'], $forum_row['forum_desc_uid'], $forum_row['forum_desc_bitfield'], $forum_row['forum_desc_options']),
									
		'FORUM_FOLDER_IMG' 			=> $folder_image,
		'FORUM_FOLDER_IMG_SRC' 		=> $user->img($folder_image, '', '27', '', 'src'),			
		'FORUM_FOLDER_IMG_FULL_TAG' => $user->img($folder_image, '', '27', '', 'full_tag'),	
		'FORUM_FOLDER_IMG_HTML' 	=> $user->img($folder_image, '', '27', '', 'html'),	

		'TOPIC_ICON_IMG'		=> $user->img($folder_image, '', '27', '', 'src'),
		'TOPIC_ICON_IMG_WIDTH'	=> $user->img($folder_image, '', '27', '', 'width'),
		'TOPIC_ICON_IMG_HEIGHT'	=>  $user->img($folder_image, '', '27', '', 'height'),			
		'ATTACH_ICON_IMG'		=> ($auth->acl_get('u_download') && $auth->acl_get('f_download', $row['forum_id']) && $row['topic_attachment']) ? $user->img('icon_topic_attach', $user->lang['TOTAL_ATTACHMENTS']) : '',							
		
		'FORUM_IMAGE'			=> ($forum_row['forum_image']) ? '<img src="' . $phpbb_root_path . $forum_row['forum_image'] . '" alt="' . $user->lang['FORUM_CAT'] . '" />' : '',
		'FORUM_IMAGE_SRC'		=> ($forum_row['forum_image']) ? $phpbb_root_path . $forum_row['forum_image'] : '',
		'U_VIEWFORUM'			=> append_sid(PHPBB_URL . "viewforum.$phpEx", 'f=' . $forum_row['forum_id']))
	);
	continue;
}
	
$template->assign_block_vars('forumrow',	array(
	'S_IS_CAT'	=> true,
	'S_TOPIC_ICONS'	=> true,
	
	'FORUM_ID'	=> $forum_row['forum_id'],							
								
	'ROW_COLOR' => '#' . $row_color,
	'ROW_CLASS' => $row_class,
								
	'FORUM_FOLDER_IMG' 			=> $folder_image,
	'FORUM_FOLDER_IMG_SRC' 		=> $user->img($folder_image, '', '27', '', 'src'),			
	'FORUM_FOLDER_IMG_FULL_TAG' => $user->img($folder_image, '', '27', '', 'full_tag'),	
	'FORUM_FOLDER_IMG_HTML' 	=> $user->img($folder_image, '', '27', '', 'html'),				
											
	'TOPIC_IMG_STYLE'		=> $folder_image,
	
	'TOPIC_FOLDER_IMG'		=> $user->img($folder_image, $folder_alt),
	'TOPIC_FOLDER_IMG_ALT'	=> $user->lang[$folder_alt],	
	
	'TOPIC_ICON_IMG'		=> $user->img($folder_image, '', '27', '', 'src'),
	'TOPIC_ICON_IMG_WIDTH'	=> $user->img($folder_image, '', '27', '', 'width'),
	'TOPIC_ICON_IMG_HEIGHT'	=>  $user->img($folder_image, '', '27', '', 'height'),		
	'ATTACH_ICON_IMG'		=> ($auth->acl_get('u_download') && $auth->acl_get('f_download', $row['forum_id']) && $row['topic_attachment']) ? $user->img('icon_topic_attach', $user->lang['TOTAL_ATTACHMENTS']) : '',	
	
	'S_UNREAD_TOPIC'		=> $unread_topics,

	
	'FORUM_NAME' => $forum_row['forum_name'],
	'FORUM_DESC' => $forum_row['forum_desc'],
	
	'POSTS' => $forum_row['forum_posts'],
	'TOPICS' => $forum_row['forum_topics'],

	'L_MODERATOR' => $l_moderators, 
	'L_FORUM_FOLDER_ALT' => $folder_alt, 
	// Begin Simple Subforums MOD
	'FORUM_FOLDERS' => serialize($folder_images),
	'S_HAS_SUBFORUM' => ( (intval($forum_row['forum_parent'])) ? true : false ),
	'HAS_SUB' => ( (intval($forum_row['forum_parent'])) ? true : false ),	
	'PARENT' => $forum_row['forum_parent'],
	'ID' => $forum_row['forum_id'],
	'UNREAD' => intval($unread_topics),
	'TOTAL_UNREAD' => intval($unread_topics),
	'TOTAL_POSTS' => $forum_row['forum_posts'],
	'TOTAL_TOPICS' => $forum_row['forum_topics'],
	// End Simple Subforums MOD								

	'U_VIEWFORUM' => append_sid("viewforum.$phpEx?" . POST_FORUM_URL . "=$forum_id"))		
);							
							
//End  assign template vars for simple phpBB3 templates
							
// Begin Simple Subforums MOD
if( $forum_row['forum_parent'] )
{
	$subforums_list[] = array(
		'forum_data'	=> $forum_row,
		'folder_image'	=> $folder_image,
		'last_post'		=> $last_post,
		'last_post_sub'	=> $last_post_sub,
		'moderator_list'	=> $moderator_list,
		'unread_topics'	=> $unread_topics,
		'l_moderators'	=> $l_moderators,
		'folder_alt'	=> $folder_alt,
		'last_post_time'	=> $last_post_time,
		'desc'			=> $forum_row['forum_desc'],
	);
}
// End Simple Subforums MOD							

$folder_img = isset($folder_image) ? $folder_image : $images['forum']; 


// Display active topics?
$s_display_active = ($forum_row['forum_id']) ? true : false; //FORUM_CAT

//
// User authorisation levels output
//
$s_auth_can = ( ( $is_auth['auth_post'] ) ? $lang['Rules_post_can'] : $lang['Rules_post_cannot'] ) . '<br />';
$s_auth_can .= ( ( $is_auth['auth_reply'] ) ? $lang['Rules_reply_can'] : $lang['Rules_reply_cannot'] ) . '<br />';
$s_auth_can .= ( ( $is_auth['auth_edit'] ) ? $lang['Rules_edit_can'] : $lang['Rules_edit_cannot'] ) . '<br />';
$s_auth_can .= ( ( $is_auth['auth_delete'] ) ? $lang['Rules_delete_can'] : $lang['Rules_delete_cannot'] ) . '<br />';
$s_auth_can .= ( ( $is_auth['auth_vote'] ) ? $lang['Rules_vote_can'] : $lang['Rules_vote_cannot'] ) . '<br />';

if ( $is_auth['auth_mod'] )
{
	$s_auth_can2 = sprintf($lang['Rules_moderate'], "<p>[&nbsp;<a href=\"modcp.$phpEx?" . POST_FORUM_URL . "=$forum_id&amp;start=" . $start . "&amp;sid=" . $user->data['session_id'] . '">', '</a>&nbsp;]</p>');
}

//
// Mozilla navigation bar
//
$nav_links['up'] = array(
	'url' => append_sid('index.'.$phpEx),
	'title' => sprintf($lang['Forum_Index'], $board_config['sitename'])
);

//
// Dump out the page header and load viewforum template
//
@define('SHOW_ONLINE', true);

$page_title = $lang['View_forum'] . ' - ' . $forum_row['forum_name'];
include($phpbb_root_path . 'includes/page_header.'.$phpEx);

$template->set_filenames(array(
	'body' => 'viewforum_body.tpl')
);

// Begin Simple Subforums MOD
$all_forums = array();
make_jumpbox_ref('viewforum.'.$phpEx, $forum_id, $all_forums);
// End Simple Subforums MOD;

//
// Post URL generation for templating vars
//
$template->assign_vars(array(
	'ROW_COLOR' 				=> '#' . $row_color,
	'ROW_CLASS' 				=> $row_class,
	'S_HAS_SUBFORUM'			=> ($forum_row['forum_parent']) ? true : false,	
	'S_HAS_SUBFORUMPAGINATION'	=> ($forum_row['forum_parent']) ? true : false,
	'S_TOPIC_ICONS'				=> true,	
	
	'L_DISPLAY_TOPICS' 			=> $lang['Display_topics'],
/** ** /
	'FOLDER_IMG'					=> $user->img('topic_read', 'NO_UNREAD_POSTS'),
	'FOLDER_UNREAD_IMG'			=> $user->img('topic_unread', 'UNREAD_POSTS'),
	'FOLDER_HOT_IMG'				=> $user->img('topic_read_hot', 'NO_UNREAD_POSTS_HOT'),
	'FOLDER_HOT_UNREAD_IMG'		=> $user->img('topic_unread_hot', 'UNREAD_POSTS_HOT'),
	'FOLDER_LOCKED_IMG'			=> $user->img('topic_read_locked', 'NO_UNREAD_POSTS_LOCKED'),
	'FOLDER_LOCKED_UNREAD_IMG'		=> $user->img('topic_unread_locked', 'UNREAD_POSTS_LOCKED'),
	'FOLDER_STICKY_IMG'			=> $user->img('sticky_read', 'POST_STICKY'),
	'FOLDER_STICKY_UNREAD_IMG'		=> $user->img('sticky_unread', 'POST_STICKY'),
	'FOLDER_ANNOUNCE_IMG'			=> $user->img('announce_read', 'POST_ANNOUNCEMENT'),
	'FOLDER_ANNOUNCE_UNREAD_IMG'	=> $user->img('announce_unread', 'POST_ANNOUNCEMENT'),
	'FOLDER_MOVED_IMG'			=> $user->img('topic_moved', 'TOPIC_MOVED'),	
/** **/	
	'U_POST_NEW_TOPIC' => append_sid("posting.$phpEx?mode=newtopic&amp;" . POST_FORUM_URL . "=$forum_id"),

	'S_SELECT_TOPIC_DAYS' => $select_topic_days,
	'S_POST_DAYS_ACTION' => append_sid("viewforum.$phpEx?" . POST_FORUM_URL . "=" . $forum_id . "&amp;start=$start"),

	'FORUM_ID' => $forum_id,
	'FORUM_NAME' => $forum_row['forum_name'],
	'FORUM_DESC' => generate_text_for_display($forum_row['forum_desc'], $user->data['user_sig_bbcode_uid'], $user->default_bitfield(), false),
 	'PARENT_FORUM'	=> !isset($forum_row['forum_parent']) ? $forum_row['forum_parent'] : '',	
	'MODERATORS' => $forum_moderators,
	'POST_IMG' => ($forum_row['forum_status'] == FORUM_LOCKED) ? $images['post_locked'] : $images['post_new'],
	'TOTAL_TOPICS' => $forum_row['forum_topics'],
	'HAS_SUBFORUMS'		=> ($forum_row['forum_parent']) ? true : false,
	'FOLDER_IMG' => $images['folder'],
	'FOLDER_NEW_IMG' => $images['folder_new'],
	'FOLDER_HOT_IMG' => $images['folder_hot'],
	'FOLDER_HOT_NEW_IMG' => $images['folder_hot_new'],
	'FOLDER_LOCKED_IMG' => $images['folder_locked'],
	'FOLDER_LOCKED_NEW_IMG' => $images['folder_locked_new'],
	'FOLDER_STICKY_IMG' => $images['folder_sticky'],
	'FOLDER_STICKY_NEW_IMG' => $images['folder_sticky_new'],
	'FOLDER_ANNOUNCE_IMG' => $images['folder_announce'],
	'FOLDER_ANNOUNCE_NEW_IMG' => $images['folder_announce_new'],
	
	'ATTACH_ICON_IMG'	=> ($auth->acl_get('u_download') && $auth->acl_get('f_download', $row['forum_id']) && $row['topic_attachment']) ? $user->img('icon_topic_attach', $user->lang['TOTAL_ATTACHMENTS']) : '',
	
	'L_TOPICS' => $lang['Topics'],
	'L_REPLIES' => $lang['Replies'],
	'L_VIEWS' => $lang['Views'],
	'L_POSTS' => $lang['Posts'],
	'L_LASTPOST' => $lang['Last_Post'], 
	'L_MODERATOR' => $l_moderators, 
	'L_MARK_TOPICS_READ' => $lang['Mark_all_topics'], 
	'L_POST_NEW_TOPIC' => ( $forum_row['forum_status'] == FORUM_LOCKED ) ? $lang['Forum_locked'] : $lang['Post_new_topic'], 
	'L_NO_NEW_POSTS' => $lang['No_new_posts'],
	'L_NEW_POSTS' => $lang['New_posts'],
	'L_NO_NEW_POSTS_LOCKED' => $lang['No_new_posts_locked'], 
	'L_NEW_POSTS_LOCKED' => $lang['New_posts_locked'], 
	'L_NO_NEW_POSTS_HOT' => $lang['No_new_posts_hot'],
	'L_NEW_POSTS_HOT' => $lang['New_posts_hot'],
	'L_ANNOUNCEMENT' => $lang['Post_Announcement'], 
	'L_STICKY' => $lang['Post_Sticky'], 
	'L_POSTED' => $lang['Posted'],
	'L_JOINED' => $lang['Joined'],
	'L_AUTHOR' => $lang['Author'],

	'S_DISPLAY_ACTIVE'		=> $s_display_active,
	'S_SELECT_SORT_DIR'		=> $s_sort_dir,
	'S_SELECT_SORT_KEY'		=> $s_sort_key,
	'S_SELECT_SORT_DAYS'	=> $s_limit_days,	
	'S_TOPIC_ICONS'			=> true,
	'S_AUTH_LIST2' 			=> !empty($s_auth_can2) ? $s_auth_can2 : '', 
	'S_IS_POSTABLE'			=> true,
	'S_SINGLE_MODERATOR'	=> (!empty($moderators[$forum_id]) && count($moderators[$forum_id]) > 1) ? false : true,
	'S_NO_READ_ACCESS'		=> false,	
	'S_DISPLAY_POST_INFO'	=> ($is_auth['auth_post'] || $user->data['active']) ? true : false,
	'S_DISPLAY_SEARCHBOX'	=> ($auth->acl_get('u_search') && $auth->acl_get('f_search', $forum_id)) ? true : false,
	'S_SEARCHBOX_ACTION'	=> append_sid("{$phpbb_root_path}search.$phpEx"),
	
	'U_VIEW_FORUM' => append_sid("viewforum.$phpEx?" . POST_FORUM_URL ."=$forum_id"),
	'U_CANONICAL' => generate_board_url() . '/' . append_sid("viewforum.$phpEx?f=$forum_id" . (($start) ? "&amp;start=$start" : ''), true, ''),	
	'U_MARK_TOPICS'	=> ($user->data['user_active']) ? append_sid("{$phpbb_root_path}viewforum.$phpEx?hash=" . generate_link_hash('global') . "&amp;f=$forum_id&amp;mark=topics&amp;mark_time=" . time()) : '',	
	'U_MARK_FORUMS'	=> ($user->data['user_id'] != ANONYMOUS) ? append_sid("{$phpbb_root_path}index.$phpEx", 'hash=' . generate_link_hash('global') . '&amp;mark=forums&amp;mark_time=' . time()) : '',	
	'U_MARK_READ' => append_sid("viewforum.$phpEx?" . POST_FORUM_URL . "=$forum_id&amp;mark=topics"))
);

unset($moderators);

// Begin Simple Subforums MOD
if( $forum_row['forum_parent'] )
{
	$parent_id = $forum_row['forum_parent'];
	for( $i = 0; $i < count($all_forums); $i++ )
	{
		if( $all_forums[$i]['forum_id'] == $parent_id )
		{
			$template->assign_vars(array(
				'PARENT_FORUM'			=> $forum_row['forum_parent'],
				'U_VIEW_PARENT_FORUM'	=> append_sid("viewforum.$phpEx?" . POST_FORUM_URL .'=' . $all_forums[$i]['forum_id']),
				'PARENT_FORUM_NAME'		=> $all_forums[$i]['forum_name'],
			));
		}
	}
}
else
{
	$sub_list = array();
	for( $i = 0; $i < count($all_forums); $i++ )
	{
		if( $all_forums[$i]['forum_parent'] == $forum_id )
		{
			$sub_list[] = $all_forums[$i]['forum_id'];
		}
	}
	if( count($sub_list) )
	{
		$sub_list[] = $forum_id;
		$template->vars['U_MARK_READ'] .= '&amp;mark_list=' . implode(',', $sub_list);
	}
}
// assign additional variables for subforums mod
$template->assign_vars(array(
	'NUM_TOPICS' => $forum_row['forum_topics'],
	'CAN_POST' => $is_auth['auth_post'] ? 1 : 0,
	'L_FORUM' => $lang['Forum'],
	));

// End Simple Subforums MOD
//
// End header
//

// Topic types
//@define('POST_NORMAL', 0);
//@define('POST_STICKY', 1);
//@define('POST_ANNOUNCE', 2);
@define('POST_GLOBAL', 3);

//
// Okay, lets dump out the page ...
//
$s_type_switch = 0;
if ($total_topics)
{
	for($i = 0; $i < $total_topics; $i++)
	{
		$row = &$topic_rowset[$i];
		
		$topicrow = &$i;
		
		$topic_forum_id = ($row['forum_id']) ? (int) $row['forum_id'] : $forum_id;
		
		// This will allow the style designer to output a different header
		// or even separate the list of announcements from sticky and normal topics
		$s_type_switch_test = ($row['topic_type'] == POST_ANNOUNCE || $row['topic_type'] == POST_GLOBAL) ? 1 : 0;
		
		$topic_id = $topic_rowset[$i]['topic_id'];
		
		$topic_title = ( count($orig_word) ) ? preg_replace($orig_word, $replacement_word, $topic_rowset[$i]['topic_title']) : $topic_rowset[$i]['topic_title'];
		
		// Replies		
		$replies = $topic_rowset[$i]['topic_replies'];
		
		if ($row['topic_status'] == ITEM_MOVED)
		{
			$topic_id = $row['topic_moved_id'];
			$unread_topic = false;
		}
		else
		{
			$unread_topic = (isset($topic_tracking_info[$topic_id]) && $row['topic_last_post_time'] > $topic_tracking_info[$topic_id]) ? true : false;
		}			
		
		$total_replies = intval($topic_rowset[$i]['topic_replies']) + 1;
		
		$topic_type = $topic_rowset[$i]['topic_type'];		
		
		if( $topic_type == POST_ANNOUNCE )
		{
			$topic_type = $lang['Topic_Announcement'] . ' ';
		}
		else if( $topic_type == POST_STICKY )
		{
			$topic_type = $lang['Topic_Sticky'] . ' ';
		}
		else
		{
			$topic_type = '';		
		}

		if( $topic_rowset[$i]['topic_vote'] )
		{
			$topic_type .= $lang['Topic_Poll'] . ' ';
		}
		
		if( $topic_rowset[$i]['topic_status'] == TOPIC_MOVED )
		{
			$topic_type = $lang['Topic_Moved'] . ' ';
			$topic_id = $topic_rowset[$i]['topic_moved_id'];

			$folder_image =  $images['folder'];
			$folder_alt = $lang['Topics_Moved'];
			$newest_post_img = '';
		}
		else
		{
			if( $topic_rowset[$i]['topic_type'] == POST_ANNOUNCE )
			{
				$folder = $images['folder_announce'];
				$folder_new = $images['folder_announce_new'];
			}
			else if( $topic_rowset[$i]['topic_type'] == POST_STICKY )
			{
				$folder = $images['folder_sticky'];
				$folder_new = $images['folder_sticky_new'];
			}
			else if( $topic_rowset[$i]['topic_status'] == TOPIC_LOCKED )
			{
				$folder = $images['folder_locked'];
				$folder_new = $images['folder_locked_new'];
			}
			else
			{
				if($replies >= $board_config['hot_threshold'])
				{
					$folder = $images['folder_hot'];
					$folder_new = $images['folder_hot_new'];
				}
				else
				{
					$folder = $images['folder'];
					$folder_new = $images['folder_new'];
				}
			}
			
			// This will allow the style designer to output a different header
			// or even separate the list of announcements from sticky and normal topics
			$s_type_switch_test = ($row['topic_type'] == POST_ANNOUNCE || $row['topic_type'] == POST_GLOBAL) ? 1 : 0;
			$newest_post_img = '';
			if ($user->data['session_logged_in'])
			{
				if( $topic_rowset[$i]['post_time'] > $user->data['user_lastvisit'] ) 
				{
					if( !empty($tracking_topics) || !empty($tracking_forums) || isset($_COOKIE[$board_config['cookie_name'] . '_f_all']) )
					{
						$unread_topics = true;

						if( !empty($tracking_topics[$topic_id]) )
						{
							if( $tracking_topics[$topic_id] >= $topic_rowset[$i]['post_time'] )
							{
								$unread_topics = false;
							}
						}

						if( !empty($tracking_forums[$forum_id]) )
						{
							if( $tracking_forums[$forum_id] >= $topic_rowset[$i]['post_time'] )
							{
								$unread_topics = false;
							}
						}

						if( isset($_COOKIE[$board_config['cookie_name'] . '_f_all']) )
						{
							if( $_COOKIE[$board_config['cookie_name'] . '_f_all'] >= $topic_rowset[$i]['post_time'] )
							{
								$unread_topics = false;
							}
						}

						if( $unread_topics )
						{
							$folder_image = $folder_new;
							$folder_alt = $lang['New_posts'];

							$newest_post_img = '<a href="' . append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id&amp;view=newest") . '"><img src="' . $images['icon_newest_reply'] . '" alt="' . $lang['View_newest_post'] . '" title="' . $lang['View_newest_post'] . '" border="0" /></a> ';
						}
						else
						{
							$folder_image = $folder;
							$folder_alt = ( $topic_rowset[$i]['topic_status'] == TOPIC_LOCKED ) ? $lang['Topic_locked'] : $lang['No_new_posts'];

							$newest_post_img = '';
						}
					}
					else
					{
						$folder_image = $folder_new;
						$folder_alt = ( $topic_rowset[$i]['topic_status'] == TOPIC_LOCKED ) ? $lang['Topic_locked'] : $lang['New_posts'];

						$newest_post_img = '<a href="' . append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id&amp;view=newest") . '"><img src="' . $images['icon_newest_reply'] . '" alt="' . $lang['View_newest_post'] . '" title="' . $lang['View_newest_post'] . '" border="0" /></a> ';
					}
				}
				else 
				{
					$folder_image = $folder;
					$folder_alt = ( $topic_rowset[$i]['topic_status'] == TOPIC_LOCKED ) ? $lang['Topic_locked'] : $lang['No_new_posts'];

					$newest_post_img = '';
				}
			}
			else
			{
				$folder_image = $folder;
				$folder_alt = ( $topic_rowset[$i]['topic_status'] == TOPIC_LOCKED ) ? $lang['Topic_locked'] : $lang['No_new_posts'];

				$newest_post_img = '';
			}
		}

		if( ( $replies + 1 ) > $board_config['posts_per_page'] )
		{
			$total_pages = ceil( ( $replies + 1 ) / $board_config['posts_per_page'] );
			$goto_page = '<strong class="' . 'pagination"' . '><span>';

			$times = 1;
			for($j = 0; $j < $replies + 1; $j += $board_config['posts_per_page'])
			{
				$goto_page .= '<a href="' . append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=" . $topic_id . "&amp;start=$j") . '">' . $times . '</a>';
				if( $times == 1 && $total_pages > 4 )
				{
					$goto_page .= ' ... ';
					$times = $total_pages - 3;
					$j += ( $total_pages - 4 ) * $board_config['posts_per_page'];
				}
				else if ( $times < $total_pages )
				{
					$goto_page .= '';
				}
				$times++;
			}
			$goto_page .= '</span></strong>';
		}
		else
		{
			$goto_page = '';
		}
		
		// Get folder img, topic status/type related information
		$folder_img = $folder_image;
		
		// Generate all the URIs ...
		$view_topic_url_params = 'f=' . $forum_id . '&amp;t=' . $topic_id;
		$view_topic_url1 = ($auth->acl_get('f_read', $forum_id) || $is_auth['auth_view']) ? append_sid("{$phpbb_root_path}viewtopic.$phpEx", $view_topic_url_params) : false;

		$topic_unapproved = $is_auth['auth_mod'];
		$posts_unapproved = $is_auth['auth_mod'];
		$topic_deleted = ($topic_rowset[$i]['topic_status'] == TOPIC_LOCKED);

		$u_mcp_queue = ($topic_unapproved || $posts_unapproved) ? append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=queue&amp;mode=' . (($topic_unapproved) ? 'approve_details' : 'unapproved_posts') . "&amp;t=$topic_id", true, $user->session_id) : '';
		$u_mcp_queue = (!$u_mcp_queue && $topic_deleted) ? append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=queue&amp;mode=deleted_topics&amp;t=' . $topic_id, true, $user->session_id) : $u_mcp_queue;
			
		$view_topic_url = ($auth->acl_get('f_read', $forum_id) || $is_auth['auth_view']) ? append_sid("viewtopic.$phpEx?" . $view_topic_url_params) : false;

		$topic_author = ( $topic_rowset[$i]['user_id'] != ANONYMOUS ) ? '<a href="' . append_sid("profile.$phpEx?mode=viewprofile&amp;" . POST_USERS_URL . '=' . $topic_rowset[$i]['user_id']) . '">' : '';
		$topic_author .= ( $topic_rowset[$i]['user_id'] != ANONYMOUS ) ? $topic_rowset[$i]['username'] : ( (!empty($topic_rowset[$i]['post_username'])) ? $topic_rowset[$i]['post_username'] : $lang['Guest'] );

		$topic_author .= ( $topic_rowset[$i]['user_id'] != ANONYMOUS ) ? '</a>' : '';

		$first_post_time = create_date($board_config['default_dateformat'], $topic_rowset[$i]['topic_time'], $board_config['board_timezone']);

		$last_post_time = $lang['Posted_on_date'] . '&nbsp;' . create_date($board_config['default_dateformat'], $topic_rowset[$i]['post_time'], $board_config['board_timezone']);

		$last_post_author = ( $topic_rowset[$i]['id2'] == ANONYMOUS ) ? ( !empty($topic_rowset[$i]['post_username2']) ? $topic_rowset[$i]['post_username2'] . ' ' : $lang['Guest'] . ' ' ) : $lang['Post_by_author'] . '&nbsp;<a href="' . append_sid("profile.$phpEx?mode=viewprofile&amp;" . POST_USERS_URL . '='  . $topic_rowset[$i]['id2']) . '">' . $topic_rowset[$i]['user2'] . '</a>';

		$last_post_url = '<a href="' . append_sid("viewtopic.$phpEx?"  . POST_POST_URL . '=' . $topic_rowset[$i]['topic_last_post_id']) . '#' . $topic_rowset[$i]['topic_last_post_id'] . '"><img src="' . $images['icon_latest_reply'] . '" alt="' . $lang['View_latest_post'] . '" title="' . $lang['View_latest_post'] . '" border="0" /></a>';

		$views = $topic_rowset[$i]['topic_views'];
		
		$row_color = ( !($i % 2) ) ? $theme['td_color1'] : $theme['td_color2'];
		$row_class = ( !($i % 2) ) ? $theme['td_class1'] : $theme['td_class2'];

		//
		// Decide how to order the post display
		//
		if ( !empty($_POST['postorder']) || !empty($_GET['postorder']) )
		{
			$post_order = (!empty($_POST['postorder'])) ? htmlspecialchars($_POST['postorder']) : htmlspecialchars($_GET['postorder']);
			$post_time_order = ($post_order == "asc") ? "ASC" : "DESC";
		}
		else
		{
			$post_order = 'asc';
			$post_time_order = 'ASC';
		}		
		
		
		if( !empty($_POST['postdays']) || !empty($_GET['postdays']) )
		{
			$post_days = ( !empty($_POST['postdays']) ) ? intval($_POST['postdays']) : intval($_GET['postdays']);
			$min_post_time = time() - (intval($post_days) * 86400);

			$sql = "SELECT COUNT(p.post_id) AS num_posts
				FROM " . TOPICS_TABLE . " t, " . POSTS_TABLE . " p
				WHERE t.topic_id = $topic_id
					AND p.topic_id = t.topic_id
					AND p.post_time >= $min_post_time";
			if ( !($result = $db->sql_query($sql)) )
			{
				message_die(GENERAL_ERROR, "Could not obtain limited topics count information", '', __LINE__, __FILE__, $sql);
			}

			$total_replies = ( $row = $db->sql_fetchrow($result) ) ? intval($row['num_posts']) : 0;

			$limit_posts_time = "AND p.post_time >= $min_post_time ";

			if ( !empty($_POST['postdays']))
			{
				$start = 0;
			}
		}
		else
		{
			$total_replies = intval($topic_rowset[$i]['topic_replies']) + 1;

			$limit_posts_time = '';
			$post_days = 0;
		}		
		
		
		//
		// If we've got a hightlight set pass it on to pagination,
		// I get annoyed when I lose my highlight after the first page.
		//
		$pagination = (!empty($highlight)) ? generate_pagination("viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id&amp;postdays=$post_days&amp;postorder=$post_order&amp;highlight=$highlight", $total_replies, $board_config['posts_per_page'], $start) : generate_pagination("viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id&amp;postdays=$post_days&amp;postorder=$post_order", $total_replies, $board_config['posts_per_page'], $start);
		
		// Send vars to template
		$topic_row = array(				
			'ROW_COLOR' => $row_color,
			'ROW_CLASS' => $row_class,
			
			'topicrow' => $topicrow,
			
			'FORUM_ID' => $forum_id,
			'TOPIC_ID' => $topic_id,
			
			'TOPIC_FOLDER_IMG' => $folder_image, 
			
			'TOPIC_ICON_IMG'		=> $user->img($folder_image, '', '27', '', 'src'),
			'TOPIC_ICON_IMG_WIDTH'	=> $user->img($folder_image, '', '27', '', 'width'),
			'TOPIC_ICON_IMG_HEIGHT'	=>  $user->img($folder_image, '', '27', '', 'height'),			
			
			'ATTACH_ICON_IMG'		=> ($auth->acl_get('u_download') && $auth->acl_get('f_download', $row['forum_id']) && $row['topic_attachment']) ? $user->img('icon_topic_attach', $user->lang['TOTAL_ATTACHMENTS']) : '',			
			
			'TOPIC_AUTHOR' => $topic_author,
			
			'GOTO_PAGE' => $goto_page,
			'REPLIES' => $replies,
			'NEWEST_POST_IMG' => $newest_post_img,
			
			'S_HAS_POLL'			=> !empty($topic_rowset[$i]['topic_vote']) ? true : false,
			'S_UNREAD_TOPIC'		=> $unread_topics,			

			'TOPIC_TITLE' => $topic_title,
			'TOPIC_TYPE' => $topic_type,
			
			'PAGINATION' => $pagination,			
			
			'S_TOPIC_REPORTED'		=> ($auth->acl_get('m_report', $forum_id)) ? true : false,			
			'S_TOPIC_UNAPPROVED'	=> $topic_unapproved,
			'S_POSTS_UNAPPROVED'	=> $posts_unapproved,
			'S_TOPIC_DELETED'		=> $topic_deleted,
			
			'S_POST_ANNOUNCE'		=> $topic_rowset[$i]['topic_type'] == POST_ANNOUNCE,
			'S_POST_GLOBAL'         => $topic_rowset[$i]['topic_type'] == POST_GLOBAL,
			'S_POST_STICKY'         => $topic_rowset[$i]['topic_type'] == POST_STICKY,
			'S_TOPIC_LOCKED'		=> $topic_rowset[$i]['topic_status'] == ITEM_LOCKED,
			'S_TOPIC_MOVED'			=> $topic_rowset[$i]['topic_status'] == ITEM_MOVED,
			
			'S_IS_LOCKED'			=> ($topic_rowset[$i]['topic_status'] == ITEM_UNLOCKED) ? false : true,			
			
			'VIEWS' => $views,
			'FIRST_POST_TIME' => $first_post_time, 
			'LAST_POST_TIME' => $last_post_time, 
			'LAST_POST_AUTHOR' => $last_post_author, 
			'LAST_POST_IMG' => $last_post_url, 

			'L_TOPIC_FOLDER_ALT' => $folder_alt, 

			'U_VIEW_TOPIC' => $view_topic_url,
			
			'S_TOPIC_TYPE_SWITCH'	=> ($s_type_switch == $s_type_switch_test) ? -1 : $s_type_switch_test,
		);
		
		$template->assign_block_vars('topicrow', $topic_row);
		
		$template->assign_vars(array(
			'.topicrow' 	=> $topicrow,
			'S_IS_LOCKED' => ($topic_rowset[$i]['topic_status'] == ITEM_UNLOCKED) ? false : true)
		);		
	}

	$topics_count -= $total_announcements;

	$template->assign_vars(array(
		'PAGINATION' => generate_pagination("viewforum.$phpEx?" . POST_FORUM_URL . "=$forum_id&amp;topicdays=$topic_days", $topics_count, $board_config['topics_per_page'], $start),
		'PAGE_NUMBER' => sprintf($lang['Page_of'], ( floor( $start / $board_config['topics_per_page'] ) + 1 ), ceil( $topics_count / $board_config['topics_per_page'] )),
		'L_GOTO_PAGE' => $lang['Goto_page'])
	);
}
else
{
	//
	// No topics
	//
	$no_topics_msg = ( $forum_row['forum_status'] == FORUM_LOCKED ) ? $lang['Forum_locked'] : $lang['No_topics_post_one'];
	$template->assign_vars(array(
		'L_NO_TOPICS' => $no_topics_msg)
	);

	$template->assign_block_vars('switch_no_topics', array() );

}

// Begin Simple Subforums MOD
switch(SQL_LAYER)
{
	case 'postgresql':
		$sql = "SELECT f.*, p.post_time, p.post_username, u.username, u.user_id 
			FROM " . FORUMS_TABLE . " f, " . POSTS_TABLE . " p, " . USERS_TABLE . " u
			WHERE p.post_id = f.forum_last_post_id 
				AND u.user_id = p.poster_id  
				AND f.forum_parent = '{$forum_id}'
				UNION (
					SELECT f.*, NULL, NULL, NULL, NULL
					FROM " . FORUMS_TABLE . " f
					WHERE NOT EXISTS (
						SELECT p.post_time
						FROM " . POSTS_TABLE . " p
						WHERE p.post_id = f.forum_last_post_id  
					)
				)
				ORDER BY cat_id, forum_order";
	break;

	case 'oracle':
		$sql = "SELECT f.*, p.post_time, p.post_username, u.username, u.user_id 
			FROM " . FORUMS_TABLE . " f, " . POSTS_TABLE . " p, " . USERS_TABLE . " u
			WHERE p.post_id = f.forum_last_post_id(+)
				AND u.user_id = p.poster_id(+)
				AND f.forum_parent = '{$forum_id}'
			ORDER BY f.cat_id, f.forum_order";
	break;

	default:
		$sql = "SELECT f.*, p.post_time, p.post_username, u.username, u.user_id
			FROM (( " . FORUMS_TABLE . " f
			LEFT JOIN " . POSTS_TABLE . " p ON p.post_id = f.forum_last_post_id )
			LEFT JOIN " . USERS_TABLE . " u ON u.user_id = p.poster_id )
			WHERE f.forum_parent = '{$forum_id}'
			ORDER BY f.cat_id, f.forum_order";
	break;
}
if ( !($result = $db->sql_query($sql)) )
{
	message_die(GENERAL_ERROR, 'Could not query subforums information', '', __LINE__, __FILE__, $sql);
}

$subforum_data = array();
while( $row = $db->sql_fetchrow($result) )
{
	$subforum_data[] = $row;
}
$db->sql_freeresult($result);

if ( ($total_forums = count($subforum_data)) > 0 )
{
	//
	// Find which forums are visible for this user
	//
	$is_auth_ary = array();
	$is_auth_ary = auth(AUTH_VIEW, AUTH_LIST_ALL, $user->data, $subforum_data);

	$display_forums = false;
	
	for( $j = 0; $j < $total_forums; $j++ )
	{
		if ( $is_auth_ary[$subforum_data[$j]['forum_id']]['auth_view'] )
		{
			$display_forums = true;
		}
	}
	
	if( !$display_forums )
	{
		$total_forums = 0;
	}
}

if( $total_forums )
{
	$template->assign_var('HAS_SUBFORUMS', ($forum_row['forum_parent']) ? true : false);
	$template->assign_block_vars('catrow', array(
		'CAT_ID'	=> $forum_id,
		'S_IS_LINK'	=> false,		
		'CAT_DESC'	=> $forum_row['forum_name'],
		'U_VIEWCAT' => append_sid("viewforum.$phpEx?" . POST_FORUM_URL ."=$forum_id"),
		));

	//
	// Obtain a list of topic ids which contain
	// posts made since user last visited
	//
	if ( $user->data['session_logged_in'] )
	{
		$sql = "SELECT t.forum_id, t.topic_id, p.post_time 
			FROM " . TOPICS_TABLE . " t, " . POSTS_TABLE . " p 
			WHERE p.post_id = t.topic_last_post_id 
				AND p.post_time > " . $user->data['user_lastvisit'] . " 
				AND t.topic_moved_id = 0"; 
		if ( !($result = $db->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, 'Could not query new topic information', '', __LINE__, __FILE__, $sql);
		}

		$new_topic_data = array();
		while( $topic_data = $db->sql_fetchrow($result) )
		{
			$new_topic_data[$topic_data['forum_id']][$topic_data['topic_id']] = $topic_data['post_time'];
		}
		$db->sql_freeresult($result);
	}
	//
	// Obtain list of moderators of each forum
	// First users, then groups ... broken into two queries
	//
	$subforum_moderators = array();
	$sql = "SELECT aa.forum_id, u.user_id, u.username 
		FROM " . AUTH_ACCESS_TABLE . " aa, " . USER_GROUP_TABLE . " ug, " . GROUPS_TABLE . " g, " . USERS_TABLE . " u
		WHERE aa.auth_mod = " . TRUE . " 
			AND g.group_single_user = 1 
			AND ug.group_id = aa.group_id 
			AND g.group_id = aa.group_id 
			AND u.user_id = ug.user_id 
		GROUP BY u.user_id, u.username, aa.forum_id 
		ORDER BY aa.forum_id, u.user_id";
	if ( !($result = $db->sql_query($sql, false, true)) )
	{
		message_die(GENERAL_ERROR, 'Could not query forum moderator information', '', __LINE__, __FILE__, $sql);
	}

	while( $row = $db->sql_fetchrow($result) )
	{
		$subforum_moderators[$row['forum_id']][] = '<a href="' . append_sid("profile.$phpEx?mode=viewprofile&amp;" . POST_USERS_URL . "=" . $row['user_id']) . '">' . $row['username'] . '</a>';
	}
	$db->sql_freeresult($result);
	
	$sql = "SELECT aa.forum_id, g.group_id, g.group_name 
		FROM " . AUTH_ACCESS_TABLE . " aa, " . USER_GROUP_TABLE . " ug, " . GROUPS_TABLE . " g 
		WHERE aa.auth_mod = " . TRUE . " 
			AND g.group_single_user = 0 
			AND g.group_type <> " . GROUP_HIDDEN . "
			AND ug.group_id = aa.group_id 
			AND g.group_id = aa.group_id 
		GROUP BY g.group_id, g.group_name, aa.forum_id 
		ORDER BY aa.forum_id, g.group_id";
	if ( !($result = $db->sql_query($sql, false, true)) )
	{
		message_die(GENERAL_ERROR, 'Could not query forum moderator information', '', __LINE__, __FILE__, $sql);
	}

	while( $row = $db->sql_fetchrow($result) )
	{
		$subforum_moderators[$row['forum_id']][] = '<a href="' . append_sid("groupcp.$phpEx?" . POST_GROUPS_URL . "=" . $row['group_id']) . '">' . 	$row['group_name'] . '</a>';
	}
	$db->sql_freeresult($result);

	// show subforums
	for( $j = 0; $j < $total_forums; $j++ )
	{		
		$topicrow = &$j;
		
		$topic_forum_id = ($row['forum_id']) ? (int) $row['forum_id'] : $forum_id;
		
		// This will allow the style designer to output a different header
		// or even separate the list of announcements from sticky and normal topics
		$s_type_switch_test = ($row['topic_type'] == POST_ANNOUNCE || $row['topic_type'] == POST_GLOBAL) ? 1 : 0;
							
		$subforum_id = $subforum_data[$j]['forum_id'];

		if ( $is_auth_ary[$subforum_id]['auth_view'] )
		{
			$unread_topics = false;
			if ( $subforum_data[$j]['forum_status'] == FORUM_LOCKED )
			{
				$folder_image = $images['forum_locked']; 
				$folder_alt = $lang['Forum_locked'];
			}
			else
			{
				if ( $user->data['session_logged_in'] )
				{
					if ( !empty($new_topic_data[$subforum_id]) )
					{
						$subforum_last_post_time = 0;

						while( list($check_topic_id, $check_post_time) = @each($new_topic_data[$subforum_id]) )
						{
							if ( empty($tracking_topics[$check_topic_id]) )
							{
								$unread_topics = true;
								$subforum_last_post_time = max($check_post_time, $subforum_last_post_time);
							}
							else
							{
								if ( $tracking_topics[$check_topic_id] < $check_post_time )
								{
									$unread_topics = true;
									$subforum_last_post_time = max($check_post_time, $subforum_last_post_time);
								}
							}
						}
						if ( !empty($tracking_forums[$subforum_id]) )
						{
							if ( $tracking_forums[$subforum_id] > $subforum_last_post_time )
							{
								$unread_topics = false;
							}
						}
						if ( isset($_COOKIE[$board_config['cookie_name'] . '_f_all']) )
						{
							if ( $_COOKIE[$board_config['cookie_name'] . '_f_all'] > $subforum_last_post_time )
							{
								$unread_topics = false;
							}
						}

					}
				}

				$folder_image = ( $unread_topics ) ? $images['forum_new'] : $images['forum']; 
				$folder_alt = ( $unread_topics ) ? $lang['New_posts'] : $lang['No_new_posts']; 
			}

			$posts = $subforum_data[$j]['forum_posts'];
			$topics = $subforum_data[$j]['forum_topics'];

			if ( $subforum_data[$j]['forum_last_post_id'] )
			{
				$last_post_time = create_date($board_config['default_dateformat'], $subforum_data[$j]['post_time'], $board_config['board_timezone']);

				$last_post = $last_post_time . '<br />';

				$last_post .= ( $subforum_data[$j]['user_id'] == ANONYMOUS ) ? ( !empty($subforum_data[$j]['post_username']) ? $subforum_data[$j]['post_username'] . ' ' : $lang['Guest'] . ' ' ) : '<a href="' . append_sid("profile.$phpEx?mode=viewprofile&amp;" . POST_USERS_URL . '='  . $subforum_data[$j]['user_id']) . '">' . $subforum_data[$j]['username'] . '</a> ';
								
				$last_post .= '<a href="' . append_sid("viewtopic.$phpEx?"  . POST_POST_URL . '=' . $subforum_data[$j]['forum_last_post_id']) . '#' . $subforum_data[$j]['forum_last_post_id'] . '"><img src="' . $images['icon_latest_reply'] . '" border="0" alt="' . $lang['View_latest_post'] . '" title="' . $lang['View_latest_post'] . '" /></a>';
			}
			else
			{
				$last_post = $lang['No_Posts'];
			}

			if (isset($subforum_moderators[$subforum_id]) && count($subforum_moderators[$subforum_id]) > 0)
			{
				$l_moderators = (count($subforum_moderators[$subforum_id]) == 1) ? $lang['Moderator'] : $lang['Moderators'];
				$moderator_list = implode(', ', $subforum_moderators[$subforum_id]);
			}
			else
			{
				$l_moderators = '&nbsp;';
				$moderator_list = '';
			}

			$row_color = ( !($i % 2) ) ? $theme['td_color1'] : $theme['td_color2'];
			$row_class = ( !($i % 2) ) ? $theme['td_class1'] : $theme['td_class2'];

			$template->assign_block_vars('catrow.forumrow',	array(
				'ROW_COLOR' => '#' . $row_color,
				'ROW_CLASS' => $row_class,
				'FORUM_FOLDER_IMG' => $folder_image, 
				'FORUM_NAME' => $subforum_data[$j]['forum_name'],
				'FORUM_DESC' => $subforum_data[$j]['forum_desc'],
				'ATTACH_ICON_IMG'	=> ($auth->acl_get('u_download') && $auth->acl_get('f_download', $row['forum_id']) && $row['topic_attachment']) ? $user->img('icon_topic_attach', $user->lang['TOTAL_ATTACHMENTS']) : '',				
				'POSTS' => $subforum_data[$j]['forum_posts'],
				'TOPICS' => $subforum_data[$j]['forum_topics'],
				'LAST_POST' => $last_post,
				'MODERATORS' => $moderator_list,
				'ID' => $subforum_data[$j]['forum_id'],
				'UNREAD' => intval($unread_topics),
				'LAST_POST_TIME' => $last_post_time,

				'L_MODERATOR' => $l_moderators, 
				'L_FORUM_FOLDER_ALT' => $folder_alt, 
				
				'U_VIEWFORUM' => append_sid("viewforum.$phpEx?" . POST_FORUM_URL . "=$subforum_id"))
			);

		}
	}
}
// End Simple Subforums MOD

//make_jumpbox(append_sid("{$phpbb_root_path}viewforum.$phpEx"));

	$action = 'viewforum';
	$sql = 'SELECT f.*
		FROM ' . FORUMS_TABLE . ' f
		ORDER BY left_id ASC';
	$result = $db->sql_query($sql, 600);

	$jbrowset = array();
	while ($jbrow = $db->sql_fetchrow($result))
	{
		$jbrowset[(int) $jbrow['forum_id']] = $jbrow;
	}
	$db->sql_freeresult($result);

	$right = $padding = 0;
	$padding_store = array('0' => 0);
	$display_jumpbox = false;
	$iteration = 0;

	// Sometimes it could happen that forums will be displayed here not be displayed within the index page
	// This is the result of forums not displayed at index, having list permissions and a parent of a forum with no permissions.
	// If this happens, the padding could be "broken"

	foreach ($jbrowset as $jbrow)
	{
		if ($jbrow['left_id'] < $right)
		{
			$padding++;
			$padding_store[$jbrow['parent_id']] = $padding;
		}
		else if ($jbrow['left_id'] > $right + 1)
		{
			// Ok, if the $padding_store for this parent is empty there is something wrong. For now we will skip over it.
			// @todo digging deep to find out "how" this can happen.
			$padding = (isset($padding_store[$jbrow['parent_id']])) ? $padding_store[$jbrow['parent_id']] : $padding;
		}

		$right = $jbrow['right_id'];

		if ($jbrow['forum_type'] == FORUM_CAT && ($jbrow['left_id'] + 1 == $jbrow['right_id']))
		{
			// Non-postable forum with no subforums, don't display
			continue;
		}

		if (!$auth->acl_get('f_list', $jbrow['forum_id']))
		{
			// if the user does not have permissions to list this forum skip
			continue;
		}

		if ($acl_list && !$auth->acl_gets($acl_list, $jbrow['forum_id']))
		{
			continue;
		}

		$tpl_ary = array();
		if (!$display_jumpbox)
		{
			$tpl_ary[] = array(
				'FORUM_ID'		=> ($select_all) ? 0 : -1,
				'FORUM_NAME'	=> ($select_all) ? $user->lang['ALL_FORUMS'] : $user->lang['SELECT_FORUM'],
				'S_FORUM_COUNT'	=> $iteration,
				'LINK'			=> $cache->append_url_params($action, array('f' => $forum_id)),
			);

			$iteration++;
			$display_jumpbox = true;
		}

		$tpl_ary[] = array(
			'FORUM_ID'		=> $jbrow['forum_id'],
			'FORUM_NAME'	=> $jbrow['forum_name'],
			'SELECTED'		=> ($jbrow['forum_id'] == $forum_id) ? ' selected="selected"' : '',
			'S_FORUM_COUNT'	=> $iteration,
			'S_IS_CAT'		=> ($jbrow['forum_type'] == FORUM_CAT) ? true : false,
			'S_IS_LINK'		=> ($jbrow['forum_type'] == FORUM_LINK) ? true : false,
			'S_IS_POST'		=> ($jbrow['forum_type'] == FORUM_POST) ? true : false,
			'LINK'			=> $cache->append_url_params($action, array('f' => $jbrow['forum_id'])),
		);

		$template->assign_block_vars_array('jumpbox_forums', $tpl_ary);

		unset($tpl_ary);

		for ($i = 0; $i < $padding; $i++)
		{
			$template->assign_block_vars('jumpbox_forums.level', array());
		}
		$iteration++;
	}
	unset($padding_store, $jbrowset);

	$url_parts = ''; //$cache->get_url_parts($action);

	$template->assign_vars(array(
		'S_DISPLAY_JUMPBOX'			=> $display_jumpbox,
		'S_JUMPBOX_ACTION'			=> $action,
		'HIDDEN_FIELDS_FOR_JUMPBOX'	=> '', //build_hidden_fields($url_parts['params']),
	));
 

//
// Parse the page and print
//
$template->pparse('body');

//
// Page footer
//
include($phpbb_root_path . 'includes/page_tail.'.$phpEx);

?>