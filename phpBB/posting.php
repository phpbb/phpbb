<?php
/***************************************************************************
 *                                 posting.php
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
include($phpbb_root_path . 'includes/post.'.$phpEx);
include($phpbb_root_path . 'includes/bbcode.'.$phpEx);

//
// Start session management
//
$userdata = session_pagestart($user_ip, PAGE_POSTING, $session_length);
init_userprefs($userdata);
//
// End session management
//

//
// Set initial conditions
//
if( isset($HTTP_GET_VARS[POST_FORUM_URL]) || isset($HTTP_POST_VARS[POST_FORUM_URL]) ) 
{
	$forum_id = (isset($HTTP_POST_VARS[POST_FORUM_URL])) ? $HTTP_POST_VARS[POST_FORUM_URL] : $HTTP_GET_VARS[POST_FORUM_URL];
}
else
{
	$forum_id = "";
}
if( isset($HTTP_GET_VARS[POST_POST_URL]) || isset($HTTP_POST_VARS[POST_POST_URL]) )
{
	$post_id = (isset($HTTP_POST_VARS[POST_POST_URL])) ? $HTTP_POST_VARS[POST_POST_URL] : $HTTP_GET_VARS[POST_POST_URL];
}
else
{
	$post_id = "";
}
if( isset($HTTP_GET_VARS[POST_TOPIC_URL]) || isset($HTTP_POST_VARS[POST_TOPIC_URL]) )
{
	$topic_id = (isset($HTTP_POST_VARS[POST_TOPIC_URL])) ? $HTTP_POST_VARS[POST_TOPIC_URL] : $HTTP_GET_VARS[POST_TOPIC_URL];
}
else
{
	$topic_id = "";
}

$mode = (isset($HTTP_POST_VARS['mode'])) ? $HTTP_POST_VARS['mode'] : ( (isset($HTTP_GET_VARS['mode'])) ? $HTTP_GET_VARS['mode'] : "");

$disable_html = (isset($HTTP_POST_VARS['disable_html'])) ? $HTTP_POST_VARS['disable_html'] : !$userdata['user_allowhtml'];
$disable_bbcode = (isset($HTTP_POST_VARS['disable_bbcode'])) ? $HTTP_POST_VARS['disable_bbcode'] : !$userdata['user_allowbbcode'];
$disable_smilies = (isset($HTTP_POST_VARS['disable_smile'])) ? $HTTP_POST_VARS['disable_smile'] : !$userdata['user_allowsmile'];
$attach_sig = (isset($HTTP_POST_VARS['attach_sig'])) ? $HTTP_POST_VARS['attach_sig'] : $userdata['user_attachsig'];

$notify = (isset($HTTP_POST_VARS['notify'])) ? $HTTP_POST_VARS['notify'] : $userdata["always_notify"];
$preview = (isset($HTTP_POST_VARS['preview'])) ? TRUE : FALSE;

if( isset($HTTP_POST_VARS['topictype']) )
{
	if($HTTP_POST_VARS['topictype']  == "announce")
	{
		$topic_type = POST_ANNOUNCE;
	}
	else if($HTTP_POST_VARS['topictype'] == "sticky")
	{
		$topic_type = POST_STICKY;
	}
	else
	{
		$topic_type = POST_NORMAL;
	}
}
else
{
	$topic_type = POST_NORMAL;
}
//
// Here we do various lookups to find topic_id, forum_id, post_id
// etc. Doing it here prevents spoofing (eg. faking forum_id, 
// topic_id or post_id). 
//
if( $mode != "newtopic" )
{
	if($mode == "reply" || $mode == "quote")
	{
		if($mode == "reply" && !empty($topic_id) )
		{
			$sql = "SELECT forum_id, topic_status   
				FROM " . TOPICS_TABLE . " t 
				WHERE topic_id = $topic_id";

			$msg = $lang['No_topic_id'];
		}
		else if( !empty($post_id) )
		{
			$sql = "SELECT t.topic_id, t.forum_id, t.topic_status   
				FROM " . POSTS_TABLE . " p, " . TOPICS_TABLE . " t 
				WHERE p.post_id = $post_id 
					AND t.topic_id = p.topic_id";

			$msg = $lang['No_post_id'];
		}
		else
		{
			message_die(GENERAL_MESSAGE, $msg);
		}
	}
	else if($mode == "editpost")
	{
		if( isset($post_id) )
		{
			$sql = "SELECT p.post_id, t.forum_id, t.topic_status, t.topic_last_post_id, f.forum_last_post_id     
				FROM " . POSTS_TABLE . " p, " . TOPICS_TABLE . " t, " . FORUMS_TABLE . " f  
				WHERE t.topic_id = $topic_id 
					AND p.topic_id = t.topic_id 
					AND f.forum_id = t.forum_id 
				ORDER BY p.post_time ASC 
				LIMIT 1";
		}
		else
		{
			message_die(GENERAL_MESSAGE, $lang['No_post_id']);
		}
	}
	else
	{
		message_die(GENERAL_MESSAGE, $lang['No_valid_mode']);
	}

	if($result = $db->sql_query($sql))
	{
		$check_row = $db->sql_fetchrow($result);

		$forum_id = $check_row['forum_id'];
		$topic_status = $check_row['topic_status']; 

		if( $mode == "editpost" )
		{
			$is_first_post = ($check_row['post_id'] == $post_id) ? TRUE : FALSE; 
			$is_last_post = ($check_row['topic_last_post_id'] == $post_id) ? TRUE : FALSE; 
			$is_last_post_forum = ($check_row['forum_last_post_id'] == $post_id) ? TRUE : FALSE; 
		}
		else
		{
			if($mode == "quote")
			{
				$topic_id = $check_row['topic_id'];
			}

			$is_first_post = FALSE;
			$is_last_post = FALSE;
		}
	}
	else
	{
		message_die(GENERAL_ERROR, $lang['No_such_post'], "", __LINE__, __FILE__, $sql);
	}
}
else
{
	$is_first_post = TRUE;
	$is_last_post = FALSE;
	$topic_status = TOPIC_UNLOCKED;
}

//
// Is topic locked?
//
if($topic_status == TOPIC_LOCKED)
{
	message_die(GENERAL_MESSAGE, $lang['Topic_locked']);
}

//
// Auth checks
//
switch($mode)
{
	case 'newtopic':
		if($topic_type == POST_ANNOUNCE)
		{
			$auth_type = AUTH_ANNOUNCE;
			$is_auth_type = "auth_announce";
			$auth_string = $lang['can_post_announcements'];
		}
		else if($topic_type == POST_STICKY)
		{
			$auth_type = AUTH_STICKY;
			$is_auth_type = "auth_sticky";
			$auth_string = $lang['can_post_sticky_topics'];
		}
		else
		{
			$auth_type = AUTH_ALL;
			$is_auth_type = "auth_post";
			$auth_string = $lang['can_post_new_topics'];
		}
		break;
	case 'reply':
		$auth_type = AUTH_ALL;
		$is_auth_type = "auth_reply";
		$auth_string = $lang['can_reply_to_topics'];
		break;
	case 'quote':
		$auth_type = AUTH_ALL;
		$is_auth_type = "auth_reply";
		$auth_string = $lang['can_reply_to_topics'];
		break;
	case 'editpost':
		$auth_type = AUTH_ALL;
		$is_auth_type = "auth_edit";
		$auth_string = $lang['can_edit_topics'];
		break;
	case 'delete':
		$auth_type = AUTH_DELETE;
		$is_auth_type = "auth_delete";
		$auth_string = $lang['can_delete_topics'];
		break;
	default:
		$auth_type = AUTH_ALL;
		$is_auth_type = "auth_all";
		$auth_string = $lang['can_post_new_topics'];
		break;
}

$is_auth = auth($auth_type, $forum_id, $userdata);

if(!$is_auth[$is_auth_type])
{
	//
	// The user is not authed
	//
	if(!$userdata['session_logged_in'])
	{
		if($mode == "newtopic")
		{
			$redirect = "mode=newtopic&" . POST_FORUM_URL . "=$forum_id";
		}
		else if($mode == "reply")
		{
			$redirect = "mode=reply&" . POST_TOPIC_URL . "=$topic_id";
		}
		else if($mode == "quote")
		{
			$redirect = "mode=quote&" . POST_POST_URL ."=$post_id";
		}
		else if($mode == "editpost")
		{
			$redirect = "mode=editpost&" . POST_POST_URL ."=$post_id&" . POST_TOPIC_URL . "=$topic_id";
		}

		header("Location: login.$phpEx?forward_page=posting.$phpEx&" . $redirect);

	}
	else
	{
		$msg = $lang['Sorry_auth'] . $is_auth[$is_auth_type . "_type"] . $auth_string . $lang['this_forum'];
	}

	message_die(GENERAL_MESSAGE, $msg);
}
//
// End Auth
//

//
// Clear error check
//
$error = FALSE;
$error_msg = "";

//
// Prepare our message and subject on a 'submit'
//
if( ( isset($HTTP_POST_VARS['submit']) || $preview ) && $topic_status == TOPIC_UNLOCKED )
{

	//
	// Flood control
	//
	if($mode != 'editpost' && !$preview)
	{
		$sql = "SELECT MAX(post_time) AS last_post_time
			FROM " . POSTS_TABLE . "
			WHERE poster_ip = '$user_ip'";
		if($result = $db->sql_query($sql))
		{
			$db_row = $db->sql_fetchrow($result);

			$last_post_time = $db_row['last_post_time'];
			$current_time = get_gmt_ts();

			if(($current_time - $last_post_time) < $board_config['flood_interval'])
			{
				$error = TRUE;
				$error_msg = $lang['Flood_Error'];
			}
		}
	}
	//
	// End Flood control
	//

	//
	// Handle anon posting with usernames
	//
	if(isset($HTTP_POST_VARS['username']))
	{
		$username = trim(strip_tags(htmlspecialchars(stripslashes($HTTP_POST_VARS['username']))));
		if(!validate_username($username))
		{
			$error = TRUE;
			if(!empty($error_msg))
			{
				$error_msg .= "<br />";
			}
			$error_msg .= $lang['Bad_username'];
		}
	}
	else
	{
		$username = "";
	}

	$subject = trim(strip_tags(htmlspecialchars(stripslashes($HTTP_POST_VARS['subject']))));
	if($mode == 'newtopic' && empty($subject))
	{
		$error = TRUE;
		if(!empty($error_msg))
		{
			$error_msg .= "<br />";
		}
		$error_msg .= $lang['Empty_subject'];
	}

	//
	// You can't make it both an annoumcement and a stick topic
	//
	if($annouce && $sticky)
	{
		$error = TRUE;
		if(!empty($error_msg))
		{
			$error_msg .= "<br />";
		}
		$error_msg .= $lang['Annouce_and_sticky'];
	}

	if(!empty($HTTP_POST_VARS['message']))
	{
		if(!$error && !$preview)
		{
			$smile_on = ($disable_smilies) ? FALSE : TRUE;
			$html_on = ($disable_html) ? FALSE : TRUE;

			if($disable_bbcode)
			{
				$bbcode_on = FALSE;
			}
			else
			{
				$bbcode_uid = make_bbcode_uid();
				$bbcode_on = TRUE;
			}

			$message = prepare_message(stripslashes($HTTP_POST_VARS['message']), $html_on, $bbcode_on, $smile_on, $bbcode_uid);

			if( $attach_sig )
			{
				$message .= (eregi(" $", $message)) ? "[addsig]" : " [addsig]";
			}
		}
		else
		{
			// do stripslashes incase magic_quotes is on.
			$message = stripslashes($HTTP_POST_VARS['message']);
		}
	}
	else
	{
		$error = TRUE;
		if(!empty($error_msg))
		{
			$error_msg .= "<br />";
		}
		$error_msg .= $lang['Empty_message'];
	}
}

//
// If submitted then update tables
// according to the mode
//
if( ($mode == "newtopic" || $mode == "reply") && $topic_status == TOPIC_UNLOCKED)
{
	$page_title = ($mode == "newtopic") ? " " . $lang['Post_new_topic'] : " " . $lang['Post_reply'];
	$section_title = ($mode == "newtopic") ? $lang['Post_new_topic_in'] : " " . $Lang['Post_reply_to'];

	if(isset($HTTP_POST_VARS['submit']) && !$error && !$preview)
	{
		$topic_time = get_gmt_ts();

		if($mode == "reply")
		{
			$new_topic_id = $topic_id;
		}
		else if($mode == "newtopic")
		{
			$topic_notify = ($HTTP_POST_VARS['notify']) ? 1 : 0;

			$sql  = "INSERT INTO " . TOPICS_TABLE . " (topic_title, topic_poster, topic_time, forum_id, topic_notify, topic_status, topic_type)
				VALUES ('$subject', " . $userdata['user_id'] . ", " . $topic_time . ", $forum_id, $topic_notify, " . TOPIC_UNLOCKED . ", $topic_type)";

			if($result = $db->sql_query($sql, BEGIN_TRANSACTION))
			{
				$new_topic_id = $db->sql_nextid();
			}
			else
			{
				message_die(GENERAL_ERROR, "Error inserting data into topics table", "", __LINE__, __FILE__, $sql);
			}
		}

		if($mode == "reply" || ( $mode == "newtopic" && $result ) )
		{
			$sql = "INSERT INTO " . POSTS_TABLE . " (topic_id, forum_id, poster_id, post_username, post_time, poster_ip, bbcode_uid) 
				VALUES ($new_topic_id, $forum_id, " . $userdata['user_id'] . ", '$username', $topic_time, '$user_ip', '$bbcode_uid')";
			if($mode == "reply")
			{
				$result = $db->sql_query($sql, BEGIN_TRANSACTION);
			}
			else
			{
				$result = $db->sql_query($sql);
			}

			if($result)
			{
				$new_post_id = $db->sql_nextid();

				$sql = "INSERT INTO " . POSTS_TEXT_TABLE . " (post_id, post_subject, post_text) 
					VALUES ($new_post_id, '$subject', '$message')";

				if($db->sql_query($sql))
				{
					$sql = "UPDATE " . TOPICS_TABLE . " 
						SET topic_last_post_id = $new_post_id";
					if($mode == "reply")
					{
						$sql .= ", topic_replies = topic_replies + 1 ";
					}
					$sql .= " WHERE topic_id = $new_topic_id";

					if($db->sql_query($sql))
					{
						$sql = "UPDATE " . FORUMS_TABLE . " 
							SET forum_last_post_id = $new_post_id, forum_posts = forum_posts + 1";
						if($mode == "newtopic")
						{
							$sql .= ", forum_topics = forum_topics + 1";
						}
						$sql .= " WHERE forum_id = $forum_id";

						if($db->sql_query($sql))
						{
							$sql = "UPDATE " . USERS_TABLE . " 
								SET user_posts = user_posts + 1 
								WHERE user_id = " . $userdata['user_id'];

							if($db->sql_query($sql, END_TRANSACTION))
							{
								setcookie('phpbb2_' . $forum_id . '_' . $new_topic_id, '', 0, $cookiepath, $cookiedomain, $cookiesecure);
								//
								// If we get here the post has been inserted successfully.
								//
								$msg = $lang['Stored'] . "<br /><br />" . $lang['Click'] . " <a href=\"" . append_sid("viewtopic.$phpEx?" . POST_POST_URL . "=$new_post_id") . "#$new_post_id\">" . $lang['Here'] . "</a> " . $lang['to_view_message'] . "<br /><br />" . $lang['Click'] . " <a href=\"" . append_sid("viewforum.$phpEx?" . POST_FORUM_URL . "=$forum_id") . "\">" . $lang['Here'] . "</a> ". $lang['to_return_forum'];

								message_die(GENERAL_MESSAGE, $msg);
							}
							else
							{
								message_die(GENERAL_ERROR, "Error updating users table", "", __LINE__, __FILE__, $sql);
							}
						}
						else
						{
							// Rollback ?
							message_die(GENERAL_ERROR, "Error updating forums table", "", __LINE__, __FILE__, $sql);
						}
					}
					else
					{
						// Rollback ?
						message_die(GENERAL_ERROR, "Error updating topics table", "", __LINE__, __FILE__, $sql);
					}
				}
				else
				{
					// Rollback ?
					message_die(GENERAL_ERROR, "Error inserting data into posts text table", "", __LINE__, __FILE__, $sql);
				}
			}
			else
			{
				// Rollback ?
				message_die(GENERAL_ERROR, "Error inserting data into posts table", "", __LINE__, __FILE__, $sql);
			}
		}
	}
}
else if($mode == "quote" && !$preview && $topic_status == TOPIC_UNLOCKED)
{
	$page_title = " " . $lang['Post_reply'];
	$section_title = " " . $Lang['Post_reply_to'];

	if( isset($post_id) )
	{
		$sql = "SELECT p.*, pt.post_text, pt.post_subject, u.username, u.user_id, u.user_sig, t.topic_title, t.topic_notify, t.topic_type 
			FROM " . POSTS_TABLE . " p, " . USERS_TABLE . " u, " . TOPICS_TABLE . " t, " . POSTS_TEXT_TABLE . " pt 
			WHERE p.post_id = $post_id 
				AND pt.post_id = p.post_id 
				AND p.topic_id = t.topic_id 
				AND p.poster_id = u.user_id";
		if($result = $db->sql_query($sql))
		{
			$postrow = $db->sql_fetchrow($result);

			$poster = stripslashes(trim($postrow['username']));
			$subject = stripslashes(trim($postrow['post_subject']));
			$message = stripslashes(trim($postrow['post_text']));
			if(eregi("\[addsig]$", $message))
			{
				$attach_sig = TRUE;
			}
			$message = eregi_replace("\[addsig]$", "", $message);

			// Removes UID from BBCode entries
			$message = preg_replace("/\:[0-9a-z\:]*?\]/si", "]", $message);

			// This has not been implemented yet!
			//$message = desmile($message);

			$message = str_replace("<br />", "\n", $message);

			$message = undo_htmlspecialchars($message);
				
			// Special handling for </textarea> tags in the message, which can break the editing form..
			$message = preg_replace('#</textarea>#si', '&lt;/TEXTAREA&gt;', $message);

			$msg_date =  create_date($board_config['default_dateformat'], $postrow['post_time'], $board_config['default_timezone']);

			$message = "On " . $msg_date . " " . $poster . " wrote:\n\n[quote]\n" . $message . "\n[/quote]";

		}
		else
		{
			message_die(GENERAL_ERROR, "Couldn't obtain post and post text", "", __LINE__, __FILE__, $sql);
		}
	}
	else
	{
		message_die(GENERAL_MESSAGE, $lang['No_such_post']);
	}
}
else if( $mode == "editpost" && $topic_status == TOPIC_UNLOCKED )
{
	$page_title = " " . $lang['Edit_post'];
	$section_title = $lang['Edit_post_in'];

	if( ( isset($HTTP_POST_VARS['submit']) || isset($HTTP_GET_VARS['confirm']) || isset($HTTP_POST_VARS['confirm']) ) && 
		!$error && !$preview )
	{
		
		$sql = "SELECT poster_id  
			FROM " . POSTS_TABLE . " 
			WHERE post_id = $post_id";

		if($result = $db->sql_query($sql))
		{
			list($check_user_id) = $db->sql_fetchrow($result);

			if($userdata['user_id'] != $check_user_id && !$is_auth['auth_mod'])
			{
				$msg = ( isset($HTTP_POST_VARS['delete']) || isset($HTTP_GET_VARS['delete']) ) ? $lang['Sorry_delete_own_posts'] : $lang['Sorry_edit_own_posts'];

				message_die(GENERAL_MESSAGE, $msg);
			}
		}
		
		if( ( isset($HTTP_POST_VARS['delete']) || isset($HTTP_GET_VARS['delete']) ) && 
			( $is_last_post || $is_auth['auth_mod'] ) )
		{
			// 
			// Output a confirmation message, unless we've over-ridden it on the posting_body form (
			// override_confirm set ), this is so people can implement JavaScript checkers if they wish
			//
			if( isset($HTTP_POST_VARS['delete']) && 
				!isset($HTTP_POST_VARS['override_confirm']) && 
				!isset($HTTP_GET_VARS['confirm']) && !isset($HTTP_POST_VARS['confirm']))
			{

				$s_hidden_fields = '<input type="hidden" name="mode" value="' . $mode . '"><input type="hidden" name="' . POST_TOPIC_URL . '" value="'. $topic_id . '"><input type="hidden" name="' . POST_POST_URL . '" value="' . $post_id . '"><input type="hidden" name="delete" value="true">';

				//
				// Output confirmation page
				//
				include($phpbb_root_path . 'includes/page_header.'.$phpEx);

				$template->set_filenames(array(
					"confirm_body" => "confirm_body.tpl")
				);
				$template->assign_vars(array(
					"MESSAGE_TITLE" => $lang['Information'],
					"MESSAGE_TEXT" => $lang['Confirm_delete'], 

					"L_YES" => $lang['Yes'], 
					"L_NO" => $lang['No'], 
					
					"S_CONFIRM_ACTION" => append_sid("posting.$phpEx"), 
					"S_HIDDEN_FIELDS" => $s_hidden_fields)
				);
				$template->pparse("confirm_body");

				include($phpbb_root_path . 'includes/page_tail.'.$phpEx);

			}
			else if( isset($HTTP_GET_VARS['confirm']) || isset($HTTP_POST_VARS['confirm']) || 
				isset($HTTP_POST_VARS['override_confirm']) )
			{
				
				$sql = "DELETE FROM " . POSTS_TEXT_TABLE . " 
					WHERE post_id = $post_id";

				if($db->sql_query($sql, BEGIN_TRANSACTION))
				{
					$sql = "DELETE FROM " . POSTS_TABLE . " 
						WHERE post_id = $post_id";

					if($is_last_post && $is_first_post)
					{
						//
						// Delete the topic completely, updating the forum_last_post_id
						// if necessary
						//
						if($db->sql_query($sql))
						{
							$sql = "DELETE FROM " . TOPICS_TABLE . " 
								WHERE topic_id = $topic_id";

							$sql_forum_upd = "forum_posts = forum_posts - 1, forum_topics = forum_topics - 1"; 

							$if_die_msg = "Couldn't delete from topics table";
						}
						else
						{
							// Rollback ?
							message_die(GENERAL_ERROR, "Error deleting from post  table", "", __LINE__, __FILE__, $sql);
						}
					}
					else if($is_last_post)
					{
						//
						// Delete the post and update the _last_post_id's of both
						// the topic and forum if necessary
						//
						if($db->sql_query($sql))
						{
							$sql = "SELECT MAX(post_id) AS new_last_post_id 
								FROM " . POSTS_TABLE . " 
								WHERE topic_id = $topic_id";
						
							if($result = $db->sql_query($sql))
							{
								list($new_last_post_id) = $db->sql_fetchrow($result);

								$sql = "UPDATE " . TOPICS_TABLE . " 
									SET topic_replies = topic_replies - 1, topic_last_post_id = $new_last_post_id 
									WHERE topic_id = $topic_id";

								$sql_forum_upd = "forum_posts = forum_posts - 1";

								$if_die_msg = "Error updating topics table";
							}
							else
							{
								// Rollback ?
								message_die(GENERAL_ERROR, "Error obtaining new last topic id", "", __LINE__, __FILE__, $sql);
							}
						}
						else
						{
							// Rollback ?
							message_die(GENERAL_ERROR, "Error deleting from post table", "", __LINE__, __FILE__, $sql);
						}
					}
					else if($is_auth['auth_mod']) 
					{
						//
						// It's not last and it's not both first and last so it's somewhere in
						// the middle(!) Only moderators can delete these posts, all we need do
						// is update the forums table data as necessary
						//
						$sql_forum_upd = "forum_posts = forum_posts - 1";

						$if_die_msg = "Couldn't delete from posts table";
					}
								
					//
					// Updating the forum is common to all three possibilities,
					// _remember_ we're still in a transaction here!
					//
					if($db->sql_query($sql))
					{
						if($is_last_post_forum)
						{
							$sql = "SELECT MAX(post_id) AS new_last_post_id 
								FROM " . POSTS_TABLE . " 
								WHERE forum_id = $forum_id";
					
							if($result = $db->sql_query($sql))
							{
								list($new_last_post_id) = $db->sql_fetchrow($result);
							}
							else
							{
								message_die(GENERAL_ERROR, "Couldn't obtain new last post id for the forum", "", __LINE__, __FILE__, $sql);
							}

							$new_last_sql = ", forum_last_post_id = $new_last_post_id";
						}
						else
						{
							$new_last_sql = "";
						}

						$sql = "UPDATE " . FORUMS_TABLE . " 
							SET " . $sql_forum_upd . $new_last_sql . " 
							WHERE forum_id = $forum_id";

						if($db->sql_query($sql, END_TRANSACTION))
						{
							//
							// If we get here the post has been deleted successfully.
							//
							$msg = $lang['Deleted'];
								
							if(!$is_last_post || !$is_first_post)
							{
								$msg .= "<br /><br />" . $lang['Click'] . " <a href=\"" . append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id") . "\">" . $lang['Here'] . "</a> " . $lang['to_return_topic'];
							}
							$msg .= "<br /><br />" . $lang['Click'] . " <a href=\"" . append_sid("viewforum.$phpEx?" . POST_FORUM_URL . "=$forum_id") . "\">" . $lang['Here'] . "</a> ". $lang['to_return_forum'];

							message_die(GENERAL_MESSAGE, $msg);
						}
						else
						{
							// Rollback ?
							message_die(GENERAL_ERROR, "Error updating forums table", "", __LINE__, __FILE__, $sql);
						}
					}
					else
					{
						//
						// This error is produced by the last SQL query carried out
						// before we jumped into this common block
						//
						// Rollback ?
						message_die(GENERAL_ERROR, $if_die_msg, "", __LINE__, __FILE__, $sql);
					}
				}
				else
				{
					// Rollback ?
					message_die(GENERAL_ERROR, "Error deleting from posts text table", "", __LINE__, __FILE__, $sql);
				}
			}
			else
			{
				//
				// No action matched so return to viewtopic, should be fine for URL based
				// confirmations
				//
				header("Location: viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id");
			}
		}
		else
		{
			if( !$is_last_post && ( !$is_auth['auth_mod'] || $check_user_id == $userdata['user_id'] ) )
			{
				$edited_sql = ", post_edit_time = " . time() . ", post_edit_count = post_edit_count + 1 ";
			}
			else
			{
				$edited_sql = "";
			}

			$sql = "UPDATE " . POSTS_TABLE . " 
				SET bbcode_uid = '$bbcode_uid'" . $edited_sql . "  
				WHERE post_id = $post_id";

			if($db->sql_query($sql, BEGIN_TRANSACTION))
			{
				$sql = "UPDATE " . POSTS_TEXT_TABLE . " 
					SET post_text = '$message', post_subject = '$subject' 
					WHERE post_id = $post_id";

				if($is_first_post)
				{
					if($db->sql_query($sql))
					{
						//
						// Update topics table here, set notification level and such
						//
						$sql = "UPDATE " . TOPICS_TABLE . " 
							SET topic_title = '$subject', topic_notify = '$notify', topic_type = '".$topic_type."' 
							WHERE topic_id = $topic_id";

						if($db->sql_query($sql, END_TRANSACTION))
						{
							//
							// If we get here the post has been inserted successfully.
							//
							$msg = $lang['Stored'] . "<br /><br />" . $lang['Click'] . " <a href=\"" . append_sid("viewtopic.$phpEx?" . POST_POST_URL . "=$post_id") . "#$post_id\">" . $lang['Here'] . "</a> " . $lang['to_view_message'] . "<br /><br />" . $lang['Click'] . " <a href=\"" . append_sid("viewforum.$phpEx?" . POST_FORUM_URL . "=$forum_id") . "\">" . $lang['Here'] . "</a> ". $lang['to_return_forum'];

							message_die(GENERAL_MESSAGE, $msg);
						}
						else
						{
							message_die(GENERAL_ERROR, "Updating topics table", "", __LINE__, __FILE__, $sql);
						}
					}
				}
				else
				{
					if($db->sql_query($sql, END_TRANSACTION))
					{
						//
						// If we get here the post has been inserted successfully.
						//
						$msg = $lang['Stored'] . "<br /><br />" . $lang['Click'] . " <a href=\"" . append_sid("viewtopic.$phpEx?" . POST_POST_URL . "=$post_id") . "#$post_id\">" . $lang['Here'] . "</a> " . $lang['to_view_message'] . "<br /><br />" . $lang['Click'] . " <a href=\"" . append_sid("viewforum.$phpEx?" . POST_FORUM_URL . "=$forum_id") . "\">" . $lang['Here'] . "</a> ". $lang['to_return_forum'];

						message_die(GENERAL_MESSAGE, $msg);
					}
					else
					{
						message_die(GENERAL_ERROR, "Error updating posts text table", "", __LINE__, __FILE__, $sql);
					}
				}
			}
			else
			{
				message_die(GENERAL_ERROR, "Error updating posts text table", "", __LINE__, __FILE__, $sql);
			}
		}
	}
	else if( isset($HTTP_GET_VARS['not_confirm']) || isset($HTTP_POST_VARS['not_confirm']) )
	{

		//
		// Cancelled a confirmation, just to viewtopic
		//
		header("Location: viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id");

	}
	else if(!$preview)
	{
		if( !empty($post_id) )
		{
   			$sql = "SELECT p.*, pt.post_text, pt.post_subject, u.username, u.user_id, u.user_sig, t.topic_title, t.topic_notify, t.topic_type 
				FROM " . POSTS_TABLE . " p, " . USERS_TABLE . " u, " . TOPICS_TABLE . " t, " . POSTS_TEXT_TABLE . " pt 
				WHERE p.post_id = $post_id 
					AND pt.post_id = p.post_id 
					AND p.topic_id = t.topic_id 
					AND p.poster_id = u.user_id";

			if($result = $db->sql_query($sql))
			{
				$postrow = $db->sql_fetchrow($result);

				if($userdata['user_id'] != $postrow['user_id'] && !$is_auth['auth_mod'])
				{
					message_die(GENERAL_MESSAGE, $lang['Sorry_edit_own_posts']);
				}

				$subject = stripslashes(trim($postrow['post_subject']));
				$message = stripslashes(trim($postrow['post_text']));

				if(eregi("\[addsig]$", $message))
				{
					$message = eregi_replace("\[addsig]$", "", $message);
					$attach_sig = TRUE;
				}
				else
				{
					$attach_sig = FALSE;
				}

				// Removes UID from BBCode entries
				$message = preg_replace("/\:[0-9a-z\:]*?\]/si", "]", $message);

				// This has not been implemented yet!
				//$message = desmile($message);

				$message = str_replace("<br />", "\n", $message);

   				$message = undo_htmlspecialchars($message);
				
				// Special handling for </textarea> tags in the message, which can break the editing form..
				$message = preg_replace('#</textarea>#si', '&lt;/TEXTAREA&gt;', $message);

				if($is_first_post)
				{
					$notify_show = TRUE;
					if($postrow['topic_notify'])
					{
						$notify = TRUE;
					}
					$subject = stripslashes($postrow['topic_title']);

					switch($postrow['topic_type'])
					{
						case POST_ANNOUNCE:
							$is_announce = TRUE;
							break;

						case POST_STICKY:
							$is_sticky = TRUE;
							break;
					}
				}
			}
   		}
		else
		{
			message_die(GENERAL_MESSAGE, $lang['No_such_post']);
   		}
	}
}// end if ... mode

//
// Output page
//
include($phpbb_root_path . 'includes/page_header.'.$phpEx);

//
// Start: Error handling
//
if($error)
{
	$template->set_filenames(array(
		"reg_header" => "error_body.tpl")
	);
	$template->assign_vars(array(
		"ERROR_MESSAGE" => $error_msg)
	);
	$template->pparse("reg_header");
}
//
// End: error handling
//

if(empty($username))
{
	$username = $userdata['username'];
}

//
// Start: Preview Post
//
if($preview && !$error)
{
	switch($topic_type)
	{
		case POST_ANNOUNCE:
			$is_announce = TRUE;
			break;

		case POST_STICKY:
			$is_sticky = TRUE;
			break;
	}

	$preview_message = $message;
	$bbcode_uid = make_bbcode_uid();
	$preview_message = prepare_message($preview_message, TRUE, TRUE, TRUE, $bbcode_uid);
	$preview_message = bbencode_second_pass($preview_message, $bbcode_uid);
	$preview_message = make_clickable($preview_message);

	$template->set_filenames(array(
		"preview" => "posting_preview.tpl")
	);
	$template->assign_vars(array(
		"TOPIC_TITLE" => $subject, 
		"POST_SUBJECT" => $subject, 
		"ROW_COLOR" => "#" . $theme['td_color1'],
		"POSTER_NAME" => $username,
		"POST_DATE" => create_date($board_config['default_dateformat'], time(), $board_config['default_timezone']),
		"MESSAGE" => stripslashes(nl2br($preview_message)),
		
		"L_PREVIEW" => $lang['Preview'],
		"L_POSTED" => $lang['Posted'])
	);
	$template->pparse("preview");
}
//
// End: Preview Post
//

//
// Show the same form for each mode.
//
if( empty($forum_id) )
{
	message_die(GENERAL_ERROR, $lang['Forum_not_exist']);
}

$sql = "SELECT forum_name
		FROM " . FORUMS_TABLE . "
		WHERE forum_id = $forum_id";
if(!$result = $db->sql_query($sql))
{
	message_die(GENERAL_ERROR, "Could not obtain forum information.", "", __LINE__, __FILE__, $sql);
}
$forum_info = $db->sql_fetchrow($result);
$forum_name = stripslashes($forum_info['forum_name']);

$template->set_filenames(array(
	"body" => "posting_body.tpl",
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

	"L_POSTNEWIN" => $section_title,

	"U_VIEW_FORUM" => append_sid("viewforum.$phpEx?" . POST_FORUM_URL . "=$forum_id"))
);

if($userdata['session_logged_in'])
{
	$username_input = $userdata["username"];
	$password_input = "";
}
else
{
	$username_input = '<input type="text" name="username" value="' . $username . '" size="25" maxlength="50">';
	$password_input = '<input type="password" name="password" size="25" maxlenght="40">';
}
$subject_input = '<input type="text" name="subject" value="'.$subject.'" size="50" maxlength="255">';
$message_input = '<textarea name="message" rows="10" cols="40" wrap="virtual">'.$message.'</textarea>';

if($board_config['allow_html'])
{
	$html_status = $lang['ON'];
	$html_toggle = '<input type="checkbox" name="disable_html" ';
	if($disable_html)
	{
		$html_toggle .= 'checked';
	}
	$html_toggle .= "> " . $lang['Disable'] . $lang['HTML'] . $lang['in_this_post'];
}
else
{
	$html_status = $lang['OFF'];
}

if($board_config['allow_bbcode'])
{
	$bbcode_status = $lang['ON'];
	$bbcode_toggle = '<input type="checkbox" name="disable_bbcode" ';
	if($disable_bbcode)
	{
		$bbcode_toggle .= "checked";
	}
	$bbcode_toggle .= "> " . $lang['Disable'] . $lang['BBCode'] . $lang['in_this_post'];
}
else
{
	$bbcode_status = $lang['OFF'];
}

if($board_config['allow_smilies'])
{
	$smilies_status = $lang['ON'];
	$smile_toggle = '<input type="checkbox" name="disable_smile" ';
	if($disable_smilies)
	{
		$smile_toggle .= "checked";
	}
	$smile_toggle .= "> " . $lang['Disable'] . $lang['Smilies'] . $lang['in_this_post'];
}
else
{
	$smilies_status = $lang['OFF'];
}


$sig_toggle = '<input type="checkbox" name="attach_sig" ';
if($attach_sig)
{
	$sig_toggle .= "checked";
}
$sig_toggle .= "> " . $lang['Attach_signature'];

$topic_type_radio = '';
if($mode == 'newtopic' || ( $mode == 'editpost' && $is_first_post ) )
{
	if($is_auth['auth_announce'])
	{
		$announce_toggle = '<input type="radio" name="topictype" value="announce"';
		if($is_announce)
		{
			$announce_toggle .= ' checked';
		}
		$announce_toggle .= '> ' . $lang['Post_Annoucement'] . '&nbsp;&nbsp;';
	}

	if($is_auth['auth_sticky'])
	{
		$sticky_toggle = '<input type="radio" name="topictype" value="sticky"';
		if($is_sticky)
		{
			$sticky_toggle .= ' checked';
		}
		$sticky_toggle .= '> ' . $lang['Post_Sticky'] . '&nbsp;&nbsp;';
	}

	if( $is_auth['auth_announce'] || $is_auth['auth_sticky'] )
	{
		$topic_type_toggle = '&nbsp;' . $lang['Post_topic_as'] . ': <input type="radio" name="topictype" value="normal"';
		if(!$is_announce && !$is_sticky)
		{
			$topic_type_toggle .= ' checked';
		}
		$topic_type_toggle .= '> ' . $lang['Post_Normal'] . '&nbsp;&nbsp;' . $sticky_toggle . $announce_toggle;
	}
}

if($mode == "newtopic" || ($mode == "editpost" && $notify_show))
{
	$notify_toggle = '<input type="checkbox" name="notify" ';
	if($notify)
	{
		$notify_toggle .= "checked";
	}
	$notify_toggle .= "> " . $lang['Notify'];
}

//
// Display delete toggle?
//
if($mode == 'editpost' && ( $is_last_post || $is_auth['auth_mod'] ) )
{
	$delete_toggle = '<input type="checkbox" name="delete"> ' . $lang['Delete_post'];
}

//
// Define hidden fields
//
$hidden_form_fields = "";
if($mode == "newtopic")
{
	$hidden_form_fields .= "<input type=\"hidden\" name=\"" . POST_FORUM_URL . "\" value=\"$forum_id\">";
}
else if($mode == "reply" || $mode == "quote")
{
	//
	// Reset mode to reply if quote is in effect
	// to allow proper handling by submit/preview
	//
	$mode = "reply";
	$hidden_form_fields .= "<input type=\"hidden\" name=\"" . POST_TOPIC_URL . "\" value=\"$topic_id\">";
}
else if($mode == "editpost")
{
	$hidden_form_fields .= "<input type=\"hidden\" name=\"" . POST_TOPIC_URL . "\" value=\"$topic_id\"><input type=\"hidden\" name=\"" . POST_POST_URL . "\" value=\"$post_id\">";
}
$hidden_form_fields .= "<input type=\"hidden\" name=\"mode\" value=\"$mode\">";

if($mode == "newtopic")
{
	$post_a = $lang['Post_a_new_topic'];
}
else if($mode == "reply")
{
	$post_a = $lang['Post_a_reply'];
}
else if($mode == "editpost")
{
	$post_a = $lang['Edit_Post'];
}
		
$template->assign_vars(array(
	"USERNAME_INPUT" => $username_input,
	"PASSWORD_INPUT" => $password_input,
	"SUBJECT_INPUT" => $subject_input,
	"MESSAGE_INPUT" => $message_input,
	"HTML_STATUS" => $html_status,
	"HTML_TOGGLE" => $html_toggle,
	"SMILIES_STATUS" => $smilies_status, 
	"SMILE_TOGGLE" => $smile_toggle,
	"SIG_TOGGLE" => $sig_toggle,
	"NOTIFY_TOGGLE" => $notify_toggle,
	"DELETE_TOGGLE" => $delete_toggle,
	"TYPE_TOGGLE" => $topic_type_toggle,
	"BBCODE_TOGGLE" => $bbcode_toggle,
	"BBCODE_STATUS" => $bbcode_status,

	"L_SUBJECT" => $lang['Subject'],
	"L_MESSAGE_BODY" => $lang['Message_body'],
	"L_OPTIONS" => $lang['Options'],
	"L_PREVIEW" => $lang['Preview'],
	"L_SUBMIT" => $lang['Submit_post'],
	"L_CANCEL" => $lang['Cancel_post'], 
	"L_CONFIRM_DELETE" => $lang['Confirm_delete'], 
	"L_POST_A" => $post_a,
	"L_HTML_IS" => $lang['HTML'] . " " . $lang['is'],
	"L_BBCODE_IS" => $lang['BBCode'] . " " . $lang['is'],
	"L_SMILIES_ARE" => $lang['Smilies'] . " " . $lang['are'],

	"S_TOPIC_ID" => $topic_id, 

	"S_POST_ACTION" => append_sid("posting.$phpEx"),
	"S_HIDDEN_FORM_FIELDS" => $hidden_form_fields)
);

$template->pparse("body");

include($phpbb_root_path . 'includes/page_tail.'.$phpEx);

?>