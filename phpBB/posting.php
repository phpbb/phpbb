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

//
// Start session management
//
$userdata = session_pagestart($user_ip, PAGE_POSTING, $session_length);
init_userprefs($userdata);
//
// End session management
//

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
// Put AUTH code here
//

$error = FALSE;

//
// Set initial conditions
//
$disable_html = (isset($HTTP_POST_VARS['disable_html'])) ? $HTTP_POST_VARS['disable_html'] : !$userdata['user_allowhtml'];
$disable_bbcode = (isset($HTTP_POST_VARS['disable_bbcode'])) ? $HTTP_POST_VARS['disable_bbcode'] : !$userdata['user_allowbbcode'];
$disable_smilies = (isset($HTTP_POST_VARS['disable_smile'])) ? $HTTP_POST_VARS['disable_smile'] : !$userdata['user_allowsmile'];
$attach_sig = (isset($HTTP_POST_VARS['attach_sig'])) ? $HTTP_POST_VARS['attach_sig'] : $userdata['user_attachsig'];
$notify = (isset($HTTP_POST_VARS['notify'])) ? $HTTP_POST_VARS['notify'] : $userdata["always_notify"];

//
// Prepare our message and subject on a 'submit'
//
if(isset($HTTP_POST_VARS['submit']))
{
	//
	// Flood control
	//
	if($mode != 'editpost')
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

	if(!empty($HTTP_POST_VARS['message']))
	{
		if(!$error)
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

		if(isset($HTTP_POST_VARS['submit']) && !$error)
		{
			$topic_time = get_gmt_ts();
			$topic_notify = ($HTTP_POST_VARS['notify']) ? $HTTP_POST_VARS['notify'] : 0;
			$sql  = "INSERT INTO ".TOPICS_TABLE." (topic_title, topic_poster, topic_time, forum_id, topic_notify, topic_status)
						VALUES ('$subject', ".$userdata['user_id'].", ".$topic_time.", $forum_id, $topic_notify, ".UNLOCKED.")";

			if($db->sql_query($sql))
			{
				$new_topic_id = $db->sql_nextid();
				$sql = "INSERT INTO ".POSTS_TABLE." (topic_id, forum_id, poster_id, post_time, poster_ip, bbcode_uid)
						  VALUES ($new_topic_id, $forum_id, ".$userdata['user_id'].", $topic_time, '$user_ip', '$uid')";

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

								include('includes/page_header.'.$phpEx);
								// If we get here the post has been inserted successfully.
								$msg = "$l_stored<br />$l_click <a href=\"".append_sid("viewtopic.$phpEx?".POST_TOPIC_URL."=$new_topic_id")."\">$l_here</a>
   										$l_viewmsg<br />$l_click <a href=\"".append_sid("viewforum.$phpEx?".POST_FORUM_URL."=$forum_id")."\">$l_here</a> $l_returntopic";

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
								error_die(QUERY_ERROR);
							}
						}
						else
						{
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

		if(isset($HTTP_POST_VARS['submit']) && !$error)
		{
			$new_topic_id = $HTTP_POST_VARS[POST_TOPIC_URL];
			$topic_time = get_gmt_ts();

				$sql = "INSERT INTO ".POSTS_TABLE." (topic_id, forum_id, poster_id, post_time, poster_ip, bbcode_uid)
						  VALUES ($new_topic_id, $forum_id, ".$userdata['user_id'].", $topic_time, '$user_ip', '$uid')";

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
							// If we get here the post has been inserted successfully.
							$msg = "$l_stored<br />$l_click <a href=\"".append_sid("viewtopic.$phpEx?".POST_TOPIC_URL."=$new_topic_id#$new_post_id")."\">$l_here</a>
  										$l_viewmsg<br />$l_click <a href=\"".append_sid("viewforum.$phpEx?".POST_FORUM_URL."=$forum_id")."\">$l_here</a> $l_returntopic";

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
							error_die(QUERY_ERROR);
						}
					}
					else
					{
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
		if(isset($HTTP_POST_VARS['submit']) && !$error)
		{
			if(isset($HTTP_POST_VARS['delete_post']))
			{


			}
			else
			{
				$post_id = $HTTP_POST_VARS[POST_POST_URL];
				$new_topic_id = $HTTP_POST_VARS[POST_TOPIC_URL];

				$sql = "UPDATE ".POSTS_TEXT_TABLE." SET post_text = '$message', post_subject = '$subject' WHERE post_id = ".$HTTP_POST_VARS[POST_POST_URL];
				if($db->sql_query($sql))
				{
					if($is_first_post)
					{
						// Update topics table here, set notification level and such
					}
					else
					{
						include('includes/page_header.'.$phpEx);
						// If we get here the post has been inserted successfully.
						$msg = "$l_stored<br />$l_click <a href=\"".append_sid("viewtopic.$phpEx?".POST_TOPIC_URL."=$new_topic_id#$post_id")."\">$l_here</a>
  									$l_viewmsg<br />$l_click <a href=\"".append_sid("viewforum.$phpEx?".POST_FORUM_URL."=$forum_id")."\">$l_here</a> $l_returntopic";

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
		else
		{
			$post_id = ($HTTP_GET_VARS[POST_POST_URL]) ? $HTTP_GET_VARS[POST_POST_URL] : $HTTP_POST_VARS[POST_POST_URL];
			if(!empty($post_id))
			{

   			$sql = "SELECT p.*, pt.post_text, pt.post_subject, u.username, u.user_id, u.user_sig, t.topic_title, t.topic_notify
   						FROM ".POSTS_TABLE." p, ".USERS_TABLE." u, ".TOPICS_TABLE." t, ".POSTS_TEXT_TABLE." pt
   						WHERE (p.post_id = '$post_id')
   							AND pt.post_id = p.post_id
   							AND (p.topic_id = t.topic_id)
   							AND (p.poster_id = u.user_id)";

				if($result = $db->sql_query($sql))
				{
					$postrow = $db->sql_fetchrowset($result);
					$subject = stripslashes($postrow[0]['post_subject']);
					$message = stripslashes($postrow[0]['post_text']);
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

   				// is_first_post needs functionality!
   				if($postrow[0]['topic_notify'] && $is_first_post)
   				{
   					$notify = TRUE;
   				}

					if($is_first_post)
					{
						$subject = stripslashes($postrow[0]['topic_title']);
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

//
// Show the same form for each mode.
//
		if(!isset($HTTP_GET_VARS[POST_FORUM_URL]) && !isset($HTTP_POST_VARS[POST_FORUM_URL]))
		{
			error_die(GENERAL_ERROR, "Sorry, no there is no such forum");
		}

		$sql = "SELECT forum_name, forum_access
					FROM ".FORUMS_TABLE."
					WHERE forum_id = $forum_id";
		if(!$result = $db->sql_query($sql))
		{
			error_die(SQL_QUERY, "Could not obtain forum/forum access information.", __LINE__, __FILE__);
		}
		$forum_info = $db->sql_fetchrow($result);
		$forum_name = stripslashes($forum_info['forum_name']);
		$forum_access = $forum_info['forum_access'];

		if($forum_access == ANONALLOWED)
		{
			$about_posting = "$l_anonusers $l_inthisforum $l_anonhint";
		}
		if($forum_access == REGONLY)
		{
			$about_posting = "$l_regusers $l_inthisforum";
		}
		if($forum_access == MODONLY)
		{
			$about_posting = "$l_modusers $l_inthisforum";
		}

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
			"L_POSTNEWIN" => $section_title,
			"FORUM_ID" => $forum_id,
			"FORUM_NAME" => $forum_name,

			"U_VIEW_FORUM" => append_sid("viewforum.$phpEx?".POST_FORUM_URL."=$forum_id"))
		);

		if($userdata['session_logged_in'])
		{
			$username_input = $userdata["username"];
			$password_input = "";
		}
		else
		{
			if(!isset($username))
			{
				$username = $userdata["username"];
			}
			$username_input = '<input type="text" name="username" value="'.$username.'" size="25" maxlength="50">';
			$password_input = '<input type="password" name="password" size="25" maxlenght="40">';
		}
		$subject_input = '<input type="text" name="subject" value="'.$subject.'" size="50" maxlenght="255">';
		$message_input = '<textarea name="message" rows="10" cols="35" wrap="virtual">'.$message.'</textarea>';

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

		if($mode == 'newtopic' || ($mode == 'editpost' && $notify))
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
		$hidden_form_fields = "<input type=\"hidden\" name=\"mode\" value=\"$mode\"><input type=\"hidden\" name=\"".POST_FORUM_URL."\" value=\"$forum_id\"><input type=\"hidden\" name=\"".POST_TOPIC_URL."\" value=\"$topic_id\"><input type=\"hidden\" name=\"".POST_POST_URL."\" value=\"$post_id\">";

		$template->assign_vars(array(
			"L_ABOUT_POST" => $l_aboutpost,
			"L_SUBJECT" => $l_subject,
			"L_MESSAGE_BODY" => $l_body,
			"L_OPTIONS" => $l_options,
			"L_PREVIEW" => $l_preview,
			"L_SUBMIT" => $l_submit,
			"L_CANCEL" => $l_cancelpost,

			"ABOUT_POSTING" => $about_posting,
			"USERNAME_INPUT" => $username_input,
			"PASSWORD_INPUT" => $password_input,
			"SUBJECT_INPUT" => $subject_input,
			"MESSAGE_INPUT" => $message_input,
			"HTML_STATUS" => $html_status,
			"HTML_TOGGLE" => $html_toggle,
			"SMILE_TOGGLE" => $smile_toggle,
			"SIG_TOGGLE" => $sig_toggle,
			"NOTIFY_TOGGLE" => $notify_toggle,
			"BBCODE_TOGGLE" => $bbcode_toggle,
			"BBCODE_STATUS" => $bbcode_status,

			"S_POST_ACTION" => append_sid("posting.$phpEx"),
			"S_HIDDEN_FORM_FIELDS" => $hidden_form_fields)
		);

		$template->pparse("body");

		include('includes/page_tail.'.$phpEx);
?>