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
$message = $subject = $group_id = '';

// Do the job ...
if (isset($_POST['submit']))
{
	// Increase maximum execution time in case of a lot of users, but don't complain
	// about it if it isn't allowed.
	@set_time_limit(1200);

	// Error checking needs to go here ... if no subject and/or no message then skip 
	// over the send and return to the form
	$group_id = (isset($_POST['g'])) ? intval($_POST['g']) : 0;
	$subject = (!empty($_POST['subject'])) ? stripslashes(trim($_POST['subject'])) : '';
	$message = (!empty($_POST['message'])) ? stripslashes(trim($_POST['message'])) : '';

	$error = array();
	if ($subject == '')
	{
		$error[] = $user->lang['NO_EMAIL_SUBJECT'];
	}

	if ($message == '')
	{
		$error[] = $user->lang['NO_EMAIL_MESSAGE'];
	}

	if (!sizeof($error))	
	{
		$sql = ($group_id) ? 'SELECT u.user_email, u.username, u.user_lang FROM ' . USERS_TABLE . ' u, ' . USER_GROUP_TABLE . " ug  WHERE ug.group_id = $group_id AND ug.user_pending <> 1 AND u.user_id = ug.user_id AND u.user_allow_massemail = 1" : 'SELECT user_email FROM ' . USERS_TABLE . ' WHERE user_allow_massemail = 1';
		$result = $db->sql_query($sql);

		if (!($row = $db->sql_fetchrow($result)))
		{
			trigger_error($user->lang['NO_USER']);
		}
		$db->sql_freeresult($result);
	
		$i = 0;
		$email_list = array();
		do
		{
			$email_list[$row['user_lang']][$i]['email'] = $row['user_email'];
			$email_list[$row['user_lang']][$i]['name'] = $row['username'];
			$i++;
		} 
		while ($row = $db->sql_fetchrow($result));
		$db->sql_freeresult($result);


		// Let's do some checking to make sure that mass mail functions are working in win32 versions of php.
		if (preg_match('#^[c-z]:\\\#i', getenv('PATH')) && !$config['smtp_delivery'] && phpversion() < '4.3')
		{
			// We are running on windows, force delivery to use our smtp functions since
			// php's are broken by default
			$config['smtp_delivery'] = 1;
			$config['smtp_host'] = @ini_get('SMTP');
		}


		include($phpbb_root_path . 'includes/emailer.'.$phpEx);
		$emailer = new emailer(true);

		$extra_headers = 'X-AntiAbuse: Board servername - ' . $config['server_name'] . "\n";
		$extra_headers .= 'X-AntiAbuse: User_id - ' . $user->data['user_id'] . "\n";
		$extra_headers .= 'X-AntiAbuse: Username - ' . $user->data['username'] . "\n";
		$extra_headers .= 'X-AntiAbuse: User IP - ' . $user->ip . "\n";

		foreach ($email_list as $lang => $to_ary)
		{
			foreach ($to_ary as $to)
			{
				$emailer->template('admin_send_email', $lang);

				$emailer->subject($subject);
				$emailer->headers($extra_headers);

				$emailer->replyto($config['board_email']);
				$emailer->to($to['email'], $to['name']);

				$emailer->assign_vars(array(
					'SITENAME'		=> $config['sitename'],
					'CONTACT_EMAIL' => $config['board_contact'],
					'MESSAGE'		=> $message)
				);

				$emailer->send();
				$emailer->reset();
			}
		}

		$emailer->mail_queue->save();
		unset($email_list);

		if ($group_id)
		{
			$sql = 'SELECT group_name 
				FROM ' . GROUPS_TABLE . " 
				WHERE group_id = $group_id";
			$result = $db->sql_query($sql);

			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
			extract($row);
		}
		else
		{
			// Not great but the logging routine doesn't cope well with localising
			// on the fly
			$group_name = $user->lang['ALL_USERS'];
		}

		add_log('admin', 'LOG_MASS_EMAIL', $group_name);
		trigger_error($user->lang['EMAIL_SENT']);
	}
}

// Initial selection
$sql = 'SELECT group_id, group_type, group_name
	FROM ' . GROUPS_TABLE . ' 
	ORDER BY group_type DESC, group_name ASC';
$result = $db->sql_query($sql);

$select_list = '<option value="0"' . ((!$group_id) ? ' selected="selected"' : '') . '>' . $user->lang['ALL_USERS'] . '</option>';
if ($row = $db->sql_fetchrow($result))
{
	do
	{
		$selected = ($group_id == $row['group_id']) ? ' selected="selected"' : '';
		$select_list .= '<option value = "' . $row['group_id'] . '"' . (($row['group_type'] == GROUP_SPECIAL) ? ' class="blue"' : '') . $selected . '>' . (($row['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $row['group_name']] : $row['group_name']) . '</option>';
	}
	while ($row = $db->sql_fetchrow($result));
}
$db->sql_freeresult($result);

adm_page_header($user->lang['MASS_EMAIL']);

?>

<h1><?php echo $user->lang['MASS_EMAIL']; ?></h1>

<p><?php echo $user->lang['MASS_EMAIL_EXPLAIN']; ?></p>

<form method="post" action="admin_email.<?php echo $phpEx.$SID; ?>"><table class="bg" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th colspan="2"><?php echo $user->lang['COMPOSE']; ?></th>
	</tr>
<?php

	if (sizeof($error))
	{

?>
	<tr>
		<td class="row3" colspan="2" align="center"><span class="error"><?php echo implode('<br />', $error); ?></span></td>
	</tr>
<?php

	}

?>
	<tr>
		<td class="row1" align="right"><b><?php echo $user->lang['RECIPIENTS']; ?></b></td>
		<td class="row2" align="left"><select name="g"><?php echo $select_list; ?></select></td>
	</tr>
	<tr>
		<td class="row1" align="right"><b><?php echo $user->lang['SUBJECT']; ?></b></td>
		<td class="row2"><input class="post" type="text" name="subject" size="45" maxlength="100" tabindex="2" value="<?php echo $subject; ?>" /></td>
	</tr>
	<tr>
		<td class="row1" align="right" valign="top"><span class="gen"><b><?php echo $user->lang['MESSAGE']; ?></b></span>
		<td class="row2"><textarea class="post" name="message" rows="10" cols="76" wrap="virtual" tabindex="3"><?php echo $message; ?></textarea></td>
	</tr>
	<tr>
		<td class="cat" colspan="2" align="center"><input type="submit" value="<?php echo $user->lang['EMAIL']; ?>" name="submit" class="mainoption" /></td>
	</tr>
</table></form>

<?php

adm_page_footer();

?>