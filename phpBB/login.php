<?php
/***************************************************************************
 *                                login.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
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
 ***************************************************************************/

//
// Allow people to reach login page if
// board is shut down
//
define("IN_LOGIN", true);

$phpbb_root_path = "./";
include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'common.'.$phpEx);


//
// Set page ID for session management
//
$userdata = session_pagestart($user_ip, PAGE_LOGIN, $session_length);
init_userprefs($userdata);
//
// End session management
//

if( isset($HTTP_POST_VARS['login']) || isset($HTTP_GET_VARS['login']) || isset($HTTP_POST_VARS['logout']) || isset($HTTP_GET_VARS['logout']) )
{
	if( ( isset($HTTP_POST_VARS['login']) || isset($HTTP_GET_VARS['login']) ) && !$userdata['session_logged_in'] )
	{
		$username = isset($HTTP_POST_VARS['username']) ? $HTTP_POST_VARS['username'] : "";
		$password = isset($HTTP_POST_VARS['password']) ? $HTTP_POST_VARS['password'] : "";

		$sql = "SELECT user_id, username, user_password, user_active, user_level 
			FROM ".USERS_TABLE."
			WHERE username = '" . str_replace("\'", "''", $username) . "'";
		$result = $db->sql_query($sql);
		if(!$result)
		{
			message_die(GENERAL_ERROR, "Error in obtaining userdata : login", "", __LINE__, __FILE__, $sql);
		}

		$rowresult = $db->sql_fetchrow($result);

		if( count($rowresult) )
		{
			if( $rowresult['user_level'] != ADMIN && $board_config['board_disable'] )
			{
				header("Location: " . append_sid("index.$phpEx", true));
			}
			else
			{
				if( md5($password) == $rowresult['user_password'] && $rowresult['user_active'] )
				{
					$autologin = ( isset($HTTP_POST_VARS['autologin']) ) ? TRUE : 0;

					$session_id = session_begin($rowresult['user_id'], $user_ip, PAGE_INDEX, $session_length, FALSE, $autologin);

					if( $session_id )
					{
						if( !empty($HTTP_POST_VARS['redirect']) )
						{
							header("Location: " . append_sid($HTTP_POST_VARS['redirect'], true));
						}
						else
						{
							header("Location: " . append_sid("index.$phpEx", true));
						}
					}
					else
					{
						message_die(CRITICAL_ERROR, "Couldn't start session : login", "", __LINE__, __FILE__);
					}
				}
				else
				{
					$redirect = ( !empty($HTTP_POST_VARS['redirect']) ) ? $HTTP_POST_VARS['redirect'] : "";

					$template->assign_vars(array(
						"META" => '<meta http-equiv="refresh" content="3;url=' . append_sid("login.$phpEx?$redirect") . '">')
					);

					$message = $lang['Error_login'] . "<br /><br />" . sprintf($lang['Click_return_login'], "<a href=\"" . append_sid("login.$phpEx?$redirect") . "\">", "</a> ") . "<br /><br />" .  sprintf($lang['Click_return_index'], "<a href=\"" . append_sid("index.$phpEx") . "\">", "</a> ");

					message_die(GENERAL_MESSAGE, $message);
				}
			}
		}
		else
		{
			$redirect = ( !empty($HTTP_POST_VARS['redirect']) ) ? $HTTP_POST_VARS['redirect'] : "";

			$template->assign_vars(array(
				"META" => '<meta http-equiv="refresh" content="3;url=' . append_sid("login.$phpEx?$redirect") . '">')
			);

			$message = $lang['Error_login'] . "<br /><br />" . sprintf($lang['Click_return_login'], "<a href=\"" . append_sid("login.$phpEx?$redirect") . "\">", "</a> ") . "<br /><br />" .  sprintf($lang['Click_return_index'], "<a href=\"" . append_sid("index.$phpEx") . "\">", "</a> ");

			message_die(GENERAL_MESSAGE, $message);
		}
	}
	else if( ( isset($HTTP_GET_VARS['logout']) || isset($HTTP_POST_VARS['logout']) ) && $userdata['session_logged_in'] )
	{
		if( $userdata['session_logged_in'] )
		{
			session_end($userdata['session_id'], $userdata['user_id']);
		}

		if( !empty($HTTP_POST_VARS['redirect']) )
		{
			header("Location: " . append_sid($HTTP_POST_VARS['redirect'], true));
		}
		else
		{
			header("Location: " . append_sid("index.$phpEx", true));
		}
	}
	else
	{
		if( !empty($HTTP_POST_VARS['redirect']) )
		{
			header("Location: " . append_sid($HTTP_POST_VARS['redirect'], true));
		}
		else
		{
			header("Location: " . append_sid("index.$phpEx", true));
		}
	}
}
else
{
	//
	// Do a full login page dohickey if
	// user not already logged in
	//
	if( !$userdata['session_logged_in'] )
	{
		include($phpbb_root_path . 'includes/page_header.'.$phpEx);

		$template->set_filenames(array(
			"body" => "login_body.tpl")
		);

		if( isset($HTTP_POST_VARS['redirect']) || isset($HTTP_GET_VARS['redirect']) )
		{
			$forward_to = $HTTP_SERVER_VARS['QUERY_STRING'];

			if( preg_match("/^redirect=(.*)$/si", $forward_to, $forward_matches) )
			{
				$forward_to = ($forward_matches[3]) ? $forward_matches[3] : $forward_matches[1];

				$forward_match = explode("&", $forward_to);

				if(count($forward_match) > 1)
				{
					$forward_page = "";

					for($i = 1; $i < count($forward_match); $i++)
					{
						if( !ereg("sid=", $forward_match[$i]) )
						{
							if( $forward_page != "" )
							{
								$forward_page .= "&";
							}
							$forward_page .= $forward_match[$i];
						}
					}

					$forward_page = $forward_match[0] . "?" . $forward_page;
				}
				else
				{
					$forward_page = $forward_match[0];
				}
			}
		}
		else
		{
			$forward_page = "";
		}

		$username = ( $userdata['user_id'] != ANONYMOUS ) ? $userdata['username'] : "";

		$s_hidden_fields = '<input type="hidden" name="redirect" value="' . $forward_page . '" />';

		$template->assign_vars(array(
			"USERNAME" => $username,

			"L_ENTER_PASSWORD" => $lang['Enter_password'], 
			"L_SEND_PASSWORD" => $lang['Forgotten_password'],

			"U_SEND_PASSWORD" => append_sid("profile.$phpEx?mode=sendpassword"), 
			
			"S_HIDDEN_FIELDS" => $s_hidden_fields)
		);

		$template->pparse("body");

		include($phpbb_root_path . 'includes/page_tail.'.$phpEx);
	}
	else
	{
		header("Location: " . append_sid("index.$phpEx", true));
	}

}

?>