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

/***************************************************************************
	Mass Mailings can be used by admin to mail either the entire board or
	all members of a specific group.
***************************************************************************/

if($setmodules == 1)
{
        $filename = basename(__FILE__);
        $module['General']['Mass_Email'] = $filename;

        return;
}

$phpbb_root_path = "./../";
include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'common.'.$phpEx);

//
// Start session management
//
$userdata = session_pagestart($user_ip, PAGE_INDEX, $session_length);
init_userprefs($userdata);
//
// End session management
//

//
// Is user logged in? If yes are they an admin?
//
if( !$userdata['session_logged_in'] )
{
        header("Location: ../login.$phpEx?forward_page=admin/");
}
else if( $userdata['user_level'] != ADMIN )
{
        message_die(GENERAL_MESSAGE, $lang['Not_admin']);
}

//
// Set VERBOSE to 1  for debugging info..
//
define("VERBOSE", 0);

//
// Increase maximum execution time in case of a lot of users, but don't complain about it if it isn't
// allowed.
//
@set_time_limit(600);
//Set form names
$f_title = 'e_title';
$f_msg = 'e_msg';

if(isset($HTTP_POST_VARS['submit']))
{
	$group_id = $HTTP_POST_VARS[POST_GROUPS_URL];
	if($group_id != -1)
	{
		$sql = 'SELECT u.user_email 
			FROM '.USERS_TABLE.' u, '.USER_GROUP_TABLE.' g
			WHERE u.user_id = g.user_id AND g.group_id = '.$group_id;
	}
	else
	{
		$sql = 'SELECT user_email FROM '.USERS_TABLE;
	}
	if(!$g_result = $db->sql_query($sql))
	{
		message_die(GENERAL_ERROR, "Coult not select group members!", __LINE__, __FILE__, $sql);
	}
	$g_list = $db->sql_fetchrowset($g_result);
	
	$email_headers = "From: " . $board_config['board_email_from'] . "\r\n";
	$msg = stripslashes($HTTP_POST_VARS["$f_msg"]);
	
	$email_headers .= 'bcc: ';
	for($i = 0;$i < count($g_list); $i++)
	{
		if($i != 0)
		{
			$email_headers.= ' ,';
		}
		$email_headers .= $g_list[$i]['user_email'];
	}
	mail($board_config['board_email_from'],$HTTP_POST_VARS["$f_title"],$HTTP_POST_VARS["$f_msg"],$email_headers);
	$notice = $lang['Messages'].' '.$lang['Sent'].'!';
}	
//Else, or if they already sent a message

$sql = "SELECT group_id, group_name FROM ".GROUPS_TABLE.' WHERE group_single_user <> 1';
$g_result = $db->sql_query($sql);
$group_list = $db->sql_fetchrowset($g_result);

$select_list = '<SELECT name = "'.POST_GROUPS_URL.'">';
$select_list .= '<OPTION value = "-1">'.$lang['All'].'</OPTION>';

for($i = 0;$i < count($group_list); $i++)
{
	$select_list .= "<OPTION value = \"".$group_list[$i]['group_id'];
	$select_list .= "\">".$group_list[$i]['group_name']."</OPTION>";
}
$select_list .= "</SELECT>";

include('page_header_admin.'.$phpEx);

$template->set_filenames(array(
	"body" => "admin/user_email.tpl")
);

$template->assign_vars(array(
	"L_EMAIL_TITLE" => $lang['Email'],
	"L_EMAIL_EXPLAIN" => $lang['Mass_email_explain'],
	"L_COMPOSE" => $lang['Compose'],
	"L_GROUP_SELECT" => $lang['Group'],
	"L_EMAIL_SUBJECT" => $lang['Subject'],
	"L_EMAIL_MSG" => $lang['Message'],
	"L_EMAIL" => $lang['Email'],
	"L_NOTICE" => $notice,

	"S_USER_ACTION" => append_sid('admin_mass_email.'.$phpEx),
	"S_GROUP_SELECT" => $select_list,
	"S_EMAIL_SUBJECT" => $f_title,
	"S_EMAIL_MSG" => $f_msg)
);

$template->pparse('body');

include('page_footer_admin.'.$phpEx);
?>
