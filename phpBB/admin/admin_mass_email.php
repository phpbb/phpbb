<?php
/***************************************************************************
*                             admin_email.php
*                              -------------------
*     begin                : Thu May 31, 2001
*     copyright            : (C) 2001 The phpBB Group
*     email                : support@phpbb.com
*
*     $Id$
*
****************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

if($setmodules == 1)
{
	$filename = basename(__FILE__);
	$module['General']['Mass_Email'] = $filename;
	
	return;
}

//
// Load default header
//
$phpbb_root_dir = "./../";
$no_page_header = TRUE;
require('pagestart.inc');

//
// Increase maximum execution time in case of a lot of users, but don't complain about it if it isn't
// allowed.
//
@set_time_limit(1200);

$message = "";
$subject = "";

//
// Do the job ...
//
if( isset($HTTP_POST_VARS['submit']) )
{
	$group_id = intval($HTTP_POST_VARS[POST_GROUPS_URL]);

	if( $group_id != -1 )
	{
		$sql = "SELECT u.user_email 
			FROM " . USERS_TABLE . " u, " . USER_GROUP_TABLE . " ug
			WHERE ug.group_id = $group_id 
				AND ug.user_pending <> " . TRUE . "  
				AND u.user_id = ug.user_id";
	}
	else
	{
		$sql = "SELECT user_email 
			FROM " . USERS_TABLE;
	}

	if( !$result = $db->sql_query($sql) )
	{
		message_die(GENERAL_ERROR, "Coult not select group members!", __LINE__, __FILE__, $sql);
	}

	if( !$db->sql_numrows($result) )
	{
		//
		// Output a relevant GENERAL_MESSAGE about users/group
		// not existing
		//
	}

	$email_list = $db->sql_fetchrowset($g_result);
	
	$subject = stripslashes($HTTP_POST_VARS["subject"]);
	$message = stripslashes($HTTP_POST_VARS["message"]);
	
	//
	// Error checking needs to go here ... if no subject and/or
	// no message then skip over the send and return to the form
	//
	$error = FALSE;

	if( !$error )
	{
		include($phpbb_root_path . 'includes/emailer.'.$phpEx);
		$emailer = new emailer($board_config['smtp_delivery']);

		$email_headers = "From: " . $board_config['board_email'] . "\n";

		$bcc_list = "";
		for($i = 0; $i < count($email_list); $i++)
		{
			if( $bcc_list != "" )
			{
				$bcc_list .= ", ";
			}
			$bcc_list .= $email_list[$i]['user_email'];
		}
		$email_headers .= "Bcc: $bcc_list\n";
		
		$email_headers .= "Return-Path: " . $userdata['board_email'] . "\n";
		$email_headers .= "X-AntiAbuse: Board servername - " . $server_name . "\n";
		$email_headers .= "X-AntiAbuse: User_id - " . $userdata['user_id'] . "\n";
		$email_headers .= "X-AntiAbuse: Username - " . $userdata['username'] . "\n";
		$email_headers .= "X-AntiAbuse: User IP - " . decode_ip($user_ip) . "\r\n";

		$emailer->use_template("admin_send_email");
		$emailer->email_address($board_config['board_email']);
		$emailer->set_subject($subject);
		$emailer->extra_headers($email_headers);

		$emailer->assign_vars(array(
			"SITENAME" => $board_config['sitename'], 
			"BOARD_EMAIL" => $board_config['board_email'], 
			"MESSAGE" => $message)
		);
		$emailer->send();
		$emailer->reset();

		$template->assign_vars(array(
			"META" => '<meta http-equiv="refresh" content="5;url=' . append_sid("index.$phpEx") . '">')
		);

		$message = $lang['Email_sent'] . "<br /><br />" . sprintf($lang['Click_return_admin_index'],  "<a href=\"" . append_sid("index.$phpEx?pane=right") . "\">", "</a>");

		message_die(GENERAL_MESSAGE, $message);
	}
}	

//
// Initial selection
//

$sql = "SELECT group_id, group_name 
	FROM ".GROUPS_TABLE . "  
	WHERE group_single_user <> 1";
$g_result = $db->sql_query($sql);
$group_list = $db->sql_fetchrowset($g_result);

$select_list = '<select name = "' . POST_GROUPS_URL . '">';
$select_list .= '<option value = "-1">' . $lang['All_users'] . '</option>';

for($i = 0;$i < count($group_list); $i++)
{
	$select_list .= "<option value = \"" . $group_list[$i]['group_id'];
	$select_list .= "\">" . $group_list[$i]['group_name'] . "</option>";
}
$select_list .= "</select>";

//
// Generate page
//
include('page_header_admin.'.$phpEx);

$template->set_filenames(array(
	"body" => "admin/user_email_body.tpl")
);

$template->assign_vars(array(
	"MESSAGE" => $message,
	"SUBJECT" => $subject, 

	"L_EMAIL_TITLE" => $lang['Email'],
	"L_EMAIL_EXPLAIN" => $lang['Mass_email_explain'],
	"L_COMPOSE" => $lang['Compose'],
	"L_RECIPIENTS" => $lang['Recipients'],
	"L_EMAIL_SUBJECT" => $lang['Subject'],
	"L_EMAIL_MSG" => $lang['Message'],
	"L_EMAIL" => $lang['Email'],
	"L_NOTICE" => $notice,

	"S_USER_ACTION" => append_sid('admin_mass_email.'.$phpEx),
	"S_GROUP_SELECT" => $select_list)
);

$template->pparse('body');

include('page_footer_admin.'.$phpEx);

?>