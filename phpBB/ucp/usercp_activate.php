<?php
/***************************************************************************
 *                            usercp_activate.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id$
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

 
if (!defined('IN_PHPBB'))
{
	die('Hacking attempt');
	exit;
}

$sql = "SELECT user_active, user_id, user_email, user_newpasswd, user_lang, user_actkey, username
	FROM " . USERS_TABLE . "
	WHERE user_id = " . intval($_GET['u']);
$result = $db->sql_query($sql);

if ( $row = $db->sql_fetchrow($result) )
{
	if ( $row['user_active'] && $row['user_actkey'] == '' )
	{
		$template->assign_vars(array(
			'META' => '<meta http-equiv="refresh" content="10;url=index.' . $phpEx . $SID . '">')
		);

		trigger_error($user->lang['Already_activated']);
	}
	else if ( $row['user_actkey'] == $_GET['act_key'] )
	{
		$sql_update_pass = ( $row['user_newpasswd'] != '' ) ? ", user_password = '" . str_replace("\'", "''", $row['user_newpasswd']) . "', user_newpasswd = ''" : '';

		$sql = "UPDATE " . USERS_TABLE . "
			SET user_active = 1, user_actkey = ''" . $sql_update_pass . "
			WHERE user_id = " . $row['user_id'];
		
		$result = $db->sql_query($sql);

		if ( $config['require_activation'] == USER_ACTIVATION_ADMIN && $sql_update_pass == '' )
		{
			include($phpbb_root_path . 'includes/emailer.'.$phpEx);
			$emailer = new emailer($config['smtp_delivery']);

			$email_headers = 'From: ' . $config['board_email'] . "\nReturn-Path: " . $config['board_email'] . "\n";

			$emailer->use_template('admin_welcome_activated', $row['user_lang']);
			$emailer->email_address($row['user_email']);
			$emailer->set_subject();//$lang['Account_activated_subject']
			$emailer->extra_headers($email_headers);

			$emailer->assign_vars(array(
				'SITENAME' => $config['sitename'],
				'USERNAME' => $username,
				'PASSWORD' => $password_confirm,
				'EMAIL_SIG' => str_replace('<br />', "\n", "-- \n" . $config['board_email_sig']))
			);
			$emailer->send();
			$emailer->reset();

			$template->assign_vars(array(
				'META' => '<meta http-equiv="refresh" content="10;url=index.' . $phpEx . $SID . '">')
			);

			trigger_error($user->lang['Account_active_admin']);
		}
		else
		{
			$template->assign_vars(array(
				'META' => '<meta http-equiv="refresh" content="10;url=index.' . $phpEx . $SID . '">')
			);

			$message = ( $sql_update_pass == '' ) ? $user->lang['Account_active'] : $user->lang['Password_activated'];
			trigger_error($message);
		}

		// Sync config
		$sql = "UPDATE " . CONFIG_TABLE . "
			SET config_value = " . $row['user_id'] . "
			WHERE config_name = 'newest_user_id'";
		$db->sql_query($sql);
		$sql = "UPDATE " . CONFIG_TABLE . "
			SET config_value = '" . $row['username'] . "'
			WHERE config_name = 'newest_username'";
		$db->sql_query($sql);
		$sql = "UPDATE " . CONFIG_TABLE . "
			SET config_value = " . ($config['num_users'] + 1) . "
			WHERE config_name = 'num_users'";
		$db->sql_query($sql);
		
	}
	else
	{
		trigger_error($user->lang['Wrong_activation']);
	}
}
else
{
	trigger_error($user->lang['No_such_user']);
}

?>