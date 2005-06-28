<?php
/***************************************************************************
*                             admin_mass_email.php
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

define('IN_PHPBB', 1);

if( !empty($setmodules) )
{
	$filename = basename(__FILE__);
	$module['General']['Mass_Email'] = $filename;
	
	return;
}

//
// Load default header
//
$no_page_header = TRUE;
$phpbb_root_path = './../';
require($phpbb_root_path . 'extension.inc');
require('./pagestart.' . $phpEx);

//
// Increase maximum execution time in case of a lot of users, but don't complain about it if it isn't
// allowed.
//
@set_time_limit(1200);

$message = '';
$subject = '';

//
// Do the job ...
//
if ( isset($HTTP_POST_VARS['submit']) )
{
	$subject = stripslashes(trim($HTTP_POST_VARS['subject']));
	$message = stripslashes(trim($HTTP_POST_VARS['message']));
	
	$error = FALSE;
	$error_msg = '';

	if ( empty($subject) )
	{
		$error = true;
		$error_msg .= ( !empty($error_msg) ) ? '<br />' . $lang['Empty_subject'] : $lang['Empty_subject'];
	}

	if ( empty($message) )
	{
		$error = true;
		$error_msg .= ( !empty($error_msg) ) ? '<br />' . $lang['Empty_message'] : $lang['Empty_message'];
	}

	$group_id = intval($HTTP_POST_VARS[POST_GROUPS_URL]);

	$sql = ( $group_id != -1 ) ? "SELECT u.user_email FROM " . USERS_TABLE . " u, " . USER_GROUP_TABLE . " ug WHERE ug.group_id = $group_id AND ug.user_pending <> " . TRUE . " AND u.user_id = ug.user_id" : "SELECT user_email FROM " . USERS_TABLE;
	if ( !($result = $db->sql_query($sql)) )
	{
		message_die(GENERAL_ERROR, 'Could not select group members', '', __LINE__, __FILE__, $sql);
	}

	if ( $row = $db->sql_fetchrow($result) )
	{
		$bcc_list = array();
		do
		{
			$bcc_list[] = $row['user_email'];
		}
		while ( $row = $db->sql_fetchrow($result) );

		$db->sql_freeresult($result);
	}
	else
	{
		$message = ( $group_id != -1 ) ? $lang['Group_not_exist'] : $lang['No_such_user'];

		$error = true;
		$error_msg .= ( !empty($error_msg) ) ? '<br />' . $message : $message;
	}

	if ( !$error )
	{
		include($phpbb_root_path . 'includes/emailer.'.$phpEx);

		//
		// Let's do some checking to make sure that mass mail functions
		// are working in win32 versions of php.
		//
		if ( preg_match('/[c-z]:\\\.*/i', getenv('PATH')) && !$board_config['smtp_delivery'])
		{
			$ini_val = ( @phpversion() >= '4.0.0' ) ? 'ini_get' : 'get_cfg_var';

			// We are running on windows, force delivery to use our smtp functions
			// since php's are broken by default
			$board_config['smtp_delivery'] = 1;
			$board_config['smtp_host'] = @$ini_val('SMTP');
		}

		$emailer = new emailer($board_config['smtp_delivery']);
	
		$emailer->from($board_config['board_email']);
		$emailer->replyto($board_config['board_email']);

		for ($i = 0; $i < count($bcc_list); $i++)
		{
			$emailer->bcc($bcc_list[$i]);
		}

		$email_headers = 'X-AntiAbuse: Board servername - ' . $board_config['server_name'] . "\n";
		$email_headers .= 'X-AntiAbuse: User_id - ' . $userdata['user_id'] . "\n";
		$email_headers .= 'X-AntiAbuse: Username - ' . $userdata['username'] . "\n";
		$email_headers .= 'X-AntiAbuse: User IP - ' . decode_ip($user_ip) . "\n";

		$emailer->use_template('admin_send_email');
		$emailer->email_address($board_config['board_email']);
		$emailer->set_subject($subject);
		$emailer->extra_headers($email_headers);

		$emailer->assign_vars(array(
			'SITENAME' => $board_config['sitename'], 
			'BOARD_EMAIL' => $board_config['board_email'], 
			'MESSAGE' => $message)
		);
		$emailer->send();
		$emailer->reset();

		message_die(GENERAL_MESSAGE, $lang['Email_sent'] . '<br /><br />' . sprintf($lang['Click_return_admin_index'],  '<a href="' . append_sid("index.$phpEx?pane=right") . '">', '</a>'));
	}
}	

if ( $error )
{
	$template->set_filenames(array(
		'reg_header' => 'error_body.tpl')
	);
	$template->assign_vars(array(
		'ERROR_MESSAGE' => $error_msg)
	);
	$template->assign_var_from_handle('ERROR_BOX', 'reg_header');
}

//
// Initial selection
//

$sql = "SELECT group_id, group_name 
	FROM ".GROUPS_TABLE . "  
	WHERE group_single_user <> 1";
if ( !($result = $db->sql_query($sql)) ) 
{
	message_die(GENERAL_ERROR, 'Could not obtain list of groups', '', __LINE__, __FILE__, $sql);
}

$select_list = '<select name = "' . POST_GROUPS_URL . '"><option value = "-1">' . $lang['All_users'] . '</option>';
if ( $row = $db->sql_fetchrow($result) )
{
	do
	{
		$select_list .= '<option value = "' . $row['group_id'] . '">' . $row['group_name'] . '</option>';
	}
	while ( $row = $db->sql_fetchrow($result) );
}
$select_list .= '</select>';

//
// Generate page
//
include('./page_header_admin.'.$phpEx);

$template->set_filenames(array(
	'body' => 'admin/user_email_body.tpl')
);

$template->assign_vars(array(
	'MESSAGE' => $message,
	'SUBJECT' => $subject, 

	'L_EMAIL_TITLE' => $lang['Email'],
	'L_EMAIL_EXPLAIN' => $lang['Mass_email_explain'],
	'L_COMPOSE' => $lang['Compose'],
	'L_RECIPIENTS' => $lang['Recipients'],
	'L_EMAIL_SUBJECT' => $lang['Subject'],
	'L_EMAIL_MSG' => $lang['Message'],
	'L_EMAIL' => $lang['Email'],
	'L_NOTICE' => $notice,

	'S_USER_ACTION' => append_sid('admin_mass_email.'.$phpEx),
	'S_GROUP_SELECT' => $select_list)
);

$template->pparse('body');

include('./page_footer_admin.'.$phpEx);

?>