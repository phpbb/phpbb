<?php
/***************************************************************************
*                                admin_email.php
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

if ( !empty($setmodules) )
{
	if ( !$acl->get_acl_admin('general') )
	{
		return;
	}

	$filename = basename(__FILE__);
	$module['General']['Mass_Email'] = $filename . $SID;
	
	return;
}

define('IN_PHPBB', 1);
//
// Include files
//
$phpbb_root_path = '../';
require($phpbb_root_path . 'extension.inc');
require('pagestart.' . $phpEx);

//
// Do we have general admin permissions?
//
if ( !$acl->get_acl_admin('general') )
{
	return;
}

//
// Set some vars
// 
$message = '';
$subject = '';

//
// Do the job ...
//
if ( isset($HTTP_POST_VARS['submit']) )
{
	//
	// Increase maximum execution time in case of a lot of users, but don't complain about it if it isn't
	// allowed.
	//
	@set_time_limit(1200);

	$group_id = intval($HTTP_POST_VARS['g']);

	$sql = ( $group_id != -1 ) ? "SELECT u.user_email FROM " . USERS_TABLE . " u, " . USER_GROUP_TABLE . " ug WHERE ug.group_id = $group_id AND ug.user_pending <> " . TRUE . " AND u.user_id = ug.user_id" : "SELECT user_email FROM " . USERS_TABLE;
	$result = $db->sql_query($sql);

	if ( !($email_list = $db->sql_fetchrowset($g_result)) )
	{
		//
		// Output a relevant GENERAL_MESSAGE about users/group
		// not existing
		//
	}

	$subject = stripslashes($HTTP_POST_VARS['subject']);
	$message = stripslashes($HTTP_POST_VARS['message']);
	
	//
	// Error checking needs to go here ... if no subject and/or
	// no message then skip over the send and return to the form
	//
	$error = FALSE;

	if ( !$error )
	{
		include($phpbb_root_path . 'includes/emailer.'.$phpEx);
		//
		// Let's do some checking to make sure that mass mail functions
		// are working in win32 versions of php.
		//
		if ( preg_match('/[c-z]:\\\.*/i', getenv('PATH')) && !$board_config['smtp_delivery'])
		{
			// We are running on windows, force delivery to use
			// our smtp functions since php's are broken by default
			$board_config['smtp_delivery'] = 1;
			$board_config['smtp_host'] = get_cfg_var('SMTP');
		}
		$emailer = new emailer($board_config['smtp_delivery']);
	
		$email_headers = 'From: ' . $board_config['board_email'] . "\n";

		$bcc_list = '';
		for($i = 0; $i < count($email_list); $i++)
		{
			$bcc_list .= ( ( $bcc_list != '' ) ? ', ' : '' ) . $email_list[$i]['user_email'];
		}
		$email_headers .= "Bcc: $bcc_list\n";
		
		$email_headers .= 'Return-Path: ' . $userdata['board_email'] . "\n";
		$email_headers .= 'X-AntiAbuse: Board servername - ' . $server_name . "\n";
		$email_headers .= 'X-AntiAbuse: User_id - ' . $userdata['user_id'] . "\n";
		$email_headers .= 'X-AntiAbuse: Username - ' . $userdata['username'] . "\n";
		$email_headers .= 'X-AntiAbuse: User IP - ' . $user_ip . "\r\n";

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

		$message = $lang['Email_sent'] . '<br /><br />' . sprintf($lang['Click_return_admin_index'],  '<a href="' . "index.$phpEx$SID?pane=right" . '">', '</a>');

		message_die(MESSAGE, $message);
	}
}	

//
// Initial selection
//

$sql = "SELECT group_id, group_name 
	FROM ".GROUPS_TABLE;
$result = $db->sql_query($sql);

$select_list = '<select name = "g"><option value = "-1">' . $lang['All_users'] . '</option>';
if ( $row = $db->sql_fetchrow($result) )
{
	do
	{
		$select_list .= '<option value = "' . $row['group_id'] . '">' . $row['group_name'] . '</option>';
	}
	while ( $row = $db->sql_fetchrow($result) );
}
$select_list .= '</select>';

page_header($lang['Users']);

?>

<h1><?php echo $lang['Email']; ?></h1>

<p><?php echo $lang['Mass_email_explain']; ?></p>

<form method="post" action="<?php echo "admin_mass_email.$phpEx$SID"; ?>"><table cellspacing="1" cellpadding="4" border="0" align="center" bgcolor="#98AAB1">
	<tr> 
		<th colspan="2"><?php echo $lang['Compose']; ?></th>
	</tr>
	<tr> 
		<td class="row1" align="right"><b><?php echo $lang['Recipients']; ?></b></td>
		<td class="row2" align="left"><?php echo $select_list; ?></td>
	</tr>
	<tr> 
		<td class="row1" align="right"><b><?php echo $lang['Subject']; ?></b></td>
		<td class="row2"><span class="gen"><input type="text" name="subject" size="45" maxlength="100" tabindex="2" class="post" value="<?php echo $subject; ?>" /></span></td>
	</tr>
	<tr> 
		<td class="row1" align="right" valign="top"><span class="gen"><b><?php echo $lang['Message']; ?></b></span> 
		<td class="row2"><textarea class="post" name="message" rows="15" cols="35" wrap="virtual" style="width:450px" tabindex="3"><?php echo $message; ?></textarea></td> 
	</tr>
	<tr> 
		<td class="cat" colspan="2" align="center"><input type="submit" value="<?php echo $lang['Email']; ?>" name="submit" class="mainoption" /></td>
	</tr>
</table></form>

<?php

page_footer();

?>