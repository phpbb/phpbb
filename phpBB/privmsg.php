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

$mode = (!empty($HTTP_GET_VARS['mode'])) ? $HTTP_GET_VARS['mode'] : 'inbox';
$start = (!empty($HTTP_GET_VARS['start'])) ? $HTTP_GET_VARS['start'] : 0;

//
// Output page header and
// open the index body template
//
include('includes/page_header.'.$phpEx);

//
// Start main
//

if($mode == "inbox" || $mode == "sent")
{

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

	$inbox_url =  "<img src=\"images/msg_inbox.gif\" border=\"0\">&nbsp;";
	$inbox_url .= ($mode == "sent") ? "<a href=\"" . append_sid("privmsg.$phpEx?mode=inbox") . "\"><b>" . $lang['Inbox'] . "</b></a>" : "<b>" . $lang['Inbox'] . "</b>";
	$sent_url =  "<img src=\"images/msg_inbox.gif\" border=\"0\">&nbsp;";
	$sent_url .= ($mode == "inbox") ? "<a href=\"" . append_sid("privmsg.$phpEx?mode=sent") . "\"><b>" . $lang['Sent'] . "</b></a>" : "<b>" . $lang['Sent'] . "</b>";

	$template->assign_vars(array(
		"INBOX_FOLDER" => $inbox_url, 
		"SENT_FOLDER" => $sent_url, 

		"L_FLAG" => $lang['Flag'],
		"L_SUBJECT" => $lang['Subject'],
		"L_DATE" => $lang['Date'], 
		"L_FROM_OR_TO" => (($mode == "inbox") ? $lang['From'] : $lang['To']),

		"U_FOLDER2" => $u_folder2)
	);

	if($mode == "inbox")
	{
		$sql_tot = "SELECT COUNT(pm.privmsgs_id) AS pm_total 
			FROM " . PRIVMSGS_TABLE . " pm, " . USER_GROUP_TABLE . " ug  
			WHERE ug.group_id = pm.privmsgs_to_groupid 
				AND ug.user_id = " . $userdata['user_id'] . " 
				AND pm.privmsgs_type <> " . PRIVMSGS_SENT_MAIL;

		$sql = "SELECT pm.privmsgs_type, pm.privmsgs_id, pm.privmsgs_date, pm.privmsgs_subject, ug.user_id, g.group_name, g.group_single_user 
			FROM " . PRIVMSGS_TABLE . " pm, " . USER_GROUP_TABLE . " ug, " . USER_GROUP_TABLE . " ug2, " . GROUPS_TABLE . " g 
			WHERE ug.group_id = pm.privmsgs_from_groupid 
				AND g.group_id = ug.group_id 
				AND ug2.group_id = pm.privmsgs_to_groupid 
				AND ug2.user_id = " . $userdata['user_id'] . " 
				AND pm.privmsgs_type <> " . PRIVMSGS_SENT_MAIL . "
			ORDER BY pm.privmsgs_date DESC 
			LIMIT $start, " . $board_config['topics_per_page'];
	}
	else
	{
		$sql_tot = "SELECT COUNT(pm.privmsgs_id) AS pm_total 
			FROM " . PRIVMSGS_TABLE . " pm, " . USER_GROUP_TABLE . " ug  
			WHERE ug.group_id = pm.privmsgs_from_groupid 
				AND ug.user_id = " . $userdata['user_id'] . "
				AND pm.privmsgs_type = " . PRIVMSGS_SENT_MAIL;

		$sql = "SELECT pm.privmsgs_type, pm.privmsgs_id, pm.privmsgs_date, pm.privmsgs_subject, ug.user_id, g.group_name, g.group_single_user 
			FROM " . PRIVMSGS_TABLE . " pm, " . USER_GROUP_TABLE . " ug, " . USER_GROUP_TABLE . " ug2, " . GROUPS_TABLE . " g 
			WHERE ug.group_id = pm.privmsgs_to_groupid 
				AND g.group_id = ug.group_id 
				AND ug2.group_id = pm.privmsgs_from_groupid 
				AND ug2.user_id = " . $userdata['user_id'] . "
				AND pm.privmsgs_type = " . PRIVMSGS_SENT_MAIL . "
			ORDER BY pm.privmsgs_date DESC 
			LIMIT $start, " . $board_config['topics_per_page'];
	}
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
	// Okay, let's build the index
	//

	for($i = 0; $i < count($pm_list); $i++)
	{

		$flag = $pm_list[$i]['privmsgs_type'];
		$icon_flag = ($flag == PRIVMSGS_READ_MAIL || $flag == PRIVMSGS_SENT_MAIL) ? "<img src=\"images/msg_read.gif\">" : "<img src=\"images/msg_unread.gif\">";

		$msg_userid = $pm_list[$i]['user_id'];
		$msg_username = stripslashes($pm_list[$i]['group_name']);
		if($pm_list[$i]['group_single_user'])
		{
			$u_from_user_profile = "profile.$phpEx?mode=viewprofile&" . POST_USERS_URL . "=$msg_userid";
		}
		else
		{
			$u_from_user_profile = "groupadmin.$phpEx?" . POST_GROUPS_URL . "=$msg_userid";
		}

		$msg_id = $pm_list[$i]['privmsgs_id'];

		$msg_subject = stripslashes($pm_list[$i]['privmsgs_subject']);
		$u_subject = "privmsg.$phpEx?mode=read&box=$mode&" . POST_POST_URL . "=$msg_id";

		$msg_date = create_date($board_config['default_dateformat'], $pm_list[$i]['privmsgs_date'], $board_config['default_timezone']);

		if($flag == PRIVMSGS_NEW_MAIL)
		{
			$msg_subject = "<b>" . $msg_subject . "</b>";
			$msg_date = "<b>" . $msg_date . "</b>";
			$msg_username = "<b>" . $msg_username . "</b>";
		}

		if(!($i % 2))
		{
			$row_color = "#".$theme['td_color1'];
		}
		else
		{
			$row_color = "#".$theme['td_color2'];
		}

		$template->assign_block_vars("listrow",
			array(
				"ICON_FLAG_IMG" => $icon_flag,
				"FROM" => $msg_username,
				"SUBJECT" => $msg_subject,
				"DATE" => $msg_date,
				"ROW_COLOR" => $row_color,

				"U_READ" => $u_subject,
				"U_FROM_USER_PROFILE" => $u_from_user_profile)
		);
	} // for ... 

	$template->assign_vars(array(
		"PAGINATION" => generate_pagination("privmsg.$phpEx?mode=$mode", $pm_total, $board_config['topics_per_page'], $start),
		"ON_PAGE" => (floor($start/$board_config['topics_per_page'])+1),
		"TOTAL_PAGES" => ceil(($pm_total)/$board_config['topics_per_page']),

		"L_OF" => $lang['of'],
		"L_PAGE" => $lang['Page'],
		"L_GOTO_PAGE" => $lang['Goto_page'])
	);

}
else if($mode == "read")
{

	if(!empty($HTTP_GET_VARS[POST_POST_URL]))
	{
		$privmsgs_id = $HTTP_GET_VARS[POST_POST_URL]; 
	}
	else
	{
		// Error out

	}
	if(!empty($HTTP_GET_VARS['box']))
	{
		$box_type = $HTTP_GET_VARS['box']; 
		if($box_type == "inbox" || $box_type == "saved")
		{
			$user_to_sql = "AND pm.privmsgs_to_groupid = ug2.group_id AND ug2.user_id = " . $userdata['user_id'];
			$user_from_sql = "AND pm.privmsgs_from_groupid = ug.group_id AND u.user_id = ug.user_id";
		}
		else
		{
			$user_to_sql = "AND pm.privmsgs_to_groupid = ug.group_id AND u.user_id = ug.user_id";
			$user_from_sql = "AND pm.privmsgs_from_groupid = ug2.group_id AND ug2.user_id = " . $userdata['user_id'];
		}
	}
	else
	{
		// Error out

	}

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

	$sql = "SELECT u.username, u.user_id, u.user_website, u.user_icq, u.user_aim, u.user_yim, u.user_msnm, u.user_viewemail, u.user_sig, u.user_avatar, pm.privmsgs_id, pm.privmsgs_type, pm.privmsgs_date, pm.privmsgs_subject, pm.privmsgs_bbcode_uid, pmt.privmsgs_text 
		FROM ".PRIVMSGS_TABLE." pm, " . PRIVMSGS_TEXT_TABLE . " pmt, ".USERS_TABLE." u, " . USER_GROUP_TABLE . " ug,  " . USER_GROUP_TABLE . " ug2
		WHERE pm.privmsgs_id = $privmsgs_id 
			AND pmt.privmsgs_text_id = pm.privmsgs_id 
			$user_to_sql 
			$user_from_sql";
	if(!$pm_status = $db->sql_query($sql))
	{
		error_die(SQL_QUERY, "Could not query private message post information.", __LINE__, __FILE__);
	}
	$privmsg = $db->sql_fetchrow($pm_status);

	if($privmsg['privmsgs_type'] == PRIVMSGS_NEW_MAIL)
	{
		$sql = "UPDATE " . PRIVMSGS_TABLE . " 
			SET privmsgs_type = " . PRIVMSGS_READ_MAIL . " 
			WHERE privmsgs_id = " . $privmsg['privmsgs_id'];
		if(!$pm_upd_status = $db->sql_query($sql))
		{
			error_die(SQL_QUERY, "Could not update private message read status.", __LINE__, __FILE__);
		}
	}

	$inbox_url = "<img src=\"images/msg_inbox.gif\" border=\"0\">&nbsp;<a href=\"" . append_sid("privmsg.$phpEx?mode=inbox") . "\"><b>" . $lang['Inbox'] . "</b></a>";
	$sent_url = "<img src=\"images/msg_inbox.gif\" border=\"0\">&nbsp;<a href=\"" . append_sid("privmsg.$phpEx?mode=sent") . "\"><b>" . $lang['Sent'] . "</b></a>";

	$template->assign_vars(array(
		"INBOX_FOLDER" => $inbox_url, 
		"SENT_FOLDER" => $sent_url, 

		"L_FLAG" => $lang['Flag'],
		"L_SUBJECT" => $lang['Subject'],
		"L_DATE" => $lang['Date'], 
		"L_FROM_OR_TO" => (($mode == "inbox") ? $lang['From'] : $lang['To']),

		"U_FOLDER2" => $u_folder2)
	);

	$poster = stripslashes($privmsg['username']);
	$poster_id = $privmsg['user_id'];
	$post_date = create_date($board_config['default_dateformat'], $privmsg['privmsgs_date'], $board_config['default_timezone']);
	$poster_avatar = ($privmsg['user_avatar'] != "" && $userdata['user_id'] != ANONYMOUS) ? "<img src=\"".$board_config['avatar_path']."/".$privmsg['user_avatar']."\">" : "";

	$profile_img = "<a href=\"".append_sid("profile.$phpEx?mode=viewprofile&".POST_USERS_URL."=$poster_id")."\"><img src=\"".$images['profile']."\" alt=\"$l_profileof $poster\" border=\"0\"></a>";
	$email_img = ($privmsg['user_viewemail'] == 1) ? "<a href=\"mailto:".$privmsg['user_email']."\"><img src=\"".$images['email']."\" alt=\"$l_email $poster\" border=\"0\"></a>" : "";
	$www_img = ($privmsg['user_website']) ? "<a href=\"".$privmsg['user_website']."\"><img src=\"".$images['www']."\" alt=\"$l_viewsite\" border=\"0\"></a>" : "";

	if($privmsg['user_icq'])
	{
		$icq_status_img = "<a href=\"http://wwp.icq.com/".$privmsg['user_icq']."#pager\"><img src=\"http://online.mirabilis.com/scripts/online.dll?icq=".$privmsg['user_icq']."&img=5\" alt=\"$l_icqstatus\" border=\"0\"></a>";
		$icq_add_img = "<a href=\"http://wwp.icq.com/scripts/search.dll?to=".$privmsg['user_icq']."\"><img src=\"".$images['icq']."\" alt=\"$l_icq\" border=\"0\"></a>";
	}
	else
	{
		$icq_status_img = "";
		$icq_add_img = "";
	}

	$aim_img = ($privmsg['user_aim']) ? "<a href=\"aim:goim?screenname=".$privmsg['user_aim']."&message=Hello+Are+you+there?\"><img src=\"".$images['aim']."\" border=\"0\"></a>" : "";
	$msn_img = ($privmsg['user_msnm']) ? "<a href=\"profile.$phpEx?mode=viewprofile&".POST_USERS_URL."=$poster_id\"><img src=\"".$images['msn']."\" border=\"0\"></a>" : "";
	$yim_img = ($privmsg['user_yim']) ? "<a href=\"http://edit.yahoo.com/config/send_webmesg?.target=".$privmsg['user_yim']."&.src=pg\"><img src=\"".$images['yim']."\" border=\"0\"></a>" : "";

	$quote_img = "<a href=\"".append_sid("posting.$phpEx?mode=reply&quote=true&".POST_POST_URL."=".$privmsg['post_id']."&".POST_TOPIC_URL."=$topic_id&".POST_FORUM_URL."=$forum_id")."\"><img src=\"".$images['quote']."\" alt=\"$l_replyquote\" border=\"0\"></a>";

	$post_subject = stripslashes($privmsg['privmsgs_subject']);
	$message = stripslashes($privmsg['privmsgs_text']);
	$bbcode_uid = $privmsg['privmsgs_bbcode_uid'];
	$user_sig = stripslashes($privmsg['user_sig']);

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

		"L_FROM" => $lang['From'])
	);

}

$template->pparse("body");

include('includes/page_tail.'.$phpEx);

?>