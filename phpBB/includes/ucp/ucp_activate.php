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
 ***************************************************************************/
 
class ucp_activate extends ucp
{
	function main($module_id)
	{
		global $censors, $config, $db, $user, $auth, $SID, $template, $phpbb_root_path, $phpEx;

		$sql = "SELECT user_active, user_id, user_email, user_newpasswd, user_lang, user_actkey, username
			FROM " . USERS_TABLE . "
			WHERE user_id = " . intval($_GET['u']);
		$result = $db->sql_query($sql);

		if ($row = $db->sql_fetchrow($result))
		{
			if ($row['user_active'] && $row['user_actkey'] == '')
			{
				meta_refresh(3, "index.$phpEx$SID");
				trigger_error($user->lang['Already_activated']);
			}
			else if ($row['user_actkey'] == $_GET['k'])
			{
				$sql_update_pass = ($row['user_newpasswd'] != '') ? ", user_password = '" . $db->sql_escape($row['user_newpasswd']) . "', user_newpasswd = ''" : '';
z
				$sql = "UPDATE " . USERS_TABLE . "
					SET user_active = 1, user_actkey = ''" . $sql_update_pass . "
					WHERE user_id = " . $row['user_id'];
				$result = $db->sql_query($sql);

				if ($config['require_activation'] == USER_ACTIVATION_ADMIN && $sql_update_pass == '')
				{
					$this->include_file('includes/emailer');
					$emailer = new emailer($config['smtp_delivery']);

					$emailer->use_template('admin_welcome_activated', $row['user_lang']);
					$emailer->to($row['user_email']);

					$emailer->assign_vars(array(
						'SITENAME'	=> $config['sitename'],
						'USERNAME'	=> $row['username'],
						'PASSWORD'	=> $password_confirm,
						'EMAIL_SIG' => str_replace('<br />', "\n", "-- \n" . $config['board_email_sig']))
					);
					$emailer->send();
					$emailer->reset();

					meta_refresh(3, "index.$phpEx$SID");
					trigger_error($user->lang['Account_active_admin']);
				}
				else
				{
					meta_refresh(3, "index.$phpEx$SID");
					$message = ($sql_update_pass == '') ? $user->lang['Account_active'] : $user->lang['Password_activated'];
					trigger_error($message);
				}

				set_config('newest_user_id', $row['user_id']);
				set_config('newest_username', $row['username']);
				set_config('num_users', $config['num_users'] + 1, TRUE);
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
		$db->sql_freeresult($result);
	}
}

?>