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

/**
 * Moderator Control Panel
 *
 * From this 'Control Panel' the moderator of a forum will be able to do
 * mass topic operations (locking/unlocking/moving/deleteing), and it will
 * provide an interface to do quick locking/unlocking/moving/deleting of 
 * topics via the moderator operations buttons on all of the viewtopic pages.
 */
 
include('extension.inc');
include('common.'.$phpEx);


$pagetype = "modcp";
$page_title = "Modertator Control Panel";

$forum_id = ($HTTP_POST_VARS[POST_FORUM_URL]) ? $HTTP_POST_VARS[POST_FORUM_URL] : $HTTP_GET_VARS[POST_FORUM_URL];
$topic_id = ($HTTP_POST_VARS[POST_TOPIC_URL]) ? $HTTP_POST_VARS[POST_TOPIC_URL] : $HTTP_GET_VARS[POST_TOPIC_URL];

if(empty($forum_id))
{
	$sql = "SELECT forum_id, forum_topics FROM ".TOPICS_TABLE." WHERE topic_id = ".$topic_id;
	if(!$result = $db->sql_query($sql))
	{
		message_die(GENERAL_MESSAGE, $lang['Topic_post_not_exist'], "", __LINE__, __FILE__, $sql);
	}
	$topic_row = $db->sql_fetchrowset($result);
	$forum_topics = $topic_row[0]['forum_topics'];
	$forum_id = $topic_row[0]['forum_id'];
}
else
{
	$sql = "SELECT forum_topics FROM ".FORUMS_TABLE." WHERE forum_id = ".$forum_id;
	if(!$result = $db->sql_query($sql))
	{
		message_die(GENERAL_MESSAGE, $lang['Topic_post_not_exist'], "", __LINE__, __FILE__, $sql);
	}
	$topic_row = $db->sql_fetchrowset($result);
	$forum_topics = $topic_row[0]['forum_topics'];
}

$is_mod = 0;

//
// Start session management
//
$userdata = session_pagestart($user_ip, $forum_id, $session_length);
init_userprefs($userdata);
//
// End session management
//


//
// Start auth check
//
$is_auth = auth(AUTH_ALL, $forum_id, $userdata);
	

if($is_auth['auth_mod'] || $userdata['user_level'] == ADMIN)
{
	$is_mod = TRUE;
}
else
{
	$is_mod = FALSE;
}
//
// End Auth Check
//

if(!$is_mod)
{
	message_die(CRITICAL_MESSAGE, $lang['Not_Moderator'], $lang['Not_Authorised'], __LINE__, __FILE__);
}

include('includes/page_header.'.$phpEx);

switch($mode)
{
	case 'delete':
	
	
	break;
	case 'move':
	
	
	break;
	case 'lock':
	
	
	break;
	case 'unlock':
	
	break;
	default:
		$template->set_filenames(array("body" => "modcp_body.tpl"));
		$template->assign_vars(array("L_MOD_EXPLAIN" => $lang['ModCp_Explain'],
												"L_SELECT" => $lang['Select'],
												"L_DELETE" => $lang['Delete'],
												"L_MOVE" => $lang['Move'],
												"L_LOCK" => $lang['Lock'],
												"L_UNLOCK" => $lang['Unlock'],
												"S_MODCP_URL" => append_sid("modcp.$phpEx") ));
		if(!$start)
		{
			$start = 0;
		}
		
		$sql = "SELECT t.topic_title, t.topic_id, t.topic_replies, u.username, u.user_id, p.post_time
					FROM " . TOPICS_TABLE . " t, " . USERS_TABLE . " u, " . POSTS_TABLE . " p 
					WHERE t.forum_id = $forum_id
					AND t.topic_poster = u.user_id
					AND p.post_id = t.topic_last_post_id
					AND t.topic_type <> " . POST_GLOBAL_ANNOUNCE . "
					ORDER BY t.topic_type DESC, p.post_time DESC
					LIMIT $start, ".$board_config['topics_per_page'];

		if(!$t_result = $db->sql_query($sql))
		{
   		message_die(GENERAL_ERROR, "Couldn't obtain topic information", "", __LINE__, __FILE__, $sql);
		}
		$total_topics = $db->sql_numrows($t_result);
		$topics = $db->sql_fetchrowset($t_result);
		
		for($x = 0; $x < $total_topics; $x++)
		{	
			$topic_id = $topics[$x]['topic_id'];
			$topic_title = stripslashes($topics[$x]['topic_title']);
			$s_topic_url = append_sid("viewtopic.$phpEx?".POST_TOPIC_URL."=$topic_id");
			$topic_replies = $topics[$x]['topic_replies'];
			$last_post_time = create_date($board_config['default_dateformat'], $topics[$x]['post_time'], $board_config['default_timezone']);

			
			$template->assign_block_vars("topicrow", array(
													"S_TOPIC_URL" => $s_topic_url,
													"TOPIC_TITLE" => $topic_title,
													"REPLIES" => $topic_replies,
													"LAST_POST" => $last_post_time,
													"TOPIC_ID" => $topic_id));
		}
		
		$pagination = generate_pagination("modcp.$phpEx?".POST_FORUM_URL."=$forum_id", $forum_topics, $board_config['topics_per_page'], $start);
		$template->assign_vars(array("PAGINATION" => $pagination,
												"FORUM_ID" => $forum_id,
												"POST_FORUM_URL" => POST_FORUM_URL,
												"ON_PAGE" => (floor($start/$board_config['topics_per_page'])+1),
												"TOTAL_PAGES" => ceil($forum_topics/$board_config['topics_per_page']),
												"L_OF" => $lang['of'],
												"L_PAGE" => $lang['Page'],
												"L_GOTO_PAGE" => $lang['Goto_page']));
		$template->pparse("body");
	break;
}

include('includes/page_tail.'.$phpEx);
	
?>
