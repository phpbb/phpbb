<?php
/***************************************************************************
 *                                posting.php
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

//
// Set toggles for various options
//
if(!$board_config['allow_html'])
{
	$html_on = 0;
}
else
{
	$html_on = ( isset($HTTP_POST_VARS['submit']) || isset($HTTP_POST_VARS['preview']) ) ? ( ( !empty($HTTP_POST_VARS['disable_html']) ) ? 0 : TRUE ) : $userdata['user_allowhtml'];
}

if(!$board_config['allow_bbcode'])
{
	$bbcode_on = 0;
}
else
{
	$bbcode_on = ( isset($HTTP_POST_VARS['submit']) || isset($HTTP_POST_VARS['preview']) ) ? ( ( !empty($HTTP_POST_VARS['disable_bbcode']) ) ? 0 : TRUE ) : $userdata['user_allowbbcode'];
}

if(!$board_config['allow_smilies'])
{
	$smilies_on = 0;
}
else
{
	$smilies_on = ( isset($HTTP_POST_VARS['submit']) || isset($HTTP_POST_VARS['preview']) ) ? ( ( !empty($HTTP_POST_VARS['disable_smilies']) ) ? 0 : TRUE ) : $userdata['user_allowsmile'];
}

$attach_sig = ( isset($HTTP_POST_VARS['submit']) || isset($HTTP_POST_VARS['preview']) ) ? ( ( !empty($HTTP_POST_VARS['attach_sig']) ) ? TRUE : 0 ) : $userdata['user_attachsig'];

if($mode == "reply" && !empty($topic_id) )
{
	if( isset($HTTP_POST_VARS['submit']) || isset($HTTP_POST_VARS['preview']) )
	{
		$notify = ( !empty($HTTP_POST_VARS['notify']) ) ? TRUE : 0;
	}
	else
	{
		$sql = "SELECT *
			FROM " . TOPICS_WATCH_TABLE . "
			WHERE topic_id = $topic_id
				AND user_id = " . $userdata['user_id'];
		if( !$result = $db->sql_query($sql) )
		{
			message_die(GENERAL_ERROR, "Couldn't obtain topic watch information", "", __LINE__, __FILE__, $sql);
		}

		$notify = ( $db->sql_numrows($result)) ? TRUE : 0;
	}
}
else
{
	$notify = ( isset($HTTP_POST_VARS['submit']) || isset($HTTP_POST_VARS['preview']) ) ? ( ( !empty($HTTP_POST_VARS['notify']) ) ? TRUE : 0 ) : $userdata['user_notify'];
}

$preview = (isset($HTTP_POST_VARS['preview'])) ? TRUE : 0;

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
// Here we do various lookups to find topic_id, forum_id, post_id etc.
// Doing it here prevents spoofing (eg. faking forum_id, topic_id or post_id
//
if( $mode != "newtopic" )
{
	if($mode == "reply" || $mode == "quote")
	{
		if($mode == "reply" && !empty($topic_id) )
		{
			$sql = "SELECT f.forum_id, f.forum_status, t.topic_status
				FROM " . FORUMS_TABLE . " f, " . TOPICS_TABLE . " t
				WHERE t.topic_id = $topic_id
					AND f.forum_id = t.forum_id";

			$msg = $lang['No_topic_id'];
		}
		else if( !empty($post_id) )
		{
			$sql = "SELECT f.forum_id, f.forum_status, t.topic_id, t.topic_status
				FROM " . POSTS_TABLE . " p, " . TOPICS_TABLE . " t, " . FORUMS_TABLE . " f
				WHERE p.post_id = $post_id
					AND t.topic_id = p.topic_id
					AND f.forum_id = t.forum_id";

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
			$sql = "SELECT p.post_id, t.forum_id, t.topic_status, t.topic_last_post_id, f.forum_last_post_id, f.forum_status
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
		$forum_status = $check_row['forum_status'];

		if( $mode == "editpost" )
		{
			$is_first_post = ($check_row['post_id'] == $post_id) ? TRUE : 0;
			$is_last_post = ($check_row['topic_last_post_id'] == $post_id) ? TRUE : 0;
			$is_last_post_forum = ($check_row['forum_last_post_id'] == $post_id) ? TRUE : 0;
		}
		else
		{
			if($mode == "quote")
			{
				$topic_id = $check_row['topic_id'];
			}

			$is_first_post = 0;
			$is_last_post = 0;
		}
	}
	else
	{
		message_die(GENERAL_MESSAGE, $lang['No_such_post']);
	}
}
else
{
	$sql = "SELECT forum_status
		FROM " . FORUMS_TABLE . " f
		WHERE forum_id = $forum_id";
	if($result = $db->sql_query($sql))
	{
		$check_row = $db->sql_fetchrow($result);

		$is_first_post = TRUE;
		$is_last_post = 0;
		$topic_status = TOPIC_UNLOCKED;
		$forum_status = $check_row['forum_status'];
	}
	else
	{
		message_die(GENERAL_MESSAGE, $lang['Forum_not_exist']);
	}
}

//
// Is topic or forum locked?
//
if($forum_status == FORUM_LOCKED)
{
	message_die(GENERAL_MESSAGE, $lang['Forum_locked']);
}
else if($topic_status == TOPIC_LOCKED)
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
$error = 0;
$error_msg = "";

//
// Prepare our message and subject on a 'submit' (inc. preview)
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
		$username = trim(strip_tags(htmlspecialchars($HTTP_POST_VARS['username'])));
		if(!validate_username(stripslashes($username)))
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

	$subject = trim(strip_tags(htmlspecialchars($HTTP_POST_VARS['subject'])));
	if($mode == 'newtopic' && empty($subject))
	{
		$error = TRUE;
		if(!empty($error_msg))
		{
			$error_msg .= "<br />";
		}
		$error_msg .= $lang['Empty_subject'];
	}

	if(!empty($HTTP_POST_VARS['message']))
	{
		if(!$error && !$preview)
		{
			if($bbcode_on)
			{
				$bbcode_uid = make_bbcode_uid();
			}

			//
			// prepare_message returns a bbcode parsed html parsed and slashed result
			// ... note that we send NOT'ed version of the disable vars to the function
			//
			$message = prepare_message(stripslashes($HTTP_POST_VARS['message']), $html_on, $bbcode_on, $smilies_on, $bbcode_uid);

			if( $attach_sig )
			{
				$message .= (ereg(" $", $message)) ? "[addsig]" : " [addsig]";
			}
		}
		else
		{
			$message = stripslashes(trim($HTTP_POST_VARS['message']));
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
			$sql  = "INSERT INTO " . TOPICS_TABLE . " (topic_title, topic_poster, topic_time, forum_id, topic_status, topic_type)
				VALUES ('$subject', " . $userdata['user_id'] . ", " . $topic_time . ", $forum_id, " . TOPIC_UNLOCKED . ", $topic_type)";

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
			$sql = "INSERT INTO " . POSTS_TABLE . " (topic_id, forum_id, poster_id, post_username, post_time, poster_ip, bbcode_uid, enable_bbcode, enable_html, enable_smilies)
				VALUES ($new_topic_id, $forum_id, " . $userdata['user_id'] . ", '$username', $topic_time, '$user_ip', '$bbcode_uid', $bbcode_on, $html_on, $smilies_on)";
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
								setcookie('phpbb2_' . $forum_id . '_' . $new_topic_id, '', time() - 1, $cookiepath, $cookiedomain, $cookiesecure);

								//
								// Email users who are watching this topic
								//
								if($mode == "reply")
								{
									$sql = "SELECT u.user_id, u.username, u.user_email, t.topic_title
										FROM " . TOPICS_WATCH_TABLE . " tw, " . TOPICS_TABLE . " t, " . USERS_TABLE . " u
										WHERE tw.topic_id = $new_topic_id
											AND tw.user_id <> " . $userdata['user_id'] . "
											AND tw.user_id <> " . ANONYMOUS . "
											AND tw.notify_status = " . TOPIC_WATCH_UN_NOTIFIED . "
											AND t.topic_id = tw.topic_id
											AND u.user_id = tw.user_id";
									if( $result = $db->sql_query($sql) )
									{
										$email_set = $db->sql_fetchrowset($result);
										$update_watched_sql = "";

										for($i = 0; $i < count($email_set); $i++)
										{
											if($email_set[$i]['user_email'] != "")
											{
												$email_headers = "From: " . $board_config['board_email_from'] . "\nReturn-Path: " . $board_config['board_email_from'] . "\r\n";

												$emailer->use_template("topic_notify");
												$emailer->email_address($email_set[$i]['user_email']);
												$emailer->set_subject($lang['Topic_reply_notification']);
												$emailer->extra_headers($email_headers);

												$path = (dirname($HTTP_SERVER_VARS['REQUEST_URI']) == "/") ? "" : dirname($HTTP_SERVER_VARS['REQUEST_URI']);

												$emailer->assign_vars(array(
													"USERNAME" => $email_set[$i]['username'],
													"SITENAME" => $board_config['sitename'],
													"TOPIC_TITLE" => $email_set[$i]['topic_title'],
													"TOPIC_URL" => "http://" . $HTTP_SERVER_VARS['SERVER_NAME'] . $path . "/viewtopic.$phpEx?" . POST_POST_URL . "=$new_post_id#$new_post_id",
													"UN_WATCH_URL" => "http://" . $HTTP_SERVER_VARS['SERVER_NAME'] . $path . "/viewtopic.$phpEx?" . POST_TOPIC_URL . "=$new_topic_id&unwatch=topic",
													"EMAIL_SIG" => $board_config['board_email'])
												);

												$emailer->send();
												$emailer->reset();

												if($update_watched_sql != "")
												{
													$update_watched_sql .= " OR ";
												}
												$update_watched_sql .= "user_id = " . $email_set[$i]['user_id'];
											}
										}

										if($update_watched_sql != "")
										{
											$sql = "UPDATE " . TOPICS_WATCH_TABLE . "
												SET notify_status = " . TOPIC_WATCH_NOTIFIED . "
												WHERE topic_id = $new_topic_id
													AND $update_watched_sql";
											$db->sql_query($sql);
										}
									}
								}

								//
								// Handle notification request ... not complete
								// only fully functional for new posts
								//
								if( isset($notify) )
								{
									if($mode == "reply")
									{
										$sql = "SELECT *
											FROM " . TOPICS_WATCH_TABLE . "
											WHERE topic_id = $new_topic_id
												AND user_id = " . $userdata['user_id'];
										if( !$result = $db->sql_query($sql) )
										{
											message_die(GENERAL_ERROR, "Couldn't obtain topic watch information", "", __LINE__, __FILE__, $sql);
										}

										if( $db->sql_numrows($result))
										{
											if( !$notify )
											{
												$sql = "DELETE FROM " . TOPICS_WATCH_TABLE . "
													WHERE topic_id = $new_topic_id
														AND user_id = " . $userdata['user_id'];
												if( !$result = $db->sql_query($sql) )
												{
													message_die(GENERAL_ERROR, "Couldn't delete topic watch information", "", __LINE__, __FILE__, $sql);
												}
											}
										}
										else if( $notify )
										{
											$sql = "INSERT INTO " . TOPICS_WATCH_TABLE . " (user_id, topic_id, notify_status)
												VALUES (" . $userdata['user_id'] . ", $new_topic_id, 0)";
											if( !$result = $db->sql_query($sql) )
											{
												message_die(GENERAL_ERROR, "Couldn't insert topic watch information", "", __LINE__, __FILE__, $sql);
											}
										}
									}
									else if( $notify )
									{
										$sql = "INSERT INTO " . TOPICS_WATCH_TABLE . " (user_id, topic_id, notify_status)
											VALUES (" . $userdata['user_id'] . ", $new_topic_id, 0)";
										if( !$result = $db->sql_query($sql) )
										{
											message_die(GENERAL_ERROR, "Couldn't insert topic watch information", "", __LINE__, __FILE__, $sql);
										}
									}
								}

								//
								// If we get here the post has been inserted successfully.
								//
								$msg = $lang['Stored'] . "<br /><br />" . $lang['Click'] . " <a href=\"" . append_sid("viewtopic.$phpEx?" . POST_POST_URL . "=$new_post_id") . "#$new_post_id\">" . $lang['Here'] . "</a> " . $lang['to_view_message'] . "<br /><br />" . $lang['Click'] . " <a href=\"" . append_sid("viewforum.$phpEx?" . POST_FORUM_URL . "=$forum_id") . "\">" . $lang['Here'] . "</a> ". $lang['to_return_forum'];

								message_die(GENERAL_MESSAGE, $msg);
							}
							else
							{
								if(SQL_LAYER == "mysql")
								{
								}
								message_die(GENERAL_ERROR, "Error updating users table", "", __LINE__, __FILE__, $sql);
							}
						}
						else
						{
							if(SQL_LAYER == "mysql")
							{
							}
							// Rollback ?
							message_die(GENERAL_ERROR, "Error updating forums table", "", __LINE__, __FILE__, $sql);
						}
					}
					else
					{
						if(SQL_LAYER == "mysql")
						{
						}
						// Rollback ?
						message_die(GENERAL_ERROR, "Error updating topics table", "", __LINE__, __FILE__, $sql);
					}
				}
				else
				{
					if(SQL_LAYER == "mysql")
					{
						$sql = "DELETE FROM " . POSTS_TABLE . "
							WHERE post_id = $new_post_id";
						if( !$db->sql_query($sql) )
						{
							message_die(GENERAL_ERROR, "Error inserting data into posts text table and could not rollback", "", __LINE__, __FILE__, $sql);
						}
					}
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
		$sql = "SELECT p.*, pt.post_text, pt.post_subject, u.username, u.user_id, u.user_sig, t.topic_title, t.topic_type
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

				$s_hidden_fields = '<input type="hidden" name="mode" value="' . $mode . '" /><input type="hidden" name="' . POST_TOPIC_URL . '" value="'. $topic_id . '" /><input type="hidden" name="' . POST_POST_URL . '" value="' . $post_id . '" /><input type="hidden" name="delete" value="true" />';

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
							if(SQL_LAYER == "mysql")
							{
							}
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
								if(SQL_LAYER == "mysql")
								{
								}
								// Rollback ?
								message_die(GENERAL_ERROR, "Error obtaining new last topic id", "", __LINE__, __FILE__, $sql);
							}
						}
						else
						{
							if(SQL_LAYER == "mysql")
							{
							}
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
							if(SQL_LAYER == "mysql")
							{
							}
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
						if(SQL_LAYER == "mysql")
						{
						}
						// Rollback ?
						message_die(GENERAL_ERROR, $if_die_msg, "", __LINE__, __FILE__, $sql);
					}
				}
				else
				{
					if(SQL_LAYER == "mysql")
					{
					}
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
				SET bbcode_uid = '$bbcode_uid', enable_bbcode = $bbcode_on, enable_html = $html_on, enable_smilies = $smilies_on" . $edited_sql . "
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
							SET topic_title = '$subject', topic_type = '".$topic_type."'
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
							if(SQL_LAYER == "mysql")
							{
							}
							message_die(GENERAL_ERROR, "Updating topics table", "", __LINE__, __FILE__, $sql);
						}
					}
					else
					{
						if(SQL_LAYER == "mysql")
						{
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
						if(SQL_LAYER == "mysql")
						{
						}
						message_die(GENERAL_ERROR, "Error updating posts text table", "", __LINE__, __FILE__, $sql);
					}
				}
			}
			else
			{
				if(SQL_LAYER == "mysql")
				{
				}
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
	else
	{
		if( !empty($post_id) )
		{
   			$sql = "SELECT p.*, pt.post_text, pt.post_subject, u.username, u.user_id, u.user_sig, t.topic_title, t.topic_type
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

				if(!$preview)
				{
					$subject = stripslashes(trim($postrow['post_subject']));
					$message = stripslashes(trim($postrow['post_text']));

					if(eregi("\[addsig]$", $message))
					{
						$message = eregi_replace("\[addsig]$", "", $message);

						$user_sig = ($postrow['user_sig'] != "") ? $postrow['user_sig'] : "";
						$attach_sig = ($postrow['user_sig'] != "") ? TRUE : 0;
					}
					else
					{
						$attach_sig = 0;
					}

					// Removes UID from BBCode entries
					$message = preg_replace("/\:[0-9a-z\:]+\]/si", "]", $message);

					$message = str_replace("<br />", "\n", $message);

   					$message = undo_htmlspecialchars($message);

					// Special handling for </textarea> tags in the message, which can break the editing form..
					$message = preg_replace('#</textarea>#si', '&lt;/TEXTAREA&gt;', $message);

					if($is_first_post)
					{
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
if($mode == "newtopic")
{
	$post_a = $lang['Post_a_new_topic'];
}
else if($mode == "reply" || $mode == "quote")
{
	//
	// Set mode to reply
	//
	$mode = "reply";
	$post_a = $lang['Post_a_reply'];
}
else if($mode == "editpost")
{
	$post_a = $lang['Edit_Post'];
}

$page_title = $post_a;
include($phpbb_root_path . 'includes/page_header.'.$phpEx);

//
// Start Error handling
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
// End error handling
//

if(empty($username))
{
	$username = stripslashes($userdata['username']);
}

//
// Define a signature, this is in practice only used for
// preview but doing this here allows us to use it as a
// check for attach_sig later
//
if( $mode == "editpost" )
{
	$user_sig = ($postrow['user_sig'] != "") ? $postrow['user_sig'] : "";
}
else
{
	$user_sig = ($userdata['user_sig'] != "") ? $userdata['user_sig'] : "";
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

	//
	// Define censored word matches
	//
	$sql = "SELECT word, replacement
		FROM  " . WORDS_TABLE;
	if( !$words_result = $db->sql_query($sql) )
	{
		message_die(GENERAL_ERROR, "Couldn't get censored words from database.", "", __LINE__, __FILE__, $sql);
	}
	else
	{
		$word_list = $db->sql_fetchrowset($words_result);

		$orig_word = array();
		$replacement_word = array();

		for($i = 0; $i < count($word_list); $i++)
		{
			$word = str_replace("\*", "\w*?", preg_quote($word_list[$i]['word']));

			$orig_word[] = "/\b(" . $word . ")\b/i";
			$replacement_word[] = $word_list[$i]['replacement'];
		}
	}

	if($bbcode_on)
	{
		$bbcode_uid = make_bbcode_uid();
	}

	$preview_message = stripslashes(prepare_message($message, $html_on, $bbcode_on, $smilies_on, $bbcode_uid));

	//
	// Finalise processing as per viewtopic
	//
	if( !$html_on )
	{
		if($user_sig != "")
		{
			$user_sig = htmlspecialchars($user_sig);
		}
		$preview_message = htmlspecialchars($preview_message);
	}

	if($user_sig != "")
	{
		$sig_uid = make_bbcode_uid();
		$user_sig = bbencode_first_pass($user_sig, $sig_uid);
		$user_sig = bbencode_second_pass($user_sig, $sig_uid);
	}

	if($bbcode_on)
	{
		$preview_message = bbencode_second_pass($preview_message, $bbcode_uid);

		//
		// This compensates for bbcode's rather agressive (but I guess necessary)
		// HTML handling
		//
		if( !$html_on )
		{
			$preview_message = preg_replace("'&amp;'", "&", $preview_message);
		}
	}
	else
	{
		// Removes UID from BBCode entries
		$preview_message = preg_replace("/\:[0-9a-z\:]+\]/si", "]", $preview_message);
	}

	if( count($orig_word) )
	{
		$preview_subject = preg_replace($orig_word, $replacement_word, stripslashes($subject));
		$preview_message = preg_replace($orig_word, $replacement_word, $preview_message);
	}

	if($smilies_on)
	{
		$preview_message = smilies_pass($preview_message);
	}

	if($attach_sig && $user_sig != "")
	{
		$preview_message = $preview_message . "<br /><br />_________________<br />" . $user_sig;
	}

	$preview_message = make_clickable($preview_message);
	$preview_message = str_replace("\n", "<br />", $preview_message);

	$template->set_filenames(array(
		"preview" => "posting_preview.tpl")
	);
	$template->assign_vars(array(
		"TOPIC_TITLE" => $preview_subject,
		"POST_SUBJECT" => $preview_subject,
		"POSTER_NAME" => stripslashes($username),
		"POST_DATE" => create_date($board_config['default_dateformat'], time(), $board_config['default_timezone']),
		"MESSAGE" => $preview_message,

		"L_PREVIEW" => $lang['Preview'],
		"L_POSTED" => $lang['Posted'])
	);
	$template->pparse("preview");
}
//
// End Preview Post
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
	"L_GO" => $lang['Go'],
	"L_JUMP_TO" => $lang['Jump_to'],
	"L_SELECT_FORUM" => $lang['Select_forum'],
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

//
// Generate form data
//
$display_username = ($userdata['session_logged_in']) ? stripslashes($userdata["username"]) : "";
$display_subject = ($subject != "") ? stripslashes($subject) : "";

//
// HTML toggle selection
//
if($board_config['allow_html'])
{
	$html_status = $lang['ON'];
	$template->assign_block_vars("html_checkbox", array());
}
else
{
	$html_status = $lang['OFF'];
}

//
// BBCode toggle selection
//
if($board_config['allow_bbcode'])
{
	$bbcode_status = $lang['ON'];
	$template->assign_block_vars("bbcode_checkbox", array());
}
else
{
	$bbcode_status = $lang['OFF'];
}

//
// Smilies toggle selection
//
if($board_config['allow_smilies'])
{
	$smilies_status = $lang['ON'];
	$template->assign_block_vars("smilies_checkbox", array());
}
else
{
	$smilies_status = $lang['OFF'];
}

//
// Signature toggle selection - only show if
// the user has a signature
//
if( $user_sig != "" )
{
	$template->assign_block_vars("signature_checkbox", array());
}

//
// Delete selection
//
if($mode == 'editpost' && !$preview && ( $is_last_post || $is_auth['auth_mod'] ) )
{
	$template->assign_block_vars("delete_checkbox", array());
}

//
// Topic type selection
//
$topic_type_radio = '';
if($mode == 'newtopic' || ( $mode == 'editpost' && $is_first_post ) )
{
	$template->assign_block_vars("type_toggle", array());

	if($is_auth['auth_announce'])
	{
		$announce_toggle = '<input type="radio" name="topictype" value="announce"';
		if($is_announce)
		{
			$announce_toggle .= ' checked';
		}
		$announce_toggle .= ' /> ' . $lang['Post_Announcement'] . '&nbsp;&nbsp;';
	}

	if($is_auth['auth_sticky'])
	{
		$sticky_toggle = '<input type="radio" name="topictype" value="sticky"';
		if($is_sticky)
		{
			$sticky_toggle .= ' checked';
		}
		$sticky_toggle .= ' /> ' . $lang['Post_Sticky'] . '&nbsp;&nbsp;';
	}

	if( $is_auth['auth_announce'] || $is_auth['auth_sticky'] )
	{
		$topic_type_toggle = $lang['Post_topic_as'] . ': <input type="radio" name="topictype" value="normal"';
		if(!$is_announce && !$is_sticky)
		{
			$topic_type_toggle .= ' checked';
		}
		$topic_type_toggle .= ' /> ' . $lang['Post_Normal'] . '&nbsp;&nbsp;' . $sticky_toggle . $announce_toggle;
	}
}

//
// Define hidden fields
//
$hidden_form_fields = "";
if($mode == "newtopic")
{
	$hidden_form_fields .= "<input type=\"hidden\" name=\"" . POST_FORUM_URL . "\" value=\"$forum_id\" />";
}
else if($mode == "reply")
{
	$hidden_form_fields .= "<input type=\"hidden\" name=\"" . POST_TOPIC_URL . "\" value=\"$topic_id\" />";
}
else if($mode == "editpost")
{
	$hidden_form_fields .= "<input type=\"hidden\" name=\"" . POST_TOPIC_URL . "\" value=\"$topic_id\" /><input type=\"hidden\" name=\"" . POST_POST_URL . "\" value=\"$post_id\" />";
}
$hidden_form_fields .= "<input type=\"hidden\" name=\"mode\" value=\"$mode\" />";

//
// User not logged in so offer up a username
// field box
//
if( !$userdata['session_logged_in'] )
{
	$template->assign_block_vars("anon_user", array());
}

//
// Here we check (if we're editing or replying)
// whether the post has html/bbcode/smilies disabled
// if it does then we modify the status vars appropriately
//
if( !$preview && $mode == "editpost" )
{
	if($postrow['enable_html'] && $board_config['allow_html'])
	{
		$html_on = TRUE;
	}
	else
	{
		$html_on = 0;
	}
	if($postrow['enable_bbcode'] && $board_config['allow_bbcode'])
	{
		$bbcode_on = TRUE;
	}
	else
	{
		$bbcode_on = 0;
	}
	if($postrow['enable_smilies'] && $board_config['allow_smilies'])
	{
		$smilies_on = TRUE;
	}
	else
	{
		$smilies_on = 0;
	}
}

//
// Output the data to the template
//
$template->assign_vars(array(
	"USERNAME" => $display_username,
	"SUBJECT" => $display_subject,
	"MESSAGE" => $message,
	"HTML_STATUS" => $html_status,
	"BBCODE_STATUS" => $bbcode_status,
	"SMILIES_STATUS" => $smilies_status,

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

	"L_DISABLE_HTML" => $lang['Disable'] . $lang['HTML'] . $lang['in_this_post'],
	"L_DISABLE_BBCODE" => $lang['Disable'] . $lang['BBCode'] . $lang['in_this_post'],
	"L_DISABLE_SMILIES" => $lang['Disable'] . $lang['Smilies'] . $lang['in_this_post'],
	"L_ATTACH_SIGNATURE" => $lang['Attach_signature'],
	"L_NOTIFY_ON_REPLY" => $lang['Notify'],
	"L_DELETE_POST" => $lang['Delete_post'],

	"S_HTML_CHECKED" => (!$html_on) ? "checked=\"checked\"" : "",
	"S_BBCODE_CHECKED" => (!$bbcode_on) ? "checked=\"checked\"" : "",
	"S_SMILIES_CHECKED" => (!$smilies_on) ? "checked=\"checked\"" : "",
	"S_SIGNATURE_CHECKED" => ($attach_sig) ? "checked=\"checked\"" : "",
	"S_NOTIFY_CHECKED" => ($notify) ? "checked=\"checked\"" : "",
	"S_TYPE_TOGGLE" => $topic_type_toggle,
	"S_TOPIC_ID" => $topic_id,

	"S_POST_ACTION" => append_sid("posting.$phpEx"),
	"S_HIDDEN_FORM_FIELDS" => $hidden_form_fields)
);

$template->pparse("body");

include($phpbb_root_path . 'includes/page_tail.'.$phpEx);

?>