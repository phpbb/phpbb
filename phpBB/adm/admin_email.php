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

if (!empty($setmodules))
{
	$file = basename(__FILE__);
	$module['GENERAL']['MASS_EMAIL'] = ($auth->acl_get('a_email')) ? "$file$SID" : '';
	return;
}

define('IN_PHPBB', 1);
// Include files
$phpbb_root_path = '../';
require($phpbb_root_path . 'extension.inc');
require('pagestart.' . $phpEx);

// Check permissions
if (!$auth->acl_get('a_email'))
{
	trigger_error($user->lang['NO_ADMIN']);
}

// Set some vars
$message = '';
$subject = '';

// Do the job ...
if (isset($_POST['submit']))
{
	// Increase maximum execution time in case of a lot of users, but don't complain about it if it isn't
	// allowed.
	@set_time_limit(1200);

	$group_id = intval($_POST['g']);

	if ($group_id > 0)
	{
		$sql = 'SELECT u.user_email, u.username, u.user_lang
			FROM ' . USERS_TABLE . ' u, ' . USER_GROUP_TABLE . " ug 
			WHERE ug.group_id = $group_id 
				AND g.user_pending <> " . TRUE . " 
				AND u.user_id = ug.user_id";
	}
	else
	{
		$sql = 'SELECT user_email FROM ' . USERS_TABLE;
	}
	$result = $db->sql_query($sql);

	$subject = stripslashes(trim($_POST['subject']));
	$message = stripslashes(trim($_POST['message']));
	
	if (!($row = $db->sql_fetchrow($result)))
	{
		// Output a relevant GENERAL_MESSAGE about users/group
		// not existing
		trigger_error($user->lang['GROUP_DOES_NOT_EXIST']);
	}

	
	// Error checking needs to go here ... if no subject and/or
	// no message then skip over the send and return to the form


	if ($subject != '' && $message != '')	
	{
		include($phpbb_root_path . 'includes/emailer.'.$phpEx);

		// Let's do some checking to make sure that mass mail functions
		// are working in win32 versions of php.
		if (preg_match('/[c-z]:\\\.*/i', getenv('PATH')) && !$config['smtp_delivery'])
		{
			// We are running on windows, force delivery to use
			// our smtp functions since php's are broken by default
			$config['smtp_delivery'] = 1;
			$config['smtp_host'] = get_cfg_var('SMTP');
		}
		$emailer = new emailer(true);

		$extra_headers = 'X-AntiAbuse: Board servername - ' . $config['server_name'] . "\r\n";
		$extra_headers .= 'X-AntiAbuse: User_id - ' . $user->data['user_id'] . "\r\n";
		$extra_headers .= 'X-AntiAbuse: Username - ' . $user->data['username'] . "\r\n";
		$extra_headers .= 'X-AntiAbuse: User IP - ' . $user->ip . "\r\n";

		$email_list = array();
		$count = 0;
		do
		{
			$email_list[$count]['email'] = $row['user_email'];
			$email_list[$count]['name'] = $row['username'];
			$email_list[$count]['lang'] = $row['user_lang'];
			$count++;
		} 
		while ($row = $db->sql_fetchrow($result));
		$db->sql_freeresult($result);

		foreach ($email_list as $addr)
		{
			$emailer->template('admin_send_email', $addr['lang']);

			$emailer->subject($subject);
			$emailer->headers($extra_headers);

			$emailer->replyto($config['board_email']);
			$emailer->to($addr['email'], $addr['name']);


			$emailer->assign_vars(array(
				'SITENAME' => $config['sitename'],
				'CONTACT_EMAIL' => $config['board_contact'],
				'MESSAGE' => $message)
			);

			$emailer->send();
			$emailer->reset();
		}
	
		$emailer->queue->save();
		unset($email_list);
	
		add_log('admin', 'LOG_MASS_EMAIL');
		trigger_error($user->lang['EMAIL_SENT'], E_USER_NOTICE);
	}
}

// Initial selection
$sql = 'SELECT group_id, group_name
	FROM ' . GROUPS_TABLE;
$result = $db->sql_query($sql);

$select_list = '<select name="g"><option value="-1">' . $user->lang['ALL_USERS'] . '</option>';
if ($row = $db->sql_fetchrow($result))
{
	do
	{
		$select_list .= '<option value = "' . $row['group_id'] . '">' . $row['group_name'] . '</option>';
	}
	while ($row = $db->sql_fetchrow($result));
}
$select_list .= '</select>';

adm_page_header($user->lang['MASS_EMAIL']);

?>

<h1><?php echo $user->lang['MASS_EMAIL']; ?></h1>

<p><?php echo $user->lang['MASS_EMAIL_EXPLAIN']; ?></p>

<form method="post" action="admin_email.<?php echo $phpEx.$SID; ?>">
<table class="bg" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th colspan="2"><?php echo $user->lang['COMPOSE']; ?></th>
	</tr>
	<tr>
		<td class="row1" align="right"><b><?php echo $user->lang['RECIPIENTS']; ?></b></td>
		<td class="row2" align="left"><?php echo $select_list; ?></td>
	</tr>
	<tr>
		<td class="row1" align="right"><b><?php echo $user->lang['SUBJECT']; ?></b></td>
		<td class="row2"><span class="gen"><input type="text" name="subject" size="45" maxlength="100" tabindex="2" class="post" value="<?php echo $subject; ?>" /></span></td>
	</tr>
	<tr>
		<td class="row1" align="right" valign="top"><span class="gen"><b><?php echo $user->lang['MESSAGE']; ?></b></span>
		<td class="row2"><textarea class="post" name="message" rows="15" cols="35" wrap="virtual" style="width:450px" tabindex="3"><?php echo $message; ?></textarea></td>
	</tr>
	<tr>
		<td class="cat" colspan="2" align="center"><input type="submit" value="<?php echo $user->lang['EMAIL']; ?>" name="submit" class="mainoption" /></td>
	</tr>
</table></form>

<?php

adm_page_footer();

?>