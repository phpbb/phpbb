<?php
/***************************************************************************
 *                             ucp_register.php
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

class ucp_register extends ucp
{
	function main()
	{
		global $config, $db, $user, $SID, $template, $phpEx;

		//
		if ($config['require_activation'] == USER_ACTIVATION_DISABLE)
		{
			trigger_error($user->lang['UCP_REGISTER_DISABLE']);
		}

		$coppa = (isset($_REQUEST['coppa'])) ? ((!empty($_REQUEST['coppa'])) ? 1 : 0) : false;
		$agreed = (!empty($_POST['agreed'])) ? 1 : 0;

		//
		if (!$agreed)
		{
			if ($coppa === false && $config['coppa_enable'])
			{
				$now = getdate();
				$coppa_birthday = $user->format_date(mktime($now['hours'] + $user->data['user_dst'], $now['minutes'], $now['seconds'], $now['mon'], $now['mday'] - 1, $now['year'] - 13), $user->lang['DATE_FORMAT']); 
				unset($now);

				$template->assign_vars(array(
					'L_COPPA_NO'		=> sprintf($user->lang['UCP_COPPA_BEFORE'], $coppa_birthday),
					'L_COPPA_YES'		=> sprintf($user->lang['UCP_COPPA_ON_AFTER'], $coppa_birthday),

					'U_COPPA_NO'		=> "ucp.$phpEx$SID&amp;mode=register&amp;coppa=0", 
					'U_COPPA_YES'		=> "ucp.$phpEx$SID&amp;mode=register&amp;coppa=1", 

					'S_SHOW_COPPA'		=> true, 
					'S_REGISTER_ACTION'	=> "ucp.$phpEx$SID&amp;mode=register")
				);
			}
			else
			{
				$template->assign_vars(array(
					'L_AGREEMENT'		=> $user->lang['UCP_AGREEMENT'], 

					'S_SHOW_COPPA'		=> false, 
					'S_REGISTER_ACTION'	=> "ucp.$phpEx$SID&amp;mode=register")
				);
			}

			ucp::output($user->lang['REGISTER'], 'ucp_agreement.html');
		}

		// Check and initialize some variables if needed
		$error = array();
		if (isset($_POST['submit']))
		{
			// Load the userdata manipulation methods
			ucp::load('includes/functions_user');
			$userdata = new userdata();

			if ($message = $userdata->add_user($coppa))
			{
				$message = $message . '<br /><br />' . sprintf($user->lang['RETURN_INDEX'],  "<a href=\"index.$phpEx$SID\">", '</a>');
				trigger_error($message);
			}
		}

		// If an error occured we need to stripslashes on returned data
		$username		= (isset($_POST['username'])) ? stripslashes(htmlspecialchars($_POST['username'])) : '';
		$password		= (isset($_POST['new_password'])) ? stripslashes(htmlspecialchars($_POST['new_password'])) : '';
		$password_confirm = (isset($_POST['password_confirm'])) ? stripslashes(htmlspecialchars($_POST['password_confirm'])) : '';
		$email			= (isset($_POST['email'])) ? stripslashes(htmlspecialchars($_POST['email'])) : '';
		$email_confirm	= (isset($_POST['email_confirm'])) ? stripslashes(htmlspecialchars($_POST['email_confirm'])) : '';
		$lang			= (isset($_POST['lang'])) ? htmlspecialchars($_POST['lang']) : '';
		$tz				= (isset($_POST['tz'])) ? intval($_POST['tz']) : $config['board_timezone'];

		$s_hidden_fields = '<input type="hidden" name="agreed" value="true" /><input type="hidden" name="coppa" value="' . $coppa . '" />';

		// Visual Confirmation - Show images
		$confirm_image = '';
		if (!empty($config['enable_confirm']))
		{
			$sql = "SELECT session_id 
				FROM " . SESSIONS_TABLE; 
			$result = $db->sql_query($sql);

			if ($row = $db->sql_fetchrow($result))
			{
				$confirm_sql = '';
				do
				{
					$confirm_sql .= (($confirm_sql != '') ? ', ' : '') . "'" . $row['session_id'] . "'";
				}
				while ($row = $db->sql_fetchrow($result));
			
				$sql = "DELETE FROM " .  CONFIRM_TABLE . " 
					WHERE session_id NOT IN ($confirm_sql)";
				$db->sql_query($sql);
			}
			$db->sql_freeresult($result);

			$sql = "SELECT COUNT(session_id) AS attempts 
				FROM " . CONFIRM_TABLE . " 
				WHERE session_id = '" . $userdata['session_id'] . "'";
			$result = $db->sql_query($sql);

			if ($row = $db->sql_fetchrow($result))
			{
				if ($row['attempts'] > 5)
				{
					trigger_error($user->lang['Too_many_registers']);
				}
			}
			$db->sql_freeresult($result);

			$code = ucp::gen_rand_string(6);
			$confirm_id = md5(uniqid($user_ip));

			$sql = "INSERT INTO " . CONFIRM_TABLE . " (confirm_id, session_id, code) 
				VALUES ('$confirm_id', '" . $user->data['session_id'] . "', '$code')";
			$db->sql_query($sql);
			
			$confirm_image = (@extension_loaded('zlib')) ? "<img src=\"ucp/usercp_confirm.$phpEx$SID&amp;id=$confirm_id\" alt=\"\" title=\"\" />" : '<img src="ucp/usercp_confirm.$phpEx?$SID&amp;id=$confirm_id&amp;c=1" alt="" title="" /><img src="ucp/usercp_confirm.$phpEx?$SID&amp;id=$confirm_id&amp;c=2" alt="" title="" /><img src="ucp/usercp_confirm.$phpEx?$SID&amp;id=$confirm_id&amp;c=3" alt="" title="" /><img src="ucp/usercp_confirm.$phpEx?$SID&amp;id=$confirm_id&amp;c=4" alt="" title="" /><img src="ucp/usercp_confirm.$phpEx?$SID&amp;id=$confirm_id&amp;c=5" alt="" title="" /><img src="ucp/usercp_confirm.$phpEx?$SID&amp;id=$confirm_id&amp;c=6" alt="" title="" />';
			$s_hidden_fields .= '<input type="hidden" name="confirm_id" value="' . $confirm_id . '" />';
		}

		// 
		$l_reg_cond = '';
		switch ($config['require_activation'])
		{
			case USER_ACTIVATION_SELF:
				$l_reg_cond = $user->lang['UCP_EMAIL_ACTIVATE'];
				break;

			case USER_ACTIVATION_ADMIN:
				$l_reg_cond = $user->lang['UCP_ADMIN_ACTIVATE'];
				break;
		}

		//
		$template->assign_vars(array(
			'USERNAME'			=> $username,
			'PASSWORD'			=> $password,
			'PASSWORD_CONFIRM'	=> $password_confirm,
			'EMAIL'				=> $email,
			'EMAIL_CONFIRM'		=> $email,
			'CONFIRM_IMG'		=> $confirm_image, 
			'ERROR'				=> (sizeof($userdata->error)) ? implode('<br />', $userdata->error) : '', 

			'L_CONFIRM_EXPLAIN'	=> sprintf($user->lang['CONFIRM_EXPLAIN'], '<a href="mailto:' . htmlentities($config['board_contact']) . '">', '</a>'), 
			'L_ITEMS_REQUIRED'	=> $l_reg_cond, 

			'S_LANG_OPTIONS'	=> language_select($lang), 
			'S_TZ_OPTIONS'		=> tz_select($tz),
			'S_CONFIRM_CODE'	=> ($config['enable_confirm']) ? true : false,
			'S_COPPA'			=> $coppa, 
			'S_HIDDEN_FIELDS'	=> $s_hidden_fields,
			'S_UCP_ACTION'		=> "ucp.$phpEx$SID&amp;mode=register")
		);

		//
		ucp::output($user->lang['REGISTER'], 'ucp_register.html');
	}
}

?>