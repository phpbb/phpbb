<?php
/***************************************************************************
 *                               privmsgs.php
 *                            -------------------
 *   begin                : Saturday, Jun 9, 2001
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
include('includes/post.'.$phpEx);
include('includes/bbcode.'.$phpEx);

$pagetype = "privmsgs";
$page_title = "Private Messageing";

//
// Start session management
//
$userdata = session_pagestart($user_ip, PAGE_PRIVMSGS, $session_length);
init_userprefs($userdata);
//
// End session management
//


$folder = (!empty($HTTP_POST_VARS['folder'])) ? $HTTP_POST_VARS['folder'] : ( (!empty($HTTP_GET_VARS['folder'])) ? $HTTP_GET_VARS['folder'] : "inbox" );
if(empty($HTTP_POST_VARS['cancel']))
{
	$mode = (!empty($HTTP_POST_VARS['mode'])) ? $HTTP_POST_VARS['mode'] : ( (!empty($HTTP_GET_VARS['mode'])) ? $HTTP_GET_VARS['mode'] : "" );
}
else
{
	$mode = "";
}
$start = (!empty($HTTP_GET_VARS['start'])) ? $HTTP_GET_VARS['start'] : 0;

//
// Start main
//
if($mode == "read")
{

	if(!empty($HTTP_GET_VARS[POST_POST_URL]))
	{
		$privmsgs_id = $HTTP_GET_VARS[POST_POST_URL]; 
	}
	else
	{
		// Error out

	}

	if(!$userdata['session_logged_in'])
	{
		header("Location: " . append_sid("login.$phpEx?forward_page=privmsg.$phpEx&folder=$folder&mode=$mode&" . POST_POST_URL . "=$privmsgs_id"));
	}

	if(!empty($HTTP_GET_VARS['folder']))
	{
		if($folder == "inbox")
		{
			$user_to_sql = "AND pm.privmsgs_to_userid = " . $userdata['user_id'];
			$user_from_sql = "AND u.user_id = pm.privmsgs_from_userid"; 
			$sql_type = "AND (pm.privmsgs_type = " . PRIVMSGS_READ_MAIL . " OR pm.privmsgs_type = " . PRIVMSGS_NEW_MAIL . " )";
		}
		else if($folder == "outbox")
		{
			$user_to_sql = "AND u.user_id = pm.privmsgs_to_userid";
			$user_from_sql = "AND pm.privmsgs_from_userid =  " . $userdata['user_id'];
			$sql_type = "AND pm.privmsgs_type = " . PRIVMSGS_NEW_MAIL;
		}
		else if($folder == "sentbox")
		{
			$user_to_sql = "AND u.user_id = pm.privmsgs_to_userid";
			$user_from_sql = "AND pm.privmsgs_from_userid =  " . $userdata['user_id'];
			$sql_type = "AND pm.privmsgs_type = " . PRIVMSGS_SENT_MAIL;
		}
		else if($folder == "savebox")
		{
			$user_to_sql = "AND ( (pm.privmsgs_to_userid = " . $userdata['user_id'] . " AND u.user_id = pm.privmsgs_from_userid) ";
			$user_from_sql = "OR (u.user_id = pm.privmsgs_to_userid AND pm.privmsgs_from_userid =  " . $userdata['user_id'] . ") )";
			$sql_type = "AND pm.privmsgs_type = " . PRIVMSGS_SAVED_MAIL;
		}
		else
		{
			// Error out
		}
	}
	else
	{
		// Error out

	}

	$sql = "SELECT u.username, u.user_id, u.user_website, u.user_icq, u.user_aim, u.user_yim, u.user_msnm, u.user_viewemail, u.user_sig, u.user_avatar, pm.privmsgs_id, pm.privmsgs_type, pm.privmsgs_subject, pm.privmsgs_from_userid, pm.privmsgs_to_userid, pm.privmsgs_date, pm.privmsgs_ip, pm.privmsgs_bbcode_uid, pmt.privmsgs_text 
		FROM ".PRIVMSGS_TABLE." pm, " . PRIVMSGS_TEXT_TABLE . " pmt, ".USERS_TABLE." u  
		WHERE pm.privmsgs_id = $privmsgs_id 
			AND pmt.privmsgs_text_id = pm.privmsgs_id 
			$user_to_sql 
			$user_from_sql 
			$sql_type";
	if(!$pm_status = $db->sql_query($sql))
	{
		error_die(SQL_QUERY, "Could not query private message post information.", __LINE__, __FILE__);
	}
	if(!$db->sql_numrows($pm_status))
	{
		header("Location: " . append_sid("privmsg.$phpEx?folder=$folder"));
	}

	$privmsg = $db->sql_fetchrow($pm_status);

	if($privmsg['privmsgs_type'] == PRIVMSGS_NEW_MAIL && $folder == "inbox")
	{
		$sql = "UPDATE " . PRIVMSGS_TABLE . " 
			SET privmsgs_type = " . PRIVMSGS_READ_MAIL . " 
			WHERE privmsgs_id = " . $privmsg['privmsgs_id'];
		if(!$pm_upd_status = $db->sql_query($sql))
		{
			error_die(SQL_QUERY, "Could not update private message read status.", __LINE__, __FILE__);
		}

		//
		// This makes a copy of the post and stores
		// it as a SENT message from the sendee. Perhaps
		// not the most DB friendly way but a lot easier
		// to manage, besides the admin will be able to
		// set limits on numbers of storable posts for
		// users ... hopefully!
		//
		$sql = "INSERT INTO " . PRIVMSGS_TABLE . " (privmsgs_type, privmsgs_subject, privmsgs_from_userid, privmsgs_to_userid, privmsgs_date, privmsgs_ip, privmsgs_bbcode_uid) 
			VALUES (" . PRIVMSGS_SENT_MAIL . ", '" . $privmsg['privmsgs_subject'] . "', " . $privmsg['privmsgs_from_userid'] . ", " . $privmsg['privmsgs_to_userid'] . ", " . $privmsg['privmsgs_date'] . ", '" . $privmsg['privmsgs_ip'] . "', '" . $privmsg['privmsgs_bbcode_uid'] . "')";
		if(!$pm_sent_status = $db->sql_query($sql))
		{
			error_die(SQL_QUERY, "Could not insert private message sent info.", __LINE__, __FILE__);
		}
		else
		{
			$privmsg_sent_id = $db->sql_nextid($pm_sent_status);

			$sql = "INSERT INTO " . PRIVMSGS_TEXT_TABLE . " (privmsgs_text_id, privmsgs_text) 
				VALUES ($privmsg_sent_id, '" . $privmsg['privmsgs_text'] . "')";
			if(!$pm_sent_text_status = $db->sql_query($sql))
			{
				error_die(SQL_QUERY, "Could not insert private message sent text.<BR>$sql", __LINE__, __FILE__);
			}
		}
	}

	//
	// These may well be better handled in the
	// templates
	//
	$inbox_url = "<img src=\"images/msg_inbox.gif\" border=\"0\">&nbsp;<a href=\"" . append_sid("privmsg.$phpEx?folder=inbox") . "\"><b>" . $lang['Inbox'] . "</b></a>";

	$sentbox_url =  "<img src=\"images/msg_sentbox.gif\" border=\"0\">&nbsp;<a href=\"" . append_sid("privmsg.$phpEx?folder=sentbox") . "\"><b>" . $lang['Sent'] . "</b></a>";

	$outbox_url =  "<img src=\"images/msg_outbox.gif\" border=\"0\">&nbsp;<a href=\"" . append_sid("privmsg.$phpEx?folder=outbox") . "\"><b>" . $lang['Outbox'] . "</b></a>";

	$savebox_url = "<img src=\"images/msg_savebox.gif\" border=\"0\">&nbsp;<a href=\"" . append_sid("privmsg.$phpEx?folder=savebox") . "\"><b>" . $lang['Saved'] . "</b></a>";

	$post_new_mesg_url = "<a href=\"privmsg.$phpEx?mode=post\"><img src=\"templates/PSO/images/post.gif\" border=\"1\"></a>";
	$post_reply_mesg_url = ($folder == "inbox") ? "<a href=\"privmsg.$phpEx?mode=reply&" . POST_POST_URL . "=$privmsgs_id\"><img src=\"templates/PSO/images/reply.gif\" border=\"1\"></a>" : "";

	$s_hidden_fields = "<input type=\"hidden\" name=\"mark[]\" value=\"$privmsgs_id\">";

	include('includes/page_header.'.$phpEx);

	//
	// Load templates
	//
	$template->set_filenames(array(
		"body" => "privmsgs_read_body.tpl",
		"jumpbox" => "jumpbox.tpl")
	);
	$jumpbox = make_jumpbox();
	$template->assign_vars(array(
		"JUMPBOX_LIST" => $jumpbox,
		"SELECT_NAME" => POST_FORUM_URL)
	);
	$template->assign_var_from_handle("JUMPBOX", "jumpbox");

	$template->assign_vars(array(
		"INBOX" => $inbox_url, 
		"SENTBOX" => $sentbox_url, 
		"OUTBOX" => $outbox_url, 
		"SAVEBOX" => $savebox_url, 

		"L_FLAG" => $lang['Flag'],
		"L_SUBJECT" => $lang['Subject'],
		"L_DATE" => $lang['Date'], 
		"L_FROM_OR_TO" => (($folder == "inbox" || $folder == "savebox") ? $lang['From'] : $lang['To']),

		"S_PRIVMSGS_ACTION" => append_sid("privmsg.$phpEx?folder=$folder"), 
		"S_HIDDEN_FIELDS" => $s_hidden_fields, 
		"S_POST_NEW_MSG" => $post_new_mesg_url,
		"S_POST_REPLY_MSG" => $post_reply_mesg_url)
	);

	$poster = stripslashes($privmsg['username']);
	$poster_id = $privmsg['user_id'];

	$post_date = create_date($board_config['default_dateformat'], $privmsg['privmsgs_date'], $board_config['default_timezone']);

	$poster_avatar = ($privmsg['user_avatar'] != "" && $userdata['user_id'] != ANONYMOUS) ? "<img src=\"" . $board_config['avatar_path'] . "/" . $privmsg['user_avatar'] . "\">" : "";

	$profile_img = "<a href=\"" . append_sid("profile.$phpEx?mode=viewprofile&" . POST_USERS_URL . "=$poster_id") . "\"><img src=\"" . $images['profile'] . "\" alt=\"$l_profileof $poster\" border=\"0\"></a>";

	$email_img = ($privmsg['user_viewemail'] == 1) ? "<a href=\"mailto:" . $privmsg['user_email'] . "\"><img src=\"" .$images['email'] . "\" alt=\"$l_email $poster\" border=\"0\"></a>" : "";

	$www_img = ($privmsg['user_website']) ? "<a href=\"" . $privmsg['user_website'] . "\"><img src=\"" . $images['www'] . "\" alt=\"$l_viewsite\" border=\"0\"></a>" : "";

	if($privmsg['user_icq'])
	{
		$icq_status_img = "<a href=\"http://wwp.icq.com/" . $privmsg['user_icq'] . "#pager\"><img src=\"http://online.mirabilis.com/scripts/online.dll?icq=" . $privmsg['user_icq'] . "&img=5\" alt=\"$l_icqstatus\" border=\"0\"></a>";

		$icq_add_img = "<a href=\"http://wwp.icq.com/scripts/search.dll?to=" . $privmsg['user_icq'] . "\"><img src=\"" . $images['icq'] . "\" alt=\"$l_icq\" border=\"0\"></a>";
	}
	else
	{
		$icq_status_img = "";
		$icq_add_img = "";
	}

	$aim_img = ($privmsg['user_aim']) ? "<a href=\"aim:goim?screenname=" . $privmsg['user_aim'] . "&message=Hello+Are+you+there?\"><img src=\"" . $images['aim'] . "\" border=\"0\"></a>" : "";

	$msn_img = ($privmsg['user_msnm']) ? "<a href=\"profile.$phpEx?mode=viewprofile&" . POST_USERS_URL . "=$poster_id\"><img src=\"" . $images['msn'] . "\" border=\"0\"></a>" : "";

	$yim_img = ($privmsg['user_yim']) ? "<a href=\"http://edit.yahoo.com/config/send_webmesg?.target=" . $privmsg['user_yim'] . "&.src=pg\"><img src=\"" . $images['yim'] . "\" border=\"0\"></a>" : "";

	if($folder == "inbox")
	{
		$quote_img = "<a href=\"" . append_sid("privmsg.$phpEx?mode=reply&quote=true&" . POST_POST_URL . "=" . $privmsgs_id) . "\"><img src=\"" . $images['quote'] . "\" alt=\"\" border=\"0\"></a>";
	}

	if($folder == "outbox")
	{
		$edit_img = "<a href=\"" . append_sid("privmsg.$phpEx?folder=$folder&mode=edit&" . POST_POST_URL . "=" . $privmsgs_id) . "\"><img src=\"" . $images['edit'] . "\" alt=\"\" border=\"0\"></a>";
	}

	$post_subject = stripslashes($privmsg['privmsgs_subject']);

	$message = stripslashes($privmsg['privmsgs_text']);
	$bbcode_uid = $privmsg['privmsgs_bbcode_uid'];

	$user_sig = ($privmsg['privmsgs_from_userid'] == $userdata['user_id']) ? stripslashes($userdata['user_sig']) : stripslashes($privmsg['user_sig']);

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
	
	$template->assign_vars(array(
		"POSTER_NAME" => $poster,
		"POSTER_AVATAR" => $poster_avatar,
		"POST_DATE" => $post_date,
		"POST_SUBJECT" => $post_subject,
		"MESSAGE" => $message,
		"PROFILE_IMG" => $profile_img,
		"EMAIL_IMG" => $email_img,
		"WWW_IMG" => $www_img,
		"ICQ_STATUS_IMG" => $icq_status_img,
		"ICQ_ADD_IMG" => $icq_add_img,
		"AIM_IMG" => $aim_img,
		"MSN_IMG" => $msn_img,
		"YIM_IMG" => $yim_img,
		"QUOTE_IMG" => $quote_img, 
		"EDIT_IMG" => $edit_img, 

		"L_FROM" => $lang['From'])
	);

	$template->pparse("body");

	include('includes/page_tail.'.$phpEx);

}
else if($mode == "post" || $mode == "reply" || $mode == "edit")
{
	// -----------------------------
	// Posting capabilities are here
	// -----------------------------

	if(!$userdata['session_logged_in'])
	{
		header("Location: " . append_sid("login.$phpEx?forward_page=privmsg.$phpEx&folder=$folder&mode=$mode"));
	}

	if(!$userdata['user_allow_pm'])
	{
		//
		// Admin has prevented user
		// from sending PM's
		//
		include('includes/page_header.'.$phpEx);

		$msg = $lang['Cannot_send_privmsg'];

		$template->set_filenames(array(
			"reg_header" => "error_body.tpl")
		);
		$template->assign_vars(array(
			"ERROR_MESSAGE" => $msg)
		);
		$template->pparse("reg_header");

		include('includes/page_tail.'.$phpEx);
	}

	//
	// When we get to the point of a code review
	// we really really really need to look at 
	// combining the following fragments with the 
	// posting routine. I don't think or see it 
	// necessary to actually use posting for privmsgs
	// but I'm sure more can be combined in common
	// functions ... not that I think all functions are
	// common, some functions are actually quite classy
	// and sophisticated, champagne, caviar and all that
	//

	$disable_html = (isset($HTTP_POST_VARS['disable_html'])) ? $HTTP_POST_VARS['disable_html'] : !$userdata['user_allowhtml'];
	$disable_bbcode = (isset($HTTP_POST_VARS['disable_bbcode'])) ? $HTTP_POST_VARS['disable_bbcode'] : !$userdata['user_allowbbcode'];
	$disable_smilies = (isset($HTTP_POST_VARS['disable_smile'])) ? $HTTP_POST_VARS['disable_smile'] : !$userdata['user_allowsmile'];
	$attach_sig = (isset($HTTP_POST_VARS['attach_sig'])) ? $HTTP_POST_VARS['attach_sig'] : $userdata['user_attachsig'];
	$preview = (isset($HTTP_POST_VARS['preview'])) ? TRUE : FALSE;
	$submit = (isset($HTTP_POST_VARS['submit'])) ? TRUE : FALSE;

	if($mode == "reply" || $mode == "edit")
	{
		if(!empty($HTTP_GET_VARS[POST_POST_URL]))
		{
			$privmsgs_id = $HTTP_GET_VARS[POST_POST_URL]; 
		}
		else if(!empty($HTTP_POST_VARS[POST_POST_URL]))
		{
			$privmsgs_id = $HTTP_POST_VARS[POST_POST_URL]; 
		}
		else
		{
			// Error out
		}
	}

	if(empty($HTTP_GET_VARS[POST_USERS_URL]) && !$preview && empty($HTTP_POST_VARS['submit']))
	{
		$user_id = $HTTP_GET_VARS[POST_USERS_URL];

		$sql = "SELECT username 
			FROM " . USERS_TABLE . " 
			WHERE user_id = $user_id 
				AND user_id <> " . ANONYMOUS;
		if(!$result = $db->sql_query($sql))
		{
			$error = TRUE;
			$error_msg = $lang['No_such_user'];
		}
		else
		{
			list($to_username) = $db->sql_fetchrow($result);
			$to_username = stripslashes($to_username);
		}
	}
	else
	{
		if(!empty($HTTP_POST_VARS['username_list']))
		{
			$to_username = $HTTP_POST_VARS['username_list'];
		}
		else
		{
			$to_username = "";
		}
	}


	//
	// Process the username list operations
	//
	if( !empty($HTTP_POST_VARS['usersubmit']))
	{
		if(!empty($HTTP_POST_VARS['username_search']) && !$preview)
		{
			$username_search = stripslashes(str_replace("*", "%", $HTTP_POST_VARS['username_search']));
			$first_letter = 65;

			$sql = "SELECT username 
				FROM " . USERS_TABLE . " 
				WHERE ( username LIKE '%$username_search' 
					OR username LIKE '$username_search%' 
					OR username LIKE '%$username_search%' 
					OR username LIKE '$username_search' ) 
					AND user_id <> " . ANONYMOUS;

		}
		else
		{
			$first_letter = $HTTP_POST_VARS['user_alpha'];

			$sql = "SELECT username 
				FROM " . USERS_TABLE . " 
				WHERE ( username LIKE '" . chr($first_letter) . "%' 
					OR username LIKE '" . chr($first_letter) . "' ) 
					AND user_id <> " . ANONYMOUS;

		}

	}
	else
	{
		$first_letter = (!empty($to_username)) ? ord(ucfirst($to_username)) : 65;

		$sql = "SELECT username 
			FROM " . USERS_TABLE . " 
			WHERE ( username LIKE '" . chr($first_letter) . "%' 
				OR username LIKE '" . chr($first_letter) . "' ) 
				AND user_id <> " . ANONYMOUS;
	}

	$result = $db->sql_query($sql);
	$name_set = $db->sql_fetchrowset($result);

	$user_names_select = "<select name=\"username_list\">";
	if($db->sql_numrows($result))
	{
		for($i = 0; $i < count($name_set); $i++)
		{
			$name_selected = ($to_username == $name_set[$i]['username']) ? " selected" : "";
			$user_names_select .=  "<option value=\"" . $name_set[$i]['username'] . "\"$name_selected>" . $name_set[$i]['username'] . "</option>\n";
		}
	}
	else
	{
		$user_names_select .=  "<option value=\"" . ANONYMOUS . "\"$name_selected>" . $lang['No_match'] . "</option>\n";
	}
	$user_names_select .= "</select>";

	$user_alpha_select = "<select name=\"user_alpha\">";
	for($i = 65; $i < 91; $i++)
	{
		if($first_letter == $i)
		{
			$user_alpha_select .= "<option value=\"$i\" selected>" . chr($i) . "</option>";
		}
		else
		{
			$user_alpha_select .= "<option value=\"$i\">" . chr($i) . "</option>";
		}
	}
	$user_alpha_select .= "</select>";

	if($mode == "edit" && !$preview && !$submit)
	{ 

		$sql = "SELECT pm.privmsgs_id, pm.privmsgs_subject, pmt.privmsgs_text, u.username, u.user_id 
			FROM " . PRIVMSGS_TABLE . " pm, " . PRIVMSGS_TEXT_TABLE . " pmt, " . USERS_TABLE . " u 
			WHERE pm.privmsgs_id = $privmsgs_id 
				AND pmt.privmsgs_text_id = pm.privmsgs_id 
				AND pm.privmsgs_from_userid = " . $userdata['user_id'] . " 
				AND pm.privmsgs_type = " . PRIVMSGS_NEW_MAIL . " 
				AND u.user_id = pm.privmsgs_to_userid";
		if(!$pm_edit_status = $db->sql_query($sql))
		{
			error_die(SQL_QUERY, "Could not obtain private message for editing.", __LINE__, __FILE__);
		}
		if(!$db->sql_numrows($pm_edit_status))
		{
			header("Location: " . append_sid("privmsg.$phpEx?folder=$folder"));
		}

		$privmsg = $db->sql_fetchrow($pm_edit_status);

		$subject = stripslashes($privmsg['privmsgs_subject']);
		$message = stripslashes($privmsg['privmsgs_text']); 
		$message = str_replace("[addsig]", "", $message);
		$message = preg_replace("/\:[0-9a-z\:]*?\]/si", "]", $message);

		$to_username = stripslashes($privmsg['username']);
		$to_userid = $privmsg['user_id'];

	}
	else if($mode == "reply" && !$preview && !$submit)
	{

		$sql = "SELECT pm.privmsgs_subject, pm.privmsgs_date, pmt.privmsgs_text, u.username, u.user_id 
			FROM " . PRIVMSGS_TABLE . " pm, " . PRIVMSGS_TEXT_TABLE . " pmt, " . USERS_TABLE . " u 
			WHERE pm.privmsgs_id = $privmsgs_id 
				AND pmt.privmsgs_text_id = pm.privmsgs_id 
				AND pm.privmsgs_to_userid = " . $userdata['user_id'] . " 
				AND pm.privmsgs_type = " . PRIVMSGS_READ_MAIL . " 
				AND u.user_id = pm.privmsgs_from_userid";
		if(!$pm_reply_status = $db->sql_query($sql))
		{
			error_die(SQL_QUERY, "Could not obtain private message for editing.", __LINE__, __FILE__);
		}
		if(!$db->sql_numrows($pm_reply_status))
		{
			header("Location: " . append_sid("privmsg.$phpEx?folder=$folder"));
		}
		$privmsg = $db->sql_fetchrow($pm_reply_status);

		$subject = $lang['Re'] . ":"  . stripslashes($privmsg['privmsgs_subject']);

		$to_username = stripslashes($privmsg['username']);
		$to_userid = $privmsg['user_id'];

		if(isset($HTTP_GET_VARS['quote']))
		{
			$msg_date =  create_date($board_config['default_dateformat'], $privmsg['privmsgs_date'], $board_config['default_timezone']); //"[date]" . $privmsg['privmsgs_time'] . "[/date]";

			$message = stripslashes(str_replace("[addsig]", "", $privmsg['privmsgs_text'])); 
			$message = preg_replace("/\:[0-9a-z\:]*?\]/si", "]", $message);
			$message = "On " . $msg_date . " " . $to_username . " wrote:\n\n[quote]\n" . $message . "\n[/quote]";
		}

	}

	if($submit || $preview)
	{
		//
		// Flood control
		//
		if($mode != 'edit' && !$preview)
		{
			$sql = "SELECT MAX(privmsgs_date) AS last_post_time
				FROM " . PRIVMSGS_TABLE . "
				WHERE privmsgs_ip = '$user_ip'";
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
		// End: Flood control
		//

		$subject = (!empty($HTTP_POST_VARS['subject'])) ? $HTTP_POST_VARS['subject'] : "";
		$subject = trim(strip_tags(htmlspecialchars($subject)));
		$message = (!empty($HTTP_POST_VARS['message'])) ? $HTTP_POST_VARS['message'] : "";

		if(empty($subject))
		{
			$error = TRUE;
			if(isset($error_msg))
			{
				$error_msg .= "<br />";
			}
			$error_msg .= $lang['Empty_subject'];
		}

		if(!empty($message))
		{
			if(!$error && !$preview)
			{
				$html_on = ($disable_html) ? FALSE : TRUE;
				$bbcode_on = ($diable_bbcode) ? FALSE : TRUE;
				$smile_on = ($disable_smilies) ? FALSE : TRUE;

				$bbcode_uid = make_bbcode_uid();

				$message = prepare_message($message, $html_on, $bbcode_on, $smile_on, $bbcode_uid);
				$message = preg_replace('#</textarea>#si', '&lt;/TEXTAREA&gt;', $message);

				if($attach_sig && !empty($userdata['user_sig']))
				{
					$message .= "[addsig]";
				}
			}
			else
			{
				// do stripslashes incase magic_quotes is on.
				$message = stripslashes($HTTP_POST_VARS['message']);
				$message = preg_replace('#</textarea>#si', '&lt;/TEXTAREA&gt;', $message);
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

		if(!empty($to_username))
		{
			$sql = "SELECT user_id, username, user_notify_pm, user_email   
				FROM " . USERS_TABLE . " 
				WHERE username = '" . addslashes($to_username) . "' 
					AND user_id <> " . ANONYMOUS;
			if(!$result = $db->sql_query($sql))
			{
				$error = TRUE;
				$error_msg = $lang['No_such_user'];
			}
			else
			{
				$to_userdata = $db->sql_fetchrow($result);
			}
		}
		else
		{
			$error = TRUE;
			if(isset($error_msg))
			{
				$error_msg .= "<br />";
			}
			$error_msg .= $lang['No_to_user'];
		}

		if(!$preview)
		{
			$msg_time = get_gmt_ts();

			if($mode != "edit")
			{
				$sql_info = "INSERT INTO " . PRIVMSGS_TABLE . " (privmsgs_type, privmsgs_subject, privmsgs_from_userid, privmsgs_to_userid, privmsgs_date, privmsgs_ip, privmsgs_bbcode_uid) 
					VALUES (" . PRIVMSGS_NEW_MAIL . ", '$subject', " . $userdata['user_id'] . ", " . $to_userdata['user_id'] . ", $msg_time, '$user_ip', '" . $bbcode_uid . "')";
			}
			else
			{
				$sql_info = "UPDATE " . PRIVMSGS_TABLE . " 
					SET privmsgs_type = " . PRIVMSGS_NEW_MAIL . ", privmsgs_subject = '$subject', privmsgs_from_userid = " . $userdata['user_id'] . ", privmsgs_to_userid = " . $to_userdata['user_id'] . ", privmsgs_date = $msg_time, privmsgs_ip = '$user_ip', privmsgs_bbcode_uid = '$bbcode_uid' 
					WHERE privmsgs_id = $privmsgs_id";	
			}

			if(!$pm_sent_status = $db->sql_query($sql_info))
			{
				error_die(SQL_QUERY, "Could not insert/update private message sent info.", __LINE__, __FILE__);
			}
			else
			{
				$privmsg_sent_id = $db->sql_nextid($pm_sent_status);

				if($mode != "edit")
				{
					$sql = "INSERT INTO " . PRIVMSGS_TEXT_TABLE . " (privmsgs_text_id, privmsgs_text) 
						VALUES ($privmsg_sent_id, '$message')";
				}
				else
				{
					$sql = "UPDATE " . PRIVMSGS_TEXT_TABLE . " 
						SET privmsgs_text = '$message' 
						WHERE privmsgs_text_id = $privmsgs_id";
				}

				if(!$pm_sent_text_status = $db->sql_query($sql))
				{
					error_die(SQL_QUERY, "Could not insert/update private message sent text.", __LINE__, __FILE__);
				}
				else if($mode != "edit")
				{
					if($to_userdata['user_notify_pm'] && !empty($to_userdata['user_email']))
					{
						//mail($to_userdata['user_email'], $lang['Notification_subject'], $email_msg, "From: ".$board_config['board_email_from']."\r\n");
					}
				}

				include('includes/page_header.'.$phpEx);

				$msg = $lang['Message_sent'] . "<br /><br />" . $lang['Click'] . " <a href=\"" . append_sid("privmsg.$phpEx?folder=inbox") . "\">" . $lang['Here'] . "</a> " . $lang['to_return_inbox'] . "<br /><br />" . $lang['Click'] . " <a href=\"" . append_sid("index.$phpEx") . "\">" . $lang['Here'] . "</a> ". $lang['to_return_index'];

				$template->set_filenames(array(
					"reg_header" => "error_body.tpl")
				);
				$template->assign_vars(array(
					"ERROR_MESSAGE" => $msg)
				);
				$template->pparse("reg_header");

				include('includes/page_tail.'.$phpEx);
			}
		}
		
	}

	//
	// Obtain list of groups/users is
	// this user is a group moderator
	//
	if($mode == "post")
	{
		unset($mod_group_list);
		$sql = "SELECT g.group_id, g.group_name, g.group_moderator, g.group_single_user, u.username 
			FROM " . GROUPS_TABLE . " g, " . USER_GROUP_TABLE . " ug, " . USERS_TABLE . " u 
			WHERE g.group_moderator = " . $userdata['user_id'] ." 
				AND ug.group_id = g.group_id 
				AND u.user_id = ug.user_id";
		if(!$group_status = $db->sql_query($sql))
		{
			error_die(SQL_QUERY, "Could not obtain group moderator list.", __LINE__, __FILE__);
		}
		if($db->sql_numrows($group_status))
		{
			$mod_group_list = $db->sql_fetchrowset($group_status);
		}
	}

	include('includes/page_header.'.$phpEx);

	if($preview && !$error)
	{
		$bbcode_uid = make_bbcode_uid();

		$preview_message = $message;
		$preview_message = prepare_message($preview_message, TRUE, TRUE, TRUE, $bbcode_uid);
		$preview_message = bbencode_second_pass($preview_message, $bbcode_uid);
		$preview_message = make_clickable($preview_message);

		$s_hidden_fields = "<input type=\"hidden\" name=\"folder\" value=\"$folder\">";
		$s_hidden_fields .= "<input type=\"hidden\" name=\"mode\" value=\"$mode\">";
		if(isset($HTTP_GET_VARS['quote']))
		{
			$s_hidden_fields .= "<input type=\"hidden\" name=\"quote\" value=\"true\">";
		}
		if(isset($privmsg_id))
		{
			$s_hidden_fields .= "<input type=\"hidden\" name=\"" . POST_POST_URL . "\" value=\"$privmsgs_id\">";
		}

		$template->set_filenames(array(
			"preview" => "posting_preview.tpl")
		);
		$template->assign_vars(array(
			"TOPIC_TITLE" => $subject, 
			"POST_SUBJECT" => $subject, 
			"ROW_COLOR" => "#" . $theme['td_color1'],
			"POSTER_NAME" => $to_username,
			"POST_DATE" => create_date($board_config['default_dateformat'], time(), $board_config['default_timezone']),
			"MESSAGE" => stripslashes(nl2br($preview_message)),

			"S_HIDDEN_FIELDS" => $s_hidden_fields, 
		
			"L_PREVIEW" => $lang['Preview'],
			"L_POSTED" => $lang['Posted'])
		);
		$template->pparse("preview");
	}

	//
	// Load templates
	//
	$template->set_filenames(array(
		"body" => "privmsgs_posting_body.tpl",
		"jumpbox" => "jumpbox.tpl")
	);
	$jumpbox = make_jumpbox();
	$template->assign_vars(array(
		"JUMPBOX_LIST" => $jumpbox,
		"SELECT_NAME" => POST_FORUM_URL)
	);
	$template->assign_var_from_handle("JUMPBOX", "jumpbox");

	if($board_config['allow_html'])
	{
		$html_status = $lang['HTML'] . $lang['is_ON'];
		$html_toggle = '<input type="checkbox" name="disable_html" ';
		if($disable_html)
		{
			$html_toggle .= 'checked';
		}
		$html_toggle .= "> " . $lang['Disable'] . $lang['HTML'] . $lang['in_this_post'];
	}
	else
	{
		$html_status = $lang['HTML'] . $lang['is_OFF'];
	}

	if($board_config['allow_bbcode'])
	{
		$bbcode_status = $lang['BBCode'] . $lang['is_ON'];
		$bbcode_toggle = '<input type="checkbox" name="disable_bbcode" ';
		if($disable_bbcode)
		{
			$bbcode_toggle .= "checked";
		}
		$bbcode_toggle .= "> " . $lang['Disable'] . $lang['BBCode'] . $lang['in_this_post'];
	}
	else
	{
		$bbcode_status = $lang['BBCode'] . $lang['is_OFF'];
	}
		
	if($board_config['allow_smilies'])
	{
		$smile_toggle = '<input type="checkbox" name="disable_smile" ';
		if($disable_smilies)
		{
			$smile_toggle .= "checked";
		}
		$smile_toggle .= "> " . $lang['Disable'] . $lang['Smilies'] . $lang['in_this_post'];
	}

	$sig_toggle = '<input type="checkbox" name="attach_sig" ';
	if($attach_sig)
	{
		$sig_toggle .= "checked";
	}
	$sig_toggle .= "> " . $lang['Attach_signature'];

	if($mode == 'post')
	{
		$post_a = $lang['Send_a_new_message'];
	}
	else if($mode == 'reply')
	{
		$post_a = $lang['Send_a_reply'];
	}
	else if($mode == 'edit')
	{
		$post_a = $lang['Edit_message'];
	}

	$username_input = '<input type="text" name="username_search" value="' . $username_search . '">';
	$subject_input = '<input type="text" name="subject" value="' . $subject . '" size="50" maxlength="255">';
	$message_input = '<textarea name="message" rows="10" cols="40" wrap="virtual">' . $message . '</textarea>';

	$s_hidden_fields = "<input type=\"hidden\" name=\"folder\" value=\"$folder\">";
	$s_hidden_fields .= "<input type=\"hidden\" name=\"mode\" value=\"$mode\">";
	if($mode == "edit")
	{
		$s_hidden_fields .= "<input type=\"hidden\" name=\"" . POST_POST_URL . "\" value=\"$privmsgs_id\">";
	}
		
	$template->assign_vars(array(
		"S_USERNAME_INPUT" => $username_input, 
		"SUBJECT_INPUT" => $subject_input,
		"MESSAGE_INPUT" => $message_input,
		"HTML_STATUS" => $html_status,
		"HTML_TOGGLE" => $html_toggle,
		"SMILE_TOGGLE" => $smile_toggle,
		"SIG_TOGGLE" => $sig_toggle,
		"NOTIFY_TOGGLE" => $notify_toggle,
		"BBCODE_TOGGLE" => $bbcode_toggle,
		"BBCODE_STATUS" => $bbcode_status,

		"L_SUBJECT" => $lang['Subject'],
		"L_MESSAGE_BODY" => $lang['Message_body'],
		"L_OPTIONS" => $lang['Options'],
		"L_PREVIEW" => $lang['Preview'],
		"L_SUBMIT" => $lang['Submit_post'],
		"L_CANCEL" => $lang['Cancel_post'],
		"L_POST_A" => $post_a,
		"L_FIND_USERNAME" => $lang['Find_username'],
		"L_FIND" => $lang['Find'],

		"S_ALPHA_SELECT" => $user_alpha_select,
		"S_NAMES_SELECT" => $user_names_select, 
		"S_POST_ACTION" => append_sid("privmsg.$phpEx"),
		"S_HIDDEN_FORM_FIELDS" => $s_hidden_fields)
	);

	$template->pparse("body");

	include('includes/page_tail.'.$phpEx);

}
else if( ( isset($HTTP_POST_VARS['delete']) && !empty($HTTP_POST_VARS['mark']) ) || !empty($HTTP_POST_VARS['deleteall']) )
{
	if(!$userdata['session_logged_in'])
	{
		header("Location: " . append_sid("login.$phpEx?forward_page=privmsg.$phpEx&folder=inbox"));
	}


	if(isset($HTTP_POST_VARS['delete']))
	{
		$delete_ary = $HTTP_POST_VARS['mark'];
	}
	else if(!empty($HTTP_POST_VARS['deleteall']))
	{
		switch($folder)
		{
			case 'inbox':
				$delete_type = "privmsgs_to_userid = " . $userdata['user_id'] . " AND ( 
				privmsgs_type = " . PRIVMSGS_READ_MAIL . " OR privmsgs_type = " . PRIVMSGS_NEW_MAIL . " )";
				break;
			case 'outbox':
				$delete_type = "privmsgs_from_userid = " . $userdata['user_id'] . " AND privmsgs_type = " . PRIVMSGS_NEW_MAIL;
				break;
			case 'sentbox':
				$delete_type = "privmsgs_from_userid = " . $userdata['user_id'] . " AND privmsgs_type = " . PRIVMSGS_SENT_MAIL;
				break;
			case 'savebox':
				$delete_type = "( privmsgs_from_userid = " . $userdata['user_id'] . " OR privmsgs_to_userid = " . $userdata['user_id'] . " ) 
					AND privmsgs_type = " . PRIVMSGS_SAVED_MAIL;
				break;
		}

		$deleteall_sql = "SELECT privmsgs_id 
			FROM " . PRIVMSGS_TABLE . " 
			WHERE " . $delete_type;

		if(!$del_list_status = $db->sql_query($deleteall_sql))
		{
			error_die(SQL_QUERY, "Could not obtain id list to delete all messages.", __LINE__, __FILE__);
		}

		$delete_list = $db->sql_fetchrowset($del_list_status);
		for($i = 0; $i < count($delete_list); $i++)
		{
			$delete_ary[] = $delete_list[$i]['privmsgs_id'];
		}
		unset($delete_list);
		unset($delete_type);
	}
	

	$delete_sql = "DELETE FROM " . PRIVMSGS_TABLE . " 
		WHERE ";
	$delete_text_sql = "DELETE FROM " . PRIVMSGS_TEXT_TABLE . " 
		WHERE ";

	for($i = 0; $i < count($delete_ary); $i++)
	{
		$delete_sql .= "privmsgs_id = " . $delete_ary[$i] . " ";
		$delete_text_sql .= "privmsgs_text_id = " . $delete_ary[$i] . " ";

		if($i < count($delete_ary) -1)
		{
			$delete_sql .= "OR ";
			$delete_text_sql .= "OR ";
		}
	}

	$delete_sql .= "AND ";

	switch($folder)
	{
		case 'inbox':
			$delete_sql .= "privmsgs_to_userid = " . $userdata['user_id'] . " AND ( 
				privmsgs_type = " . PRIVMSGS_READ_MAIL . " OR privmsgs_type = " . PRIVMSGS_NEW_MAIL . " )";
			break;
		case 'outbox':
			$delete_sql .= "privmsgs_from_userid = " . $userdata['user_id'] . " AND privmsgs_type = " . PRIVMSGS_NEW_MAIL;
			break;
		case 'sentbox':
			$delete_sql .= "privmsgs_from_userid = " . $userdata['user_id'] . " AND privmsgs_type = " . PRIVMSGS_SENT_MAIL;
			break;
		case 'savebox':
			$delete_sql .= "( privmsgs_from_userid = " . $userdata['user_id'] . " OR privmsgs_to_userid = " . $userdata['user_id'] . " ) 
				AND privmsgs_type = " . PRIVMSGS_SAVED_MAIL;
			break;
	}

	if(!$del_status = $db->sql_query($delete_sql))
	{
		error_die(SQL_QUERY, "Could not delete private message info.", __LINE__, __FILE__);
	}
	else
	{
		if(!$del_text_status = $db->sql_query($delete_text_sql))
		{
			error_die(SQL_QUERY, "Could not delete private message text.", __LINE__, __FILE__);
		}
	}

}
else if( ( isset($HTTP_POST_VARS['save'])  && !empty($HTTP_POST_VARS['mark']) ) && $folder != "savebox" && $folder != "outbox")
{
	if(!$userdata['session_logged_in'])
	{
		header("Location: " . append_sid("login.$phpEx?forward_page=privmsg.$phpEx&folder=inbox"));
	}

	$saved_sql = "UPDATE " . PRIVMSGS_TABLE . "  
		SET privmsgs_type = " . PRIVMSGS_SAVED_MAIL . " 
		WHERE ";

	if(isset($HTTP_POST_VARS['save']))
	{
		$saved_ary = $HTTP_POST_VARS['mark'];
		
		for($i = 0; $i < count($saved_ary); $i++)
		{
			$saved_sql .= "privmsgs_id = " . $saved_ary[$i] . " ";
			if($i < count($saved_ary) -1)
			{
				$saved_sql .= "OR ";
			}
		}

		$saved_sql .= "AND ";
		
	}

	switch($folder)
	{
		case 'inbox':
			$saved_sql .= "privmsgs_to_userid = " . $userdata['user_id'] . " AND ( 
				privmsgs_type = " . PRIVMSGS_READ_MAIL . " OR privmsgs_type = " . PRIVMSGS_NEW_MAIL . " )";
			break;
		case 'sentbox':
			$saved_sql .= "privmsgs_from_userid = " . $userdata['user_id'] . " AND privmsgs_type = " . PRIVMSGS_READ_MAIL;
			break;
	}

	if(!$save_status = $db->sql_query($saved_sql))
	{
		error_die(SQL_QUERY, "Could not save private messages.", __LINE__, __FILE__);
	}

	$folder = "savebox";

}
else if($HTTP_POST_VARS['cancel'])
{
	$folder = "inbox";
	$mode = "";

}

//
// Default page
//

if(!$userdata['session_logged_in'])
{
	header("Location: " . append_sid("login.$phpEx?forward_page=privmsg.$phpEx&folder=inbox"));
}

include('includes/page_header.'.$phpEx);

//
// Load templates
//
$template->set_filenames(array(
	"body" => "privmsgs_body.tpl",
	"jumpbox" => "jumpbox.tpl")
);
$jumpbox = make_jumpbox();
$template->assign_vars(array(
	"JUMPBOX_LIST" => $jumpbox,
	"SELECT_NAME" => POST_FORUM_URL)
);
$template->assign_var_from_handle("JUMPBOX", "jumpbox");

//
// These may well be better handled in the
// templates
//
$inbox_url =  "<img src=\"images/msg_inbox.gif\" border=\"0\">&nbsp;";
$inbox_url .= ($folder != "inbox") ? "<a href=\"" . append_sid("privmsg.$phpEx?folder=inbox") . "\"><b>" . $lang['Inbox'] . "</b></a>" : "<b>" . $lang['Inbox'] . "</b>";

$sentbox_url =  "<img src=\"images/msg_sentbox.gif\" border=\"0\">&nbsp;";
$sentbox_url .= ($folder != "sentbox") ? "<a href=\"" . append_sid("privmsg.$phpEx?folder=sentbox") . "\"><b>" . $lang['Sent'] . "</b></a>" : "<b>" . $lang['Sent'] . "</b>";

$outbox_url =  "<img src=\"images/msg_outbox.gif\" border=\"0\">&nbsp;";
$outbox_url .= ($folder != "outbox") ? "<a href=\"" . append_sid("privmsg.$phpEx?folder=outbox") . "\"><b>" . $lang['Outbox'] . "</b></a>" : "<b>" . $lang['Outbox'] . "</b>";

$savebox_url =  "<img src=\"images/msg_savebox.gif\" border=\"0\">&nbsp;";
$savebox_url .= ($folder != "savebox") ? "<a href=\"" . append_sid("privmsg.$phpEx?folder=savebox") . "\"><b>" . $lang['Saved'] . "</b></a>" : "<b>" . $lang['Saved'] . "</b>";

$post_new_mesg_url = "<a href=\"privmsg.$phpEx?mode=post\"><img src=\"templates/PSO/images/post.gif\" border=\"1\"></a>";

$template->assign_vars(array(
	"INBOX" => $inbox_url, 
	"SENTBOX" => $sentbox_url, 
	"OUTBOX" => $outbox_url, 
	"SAVEBOX" => $savebox_url, 

	"L_MARK" => $lang['Mark'],
	"L_FLAG" => $lang['Flag'],
	"L_SUBJECT" => $lang['Subject'],
	"L_DATE" => $lang['Date'], 
	"L_FROM_OR_TO" => (($folder == "inbox" || $folder == "savebox") ? $lang['From'] : $lang['To']),

	"S_HIDDEN_FIELDS" => "", 
	"S_PRIVMSGS_ACTION" => append_sid("privmsg.$phpEx?folder=$folder"), 
	"S_POST_NEW_MSG" => $post_new_mesg_url)
);

$sql_tot = "SELECT COUNT(privmsgs_id) AS total FROM " . PRIVMSGS_TABLE . " ";
$sql = "SELECT pm.privmsgs_type, pm.privmsgs_id, pm.privmsgs_date, pm.privmsgs_subject, u.user_id, u.username FROM " . PRIVMSGS_TABLE . " pm, " . USERS_TABLE . " u ";

switch($folder)
{
	case 'inbox':
		$sql_tot .= "WHERE privmsgs_to_userid = " . $userdata['user_id'] . " 
			AND ( privmsgs_type =  " . PRIVMSGS_NEW_MAIL . " 
				OR privmsgs_type = " . PRIVMSGS_READ_MAIL . " )";

		$sql .= "WHERE pm.privmsgs_to_userid = " . $userdata['user_id'] . " 
			AND u.user_id = pm.privmsgs_from_userid 
			AND ( pm.privmsgs_type =  " . PRIVMSGS_NEW_MAIL . " 
				OR pm.privmsgs_type = " . PRIVMSGS_READ_MAIL . " )";
		break;

	case 'outbox':
		$sql_tot .= "WHERE privmsgs_from_userid = " . $userdata['user_id'] . " 
			AND privmsgs_type =  " . PRIVMSGS_NEW_MAIL;

		$sql .= "WHERE pm.privmsgs_from_userid = " . $userdata['user_id'] . " 
			AND u.user_id = pm.privmsgs_to_userid 
			AND pm.privmsgs_type =  " . PRIVMSGS_NEW_MAIL;
		break;

	case 'sentbox':
		$sql_tot .= "WHERE privmsgs_from_userid = " . $userdata['user_id'] . " 
			AND privmsgs_type =  " . PRIVMSGS_SENT_MAIL;

		$sql .= "WHERE pm.privmsgs_from_userid = " . $userdata['user_id'] . " 
			AND u.user_id = pm.privmsgs_to_userid 
			AND pm.privmsgs_type =  " . PRIVMSGS_SENT_MAIL;
		break;

	case 'savebox':
		$sql_tot .= "WHERE privmsgs_to_userid = " . $userdata['user_id'] . " 
			AND privmsgs_type = " . PRIVMSGS_SAVED_MAIL; 

		$sql .= "WHERE pm.privmsgs_to_userid = " . $userdata['user_id'] . " 
			AND u.user_id = pm.privmsgs_from_userid 
			AND pm.privmsgs_type = " . PRIVMSGS_SAVED_MAIL;
		break;
}

$sql .= " ORDER BY pm.privmsgs_date DESC LIMIT $start, " . $board_config['topics_per_page'];

if(!$pm_tot_status = $db->sql_query($sql_tot))
{
	error_die(SQL_QUERY, "Could not query private message information.", __LINE__, __FILE__);
}
if(!$pm_status = $db->sql_query($sql))
{
	error_die(SQL_QUERY, "Could not query private message information.", __LINE__, __FILE__);
}
$pm_total = $db->sql_numrows($pm_tot_status);
$pm_list = $db->sql_fetchrowset($pm_status);

//
// Okay, let's build the correct folder
//

for($i = 0; $i < count($pm_list); $i++)
{
	$privmsg_id = $pm_list[$i]['privmsgs_id'];

	$flag = $pm_list[$i]['privmsgs_type'];
	$icon_flag = ($flag == PRIVMSGS_READ_MAIL || $flag == PRIVMSGS_SAVED_MAIL || $flag == PRIVMSGS_SENT_MAIL) ? "<img src=\"images/msg_read.gif\">" : "<img src=\"images/msg_unread.gif\">";

	$msg_userid = $pm_list[$i]['user_id'];
	$msg_username = stripslashes($pm_list[$i]['username']);

	$u_from_user_profile = append_sid("profile.$phpEx?mode=viewprofile&" . POST_USERS_URL . "=$msg_userid");

	$msg_subject = stripslashes($pm_list[$i]['privmsgs_subject']);
	$u_subject = append_sid("privmsg.$phpEx?folder=$folder&mode=read&" . POST_POST_URL . "=$privmsg_id");

	$msg_date = create_date($board_config['default_dateformat'], $pm_list[$i]['privmsgs_date'], $board_config['default_timezone']);

	if($flag == PRIVMSGS_NEW_MAIL && $folder == "inbox")
	{
		$msg_subject = "<b>" . $msg_subject . "</b>";
		$msg_date = "<b>" . $msg_date . "</b>";
		$msg_username = "<b>" . $msg_username . "</b>";
	}

	$row_color = (!($i % 2)) ? "#".$theme['td_color1'] : "#".$theme['td_color2'];

	$template->assign_block_vars("listrow", array(
		"ICON_FLAG_IMG" => $icon_flag,
		"FROM" => $msg_username,
		"SUBJECT" => $msg_subject,
		"DATE" => $msg_date,
		"ROW_COLOR" => $row_color,

		"S_DEL_CHECKBOX" => "<input type=\"checkbox\" name=\"mark[]\" value=\"$privmsg_id\">",

		"U_READ" => $u_subject,
		"U_FROM_USER_PROFILE" => $u_from_user_profile)
	);
} // for ... 

$template->assign_vars(array(
	"PAGINATION" => generate_pagination("privmsg.$phpEx?folder=$folder", $pm_total, $board_config['topics_per_page'], $start),
	"ON_PAGE" => (floor($start/$board_config['topics_per_page'])+1),
	"TOTAL_PAGES" => ceil(($pm_total)/$board_config['topics_per_page']),

	"L_OF" => $lang['of'],
	"L_PAGE" => $lang['Page'],
	"L_GOTO_PAGE" => $lang['Goto_page'])
);

$template->pparse("body");

include('includes/page_tail.'.$phpEx);

?>