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
include('extension.inc');
include('common.'.$phpEx);
include('includes/bbcode.'.$phpEx);

//
// Posting specific functions.
//

// This function will prepare the message for entry into the database.
function prepare_message($message, $html_on, $bbcode_on, $smile_on, $bbcode_uid = 0)
{
	$message = trim($message);

	if(!$html_on)
	{
		$message = htmlspecialchars($message);
	}

	if($bbcode_on)
	{
		$message = bbencode_first_pass($message, $bbcode_uid);
	}

	if($smile_on)
	{
		// No smile() function yet, write one...
		//$message = smile($message);
	}

	$message = addslashes($message);
	return($message);
}
//
// End Posting specific functions.
//

//
// Start program proper
//
if(!isset($HTTP_GET_VARS['forum']) && !isset($HTTP_POST_VARS['forum']))  // For backward compatibility
{
	$forum_id = ($HTTP_GET_VARS[POST_FORUM_URL]) ? $HTTP_GET_VARS[POST_FORUM_URL] : $HTTP_POST_VARS[POST_FORUM_URL];
}
else
{
	$forum_id = ($HTTP_GET_VARS['forum']) ? $HTTP_GET_VARS['forum'] : $HTTP_POST_VARS['forum'];
}

$mode = (isset($HTTP_GET_VARS['mode'])) ? $HTTP_GET_VARS['mode'] : ( (isset($HTTP_POST_VARS['mode'])) ? $HTTP_POST_VARS['mode'] : "");

//
// Start session management
//
$userdata = session_pagestart($user_ip, PAGE_POSTING, $session_length);
init_userprefs($userdata);
//
// End session management
//


//
// Auth code
//

// This is a quick check to see if it works
// can probably be placed better ...

switch($mode)
{
	case 'newtopic':
		if(isset($HTTP_POST_VARS['annouce']))
		{
			$auth_type = AUTH_ANNOUCE;
			$is_auth_type = "auth_announce";
			$error_string = "post annoucements";
		}
		else if(isset($HTTP_POST_VARS['sticky']))
		{
			$auth_type = AUTH_STICKY;
			$is_auth_type = "auth_sticky";
			$error_string = "post sticky topics";
		}
		else
		{
			$auth_type = AUTH_ALL;
			$is_auth_type = "auth_post";
			$error_string = "post new topics";
		}
		break;
	case 'reply':
		$auth_type = AUTH_ALL;
		$is_auth_type = "auth_reply";
		$error_string = "reply to topics";
		break;
	case 'editpost':
		$auth_type = AUTH_ALL;
		$is_auth_type = "auth_edit";
		$error_string = "edit topics";
		break;
	case 'delete':
		$auth_type = AUTH_DELETE;
		$is_auth_type = "auth_delete";
		$error_string = "delete topics";
		break;
	default:
		$auth_type = AUTH_ALL;
		$is_auth_type = "auth_all";
		$error_string = "post new topics";
		break;
}

$is_auth = auth($auth_type, $forum_id, $userdata);

if(!$is_auth[$is_auth_type])
{
	//
	// Ooopss, user is not authed
	// to read this forum ...
	//
	include('includes/page_header.'.$phpEx);

	$msg = "I am sorry but you cannot $error_string in this forum, this function is restricted to " . $is_auth[$is_auth_type . "_type"] . " only.";

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
// End Auth
//

$error = FALSE;

//
// Set initial conditions
//
$is_first_post = (($HTTP_GET_VARS['is_first_post'] == 1) || ($HTTP_POST_VARS['is_first_post'] == 1)) ? TRUE : FALSE;
$disable_html = (isset($HTTP_POST_VARS['disable_html'])) ? $HTTP_POST_VARS['disable_html'] : !$userdata['user_allowhtml'];
$disable_bbcode = (isset($HTTP_POST_VARS['disable_bbcode'])) ? $HTTP_POST_VARS['disable_bbcode'] : !$userdata['user_allowbbcode'];
$disable_smilies = (isset($HTTP_POST_VARS['disable_smile'])) ? $HTTP_POST_VARS['disable_smile'] : !$userdata['user_allowsmile'];
$attach_sig = (isset($HTTP_POST_VARS['attach_sig'])) ? $HTTP_POST_VARS['attach_sig'] : $userdata['user_attachsig'];
$notify = (isset($HTTP_POST_VARS['notify'])) ? $HTTP_POST_VARS['notify'] : $userdata["always_notify"];
$annouce = (isset($HTTP_POST_VARS['annouce'])) ? $HTTP_POST_VARS['annouce'] : "";
$unannouce = (isset($HTTP_POST_VARS['unannouce'])) ? $HTTP_POST_VARS['unannouce'] : "";
$sticky = (isset($HTTP_POST_VARS['sticky'])) ? $HTTP_POST_VARS['sticky'] : "";
$unstick = (isset($HTTP_POST_VARS['unstick'])) ? $HTTP_POST_VARS['unstick'] : "";
$preview = (isset($HTTP_POST_VARS['preview'])) ? TRUE : FALSE;


if($annouce)
{
	$topic_type = ANNOUCE;
}
else if($sticky)
{
	$topic_type = STICKY;
}
else
{
	$topic_type = NORMAL;
}

//
// Prepare our message and subject on a 'submit'
//
if(isset($HTTP_POST_VARS['submit']) || $preview)
{
	//
	// Flood control
	//
	if($mode != 'editpost' && !$preview)
	{
		$sql = "SELECT max(post_time) AS last_post_time
			FROM ".POSTS_TABLE."
			WHERE poster_ip = '$user_ip'";
		if($result = $db->sql_query($sql))
		{
			$db_row = $db->sql_fetchrowset($result);
			$last_post_time = $db_row[0]['last_post_time'];
			$current_time = get_gmt_ts();

			if(($current_time - $last_post_time) < $board_config['flood_interval'])
			{
				$error = TRUE;
				$error_msg = $lang['Flood_Error'];
			}
		}
	}
	//
	// End: Flood control
	//

	// Handle anon posting with usernames
	if(isset($HTTP_POST_VARS['username']))
	{
		$username = trim(strip_tags(htmlspecialchars(stripslashes($HTTP_POST_VARS['username']))));
		if(!validate_username($username))
		{
			$error = TRUE;
			if(isset($error_msg))
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
		if(isset($error_msg))
		{
			$error_msg .= "<br />";
		}
		$error_msg .= $lang['Empty_subj'];
	}

	// You can't make it both an annoumcement and a stick topic
	if($annouce && $sticky)
	{
		$error = TRUE;
		if(isset($error_msg))
		{
			$error_msg .= "<br />";
		}
		$error_msg .= $lang['Annouce_and_sticky'];
	}

	if(!empty($HTTP_POST_VARS['message']))
	{
		if(!$error && !$preview)
		{
			if($disable_html)
			{
				$html_on = FALSE;
			}
			else
			{
				$html_on = TRUE;
			}

			if($disable_bbcode)
			{
				$bbcode_on = FALSE;
			}
			else
			{
				$uid = make_bbcode_uid();
				$bbcode_on = TRUE;
			}

			if($disable_smilies)
			{
				$smile_on = FALSE;
			}
			else
			{
				$smile_on = TRUE;
			}

			$message = prepare_message($HTTP_POST_VARS['message'], $html_on, $bbcode_on, $smile_on, $uid);

			if($attach_sig && !empty($userdata['user_sig']))
			{
				$message .= "[addsig]";
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
		if(isset($error_msg))
		{
			$error_msg .= "<br />";
		}
		$error_msg .= $lang['Empty_msg'];
	}
}

switch($mode)
{
	case 'newtopic':
		$page_title = " ".$lang['Postnew'];
		$section_title = $lang['Post_new_in'];

		if($SQL_LAYER != "mysql")
		{
			switch($SQL_LAYER)
			{
				case 'postgres':
					$result = $db->sql_query("BEGIN");
					break;
				case 'mssql':
					$result = $db->sql_query("BEGIN TRANSACTION");
					break;
			}
		}

		if(isset($HTTP_POST_VARS['submit']) && !$error && !$preview)
		{
			if($username)
			{
				$username = addslashes($username);
			}
			$topic_time = get_gmt_ts();
			$topic_notify = ($HTTP_POST_VARS['notify']) ? 1 : 0;
			$sql  = "INSERT INTO ".TOPICS_TABLE." (topic_title, topic_poster, topic_time, forum_id, topic_notify, topic_status, topic_type)
						VALUES ('$subject', ".$userdata['user_id'].", ".$topic_time.", $forum_id, $topic_notify, ".UNLOCKED.", ".$topic_type.")";

			if($db->sql_query($sql))
			{
				$new_topic_id = $db->sql_nextid();
				$sql = "INSERT INTO ".POSTS_TABLE." (topic_id, forum_id, poster_id, post_username, post_time, poster_ip, bbcode_uid)
						  VALUES ($new_topic_id, $forum_id, ".$userdata['user_id'].", '".$username."', $topic_time, '$user_ip', '$uid')";

				if($db->sql_query($sql))
				{
					$new_post_id = $db->sql_nextid();
					$sql = "INSERT INTO ".POSTS_TEXT_TABLE." (post_id, post_subject, post_text) VALUES ($new_post_id, '".$subject."', '".$message."')";
					if($db->sql_query($sql))
					{
						$sql = "UPDATE ".TOPICS_TABLE." SET topic_last_post_id = $new_post_id WHERE topic_id = $new_topic_id";
						if($db->sql_query($sql))
						{
							$sql = "UPDATE ".FORUMS_TABLE." SET forum_last_post_id = $new_post_id, forum_posts = forum_posts + 1, forum_topics = forum_topics + 1 WHERE forum_id = $forum_id";
							if($db->sql_query($sql))
							{
								if($userdata['user_id'] != ANONYMOUS)
								{
									$sql = "UPDATE ".USERS_TABLE." SET user_posts = user_posts + 1 WHERE user_id = ".$userdata['user_id'];
									$db->sql_query($sql);
								}

								if(SQL_LAYER != "mysql")
								{
									switch($SQL_LAYER)
									{
										case 'postgres':
											$result = $db->sql_query("COMMIT");
											break;
										case 'mssql':
											$result = $db->sql_query("COMMIT TRANSACTION");
											break;
									}
									if(!$result)
									{
										error_die(SQL_ERROR, "Couldn't commit");
									}
								}

								include('includes/page_header.'.$phpEx);
								// If we get here the post has been inserted successfully.
								$msg = "$l_stored<br /><br />$l_click <a href=\"".append_sid("viewtopic.$phpEx?".POST_POST_URL."=$new_post_id")."\">$l_here</a>
   										$l_viewmsg<br /><br />$l_click <a href=\"".append_sid("viewforum.$phpEx?".POST_FORUM_URL."=$forum_id")."\">$l_here</a> $l_returntopic";

								$template->set_filenames(array(
									"reg_header" => "error_body.tpl"
								));
								$template->assign_vars(array(
									"ERROR_MESSAGE" => $msg
								));
								$template->pparse("reg_header");

								include('includes/page_tail.'.$phpEx);
							}
							else
							{
								if(SQL_LAYER != "mysql")
								{
									switch($SQL_LAYER)
									{
										case 'postgres':
											$result = $db->sql_query("ROLLBACK");
											break;
										case 'mssql':
											$result = $db->sql_query("ROLLBACK TRANSACTION");
											break;
									}
								}
								error_die(QUERY_ERROR);
							}
						}
						else
						{
							if(SQL_LAYER != "mysql")
							{
								switch($SQL_LAYER)
								{
									case 'postgres':
										$result = $db->sql_query("ROLLBACK");
										break;
									case 'mssql':
										$result = $db->sql_query("ROLLBACK TRANSACTION");
										break;
								}
							}
							if(DEBUG)
							{
								$error = $db->sql_error();
								error_die(QUERY_ERROR, "Error updating topics table.<br>Reason: ".$error['message']."<br>Query: $sql", __LINE__, __FILE__);
							}
							else
							{
								error_die(QUERY_ERROR);
							}
						}
					}
					else
					{
						if(SQL_LAYER != "mysql")
						{
							switch($SQL_LAYER)
							{
								case 'postgres':
									$result = $db->sql_query("ROLLBACK");
									break;
								case 'mssql':
									$result = $db->sql_query("ROLLBACK TRANSACTION");
									break;
							}
						}
						if(DEBUG)
						{
							$error = $db->sql_error();
							error_die(QUERY_ERROR, "Error inserting data into posts text table.<br>Reason: ".$error['message']."<br>Query: $sql", __LINE__, __FILE__);
						}
						else
						{
							error_die(QUERY_ERROR);
						}
					}
				}
				else
				{
					if(SQL_LAYER != "mysql")
					{
						switch($SQL_LAYER)
						{
							case 'postgres':
								$result = $db->sql_query("ROLLBACK");
								break;
							case 'mssql':
								$result = $db->sql_query("ROLLBACK TRANSACTION");
								break;
						}
					}
					if(DEBUG)
					{
						$error = $db->sql_error();
						error_die(QUERY_ERROR, "Error inserting data into posts table.<br>Reason: ".$error['message']."<br>Query: $sql", __LINE__, __FILE__);
					}
					else
					{
						error_die(QUERY_ERROR);
					}
				}
			}
			else
			{
				if(SQL_LAYER != "mysql")
				{
					switch($SQL_LAYER)
					{
						case 'postgres':
							$result = $db->sql_query("ROLLBACK");
							break;
						case 'mssql':
							$result = $db->sql_query("ROLLBACK TRANSACTION");
							break;
					}
				}
				if(DEBUG)
				{
					$error = $db->sql_error();
					error_die(QUERY_ERROR, "Error inserting data into topics text table.<br>Reason: ".$error['message']."<br>Query: $sql", __LINE__, __FILE__);
				}
				else
				{
					error_die(QUERY_ERROR);
				}
			}
		}
      else if(isset($HTTP_POST_VARS['preview']))
      {


      }

	  break;

	case 'reply':
		$page_title = " $l_reply";
		$section_title = $l_postreplyto;

		if(isset($HTTP_POST_VARS['submit']) && !$error && !$preview)
		{
			if($SQL_LAYER != "mysql")
			{
				switch($SQL_LAYER)
				{
					case 'postgres':
						$result = $db->sql_query("BEGIN");
						break;
					case 'mssql':
						$result = $db->sql_query("BEGIN TRANSACTION");
						break;
				}
			}

			if($username)
			{
				$username = addslashes($username);
			}

			$new_topic_id = $HTTP_POST_VARS[POST_TOPIC_URL];
			$topic_time = get_gmt_ts();

			$sql = "INSERT INTO ".POSTS_TABLE." (topic_id, forum_id, poster_id, post_username, post_time, poster_ip, bbcode_uid)
					  VALUES ($new_topic_id, $forum_id, ".$userdata['user_id'].", '".$username."', $topic_time, '$user_ip', '$uid')";

			if($db->sql_query($sql))
			{
				$new_post_id = $db->sql_nextid();
				$sql = "INSERT INTO ".POSTS_TEXT_TABLE." (post_id, post_subject, post_text) VALUES ($new_post_id, '".$subject."', '".$message."')";
				if($db->sql_query($sql))
				{
					$sql = "UPDATE ".TOPICS_TABLE." SET topic_last_post_id = $new_post_id, topic_replies = topic_replies + 1 WHERE topic_id = $new_topic_id";
					if($db->sql_query($sql))
					{
						$sql = "UPDATE ".FORUMS_TABLE." SET forum_last_post_id = $new_post_id, forum_posts = forum_posts + 1 WHERE forum_id = $forum_id";
						if($db->sql_query($sql))
						{
							if($userdata['user_id'] != ANONYMOUS)
							{

								$sql = "UPDATE ".USERS_TABLE." SET user_posts = user_posts + 1 WHERE user_id = ".$userdata['user_id'];
								$db->sql_query($sql);
							}
							include('includes/page_header.'.$phpEx);
							//
							// If we get here the post has been inserted successfully.
							//
							if(SQL_LAYER != "mysql")
							{
								switch($SQL_LAYER)
								{
									case 'postgres':
										$result = $db->sql_query("COMMIT");
										break;
									case 'mssql':
										$result = $db->sql_query("COMMIT TRANSACTION");
										break;
								}
								if(!$result)
								{
									error_die(SQL_ERROR, "Couldn't commit");
								}
							}

							$msg = "$l_stored<br /><br />$l_click <a href=\"".append_sid("viewtopic.$phpEx?".POST_POST_URL."=$new_post_id#$new_post_id")."\">$l_here</a>$l_viewmsg<br /><br />$l_click <a href=\"".append_sid("viewforum.$phpEx?".POST_FORUM_URL."=$forum_id")."\">$l_here</a> $l_returntopic";

							$template->set_filenames(array(
								"reg_header" => "error_body.tpl"
							));
							$template->assign_vars(array(
								"ERROR_MESSAGE" => $msg
							));
							$template->pparse("reg_header");

							include('includes/page_tail.'.$phpEx);
						}
						else
						{
							if(SQL_LAYER != "mysql")
							{
								switch($SQL_LAYER)
								{
									case 'postgres':
										$result = $db->sql_query("ROLLBACK");
										break;
									case 'mssql':
										$result = $db->sql_query("ROLLBACK TRANSACTION");
										break;
								}
							}
							error_die(QUERY_ERROR);
						}
					}
					else
					{
					if(SQL_LAYER != "mysql")
					{
							switch($SQL_LAYER)
							{
								case 'postgres':
									$result = $db->sql_query("ROLLBACK");
									break;
								case 'mssql':
									$result = $db->sql_query("ROLLBACK TRANSACTION");
									break;
							}
						}
						if(DEBUG)
						{
							$error = $db->sql_error();
							error_die(QUERY_ERROR, "Error updating topics table.<br>Reason: ".$error['message']."<br>Query: $sql", __LINE__, __FILE__);
						}
						else
						{
							error_die(QUERY_ERROR);
						}
					}
				}
				else
				{
					if(SQL_LAYER != "mysql")
					{
						switch($SQL_LAYER)
						{
							case 'postgres':
								$result = $db->sql_query("ROLLBACK");
								break;
							case 'mssql':
								$result = $db->sql_query("ROLLBACK TRANSACTION");
								break;
						}
					}
					if(DEBUG)
					{
						$error = $db->sql_error();
						error_die(QUERY_ERROR, "Error inserting data into posts text table.<br>Reason: ".$error['message']."<br>Query: $sql", __LINE__, __FILE__);
					}
					else
					{
						error_die(QUERY_ERROR);
					}
				}
			}
			else
			{
				if(SQL_LAYER != "mysql")
				{
					switch($SQL_LAYER)
					{
						case 'postgres':
							$result = $db->sql_query("ROLLBACK");
							break;
						case 'mssql':
							$result = $db->sql_query("ROLLBACK TRANSACTION");
							break;
					}
				}
				if(DEBUG)
				{
					$error = $db->sql_error();
					error_die(QUERY_ERROR, "Error inserting data into posts table.<br>Reason: ".$error['message']."<br>Query: $sql", __LINE__, __FILE__);
				}
				else
				{
					error_die(QUERY_ERROR);
				}
			}
		}
		break;

	case 'editpost':
		$page_title = " $l_editpost";
		$section_title = $l_editpostin;
		if(isset($HTTP_POST_VARS['submit']) && !$error && !$preview)
		{
			if(isset($HTTP_POST_VARS['delete_post']))
			{


			}
			else
			{
				$post_id = $HTTP_POST_VARS[POST_POST_URL];
				$new_topic_id = $HTTP_POST_VARS[POST_TOPIC_URL];
				
				if($SQL_LAYER != "mysql")
				{
					switch($SQL_LAYER)
					{
						case 'postgres':
							$result = $db->sql_query("BEGIN");
							break;
						case 'mssql':
							$result = $db->sql_query("BEGIN TRANSACTION");
							break;
					}
				}
				
				$sql = "UPDATE ".POSTS_TEXT_TABLE." SET post_text = '$message', post_subject = '$subject' WHERE post_id = ".$HTTP_POST_VARS[POST_POST_URL];
				if($db->sql_query($sql))
				{
					if($is_first_post)
					{
						// Update topics table here, set notification level and such
						$sql = "UPDATE ".TOPICS_TABLE." SET topic_title = '$subject', topic_notify = '$notify', topic_type = '".$topic_type."' WHERE topic_id = ".$HTTP_POST_VARS[POST_TOPIC_URL];
						if(!$db->sql_query($sql))
						{
							if(SQL_LAYER != "mysql")
							{
								switch($SQL_LAYER)
								{
									case 'postgres':
										$result = $db->sql_query("ROLLBACK");
									break;
									case 'mssql':
										$result = $db->sql_query("ROLLBACK TRANSACTION");
									break;
								}
							}

							if(DEBUG)
							{
								$error = $db->sql_error();
								error_die(QUERY_ERROR, "Updating topics table.<br>Reason: ".$error['message']."<br>Query: $sql", __LINE__, __FILE__);
							}
							else
							{
								error_die(QUERY_ERROR);
							}
						}
						else
						{
							if(SQL_LAYER != "mysql")
							{
								switch($SQL_LAYER)
								{
									case 'postgres':
										$result = $db->sql_query("COMMIT");
										break;
									case 'mssql':
										$result = $db->sql_query("COMMIT TRANSACTION");
										break;
								}
								if(!$result)
								{
									error_die(SQL_ERROR, "Couldn't commit");
								}
							}

							include('includes/page_header.'.$phpEx);
							// If we get here the post has been inserted successfully.
							$msg = "$l_stored<br /><br />$l_click <a href=\"".append_sid("viewtopic.$phpEx?".POST_POST_URL."=$post_id#$post_id")."\">$l_here</a>
  										$l_viewmsg<br /><br />$l_click <a href=\"".append_sid("viewforum.$phpEx?".POST_FORUM_URL."=$forum_id")."\">$l_here</a> $l_returntopic";
	
							$template->set_filenames(array(
								"reg_header" => "error_body.tpl"
							));
							$template->assign_vars(array(
								"ERROR_MESSAGE" => $msg
							));
							$template->pparse("reg_header");
	
							include('includes/page_tail.'.$phpEx);
						}
					}
					else
					{
						if(SQL_LAYER != "mysql")
						{
							switch($SQL_LAYER)
							{
								case 'postgres':
									$result = $db->sql_query("COMMIT");
								break;
								case 'mssql':
									$result = $db->sql_query("COMMIT TRANSACTION");
								break;
							}
							if(!$result)
							{
								error_die(SQL_ERROR, "Couldn't commit");
							}
						}

						include('includes/page_header.'.$phpEx);
						// If we get here the post has been inserted successfully.
						$msg = "$l_stored<br /><br />$l_click <a href=\"".append_sid("viewtopic.$phpEx?".POST_POST_URL."=$post_id#$post_id")."\">$l_here</a>
  									$l_viewmsg<br /><br />$l_click <a href=\"".append_sid("viewforum.$phpEx?".POST_FORUM_URL."=$forum_id")."\">$l_here</a> $l_returntopic";

						$template->set_filenames(array(
							"reg_header" => "error_body.tpl"
						));
						$template->assign_vars(array(
							"ERROR_MESSAGE" => $msg
						));
						$template->pparse("reg_header");

						include('includes/page_tail.'.$phpEx);
					}
				}
				else
				{
					if(DEBUG)
					{
						$error = $db->sql_error();
						error_die(QUERY_ERROR, "Error updateing posts text table.<br>Reason: ".$error['message']."<br>Query: $sql", __LINE__, __FILE__);
					}
					else
					{
						error_die(QUERY_ERROR);
					}
				}
			}
		}
		else if(!$preview)
		{
			$post_id = ($HTTP_GET_VARS[POST_POST_URL]) ? $HTTP_GET_VARS[POST_POST_URL] : $HTTP_POST_VARS[POST_POST_URL];
			if(!empty($post_id))
			{

   			$sql = "SELECT p.*, pt.post_text, pt.post_subject, u.username, u.user_id, u.user_sig, t.topic_title, t.topic_notify, t.topic_type
   						FROM ".POSTS_TABLE." p, ".USERS_TABLE." u, ".TOPICS_TABLE." t, ".POSTS_TEXT_TABLE." pt
   						WHERE (p.post_id = '$post_id')
   							AND pt.post_id = p.post_id
   							AND (p.topic_id = t.topic_id)
   							AND (p.poster_id = u.user_id)";

				if($result = $db->sql_query($sql))
				{
					$postrow = $db->sql_fetchrow($result);

					if($userdata['user_id'] != $postrow['user_id'] && !$is_auth['auth_mod'])
					{
						include('includes/page_header.'.$phpEx);

						$msg = "Sorry but you can only edit your own posts.";

						$template->set_filenames(array(
							"reg_header" => "error_body.tpl"
						));
						$template->assign_vars(array(
							"ERROR_MESSAGE" => $msg
						));
						$template->pparse("reg_header");

						include('includes/page_tail.'.$phpEx);
					}

					$subject = stripslashes($postrow['post_subject']);
					$message = stripslashes($postrow['post_text']);
					if(eregi("\[addsig]$", $message))
					{
						$attach_sig = TRUE;
					}
					$message = eregi_replace("\[addsig]$", "", $message);
					$message = str_replace("<br />", "\n", $message);

					// These have not been implemented yet!
					/*
					$message = bbdecode($message);
					$message = desmile($message);
					 */

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
							case ANNOUCE:
								$is_annouce = TRUE;
							break;
							case STICKY:
								$is_stuck = TRUE;
							break;
						}
						
					}
					
					
   			}
   			else
   			{
   				if(DEBUG)
   				{
   					$error = $db->error();
   					error_die(QUERY_ERROR, "Error get post information. <br>Reason: ".$error['message']."<br>Query: $sql", __LINE__, __FILE__);
   				}
   				else
   				{
   					error_die(QUERY_ERROR);
   				}
   			}
   		}
   		else
   		{
   			error_die(GENERAL_ERROR, "Sorry, no there is no such post");
   		}
		}
	break;
} // end switch

//
// Output page
//
include('includes/page_header.'.$phpEx);

//
// Start: Error handling
//
if($error)
{
	$template->set_filenames(array(
		"reg_header" => "error_body.tpl"
	));
	$template->assign_vars(array(
		"ERROR_MESSAGE" => $error_msg
	));
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
if($preview)
{
	$preview_message = $message;
	$uid = make_bbcode_uid();
	$preview_message = prepare_message($preview_message, TRUE, TRUE, TRUE, $uid);
	$preview_message = bbencode_second_pass($preview_message, $uid);
	$preview_message = make_clickable($preview_message);

	$template->set_filenames(array("preview" => "posting_preview.tpl"));
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

if(!isset($HTTP_GET_VARS[POST_FORUM_URL]) && !isset($HTTP_POST_VARS[POST_FORUM_URL]))
{
	error_die(GENERAL_ERROR, "Sorry, no there is no such forum");
}

		$sql = "SELECT forum_name
					FROM ".FORUMS_TABLE."
					WHERE forum_id = $forum_id";
		if(!$result = $db->sql_query($sql))
		{
			error_die(SQL_QUERY, "Could not obtain forum/forum access information.", __LINE__, __FILE__);
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

			"U_VIEW_FORUM" => append_sid("viewforum.$phpEx?".POST_FORUM_URL."=$forum_id"))
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
			$html_status = $l_htmlis . " " . $l_on;
			$html_toggle = '<input type="checkbox" name="disable_html" ';
			if($disable_html)
			{
				$html_toggle .= 'checked';
			}
			$html_toggle .= "> $l_disable $l_html $l_onthispost";
		}
		else
		{
			$html_status = $l_htmlis . " " . $l_off;
		}

		if($board_config['allow_bbcode'])
		{
			$bbcode_status = $l_bbcodeis . " " . $l_on;
			$bbcode_toggle = '<input type="checkbox" name="disable_bbcode" ';
			if($disable_bbcode)
			{
				$bbcode_toggle .= "checked";
			}
			$bbcode_toggle .= "> $l_disable $l_bbcode $l_onthispost";
		}
		else
		{
			$bbcode_status = $l_bbcodeis . " " . $l_off;
		}

		if($board_config['allow_smilies'])
		{
			$smile_toggle = '<input type="checkbox" name="disable_smile" ';
			if($disable_smilies)
			{
				$smile_toggle .= "checked";
			}
			$smile_toggle .= "> $l_disable $l_smilies $l_onthispost";
		}

		$sig_toggle = '<input type="checkbox" name="attach_sig" ';
		if($attach_sig)
		{
			$sig_toggle .= "checked";
		}
		$sig_toggle .= "> $l_attachsig";

		if($mode == 'newtopic' || ($mode == 'editpost' && $is_first_post))
		{
			if($is_auth['auth_announce'])
			{
				if(!$is_annouce)
				{
					$annouce_toggle = '<input type="checkbox" name="annouce" ';
					if($annouce)
					{
						$announce_toggle .= "checked";
					}
					$annouce_toggle .= '> '.$lang['Post_Annoucement'];
				}
				else
				{
					$annouce_toggle = '<input type="checkbox" name="unannouce" ';
					if($unannouce)
					{
						$announce_toggle .= "checked";
					}
					$annouce_toggle .= '> '.$lang['UnAnnounce'];
				}
			}

			if($is_auth['auth_sticky'])
			{
				if(!$is_stuck)
				{
					$sticky_toggle = '<input type="checkbox" name="sticky" ';
					if($sticky)
					{
						$sticky_toggle .= "checked";
					}
					$sticky_toggle .= '> '.$lang['Post_Sticky'];
				}
				else
				{
					$sticky_toggle = '<input type="checkbox" name="unstick" ';
					if($unstick)
					{
						$sticky_toggle .= "checked";
					}
					$sticky_toggle .= '> '.$lang['UnStick'];
				}
			}
		}


		if($mode == 'newtopic' || ($mode == 'editpost' && $notify_show))
		{
			$notify_toggle = '<input type="checkbox" name="notify" ';
			if($notify)
			{
				$notify_toggle .= "checked";
			}
			$notify_toggle .= "> $l_notify";
		}

		if($mode == 'reply' || $mode == 'editpost')
		{
			$topic_id = ($HTTP_GET_VARS[POST_TOPIC_URL]) ? $HTTP_GET_VARS[POST_TOPIC_URL] : $HTTP_POST_VARS[POST_TOPIC_URL];
			$post_id = ($HTTP_GET_VARS[POST_POST_URL]) ? $HTTP_GET_VARS[POST_POST_URL] : $HTTP_POST_VARS[POST_POST_URL];
		}
		$hidden_form_fields = "<input type=\"hidden\" name=\"mode\" value=\"$mode\"><input type=\"hidden\" name=\"".POST_FORUM_URL."\" value=\"$forum_id\"><input type=\"hidden\" name=\"".POST_TOPIC_URL."\" value=\"$topic_id\"><input type=\"hidden\" name=\"".POST_POST_URL."\" value=\"$post_id\"><input type=\"hidden\" name=\"is_first_post\" value=\"$is_first_post\">";

		if($mode == 'newtopic')
		{
			$post_a = $lang['Post_a'] . " " . $lang['Topic'];
		}
		else if($mode == 'reply')
		{
			$post_a = $lang['Post_a'] . " " . $lang['Reply'];
		}
		else if($mode == 'editpost')
		{
			$post_a = $lang['Edit_Post'];
		}
		
		$template->assign_vars(array(
			"L_SUBJECT" => $l_subject,
			"L_MESSAGE_BODY" => $l_body,
			"L_OPTIONS" => $l_options,
			"L_PREVIEW" => $lang['Preview'],
			"L_SUBMIT" => $l_submit,
			"L_CANCEL" => $l_cancelpost,
			"L_POST_A" => $post_a,
			"USERNAME_INPUT" => $username_input,
			"PASSWORD_INPUT" => $password_input,
			"SUBJECT_INPUT" => $subject_input,
			"MESSAGE_INPUT" => $message_input,
			"HTML_STATUS" => $html_status,
			"HTML_TOGGLE" => $html_toggle,
			"SMILE_TOGGLE" => $smile_toggle,
			"SIG_TOGGLE" => $sig_toggle,
			"ANNOUNCE_TOGGLE" => $annouce_toggle,
			"STICKY_TOGGLE" => $sticky_toggle,
			"NOTIFY_TOGGLE" => $notify_toggle,
			"BBCODE_TOGGLE" => $bbcode_toggle,
			"BBCODE_STATUS" => $bbcode_status,

			"S_POST_ACTION" => append_sid("posting.$phpEx"),
			"S_HIDDEN_FORM_FIELDS" => $hidden_form_fields)
		);

		$template->pparse("body");

		include('includes/page_tail.'.$phpEx);
?>