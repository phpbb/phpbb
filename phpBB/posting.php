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
function prepare_message($message, $html_on, $bbocde_on, $smile_on, $bbcode_uid = 0)
{
	$message = trim($message);
	
	if(!$html_on) 
	{
		$message = htmlspecialchars($message);
	}
	
	if($bbocde_on)
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


switch($mode)
{
	case 'newtopic':
		if(isset($HTTP_POST_VARS['submit']))
		{
			if(isset($HTTP_POST_VARS['disable_html']) || !$board_config['allow_html'])
			{
				$html_on = FALSE;
			}
			else
			{
				$html_on = TRUE;
			}
			
			if(isset($HTTP_POST_VARS['disable_bbcode']) || !$board_config['allow_bbcode'])
			{
				$bbcode_on = FALSE;
			}
			else
			{
				$uid = make_bbcode_uid();
				$bbocde_on = TRUE;
			}
			
			if(isset($HTTP_POST_VARS['disable_smile']))
			{
				$smile_on = FALSE;
			}
			else
			{
				$smile_on = TRUE;
			}
			
			$message = prepare_message($HTTP_POST_VARS['message'], $html_on, $bbocde_on, $smile_on, $uid);
			
			if(isset($HTTP_POST_VARS['attach_sig']) && !empty($userdata['user_sig']))
			{
				$message .= "[addsig]";
			}
			$subject = trim(strip_tags(htmlspecialchars($HTTP_POST_VARS['subject'])));
			$topic_time = gmmktime(date("h, i, s, m, d, Y"));
			$topic_notify = ($HTTP_POST_VARS['notify']) ? $HTTP_POST_VARS['notify'] : 0;
			$sql  = "INSERT INTO ".TOPICS_TABLE." (topic_title, topic_poster, topic_time, forum_id, topic_notify, topic_status)
						VALUES ('$subject', ".$userdata['user_id'].", ".$topic_time.", $forum_id, $topic_notify, ".UNLOCKED.")";
			
			if($db->sql_query($sql))
			{
				$new_topic_id = $db->sql_nextid();
				$sql = "INSERT INTO ".POSTS_TABLE." (topic_id, forum_id, poster_id, post_time, poster_ip, bbcode_uid)
						  VALUES ($new_topic_id, $forum_id, ".$userdata['user_id'].", $topic_time, '".encode_ip($user_ip)."', '$uid')";
				
				if($db->sql_query($sql))
				{
					$new_post_id = $db->sql_nextid();
					$sql = "INSERT INTO ".POSTS_TEXT_TABLE." VALUES ($new_post_id, '".$message."')";
					if($db->sql_query($sql))
					{
						$sql = "UPDATE ".TOPICS_TABLE." SET topic_last_post_id = $new_post_id WHERE topic_id = $new_topic_id";
						if($db->sql_query($sql))
						{
							$sql = "UPDATE ".FORUMS_TABLE." SET forum_last_post_id = $new_post_id, forum_posts = forum_posts + 1, forum_topics = forum_topics + 1 WHERE forum_id = $forum_id";
							if($db->sql_query($sql))
							{
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
		if(!isset($HTTP_GET_VARS[POST_FORUM_URL]) && !isset($HTTP_POST_VARS[POST_FORUM_URL]))
		{
			error_die(GENERAL_ERROR, "Sorry, no there is no such forum");
		}

		$pagetype = "newtopic";
		$page_title = " $l_postnew";
	
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

		include('includes/page_header.'.$phpEx);

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
										"L_POSTNEWIN" => $l_postnewin,
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

		$smile_toggle = '<input type="checkbox" name="disable_smile" ';
		if($disable_smile)
		{
			$smile_toggle .= "checked";
		}
		$smile_toggle .= "> $l_disable $l_smilies $l_onthispost";

		$sig_toggle = '<input type="checkbox" name="attach_sig" ';
		if($attach_sig || $userdata["attach_sig"] == 1)
		{
			$sig_toggle .= "checked";
		}
		$sig_toggle .= "> $l_attachsig";

		$notify_toggle = '<input type="checkbox" name="notify" ';
		if($notify || $userdata["always_notify"] == 1)
		{
			$notify_toggle .= "checked";
		}
		$notify_toggle .= "> $l_notify";

		$hidden_form_fields = "<input type=\"hidden\" name=\"mode\" value=\"$mode\"><input type=\"hidden\" name=\"".POST_FORUM_URL."\" value=\"$forum_id\"><input type=\"hidden\" name=\"topic_id\" value=\"$topic_id\">";

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
		break;
	case 'reply':

		break;
	case 'editpost':

		break;
}


?>
