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
 ***************************************************************************/

$phpbb_root_path = "./";
include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'common.'.$phpEx);
include($phpbb_root_path . 'includes/post.'.$phpEx);
include($phpbb_root_path . 'includes/bbcode.'.$phpEx);

//
// Is PM disabled?
//
if( !empty($board_config['privmsg_disable']) )
{
	message_die(GENERAL_MESSAGE, 'PM_disabled');
}

//
// Var definitions
//
$html_entities_match = array("#<#", "#>#", "#& #", "#\"#");
$html_entities_replace = array("&lt;", "&gt;", "&amp; ", "&quot;");

//
// Parameters
//
$submit = ( isset($HTTP_POST_VARS['submit']) ) ? TRUE : 0;
$submit_search = ( isset($HTTP_POST_VARS['usersubmit']) ) ? TRUE : 0; 
$submit_msgdays = ( isset($HTTP_POST_VARS['submit_msgdays']) ) ? TRUE : 0;
$cancel = ( isset($HTTP_POST_VARS['cancel']) ) ? TRUE : 0;
$preview = ( isset($HTTP_POST_VARS['preview']) ) ? TRUE : 0;
$confirm = ( isset($HTTP_POST_VARS['confirm']) ) ? TRUE : 0;
$delete = ( isset($HTTP_POST_VARS['delete']) ) ? TRUE : 0;
$delete_all = ( isset($HTTP_POST_VARS['deleteall']) ) ? TRUE : 0;

$refresh = $preview || $submit_search;

$mark_list = ( !empty($HTTP_POST_VARS['mark']) ) ? $HTTP_POST_VARS['mark'] : 0;

$folder = ( !empty($HTTP_POST_VARS['folder']) ) ? $HTTP_POST_VARS['folder'] : ( (!empty($HTTP_GET_VARS['folder'])) ? $HTTP_GET_VARS['folder'] : "inbox" );

//
// Cancel 
//
if( $cancel )
{
	header("Location: " . append_sid("privmsg.$phpEx?folder=$folder", true));
}

//
// Start session management
//
$userdata = session_pagestart($user_ip, PAGE_PRIVMSGS, $session_length);
init_userprefs($userdata);
//
// End session management
//

//
// Var definitions
//
if( !empty($HTTP_POST_VARS['mode']) || !empty($HTTP_GET_VARS['mode']) )
{
	$mode = ( !empty($HTTP_POST_VARS['mode']) ) ? $HTTP_POST_VARS['mode'] : $HTTP_GET_VARS['mode'];
}
else
{
	$mode = "";
}

$start = ( !empty($HTTP_GET_VARS['start']) ) ? $HTTP_GET_VARS['start'] : 0;

if( isset($HTTP_POST_VARS[POST_POST_URL]) || isset($HTTP_GET_VARS[POST_POST_URL]) )
{
	$privmsg_id = ( isset($HTTP_POST_VARS[POST_POST_URL]) ) ? $HTTP_POST_VARS[POST_POST_URL] : $HTTP_GET_VARS[POST_POST_URL];
}
else
{
	$privmsg_id = "";
}

$error = FALSE;

//
// Define the box image links
//
$inbox_img = ($folder != "inbox" || $mode != "") ? '<a href="' . append_sid("privmsg.$phpEx?folder=inbox") . '"><img src="' . $images['pm_inbox'] . '" border="0" alt="' . $lang['Inbox'] . '" /></a>' : '<img src="' . $images['pm_inbox'] . '" border="0" alt="' . $lang['Inbox'] . '" />';
$inbox_url = ($folder != "inbox" || $mode != "") ? '<a href="' . append_sid("privmsg.$phpEx?folder=inbox") . '">' . $lang['Inbox'] . '</a>' : $lang['Inbox'];

$outbox_img = ($folder != "outbox" || $mode != "") ? '<a href="' . append_sid("privmsg.$phpEx?folder=outbox") . '"><img src="' . $images['pm_outbox'] . '" border="0" alt="' . $lang['Outbox'] . '" /></a>' : '<img src="' . $images['pm_outbox'] . '" border="0" alt="' . $lang['Outbox'] . '" />';
$outbox_url = ($folder != "outbox" || $mode != "") ? '<a href="' . append_sid("privmsg.$phpEx?folder=outbox") . '">' . $lang['Outbox'] . '</a>' : $lang['Outbox'];

$sentbox_img = ($folder != "sentbox" || $mode != "") ? '<a href="' . append_sid("privmsg.$phpEx?folder=sentbox") . '"><img src="' . $images['pm_sentbox'] . '" border="0" alt="' . $lang['Sentbox'] . '" /></a>' : '<img src="' . $images['pm_sentbox'] . '" border="0" alt="' . $lang['Sentbox'] . '" />';
$sentbox_url = ($folder != "sentbox" || $mode != "") ? '<a href="' . append_sid("privmsg.$phpEx?folder=sentbox") . '">' . $lang['Sentbox'] . '</a>' : $lang['Sentbox'];

$savebox_img = ($folder != "savebox" || $mode != "") ? '<a href="' . append_sid("privmsg.$phpEx?folder=savebox") . '"><img src="' . $images['pm_savebox'] . '" border="0" alt="' . $lang['Savebox'] . '" /></a>' : '<img src="' . $images['pm_savebox'] . '" border="0" alt="' . $lang['Savebox'] . '" />';
$savebox_url = ($folder != "savebox" || $mode != "") ? '<a href="' . append_sid("privmsg.$phpEx?folder=savebox") . '">' . $lang['Savebox'] . '</a>' : $lang['Savebox'];

// ----------
// Start main
//
if( $mode == "newpm" )
{
	$gen_simple_header = TRUE;

	$page_title = $lang['Private_Messaging'];
	include($phpbb_root_path . 'includes/page_header.'.$phpEx);

	$template->set_filenames(array(
		"body" => "privmsgs_popup.tpl")
	);

	if( $userdata['session_logged_in'] )
	{
		if( $userdata['user_new_privmsg'] )
		{
			$l_new_message = ( $userdata['user_new_privmsg'] == 1 ) ? $lang['You_new_pm'] : $lang['You_new_pms'];
		}
		else
		{
			$l_new_message = $lang['You_no_new_pm'];
		}

		$l_new_message .= "<br /><br />" . sprintf($lang['Click_view_privmsg'], "<a href=\"" . append_sid("privmsg.".$phpEx."?folder=inbox") . "\" onClick=\"jump_to_inbox();return false;\" target=\"_new\">", "</a>");
	}
	else
	{
		$l_new_message = $lang['Login_check_pm'];
	}

	$template->assign_vars(array(
		"L_CLOSE_WINDOW" => $lang['Close_window'], 
		"L_MESSAGE" => $l_new_message)
	);

	$template->pparse("body");

	include($phpbb_root_path . 'includes/page_tail.'.$phpEx);
	
}
else if( $mode == "read" )
{
	if( !empty($HTTP_GET_VARS[POST_POST_URL]) )
	{
		$privmsgs_id = $HTTP_GET_VARS[POST_POST_URL];
	}
	else
	{
		message_die(GENERAL_ERROR, $lang['No_post_id']);
	}

	if( !$userdata['session_logged_in'] )
	{
		header("Location: " . append_sid("login.$phpEx?redirect=privmsg.$phpEx&folder=$folder&mode=$mode&" . POST_POST_URL . "=$privmsgs_id", true));
	}

	if( $folder )
	{
		//
		// SQL to pull appropriate message, prevents nosey people
		// reading other peoples messages ... hopefully!
		//
		if($folder == "inbox")
		{
			$l_box_name = $lang['Inbox'];

			$pm_sql_user = "AND pm.privmsgs_to_userid = " . $userdata['user_id'] . " 
				AND ( pm.privmsgs_type = " . PRIVMSGS_READ_MAIL . " 
					OR pm.privmsgs_type = " . PRIVMSGS_NEW_MAIL . " )";
		}
		else if($folder == "outbox")
		{
			$l_box_name = $lang['Outbox'];

			$pm_sql_user = "AND pm.privmsgs_from_userid =  " . $userdata['user_id'] . " 
				AND pm.privmsgs_type = " . PRIVMSGS_NEW_MAIL;
		}
		else if($folder == "sentbox")
		{
			$l_box_name = $lang['Sentbox'];

			$pm_sql_user = "AND pm.privmsgs_from_userid =  " . $userdata['user_id'] . " 
				AND pm.privmsgs_type = " . PRIVMSGS_SENT_MAIL;
		}
		else if($folder == "savebox")
		{
			$l_box_name = $lang['Savebox'];

			$pm_sql_user .= "AND ( ( pm.privmsgs_to_userid = " . $userdata['user_id'] . "
					AND pm.privmsgs_type = " . PRIVMSGS_SAVED_IN_MAIL . " ) 
				OR ( pm.privmsgs_from_userid = " . $userdata['user_id'] . "
					AND pm.privmsgs_type = " . PRIVMSGS_SAVED_OUT_MAIL . " ) 
				)";
		}
		else
		{
			message_die(GENERAL_ERROR, $lang['No_such_folder']);
		}
	}
	else
	{
		message_die(GENERAL_ERROR, $lang['No_folder']);
	}

	//
	// Major query obtains the message ...
	//
	$sql = "SELECT u.username AS username_1, u.user_id AS user_id_1, u2.username AS username_2, u2.user_id AS user_id_2, u.user_sig_bbcode_uid, u.user_posts, u.user_from, u.user_website, u.user_email, u.user_icq, u.user_aim, u.user_yim, u.user_regdate, u.user_msnm, u.user_viewemail, u.user_rank, u.user_sig, u.user_avatar, pm.*, pmt.privmsgs_bbcode_uid, pmt.privmsgs_text
		FROM " . PRIVMSGS_TABLE . " pm, " . PRIVMSGS_TEXT_TABLE . " pmt, " . USERS_TABLE . " u, " . USERS_TABLE . " u2 
		WHERE pm.privmsgs_id = $privmsgs_id
			AND pmt.privmsgs_text_id = pm.privmsgs_id 
			$pm_sql_user 
			AND u.user_id = pm.privmsgs_from_userid 
			AND u2.user_id = pm.privmsgs_to_userid";
	if( !$pm_status = $db->sql_query($sql) )
	{
		message_die(GENERAL_ERROR, "Could not query private message post information.", "", __LINE__, __FILE__, $sql);
	}

	//
	// Did the query return any data?
	//
	if( !( $privmsg = $db->sql_fetchrow($pm_status) ) )
	{
		header("Location: " . append_sid("privmsg.$phpEx?folder=$folder", true));
	}

	$privmsg_id = $privmsg['privmsgs_id'];

	//
	// Is this a new message in the inbox? If it is then save
	// a copy in the posters sent box
	//
	if( $privmsg['privmsgs_type'] == PRIVMSGS_NEW_MAIL && $folder == "inbox" )
	{
		$sql = "UPDATE " . PRIVMSGS_TABLE . "
			SET privmsgs_type = " . PRIVMSGS_READ_MAIL . "
			WHERE privmsgs_id = " . $privmsg['privmsgs_id'];
		if( !$pm_upd_status = $db->sql_query($sql) )
		{
			message_die(GENERAL_ERROR, "Could not update private message read status.", "", __LINE__, __FILE__, $sql);
		}

		$sql = "UPDATE " . USERS_TABLE . " 
			SET user_unread_privmsg = user_unread_privmsg - 1 
			WHERE user_id = " . $userdata['user_id'];
		if( !$user_upd_status = $db->sql_query($sql) )
		{
			message_die(GENERAL_ERROR, "Could not update private message read status for user.", "", __LINE__, __FILE__, $sql);
		}

		//
		// Check to see if the poster has a 'full' sent box
		//
		$sql = "SELECT COUNT(privmsgs_id) AS sent_items, MIN(privmsgs_date) AS oldest_post_time 
			FROM " . PRIVMSGS_TABLE . " 
			WHERE privmsgs_type = " . PRIVMSGS_SENT_MAIL . " 
				AND privmsgs_from_userid = " . $privmsg['privmsgs_from_userid'];
		if( !$result = $db->sql_query($sql) )
		{
			message_die(GENERAL_ERROR, "Could not obtain sent message info for sendee.", "", __LINE__, __FILE__, $sql);
		}

		$sql_priority = (SQL_LAYER == "mysql") ? "LOW_PRIORITY" : "";

		if( $db->sql_numrows($result) )
		{
			$sent_info = $db->sql_fetchrow($result);

			if( $sent_info['sent_items'] > $board_config['max_sentbox_privmsgs'] )
			{
				$sql = "DELETE $sql_priority FROM " . PRIVMSGS_TABLE . " 
					WHERE privmsgs_type = " . PRIVMSGS_SENT_MAIL . " 
						AND privmsgs_date <= " . $sent_info['oldest_post_time'] . " 
						AND privmsgs_from_userid = " . $privmsg['privmsgs_from_userid'];
				if( !$result = $db->sql_query($sql) )
				{
					message_die(GENERAL_ERROR, "Could not delete oldest privmsgs.", "", __LINE__, __FILE__, $sql);
				}
			}
		}

		//
		// This makes a copy of the post and stores
		// it as a SENT message from the sendee. Perhaps
		// not the most DB friendly way but a lot easier
		// to manage, besides the admin will be able to
		// set limits on numbers of storable posts for
		// users ... hopefully!
		//
		$sql = "INSERT $sql_priority INTO " . PRIVMSGS_TABLE . " (privmsgs_type, privmsgs_subject, privmsgs_from_userid, privmsgs_to_userid, privmsgs_date, privmsgs_ip, privmsgs_enable_html, privmsgs_enable_bbcode, privmsgs_enable_smilies, privmsgs_attach_sig)
			VALUES (" . PRIVMSGS_SENT_MAIL . ", '" . addslashes($privmsg['privmsgs_subject']) . "', " . $privmsg['privmsgs_from_userid'] . ", " . $privmsg['privmsgs_to_userid'] . ", " . $privmsg['privmsgs_date'] . ", '" . $privmsg['privmsgs_ip'] . "', " . $privmsg['privmsgs_enable_html'] . ", " . $privmsg['privmsgs_enable_bbcode'] . ", " . $privmsg['privmsgs_enable_smilies'] . ", " .  $privmsg['privmsgs_attach_sig'] . ")";
		if( !$pm_sent_status = $db->sql_query($sql) )
		{
			message_die(GENERAL_ERROR, "Could not insert private message sent info.", "", __LINE__, __FILE__, $sql);
		}
		else
		{
			$privmsg_sent_id = $db->sql_nextid($pm_sent_status);

			$sql = "INSERT $sql_priority INTO " . PRIVMSGS_TEXT_TABLE . " (privmsgs_text_id, privmsgs_bbcode_uid, privmsgs_text)
				VALUES ($privmsg_sent_id, '" . $privmsg['privmsgs_bbcode_uid'] . "', '" . addslashes($privmsg['privmsgs_text']) . "')";
			if(!$pm_sent_text_status = $db->sql_query($sql))
			{
				message_die(GENERAL_ERROR, "Could not insert private message sent text.<BR>$sql", "", __LINE__, __FILE__, $sql);
			}
		}
	}

	//
	// Pick a folder, any folder, so long as it's one
	// below ...
	//
	if( $folder == "inbox" )
	{
		$post_pm_img = "<a href=\"" . append_sid("privmsg.$phpEx?mode=post") . "\"><img src=\"" . $images['pm_postmsg'] . "\" alt=\"" . $lang['Post_new_pm'] . "\" border=\"0\"></a>";
		$reply_pm_img = "<a href=\"" . append_sid("privmsg.$phpEx?mode=reply&amp;" . POST_POST_URL . "=$privmsg_id") . "\"><img src=\"" . $images['pm_replymsg'] . "\" alt=\"" . $lang['Post_reply_pm'] . "\" border=\"0\"></a>";
		$quote_pm_img = "<a href=\"" . append_sid("privmsg.$phpEx?mode=quote&amp;" . POST_POST_URL . "=$privmsg_id") . "\"><img src=\"" . $images['pm_quotemsg'] . "\" alt=\"" . $lang['Post_quote_pm'] . "\" border=\"0\"></a>";
		$edit_pm_img = "";
		$l_box_name = $lang['Inbox'];
	}
	else if( $folder == "outbox" )
	{
		$post_pm_img = "<a href=\"" . append_sid("privmsg.$phpEx?mode=post") . "\"><img src=\"" . $images['pm_postmsg'] . "\" alt=\"" . $lang['Post_new_pm'] . "\" border=\"0\"></a>";
		$reply_pm_img = "";
		$quote_pm_img = "";
		$edit_pm_img = "<a href=\"" . append_sid("privmsg.$phpEx?mode=edit&amp;" . POST_POST_URL . "=$privmsg_id") . "\"><img src=\"" . $images['pm_editmsg'] . "\" alt=\"" . $lang['Edit_pm'] . "\" border=\"0\"></a>";
		$l_box_name = $lang['Outbox'];
	}
	else if( $folder == "savebox" )
	{
		if( $privmsg['privmsgs_type'] == PRIVMSGS_SAVED_IN_MAIL )
		{
			$post_pm_img = "<a href=\"" . append_sid("privmsg.$phpEx?mode=post") . "\"><img src=\"" . $images['pm_postmsg'] . "\" alt=\"" . $lang['Post_new_pm'] . "\" border=\"0\"></a>";
			$reply_pm_img = "<a href=\"" . append_sid("privmsg.$phpEx?mode=reply&amp;" . POST_POST_URL . "=$privmsg_id") . "\"><img src=\"" . $images['pm_replymsg'] . "\" alt=\"" . $lang['Post_reply_pm'] . "\" border=\"0\"></a>";
			$quote_pm_img = "<a href=\"" . append_sid("privmsg.$phpEx?mode=quote&amp;" . POST_POST_URL . "=$privmsg_id") . "\"><img src=\"" . $images['pm_quotemsg'] . "\" alt=\"" . $lang['Post_quote_pm'] . "\" border=\"0\"></a>";
			$edit_pm_img = "";
		}
		else
		{
			$post_pm_img = "<a href=\"" . append_sid("privmsg.$phpEx?mode=post") . "\"><img src=\"" . $images['pm_postmsg'] . "\" alt=\"" . $lang['Post_new_pm'] . "\" border=\"0\"></a>";
			$reply_pm_img = "";
			$quote_pm_img = "";
			$edit_pm_img = "";
		}
		$l_box_name = $lang['Saved'];
	}
	else if( $folder == "sentbox" )
	{
		$post_pm_img = "<a href=\"" . append_sid("privmsg.$phpEx?mode=post") . "\"><img src=\"" . $images['pm_postmsg'] . "\" alt=\"" . $lang['Post_new_pm'] . "\" border=\"0\"></a>";
		$reply_pm_img = "";
		$quote_pm_img = "";
		$edit_pm_img = "";
		$l_box_name = $lang['Sent'];
	}

	$s_hidden_fields = "<input type=\"hidden\" name=\"mark[]\" value=\"$privmsgs_id\" />";

	$page_title = $lang['Read_private_message'];
	include($phpbb_root_path . 'includes/page_header.'.$phpEx);

	//
	// Load templates
	//
	$template->set_filenames(array(
		"body" => "privmsgs_read_body.tpl",
		"jumpbox" => "jumpbox.tpl")
	);

	$jumpbox = make_jumpbox();
	$template->assign_vars(array(
		"L_GO" => $lang['Go'],
		"L_JUMP_TO" => $lang['Jump_to'],
		"L_SELECT_FORUM" => $lang['Select_forum'],

		"S_JUMPBOX_LIST" => $jumpbox,
		"S_JUMPBOX_ACTION" => append_sid("viewforum.$phpEx"))
	);
	$template->assign_var_from_handle("JUMPBOX", "jumpbox");

	$template->assign_vars(array(
		"INBOX_IMG" => $inbox_img, 
		"SENTBOX_IMG" => $sentbox_img, 
		"OUTBOX_IMG" => $outbox_img, 
		"SAVEBOX_IMG" => $savebox_img, 
		"INBOX_LINK" => $inbox_url, 

		"POST_PM_IMG" => $post_pm_img,
		"REPLY_PM_IMG" => $reply_pm_img, 
		"EDIT_PM_IMG" => $edit_pm_img, 
		"QUOTE_PM_IMG" => $quote_pm_img, 

		"SENTBOX_LINK" => $sentbox_url, 
		"OUTBOX_LINK" => $outbox_url, 
		"SAVEBOX_LINK" => $savebox_url, 

		"BOX_NAME" => $l_box_name, 

		"L_INBOX" => $lang['Inbox'],
		"L_OUTBOX" => $lang['Outbox'],
		"L_SENTBOX" => $lang['Sent'],
		"L_SAVEBOX" => $lang['Saved'],
		"L_FLAG" => $lang['Flag'],
		"L_SUBJECT" => $lang['Subject'],
		"L_DATE" => $lang['Date'],
		"L_FROM" => $lang['From'],
		"L_TO" => $lang['To'], 
		"L_SAVE_MSG" => $lang['Save_message'], 
		"L_DELETE_MSG" => $lang['Delete_message'], 

		"S_PRIVMSGS_ACTION" => append_sid("privmsg.$phpEx?folder=$folder"),
		"S_HIDDEN_FIELDS" => $s_hidden_fields)
	);
	
	$username_from = $privmsg['username_1'];
	$user_id_from = $privmsg['user_id_1'];
	$username_to = $privmsg['username_2'];
	$user_id_to = $privmsg['user_id_2'];

	$post_date = create_date($board_config['default_dateformat'], $privmsg['privmsgs_date'], $board_config['board_timezone']);

	$profile_img = "<a href=\"" . append_sid("profile.$phpEx?mode=viewprofile&amp;" . POST_USERS_URL . "=$user_id_from") . "\"><img src=\"" . $images['icon_profile'] . "\" alt=\"" . $lang['Read_profile'] . "\" border=\"0\" /></a>";

	if( !empty($privmsg['user_viewemail']) )
	{
		$email_uri = ( $board_config['board_email_form'] ) ? append_sid("profile.$phpEx?mode=email&amp;" . POST_USERS_URL ."=" . $user_id_from) : "mailto:" . $privmsg['user_email'];

		$email_img = "<a href=\"$email_uri\"><img src=\"" . $images['icon_email'] . "\" alt=\"" . $lang['Send_email'] . "\" border=\"0\" /></a>";
	}
	else
	{
		$email_img = "";
	}

	$www_img = ( $privmsg['user_website'])  ? "<a href=\"" . $privmsg['user_website'] . "\" target=\"_userwww\"><img src=\"" . $images['icon_www'] . "\" alt=\"" . $lang['Visit_website'] . "\" border=\"0\" /></a>" : "";

	if( $privmsg['user_icq'] )
	{
		$icq_status_img = "<a href=\"http://wwp.icq.com/" . $privmsg['user_icq'] . "#pager\"><img src=\"http://web.icq.com/whitepages/online?icq=" . $privmsg['user_icq'] . "&amp;img=5\" width=\"18\" height=\"18\" border=\"0\" /></a>";

		//
		// This cannot stay like this, it needs a 'proper' solution, eg a separate
		// template for overlaying the ICQ icon, or we just do away with the icq status 
		// display (which is after all somewhat a pain in the rear :D 
		//
		if( $theme['template_name'] == "subSilver" )
		{
			$icq_add_img = '<table width="59" border="0" cellspacing="0" cellpadding="0"><tr><td nowrap="nowrap" class="icqback"><img src="images/spacer.gif" width="3" height="18" alt = "">' . $icq_status_img . '<a href="http://wwp.icq.com/scripts/search.dll?to=' . $privmsg['user_icq'] . '"><img src="images/spacer.gif" width="35" height="18" border="0" alt="' . $lang['ICQ'] . '" /></a></td></tr></table>'; 
			$icq_status_img = "";
		}
		else
		{
			$icq_add_img = "<a href=\"http://wwp.icq.com/scripts/search.dll?to=" . $privmsg['user_icq'] . "\"><img src=\"" . $images['icon_icq'] . "\" alt=\"" . $lang['ICQ'] . "\" border=\"0\" /></a>";
		}
	}
	else
	{
		$icq_status_img = "";
		$icq_add_img = "";
	}

	$aim_img = ($privmsg['user_aim']) ? "<a href=\"aim:goim?screenname=" . $privmsg['user_aim'] . "&amp;message=Hello+Are+you+there?\"><img src=\"" . $images['icon_aim'] . "\" border=\"0\" alt=\"" . $lang['AIM'] . "\" /></a>" : "";

	$msn_img = ($privmsg['user_msnm']) ? "<a href=\"profile.$phpEx?mode=viewprofile&amp;" . POST_USERS_URL . "=$user_id_from\"><img src=\"" . $images['icon_msnm'] . "\" border=\"0\" alt=\"" . $lang['MSNM'] . "\" /></a>" : "";

	$yim_img = ($privmsg['user_yim']) ? "<a href=\"http://edit.yahoo.com/config/send_webmesg?.target=" . $privmsg['user_yim'] . "&amp;.src=pg\"><img src=\"" . $images['icon_yim'] . "\" border=\"0\" alt=\"" . $lang['YIM'] . "\" /></a>" : "";

	$search_img = "<a href=\"" . append_sid("search.$phpEx?search_author=" . urlencode($username_from) . "&amp;showresults=topics") . "\"><img src=\"" . $images['icon_search'] . "\" border=\"0\" /></a>";

	//
	// Processing of post
	//
	$post_subject = $privmsg['privmsgs_subject'];

	$private_message = $privmsg['privmsgs_text'];
	$bbcode_uid = $privmsg['privmsgs_bbcode_uid'];

	$user_sig = ( $privmsg['privmsgs_from_userid'] == $userdata['user_id'] ) ? $userdata['user_sig'] : $privmsg['user_sig'];
	$user_sig_bbcode_uid = ( $privmsg['privmsgs_from_userid'] == $userdata['user_id'] ) ? $userdata['user_sig_bbcode_uid'] : $privmsg['user_sig_bbcode_uid'];

	//
	// If the board has HTML off but the post has HTML
	// on then we process it, else leave it alone
	//
	if( !$board_config['allow_html'] )
	{
		if( $user_sig != "" && $privmsg['privmsgs_enable_sig'] && $userdata['user_allowhtml'] )
		{
			$user_sig = preg_replace("#(<)([\/]?.*?)(>)#is", "&lt;\\2&gt;", $user_sig);
		}

		if( $privmsg['privmsgs_enable_html'] )
		{
			$private_message = preg_replace("#(<)([\/]?.*?)(>)#is", "&lt;\\2&gt;", $private_message);
		}
	}

	if( $user_sig != "" && $privmsg['privmsgs_attach_sig'] && $user_sig_bbcode_uid != "" )
	{
		$user_sig = ( $board_config['allow_bbcode'] ) ? bbencode_second_pass($user_sig, $user_sig_bbcode_uid) : preg_replace("/\:[0-9a-z\:]+\]/si", "]", $user_sig);
	}

	if( $bbcode_uid != "" )
	{
		$private_message = ( $board_config['allow_bbcode'] ) ? bbencode_second_pass($private_message, $bbcode_uid) : preg_replace("/\:[0-9a-z\:]+\]/si", "]", $private_message);
	}

	$private_message = make_clickable($private_message);

	if( $privmsg['privmsgs_attach_sig'] && $user_sig != "" )
	{
		$private_message .= "<br /><br />_________________<br />" . make_clickable($user_sig);
	}

	if( count($orig_word) )
	{
		$post_subject = preg_replace($orig_word, $replacement_word, $post_subject);
		$private_message = preg_replace($orig_word, $replacement_word, $private_message);
	}

	if( $board_config['allow_smilies'] && $privmsg['privmsgs_enable_smilies'] )
	{
		$private_message = smilies_pass($private_message);
	}

	$private_message = str_replace("\n", "<br />", $private_message);

	//
	// Dump it to the templating engine
	//
	$template->assign_vars(array(
		"MESSAGE_TO" => $username_to,
		"MESSAGE_FROM" => $username_from,
		"RANK_IMAGE" => $rank_image,
		"POSTER_JOINED" => $poster_joined,
		"POSTER_POSTS" => $poster_posts,
		"POSTER_FROM" => $poster_from,
		"POSTER_AVATAR" => $poster_avatar,
		"PROFILE_IMG" => $profile_img,
		"SEARCH_IMG" => $search_img,
		"EMAIL_IMG" => $email_img,
		"WWW_IMG" => $www_img,
		"ICQ_STATUS_IMG" => $icq_status_img,
		"ICQ_ADD_IMG" => $icq_add_img,
		"AIM_IMG" => $aim_img,
		"MSN_IMG" => $msn_img,
		"YIM_IMG" => $yim_img, 
		"POST_SUBJECT" => $post_subject,
		"MESSAGE" => $private_message,
		"POST_DATE" => $post_date)
	);

	$template->pparse("body");

	include($phpbb_root_path . 'includes/page_tail.'.$phpEx);

}
else if( ( $delete && $mark_list ) || $delete_all )
{
	if(!$userdata['session_logged_in'])
	{
		header("Location: " . append_sid("login.$phpEx?redirect=privmsg.$phpEx&folder=inbox", true));
	}

	if( !$confirm )
	{
		$s_hidden_fields = '<input type="hidden" name="mode" value="' . $mode . '" />';
		$s_hidden_fields .= (isset($HTTP_POST_VARS['delete'])) ? '<input type="hidden" name="delete" value="true" />' : '<input type="hidden" name="deleteall" value="true" />';

		for($i = 0; $i < count($mark_list); $i++)
		{
			$s_hidden_fields .= '<input type="hidden" name="mark[]" value="' . $mark_list[$i] . '" />';
		}

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

			"S_CONFIRM_ACTION" => append_sid("privmsg.$phpEx?folder=$folder"),
			"S_HIDDEN_FIELDS" => $s_hidden_fields)
		);
		$template->pparse("confirm_body");

		include($phpbb_root_path . 'includes/page_tail.'.$phpEx);

	}
	else if( $confirm )
	{
		if( $delete_all )
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
					$delete_type = "( ( privmsgs_from_userid = " . $userdata['user_id'] . " 
						AND privmsgs_type = " . PRIVMSGS_SAVED_OUT_MAIL . " ) 
					OR ( privmsgs_to_userid = " . $userdata['user_id'] . " 
						AND privmsgs_type = " . PRIVMSGS_SAVED_IN_MAIL . " ) )";
					break;
			}

			$deleteall_sql = "SELECT privmsgs_id
				FROM " . PRIVMSGS_TABLE . "
				WHERE " . $delete_type;

			if(!$del_list_status = $db->sql_query($deleteall_sql))
			{
				message_die(GENERAL_ERROR, "Could not obtain id list to delete all messages.", "", __LINE__, __FILE__, $deleteall_sql);
			}

			$delete_list = $db->sql_fetchrowset($del_list_status);
			for($i = 0; $i < count($delete_list); $i++)
			{
				$mark_list[] = $delete_list[$i]['privmsgs_id'];
			}
			unset($delete_list);
			unset($delete_type);
		}

		$delete_sql = "DELETE FROM " . PRIVMSGS_TABLE . "
			WHERE ";
		$delete_text_sql = "DELETE FROM " . PRIVMSGS_TEXT_TABLE . "
			WHERE ";

		$delete_sql_id = "";
		for($i = 0; $i < count($mark_list); $i++)
		{
			if( $delete_sql_id != "" )
			{
				$delete_sql_id .= ", ";
			}
			$delete_sql_id .= $mark_list[$i];
		}

		$delete_sql .= "privmsgs_id IN ($delete_sql_id)";
		$delete_text_sql .= "privmsgs_text_id IN ($delete_sql_id)";

		$delete_sql .= " AND ";

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
				$delete_sql .= "( ( privmsgs_from_userid = " . $userdata['user_id'] . " 
					AND privmsgs_type = " . PRIVMSGS_SAVED_OUT_MAIL . " ) 
				OR ( privmsgs_to_userid = " . $userdata['user_id'] . " 
					AND privmsgs_type = " . PRIVMSGS_SAVED_IN_MAIL . " ) )";
				break;
		}

		if(!$del_status = $db->sql_query($delete_sql, BEGIN_TRANSACTION))
		{
			message_die(GENERAL_ERROR, "Could not delete private message info.", "", __LINE__, __FILE__, $delete_sql);
		}
		else
		{
			if(!$del_text_status = $db->sql_query($delete_text_sql, END_TRANSACTION))
			{
				message_die(GENERAL_ERROR, "Could not delete private message text.", "", __LINE__, __FILE__, $delete_text_sql);
			}
		}
	}

}
else if( $save && $mark_list && $folder != "savebox" && $folder != "outbox")
{
	if( !$userdata['session_logged_in'] )
	{
		header("Location: " . append_sid("login.$phpEx?redirect=privmsg.$phpEx&folder=inbox", true));
	}

	//
	// See if recipient is at their savebox limit
	//
	$sql = "SELECT COUNT(privmsgs_id) AS savebox_items, MIN(privmsgs_date) AS oldest_post_time 
		FROM " . PRIVMSGS_TABLE . " 
		WHERE ( ( privmsgs_to_userid = " . $userdata['user_id'] . " 
				AND privmsgs_type = " . PRIVMSGS_SAVED_IN_MAIL . " )
			OR ( privmsgs_from_userid = " . $userdata['user_id'] . " 
				AND privmsgs_type = " . PRIVMSGS_SAVED_OUT_MAIL . ") )";
	if( !$result = $db->sql_query($sql) )
	{
		message_die(GENERAL_ERROR, "Could not obtain sent message info for sendee.", "", __LINE__, __FILE__, $sql);
	}

	$sql_priority = (SQL_LAYER == "mysql") ? "LOW_PRIORITY" : "";

	if( $db->sql_numrows($result) )
	{
		$saved_info = $db->sql_fetchrow($result);

		if( $saved_info['savebox_items'] > $board_config['max_savebox_privmsgs'] )
		{
			$sql = "DELETE $sql_priority FROM " . PRIVMSGS_TABLE . " 
				WHERE ( ( privmsgs_to_userid = " . $userdata['user_id'] . " 
						AND privmsgs_type = " . PRIVMSGS_SAVED_IN_MAIL . " )
					OR ( privmsgs_from_userid = " . $userdata['user_id'] . " 
						AND privmsgs_type = " . PRIVMSGS_SAVED_OUT_MAIL . ") ) 
					AND privmsgs_date = " . $saved_info['oldest_post_time'];
			if( !$result = $db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, "Could not delete oldest privmsgs.", "", __LINE__, __FILE__, $sql);
			}
		}
	}

	//
	// Process request
	//
	$saved_sql = "UPDATE " . PRIVMSGS_TABLE;

	switch($folder)
	{
		case 'inbox':
			$saved_sql .= " SET privmsgs_type = " . PRIVMSGS_SAVED_IN_MAIL . " 
				WHERE privmsgs_to_userid = " . $userdata['user_id'] . " 
					AND ( privmsgs_type = " . PRIVMSGS_READ_MAIL . " 
						OR privmsgs_type = " . PRIVMSGS_NEW_MAIL . " )";
			break;

		case 'outbox':
			$saved_sql .= " SET privmsgs_type = " . PRIVMSGS_SAVED_OUT_MAIL . " 
				WHERE privmsgs_from_userid = " . $userdata['user_id'] . " 
					AND privmsgs_type = " . PRIVMSGS_NEW_MAIL;
			break;

		case 'sentbox':
			$saved_sql .= " SET privmsgs_type = " . PRIVMSGS_SAVED_OUT_MAIL . " 
				WHERE privmsgs_from_userid = " . $userdata['user_id'] . " 
					AND privmsgs_type = " . PRIVMSGS_SENT_MAIL;
			break;
	}

	$saved_sql_id = "";
	for($i = 0; $i < count($mark_list); $i++)
	{
		if( $saved_sql_id != "" )
		{
			$saved_sql_id .= ", ";
		}
		$saved_sql_id .= $mark_list[$i];
	}

	$saved_sql .= " AND privmsgs_id IN ($saved_sql_id)";

	if( !$save_status = $db->sql_query($saved_sql) )
	{
		message_die(GENERAL_ERROR, "Could not save private messages.", "", __LINE__, __FILE__, $saved_sql);
	}

}
else if( $submit || $refresh || $mode != "" )
{

	if(!$userdata['session_logged_in'])
	{
		header("Location: " . append_sid("login.$phpEx?redirect=privmsg.$phpEx&folder=$folder&mode=$mode", true));
	}

	if( $mode == "searchuser" )
	{
		//
		// This 'will' handle a simple user search 
		// performed from within the private message post
		// form ... for 2.2 now, too late for 2.0 ... if we
		// decide to do it all, I'm sooo lazy!
		//

	}

	//
	// Toggles
	//
	if( !$board_config['allow_html'] )
	{
		$html_on = 0;
	}
	else
	{
		$html_on = ( $submit || $refresh ) ? ( ( !empty($HTTP_POST_VARS['disable_html']) ) ? 0 : TRUE ) : $userdata['user_allowhtml'];
	}

	if( !$board_config['allow_bbcode'] )
	{
		$bbcode_on = 0;
	}
	else
	{
		$bbcode_on = ( $submit || $refresh ) ? ( ( !empty($HTTP_POST_VARS['disable_bbcode']) ) ? 0 : TRUE ) : $userdata['user_allowbbcode'];
	}

	if( !$board_config['allow_smilies'] )
	{
		$smilies_on = 0;
	}
	else
	{
		$smilies_on = ( $submit || $refresh ) ? ( ( !empty($HTTP_POST_VARS['disable_smilies']) ) ? 0 : TRUE ) : $userdata['user_allowsmile'];
	}

	$attach_sig = ( $submit || $refresh ) ? ( ( !empty($HTTP_POST_VARS['attach_sig']) ) ? TRUE : 0 ) : $userdata['user_attachsig'];
	$user_sig = ( $userdata['user_sig'] != "" ) ? $userdata['user_sig'] : "";
	
	if( $submit && $mode != "edit" )
	{
		//
		// Flood control
		//
		$sql = "SELECT MAX(privmsgs_date) AS last_post_time
			FROM " . PRIVMSGS_TABLE . "
			WHERE privmsgs_from_userid = " . $userdata['user_id'];
		if( $result = $db->sql_query($sql) )
		{
			$db_row = $db->sql_fetchrow($result);

			$last_post_time = $db_row['last_post_time'];
			$current_time = time();

			if( ( $current_time - $last_post_time ) < $board_config['flood_interval'])
			{
				message_die(GENERAL_MESSAGE, $lang['Flood_Error']);
			}
		}
		//
		// End Flood control
		//
	}

	if( $submit )
	{
		if( !empty($HTTP_POST_VARS['username']) )
		{
			$to_username = $HTTP_POST_VARS['username'];

			$sql = "SELECT user_id, user_notify_pm, user_email, user_lang 
				FROM " . USERS_TABLE . "
				WHERE username = '" . $to_username . "'
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

		$privmsg_subject = trim(strip_tags($HTTP_POST_VARS['subject']));
		if( empty($privmsg_subject) )
		{
			$error = TRUE;
			if( !empty($error_msg) )
			{
				$error_msg .= "<br />";
			}
			$error_msg .= $lang['Empty_subject'];
		}

		if( !empty($HTTP_POST_VARS['message']) )
		{
			if( !$error )
			{
				if( $bbcode_on )
				{
					$bbcode_uid = make_bbcode_uid();
				}

				$privmsg_message = prepare_message($HTTP_POST_VARS['message'], $html_on, $bbcode_on, $smilies_on, $bbcode_uid);

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

	if( $submit && !$error )
	{
		//
		// Has admin prevented user from sending PM's?
		//
		if( !$userdata['user_allow_pm'] )
		{
			$message = $lang['Cannot_send_privmsg'];
			message_die(GENERAL_MESSAGE, $message);
		}

		$msg_time = time();

		if( $mode != "edit" )
		{
			//
			// See if recipient is at their inbox limit
			//
			$sql = "SELECT COUNT(privmsgs_id) AS inbox_items, MIN(privmsgs_date) AS oldest_post_time 
				FROM " . PRIVMSGS_TABLE . " 
				WHERE ( privmsgs_type = " . PRIVMSGS_NEW_MAIL . " 
						OR privmsgs_type = " . PRIVMSGS_READ_MAIL . " ) 
					AND privmsgs_from_userid = " . $to_userdata['user_id'];
			if( !$result = $db->sql_query($sql) )
			{
				message_die(GENERAL_MESSAGE, $lang['No_such_user']);
			}

			$sql_priority = (SQL_LAYER == "mysql") ? "LOW_PRIORITY" : "";

			if( $db->sql_numrows($result) )
			{
				$inbox_info = $db->sql_fetchrow($result);

				if( $inbox_info['inbox_items'] > $board_config['max_inbox_privmsgs'] )
				{
					$sql = "DELETE $sql_priority FROM " . PRIVMSGS_TABLE . " 
						WHERE privmsgs_type = " . PRIVMSGS_NEW_MAIL . " 
							AND privmsgs_date = " . $inbox_info['oldest_post_time'] . " 
							AND privmsgs_to_userid = " . $to_userdata['user_id'];
					if( !$result = $db->sql_query($sql) )
					{
						message_die(GENERAL_ERROR, "Could not delete oldest privmsgs.", "", __LINE__, __FILE__, $sql);
					}
				}
			}

			//
			// This area is reserved for future use :D
			//
			
			//
			//
			//

			$sql_info = "INSERT INTO " . PRIVMSGS_TABLE . " (privmsgs_type, privmsgs_subject, privmsgs_from_userid, privmsgs_to_userid, privmsgs_date, privmsgs_ip, privmsgs_enable_html, privmsgs_enable_bbcode, privmsgs_enable_smilies, privmsgs_attach_sig)
				VALUES (" . PRIVMSGS_NEW_MAIL . ", '$privmsg_subject', " . $userdata['user_id'] . ", " . $to_userdata['user_id'] . ", $msg_time, '$user_ip', $html_on, $bbcode_on, $smilies_on, $attach_sig)";
		}
		else
		{
			$sql_info = "UPDATE " . PRIVMSGS_TABLE . "
				SET privmsgs_type = " . PRIVMSGS_NEW_MAIL . ", privmsgs_subject = '$privmsg_subject', privmsgs_from_userid = " . $userdata['user_id'] . ", privmsgs_to_userid = " . $to_userdata['user_id'] . ", privmsgs_date = $msg_time, privmsgs_ip = '$user_ip', privmsgs_enable_html = $html_on, privmsgs_enable_bbcode = $bbcode_on, privmsgs_enable_smilies = $smilies_on, privmsgs_attach_sig = $attach_sig 
				WHERE privmsgs_id = $privmsg_id";
		}

		if( !$pm_sent_status = $db->sql_query($sql_info, BEGIN_TRANSACTION) )
		{
			message_die(GENERAL_ERROR, "Could not insert/update private message sent info.", "", __LINE__, __FILE__, $sql_info);
		}
		else
		{
			if( $mode != "edit" )
			{
				$privmsg_sent_id = $db->sql_nextid();

				$sql = "INSERT INTO " . PRIVMSGS_TEXT_TABLE . " (privmsgs_text_id, privmsgs_bbcode_uid, privmsgs_text)
					VALUES ($privmsg_sent_id, '" . $bbcode_uid . "', '$privmsg_message')";
			}
			else
			{
				$sql = "UPDATE " . PRIVMSGS_TEXT_TABLE . "
					SET privmsgs_text = '$privmsg_message', privmsgs_bbcode_uid = '$bbcode_uid' 
					WHERE privmsgs_text_id = $privmsg_id";
			}

			if( !$pm_sent_text_status = $db->sql_query($sql, END_TRANSACTION) )
			{
				message_die(GENERAL_ERROR, "Could not insert/update private message sent text.", "", __LINE__, __FILE__, $sql_info);
			}
			else if( $mode != "edit" )
			{

				//
				// Add to the users new pm counter
				//
				$sql = "UPDATE " . USERS_TABLE . "
					SET user_new_privmsg = user_new_privmsg + 1, user_last_privmsg = " . time() . "  
					WHERE user_id = " . $to_userdata['user_id']; 
				if( !$status = $db->sql_query($sql) )
				{
					message_die(GENERAL_ERROR, "Could not update private message new/read status for user.", "", __LINE__, __FILE__, $sql);
				}

				if( $to_userdata['user_notify_pm'] && !empty($to_userdata['user_email']) )
				{
					if( isset($HTTP_SERVER_VARS['PATH_INFO']) && dirname($HTTP_SERVER_VARS['PATH_INFO']) != '/')
					{
						$path = dirname($HTTP_SERVER_VARS['PATH_INFO']);
					}
					else if( dirname($HTTP_SERVER_VARS['SCRIPT_NAME']) != '/')
					{
						$path = dirname($HTTP_SERVER_VARS['SCRIPT_NAME']);
					}
					else
					{
						$path = '';
					}
					$server_name = ( isset($HTTP_SERVER_VARS['HTTP_HOST']) ) ? $HTTP_SERVER_VARS['HTTP_HOST'] : $HTTP_SERVER_VARS['SERVER_NAME'];
					$protocol = ( !empty($HTTP_SERVER_VARS['HTTPS']) ) ?  ( ( $HTTP_SERVER_VARS['HTTPS'] == "on" ) ? "https://" : "http://" )  : "http://";

					$email_headers = "From: " . $board_config['board_email'] . "\nReturn-Path: " . $board_config['board_email'] . "\r\n";

					include($phpbb_root_path . 'includes/emailer.'.$phpEx);
					$emailer = new emailer($board_config['smtp_delivery']);
					
					//
					// Attempt to use language setting for recipient
					//
					$emailer->use_template("privmsg_notify", $to_userdata['user_lang']);

					$emailer->extra_headers($email_headers);
					$emailer->email_address($to_userdata['user_email']);
					$emailer->set_subject($lang['Notification_subject']);
					
					$emailer->assign_vars(array(
						"USERNAME" => $to_username, 
						"SITENAME" => $board_config['sitename'],
						"EMAIL_SIG" => str_replace("<br />", "\n", "-- \n" . $board_config['board_email_sig']), 

						"U_INBOX" => $protocol . $server_name . $path . "/privmsg.$phpEx?folder=inbox")
					);

					$emailer->send();
					$emailer->reset();
				}
			}

			$template->assign_vars(array(
				"META" => '<meta http-equiv="refresh" content="3;url=' . append_sid("privmsg.$phpEx?folder=inbox") . '">')
			);

			$msg = $lang['Message_sent'] . "<br /><br />" . sprintf($lang['Click_return_inbox'], "<a href=\"" . append_sid("privmsg.$phpEx?folder=inbox") . "\">", "</a> ") . "<br /><br />" . sprintf($lang['Click_return_index'], "<a href=\"" . append_sid("index.$phpEx") . "\">", "</a>");

			message_die(GENERAL_MESSAGE, $msg);
		}
	}
	else if( $preview || $refresh || $error )
	{

		//
		// If we're previewing or refreshing then obtain the data
		// passed to the script, process it a little, do some checks
		// where neccessary, etc.
		//
		$to_username = ( isset($HTTP_POST_VARS['username']) ) ? trim(strip_tags(stripslashes($HTTP_POST_VARS['username']))) : "";
		$privmsg_subject = ( isset($HTTP_POST_VARS['subject']) ) ? trim(strip_tags(stripslashes($HTTP_POST_VARS['subject']))) : "";
		$privmsg_message = ( isset($HTTP_POST_VARS['message']) ) ? trim(stripslashes($HTTP_POST_VARS['message'])) : "";
		$privmsg_message = preg_replace('#<textarea>#si', '&lt;textarea&gt;', $privmsg_message);

		//
		// Do mode specific things
		//
		if( $mode == "post" )
		{
			$page_title = $lang['Send_new_privmsg'];

			$user_sig = ( $userdata['user_sig'] != "" ) ? $userdata['user_sig'] : "";

		}
		else if( $mode == "reply" )
		{
			$page_title = $lang['Reply_privmsg'];

			$user_sig = ( $userdata['user_sig'] != "" ) ? $userdata['user_sig'] : "";

		}
		else if( $mode == "edit" )
		{
			$page_title = $lang['Edit_privmsg'];

			$sql = "SELECT u.user_id, u.user_sig 
				FROM " . PRIVMSGS_TABLE . " pm, " . USERS_TABLE . " u 
				WHERE pm.privmsgs_id = $privmsg_id 
					AND u.user_id = pm.privmsgs_from_userid";
			if($result = $db->sql_query($sql))
			{
				$postrow = $db->sql_fetchrow($result);

				if( $userdata['user_id'] != $postrow['user_id'] )
				{
					message_die(GENERAL_MESSAGE, $lang['Sorry_edit_own_posts']);
				}

				$user_sig = ( $postrow['user_sig'] != "" ) ? $postrow['user_sig'] : "";
			}
			else
			{
				message_die(GENERAL_ERROR, "Couldn't obtain post and post text", "", __LINE__, __FILE__, $sql);
			}
		}

		//
		// Process the username list operations
		//
		if( $submit_search )
		{
			if( !empty($HTTP_POST_VARS['username_search']) )
			{
			}
		}
	}
	else 
	{
		if( !$privmsg_id && ( $mode == "reply" || $mode == "edit" || $mode == "quote" ) )
		{
			message_die(GENERAL_ERROR, $lang['No_post_id']);
		}

		if( !empty($HTTP_GET_VARS[POST_USERS_URL]) )
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
				$row = $db->sql_fetchrow($result);
				$to_username = $row['username'];
			}
		}

		//
		// Obtain list of groups/users is
		// this user is a group moderator
		//
		if( $mode == "post" )
		{
			unset($mod_group_list);
			$sql = "SELECT g.group_id, g.group_name, g.group_moderator, g.group_single_user, u.username
				FROM " . GROUPS_TABLE . " g, " . USER_GROUP_TABLE . " ug, " . USERS_TABLE . " u
				WHERE g.group_moderator = " . $userdata['user_id'] ."
					AND ug.group_id = g.group_id
					AND u.user_id = ug.user_id";
			if(!$group_status = $db->sql_query($sql))
			{
				message_die(GENERAL_ERROR, "Could not obtain group moderator list.", "", __LINE__, __FILE__, $sql);
			}
			if($db->sql_numrows($group_status))
			{
				$mod_group_list = $db->sql_fetchrowset($group_status);
			}
		}

		if( $mode == "edit" )
		{
			$sql = "SELECT pm.privmsgs_id, pm.privmsgs_subject, pmt.privmsgs_text, u.username, u.user_id, u.user_sig 
				FROM " . PRIVMSGS_TABLE . " pm, " . PRIVMSGS_TEXT_TABLE . " pmt, " . USERS_TABLE . " u
				WHERE pm.privmsgs_id = $privmsg_id
					AND pmt.privmsgs_text_id = pm.privmsgs_id
					AND pm.privmsgs_from_userid = " . $userdata['user_id'] . "
					AND pm.privmsgs_type = " . PRIVMSGS_NEW_MAIL . "
					AND u.user_id = pm.privmsgs_to_userid";
			if( !$pm_edit_status = $db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, "Could not obtain private message for editing.", "", __LINE__, __FILE__, $sql);
			}
			if(!$db->sql_numrows($pm_edit_status))
			{
				header("Location: " . append_sid("privmsg.$phpEx?folder=$folder", true));
			}

			$privmsg = $db->sql_fetchrow($pm_edit_status);

			$privmsg_subject = $privmsg['privmsgs_subject'];
			$privmsg_message = $privmsg['privmsgs_text'];

			$privmsg_message = preg_replace("/\:[0-9a-z\:]*?\]/si", "]", $privmsg_message);
			$privmsg_message = str_replace("<br />", "\n", $privmsg_message);
			$privmsg_message = preg_replace($html_entities_match, $html_entities_replace, $privmsg_message);
			$privmsg_message = preg_replace('#</textarea>#si', '&lt;/textarea&gt;', $privmsg_message);

			$user_sig = $privmsg['user_sig'];

			$to_username = $privmsg['username'];
			$to_userid = $privmsg['user_id'];

		}
		else if( $mode == "reply" || $mode == "quote" )
		{

			$sql = "SELECT pm.privmsgs_subject, pm.privmsgs_date, pmt.privmsgs_text, u.username, u.user_id
				FROM " . PRIVMSGS_TABLE . " pm, " . PRIVMSGS_TEXT_TABLE . " pmt, " . USERS_TABLE . " u
				WHERE pm.privmsgs_id = $privmsg_id
					AND pmt.privmsgs_text_id = pm.privmsgs_id
					AND pm.privmsgs_to_userid = " . $userdata['user_id'] . "
					AND u.user_id = pm.privmsgs_from_userid";
			if(!$pm_reply_status = $db->sql_query($sql))
			{
				message_die(GENERAL_ERROR, "Could not obtain private message for editing.", "", __LINE__, __FILE__, $sql);
			}

			if( !$db->sql_numrows($pm_reply_status) )
			{
				header("Location: " . append_sid("privmsg.$phpEx?folder=$folder", true));
			}
			$privmsg = $db->sql_fetchrow($pm_reply_status);

			$privmsg_subject = ( (strstr("Re:", $privmsg['privmsgs_subject'])) ? $lang['Re'] . ":" : "" ) . $privmsg['privmsgs_subject'];

			$to_username = $privmsg['username'];
			$to_userid = $privmsg['user_id'];

			$privmsg_message = preg_replace("/\:(([a-z0-9]:)?)$post_bbcode_uid/si", "", $privmsg_message);
			$privmsg_message = str_replace("<br />", "\n", $privmsg_message);
			$privmsg_message = preg_replace($html_entities_match, $html_entities_replace, $privmsg_message);
			$privmsg_message = preg_replace('#</textarea>#si', '&lt;/textarea&gt;', $privmsg_message);

			if( $mode == "quote" )
			{
				$privmsg_message = $privmsg['privmsgs_text'];

				$msg_date =  create_date($board_config['default_dateformat'], $privmsg['privmsgs_date'], $board_config['board_timezone']); //"[date]" . $privmsg['privmsgs_time'] . "[/date]";

				$privmsg_message = "[quote=\"" . $to_username . "\"]\n" . $privmsg_message . "\n[/quote]";

				$mode = "reply";
			}
		}
	}

	//
	// Has admin prevented user from sending PM's?
	//
	if( !$userdata['user_allow_pm'] && $mode != "edit" )
	{
		$message = $lang['Cannot_send_privmsg'];
		message_die(GENERAL_MESSAGE, $message);
	}

	//
	// Start output, first preview, then errors
	// then post form
	//
	$page_title = $lang['Send_private_message'];
	include($phpbb_root_path . 'includes/page_header.'.$phpEx);

	if( $preview && !$error )
	{
		$orig_word = array();
		$replacement_word = array();
		$result = obtain_word_list($orig_word, $replacement_word);

		if( $bbcode_on )
		{
			$bbcode_uid = make_bbcode_uid();
		}

		$preview_message = prepare_message($privmsg_message, $html_on, $bbcode_on, $smilies_on, $bbcode_uid);

		//
		// Finalise processing as per viewtopic
		//
		if( !$html_on )
		{
			if( $user_sig != "" || !$userdata['user_allowhtml'] )
			{
				$user_sig = preg_replace("#(<)([\/]?.*?)(>)#is", "&lt;\\2&gt;", $user_sig);
			}
		}

		if( $attach_sig && $user_sig != "" && $userdata['user_sig_bbcode_uid'] )
		{
			$user_sig = bbencode_second_pass($user_sig, $userdata['user_sig_bbcode_uid']);
		}

		if( $bbcode_on )
		{
			$preview_message = bbencode_second_pass($preview_message, $bbcode_uid);
		}

		if( $attach_sig && $user_sig != "" )
		{
			$preview_message = $preview_message . "<br /><br />_________________<br />" . $user_sig;
		}

		if( count($orig_word) )
		{
			$preview_subject = preg_replace($orig_word, $replacement_word, $privmsg_subject);
			$preview_message = preg_replace($orig_word, $replacement_word, $preview_message);
		}

		if( $smilies_on )
		{
			$preview_message = smilies_pass($preview_message);
		}

		$preview_message = make_clickable($preview_message);
		$preview_message = str_replace("\n", "<br />", $preview_message);

		$s_hidden_fields = "<input type=\"hidden\" name=\"folder\" value=\"$folder\" />";
		$s_hidden_fields .= "<input type=\"hidden\" name=\"mode\" value=\"$mode\" />";

		if( isset($privmsg_id) )
		{
			$s_hidden_fields .= "<input type=\"hidden\" name=\"" . POST_POST_URL . "\" value=\"$privmsg_id\" />";
		}

		$template->set_filenames(array(
			"preview" => "privmsgs_preview.tpl")
		);
		$template->assign_vars(array(
			"TOPIC_TITLE" => $preview_subject,
			"POST_SUBJECT" => $preview_subject,
			"MESSAGE_TO" => $to_username, 
			"MESSAGE_FROM" => $userdata['username'], 
			"POST_DATE" => create_date($board_config['default_dateformat'], time(), $board_config['board_timezone']),
			"MESSAGE" => $preview_message,

			"S_HIDDEN_FIELDS" => $s_hidden_fields,

			"L_SUBJECT" => $lang['Subject'],
			"L_DATE" => $lang['Date'],
			"L_FROM" => $lang['From'],
			"L_TO" => $lang['To'],
			"L_PREVIEW" => $lang['Preview'],
			"L_POSTED" => $lang['Posted'])
		);
		$template->assign_var_from_handle("POST_PREVIEW_BOX", "preview");
	}

	//
	// Start error handling
	//
	if($error)
	{
		$template->set_filenames(array(
			"reg_header" => "error_body.tpl")
		);
		$template->assign_vars(array(
			"ERROR_MESSAGE" => $error_msg)
		);
		$template->assign_var_from_handle("ERROR_BOX", "reg_header");
	}
	//
	// End error handling
	//

	//
	// Generic posting form ...
	//

	//
	// Load templates
	//
	$template->set_filenames(array(
		"body" => "posting_body.tpl",
		"jumpbox" => "jumpbox.tpl")
	);

	$jumpbox = make_jumpbox();
	$template->assign_vars(array(
		"L_GO" => $lang['Go'],
		"L_JUMP_TO" => $lang['Jump_to'],
		"L_SELECT_FORUM" => $lang['Select_forum'],

		"S_JUMPBOX_LIST" => $jumpbox,
		"S_JUMPBOX_ACTION" => append_sid("viewforum.$phpEx"))
	);
	$template->assign_var_from_handle("JUMPBOX", "jumpbox");

	//
	// Generate username search output
	//
	$result = $db->sql_query($sql_namesearch);
	$name_set = $db->sql_fetchrowset($result);

	$user_names_select = "";
	if($db->sql_numrows($result))
	{
		for($i = 0; $i < count($name_set); $i++)
		{
			$name_selected = ($to_username == $name_set[$i]['username']) ? " selected=\"selected\"" : "";
			$user_names_select .=  "<option value=\"" . $name_set[$i]['username'] . "\"$name_selected>" . $name_set[$i]['username'] . "</option>\n";
		}
	}
	else
	{
		$user_names_select .=  "<option value=\"" . ANONYMOUS . "\"$name_selected>" . $lang['No_match'] . "</option>\n";
	}

	//
	// Enable extensions in posting_body
	//
	$template->assign_block_vars("privmsg_extensions", array());

	//
	// HTML toggle selection
	//
	if($board_config['allow_html'])
	{
		$html_status = $lang['HTML_is_ON'];
		$template->assign_block_vars("html_checkbox", array());
	}
	else
	{
		$html_status = $lang['HTML_is_OFF'];
	}

	//
	// BBCode toggle selection
	//
	if($board_config['allow_bbcode'])
	{
		$bbcode_status = $lang['BBCode_is_ON'];
		$template->assign_block_vars("bbcode_checkbox", array());
	}
	else
	{
		$bbcode_status = $lang['BBCode_is_OFF'];
	}

	//
	// Smilies toggle selection
	//
	if($board_config['allow_smilies'])
	{
		$smilies_status = $lang['Smilies_are_ON'];
		$template->assign_block_vars("smilies_checkbox", array());
	}
	else
	{
		$smilies_status = $lang['Smilies_are_OFF'];
	}

	//
	// Signature toggle selection - only show if
	// the user has a signature
	//
	if( $user_sig != "" )
	{
		$template->assign_block_vars("signature_checkbox", array());
	}

	if($mode == 'post')
	{
		$post_a = $lang['Send_a_new_message'];
	}
	else if($mode == 'reply')
	{
		$post_a = $lang['Send_a_reply'];
		//
		// Switch mode to post ... it's a bit of a cheat really but once the basic
		// info for the reply is determined it really becomes a new post ... so why
		// do it any other way?!
		//
		$mode = "post";
	}
	else if($mode == 'edit')
	{
		$post_a = $lang['Edit_message'];
	}

	$s_hidden_fields = "<input type=\"hidden\" name=\"folder\" value=\"$folder\" />";
	$s_hidden_fields .= "<input type=\"hidden\" name=\"mode\" value=\"$mode\" />";
	if($mode == "edit")
	{
		$s_hidden_fields .= "<input type=\"hidden\" name=\"" . POST_POST_URL . "\" value=\"$privmsg_id\" />";
	}

	$template->assign_vars(array(
		"SUBJECT" => preg_replace($html_entities_match, $html_entities_replace, $privmsg_subject), 
		"USERNAME" => preg_replace($html_entities_match, $html_entities_replace, $to_username),
		"MESSAGE" => $privmsg_message,
		"HTML_STATUS" => $html_status, 
		"SMILIES_STATUS" => $smilies_status, 
		"BBCODE_STATUS" => $bbcode_status, 
		"FORUM_NAME" => $lang['Private_message'], 

		"BOX_NAME" => $l_box_name, 
		"INBOX_IMG" => $inbox_img, 
		"SENTBOX_IMG" => $sentbox_img, 
		"OUTBOX_IMG" => $outbox_img, 
		"SAVEBOX_IMG" => $savebox_img, 
		"INBOX_LINK" => $inbox_url, 
		"SENTBOX_LINK" => $sentbox_url, 
		"OUTBOX_LINK" => $outbox_url, 
		"SAVEBOX_LINK" => $savebox_url, 

		"L_SUBJECT" => $lang['Subject'],
		"L_MESSAGE_BODY" => $lang['Message_body'],
		"L_OPTIONS" => $lang['Options'],
		"L_SPELLCHECK" => $lang['Spellcheck'],
		"L_PREVIEW" => $lang['Preview'],
		"L_SUBMIT" => $lang['Submit'],
		"L_CANCEL" => $lang['Cancel'],
		"L_POST_A" => $post_a,
		"L_FIND_USERNAME" => $lang['Find_username'],
		"L_FIND" => $lang['Find'],
		"L_DISABLE_HTML" => $lang['Disable_HTML_post'], 
		"L_DISABLE_BBCODE" => $lang['Disable_BBCode_post'], 
		"L_DISABLE_SMILIES" => $lang['Disable_Smilies_post'], 
		"L_ATTACH_SIGNATURE" => $lang['Attach_signature'], 

		"S_HTML_CHECKED" => (!$html_on) ? "checked=\"checked\"" : "", 
		"S_BBCODE_CHECKED" => (!$bbcode_on) ? "checked=\"checked\"" : "", 
		"S_SMILIES_CHECKED" => (!$smilies_on) ? "checked=\"checked\"" : "", 
		"S_SIGNATURE_CHECKED" => ($attach_sig) ? "checked=\"checked\"" : "", 
		"S_NAMES_SELECT" => $user_names_select,
		"S_HIDDEN_FORM_FIELDS" => $s_hidden_fields,
		"S_POST_ACTION" => append_sid("privmsg.$phpEx"),
			
		"U_SEARCH_USER" => append_sid("search.$phpEx?mode=searchuser"), 
		"U_VIEW_FORUM" => append_sid("privmsg.$phpEx"))
	);

	$template->pparse("body");

	include($phpbb_root_path . 'includes/page_tail.'.$phpEx);

}

//
// Default page
//
if( !$userdata['session_logged_in'] )
{
	header("Location: " . append_sid("login.$phpEx?redirect=privmsg.$phpEx&folder=inbox", true));
}

//
// Update unread status 
//
$sql = "UPDATE " . USERS_TABLE . "
	SET user_unread_privmsg = " . ( $userdata['user_new_privmsg'] + $userdata['user_unread_privmsg'] ) . ", user_new_privmsg = 0, user_last_privmsg = " . $userdata['session_start'] . " 
	WHERE user_id = " . $userdata['user_id'];
if( !$status = $db->sql_query($sql) )
{
	message_die(GENERAL_ERROR, "Could not update private message new/read status for user.", "", __LINE__, __FILE__, $sql);
}

//
// Reset PM counters
//
$userdata['user_new_privmsg'] = 0;
$userdata['user_unread_privmsg'] = ( $userdata['user_new_privmsg'] + $userdata['user_unread_privmsg'] );

//
// Generate page
//
$page_title = $lang['Private_Messaging'];
include($phpbb_root_path . 'includes/page_header.'.$phpEx);

//
// Load templates
//
$template->set_filenames(array(
	"body" => "privmsgs_body.tpl",
	"jumpbox" => "jumpbox.tpl")
);

$jumpbox = make_jumpbox();
$template->assign_vars(array(
	"L_GO" => $lang['Go'],
	"L_JUMP_TO" => $lang['Jump_to'],
	"L_SELECT_FORUM" => $lang['Select_forum'],

	"S_JUMPBOX_LIST" => $jumpbox,
	"S_JUMPBOX_ACTION" => append_sid("viewforum.$phpEx"))
);
$template->assign_var_from_handle("JUMPBOX", "jumpbox");

//
// New message
//
$post_new_mesg_url = "<a href=\"" . append_sid("privmsg.$phpEx?mode=post") . "\"><img src=\"" . $images['post_new'] . "\" alt=\"" . $lang['Post_new_message'] . "\" border=\"0\" /></a>";

//
// General SQL to obtain messages
//
$sql_tot = "SELECT COUNT(privmsgs_id) AS total 
	FROM " . PRIVMSGS_TABLE . " ";
$sql = "SELECT pm.privmsgs_type, pm.privmsgs_id, pm.privmsgs_date, pm.privmsgs_subject, u.user_id, u.username 
	FROM " . PRIVMSGS_TABLE . " pm, " . USERS_TABLE . " u ";

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
		$sql_tot .= "WHERE ( ( privmsgs_to_userid = " . $userdata['user_id'] . " 
				AND privmsgs_type = " . PRIVMSGS_SAVED_IN_MAIL . " )
			OR ( privmsgs_from_userid = " . $userdata['user_id'] . " 
				AND privmsgs_type = " . PRIVMSGS_SAVED_OUT_MAIL . ") )";

		$sql .= "WHERE ( ( pm.privmsgs_to_userid = " . $userdata['user_id'] . "
				AND pm.privmsgs_type = " . PRIVMSGS_SAVED_IN_MAIL . " 
				AND u.user_id = pm.privmsgs_from_userid ) 
			OR ( pm.privmsgs_from_userid = " . $userdata['user_id'] . "
				AND pm.privmsgs_type = " . PRIVMSGS_SAVED_OUT_MAIL . "
				AND u.user_id = pm.privmsgs_from_userid ) )";
		break;
}

//
// Show messages over previous x days/months
//
if( $submit_msgdays && ( !empty($HTTP_POST_VARS['msgdays']) || !empty($HTTP_GET_VARS['msgdays']) ) )
{
	$msg_days = (!empty($HTTP_POST_VARS['msgdays'])) ? $HTTP_POST_VARS['msgdays'] : $HTTP_GET_VARS['msgdays'];
	$min_msg_time = time() - ($msg_days * 86400);

	$limit_msg_time_total = " AND privmsgs_date > $min_msg_time";
	$limit_msg_time = " AND pm.privmsgs_date > $min_msg_time ";

	if(!empty($HTTP_POST_VARS['msgdays']))
	{
		$start = 0;
	}
}
else
{
	$limit_msg_time = "";
	$post_days = 0;
}

$sql .= $limit_msg_time . " ORDER BY pm.privmsgs_date DESC LIMIT $start, " . $board_config['topics_per_page'];
$sql_all_tot = $sql_tot;
$sql_tot .= $limit_msg_time_total;

//
// Get messages
//
if( !$pm_tot_status = $db->sql_query($sql_tot) )
{
	message_die(GENERAL_ERROR, "Could not query private message information.", "", __LINE__, __FILE__, $sql_tot);
}
else
{
	if( $db->sql_numrows($pm_tot_status) )
	{
		$row = $db->sql_fetchrow($pm_tot_status);
		$pm_total = $row['total'];
	}
}

if( !$pm_all_status = $db->sql_query($sql_all_tot) )
{
	message_die(GENERAL_ERROR, "Could not query private message information.", "", __LINE__, __FILE__, $sql_tot);
}
else
{
	if( $db->sql_numrows($pm_all_status) )
	{
		$row = $db->sql_fetchrow($pm_all_status);
		$pm_all_total = $row['total'];
	}
}

if( !$pm_status = $db->sql_query($sql) )
{
	message_die(GENERAL_ERROR, "Could not query private messages.", "", __LINE__, __FILE__, $sql);
}
$pm_count = $db->sql_numrows($pm_status);

$pm_list = $db->sql_fetchrowset($pm_status);

//
// Build select box
//
$previous_days = array(0, 1, 7, 14, 30, 90, 180, 364);
$previous_days_text = array($lang['All_Posts'], $lang['1_Day'], $lang['7_Days'], $lang['2_Weeks'], $lang['1_Month'], $lang['3_Months'], $lang['6_Months'], $lang['1_Year']);

$select_msg_days = "";
for($i = 0; $i < count($previous_days); $i++)
{
	$selected = ($msg_days == $previous_days[$i]) ? " selected=\"selected\"" : "";
	$select_msg_days .= "<option value=\"" . $previous_days[$i] . "\"$selected>" . $previous_days_text[$i] . "</option>";
}

//
// Define correct icons
//
if( $folder == "inbox" )
{
	$post_pm_img = "<a href=\"" . append_sid("privmsg.$phpEx?mode=post") . "\"><img src=\"" . $images['pm_postmsg'] . "\" alt=\"" . $lang['Post_new_pm'] . "\" border=\"0\"></a>";
	$reply_pm_img = "<a href=\"" . append_sid("privmsg.$phpEx?mode=reply&amp;" . POST_POST_URL . "=$privmsg_id") . "\"><img src=\"" . $images['pm_replymsg'] . "\" alt=\"" . $lang['Post_reply_pm'] . "\" border=\"0\"></a>";
	$quote_pm_img = "<a href=\"" . append_sid("privmsg.$phpEx?mode=quote&amp;" . POST_POST_URL . "=$privmsg_id") . "\"><img src=\"" . $images['pm_quotemsg'] . "\" alt=\"" . $lang['Post_quote_pm'] . "\" border=\"0\"></a>";
	$edit_pm_img = "";

	$l_box_name = $lang['Inbox'];
}
else if( $folder == "outbox" )
{
	$post_pm_img = "<a href=\"" . append_sid("privmsg.$phpEx?mode=post") . "\"><img src=\"" . $images['pm_postmsg'] . "\" alt=\"" . $lang['Post_new_pm'] . "\" border=\"0\"></a>";
	$reply_pm_img = "";
	$quote_pm_img = "";
	$edit_pm_img = "<a href=\"" . append_sid("privmsg.$phpEx?mode=edit&amp;" . POST_POST_URL . "=$privmsg_id") . "\"><img src=\"" . $images['pm_editmsg'] . "\" alt=\"" . $lang['Edit_pm'] . "\" border=\"0\"></a>";

	$l_box_name = $lang['Outbox'];
}
else if( $folder == "savebox" )
{
	$post_pm_img = "<a href=\"" . append_sid("privmsg.$phpEx?mode=post") . "\"><img src=\"" . $images['pm_postmsg'] . "\" alt=\"" . $lang['Post_new_pm'] . "\" border=\"0\"></a>";
	$reply_pm_img = "<a href=\"" . append_sid("privmsg.$phpEx?mode=reply&amp;" . POST_POST_URL . "=$privmsg_id") . "\"><img src=\"" . $images['pm_replymsg'] . "\" alt=\"" . $lang['Post_reply_pm'] . "\" border=\"0\"></a>";
	$quote_pm_img = "<a href=\"" . append_sid("privmsg.$phpEx?mode=quote&amp;" . POST_POST_URL . "=$privmsg_id") . "\"><img src=\"" . $images['pm_quotemsg'] . "\" alt=\"" . $lang['Post_quote_pm'] . "\" border=\"0\"></a>";
	$edit_pm_img = "";

	$l_box_name = $lang['Savedbox'];
}
else if( $folder == "sentbox" )
{
	$post_pm_img = "<a href=\"" . append_sid("privmsg.$phpEx?mode=post") . "\"><img src=\"" . $images['pm_postmsg'] . "\" alt=\"" . $lang['Post_new_pm'] . "\" border=\"0\"></a>";
	$reply_pm_img = "";
	$quote_pm_img = "<a href=\"" . append_sid("privmsg.$phpEx?mode=quote&amp;" . POST_POST_URL . "=$privmsg_id") . "\"><img src=\"" . $images['pm_quotemsg'] . "\" alt=\"" . $lang['Post_quote_pm'] . "\" border=\"0\"></a>";
	$edit_pm_img = "";

	$l_box_name = $lang['Sentbox'];
}

//
// Output data for inbox status
//
if( $folder != "outbox" )
{
	$inbox_limit_pct = round(( $pm_all_total / $board_config['max_' . $folder . '_privmsgs'] ) * 100);
	$inbox_limit_img_length = round(( $pm_all_total / $board_config['max_' . $folder . '_privmsgs'] ) * $board_config['privmsg_graphic_length']);
	$inbox_limit_remain = $board_config['max_' . $folder . '_privmsgs'] - $pm_all_total;

	$template->assign_block_vars("box_size_notice", array());

	switch( $folder )
	{
		case 'inbox':
			$l_box_size_status = sprintf($lang['Inbox_size'], $inbox_limit_pct);
			break;
		case 'sentbox':
			$l_box_size_status = sprintf($lang['Sentbox_size'], $inbox_limit_pct);
			break;
		case 'savebox':
			$l_box_size_status = sprintf($lang['Savebox_size'], $inbox_limit_pct);
			break;
		default:
			$l_box_size_status = "";
			break;
	}

}

//
// Dump vars to template
//
$template->assign_vars(array(
	"BOX_NAME" => $l_box_name, 
	"INBOX_IMG" => $inbox_img, 
	"SENTBOX_IMG" => $sentbox_img, 
	"OUTBOX_IMG" => $outbox_img, 
	"SAVEBOX_IMG" => $savebox_img, 
	"INBOX_LINK" => $inbox_url, 
	"SENTBOX_LINK" => $sentbox_url, 
	"OUTBOX_LINK" => $outbox_url, 
	"SAVEBOX_LINK" => $savebox_url, 

	"POST_PM_IMG" => $post_pm_img, 

	"INBOX_LIMIT_IMG_WIDTH" => $inbox_limit_img_length, 
	"INBOX_LIMIT_PERCENT" => $inbox_limit_pct, 

	"BOX_SIZE_STATUS" => $l_box_size_status, 

	"L_INBOX" => $lang['Inbox'],
	"L_OUTBOX" => $lang['Outbox'],
	"L_SENTBOX" => $lang['Sent'],
	"L_SAVEBOX" => $lang['Saved'],
	"L_MARK" => $lang['Mark'],
	"L_FLAG" => $lang['Flag'],
	"L_SUBJECT" => $lang['Subject'],
	"L_DATE" => $lang['Date'],
	"L_DISPLAY_MESSAGES" => $lang['Display_messages'],
	"L_FROM_OR_TO" => ($folder == "inbox" || $folder == "savebox") ? $lang['From'] : $lang['To'], 
	"L_MARK_ALL" => $lang['Mark_all'], 
	"L_UNMARK_ALL" => $lang['Unmark_all'], 
	"L_DELETE_MARKED" => $lang['Delete_marked'], 
	"L_DELETE_ALL" => $lang['Delete_all'], 
	"L_SAVE_MARKED" => $lang['Save_marked'], 

	"S_PRIVMSGS_ACTION" => append_sid("privmsg.$phpEx?folder=$folder"),
	"S_HIDDEN_FIELDS" => "",
	"S_POST_NEW_MSG" => $post_new_mesg_url,
	"S_MSG_DAYS_OPTIONS" => $select_msg_days,

	"U_POST_NEW_TOPIC" => $post_new_topic_url)
);


//
// Okay, let's build the correct folder
//
if( $pm_count )
{
	for($i = 0; $i < $pm_count; $i++)
	{
		$privmsg_id = $pm_list[$i]['privmsgs_id'];

		$flag = $pm_list[$i]['privmsgs_type'];
		$icon_flag = ($flag == PRIVMSGS_NEW_MAIL ) ? "<img src=\"" . $images['pm_unreadmsg'] . "\" alt=\"" . $lang['Unread_message'] . "\" border=\"0\">" : "<img src=\"" . $images['pm_readmsg'] . "\" alt=\"" . $lang['Read_message'] . "\" border=\"0\">";

		$msg_userid = $pm_list[$i]['user_id'];
		$msg_username = $pm_list[$i]['username'];

		$u_from_user_profile = append_sid("profile.$phpEx?mode=viewprofile&amp;" . POST_USERS_URL . "=$msg_userid");

		$msg_subject = $pm_list[$i]['privmsgs_subject'];

		if( count($orig_word) )
		{
			$msg_subject = preg_replace($orig_word, $replacement_word, $msg_subject);
		}
		
		$u_subject = append_sid("privmsg.$phpEx?folder=$folder&amp;mode=read&amp;" . POST_POST_URL . "=$privmsg_id");

		$msg_date = create_date($board_config['default_dateformat'], $pm_list[$i]['privmsgs_date'], $board_config['board_timezone']);

		if( $flag == PRIVMSGS_NEW_MAIL && $folder == "inbox" )
		{
			$msg_subject = "<b>" . $msg_subject . "</b>";
			$msg_date = "<b>" . $msg_date . "</b>";
			$msg_username = "<b>" . $msg_username . "</b>";
		}

		$row_color = (!($i % 2)) ? $theme['td_color1'] : $theme['td_color2'];
		$row_class = (!($i % 2)) ? $theme['td_class1'] : $theme['td_class2'];

		$template->assign_block_vars("listrow", array(
			"ROW_COLOR" => "#". $row_color,
			"ROW_CLASS" => $row_class,
			"ICON_FLAG_IMG" => $icon_flag,
			"FROM" => $msg_username,
			"SUBJECT" => $msg_subject,
			"DATE" => $msg_date,

			"S_MARK_ID" => $privmsg_id, 

			"U_READ" => $u_subject,
			"U_FROM_USER_PROFILE" => $u_from_user_profile)
		);
	} // for ...

	$template->assign_vars(array(
		"PAGINATION" => generate_pagination("privmsg.$phpEx?folder=$folder", $pm_total, $board_config['topics_per_page'], $start),
		"PAGE_NUMBER" => sprintf($lang['Page_of'], ( floor( $start / $board_config['topics_per_page'] ) + 1 ), ceil( $pm_total / $board_config['topics_per_page'] )), 

		"L_GOTO_PAGE" => $lang['Goto_page'])
	);

}
else
{
	$template->assign_vars(array(
		"L_NO_MESSAGES" => $lang['No_messages_folder'])
	);

	$template->assign_block_vars("nomessages", array() );
}

$template->pparse("body");

include($phpbb_root_path . 'includes/page_tail.'.$phpEx);

?>
