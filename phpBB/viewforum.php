<?php
/***************************************************************************
 *
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id$
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
 *
 ***************************************************************************/
$phpbb_root_path = "./";
include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'common.'.$phpEx);

$pagetype = "viewforum";
$page_title = "View Forum - $forum_name";

//
// Start initial var setup
//
if( isset($HTTP_GET_VARS[POST_FORUM_URL]) || isset($HTTP_POST_VARS[POST_FORUM_URL]) )
{
	$forum_id = (isset($HTTP_GET_VARS[POST_FORUM_URL])) ? $HTTP_GET_VARS[POST_FORUM_URL] : $HTTP_POST_VARS[POST_FORUM_URL];
}
else
{
	$forum_id = "";
}

$start = (isset($HTTP_GET_VARS['start'])) ? $HTTP_GET_VARS['start'] : 0;
//
// End initial var setup
//

//
// Start session management
//
$userdata = session_pagestart($user_ip, $forum_id, $session_length);
init_userprefs($userdata);
//
// End session management
//

//
// Check if the user has actually sent a forum ID with his/her request
// If not give them a nice error page.
//
if(isset($forum_id))
{
	$sql = "SELECT forum_name, forum_topics, auth_view, auth_read, auth_post, auth_reply, auth_edit, auth_delete, auth_votecreate, auth_vote, prune_enable, prune_next 
		FROM " . FORUMS_TABLE . " 
		WHERE forum_id = $forum_id";
	if(!$result = $db->sql_query($sql))
	{
		message_die(GENERAL_ERROR, "Couldn't obtain forums information.", "", __LINE__, __FILE__, $sql);
	}
}
else
{
	message_die(GENERAL_MESSAGE, $lang['Reached_on_error']);
}

//
// If the query doesn't return any rows this isn't a valid forum. Inform
// the user.
//
if(!$total_rows = $db->sql_numrows($result))
{
	message_die(GENERAL_MESSAGE, $lang['Forum_not_exist']);
}
$forum_row = $db->sql_fetchrow($result);

$forum_name = stripslashes($forum_row['forum_name']);

//
// Start auth check
//
$is_auth = auth(AUTH_ALL, $forum_id, $userdata, $forum_row);

if(!$is_auth['auth_read'] || !$is_auth['auth_view'])
{
	//
	// The user is not authed to read this forum ...
	//
	$msg = $lang['Sorry_auth'] . $is_auth['auth_read_type'] . $lang['can_read'] . $lang['this_forum'];

	message_die(GENERAL_MESSAGE, $msg);
}
//
// End of auth check
//

//
// Do the forum Prune
//
if( ( $is_auth['auth_mod'] || $is_auth['auth_admin'] ) && $board_config['prune_enable'] )
{
	if( $forum_row['prune_next'] < time() && $forum_row['prune_enable'] )
	{
		include($phpbb_root_path . 'includes/prune.php');
		auto_prune($forum_id);
	}
}
//
// End of forum prune
//

//
// Obtain list of moderators of this forum
//
$sql = "SELECT g.group_name, g.group_id, g.group_single_user, ug.user_id  
	FROM " . GROUPS_TABLE . " g, " . USER_GROUP_TABLE . " ug, " . AUTH_ACCESS_TABLE . " aa 
	WHERE aa.forum_id = $forum_id 
		AND aa.auth_mod = " . TRUE . " 
		AND g.group_id = aa.group_id 
		AND ug.group_id = g.group_id";
if(!$result_mods = $db->sql_query($sql))
{
	message_die(GENERAL_ERROR, "Couldn't obtain forums information.", "", __LINE__, __FILE__, $sql);
}

if( $total_mods = $db->sql_numrows($result_mods) )
{
	$mods_rowset = $db->sql_fetchrowset($result_mods);

	$forum_moderators = "";

	for($i = 0; $i < $total_mods; $i++)
	{
		if( !strstr($forum_moderators, $mods_rowset[$i]['group_name']) )
		{
			if($i > 0)
			{
				$forum_moderators .= ", ";
			}

			if($mods_rowset[$i]['group_single_user'])
			{
				$mod_url = "profile.$phpEx?mode=viewprofile&" . POST_USERS_URL . "=" . $mods_rowset[$i]['user_id'];
			}
			else
			{
				$mod_url = "groupcp.$phpEx?" . POST_GROUPS_URL . "=" . $mods_rowset[$i]['group_id'];
			}

			$forum_moderators .= "<a href=\"" . append_sid($mod_url) . "\">" . $mods_rowset[$i]['group_name'] ."</a>";
		}
	}
}
else
{
	$forum_moderators = $lang['None'];
}

//
// Generate a 'Show posts in previous x days' select box. If the postdays var is POSTed
// then get it's value, find the number of topics with dates newer than it (to properly
// handle pagination) and alter the main query
//
$previous_days = array(0, 1, 7, 14, 30, 90, 180, 364);
$previous_days_text = array($lang['All_Topics'], "1 " . $lang['Day'], "7 " . $lang['Days'], "2 " . $lang['Weeks'], "1 " . $lang['Month'], "3 ". $lang['Months'], "6 " . $lang['Months'], "1 " . $lang['Year']);

if(!empty($HTTP_POST_VARS['postdays']) || !empty($HTTP_GET_VARS['postdays']))
{
	$post_days = (!empty($HTTP_POST_VARS['postdays'])) ? $HTTP_POST_VARS['postdays'] : $HTTP_GET_VARS['postdays'];
	$min_post_time = time() - ($post_days * 86400);

	$sql = "SELECT COUNT(t.topic_id) AS forum_topics
		FROM " . TOPICS_TABLE . " t, " . POSTS_TABLE . " p 
		WHERE t.forum_id = $forum_id 
			AND p.post_id = t.topic_last_post_id 
			AND p.post_time > $min_post_time 
			OR t.topic_type = " . POST_ANNOUNCE;

	if(!$result = $db->sql_query($sql))
	{
		message_die(GENERAL_ERROR, "Couldn't obtain limited topics count information", "", __LINE__, __FILE__, $sql);
	}
	list($topics_count) = $db->sql_fetchrow($result);
	
	$limit_posts_time = "AND ( p.post_time > $min_post_time OR t.topic_type = " . POST_ANNOUNCE . " ) ";

	if(!empty($HTTP_POST_VARS['postdays']))
	{
		$start = 0;
	}
}
else
{
	$topics_count = $forum_row['forum_topics'];

	$limit_posts_time = "";
	$post_days = 0;
}

$select_post_days = "<select name=\"postdays\">";
for($i = 0; $i < count($previous_days); $i++)
{
	$selected = ($post_days == $previous_days[$i]) ? " selected" : "";
	$select_post_days .= "<option value=\"".$previous_days[$i]."\"$selected>".$previous_days_text[$i]."</option>";
}
$select_post_days .= "</select>";

//
// Grab all the basic data (all topics except global announcements) 
// for this forum
//
$sql = "SELECT t.*, u.username, u.user_id, u2.username as user2, u2.user_id as id2, p.post_time, p.post_username
	FROM " . TOPICS_TABLE . " t, " . USERS_TABLE . " u, " . POSTS_TABLE . " p, " . USERS_TABLE . " u2
	WHERE t.forum_id = $forum_id
		AND t.topic_poster = u.user_id
		AND p.post_id = t.topic_last_post_id
		AND p.poster_id = u2.user_id
		AND t.topic_type <> " . POST_GLOBAL_ANNOUNCE . "
		$limit_posts_time
	ORDER BY t.topic_type DESC, p.post_time DESC
	LIMIT $start, ".$board_config['topics_per_page'];

if(!$t_result = $db->sql_query($sql))
{
   message_die(GENERAL_ERROR, "Couldn't obtain topic information", "", __LINE__, __FILE__, $sql);
}
$total_topics = $db->sql_numrows($t_result);

//
// Post URL generation for templating vars
//
$post_new_topic_url = append_sid("posting.$phpEx?mode=newtopic&" . POST_FORUM_URL . "=$forum_id");

$template->assign_vars(array(
	"L_DISPLAY_TOPICS" => $lang['Display_topics'], 

	"U_POST_NEW_TOPIC" => $post_new_topic_url,

	"S_SELECT_POST_DAYS" => $select_post_days,
	"S_POST_DAYS_ACTION" => append_sid("viewforum.$phpEx?" . POST_FORUM_URL . "=" . $forum_id . "&start=$start"))
);

//
// User authorisation levels output
//
$s_auth_can = $lang['You'] . " " . ( ($is_auth['auth_read']) ? $lang['can']  : $lang['cannot'] ) . " " . $lang['read_posts'] . "<br />";
$s_auth_can .= $lang['You'] . " " . ( ($is_auth['auth_post']) ? $lang['can'] : $lang['cannot'] ) . " " . $lang['post_topics'] . "<br />";
$s_auth_can .= $lang['You'] . " " . ( ($is_auth['auth_reply']) ? $lang['can'] : $lang['cannot'] ) . " " . $lang['reply_posts'] . "<br />";
$s_auth_can .= $lang['You'] . " " . ( ($is_auth['auth_edit']) ? $lang['can'] : $lang['cannot'] ) . " " . $lang['edit_posts'] . "<br />";
$s_auth_can .= $lang['You'] . " " . ( ($is_auth['auth_delete']) ? $lang['can'] : $lang['cannot'] ) . " " . $lang['delete_posts'] . "<br />";

if($is_auth['auth_mod'] || $userdata['user_level'] == ADMIN)
{
	$s_auth_can .= $lang['You'] . " " . $lang['can'] . " <a href=\"" . append_sid("modcp.$phpEx?" . POST_FORUM_URL . "=$forum_id") . "\">" . $lang['moderate_forum'] . "</a><br />";
}

//
// Dump out the page header and load viewforum template
//
include($phpbb_root_path . 'includes/page_header.'.$phpEx);

$template->set_filenames(array(
	"body" => "viewforum_body.tpl",
	"jumpbox" => "jumpbox.tpl")
);

$jumpbox = make_jumpbox();
$template->assign_vars(array(
	"JUMPBOX_LIST" => $jumpbox,
    "SELECT_NAME" => POST_FORUM_URL)
);
$template->assign_var_from_handle("JUMPBOX", "jumpbox");

$template->assign_vars(array(
	"FORUM_ID" => $forum_id,
	"FORUM_NAME" => $forum_name,
	"MODERATORS" => $forum_moderators,
	
	"IMG_POST" => $images['topic_new'],

	"S_AUTH_LIST" => $s_auth_can)
);
//
// End header
//

//
// Okay, lets dump out the page ...
//
if($total_topics)
{
	$topic_rowset = $db->sql_fetchrowset($t_result);

	for($i = 0; $i < $total_topics; $i++)
	{
		$topic_title = stripslashes($topic_rowset[$i]['topic_title']);

		$topic_type = $topic_rowset[$i]['topic_type'];

		if($topic_type == POST_ANNOUNCE)
		{
			$topic_type = $lang['Annoucement'] . " ";
		}
		else if($topic_type == POST_STICKY)
		{
			$topic_type = $lang['Sticky'] . " ";
		}
		else
		{
			$topic_type = "";
		}

		$topic_id = $topic_rowset[$i]['topic_id'];

		$replies = $topic_rowset[$i]['topic_replies'];

		if($replies > $board_config['posts_per_page'])
		{
			$goto_page = "&nbsp;&nbsp;&nbsp;(<img src=\"" . $images['icon_minipost'] . "\">" . $lang['Goto_page'] . ": ";

			$times = 1;
			for($j = 0; $j < $replies + 1; $j += $board_config['posts_per_page'])
			{
				if($times > 4)
				{
					if( $j + $board_config['posts_per_page'] >= $replies + 1 )
					{
						$goto_page .= " ... <a href=\"".append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=" . $topic_id . "&start=$j") . "\">$times</a>";
					}
				}
				else
				{
					if($times != 1)
					{
						$goto_page .= ", ";
					}
					$goto_page .= "<a href=\"" . append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=" . $topic_id . "&start=$j") . "\">$times</a>";
				}
				$times++;
			}
			$goto_page .= ")";
		}
		else
		{
			$goto_page = "";
		}

		if($topic_rowset[$i]['topic_status'] == TOPIC_LOCKED)
		{	
			$folder_image = "<img src=\"" . $images['folder_locked'] . "\" alt=\"Topic Locked\">";
		}
		else
		{

			if($topic_rowset[$i]['topic_type'] == POST_ANNOUNCE)
			{
				$folder = $images['folder_announce'];
				$folder_new = $images['folder_announce_new'];
			}
			else if($topic_rowset[$i]['topic_type'] == POST_STICKY)
			{
				$folder = $images['folder_sticky'];
				$folder_new = $images['folder_sticky_new'];
			}
			else
			{
				$folder = $images['folder'];
				$folder_new = $images['folder_new'];
			}

			if($userdata['session_start'] >= $userdata['session_time'] - 300)
			{
				$folder_image = ($topic_rowset[$i]['post_time'] > $userdata['session_last_visit']) ? "<img src=\"$folder_new\">" : "<img src=\"$folder\">";
			}
			else
			{
				$folder_image = ($topic_rowset[$i]['post_time'] > $userdata['session_time'] - 300) ? "<img src=\"$folder_new\">" : "<img src=\"$folder\">";
			}

		}
			
		$view_topic_url = append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=" . $topic_id . "&" . $replies);

		$topic_poster = stripslashes($topic_rowset[$i]['username']);
		$topic_poster_profile_url = append_sid("profile.$phpEx?mode=viewprofile&" . POST_USERS_URL . "=" . $topic_rowset[$i]['user_id']);

		$last_post_time = create_date($board_config['default_dateformat'], $topic_rowset[$i]['post_time'], $board_config['default_timezone']);

		if($topic_rowset[$i]['id2'] == ANONYMOUS && $topic_rowset[$i]['post_username'] != '')
		{
			$last_post_user = $topic_rowset[$i]['post_username'];
		}
		else
		{
			$last_post_user = $topic_rowset[$i]['user2'];
		}

		$last_post = $last_post_time . "<br />by ";
		$last_post .= "<a href=\"" . append_sid("profile.$phpEx?mode=viewprofile&" . POST_USERS_URL . "="  . $topic_rowset[$i]['id2']) . "\">" . $last_post_user . "</a>&nbsp;";
		$last_post .= "<a href=\"" . append_sid("viewtopic.$phpEx?"  . POST_POST_URL . "=" . $topic_rowset[$i]['topic_last_post_id']) . "#" . $topic_rowset[$i]['topic_last_post_id'] . "\"><img src=\"" . $images['icon_latest_reply'] . "\" width=\"20\" height=\"11\" border=\"0\" alt=\"View Latest Post\"></a>";

		$views = $topic_rowset[$i]['topic_views'];

		$template->assign_block_vars("topicrow", array(
			"FORUM_ID" => $forum_id,
			"TOPIC_ID" => $topic_id,
			"FOLDER" => $folder_image,
			"TOPIC_POSTER" => $topic_poster,
			"GOTO_PAGE" => $goto_page,
			"REPLIES" => $replies,
			"TOPIC_TITLE" => $topic_title,
			"TOPIC_TYPE" => $topic_type,
			"VIEWS" => $views,
			"LAST_POST" => $last_post,

			"U_VIEW_TOPIC" => $view_topic_url,
			"U_TOPIC_POSTER_PROFILE" => $topic_poster_profile_url)
		);
	}

	$template->assign_vars(array(
		"PAGINATION" => generate_pagination("viewforum.$phpEx?" . POST_FORUM_URL . "=$forum_id&postdays=$post_days", $topics_count, $board_config['topics_per_page'], $start),
		"ON_PAGE" => ( floor( $start / $board_config['topics_per_page'] ) + 1 ),
		"TOTAL_PAGES" => ceil( $topics_count / $board_config['topics_per_page'] ),

		"L_OF" => $lang['of'],
		"L_PAGE" => $lang['Page'],
		"L_GOTO_PAGE" => $lang['Goto_page'],
			
		"S_NO_TOPICS" => FALSE)
	);

}
else
{
	//
	// No topics
	//
	$template->assign_vars(array( 
		"L_NO_TOPICS" => $lang['No_topics_post_one'], 

		"S_NO_TOPICS" => TRUE)
	);

}

//
// Parse the page and print
//
$template->pparse("body");

//
// Page footer
//
include($phpbb_root_path . 'includes/page_tail.'.$phpEx);

?>