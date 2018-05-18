<?php
/***************************************************************************
 *                               viewtopic.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id: viewtopic.php,v 1.1 2010/10/10 15:01:18 orynider Exp $
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
define("IN_VIEWTOPIC", true);

define('IN_PHPBB', true);
$phpbb_root_path = './';
//$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'extension.inc');
include_once($phpbb_root_path . 'common.'.$phpEx);
include_once($phpbb_root_path . 'includes/bbcode.'.$phpEx);

//
// Start initial var setup
//
// Initial var setup
$forum_id	= request_var('f', 0);
$topic_id	= request_var('t', 0);
$post_id	= request_var('p', 0);
$voted_id	= request_var('vote_id', array('' => 0));

$voted_id = (count($voted_id) > 1) ? array_unique($voted_id) : $voted_id;


$start	= request_var('start', 0);
$view	= request_var('view', 'show');
$start = ($start < 0) ? 0 : $start;

$default_sort_days	= (!empty($user->data['user_post_show_days'])) ? $user->data['user_post_show_days'] : 0;
$default_sort_key	= (!empty($user->data['user_post_sortby_type'])) ? $user->data['user_post_sortby_type'] : 't';
$default_sort_dir	= (!empty($user->data['user_post_sortby_dir'])) ? $user->data['user_post_sortby_dir'] : 'a';

$sort_days	= request_var('st', $default_sort_days);
$sort_key	= request_var('sk', $default_sort_key);
$sort_dir	= request_var('sd', $default_sort_dir);

$update	= request_var('update', false);

/* @var $pagination */
$pagination = $cache->get('pagination');

$quickmod = (isset($_REQUEST['quickmod'])) ? true : false;

$s_can_vote = false;
/**
* @todo normalize?
*/
$hilit_words = request_var('hilit', '', true);

// Do we have a topic or post id?
if (!$topic_id && !$post_id)
{
	trigger_error('Topic_post_not_exist');
}

if (!$topic_id && !$post_id)
{
	message_die(GENERAL_MESSAGE, 'Topic_post_not_exist');
}

//
// Find topic id if user requested a newer
// or older topic
//
if ( isset($_GET['view']) && empty($_GET[POST_POST_URL]) )
{
	if ( $_GET['view'] == 'newest' )
	{
		if ( isset($_COOKIE[$board_config['cookie_name'] . '_sid']) || isset($_GET['sid']) )
		{
			$session_id = isset($_COOKIE[$board_config['cookie_name'] . '_sid']) ? $_COOKIE[$board_config['cookie_name'] . '_sid'] : $_GET['sid'];

			if (!preg_match('/^[A-Za-z0-9]*$/', $session_id)) 
			{
				$session_id = '';
			}

			if ( $session_id )
			{
				$sql = "SELECT p.post_id
					FROM " . POSTS_TABLE . " p, " . SESSIONS_TABLE . " s,  " . USERS_TABLE . " u
					WHERE s.session_id = '$session_id'
						AND u.user_id = s.session_user_id
						AND p.topic_id = $topic_id
						AND p.post_time >= u.user_lastvisit
					ORDER BY p.post_time ASC
					LIMIT 1";
				if ( !($result = $db->sql_query($sql)) )
				{
					message_die(GENERAL_ERROR, 'Could not obtain newer/older topic information', '', __LINE__, __FILE__, $sql);
				}

				if ( !($row = $db->sql_fetchrow($result)) )
				{
					message_die(GENERAL_MESSAGE, 'No_new_posts_last_visit');
				}

				$post_id = $row['post_id'];

				if (isset($_GET['sid']))
				{
					redirect("viewtopic.$phpEx?sid=$session_id&" . POST_POST_URL . "=$post_id#$post_id");
				}
				else
				{
					redirect("viewtopic.$phpEx?" . POST_POST_URL . "=$post_id#$post_id");
				}
			}
		}

		redirect(append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id", true));
	}
	else if ( $_GET['view'] == 'next' || $_GET['view'] == 'previous' )
	{
		$sql_condition = ( $_GET['view'] == 'next' ) ? '>' : '<';
		$sql_ordering = ( $_GET['view'] == 'next' ) ? 'ASC' : 'DESC';

		$sql = "SELECT t.topic_id
			FROM " . TOPICS_TABLE . " t, " . TOPICS_TABLE . " t2
			WHERE
				t2.topic_id = $topic_id
				AND t.forum_id = t2.forum_id
				AND t.topic_moved_id = 0
				AND t.topic_last_post_id $sql_condition t2.topic_last_post_id
			ORDER BY t.topic_last_post_id $sql_ordering
			LIMIT 1";
		if ( !($result = $db->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, "Could not obtain newer/older topic information", '', __LINE__, __FILE__, $sql);
		}

		if ( $row = $db->sql_fetchrow($result) )
		{
			$topic_id = intval($row['topic_id']);
		}
		else
		{
			$message = ( $_GET['view'] == 'next' ) ? 'No_newer_topics' : 'No_older_topics';
			message_die(GENERAL_MESSAGE, $message);
		}
	}
}

//
// This rather complex gaggle of code handles querying for topics but
// also allows for direct linking to a post (and the calculation of which
// page the post is on and the correct display of viewtopic)
//
$join_sql_table = (!$post_id) ? '' : ", " . POSTS_TABLE . " p, " . POSTS_TABLE . " p2 ";
$join_sql = (!$post_id) ? "t.topic_id = $topic_id" : "p.post_id = $post_id AND t.topic_id = p.topic_id AND p2.topic_id = p.topic_id AND p2.post_id <= $post_id";
$count_sql = (!$post_id) ? '' : ", COUNT(p2.post_id) AS prev_posts";

$order_sql = (!$post_id) ? '' : "GROUP BY p.post_id, t.topic_id, t.topic_title, t.topic_status, t.topic_replies, t.topic_time, t.topic_type, t.topic_vote, t.topic_last_post_id, f.forum_name, f.forum_status, f.forum_id, f.forum_id as forum_type, f.auth_view, f.auth_read, f.auth_post, f.auth_reply, f.auth_edit, f.auth_delete, f.auth_sticky, f.auth_announce, f.auth_pollcreate, f.auth_vote, f.auth_attachments ORDER BY p.post_id ASC";

$sql = "SELECT t.*, f.*, f.forum_id as forum_type, t.topic_status as topic_attachment, t.topic_status as bookmarked " . $count_sql . "
	FROM " . TOPICS_TABLE . " t, " . FORUMS_TABLE . " f" . $join_sql_table . "
	WHERE $join_sql
		AND f.forum_id = t.forum_id
		$order_sql";
if ( !($result = $db->sql_query($sql)) )
{
	$join_sql_table = (!$post_id) ? '' : ", " . POSTS_TABLE . " p, " . POSTS_TABLE . " p2 ";
	$join_sql = (!$post_id) ? "t.topic_id = $topic_id" : "p.post_id = $post_id AND t.topic_id = p.topic_id AND p2.topic_id = p.topic_id AND p2.post_id <= $post_id";
	$count_sql = (!$post_id) ? '' : ", COUNT(p2.post_id) AS prev_posts";

	$order_sql = (!$post_id) ? '' : "GROUP BY p.post_id, t.topic_id, t.topic_title, t.topic_status, t.topic_replies, t.topic_time, t.topic_type, t.topic_vote, t.topic_last_post_id, f.forum_name, f.forum_status, f.forum_id, f.auth_view, f.auth_read, f.auth_post, f.auth_reply, f.auth_edit, f.auth_delete, f.auth_sticky, f.auth_announce, f.auth_pollcreate, f.auth_vote, f.auth_attachments ORDER BY p.post_id ASC";

	$sql = "SELECT t.topic_id, t.topic_title, t.topic_status, t.topic_replies, t.topic_time, t.topic_type, t.topic_vote, t.topic_last_post_id, f.forum_name, f.forum_status, f.forum_id, f.auth_view, f.auth_read, f.auth_post, f.auth_reply, f.auth_edit, f.auth_delete, f.auth_sticky, f.auth_announce, f.auth_pollcreate, f.auth_vote, f.auth_attachments" . $count_sql . "
		FROM " . TOPICS_TABLE . " t, " . FORUMS_TABLE . " f" . $join_sql_table . "
		WHERE $join_sql
			AND f.forum_id = t.forum_id
			$order_sql";
	if ( !($result = $db->sql_query($sql)) )
	{
		message_die(GENERAL_ERROR, "Could not obtain topic information", '', __LINE__, __FILE__, $sql);
	}
}

if ( !($forum_topic_data = $db->sql_fetchrow($result)) )
{
	message_die(GENERAL_MESSAGE, 'Topic_post_not_exist');
}

$forum_id = intval($forum_topic_data['forum_id']);

//
// Start session management
//
$userdata = session_pagestart($user_ip, $forum_id);
init_userprefs($userdata);
//
// End session management
//

@define('USER_NORMAL', 0);
@define('USER_INACTIVE', 1);
@define('USER_IGNORE', 2);

//
// Start auth check
//
$is_auth = array();
$is_auth = auth(AUTH_ALL, $forum_id, $userdata, $forum_topic_data);

if( !$is_auth['auth_view'] || !$is_auth['auth_read'] )
{
	if ( !$userdata['session_logged_in'] )
	{
		$redirect = ($post_id) ? POST_POST_URL . "=$post_id" : POST_TOPIC_URL . "=$topic_id";
		$redirect .= ($start) ? "&start=$start" : '';
		redirect(append_sid("login.$phpEx?redirect=viewtopic.$phpEx&$redirect", true));
	}

	$message = ( !$is_auth['auth_view'] ) ? $lang['Topic_post_not_exist'] : sprintf($lang['Sorry_auth_read'], $is_auth['auth_read_type']);

	message_die(GENERAL_MESSAGE, $message);
}
//
// End auth check
//

$forum_name = $forum_topic_data['forum_name'];
$topic_title = $forum_topic_data['topic_title'];
$topic_id = intval($forum_topic_data['topic_id']);
$topic_time = $forum_topic_data['topic_time'];

$s_limit_days = $s_sort_key = $s_sort_dir = $u_sort_param = '';

// Was a highlight request part of the URI?
$highlight_match = $highlight = '';
if (!$hilit_words)
{
	$highlight_match = phpbb_clean_search_string($hilit_words);
	$highlight = urlencode($highlight_match);
	$highlight_match = str_replace('\*', '\w+?', preg_quote($highlight_match, '#'));
	$highlight_match = preg_replace('#(?<=^|\s)\\\\w\*\?(?=\s|$)#', '\w+?', $highlight_match);
	$highlight_match = str_replace(' ', '|', $highlight_match);
}

if ($post_id)
{
	$start = floor(($forum_topic_data['prev_posts'] - 1) / intval($board_config['posts_per_page'])) * intval($board_config['posts_per_page']);
}

// General Viewtopic URL for return links
$viewtopic_url = append_sid("{$phpbb_root_path}viewtopic.$phpEx", "f=$forum_id&amp;t=$topic_id" . (($start == 0) ? '' : "&amp;start=$start") . ((strlen($u_sort_param)) ? "&amp;$u_sort_param" : '') . (($highlight_match) ? "&amp;hilit=$highlight" : ''));

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
//
// Is user watching this thread?
//
if( $userdata['session_logged_in'] )
{
	$can_watch_topic = TRUE;

	$sql = "SELECT notify_status
		FROM " . TOPICS_WATCH_TABLE . "
		WHERE topic_id = $topic_id
			AND user_id = " . $userdata['user_id'];
	if ( !($result = $db->sql_query($sql)) )
	{
		message_die(GENERAL_ERROR, "Could not obtain topic watch information", '', __LINE__, __FILE__, $sql);
	}

	if ( $row = $db->sql_fetchrow($result) )
	{
		if ( isset($_GET['unwatch']) )
		{
			if ( $_GET['unwatch'] == 'topic' )
			{
				$is_watching_topic = 0;

				$sql_priority = (SQL_LAYER == "mysql") ? "LOW_PRIORITY" : '';
				$sql = "DELETE $sql_priority FROM " . TOPICS_WATCH_TABLE . "
					WHERE topic_id = $topic_id
						AND user_id = " . $userdata['user_id'];
				if ( !($result = $db->sql_query($sql)) )
				{
					message_die(GENERAL_ERROR, "Could not delete topic watch information", '', __LINE__, __FILE__, $sql);
				}
			}

			$template->assign_vars(array(
				'META' => '<meta http-equiv="refresh" content="3;url=' . append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id&amp;start=$start") . '">')
			);

			$message = $lang['No_longer_watching'] . '<br /><br />' . sprintf($lang['Click_return_topic'], '<a href="' . append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id&amp;start=$start") . '">', '</a>');
			message_die(GENERAL_MESSAGE, $message);
		}
		else
		{
			$is_watching_topic = TRUE;

			if ( $row['notify_status'] )
			{
				$sql_priority = (SQL_LAYER == "mysql") ? "LOW_PRIORITY" : '';
				$sql = "UPDATE $sql_priority " . TOPICS_WATCH_TABLE . "
					SET notify_status = 0
					WHERE topic_id = $topic_id
						AND user_id = " . $userdata['user_id'];
				if ( !($result = $db->sql_query($sql)) )
				{
					message_die(GENERAL_ERROR, "Could not update topic watch information", '', __LINE__, __FILE__, $sql);
				}
			}
		}
	}
	else
	{
		if ( isset($_GET['watch']) )
		{
			if ( $_GET['watch'] == 'topic' )
			{
				$is_watching_topic = TRUE;

				$sql_priority = (SQL_LAYER == "mysql") ? "LOW_PRIORITY" : '';
				$sql = "INSERT $sql_priority INTO " . TOPICS_WATCH_TABLE . " (user_id, topic_id, notify_status)
					VALUES (" . $userdata['user_id'] . ", $topic_id, 0)";
				if ( !($result = $db->sql_query($sql)) )
				{
					message_die(GENERAL_ERROR, "Could not insert topic watch information", '', __LINE__, __FILE__, $sql);
				}
			}

			$template->assign_vars(array(
				'META' => '<meta http-equiv="refresh" content="3;url=' . append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id&amp;start=$start") . '">')
			);

			$message = $lang['You_are_watching'] . '<br /><br />' . sprintf($lang['Click_return_topic'], '<a href="' . append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id&amp;start=$start") . '">', '</a>');
			message_die(GENERAL_MESSAGE, $message);
		}
		else
		{
			$is_watching_topic = 0;
		}
	}
}
else
{
	if ( isset($_GET['unwatch']) )
	{
		if ( $_GET['unwatch'] == 'topic' )
		{
			redirect(append_sid("login.$phpEx?redirect=viewtopic.$phpEx&" . POST_TOPIC_URL . "=$topic_id&unwatch=topic", true));
		}
	}
	else
	{
		$can_watch_topic = 0;
		$is_watching_topic = 0;
	}
}

//
// Generate a 'Show posts in previous x days' select box. If the postdays var is POSTed
// then get it's value, find the number of topics with dates newer than it (to properly
// handle pagination) and alter the main query
//
$previous_days = array(0, 1, 7, 14, 30, 90, 180, 364);
// Topic ordering options
$limit_days = array(0 => $user->lang['All_Topics'], 1 => $user->lang['1_Day'], 7 => $user->lang['7_Days'], 14 => $user->lang['2_Weeks'], 30 => $user->lang['1_Month'], 90 => $user->lang['3_Months'], 180 => $user->lang['6_Months'], 365 => $user->lang['1_Year']);
$previous_days_text = array($lang['All_Topics'], $lang['1_Day'], $lang['7_Days'], $lang['2_Weeks'], $lang['1_Month'], $lang['3_Months'], $lang['6_Months'], $lang['1_Year']);
$sort_by_text = array('a' => $user->lang['Author'], 't' => $user->lang('POST_TIME'), 'r' => $user->lang['Replies'], 's' => $user->lang['Subject'], 'v' => $user->lang['Views']);
$sort_by_sql = array('a' => 't.topic_first_poster_name', 't' => array('t.topic_last_post_time', 't.topic_last_post_id'), 'r' => (($auth->acl_get('m_approve', $forum_id)) ? 't.topic_posts_approved + t.topic_posts_unapproved + t.topic_posts_softdeleted' : 't.topic_posts_approved'), 's' => 'LOWER(t.topic_title)', 'v' => 't.topic_views');
gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param, $default_sort_days, $default_sort_key, $default_sort_dir);

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
	$total_replies = intval($forum_topic_data['topic_replies']) + 1;

	$limit_posts_time = '';
	$post_days = 0;
}

$s_forum_rules = '';
if (isset($forum_topic_data['forum_rules']))
{
	$forum_topic_data['forum_rules'] = generate_text_for_display($forum_topic_data['forum_rules'], $forum_topic_data['forum_rules_uid'], $forum_topic_data['forum_rules_bitfield'], $forum_topic_data['forum_rules_options']);
}

if (!isset($forum_topic_data['forum_rules']) && !isset($forum_topic_data['forum_rules_link']))
{
	$forum_topic_data['forum_rules'] = '';
	$forum_topic_data['forum_rules_link'] = 'faq.'.$phpEx;
}

$template->assign_vars(array(
	'S_FORUM_RULES'	=> true,
	'U_FORUM_RULES'	=> $forum_topic_data['forum_rules_link'],
	'FORUM_RULES'	=> $forum_topic_data['forum_rules'])
);


$select_post_days = '<select name="postdays">';
for($i = 0; $i < count($previous_days); $i++)
{
	$selected = ($post_days == $previous_days[$i]) ? ' selected="selected"' : '';
	$select_post_days .= '<option value="' . $previous_days[$i] . '"' . $selected . '>' . $previous_days_text[$i] . '</option>';
}
$select_post_days .= '</select>';

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

$select_post_order = '<select name="postorder">';
if ( $post_time_order == 'ASC' )
{
	$select_post_order .= '<option value="asc" selected="selected">' . $lang['Oldest_First'] . '</option><option value="desc">' . $lang['Newest_First'] . '</option>';
}
else
{
	$select_post_order .= '<option value="asc">' . $lang['Oldest_First'] . '</option><option value="desc" selected="selected">' . $lang['Newest_First'] . '</option>';
}
$select_post_order .= '</select>';

//
// Go ahead and pull all data for this topic
//
$sql = "SELECT u.username, u.user_id, u.user_posts, u.user_level, u.user_from, u.user_website, u.user_email, u.user_icq, u.user_aim, u.user_yim, u.user_regdate, u.user_msnm, u.user_viewemail, u.user_rank, u.user_sig, u.user_sig_bbcode_uid, u.user_avatar, u.user_avatar_type, u.user_allowavatar, u.user_allowsmile, p.*,  pt.post_text, pt.post_subject, pt.bbcode_uid
	FROM " . POSTS_TABLE . " p, " . USERS_TABLE . " u, " . POSTS_TEXT_TABLE . " pt
	WHERE p.topic_id = $topic_id
		$limit_posts_time
		AND pt.post_id = p.post_id
		AND u.user_id = p.poster_id
	ORDER BY p.post_time $post_time_order
	LIMIT $start, ".$board_config['posts_per_page'];
if ( !($result = $db->sql_query($sql)) )
{
	message_die(GENERAL_ERROR, "Could not obtain post/user information.", '', __LINE__, __FILE__, $sql);
}

$postrow = array();
if ($row = $db->sql_fetchrow($result))
{
	do
	{
		$postrow[] = $row;
	}
	while ($row = $db->sql_fetchrow($result));
	$db->sql_freeresult($result);

	$total_posts = count($postrow);
}
else 
{ 
   include($phpbb_root_path . 'includes/functions_admin.' . $phpEx); 
   sync('topic', $topic_id); 

   message_die(GENERAL_MESSAGE, $lang['No_posts_topic']); 
} 

$resync = FALSE; 
if ($forum_topic_data['topic_replies'] + 1 < $start + count($postrow)) 
{ 
   $resync = TRUE; 
} 
elseif ($start + $board_config['posts_per_page'] > $forum_topic_data['topic_replies']) 
{ 
   $row_id = intval($forum_topic_data['topic_replies']) % intval($board_config['posts_per_page']); 
   if ($postrow[$row_id]['post_id'] != $forum_topic_data['topic_last_post_id'] || $start + count($postrow) < $forum_topic_data['topic_replies']) 
   { 
      $resync = TRUE; 
   } 
} 
elseif (count($postrow) < $board_config['posts_per_page']) 
{ 
   $resync = TRUE; 
} 

if ($resync) 
{ 
   include($phpbb_root_path . 'includes/functions_admin.' . $phpEx); 
   sync('topic', $topic_id); 

   $result = $db->sql_query('SELECT COUNT(post_id) AS total FROM ' . POSTS_TABLE . ' WHERE topic_id = ' . $topic_id); 
   $row = $db->sql_fetchrow($result); 
   $total_replies = $row['total']; 
}

$sql = "SELECT *
	FROM " . RANKS_TABLE . "
	ORDER BY rank_special, rank_min";
if ( !($result = $db->sql_query($sql)) )
{
	message_die(GENERAL_ERROR, "Could not obtain ranks information.", '', __LINE__, __FILE__, $sql);
}

$ranksrow = array();
while ( $row = $db->sql_fetchrow($result) )
{
	$ranksrow[] = $row;
}
$db->sql_freeresult($result);


//
// Define censored word matches
//
$orig_word = array();
$replacement_word = array();
obtain_word_list($orig_word, $replacement_word);

//
// Censor topic title
//
if ( count($orig_word) )
{
	$topic_title = preg_replace($orig_word, $replacement_word, $topic_title);
}

//
// Was a highlight request part of the URI?
//
$highlight_match = $highlight = '';
if (isset($_GET['highlight']))
{
	// Split words and phrases
	$words = explode(' ', trim(htmlspecialchars($_GET['highlight'])));

	for($i = 0; $i < sizeof($words); $i++)
	{
		if (!empty(trim($words[$i])))
		{
			$highlight_match .= (!empty($highlight_match) ? '|' : '') . str_replace('*', '\w*', preg_quote($words[$i], '#'));
		}
	}
	unset($words);

	$highlight = urlencode($_GET['highlight']);
	$highlight_match = phpbb_rtrim($highlight_match, "\\");
}

//
// Post, reply and other URL generation for
// templating vars
//
$new_topic_url = append_sid("posting.$phpEx?mode=newtopic&amp;" . POST_FORUM_URL . "=$forum_id");
$reply_topic_url = append_sid("posting.$phpEx?mode=reply&amp;" . POST_TOPIC_URL . "=$topic_id");
$view_forum_url = append_sid("viewforum.$phpEx?" . POST_FORUM_URL . "=$forum_id");
$view_prev_topic_url = append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id&amp;view=previous");
$view_next_topic_url = append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id&amp;view=next");

//
// Mozilla navigation bar
//
$nav_links['prev'] = array(
	'url' => $view_prev_topic_url,
	'title' => $lang['View_previous_topic']
);
$nav_links['next'] = array(
	'url' => $view_next_topic_url,
	'title' => $lang['View_next_topic']
);
$nav_links['up'] = array(
	'url' => $view_forum_url,
	'title' => $forum_name
);

$reply_img = ( $forum_topic_data['forum_status'] == FORUM_LOCKED || $forum_topic_data['topic_status'] == TOPIC_LOCKED ) ? $images['reply_locked'] : $images['reply_new'];
$reply_alt = ( $forum_topic_data['forum_status'] == FORUM_LOCKED || $forum_topic_data['topic_status'] == TOPIC_LOCKED ) ? $lang['Topic_locked'] : $lang['Reply_to_topic'];
$post_img = ( $forum_topic_data['forum_status'] == FORUM_LOCKED ) ? $images['post_locked'] : $images['post_new'];
$post_alt = ( $forum_topic_data['forum_status'] == FORUM_LOCKED ) ? $lang['Forum_locked'] : $lang['Post_new_topic'];

//
// Set a cookie for this topic
//
if ( $userdata['session_logged_in'] )
{
	$tracking_topics = ( isset($_COOKIE[$board_config['cookie_name'] . '_t']) ) ? unserialize($_COOKIE[$board_config['cookie_name'] . '_t']) : array();
	$tracking_forums = ( isset($_COOKIE[$board_config['cookie_name'] . '_f']) ) ? unserialize($_COOKIE[$board_config['cookie_name'] . '_f']) : array();

	if ( !empty($tracking_topics[$topic_id]) && !empty($tracking_forums[$forum_id]) )
	{
		$topic_last_read = ( $tracking_topics[$topic_id] > $tracking_forums[$forum_id] ) ? $tracking_topics[$topic_id] : $tracking_forums[$forum_id];
	}
	else if ( !empty($tracking_topics[$topic_id]) || !empty($tracking_forums[$forum_id]) )
	{
		$topic_last_read = ( !empty($tracking_topics[$topic_id]) ) ? $tracking_topics[$topic_id] : $tracking_forums[$forum_id];
	}
	else
	{
		$topic_last_read = $userdata['user_lastvisit'];
	}

	if ( count($tracking_topics) >= 150 && empty($tracking_topics[$topic_id]) )
	{
		asort($tracking_topics);
		unset($tracking_topics[key($tracking_topics)]);
	}

	$tracking_topics[$topic_id] = time();

	setcookie($board_config['cookie_name'] . '_t', serialize($tracking_topics), 0, $board_config['cookie_path'], $board_config['cookie_domain'], $board_config['cookie_secure']);
}

//
// Load templates
//
$template->set_filenames(array(
	'body' => 'viewtopic_body.tpl')
);
// Begin Simple Subforums MOD
$all_forums = array();
make_jumpbox_ref('viewforum.'.$phpEx, $forum_id, $all_forums);

$parent_id = 0;
for( $i = 0; $i < count($all_forums); $i++ )
{
	if( $all_forums[$i]['forum_id'] == $forum_id )
	{
		$parent_id = $all_forums[$i]['forum_parent'];
	}
}

if( $parent_id )
{
	for( $i = 0; $i < count($all_forums); $i++ )
	{
		if( $all_forums[$i]['forum_id'] == $parent_id )
		{
			$template->assign_vars(array(
				'PARENT_FORUM'			=> $parent_id,
				'U_VIEW_PARENT_FORUM'	=> append_sid("viewforum.$phpEx?" . POST_FORUM_URL .'=' . $all_forums[$i]['forum_id']),
				'PARENT_FORUM_NAME'		=> $all_forums[$i]['forum_name'],
				));
		}
	}
}
// End Simple Subforums MOD


//
// Output page header
//
$page_title = $lang['View_topic'] .' - ' . $topic_title;
include($phpbb_root_path . 'includes/page_header.'.$phpEx);

//
// User authorisation levels output
//
$s_auth_can = '';
$topic_mod = '';

// Quick mod tools
$allow_change_type = ($is_auth['auth_mod'] || ($user->data['user_active'] && $user->data['user_id'] == $forum_topic_data['topic_poster'])) ? true : false;

if ( $is_auth['auth_mod'] )
{	
	$s_auth_can .= sprintf($lang['Rules_moderate'], "<p>[&nbsp;<a href=\"modcp.$phpEx?" . POST_FORUM_URL . "=$forum_id&amp;sid=" . $userdata['session_id'] . '">', '</a>&nbsp;]</p>');
	$topic_mod .= '<form><fieldset class="' . 'jumpbox"' . '><label>' . $lang['Quick_mod'] . '&nbsp;' . '</label><select name="' . 'choice">';
	$topic_mod .= "<option value=\"modcp.$phpEx?" . POST_TOPIC_URL . "=$topic_id&amp;mode=delete&amp;sid=" . $userdata['session_id'] . '&amp;_top">' . $lang['Delete_topic'] . '</option>';
	$topic_mod .= "<option value=\"modcp.$phpEx?" . POST_TOPIC_URL . "=$topic_id&amp;mode=move&amp;sid=" . $userdata['session_id'] . '&amp;_top">' . $lang['Move_topic'] . '</option>';
	$topic_mod .= ( $forum_topic_data['topic_status'] == TOPIC_UNLOCKED ) ? "<option value=\"modcp.$phpEx?" . POST_TOPIC_URL . "=$topic_id&amp;mode=lock&amp;sid=" . $userdata['session_id'] . '&amp;_top">' . $lang['Lock_topic'] . '</option>' : "<option value=\"modcp.$phpEx?" . POST_TOPIC_URL . "=$topic_id&amp;mode=unlock&amp;sid=" . $userdata['session_id'] . '&amp;_top">' . $lang['Unlock_topic'] . '</option>';
	$topic_mod .= "<option value=\"modcp.$phpEx?" . POST_TOPIC_URL . "=$topic_id&amp;mode=split&amp;sid=" . $userdata['session_id'] . '&amp;_top">' . $lang['Split_topic'] . '</option>';
	$topic_mod .= '</select> <input class="' . 'button2"' . ' type="' . 'button" value=' . '"Go' . '" onClick=' . '"jump(this.form)' . '"></fieldset></form>';
}
elseif ( $is_auth['auth_edit'] )
{
	$s_auth_can .= $lang['Rules_edit_can'] . '<br />';
}
else
{
	$s_auth_can .= ( ( $is_auth['auth_post'] ) ? $lang['Rules_post_can'] : $lang['Rules_post_cannot'] ) . '<br />';
	$s_auth_can .= ( ( $is_auth['auth_reply'] ) ? $lang['Rules_reply_can'] : $lang['Rules_reply_cannot'] ) . '<br />';
	$s_auth_can .= ( ( $is_auth['auth_edit'] ) ? $lang['Rules_edit_can'] : $lang['Rules_edit_cannot'] ) . '<br />';
	$s_auth_can .= ( ( $is_auth['auth_delete'] ) ? $lang['Rules_delete_can'] : $lang['Rules_delete_cannot'] ) . '<br />';
	$s_auth_can .= ( ( $is_auth['auth_vote'] ) ? $lang['Rules_vote_can'] : $lang['Rules_vote_cannot'] ) . '<br />';
}

$s_quickmod_action = append_sid(
	"{$phpbb_root_path}mcp.$phpEx",
	array(
		'f'	=> $forum_id,
		't'	=> $topic_id,
		'start'		=> $start,
		'quickmod'	=> 1,
		'redirect'	=> urlencode(str_replace('&amp;', '&', $viewtopic_url)),
	),
	true,
	$user->session_id
);

// Generate all the URIs ...
$view_topic_url_params = 'f=' . $forum_id . '&amp;t=' . $topic_id;
$viewtopic_url = ($auth->acl_get('f_read', $forum_id) || $is_auth['auth_view']) ? (($viewtopic_url) ? $viewtopic_url : append_sid("viewtopic.$phpEx?" . $view_topic_url_params)) : false;

//
// Topic watch information
//
$watching_topic = '';
if ( $can_watch_topic )
{
	if ( $is_watching_topic )
	{
		$watching_topic = "<a href=\"viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id&amp;unwatch=topic&amp;start=$start&amp;sid=" . $userdata['session_id'] . '" class=' . '"icon-subscribe' . '">' . $lang['Stop_watching_topic'] . '</a>';
		$watching_topic_img = ( isset($images['topic_un_watch']) ) ? "<a href=\"viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id&amp;unwatch=topic&amp;start=$start&amp;sid=" . $userdata['session_id'] . '"><img src="' . $images['topic_un_watch'] . '" alt="' . $lang['Stop_watching_topic'] . '" title="' . $lang['Stop_watching_topic'] . '" border="0"></a>' : '';
	}
	else
	{
		$watching_topic = "<a href=\"viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id&amp;watch=topic&amp;start=$start&amp;sid=" . $userdata['session_id'] . '" class=' . '"icon-subscribe' . '">' . $lang['Start_watching_topic'] . '</a>';
		$watching_topic_img = ( isset($images['Topic_watch']) ) ? "<a href=\"viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id&amp;watch=topic&amp;start=$start&amp;sid=" . $userdata['session_id'] . '"><img src="' . $images['Topic_watch'] . '" alt="' . $lang['Start_watching_topic'] . '" title="' . $lang['Start_watching_topic'] . '" border="0"></a>' : '';
	}
}

// Are we watching this topic?
$s_watching_topic = array(
	'link'			=> '',
	'link_toggle'	=> '',
	'title'			=> '',
	'title_toggle'	=> '',
	'is_watching'	=> false,
);


$notify_status = (isset($forum_topic_data['notify_status'])) ? $forum_topic_data['notify_status'] : null;

// Reset forum notification if forum notify is set
if ($auth->acl_get('f_subscribe', $forum_id))
{
	$s_watching_forum = $s_watching_topic;
}

//
// If we've got a hightlight set pass it on to pagination,
// I get annoyed when I lose my highlight after the first page.
//
$pagination = (!empty($highlight)) ? generate_pagination("viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id&amp;postdays=$post_days&amp;postorder=$post_order&amp;highlight=$highlight", $total_replies, $board_config['posts_per_page'], $start) : generate_pagination("viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id&amp;postdays=$post_days&amp;postorder=$post_order", $total_replies, $board_config['posts_per_page'], $start);

	

//
// Send vars to template
//
$template->assign_vars(array(
	'FORUM_ID' => $forum_id,
    'FORUM_NAME' => $forum_name,
	'FORUM_DESC' => generate_text_for_display($forum_topic_data['forum_desc'], $user->data['user_sig_bbcode_uid'], $user->default_bitfield(), false),
 	'PARENT_FORUM'	=> !isset($forum_topic_data['forum_parent']) ? $forum_topic_data['forum_parent'] : '',   
	'TOPIC_ID' => $topic_id, //$topic_id = intval($forum_topic_data['topic_id']);
    'TOPIC_TITLE' => $topic_title,	
	'PAGINATION' => $pagination,
	'PAGE_NUMBER' => sprintf($lang['Page_of'], ( floor( $start / intval($board_config['posts_per_page']) ) + 1 ), ceil( $total_replies / intval($board_config['posts_per_page']) )),
	'MODERATORS' => $forum_moderators,
	'POST_IMG' => $post_img, //
	'REPLY_IMG' => $reply_img, //reply-icon
	
	'TOTAL_POSTS' => $forum_topic_data['forum_posts'],
	'TOTAL_TOPICS' => $forum_topic_data['forum_topics'],	

	'U_WATCH_TOPIC'			=> $s_watching_topic['link'],
	'U_WATCH_TOPIC_TOGGLE'	=> $s_watching_topic['link_toggle'],
	'S_WATCH_TOPIC_TITLE'	=> $s_watching_topic['title'],
	'S_WATCH_TOPIC_TOGGLE'	=> $s_watching_topic['title_toggle'],
	'S_WATCHING_TOPIC'		=> $s_watching_topic['is_watching'],		
	
	'U_BOOKMARK_TOPIC'		=> ($user->data['user_active']) ? $viewtopic_url . '&amp;bookmark=1&amp;hash=' . generate_link_hash("topic_$topic_id") : '',
	'S_BOOKMARK_TOPIC'		=> ($user->data['user_active']  && $forum_topic_data['bookmarked']) ? $user->lang('BOOKMARK_TOPIC_REMOVE') : $user->lang('BOOKMARK_TOPIC'),
	'S_BOOKMARK_TOGGLE'		=> (!$user->data['user_active'] || !$forum_topic_data['bookmarked']) ? $user->lang('BOOKMARK_TOPIC_REMOVE') : $user->lang['BOOKMARK_TOPIC'],
	'S_BOOKMARKED_TOPIC'	=> ($user->data['user_active']  && $forum_topic_data['bookmarked']) ? true : false,	
	
	'L_AUTHOR' => $lang['Author'],
	'L_MESSAGE' => $lang['Message'],
	'L_POSTED' => $lang['Posted'],
	'L_POST_SUBJECT' => $lang['Post_subject'],
	'L_VIEW_NEXT_TOPIC' => $lang['View_next_topic'],
	'L_VIEW_PREVIOUS_TOPIC' => $lang['View_previous_topic'],
	'L_POST_NEW_TOPIC' => $post_alt,
	'L_POST_REPLY_TOPIC' => $reply_alt,
	'L_BACK_TO_TOP' => $lang['Back_to_top'],
	'L_DISPLAY_POSTS' => $lang['Display_posts'],
	'L_LOCK_TOPIC' => $lang['Lock_topic'],
	'L_UNLOCK_TOPIC' => $lang['Unlock_topic'],
	'L_MOVE_TOPIC' => $lang['Move_topic'],
	'L_SPLIT_TOPIC' => $lang['Split_topic'],
	'L_DELETE_TOPIC' => $lang['Delete_topic'],
	'L_GOTO_PAGE' => $lang['Goto_page'],
	'L_MODERATOR' => $l_moderators,
	
	'QUICKMOD' => $quickmod,	
	
	'S_IS_LOCKED'	=> ($forum_topic_data['forum_status'] == FORUM_LOCKED || $forum_topic_data['topic_status'] == TOPIC_LOCKED) ? true : false,	
	'S_TOPIC_ACTION' => append_sid("{$phpbb_root_path}viewtopic.$phpEx?f=$forum_id&amp;t=$topic_id" . (($start == 0) ? '' : "&amp;start=$start")),
	'S_TOPIC_LINK' => POST_TOPIC_URL,
	'S_TOPIC_MOD' 	=> !empty($topic_mod) ? $topic_mod : '<select name="action" id="quick-mod-select"></select>',	
	'S_SELECT_POST_DAYS' => $select_post_days,
	'S_SELECT_POST_ORDER' => $select_post_order,
	'S_POST_DAYS_ACTION' => append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . '=' . $topic_id . "&amp;start=$start"),
	'S_AUTH_LIST' => $s_auth_can,
	'S_TOPIC_ADMIN' => $topic_mod,
	'S_WATCH_TOPIC' => $watching_topic,
	'S_WATCH_TOPIC_IMG' => $watching_topic_img,

	'S_HAS_POLL'	=> !empty($forum_topic_data['topic_vote']) ? true : false,
	'S_TOPIC_ICONS'		=> true,
	
	'S_IS_POSTABLE'			=> true,
	'S_SINGLE_MODERATOR'	=> (!empty($moderators[$forum_id]) && count($moderators[$forum_id]) > 1) ? false : true,
	'S_NO_READ_ACCESS'		=> false,	
	
	'S_DISPLAY_POST_INFO'	=> ($is_auth['auth_post'] || $user->data['user_id'] == ANONYMOUS) ? true : false,
	'S_DISPLAY_REPLY_INFO'	=> (($forum_topic_data['forum_type'] > 0) && ($is_auth['auth_reply'] || $user->data['user_id'] == ANONYMOUS)) ? true : false,	
	
	'S_DISPLAY_SEARCHBOX'	=> ($auth->acl_get('u_search') && $auth->acl_get('f_search', $forum_id)) ? true : false,
	'S_SEARCHBOX_ACTION'	=> append_sid("{$phpbb_root_path}search.$phpEx"),
	
	'U_TOPIC'				=> "{$phpbb_root_path}viewtopic.$phpEx?f=$forum_id&amp;t=$topic_id",
	'U_FORUM'				=> $view_forum_url,
	'U_CANONICAL'			=> generate_board_url() . '/' . append_sid("viewtopic.$phpEx", "t=$topic_id" . (($start) ? "&amp;start=$start" : ''), true, ''),

	'U_PRINT_TOPIC'			=> ($auth->acl_get('f_print', $forum_id)) ? $viewtopic_url . '&amp;view=print' : '',
	'U_EMAIL_TOPIC'			=> ($auth->acl_get('f_email', $forum_id) && $board_config['email_enable']) ? append_sid("{$phpbb_root_path}profile.$phpEx", "mode=email&amp;t=$topic_id") : '',
	'U_BUMP_TOPIC'			=> append_sid("{$phpbb_root_path}posting.$phpEx", "mode=bump&amp;f=$forum_id&amp;t=$topic_id&amp;hash=" . generate_link_hash("topic_$topic_id")),	
	
	'U_VIEW_TOPIC' => append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id&amp;start=$start&amp;postdays=$post_days&amp;postorder=$post_order&amp;highlight=$highlight"),
	'U_VIEW_FORUM' => $view_forum_url,
	'U_CANONICAL' => generate_board_url() . '/' . append_sid("viewtopic.$phpEx?t=$topic_id" . (($start) ? "&amp;start=$start" : ''), true, ''),	
	'U_VIEW_OLDER_TOPIC' => $view_prev_topic_url,
	'U_VIEW_NEWER_TOPIC' => $view_next_topic_url,
	'U_VIEW_UNREAD_POST' => '#unread',	
	
	'U_POST_NEW_TOPIC' => $new_topic_url,
	'U_POST_REPLY_TOPIC' => $reply_topic_url)
);

//
// Does this topic contain a poll?
//
if ( !empty($forum_topic_data['topic_vote']) )
{
	$s_hidden_fields = '';

	$sql = "SELECT vd.vote_id, vd.vote_text, vd.vote_start, vd.vote_length, vr.vote_option_id, vr.vote_option_text, vr.vote_result
		FROM " . VOTE_DESC_TABLE . " vd, " . VOTE_RESULTS_TABLE . " vr
		WHERE vd.topic_id = $topic_id
			AND vr.vote_id = vd.vote_id
		ORDER BY vr.vote_option_id ASC";
	if ( !($result = $db->sql_query($sql)) )
	{
		message_die(GENERAL_ERROR, "Could not obtain vote data for this topic", '', __LINE__, __FILE__, $sql);
	}

	if ( $vote_info = $db->sql_fetchrowset($result) )
	{
		$db->sql_freeresult($result);
		$vote_options = count($vote_info);

		$vote_id = $vote_info[0]['vote_id'];
		$vote_title = $vote_info[0]['vote_text'];

		$sql = "SELECT vote_id
			FROM " . VOTE_USERS_TABLE . "
			WHERE vote_id = $vote_id
				AND vote_user_id = " . intval($userdata['user_id']);
		if ( !($result = $db->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, "Could not obtain user vote data for this topic", '', __LINE__, __FILE__, $sql);
		}

		$user_voted = ( $row = $db->sql_fetchrow($result) ) ? TRUE : 0;
		$db->sql_freeresult($result);

		if ( isset($_GET['vote']) || isset($_POST['vote']) )
		{
			$view_result = ( ( ( isset($_GET['vote']) ) ? $_GET['vote'] : $_POST['vote'] ) == 'viewresult' ) ? TRUE : 0;
		}
		else
		{
			$view_result = 0;
		}

		$poll_expired = ( $vote_info[0]['vote_length'] ) ? ( ( $vote_info[0]['vote_start'] + $vote_info[0]['vote_length'] < time() ) ? TRUE : 0 ) : 0;

		if ( $user_voted || $view_result || $poll_expired || !$is_auth['auth_vote'] || $forum_topic_data['topic_status'] == TOPIC_LOCKED )
		{
			$template->set_filenames(array(
				'pollbox' => 'viewtopic_poll_result.tpl')
			);

			$vote_results_sum = 0;

			for($i = 0; $i < $vote_options; $i++)
			{
				$vote_results_sum += $vote_info[$i]['vote_result'];
			}

			$vote_graphic = 0;
			$vote_graphic_max = count($images['voting_graphic']);

			for($i = 0; $i < $vote_options; $i++)
			{
				$vote_percent = ( $vote_results_sum > 0 ) ? $vote_info[$i]['vote_result'] / $vote_results_sum : 0;
				$vote_graphic_length = round($vote_percent * $board_config['vote_graphic_length']);

				$vote_graphic_img = $images['voting_graphic'][$vote_graphic];
				$vote_graphic = ($vote_graphic < $vote_graphic_max - 1) ? $vote_graphic + 1 : 0;

				if ( count($orig_word) )
				{
					$vote_info[$i]['vote_option_text'] = preg_replace($orig_word, $replacement_word, $vote_info[$i]['vote_option_text']);
				}

				$template->assign_block_vars("poll_option", array(
					'POLL_OPTION_CAPTION' => $vote_info[$i]['vote_option_text'],
					'POLL_OPTION_RESULT' => $vote_info[$i]['vote_result'],
					'POLL_OPTION_PERCENT' => sprintf("%.1d%%", ($vote_percent * 100)),

					'POLL_OPTION_IMG' => $vote_graphic_img,
					'POLL_OPTION_IMG_WIDTH' => $vote_graphic_length)
				);
			}

			$template->assign_vars(array(
				'L_TOTAL_VOTES' => $lang['Total_votes'],
				'TOTAL_VOTES' => $vote_results_sum)
			);

		}
		else
		{
			$template->set_filenames(array(
				'pollbox' => 'viewtopic_poll_ballot.tpl')
			);

			for($i = 0; $i < $vote_options; $i++)
			{
				if ( count($orig_word) )
				{
					$vote_info[$i]['vote_option_text'] = preg_replace($orig_word, $replacement_word, $vote_info[$i]['vote_option_text']);
				}

				$template->assign_block_vars("poll_option", array(
					'POLL_OPTION_ID' => $vote_info[$i]['vote_option_id'],
					'POLL_OPTION_CAPTION' => $vote_info[$i]['vote_option_text'])
				);
			}

			$template->assign_vars(array(
				'L_SUBMIT_VOTE' => $lang['Submit_vote'],
				'L_VIEW_RESULTS' => $lang['View_results'],

				'U_VIEW_RESULTS' => append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id&amp;postdays=$post_days&amp;postorder=$post_order&amp;vote=viewresult"))
			);

			$s_hidden_fields = '<input type="hidden" name="topic_id" value="' . $topic_id . '" /><input type="hidden" name="mode" value="vote" />';
		}

		if ( count($orig_word) )
		{
			$vote_title = preg_replace($orig_word, $replacement_word, $vote_title);
		}

		$s_hidden_fields .= '<input type="hidden" name="sid" value="' . $userdata['session_id'] . '" />';

		$template->assign_vars(array(
			'POLL_QUESTION' => $vote_title,
			'S_HAS_POLL'	=> true,
			'S_HIDDEN_FIELDS' => $s_hidden_fields,
			'S_POLL_ACTION' => append_sid("posting.$phpEx?mode=vote&amp;" . POST_TOPIC_URL . "=$topic_id"))
		);

		$template->assign_var_from_handle('POLL_DISPLAY', 'pollbox');
	}
}

//
// Update the topic view counter
//
$sql = "UPDATE " . TOPICS_TABLE . "
	SET topic_views = topic_views + 1
	WHERE topic_id = $topic_id";
if ( !$db->sql_query($sql) )
{
	message_die(GENERAL_ERROR, "Could not update topic views.", '', __LINE__, __FILE__, $sql);
}

// If the user is trying to reach the second half of the topic, fetch it starting from the end
$store_reverse = false;
$sql_limit = $board_config['posts_per_page'];
$sql_sort_order = $direction = '';

if ($start > $total_posts / 2)
{
	$store_reverse = true;

	// Select the sort order
	$direction = (($sort_dir == 'd') ? 'ASC' : 'DESC');
	$sql_start = $start;
}
else
{
	// Select the sort order
	$direction = (($sort_dir == 'd') ? 'DESC' : 'ASC');
	$sql_start = $start;
}

//
// Go ahead and pull all data for this topic
$i = $i_total = 0;
$sql = "SELECT u.username, u.user_id, u.user_posts, p.post_id
	FROM " . POSTS_TABLE . " p, " . USERS_TABLE . " u, " . POSTS_TEXT_TABLE . " pt
	WHERE p.topic_id = $topic_id
		$limit_posts_time
		AND pt.post_id = p.post_id
		AND u.user_id = p.poster_id
	ORDER BY p.post_time $post_time_order
	LIMIT $start, ".$board_config['posts_per_page'];
if ( !($result = $db->sql_query($sql)) )
{
	message_die(GENERAL_ERROR, "Could not obtain post/user information.", '', __LINE__, __FILE__, $sql);
}

$i = ($store_reverse) ? $sql_limit - 1 : 0;
while ($post_row = $db->sql_fetchrow($result))
{
	$post_list[$i] = (int) $post_row['post_id'];
	($store_reverse) ? $i-- : $i++;
}
$db->sql_freeresult($result);

if (!count($post_list))
{
	if ($sort_days)
	{
		trigger_error('NO_POSTS_TIME_FRAME');
	}
	else
	{
		trigger_error('NO_TOPIC');
	}
}


// Holding maximum post time for marking topic read
// We need to grab it because we do reverse ordering sometimes
$max_post_time = 0;

$sql_ary = array(
	'SELECT'	=> 'u.*, u.user_id as user_colour, u.user_level as user_type, u.user_avatar as avatar, u.user_avatar_type as avatar_type, p.*, p.post_username as post_edit_reason, p.post_username as post_edit_user, pt.*',

	'FROM'		=> array(
		USERS_TABLE		=> 'u',
		POSTS_TABLE		=> 'p',
		POSTS_TEXT_TABLE => 'pt'
	),

	'WHERE'		=> $db->sql_in_set('p.post_id', $post_list) . '
		AND u.user_id = p.poster_id',
);

$sql = $db->sql_build_query('SELECT', $sql_ary);
if ( !($result = $db->sql_query($sql)) )
{
	message_die(GENERAL_ERROR, "Could not obtain post/user information.", '', __LINE__, __FILE__, $sql);
}

$now = $user->create_datetime();
$now = phpbb_gmgetdate($now->getTimestamp() + $now->getOffset());

// Posts are stored in the $rowset array while $attach_list, $user_cache
// and the global bbcode_bitfield are built
while ($row = $db->sql_fetchrow($result))
{
	// Set max_post_time
	if ($row['post_time'] > $max_post_time)
	{
		$max_post_time = $row['post_time'];
	}

	$poster_id = (int) $row['poster_id'];

	// Does post have an attachment? If so, add it to the list
	$attach_list[] = (int) $row['post_id'];
	
	$row['icon_id'] = 77;
	
	$icons[$row['icon_id']]['img'] = $user->img($images['post_new'], $lang['Post_new_topic'], '27', '', '');
	$icons[$row['icon_id']]['height'] = $user->img($images['post_new'], $lang['Post_new_topic'], '27', '', 'height');
	$icons[$row['icon_id']]['width'] = $user->img($images['post_new'], $lang['Post_new_topic'], '27', '', 'width');	
	
	$rowset_data = array(
		'hide_post'			=> ($view != 'show' || $post_id != $row['post_id']) ? false : true,

		'post_id'			=> $row['post_id'],
		'post_time'			=> $row['post_time'],
		'user_id'			=> $row['user_id'],
		'username'			=> $row['username'],
		'user_colour'		=> $row['user_colour'],
		'topic_id'			=> $row['topic_id'],
		'forum_id'			=> $row['forum_id'],
		'post_subject'		=> $row['post_subject'],
		'post_edit_count'	=> $row['post_edit_count'],
		'post_edit_time'	=> $row['post_edit_time'],
		'post_edit_reason'	=> $row['post_edit_reason'],
		'post_edit_user'	=> $row['post_edit_user'],
		'post_edit_locked'	=> '', //$row['post_edit_locked'],
		'post_delete_time'	=> '', //$row['post_delete_time'],
		'post_delete_reason'=> '', //$row['post_delete_reason'],
		'post_delete_user'	=> '', //$row['post_delete_user'],

		// Make sure the icon actually exists
		'icon_id'			=> (isset($icons[$row['icon_id']]['img'], $icons[$row['icon_id']]['height'], $icons[$row['icon_id']]['width'])) ? $row['icon_id'] : 0,
		'post_attachment'	=> '', //$row['post_attachment'],
		'post_visibility'	=> '', //$row['post_visibility'],
		'post_reported'		=> '', //$row['post_reported'],
		'post_username'		=> $row['post_username'],
		'post_text'			=> $row['post_text'],
		'bbcode_uid'		=> $row['bbcode_uid'],
		'bbcode_bitfield'	=> '//g=',
		'enable_smilies'	=> $row['enable_smilies'],
		'enable_sig'		=> $row['enable_sig'],
		'friend'			=> '', //$row['friend'],
		'foe'				=> '', //$row['foe'],
	);

	$rowset[$row['post_id']] = $rowset_data;

	// Cache various user specific data ... so we don't have to recompute
	// this each time the same user appears on this page
	if (!isset($user_cache[$poster_id]))
	{
		if ($poster_id == ANONYMOUS)
		{
			$user_cache_data = array(
				'user_type'		=> USER_IGNORE,
				'joined'		=> '',
				'posts'			=> '',

				'sig'					=> '',
				'sig_bbcode_uid'		=> '',
				'sig_bbcode_bitfield'	=> '',

				'online'			=> false,
				'avatar'			=> ($user->optionget('viewavatars')) ? phpbb_get_user_avatar($row) : '',
				'rank_title'		=> '',
				'rank_image'		=> '',
				'rank_image_src'	=> '',
				'pm'				=> '',
				'email'				=> '',
				'jabber'			=> '',
				'search'			=> '',
				'age'				=> '',

				'username'			=> $row['username'],
				'user_colour'		=> $row['user_colour'],
				'contact_user'		=> '',

				'warnings'			=> 0,
				'allow_pm'			=> 0,
				
				'author_full'		=> get_username_string('full', $poster_id, $row['username'], $row['user_colour']),
				'author_colour'		=> get_username_string('colour', $poster_id, $row['username'], $row['user_colour']),
				'author_username'	=> get_username_string('username', $poster_id, $row['username'], $row['user_colour']),
				'author_profile'	=> get_username_string('profile', $poster_id, $row['username'], $row['user_colour']),				
			);

			$user_cache[$poster_id] = $user_cache_data;

			$user_rank_data = phpbb_get_user_rank($row, false);
			$user_cache[$poster_id]['rank_title'] = $user_rank_data['title'];
			$user_cache[$poster_id]['rank_image'] = $user_rank_data['img'];
			$user_cache[$poster_id]['rank_image_src'] = $user_rank_data['img_src'];
		}
		else
		{
			$user_sig = '';

			// We add the signature to every posters entry because enable_sig is post dependent
			if ($row['user_sig'] && $board_config['allow_sig'] && $user->optionget('viewsigs'))
			{
				$user_sig = $row['user_sig'];
			}

			$id_cache[] = $poster_id;

			$user_cache_data = array(
				'user_type'					=> $row['user_type'],
				'user_inactive_reason'		=> '', //$$row['user_inactive_reason'],

				'joined'		=> $user->format_date($row['user_regdate']),
				'posts'			=> $row['user_posts'],
				'warnings'		=> (isset($row['user_warnings'])) ? $row['user_warnings'] : 0,

				'sig'					=> $user_sig,
				'sig_bbcode_uid'		=> (!empty($row['user_sig_bbcode_uid'])) ? $row['user_sig_bbcode_uid'] : '',
				'sig_bbcode_bitfield'	=> (!empty($row['user_sig_bbcode_bitfield'])) ? $row['user_sig_bbcode_bitfield'] : '',

				'viewonline'	=> $row['user_allow_viewonline'],
				'allow_pm'		=> $row['user_allow_pm'],

				'avatar'		=> ($user->optionget('viewavatars')) ? phpbb_get_user_avatar($row) : '',
				'age'			=> '',

				'rank_title'		=> '',
				'rank_image'		=> '',
				'rank_image_src'	=> '',

				'username'			=> $row['username'],
				'user_colour'		=> $row['user_colour'],
				'contact_user' 		=> $user->lang('CONTACT_USER', get_username_string('username', $poster_id, $row['username'], $row['user_colour'], $row['username'])),

				'online'		=> false,
				'jabber'		=> append_sid("{$phpbb_root_path}profile.{$phpEx}?mode=contact&amp;action=jabber&amp;u=$poster_id"),
				'search'		=> ($auth->acl_get('u_search')) ? append_sid("{$phpbb_root_path}search.$phpEx", "author_id=$poster_id&amp;sr=posts") : '',

				'author_full'		=> get_username_string('full', $poster_id, $row['username'], $row['user_colour'], $row['post_username']),
				'author_colour'		=> get_username_string('colour', $poster_id, $row['username'], $row['user_colour'], $row['post_username']),
				'author_username'	=> get_username_string('username', $poster_id, $row['username'], $row['user_colour'], $row['post_username']),
				'author_profile'	=> get_username_string('profile', $poster_id, $row['username'], $row['user_colour'], $row['post_username']),				
			);

			$user_cache[$poster_id] = $user_cache_data;

			$user_rank_data = phpbb_get_user_rank($row, $row['user_posts']);
			$user_cache[$poster_id]['rank_title'] = $user_rank_data['title'];
			$user_cache[$poster_id]['rank_image'] = $user_rank_data['img'];
			$user_cache[$poster_id]['rank_image_src'] = $user_rank_data['img_src'];

			if ((!empty($row['user_allow_viewemail']) && $auth->acl_get('u_sendemail')) || $auth->acl_get('a_email'))
			{
				$user_cache[$poster_id]['email'] = ($board_config['board_email_form'] && $board_config['email_enable']) ? append_sid("{$phpbb_root_path}profile.$phpEx?mode=email&amp;u=$poster_id") : (!$auth->acl_get('a_email') ? '' : 'mailto:' . $row['user_email']);
			}
			else
			{
				$user_cache[$poster_id]['email'] = '';
			}

			if (!empty($row['user_birthday']))
			{
				list($bday_day, $bday_month, $bday_year) = array_map('intval', explode('-', $row['user_birthday']));

				if ($bday_year)
				{
					$diff = $now['mon'] - $bday_month;
					if ($diff == 0)
					{
						$diff = ($now['mday'] - $bday_day < 0) ? 1 : 0;
					}
					else
					{
						$diff = ($diff < 0) ? 1 : 0;
					}

					$user_cache[$poster_id]['age'] = (int) ($now['year'] - $bday_year - $diff);
				}
			}
		}
	}
}
$db->sql_freeresult($result);

// Pull attachment data
if (count($attach_list))
{
	if ($auth->acl_get('u_download') && $auth->acl_get('f_download', $forum_id))
	{
		$sql = 'SELECT *
			FROM ' . ATTACHMENTS_TABLE . '
			WHERE ' . $db->sql_in_set('post_msg_id', $attach_list) . '
				AND in_message = 0
			ORDER BY attach_id DESC, post_msg_id ASC';
		if ( !($result = $db->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, "Could not obtain ATTACHMENTS_TABLE information.", '', __LINE__, __FILE__, $sql);
		}

		while ($row = $db->sql_fetchrow($result))
		{
			$attachments[$row['post_msg_id']][] = $row;
		}
		$db->sql_freeresult($result);

		// No attachments exist, but post table thinks they do so go ahead and reset post_attach flags
		if (!count($attachments))
		{
			$sql = 'UPDATE ' . POSTS_TABLE . '
				SET post_attachment = 0
				WHERE ' . $db->sql_in_set('post_id', $attach_list);
			if ( !($result = $db->sql_query($sql)) )
			{
				message_die(GENERAL_ERROR, "Could not update POSTS_TABLE post_attachment.", '', __LINE__, __FILE__, $sql);
			}

			// We need to update the topic indicator too if the complete topic is now without an attachment
			if (count($rowset) != $total_posts)
			{
				// Not all posts are displayed so we query the db to find if there's any attachment for this topic
				$sql = 'SELECT a.post_msg_id as post_id
					FROM ' . ATTACHMENTS_TABLE . ' a, ' . POSTS_TABLE . " p
					WHERE p.topic_id = $topic_id
						AND p.post_visibility = " . ITEM_APPROVED . '
						AND p.topic_id = a.topic_id';
				if ( !($result = $db->sql_query_limit($sql, 1)) )
				{
					message_die(GENERAL_ERROR, "Could not obtain ATTACHMENTS_TABLE information.", '', __LINE__, __FILE__, $sql);
				}					
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				if (!$row)
				{
					$sql = 'UPDATE ' . TOPICS_TABLE . "
						SET topic_attachment = 0
						WHERE topic_id = $topic_id";
					if ( !($result = $db->sql_query($sql)) )
					{
						message_die(GENERAL_ERROR, "Could not update TOPICS_TABLE topic_attachment.", '', __LINE__, __FILE__, $sql);
					}
				}
			}
			else
			{
				$sql = 'UPDATE ' . TOPICS_TABLE . "
					SET topic_attachment = 0
					WHERE topic_id = $topic_id";
					if ( !($result = $db->sql_query($sql)) )
					{
						message_die(GENERAL_ERROR, "Could not update TOPICS_TABLE topic_attachment.", '', __LINE__, __FILE__, $sql);
					}
			}
		}
		else if ($has_approved_attachments && !$forum_topic_data['topic_attachment'])
		{
			// Topic has approved attachments but its flag is wrong
			$sql = 'UPDATE ' . TOPICS_TABLE . "
				SET topic_attachment = 1
				WHERE topic_id = $topic_id";
				if ( !($result = $db->sql_query($sql)) )
				{
					message_die(GENERAL_ERROR, "Could not update TOPICS_TABLE topic_attachment.", '', __LINE__, __FILE__, $sql);
				}

			$forum_topic_data['topic_attachment'] = 1;
		}
		else if ($has_unapproved_attachments && !$forum_topic_data['topic_attachment'])
		{
			// Topic has only unapproved attachments but we have the right to see and download them
			$forum_topic_data['topic_attachment'] = 1;
		}
	}
	else
	{
		$display_notice = true;
	}
}

$template->assign_vars(array(
	'S_HAS_ATTACHMENTS' => $forum_topic_data['topic_attachment'],
	'S_NUM_POSTS' => count($post_list))
);

//
// Forum/Topic states
//
@define('FORUM_CAT', 0);
@define('FORUM_POST', 1);
@define('FORUM_LINK', 2);
@define('ITEM_UNLOCKED', 0);
@define('ITEM_LOCKED', 1);
@define('ITEM_MOVED', 2);

//
// Okay, let's do the loop, yeah come on baby let's do the loop
// and it goes like this ...
// Output the posts
$first_unread = false;
$post_unread = true;
	
//for ($i = 0, $end = count($total_posts); $i < $end; ++$i)
for ($i = 0; $i < $total_posts; $i++)
{	
	$poster_id = $postrow[$i]['user_id'];
	$poster = ( $poster_id == ANONYMOUS ) ? $lang['Guest'] : $postrow[$i]['username'];

	$post_date = create_date($board_config['default_dateformat'], $postrow[$i]['post_time'], $board_config['board_timezone']);

	$poster_posts = ( $postrow[$i]['user_id'] != ANONYMOUS ) ? $lang['Posts'] . ': ' . $postrow[$i]['user_posts'] : '';

	$poster_from = ( $postrow[$i]['user_from'] && $postrow[$i]['user_id'] != ANONYMOUS ) ? $lang['Location'] . ': ' . $postrow[$i]['user_from'] : '';

	$poster_joined = ( $postrow[$i]['user_id'] != ANONYMOUS ) ? $lang['Joined'] . ': ' . create_date($lang['DATE_FORMAT'], $postrow[$i]['user_regdate'], $board_config['board_timezone']) : '';

	$poster_avatar = '';
	if ( $postrow[$i]['user_avatar_type'] && $poster_id != ANONYMOUS && $postrow[$i]['user_allowavatar'] )
	{
		switch( $postrow[$i]['user_avatar_type'] )
		{
			case USER_AVATAR_UPLOAD:
				$poster_avatar = ( $board_config['allow_avatar_upload'] ) ? '<img src="' . $board_config['avatar_path'] . '/' . $postrow[$i]['user_avatar'] . '" alt="" border="0" />' : '';
			break;
			case USER_AVATAR_REMOTE:
				$poster_avatar = ( $board_config['allow_avatar_remote'] ) ? '<img src="' . $postrow[$i]['user_avatar'] . '" alt="" border="0" />' : '';
			break;
			case USER_AVATAR_GALLERY:
				$poster_avatar = ( $board_config['allow_avatar_local'] ) ? '<img src="' . $board_config['avatar_gallery_path'] . '/' . $postrow[$i]['user_avatar'] . '" alt="" border="0" />' : '';
			break;
		}
	}

	//
	// Define the little post icon
	//
	if ( $userdata['session_logged_in'] && $postrow[$i]['post_time'] > $userdata['user_lastvisit'] && $postrow[$i]['post_time'] > $topic_last_read )
	{
		$mini_post_img = $images['icon_minipost_new'];
		$mini_post_alt = $lang['New_post'];
		$post_unread = true;
	}
	else
	{
		$mini_post_img = $images['icon_minipost'];
		$mini_post_alt = $lang['Post'];
		$post_unread = false;		
	}
	
	if (!$row = $postrow[$i])
	{
		// Setup user environment so we can process lang string
		$user->setup('lang_main');

		trigger_error('NO_TOPIC');
	}	

	$mini_post_url = append_sid("viewtopic.$phpEx?" . POST_POST_URL . '=' . $postrow[$i]['post_id']) . '#' . $postrow[$i]['post_id'];


	// Only mark topic if it's currently unread. Also make sure we do not set topic tracking back if earlier pages are viewed.
	if ( $userdata['session_logged_in'] && $postrow[$i]['post_time'] > $userdata['user_lastvisit'] && $postrow[$i]['post_time'] > $topic_last_read )
	{
		markread('topic', $forum_id, $topic_id, $max_post_time);

		// Update forum info
		$all_marked_read = update_forum_tracking_info($forum_id, $forum_topic_data['forum_last_post_time'], (isset($forum_topic_data['forum_mark_time'])) ? $forum_topic_data['forum_mark_time'] : false, false);
	}
	else
	{
		$all_marked_read = true;
	}	
	// If there are absolutely no more unread posts in this forum
	// and unread posts shown, we can safely show the #unread link
	if ($all_marked_read)
	{
		if ($post_unread)
		{
			$template->assign_vars(array(
				'U_VIEW_UNREAD_POST'	=> '#unread',
			));
		}
		else if (isset($topic_tracking_info[$topic_id]) && $forum_topic_data['topic_last_post_time'] > $topic_tracking_info[$topic_id])
		{
			$template->assign_vars(array(
				'U_VIEW_UNREAD_POST'	=> append_sid("{$phpbb_root_path}viewtopic.{$phpEx}?f=$forum_id&amp;t=$topic_id&amp;view=unread") . '#unread',
			));
		}
	}
	else
	{
		$last_page = ((floor($start / $board_config['posts_per_page']) + 1) == max(ceil($total_posts / $board_config['posts_per_page']), 1)) ? true : false;

		// What can happen is that we are at the last displayed page. If so, we also display the #unread link based in $post_unread
		if ($last_page && $post_unread)
		{
			$template->assign_vars(array(
				'U_VIEW_UNREAD_POST'	=> '#unread',
			));
		}
		else
		{
			$template->assign_vars(array(
				'U_VIEW_UNREAD_POST'	=> append_sid("{$phpbb_root_path}viewtopic.{$phpEx}?f=$forum_id&amp;t=$topic_id&amp;view=unread") . '#unread',
			));
		}
	}
		
	
	$s_first_unread = false;
	if (!$first_unread && $post_unread)
	{
		$s_first_unread = $first_unread = true;
	}
	
	// let's set up quick_reply
	$s_quick_reply = false;
	if ($user->data['user_active'] && $is_auth['auth_reply'])
	{
		// Quick reply enabled forum
		$s_quick_reply = (($forum_topic_data['forum_status'] == ITEM_UNLOCKED && $forum_topic_data['topic_status'] == ITEM_UNLOCKED) || $auth->acl_get('m_edit', $forum_id)) ? true : false;
	}

	if ($s_can_vote || $s_quick_reply)
	{
		add_form_key('posting');

		if ($s_quick_reply)
		{
			$s_attach_sig	= $board_config['allow_sig'] && $user->optionget('attachsig') && $auth->acl_get('f_sigs', $forum_id) && $auth->acl_get('u_sig');
			$s_smilies		= $board_config['allow_smilies'] && $user->optionget('smilies') && $auth->acl_get('f_smilies', $forum_id);
			$s_bbcode		= $board_config['allow_bbcode'] && $user->optionget('bbcode') && $auth->acl_get('f_bbcode', $forum_id);
			$s_notify		= ($user->data['user_notify'] || $s_watching_topic['is_watching']);

			$qr_hidden_fields = array(
				'topic_cur_post_id'		=> (int) $forum_topic_data['topic_last_post_id'],
				'lastclick'				=> (int) time(),
				'topic_id'				=> (int) $forum_topic_data['topic_id'],
				'forum_id'				=> (int) $forum_id,
			);

			// Originally we use checkboxes and check with isset(), so we only provide them if they would be checked
			(!$s_bbcode)					? $qr_hidden_fields['disable_bbcode'] = 1		: true;
			(!$s_smilies)					? $qr_hidden_fields['disable_smilies'] = 1		: true;
			($qr_hidden_fields['disable_magic_url'] = 1)	? 	: true;
			($s_attach_sig)					? $qr_hidden_fields['attach_sig'] = 1			: true;
			($s_notify)						? $qr_hidden_fields['notify'] = 1				: true;
			($forum_topic_data['topic_status'] == ITEM_LOCKED) ? $qr_hidden_fields['lock_topic'] = 1 : true;

			$template->assign_vars(array(
				'S_QUICK_REPLY'			=> true,
				'U_QR_ACTION'			=> append_sid("{$phpbb_root_path}posting.$phpEx?mode=reply&amp;f=$forum_id&amp;t=$topic_id"),
				'QR_HIDDEN_FIELDS'		=> build_hidden_fields($qr_hidden_fields),
				'SUBJECT'				=> 'Re: ' . censor_text($forum_topic_data['topic_title']),
			));
		}
	}
	// now I have the urge to wash my hands :(	
	
	//
	// Generate ranks, set them to empty string initially.
	//
	$poster_rank = '';
	$rank_image = '';
	if ( $postrow[$i]['user_id'] == ANONYMOUS )
	{
	}
	else if ( $postrow[$i]['user_rank'] )
	{
		for($j = 0; $j < count($ranksrow); $j++)
		{
			if ( $postrow[$i]['user_rank'] == $ranksrow[$j]['rank_id'] && $ranksrow[$j]['rank_special'] )
			{
				$poster_rank = $ranksrow[$j]['rank_title'];
				$rank_image = ( $ranksrow[$j]['rank_image'] ) ? '<img src="' . $ranksrow[$j]['rank_image'] . '" alt="' . $poster_rank . '" title="' . $poster_rank . '" border="0" /><br />' : '';
			}
		}
	}
	else
	{
		for($j = 0; $j < count($ranksrow); $j++)
		{
			if ( $postrow[$i]['user_posts'] >= $ranksrow[$j]['rank_min'] && !$ranksrow[$j]['rank_special'] )
			{
				$poster_rank = $ranksrow[$j]['rank_title'];
				$rank_image = ( $ranksrow[$j]['rank_image'] ) ? '<img src="' . $ranksrow[$j]['rank_image'] . '" alt="' . $poster_rank . '" title="' . $poster_rank . '" border="0" /><br />' : '';
			}
		}
	}

	//
	// Handle anon users posting with usernames
	//
	if ( $poster_id == ANONYMOUS && (!empty($postrow[$i]['post_username'])) )
	{
		$poster = $postrow[$i]['post_username'];
		$poster_rank = $lang['Guest'];
	}
	
	$user_cache[] = array();
	
	//
	// Get user list
	//
	$sql = "SELECT u.user_id, u.username, u.user_allow_viewonline, u.user_level, s.session_user_id, MAX(s.session_time) as online_time, MIN(s.session_logged_in) AS viewonline, s.session_logged_in, s.session_time, s.session_page, s.session_ip
		FROM ".USERS_TABLE." u, ".SESSIONS_TABLE." s
		WHERE " . $db->sql_in_set('session_user_id', $poster_id) . "			
			AND u.user_id = s.session_user_id
			AND s.session_time >= ".( time() - 300 ) . "
		ORDER BY u.username ASC, s.session_ip ASC";			
	if ( !($result = $db->sql_query($sql)) )
	{
		message_die(GENERAL_ERROR, 'Could not obtain regd user/online information', '', __LINE__, __FILE__, $sql);
	}

	$update_time = 60;
	while ($user_row = $db->sql_fetchrow($result))
	{
		$user_cache[$user_row['session_user_id']]['online'] = (time() - $update_time < $user_row['online_time'] && (($user_row['viewonline']) || $auth->acl_get('u_viewonline'))) ? true : false;
	}
	$db->sql_freeresult($result);	
	

	$temp_url = '';

	if ($poster_id != ANONYMOUS)
	{
		$temp_url = append_sid("profile.$phpEx?mode=viewprofile&amp;" . POST_USERS_URL . "=$poster_id");
		$profile_img = '<a href="' . $temp_url . '"><img src="' . $images['icon_profile'] . '" alt="' . $lang['Read_profile'] . '" title="' . $lang['Read_profile'] . '" border="0" /></a>';
		$profile = '<a href="' . $temp_url . '">' . $lang['Read_profile'] . '</a>';

		$temp_url = append_sid("privmsg.$phpEx?mode=post&amp;" . POST_USERS_URL . "=$poster_id");
		$pm_img = '<a href="' . $temp_url . '"><img src="' . $images['icon_pm'] . '" alt="' . $lang['Send_private_message'] . '" title="' . $lang['Send_private_message'] . '" border="0" /></a>';
		$pm = '<li class="' . 'pm-icon' . '">' . '<a href="' . $temp_url . '" title=' . '"' . $lang['Send_private_message'] . '">' . '</a></li>';

		if ( !empty($postrow[$i]['user_viewemail']) || $is_auth['auth_mod'] )
		{
			$email_uri = ( $board_config['board_email_form'] ) ? append_sid("profile.$phpEx?mode=email&amp;" . POST_USERS_URL .'=' . $poster_id) : 'mailto:' . $postrow[$i]['user_email'];

			$email_img = '<a href="' . $email_uri . '"><img src="' . $images['icon_email'] . '" alt="' . $lang['Send_email'] . '" title="' . $lang['Send_email'] . '" border="0" /></a>';
			$email = '<li class="' . 'email-icon' . '">' . '<a href="' . $email_uri . '" title=' . '"' . $lang['Send_email'] . '">' . '</a></li>';
		}
		else
		{
			$email_img = '';
			$email = '';
		}

		$www_img = ( $postrow[$i]['user_website'] ) ? '<a href="' . $postrow[$i]['user_website'] . '" target="_userwww"><img src="' . $images['icon_www'] . '" alt="' . $lang['Visit_website'] . '" title="' . $lang['Visit_website'] . '" border="0" /></a>' : '';
		$www = ( $postrow[$i]['user_website'] ) ? '<li class="' . 'web-icon' . '">' . '<a href="' . $postrow[$i]['user_website'] . '" title=' . '"' . $lang['Visit_website'] . '">' . '</a></li>' : '';

		if ( !empty($postrow[$i]['user_icq']) )
		{
			$icq_status_img = '<a href="http://wwp.icq.com/' . $postrow[$i]['user_icq'] . '#pager"><img src="http://web.icq.com/whitepages/online?icq=' . $postrow[$i]['user_icq'] . '&img=5" width="18" height="18" border="0" /></a>';
			$icq_img = '<a href="http://wwp.icq.com/scripts/search.dll?to=' . $postrow[$i]['user_icq'] . '"><img src="' . $images['icon_icq'] . '" alt="' . $lang['ICQ'] . '" title="' . $lang['ICQ'] . '" border="0" /></a>';
			$icq = '<li class="' . 'icq-icon' . '">' . '<a href="http://wwp.icq.com/scripts/search.dll?to=' . $postrow[$i]['user_icq'] . '" title=' . '"' . $lang['ICQ'] . '">' . '</a></li>';
		}
		else
		{
			$icq_status_img = '';
			$icq_img = '';
			$icq = '';
		}

		$aim_img = ( $postrow[$i]['user_aim'] ) ? '<a href="aim:goim?screenname=' . $postrow[$i]['user_aim'] . '&amp;message=Hello+Are+you+there?"><img src="' . $images['icon_aim'] . '" alt="' . $lang['AIM'] . '" title="' . $lang['AIM'] . '" border="0" /></a>' : '';
		$aim = ( $postrow[$i]['user_aim'] ) ? '<li class="' . 'aim-icon' . '">' . '<a href="aim:goim?screenname=' . $postrow[$i]['user_aim'] . '&amp;message=Hello+Are+you+there?" title=' . '"' . $lang['AIM'] . '">' . '</a></li>' : '';

		$temp_url = append_sid("profile.$phpEx?mode=viewprofile&amp;" . POST_USERS_URL . "=$poster_id");
		$msn_img = ( $postrow[$i]['user_msnm'] ) ? '<a href="' . $temp_url . '"><img src="' . $images['icon_msnm'] . '" alt="' . $lang['MSNM'] . '" title="' . $lang['MSNM'] . '" border="0" /></a>' : '';
		$msn = ( $postrow[$i]['user_msnm'] ) ? '<li class="' . 'msnm-icon' . '">' . '<a href="' . $temp_url . '" title=' . '"' . $lang['MSNM'] . '">' . '</a></li>' : '';

		$yim_img = ( $postrow[$i]['user_yim'] ) ? '<a href="http://edit.yahoo.com/config/send_webmesg?.target=' . $postrow[$i]['user_yim'] . '&amp;.src=pg"><img src="' . $images['icon_yim'] . '" alt="' . $lang['YIM'] . '" title="' . $lang['YIM'] . '" border="0" /></a>' : '';
		$yim = ( $postrow[$i]['user_yim'] ) ? '<li class="' . 'yahoo-icon' . '">' . '<a href="http://edit.yahoo.com/config/send_webmesg?.target=' . $postrow[$i]['user_yim'] . '&amp;.src=pg" title=' . '"' . $lang['YIM'] . '">' . '</a></li>' : '';
	}
	else
	{
		$profile_img = '';
		$profile = '';
		$pm_img = '';
		$pm = '';
		$email_img = '';
		$email = '';
		$www_img = '';
		$www = '';
		$icq_status_img = '';
		$icq_img = '';
		$icq = '';
		$aim_img = '';
		$aim = '';
		$msn_img = '';
		$msn = '';
		$yim_img = '';
		$yim = '';
	}

	$temp_url = append_sid("posting.$phpEx?mode=quote&amp;" . POST_POST_URL . "=" . $postrow[$i]['post_id']);
	$quote_img = '<a href="' . $temp_url . '"><img src="' . $images['icon_quote'] . '" alt="' . $lang['Reply_with_quote'] . '" title="' . $lang['Reply_with_quote'] . '" border="0" /></a>';
	$quote = '<li class="' . 'quote-icon' . '">' . '<a href="' . $temp_url . '" title=' . '"' . $lang['Reply_with_quote'] . '">' . '</a></li>';

	$temp_url = append_sid("search.$phpEx?search_author=" . urlencode($postrow[$i]['username']) . "&amp;showresults=posts");
	$search_img = '<a href="' . $temp_url . '"><img src="' . $images['icon_search'] . '" alt="' . sprintf($lang['Search_user_posts'], $postrow[$i]['username']) . '" title="' . sprintf($lang['Search_user_posts'], $postrow[$i]['username']) . '" border="0" /></a>';
	$search = '<a href="' . $temp_url . '">' . sprintf($lang['Search_user_posts'], $postrow[$i]['username']) . '</a>';

	if ( ( $userdata['user_id'] == $poster_id && $is_auth['auth_edit'] ) || $is_auth['auth_mod'] )
	{
		$temp_url = append_sid("posting.$phpEx?mode=editpost&amp;" . POST_POST_URL . "=" . $postrow[$i]['post_id']);
		$edit_img = '<a href="' . $temp_url . '"><img src="' . $images['icon_edit'] . '" alt="' . $lang['Edit_delete_post'] . '" title="' . $lang['Edit_delete_post'] . '" border="0" /></a>';
		$edit = '<li class="' . 'edit-icon' . '">' . '<a href="' . $temp_url . '" title=' . '"' . $lang['Edit_delete_post'] . '">' . '</a></li>';
	}
	else
	{
		$edit_img = '';
		$edit = '';
	}

	if ( $is_auth['auth_mod'] )
	{
		$temp_url = "modcp.$phpEx?mode=ip&amp;" . POST_POST_URL . "=" . $postrow[$i]['post_id'] . "&amp;" . POST_TOPIC_URL . "=" . $topic_id . "&amp;sid=" . $userdata['session_id'];
		$ip_img = '<a href="' . $temp_url . '"><img src="' . $images['icon_ip'] . '" alt="' . $lang['View_IP'] . '" title="' . $lang['View_IP'] . '" border="0" /></a>';
		$ip = '<li class="' . 'info-icon' . '">' . '<a href="' . $temp_url . '" title=' . '"' . $lang['View_IP'] . '">' . '</a></li>';

		$temp_url = "posting.$phpEx?mode=delete&amp;" . POST_POST_URL . "=" . $postrow[$i]['post_id'] . "&amp;sid=" . $userdata['session_id'];
		$delpost_img = '<a href="' . $temp_url . '"><img src="' . $images['icon_delpost'] . '" alt="' . $lang['Delete_post'] . '" title="' . $lang['Delete_post'] . '" border="0" /></a>';
		$delpost = '<li class="' . 'delete-icon' . '">' . '<a href="' . $temp_url . '" title=' . '"' . $lang['Delete_post'] . '">' . '</a></li>';
	}
	else
	{
		$ip_img = '';
		$ip = '';

		if ( $userdata['user_id'] == $poster_id && $is_auth['auth_delete'] && $forum_topic_data['topic_last_post_id'] == $postrow[$i]['post_id'] )
		{
			$temp_url = "posting.$phpEx?mode=delete&amp;" . POST_POST_URL . "=" . $postrow[$i]['post_id'] . "&amp;sid=" . $userdata['session_id'];
			$delpost_img = '<a href="' . $temp_url . '"><img src="' . $images['icon_delpost'] . '" alt="' . $lang['Delete_post'] . '" title="' . $lang['Delete_post'] . '" border="0" /></a>';
			$delpost = '<li class="' . 'delete-icon' . '">' . '<a href="' . $temp_url . '" title=' . '"' . $lang['Delete_post'] . '">' . '</a></li>';
		}
		else
		{
			$delpost_img = '';
			$delpost = '';
		}
	}

	$post_subject = ( (!empty($postrow[$i]['post_subject'])) ) ? $postrow[$i]['post_subject'] : '';

	$message = $postrow[$i]['post_text'];
	$bbcode_uid = $postrow[$i]['bbcode_uid'];

	$user_sig = ( $postrow[$i]['enable_sig'] && (!empty($postrow[$i]['user_sig']))  && $board_config['allow_sig'] ) ? $postrow[$i]['user_sig'] : '';
	$user_sig_bbcode_uid = $postrow[$i]['user_sig_bbcode_uid'];
	
	
	//
	// Note! The order used for parsing the message _is_ important, moving things around could break any
	// output
	//

	//
	// If the board has HTML off but the post has HTML
	// on then we process it, else leave it alone
	//
	if ( !$board_config['allow_html'] || !$userdata['user_allowhtml'])
	{
		if (!empty($user_sig))
		{
			$user_sig = preg_replace('#(<)([\/]?.*?)(>)#is', "&lt;\\2&gt;", $user_sig);
		}

		if ( $postrow[$i]['enable_html'] )
		{
			$message = preg_replace('#(<)([\/]?.*?)(>)#is', "&lt;\\2&gt;", $message);
		}
	}

	//
	// Parse message and/or sig for BBCode if reqd
	//
	if (!empty($user_sig) && !empty($user_sig_bbcode_uid))
	{
		$user_sig = ($board_config['allow_bbcode']) ? bbencode_second_pass($user_sig, $user_sig_bbcode_uid) : preg_replace("/\:$user_sig_bbcode_uid/si", '', $user_sig);
	}

	if (!empty($bbcode_uid))
	{
		$message = ($board_config['allow_bbcode']) ? bbencode_second_pass($message, $bbcode_uid) : preg_replace("/\:$bbcode_uid/si", '', $message);
	}

	if (!empty($user_sig) )
	{
		$user_sig = make_clickable($user_sig);
	}
	$message = make_clickable($message);

	//
	// Parse smilies
	//
	if ( $board_config['allow_smilies'] )
	{
		if ( $postrow[$i]['user_allowsmile'] && !empty($user_sig) )
		{
			$user_sig = smilies_pass($user_sig);
		}

		if ( $postrow[$i]['enable_smilies'] )
		{
			$message = smilies_pass($message);
		}
	}

	//
	// Highlight active words (primarily for search)
	//
	if ($highlight_match)
	{
		// This has been back-ported from 3.0 CVS
		$message = preg_replace('#(?!<.*)(?<!\w)(' . $highlight_match . ')(?!\w|[^<>]*>)#i', '<b style="color:#'.$theme['fontcolor3'].'">\1</b>', $message);
	}

	//
	// Replace naughty words
	//
	if (count($orig_word))
	{
		$post_subject = preg_replace($orig_word, $replacement_word, $post_subject);

		if ($user_sig != '')
		{
			$user_sig = str_replace('\"', '"', substr(@preg_replace('#(\>(((?>([^><]+|(?R)))*)\<))#se', "@preg_replace(\$orig_word, \$replacement_word, '\\0')", '>' . $user_sig . '<'), 1, -1));
		}

		$message = str_replace('\"', '"', substr(@preg_replace('#(\>(((?>([^><]+|(?R)))*)\<))#se', "@preg_replace(\$orig_word, \$replacement_word, '\\0')", '>' . $message . '<'), 1, -1));
	}

	//
	// Replace newlines (we use this rather than nl2br because
	// till recently it wasn't XHTML compliant)
	//
	if (!empty($user_sig) )
	{
		$user_sig = '<div class="signature">' . str_replace("\n", "\n<br />\n", $user_sig) . '</div>';
	}

	$message = str_replace("\n", "\n<br />\n", $message);

	//
	// Editing information
	//
	$l_bumped_by = $l_edited_by = $l_deleted_by = $l_deleted_message = '';	
	
	if ( $postrow[$i]['post_edit_count'] )
	{
		$l_edit_time_total = ( $postrow[$i]['post_edit_count'] == 1 ) ? $lang['Edited_time_total'] : $lang['Edited_times_total'];

		$l_edited_by = '<br /><br />' . sprintf($l_edit_time_total, $poster, create_date($board_config['default_dateformat'], $postrow[$i]['post_edit_time'], $board_config['board_timezone']), $postrow[$i]['post_edit_count']);
	}
	
	
	$edit_allowed = ($user->data['user_active'] && ($is_auth['auth_edit'] || (
		!$s_cannot_edit &&
		!$s_cannot_edit_time &&
		!$s_cannot_edit_locked
	)));

	$quote_allowed = ($forum_topic_data['topic_status'] != ITEM_LOCKED && ($user->data['user_id'] == ANONYMOUS || $auth->acl_get('f_reply', $forum_id))
	);

	// Only display the quote button if the post is quotable.  Posts not approved are not quotable.
	$quote_allowed = ($quote_allowed) ? true : false;

	$delete_allowed = ($user->data['user_active'] && (
		($is_auth['auth_delete'] || ($auth->acl_get('m_softdelete', $forum_id))) ||
		(!$s_cannot_delete && !$s_cannot_delete_lastpost && !$s_cannot_delete_time && !$s_cannot_delete_locked)
	));

	$softdelete_allowed = (($auth->acl_get('m_softdelete', $forum_id) ||
		($auth->acl_get('f_softdelete', $forum_id) && $user->data['user_id'] == $poster_id)) && ($row['post_visibility'] != ITEM_DELETED));

	$permanent_delete_allowed = ($auth->acl_get('m_delete', $forum_id) ||
		($is_auth['auth_delete'] && $user->data['user_id'] == $poster_id));

	// Can this user receive a Private Message?
	$can_receive_pm = (
		// They must be a "normal" user
		$user_cache[$poster_id]['user_type'] != USER_IGNORE &&

		// They must not be deactivated by the administrator
		$user_cache[$poster_id]['user_type'] != USER_INACTIVE &&

		// They must be able to read PMs
		in_array($poster_id, $can_receive_pm_list) &&

		// They must not be permanently banned
		!in_array($poster_id, $permanently_banned_users) &&

		// They must allow users to contact via PM
		(($auth->acl_gets('a_', 'm_') || $auth->acl_getf_global('m_')) || $user_cache[$poster_id]['allow_pm'])
	);

	$u_pm = '';

	if ($auth->acl_get('u_sendpm') && $can_receive_pm)
	{
		$u_pm = append_sid("{$phpbb_root_path}profile.$phpEx?i=pm&amp;mode=compose&amp;action=quotepost&amp;p=" . $postrow[$i]['post_id']);
	}	

	
	// 
	// Username in colouring 
	// 
	if($postrow[$i]['user_level'] == 1) // Admin 
	{
		$glowing_color = '#' . $user->theme['fontcolor3']; 
	}
		else if($postrow[$i]['user_level'] == 2) // Moderator 
	{
		$glowing_color = '#' . $user->theme['fontcolor2']; 
	} 
		else // normal 
	{
		$glowing_color = '#' . $user->theme['fontcolor1']; 
	}
	
	//
	// Again this will be handled by the templating
	// code at some point
	//
	$row_color = ( !($i % 2) ) ? $user->theme['td_color1'] : $user->theme['td_color2'];
	$row_class = ( !($i % 2) ) ? $user->theme['td_class1'] : $user->theme['td_class2'];

	$template->assign_block_vars('postrow', array(
		'ROW_COLOR' => '#' . $row_color,
		'ROW_CLASS' => $row_class,
		
		'S_ROW_COUNT'		=> (!empty($i)) ? $i : false,
		'REMAINDER'			=> $i,
		
		'POST_ID'			=> $postrow[$i]['post_id'],
		'POST_NUMBER'		=> $i + $start + 1,
		
		'POST_AUTHOR_FULL'		=> $user_cache[$poster_id]['author_full'],
		'POST_AUTHOR_COLOUR'	=> $user_cache[$poster_id]['author_colour'],
		'POST_AUTHOR'			=> $user_cache[$poster_id]['author_username'],
		'U_POST_AUTHOR'			=> $user_cache[$poster_id]['author_profile'],
	
		'POSTER_ID' => $postrow[$i]['user_id'],		
		'POSTER_NAME' => $poster,
		'POSTER_RANK' => $poster_rank,
		
		'RANK_TITLE'		=> $user_cache[$poster_id]['rank_title'],
		'RANK_IMG'			=> $user_cache[$poster_id]['rank_image'],
		'RANK_IMAGE' 		=> $rank_image,		
		'RANK_IMG_SRC'		=> $user_cache[$poster_id]['rank_image_src'],
		//'POSTER_JOINED'		=> $user_cache[$poster_id]['joined'],
		'POSTER_JOINED' 	=> $poster_joined,
		'POSTER_POSTS' 		=> $poster_posts,		
		//'POSTER_POSTS'		=> $user_cache[$poster_id]['posts'],
		'POSTER_AVATAR'		=> $user_cache[$poster_id]['avatar'],
		//'POSTER_AVATAR' 	=> $poster_avatar,		
		'POSTER_WARNINGS'	=> $auth->acl_get('m_warn') ? $user_cache[$poster_id]['warnings'] : '',
		'POSTER_AGE'		=> $user_cache[$poster_id]['age'],
		'CONTACT_USER'		=> $user_cache[$poster_id]['contact_user'],		
				
		'POSTER_FROM' 		=> $poster_from,
		'POST_DATE' 		=> $post_date,	
		//'POST_DATE'			=> $user->format_date($post_date, false, ($view == 'print') ? true : false),
		'POST_SUBJECT' 		=> $post_subject, //$row['post_subject'],
		'MESSAGE' 			=> $message,
		//'SIGNATURE'			=> ($user_cache[$poster_id]['enable_sig']) ? $user_cache[$poster_id]['sig'] : '',
		'SIGNATURE' 		=> $user_sig,		
		'EDITED_MESSAGE'	=> $l_edited_by,
		'EDIT_REASON'		=> $rowset_data['post_edit_reason'],
		'DELETED_MESSAGE'	=> $l_deleted_by,
		'DELETE_REASON'		=> $rowset_data['post_delete_reason'],
		'BUMPED_MESSAGE'	=> $l_bumped_by,
		
		'S_POST_HIDDEN'		=> (isset($rowset_data['hide_post'])) ? $rowset_data['hide_post'] : false,		
		'S_IGNORE_POST'		=> false,		
		'S_FIRST_UNREAD'	=> $s_first_unread,
		'S_PROFILE_FIELD1'	=> false,
		
		'GLOWING_COLOR' => $glowing_color, // Username in colouring
		'MINI_POST_IMG' => $mini_post_img,
		'MINI_POST_IMG_SRC'	=> ($post_unread) ? $user->img('icon_post_target_unread', 'UNREAD_POST', '', '', 'src') : $user->img('icon_post_target', 'POST', '', '', 'src'),
	
		'POST_ICON_IMG'			=> $post_img,
		'POST_ICON_IMG_SRC' 	=> $user->img($post_img, $post_alt, '27', '', 'src'),			
		'POST_ICON_IMG_WIDTH'	=> $user->img($post_img, $post_alt, '27', '', 'width'),
		'POST_ICON_IMG_HEIGHT'	=> $user->img($post_img, $post_alt, '27', '', 'height'),
		'POST_ICON_IMG_ALT' 	=> $user->img($post_img, $post_alt, '27', '', 'alt'),
		'POST_ICON_FULL_TAG' 	=> $user->img($post_img, $post_alt, '27', '', 'full_tag'),	
		'POST_ICON_IMG_HTML' 	=> $user->img($post_img, $post_alt, '27', '', 'html'),			
	
		'ONLINE_IMG'			=> ($poster_id == ANONYMOUS ? '' : (($user_cache[$poster_id]['online']) ? $user->img('icon_user_online', 'ONLINE') : $user->img('icon_user_offline', 'OFFLINE'))),
		'S_ONLINE'				=> ($poster_id == ANONYMOUS ? false : (($user_cache[$poster_id]['online']) ? true : false)),
			
		'U_EDIT'			=> ($edit_allowed) ? append_sid("{$phpbb_root_path}posting.$phpEx?mode=edit&amp;f=$forum_id&amp;p={$postrow[$i]['post_id']}") : '',
		'U_QUOTE'			=> ($quote_allowed) ? append_sid("{$phpbb_root_path}posting.$phpEx?mode=quote&amp;f=$forum_id&amp;p={$postrow[$i]['post_id']}") : '',
		'U_INFO'			=> ($auth->acl_get('m_info', $forum_id)) ? append_sid("{$phpbb_root_path}modcp.$phpEx?i=main&amp;mode=post_details&amp;f=$forum_id&amp;p=" . $postrow[$i]['post_id'], true, $user->session_id) : '',
		'U_DELETE'			=> ($delete_allowed) ? append_sid("{$phpbb_root_path}posting.$phpEx?mode=" . (($softdelete_allowed) ? 'soft_delete' : 'delete') . "&amp;f=$forum_id&amp;p={$postrow[$i]['post_id']}") : '',
		
		'U_SEARCH'		=> $user_cache[$poster_id]['search'],
		'U_PM'			=> $u_pm,
		'U_EMAIL'		=> $user_cache[$poster_id]['email'],
		'U_JABBER'		=> $user_cache[$poster_id]['jabber'],		
		
		'S_HAS_ATTACHMENTS'	=> (!empty($attachments[$postrow[$i]['post_id']])) ? true : false,
		'S_MULTIPLE_ATTACHMENTS'	=> !empty($attachments[$postrow[$i]['post_id']]) && count($attachments[$postrow[$i]['post_id']]) > 1,
		'S_POST_UNAPPROVED'	=> false,
		'S_POST_DELETED'	=> false,
		'L_POST_DELETED_MESSAGE'	=> $l_deleted_message,
		'S_POST_REPORTED'	=> false,
		'S_DISPLAY_NOTICE'	=> $display_notice && isset($row['post_attachment']),
		'S_FRIEND'			=> isset($row['friend']) ? true : false,
		'S_UNREAD_POST'		=> $post_unread,
		'S_FIRST_UNREAD'	=> $s_first_unread,
		'S_CUSTOM_FIELDS'	=> (isset($cp_row['row']) && count($cp_row['row'])) ? true : false,
		'S_TOPIC_POSTER'	=> ($forum_topic_data['topic_poster'] == $poster_id) ? true : false,
		'S_FIRST_POST'		=> ($forum_topic_data['topic_first_post_id'] == $postrow[$i]['post_id']) ? true : false,		
		
		
		'U_APPROVE_ACTION'	=> append_sid("{$phpbb_root_path}mcp.$phpEx", "i=queue&amp;p={$postrow[$i]['post_id']}&amp;f=$forum_id&amp;redirect=" . urlencode(str_replace('&amp;', '&', $viewtopic_url . '&amp;p=' . $postrow[$i]['post_id'] . '#p' . $postrow[$i]['post_id']))),
		'U_REPORT'			=> '',
		'U_MCP_REPORT'		=> ($auth->acl_get('m_report', $forum_id)) ? append_sid("{$phpbb_root_path}modcp.$phpEx?i=reports&amp;mode=report_details&amp;f=" . $forum_id . '&amp;p=' . $postrow[$i]['post_id'], true, $user->session_id) : '',
		'U_MCP_APPROVE'		=> ($auth->acl_get('m_approve', $forum_id)) ? append_sid("{$phpbb_root_path}modcp.$phpEx?i=queue&amp;mode=approve_details&amp;f=" . $forum_id . '&amp;p=' . $postrow[$i]['post_id'], true, $user->session_id) : '',
		'U_MCP_RESTORE'		=> ($auth->acl_get('m_approve', $forum_id)) ? append_sid("{$phpbb_root_path}modcp.$phpEx?i=queue&amp;mode=" . (($forum_topic_data['topic_visibility'] != ITEM_DELETED) ? 'deleted_posts' : 'deleted_topics') . '&amp;f=' . $forum_id . '&amp;p=' . $postrow[$i]['post_id'], true, $user->session_id) : '',
		'U_MINI_POST'		=> append_sid("{$phpbb_root_path}viewtopic.$phpEx?p=" . $postrow[$i]['post_id']) . "#p" . $postrow[$i]['post_id'],
		'U_NEXT_POST_ID'	=> ($i < $i_total && isset($rowset[$post_list[$i + 1]])) ? $rowset[$post_list[$i + 1]]['post_id'] : $postrow[$i]['post_id'],
		'U_PREV_POST_ID'	=> ($i < $i_total && isset($rowset[$post_list[$i - 1]])) ? $rowset[$post_list[$i - 1]]['post_id'] : $postrow[$i]['post_id'],
		'U_NOTES'			=> ($auth->acl_getf_global('m_')) ? append_sid("{$phpbb_root_path}modcp.$phpEx?i=notes&amp;mode=user_notes&amp;u=" . $poster_id, true, $user->session_id) : '',
		'U_WARN'			=> ($auth->acl_get('m_warn') && $poster_id != $user->data['user_id'] && $poster_id != ANONYMOUS) ? append_sid("{$phpbb_root_path}modcp.$phpEx?i=warn&amp;mode=warn_post&amp;f=" . $forum_id . '&amp;p=' . $postrow[$i]['post_id'], true, $user->session_id) : '',
						
		'PROFILE_IMG' => $profile_img,
		'PROFILE' => $profile,
		'SEARCH_IMG' => $search_img,
		'SEARCH' => $search,
		'PM_IMG' => $pm_img,
		'PM' => $pm,
		'EMAIL_IMG' => $email_img,
		'EMAIL' => $email,
		'WWW_IMG' => $www_img,
		'WWW' => $www,
		'ICQ_STATUS_IMG' => $icq_status_img,
		'ICQ_IMG' => $icq_img,
		'ICQ' => $icq,
		'AIM_IMG' => $aim_img,
		'AIM' => $aim,
		'MSN_IMG' => $msn_img,
		'MSN' => $msn,
		'YIM_IMG' => $yim_img,
		'YIM' => $yim,
		'EDIT_IMG' => $edit_img,
		'EDIT' => $edit,
		'QUOTE_IMG' => $quote_img,
		'QUOTE' => $quote,
		'IP_IMG' => $ip_img,
		'IP' => $ip,
		'DELETE_IMG' => $delpost_img,
		'DELETE' => $delpost,
		
		'U_VIEWPROFILE' => append_sid("profile.$phpEx?mode=viewprofile&amp;" . POST_USERS_URL . "=$poster_id"),

		'L_MINI_POST_ALT' => $mini_post_alt,

		'U_MINI_POST' => $mini_post_url,
		'U_POST_ID' => $postrow[$i]['post_id'])
	);
	
	$template->assign_vars(array(
		'S_ROW_COUNT'		=> (!empty($i)) ? $i : false,
		'REMAINDER'			=> $i,
	));	
}

$cp_row = array();
	
$cp_row['blockrow'][] = array(
	'PROFILE_FIELD_IDENT'		=> 'ident',
	'PROFILE_FIELD_VALUE'		=> 'value',
	'PROFILE_FIELD_VALUE_RAW'	=> 'value_raw',
	'PROFILE_FIELD_CONTACT'		=> isset($user_cache['user_website']) ? $user_cache['user_website'] : $user->data['user_website'],
	'PROFILE_FIELD_DESC'		=> 'field_desc',
	'PROFILE_FIELD_TYPE'		=> 'field_type',
	'PROFILE_FIELD_NAME'		=> isset($user_cache['user_lang']) ? $user_cache['user_lang'] : $user->data['user_lang'],
	'PROFILE_FIELD_EXPLAIN'		=> $user->lang('lang_explain'),

	'S_PROFILE_CONTACT'			=> isset($user_cache['user_email']) ? $user_cache['user_email'] : $user->data['user_email'],
	//'S_PROFILE_' . strtoupper($ident)	=> true,
	'S_ROW_COUNT'		=> 4,	
);
	
//

$contact_fields = array(
	array(
		'ID'		=> 'pm',
		'NAME' 		=> $user->lang['Send_private_message'],
		'U_CONTACT'	=> $u_pm,
	),
	array(
		'ID'		=> 'email',
		'NAME'		=> $user->lang['Send_email'],
		'U_CONTACT'	=> $user_cache[$poster_id]['email'],
	),
	array(
		'ID'		=> 'jabber',
		'NAME'		=> $user->lang('JABBER'),
		'U_CONTACT'	=> $user_cache[$poster_id]['jabber'],
	),
);
	
foreach ($contact_fields as $field)
{
	if ($field['U_CONTACT'])
	{
		$template->assign_block_vars('postrow.contact', $field);
	}
}

if (!empty($cp_row['blockrow']))
{
	foreach ($cp_row['blockrow'] as $field_data)
	{
		$template->assign_block_vars('postrow.custom_fields', $field_data);
		if ($field_data['S_PROFILE_CONTACT'])
		{
			$template->assign_block_vars('postrow.contact', array(
				'ID'		=> $field_data['PROFILE_FIELD_IDENT'],
				'NAME'		=> $field_data['PROFILE_FIELD_NAME'],
				'U_CONTACT'	=> $field_data['PROFILE_FIELD_CONTACT'],
			));
		}
	}
}

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

$template->pparse('body');

include($phpbb_root_path . 'includes/page_tail.'.$phpEx);

?>