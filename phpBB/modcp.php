<?php
/***************************************************************************  
 *                                 
 *                            -------------------                         
 *   begin                : July 4, 2001
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
	

if($is_auth['auth_mod'] || $is_auth['auth_admin'])
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

$mode = ($HTTP_POST_VARS['mode']) ? $HTTP_POST_VARS['mode'] : $HTTP_GET_VARS['mode'];
$delete = ($HTTP_POST_VARS['delete']) ? 1 : 0;
$move = ($HTTP_POST_VARS['move']) ? 1 : 0;
$lock = ($HTTP_POST_VARS['lock']) ? 1 : 0;
$unlock = ($HTTP_POST_VARS['unlock']) ? 1 : 0;

if(!$mode)
{
	if($delete)
	{
		$mode = 'delete';
	}
	else if($move)
	{
		$mode = 'move';
	}
	else if($lock)
	{
		$mode = 'lock';
	}
	else if($unlock)
	{
		$mode = 'unlock';
	}
}

switch($mode)
{
	case 'delete':
		if($HTTP_POST_VARS['preform_op'])
		{
			$topics = $HTTP_POST_VARS['preform_op'];
			$sql = "SELECT post_id FROM ".POSTS_TABLE." WHERE ";
			$delete_topics = "DELETE FROM ".TOPICS_TABLE." WHERE ";
			for($x = 0; $x < count($topics); $x++)
			{
				if($x > 0)
				{
					$sql .= " OR ";
					$delete_topics .= " OR ";
				}
				$sql .= "topic_id = ".$topics[$x];
				$delete_topics .= "topic_id = ".$topics[$x];
			}
			$topics_removed = $x;
			
			if(!$result = $db->sql_query($sql))
			{
				message_die(GENERAL_ERROR, "Could not get posts lists for deletion!", "Error", __LINE__, __FILE__, $sql);
			}
			$num_posts = $db->sql_numrows($result);
			$rowset = $db->sql_fetchrowset($result);
			$delete_posts = "DELETE FROM ".POSTS_TABLE." WHERE ";
			$delete_text = "DELETE FROM ".POSTS_TEXT_TABLE." WHERE ";
			for($x = 0; $x < $num_posts; $x++)
			{
				if($x > 0)
				{
					$delete_posts .= " OR ";
					$delete_text .= " OR ";
				}
				$delete_posts .= "post_id = ".$rowset[$x]['post_id'];
				$delete_text .= "post_id = ".$rowset[$x]['post_id'];
			}
			$posts_removed = $x;
	
			if(!$result = $db->sql_query($delete_text, BEGIN_TRANSACTION))
			{	
				message_die(GENERAL_ERROR, "Could not delete posts text!", "Error", __LINE__, __FILE__, $delete_text);
			}
		
			if(!$result = $db->sql_query($delete_posts))
			{
				message_die(GENERAL_ERROR, "Could not delete posts!", "Error", __LINE__, __FILE__, $delete_posts);
			}
			
			if(!$result = $db->sql_query($delete_topics))
			{
				message_die(GENERAL_ERROR, "Could not delete topics!", "Error", __LINE__, __FILE__, $delete_topics);
			}
		
			if(SQL_LAYER != "mysql")
			{
				$update_index = "UPDATE ".FORUMS_TABLE." 
									 SET forum_topics = forum_topics - $topics_removed, 
									 forum_posts = forum_posts - $posts_removed, 
									 forum_last_post_id = (select max(post_id) FROM ".POSTS_TABLE." 
									 WHERE forum_id = $forum_id) WHERE forum_id = $forum_id";
			
				if(!$result = $db->sql_query($update_index, END_TRANSACTION))
				{
					message_die(GENERAL_ERROR, "Could not update index!", "Error", __LINE__, __FILE__, $delete_topics);
				}
			}
			else
			{
				$sql = "select max(post_id) AS last_post FROM ".POSTS_TABLE." WHERE forum_id = $forum_id";
				if(!$result = $db->sql_query($sql))
				{
					message_die(GENERAL_ERROR, "Could not get last post id", "Error", __LINE__, __FILE__, $sql);
				}
				$last_post = $db->sql_fetchrowset($result);
				$update_index = "UPDATE ".FORUMS_TABLE." 
									 SET forum_topics = forum_topics - $topics_removed, 
									 forum_posts = forum_posts - $posts_removed, 
									 forum_last_post_id = ".$last_post[0]['last_post']." WHERE forum_id = $forum_id";
				if(!$result = $db->sql_query($update_index, END_TRANSACTION))
				{
					message_die(GENERAL_ERROR, "Could not update index!", "Error", __LINE__, __FILE__, $update_index);
				}
			}
							
			$msg = $lang['Topics_Removed'] . "<br />" . "<a href=\"".append_sid("modcp.$phpEx?".POST_FORUM_URL."=$forum_id")."\">". $lang['Click'] . " " . $lang['Here'] ."</a> " . $lang['Return_to_modcp'];
			message_die(GENERAL_MESSAGE, $msg);
			
		}

	
	break;
	case 'move':
		echo 'Move';
	
	break;
	case 'lock':
		if($HTTP_POST_VARS['preform_op'])
		{
			$topics = $HTTP_POST_VARS['preform_op'];
			$sql = "UPDATE " . TOPICS_TABLE . " SET topic_status = " . TOPIC_LOCKED . " WHERE ";
			for($x = 0; $x < count($topics); $x++)
			{
				if($x > 0)
				{
					$sql .= " OR ";
				}
				$sql .= "topic_id = " . $topics[$x];
			}
			
			if(!$result = $db->sql_query($sql))
			{
				message_die(GENERAL_ERROR, "Coule not update topics table!", "Error", __LINE__, __FILE__, $sql);
			}
			else
			{
				$msg = $lang['Topics_Locked'] . "<br />" . "<a href=\"".append_sid("modcp.$phpEx?".POST_FORUM_URL."=$forum_id")."\">". $lang['Click'] . " " . $lang['Here'] ."</a> " . $lang['Return_to_modcp'];
				message_die(GENERAL_MESSAGE, $msg);
			}
		}
	
	break;
	case 'unlock':
		if($HTTP_POST_VARS['preform_op'])
		{
			$topics = $HTTP_POST_VARS['preform_op'];
			$sql = "UPDATE " . TOPICS_TABLE . " SET topic_status = " . TOPIC_UNLOCKED . " WHERE ";
			for($x = 0; $x < count($topics); $x++)
			{
				if($x > 0)
				{
					$sql .= " OR ";
				}
				$sql .= "topic_id = " . $topics[$x];
			}
			
			if(!$result = $db->sql_query($sql))
			{
				message_die(GENERAL_ERROR, "Coule not update topics table!", "Error", __LINE__, __FILE__, $sql);
			}
			else
			{
				$msg = $lang['Topics_Unlocked'] . "<br />" . "<a href=\"".append_sid("modcp.$phpEx?".POST_FORUM_URL."=$forum_id")."\">". $lang['Click'] . " " . $lang['Here'] ."</a> " . $lang['Return_to_modcp'];
				message_die(GENERAL_MESSAGE, $msg);
			}
		}
		
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
		
		$sql = "SELECT t.topic_title, t.topic_id, t.topic_replies, t.topic_status, t.topic_type, u.username, u.user_id, p.post_time
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
			$u_view_topic = append_sid("viewtopic.$phpEx?".POST_TOPIC_URL."=$topic_id");
			$topic_replies = $topics[$x]['topic_replies'];
			$last_post_time = create_date($board_config['default_dateformat'], $topics[$x]['post_time'], $board_config['default_timezone']);

			
			$template->assign_block_vars("topicrow", array(
													"U_VIEW_TOPIC" => $u_view_topic,
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
