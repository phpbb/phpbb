<?php
/***************************************************************************  
 *                                modcp.php 
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
 
$phpbb_root_path = "./";
include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'common.'.$phpEx);
include($phpbb_root_path . 'includes/bbcode.'.$phpEx);

$pagetype = "modcp";
$page_title = "Modertator Control Panel";

$forum_id = ($HTTP_POST_VARS[POST_FORUM_URL]) ? $HTTP_POST_VARS[POST_FORUM_URL] : $HTTP_GET_VARS[POST_FORUM_URL];
$topic_id = ($HTTP_POST_VARS[POST_TOPIC_URL]) ? $HTTP_POST_VARS[POST_TOPIC_URL] : $HTTP_GET_VARS[POST_TOPIC_URL];

if(empty($forum_id) || !isset($forum_id))
{
	$sql = "SELECT f.forum_id, f.forum_topics FROM ".TOPICS_TABLE." t, ".FORUMS_TABLE." f WHERE t.topic_id = ".$topic_id." AND f.forum_id = t.forum_id";
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


//
// Check if user did or did not confirm
// If they did not, forward them to the last page they were on
//
$confirm = ($HTTP_POST_VARS['confirm']) ? 1 : 0;
if($HTTP_POST_VARS['not_confirm'])
{
	header("Location: index.$phpEx");
}

include($phpbb_root_path . 'includes/page_header.'.$phpEx);

// Set template files
$template->set_filenames(array("body" => "modcp_body.tpl", "confirm" => "confirm_body.tpl", "split_body" => "split_body.tpl"));

$mode = ($HTTP_POST_VARS['mode']) ? $HTTP_POST_VARS['mode'] : $HTTP_GET_VARS['mode'];
$quick_op = ($HTTP_GET_VARS['quick_op']) ? $HTTP_GET_VARS['quick_op'] : $HTTP_POST_VARS['quick_op'];

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
		if($confirm)
		{
			if($HTTP_POST_VARS['preform_op'])
			{
				$topics = $HTTP_POST_VARS['preform_op'];
			}
			else
			{
				$topics = array($HTTP_POST_VARS[POST_TOPIC_URL]);
			}
		
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
			if($quick_op)
			{
				$next_page = "viewforum.$phpEx?".POST_FORUM_URL."=$forum_id";
				$return_message = $lang['to_return_forum'];
			}
			else
			{
				$next_page = "modcp.$phpEx?".POST_FORUM_URL."=$forum_id";
				$return_message = $lang['Return_to_modcp'];
			}
							
			$msg = $lang['Topics_Removed'] . "<br />" . "<a href=\"".append_sid($next_page)."\">". $lang['Click'] . " " . $lang['Here'] ."</a> " . $return_message;
			message_die(GENERAL_MESSAGE, $msg);
		}
		else
		{
			$hidden_fields = '<input type="hidden" name="mode" value="'.$mode.'"><input type="hidden" name="'.POST_FORUM_URL.'" value="'.$forum_id.'"><input type="hidden" name="quick_op" value="'.$quick_op.'">';
			if($HTTP_POST_VARS['preform_op'])
			{
				$topics = $HTTP_POST_VARS['preform_op'];
				for($x = 0; $x < count($topics); $x++)
				{
					$hidden_fields .= '<input type="hidden" name="preform_op[]" value="'.$topics[$x].'">';
				}
			}
			else
			{
				$hidden_fields .= '<input type="hidden" name="'.POST_TOPIC_URL.'" value="'.$topic_id.'">';
			}

			$template->assign_vars(array("MESSAGE_TITLE" => $lang['Confirm'],
												  "MESSAGE_TEXT" => $lang['Confirm_delete_topic'],
												  "L_YES" => $lang['Yes'],
												  "L_NO" => $lang['No'],
												  "S_CONFIRM_ACTION" => append_sid("modcp.$phpEx"),
												  "S_HIDDEN_FIELDS" => $hidden_fields));
			$template->pparse("confirm");
			include($phpbb_root_path . 'includes/page_tail.'.$phpEx);
		}
		break;

	case 'move':
		echo 'Move';
		break;

	case 'lock':
		if($confirm)
		{
			if($HTTP_POST_VARS['preform_op'])
			{
				$topics = $HTTP_POST_VARS['preform_op'];
			}
			else
			{
				$topics = array($HTTP_POST_VARS[POST_TOPIC_URL]);
			}
			
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
				if($quick_op)
				{
					$next_page = "viewtopic.$phpEx?".POST_TOPIC_URL."=$topic_id";
					$return_message = $lang['to_return_topic'];
				}
				else
				{
					$next_page = "modcp.$phpEx?".POST_FORUM_URL."=$forum_id";
					$return_message = $lang['Return_to_modcp'];
				}
				$msg = $lang['Topics_Locked'] . "<br />" . "<a href=\"".append_sid($next_page)."\">". $lang['Click'] . " " . $lang['Here'] ."</a> " . $return_message;
				message_die(GENERAL_MESSAGE, $msg);
			}
		}
		else
		{
			$hidden_fields = '<input type="hidden" name="mode" value="'.$mode.'"><input type="hidden" name="'.POST_FORUM_URL.'" value="'.$forum_id.'"><input type="hidden" name="quick_op" value="'.$quick_op.'">';
			if($HTTP_POST_VARS['preform_op'])
			{
				$topics = $HTTP_POST_VARS['preform_op'];
				for($x = 0; $x < count($topics); $x++)
				{
					$hidden_fields .= '<input type="hidden" name="preform_op[]" value="'.$topics[$x].'">';
				}
			}
			else
			{
				$hidden_fields .= '<input type="hidden" name="'.POST_TOPIC_URL.'" value="'.$topic_id.'">';
			}

			$template->assign_vars(array("MESSAGE_TITLE" => $lang['Confirm'],
												  "MESSAGE_TEXT" => $lang['Confirm_lock_topic'],
												  "L_YES" => $lang['Yes'],
												  "L_NO" => $lang['No'],
												  "S_CONFIRM_ACTION" => append_sid("modcp.$phpEx"),
												  "S_HIDDEN_FIELDS" => $hidden_fields));
			$template->pparse("confirm");
			include($phpbb_root_path . 'includes/page_tail.'.$phpEx);
		}

	break;
	case 'unlock':
		if($confirm)
		{
			if($HTTP_POST_VARS['preform_op'])
			{
				$topics = $HTTP_POST_VARS['preform_op'];
			}
			else
			{
				$topics = array($HTTP_POST_VARS[POST_TOPIC_URL]);
			}
				
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
				if($quick_op)
				{
					$next_page = "viewtopic.$phpEx?".POST_TOPIC_URL."=$topic_id";
					$return_message = $lang['to_return_topic'];
				}
				else
				{
					$next_page = "modcp.$phpEx?".POST_FORUM_URL."=$forum_id";
					$return_message = $lang['Return_to_modcp'];
				}

				$msg = $lang['Topics_Unlocked'] . "<br />" . "<a href=\"".append_sid($next_page)."\">". $lang['Click'] . " " . $lang['Here'] ."</a> " . $return_message;
				message_die(GENERAL_MESSAGE, $msg);
			}
		}
		else
		{
			$hidden_fields = '<input type="hidden" name="mode" value="'.$mode.'"><input type="hidden" name="'.POST_FORUM_URL.'" value="'.$forum_id.'"><input type="hidden" name="quick_op" value="'.$quick_op.'">';
			if($HTTP_POST_VARS['preform_op'])
			{
				$topics = $HTTP_POST_VARS['preform_op'];
				for($x = 0; $x < count($topics); $x++)
				{
					$hidden_fields .= '<input type="hidden" name="preform_op[]" value="'.$topics[$x].'">';
				}
			}
			else
			{
				$hidden_fields .= '<input type="hidden" name="'.POST_TOPIC_URL.'" value="'.$topic_id.'">';
			}

			$template->assign_vars(array("MESSAGE_TITLE" => $lang['Confirm'],
												  "MESSAGE_TEXT" => $lang['Confirm_unlock_topic'],
												  "L_YES" => $lang['Yes'],
												  "L_NO" => $lang['No'],
												  "S_CONFIRM_ACTION" => append_sid("modcp.$phpEx"),
												  "S_HIDDEN_FIELDS" => $hidden_fields));
			$template->pparse("confirm");
			include($phpbb_root_path . 'includes/page_tail.'.$phpEx);
		}
	
	break;
	
	case 'split':
		if($HTTP_POST_VARS['split'])
		{
			$posts = $HTTP_POST_VARS['preform_op'];
				
			$sql = "SELECT poster_id, topic_id, post_time FROM ".POSTS_TABLE." WHERE post_id = ".$posts[0];
			if(!$result = $db->sql_query($sql, BEGIN_TRANSACTION))
			{
				message_die(GENERAL_ERROR, "Could not get post information", "Error", __LINE__, __FILE__, $sql);
			}
			
			$post_rowset = $db->sql_fetchrowset($result);
			$first_poster = $post_rowset[0]['poster_id'];
			$topic_id = $post_rowset[0]['topic_id'];
			$post_time = $post_rowset[0]['post_time'];
			
			$subject = trim(strip_tags(htmlspecialchars(addslashes($HTTP_POST_VARS['subject']))));
			if(empty($subject))
			{
				message_die(GENERAL_ERROR, $lang['Empty_subject'], $lang['Error'], __LINE__, __FILE__);
			}
					
			$new_forum_id = $HTTP_POST_VARS['new_forum_id'];
			$topic_time = get_gmt_ts();
					
			$sql  = "INSERT INTO " . TOPICS_TABLE . " (topic_title, topic_poster, topic_time, forum_id, topic_notify, topic_status, topic_type)
						VALUES ('$subject', $first_poster, " . $topic_time . ", $new_forum_id, 0, " . TOPIC_UNLOCKED . ", ".POST_NORMAL.")";
			if(!$result = $db->sql_query($sql, BEGIN_TRANSACTION))
			{
				message_die(GENERAL_ERROR, "Could not insert new topic", "Error", __LINE__, __FILE__, $sql);
			}
							
			$new_topic_id = $db->sql_nextid();
			if($HTTP_POST_VARS['split_type'] == "split")
			{
				$sql = "UPDATE ".POSTS_TABLE." SET topic_id = $new_topic_id WHERE ";
				for($x = 0; $x < count($posts); $x++)
				{
					if($x > 0)
					{
						$sql .= " OR ";
					}
					$sql .= "post_id = ".$posts[$x];
					$last_post_id = $posts[$x];
				}
			}
			else if($HTTP_POST_VARS['split_type'] == "split_after")
			{
				$sql = "UPDATE ".POSTS_TABLE." SET topic_id = $new_topic_id WHERE post_time >= $post_time AND topic_id = $topic_id";
			}
					
			if(!$result = $db->sql_query($sql, END_TRANSACTION))
			{
				message_die(GENERAL_ERROR, "Could not update posts table!", $lang['Error'], __LINE__, __FILE__, $sql);
			}
			else
			{	
				sync("topic", $new_topic_id);
				sync("topic", $topic_id);
				sync("forum", $forum_id);
				$next_page = "viewtopic.$phpEx?".POST_TOPIC_URL."=$new_topic_id";
				$return_message = $lang['to_return_topic'];
				message_die(GENERAL_MESSAGE, $lang['Topic_split'] . "<br />" . "<a href=\"".append_sid($next_page)."\">". $lang['Click'] . " " . $lang['Here'] ."</a> " . $return_message);
			}
		}
		else
		{
			$topic_id = ($HTTP_POST_VARS[POST_TOPIC_URL]) ? $HTTP_POST_VARS[POST_TOPIC_URL] : $HTTP_GET_VARS[POST_TOPIC_URL];
			
			$sql = "SELECT u.username, p.post_time, p.post_id, p.bbcode_uid, pt.post_text, pt.post_subject, p.post_username
						FROM " . POSTS_TABLE . " p, " . USERS_TABLE . " u, " . POSTS_TEXT_TABLE . " pt
						WHERE p.topic_id = $topic_id
						AND p.poster_id = u.user_id
						AND p.post_id = pt.post_id
						ORDER BY p.post_time ASC";
			if(!$result = $db->sql_query($sql))
			{
				message_die(GENERAL_ERROR, "Could not get topic/post information", "Error", __LINE__, __FILE__, $sql);
			}
			
			if(($total_posts = $db->sql_numrows($result)) > 0)
			{
				$postrow = $db->sql_fetchrowset($result);
				
				$template->assign_vars(array("L_AUTHOR" => $lang['Author'],
														"L_MESSAGE" => $lang['Message'],
														"L_SELECT" => $lang['Select'],
														"L_SUBJECT" => $lang['Subject'],
														"L_POSTED" => $lang['Posted'],
														"L_SPLIT_POSTS" => $lang['Split_posts'],
														"L_SUBMIT" => $lang['Submit'],
														"L_SPLIT_AFTER" => $lang['Split_after'],
														"S_MODCP_URL" => append_sid("modcp.$phpEx"),
														"POST_FORUM_URL" => POST_FORUM_URL,
														"FORUM_ID" => $forum_id,
														"FORUM_INPUT" => make_forum_box("new_forum_id", $forum_id)));
				
				for($i = 0; $i < $total_posts; $i++)
				{
					$poster_id = $postrow[$i]['user_id'];
					$poster = stripslashes($postrow[$i]['username']);
					$post_date = create_date($board_config['default_dateformat'], $postrow[$i]['post_time'], $board_config['default_timezone']);
					$post_id = $postrow[$i]['post_id'];
					
					if($poster_id == ANONYMOUS && $postrow[$i]['post_username'] != '')
					{
						$poster = stripslashes($postrow[$i]['post_username']);
					}
					$post_subject = ($postrow[$i]['post_subject'] != "") ? stripslashes($postrow[$i]['post_subject']) : "";

					$bbcode_uid = $postrow[$i]['bbcode_uid'];
				
					$user_sig = stripslashes($postrow[$i]['user_sig']);
					$message = stripslashes($postrow[$i]['post_text']);
				
					if(!$board_config['allow_html'])
					{
						$user_sig = strip_tags($user_sig);
						$message = strip_tags($message);
					}
				
					if($board_config['allow_bbcode'])
					{
						// do bbcode stuff here
						$sig_uid = make_bbcode_uid();
						$user_sig = bbencode_first_pass($user_sig, $sig_uid);
						$user_sig = bbencode_second_pass($user_sig, $sig_uid);
				
						$message = bbencode_second_pass($message, $bbcode_uid);
					}
				
					$message = make_clickable($message);
					$message = str_replace("\n", "<br />", $message);
					$message = eregi_replace("\[addsig]$", "<br /><br />_________________<br />" . nl2br($user_sig), $message);
					
					//$message = (strlen($message) > 100) ? substr($message, 0, 100) . " ..." : $message;

					$template->assign_block_vars("postrow", array(
															"POSTER_NAME" => $poster,
															"POST_DATE" => $post_date,
															"POST_SUBJECT" => $post_subject,
															"MESSAGE" => $message,
															"POST_ID" => $post_id));
				}
				$template->pparse("split_body");
			}										
			
		}
			
	
	break;

	default:

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
			if($topics[$x]['topic_status'] == TOPIC_LOCKED)
			{	
				$folder_image = "<img src=\"" . $images['locked_folder'] . "\" alt=\"Topic Locked\">";
			}
			else
			{
				$folder_image = "<img src=\"" . $images['folder'] . "\">";
			}
			
			$topic_id = $topics[$x]['topic_id'];
			
			$topic_title = "";
			
			if($topics[$x]['topic_type'] == POST_STICKY)
			{
				$topic_title = $lang['Sticky'] . " ";
			}
			else if($topics[$x]['topic_type'] == POST_ANNOUNCE)
			{
				$topic_title = $lang['Annoucement'] . " ";
			}
			
			$topic_title .= stripslashes($topics[$x]['topic_title']);
			$u_view_topic = append_sid("viewtopic.$phpEx?".POST_TOPIC_URL."=$topic_id");
			$topic_replies = $topics[$x]['topic_replies'];
			$last_post_time = create_date($board_config['default_dateformat'], $topics[$x]['post_time'], $board_config['default_timezone']);

			
			$template->assign_block_vars("topicrow", array(
													"U_VIEW_TOPIC" => $u_view_topic,
													"FOLDER_IMG" => $folder_image,
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
			"L_GOTO_PAGE" => $lang['Goto_page'])
		);

		$template->pparse("body");

		break;
}

include($phpbb_root_path . 'includes/page_tail.'.$phpEx);
	
?>