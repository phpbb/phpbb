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
include('extension.inc');
include('common.'.$phpEx);

$pagetype = "viewforum";
$page_title = "View Forum - $forum_name";

//
// Obtain which forum id is required
//
if(!isset($HTTP_GET_VARS['forum']) && !isset($HTTP_POST_VARS['forum']))  // For backward compatibility
{
	$forum_id = ($HTTP_GET_VARS[POST_FORUM_URL]) ? $HTTP_GET_VARS[POST_FORUM_URL] : $HTTP_POST_VARS[POST_FORUM_URL];
}
else
{
	$forum_id = ($HTTP_GET_VARS['forum']) ? $HTTP_GET_VARS['forum'] : $HTTP_POST_VARS['forum'];
}

$start = (isset($HTTP_GET_VARS['start'])) ? $HTTP_GET_VARS['start'] : 0;

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
	$sql = "SELECT f.forum_name, f.forum_topics, u.username, u.user_id, fa.* 
		FROM ".FORUMS_TABLE." f, ".FORUM_MODS_TABLE." fm, ".USERS_TABLE." u, ".AUTH_FORUMS_TABLE." fa 
		WHERE f.forum_id = $forum_id 
			AND fa.forum_id = f.forum_id 
			AND fm.forum_id = f.forum_id 
			AND u.user_id = fm.user_id";
}
else 
{
	error_die(GENERAL_ERROR, "You have reached this page in error, please go back and try again");
}

if(!$result = $db->sql_query($sql))
{
	error_die(SQL_QUERY, "Couldn't obtain forums information.", __LINE__, __FILE__);
}
// If the query doesn't return any rows this 
// isn't a valid forum. Inform the user.
if(!$total_rows = $db->sql_numrows($result)) 
{
   error_die(GENERAL_ERROR, "The forum you selected does not exist. Please go back and try again.");
}

$forum_row = $db->sql_fetchrowset($result);
if(!$forum_row)
{
	error_die(SQL_QUERY, "Couldn't obtain rowset.", __LINE__, __FILE__);
}

//
// Start auth check
//
$is_auth = auth(ALL, $forum_id, $userdata, $forum_row[0]);

if(!$is_auth['auth_read'])
{
	//
	// Ooopss, user is not authed
	// to read this forum ...
	//
	include('includes/page_header.'.$phpEx);
	
	$msg = "I am sorry but you are not currently authorised to read this forum. You could try logging on and trying again. If you are logged on then this is a private forum for which you have not been granted access.";

	$template->set_filenames(array(
		"reg_header" => "error_body.tpl"
	));
	$template->assign_vars(array(
		"ERROR_MESSAGE" => $msg
	));
	$template->pparse("reg_header");

	include('includes/page_tail.'.$phpEx);
}
//
// End of auth check
//

$forum_name = stripslashes($forum_row[0]['forum_name']);
if(empty($HTTP_POST_VARS['postdays']))
{
	$topics_count = $forum_row[0]['forum_topics'];
}

for($x = 0; $x < $db->sql_numrows($result); $x++)
{
	if($x > 0)
		$forum_moderators .= ", ";
	
	$forum_moderators .= "<a href=\"".append_sid("profile.$phpEx?mode=viewprofile&".POST_USERS_URL."=".$forum_row[$x]['user_id'])."\">".$forum_row[$x]['username']."</a>";
}


//
// Generate a 'Show posts in previous x days'
// select box. If the postdays var is POSTed 
// then get it's value, find the number of topics 
// with dates newer than it (to properly handle 
// pagination) and alter the main query
//
$previous_days = array(0, 1, 7, 14, 30, 60, 180, 364);
$previous_days_text = array("$l_All_Topics", "1 $l_Day", "7 $l_Days", "2 $l_Weeks", "1 $l_Month", "2 $l_Months", "6 $l_Months", "1 $l_Year");

if(!empty($HTTP_POST_VARS['postdays']) || !empty($HTTP_GET_VARS['postdays']))
{

	$post_days = (!empty($HTTP_POST_VARS['postdays'])) ? $HTTP_POST_VARS['postdays'] : $HTTP_GET_VARS['postdays'];
	$min_post_time = time() - ($post_days * 86400);

	$sql = "SELECT COUNT(*) AS forum_topics 
		FROM ".TOPICS_TABLE."  
		WHERE forum_id = $forum_id 
			AND topic_time > $min_post_time";

	if(!$result = $db->sql_query($sql))
	{
		error_die(SQL_QUERY, "Couldn't obtain limited topics count information.", __LINE__, __FILE__);
	}
	$topics_count = $db->sql_fetchfield("forum_topics", -1, $result);

	$limit_posts_time = "AND t.topic_time > $min_post_time ";

	if(!empty($HTTP_POST_VARS['postdays']))
	{
		$start = 0;
	}
}
else
{
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
// Grab all the basic data for
// this forum
//
$sql = "SELECT t.*, u.username, u.user_id, u2.username as user2, u2.user_id as id2, p.post_time
	FROM ".TOPICS_TABLE." t, ".USERS_TABLE." u, ".POSTS_TABLE." p, ".USERS_TABLE." u2
	WHERE t.forum_id = $forum_id
		AND t.topic_poster = u.user_id 
		AND p.post_id = t.topic_last_post_id 
		AND p.poster_id = u2.user_id 
		$limit_posts_time
	ORDER BY p.post_time DESC
	LIMIT $start, ".$board_config['topics_per_page'];
if(!$t_result = $db->sql_query($sql))
{
   error_die(SQL_QUERY, "Couldn't obtain topic information.", __LINE__, __FILE__);
}
$total_topics = $db->sql_numrows($t_result);

//
// Post URL generation for 
// templating vars
//
$post_new_topic_url = append_sid("posting.".$phpEx."?mode=newtopic&".POST_FORUM_URL."=$forum_id");
$template->assign_vars(array(
	"U_POST_NEW_TOPIC" => $post_new_topic_url,
	"S_SELECT_POST_DAYS" => $select_post_days,
	"S_POST_DAYS_ACTION" => append_sid("viewforum.$phpEx?".POST_FORUM_URL."=".$forum_id."&start=$start")));

//
// Dump out the page header and
// load viewforum template
//
include('includes/page_header.'.$phpEx);

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
	"USERS_BROWSING" => $users_browsing)
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
	for($x = 0; $x < $total_topics; $x++)
	{
		$topic_title = stripslashes($topic_rowset[$x]['topic_title']);
		$topic_id = $topic_rowset[$x]['topic_id'];
		$replies = $topic_rowset[$x]['topic_replies'];
		if($replies > $board_config['posts_per_page'])
		{
			$goto_page = "&nbsp;&nbsp;&nbsp;(<img src=\"".$images['posticon']."\">$l_gotopage: ";
			$times = 1;
			for($i = 0; $i < ($replies + 1); $i += $board_config['posts_per_page'])
			{
				if($times > 4) 
				{
					if(($i + $board_config['posts_per_page']) >= ($replies + 1)) 
					{
						$goto_page.=" ... <a href=\"".append_sid("viewtopic.$phpEx?".POST_TOPIC_URL."=".$topic_id."&start=$i")."\">$times</a>";
					}
				}
				else 
				{
					if($times != 1)
					{
						$goto_page.= ", ";
					}
					$goto_page.= "<a href=\"".append_sid("viewtopic.$phpEx?".POST_TOPIC_URL."=".$topic_id."&start=$i")."\">$times</a>";
				}
				$times++;
			}
			$goto_page.= ")";
		}
		else 
		{
			$goto_page = "";
		}

		$folder_img = "<img src=\"".$images['folder']."\">";

		$view_topic_url = append_sid("viewtopic.".$phpEx."?".POST_TOPIC_URL."=".$topic_id."&".$replies);

		$topic_poster = stripslashes($topic_rowset[$x]['username']);
		$topic_poster_profile_url = append_sid("profile.$phpEx?mode=viewprofile&".POST_USERS_URL."=".$topic_rowset[$x]['user_id']);

		$last_post_time = create_date($board_config['default_dateformat'], $topic_rowset[$x]['post_time'], $board_config['default_timezone']);
		$last_post_user = $topic_rowset[$x]['user2'];
		$last_post_profile_url = append_sid("profile.$phpEx?mode=viewprofile&".POST_USERS_URL."=".$topic_rowset[$x]['id2']);

		$views = $topic_rowset[$x]['topic_views'];

		$template->assign_block_vars("topicrow", array(
			"FORUM_ID" => $forum_id,
			"TOPIC_ID" => $topic_id,
			"FOLDER" => $folder_img, 
			"TOPIC_POSTER" => $topic_poster,
			"GOTO_PAGE" => $goto_page,
			"REPLIES" => $replies,
			"TOPIC_TITLE" => $topic_title,
			"VIEWS" => $views,
			"LAST_POST_TIME" => $last_post_time,
			"LAST_POST_USER" => $last_post_user,

			"U_VIEW_TOPIC" => $view_topic_url,
			"U_LAST_POST_USER_PROFILE" => $last_post_profile_url,
			"U_TOPIC_POSTER_PROFILE" => $topic_poster_profile_url)
		);
	}

	$s_auth_can = "";
	$s_auth_can .= "You " . (($is_auth['auth_read']) ? "<b>can</b>" : "<b>cannot</b>" ) . " read posts in this forum<br>";
	$s_auth_can .= "You " . (($is_auth['auth_post']) ? "<b>can</b>" : "<b>cannot</b>") . " add new topics to this forum<br>";
	$s_auth_can .= "You " . (($is_auth['auth_reply']) ? "<b>can</b>" : "<b>cannot</b>") . " reply to posts in this forum<br>";
	$s_auth_can .= "You " . (($is_auth['auth_edit']) ? "<b>can</b>" : "<b>cannot</b>") . " edit your posts in this forum<br>";
	$s_auth_can .= "You " . (($is_auth['auth_delete']) ? "<b>can</b>" : "<b>cannot</b>") . " delete your posts in this forum<br>";
	$s_auth_can .= ($is_auth['auth_mod']) ? "You are a moderator of this forum<br>" : "";
	$s_auth_can .= ($is_auth['auth_admin']) ? "You are a board admin<br>" : "";

	$template->assign_vars(array(
		"PAGINATION" => generate_pagination("viewforum.$phpEx?".POST_FORUM_URL."=$forum_id&postdays=$post_days", $topics_count, $board_config['topics_per_page'], $start),
		"ON_PAGE" => (floor($start/$board_config['topics_per_page'])+1),
		"TOTAL_PAGES" => ceil($topics_count/$board_config['topics_per_page']), 

		"S_AUTH_LIST" => $s_auth_can,
		
		"L_OF" => $lang['of'],
		"L_PAGE" => $lang['Page'],
		"L_GOTO_PAGE" => $lang['Goto_page'])
	);

	$template->pparse("body");
}
else
{
	//
	// This will be present in the templates
	// at some future point when if...else
	// constructs are available
	//
	error_die(NO_POSTS);
}
			       
include('includes/page_tail.'.$phpEx);

?>