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
define('IN_LOGIN', true);
define('IN_PHPBB', true);
$phpbb_root_path = './';
include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'common.'.$phpEx);

//
// Set page ID for session management
//
$userdata = $session->start();
$session->configure($userdata);

$acl = new auth('list', $userdata);
//
// End session management
//

//
// Configure style, language, etc.
//

$header_location = ( @preg_match('/Microsoft|WebSTAR|Xitami/', getenv('SERVER_SOFTWARE')) ) ? 'Refresh: 0; URL=' : 'Location: ';

if ( isset($HTTP_POST_VARS['login']) || isset($HTTP_GET_VARS['login']) || isset($HTTP_POST_VARS['logout']) || isset($HTTP_GET_VARS['logout']) )
{
	//
	// This appears to work for IIS5 CGI under Win2K. Uses getenv
	// since this doesn't exist for ISAPI mode and therefore the 
	// normal Location redirector is used in preference
	//
	if ( ( isset($HTTP_POST_VARS['login']) || isset($HTTP_GET_VARS['login']) ) && !$userdata['session_logged_in'] )
	{
		$redirect = ( !empty($HTTP_POST_VARS['redirect']) ) ? $HTTP_POST_VARS['redirect'] : 'index.'.$phpEx;

		$username = ( isset($HTTP_POST_VARS['username']) ) ? $HTTP_POST_VARS['username'] : '';
		$password = ( isset($HTTP_POST_VARS['password']) ) ? $HTTP_POST_VARS['password'] : '';

		$sql = "SELECT user_id, username, user_email, user_password, user_active  
			FROM " . USERS_TABLE . "
			WHERE username = '" . str_replace("\'", "''", $username) . "'";
		$result = $db->sql_query($sql);

		if ( $row = $db->sql_fetchrow($result) )
		{
			if ( $row['user_level'] != ADMIN && $board_config['board_disable'] )
			{
				header($header_location . "index.$phpEx$SID");
				exit;
			}
			else
			{
				if ( md5($password) == $row['user_password'] && $row['user_active'] )
				{
					$autologin = ( isset($HTTP_POST_VARS['autologin']) ) ? md5($password) : '';
					$this_page = ( !empty($HTTP_SERVER_VARS['PHP_SELF']) ) ? $HTTP_SERVER_VARS['PHP_SELF'] : $HTTP_ENV_VARS['PHP_SELF'];
					$this_page .= '&' . ( ( !empty($HTTP_SERVER_VARS['QUERY_STRING']) ) ? $HTTP_SERVER_VARS['QUERY_STRING'] : $HTTP_ENV_VARS['QUERY_STRING'] );
					$session_browser = ( !empty($HTTP_SERVER_VARS['HTTP_USER_AGENT']) ) ? $HTTP_SERVER_VARS['HTTP_USER_AGENT'] : $HTTP_ENV_VARS['HTTP_USER_AGENT'];

					$userdata = $session->create($userdata['session_id'], $row['user_id'], $autologin, $this_page, $session_browser);

					header($header_location . $redirect . $SID);
					exit;
				}
				else
				{
					$template->assign_vars(array(
						'META' => '<meta http-equiv="refresh" content="3;url=' . "login.$phpEx$SID&amp;redirect=$redirect" . '">')
					);

					$message = $lang['Error_login'] . '<br /><br />' . sprintf($lang['Click_return_login'], '<a href="' . "login.$phpEx$SID&amp;redirect=$redirect" . '">', '</a>') . '<br /><br />' .  sprintf($lang['Click_return_index'], '<a href="' . "index.$phpEx$SID" . '">', '</a>');
					message_die(MESSAGE, $message);
				}
			}
		}
		else
		{
			$template->assign_vars(array(
				'META' => '<meta http-equiv="refresh" content="3;url=' . "login.$phpEx$SID&amp;redirect=$redirect" . '">')
			);

			$message = $lang['Error_login'] . '<br /><br />' . sprintf($lang['Click_return_login'], '<a href="' . "login.$phpEx$SID&amp;redirect=$redirect" . '">', '</a>') . '<br /><br />' .  sprintf($lang['Click_return_index'], '<a href="' . "index.$phpEx$SID" . '">', '</a>');
			message_die(MESSAGE, $message);
		}
	}
	else if ( ( isset($HTTP_GET_VARS['logout']) || isset($HTTP_POST_VARS['logout']) ) && $userdata['user_id'] != ANONYMOUS )
	{
		$session->destroy($userdata);
	}

	header($header_location . $redirect . $SID);
	exit;
}
else
{
	//
	// Do a full login page dohickey if
	// user not already logged in
	//
	if ( $userdata['user_id'] == ANONYMOUS )
	{
		if ( isset($HTTP_POST_VARS['redirect']) || isset($HTTP_GET_VARS['redirect']) )
		{
			$forward_to = $HTTP_SERVER_VARS['QUERY_STRING'];

			if ( preg_match('/^redirect=(.*)$/si', $forward_to, $forward_matches) )
			{
				$forward_to = ( !empty($forward_matches[3]) ) ? $forward_matches[3] : $forward_matches[1];

				$forward_match = explode('&', $forward_to);

				if ( count($forward_match) > 1 )
				{
					$forward_page = '';

					for($i = 1; $i < count($forward_match); $i++)
					{
						if ( !ereg('sid=', $forward_match[$i]) )
						{
							if ( $forward_page != '' )
							{
								$forward_page .= '&';
							}
							$forward_page .= $forward_match[$i];
						}
					}

					$forward_page = $forward_match[0] . '?' . $forward_page;
				}
				else
				{
					$forward_page = $forward_match[0];
				}
			}
		}
		else
		{
			$forward_page = '';
		}

		$template->assign_vars(array(
			'USERNAME' => ( $userdata['user_id'] != ANONYMOUS ) ? $userdata['username'] : '',

			'L_ENTER_PASSWORD' => $lang['Enter_password'], 
			'L_SEND_PASSWORD' => $lang['Forgotten_password'],

			'U_SEND_PASSWORD' => "profile.$phpEx$SID&amp;mode=sendpassword", 
			
			'S_HIDDEN_FIELDS' => '<input type="hidden" name="redirect" value="' . $forward_page . '" />')
		);

		$page_title = $lang['Login'];
		include($phpbb_root_path . 'includes/page_header.'.$phpEx);

		$template->set_filenames(array(
			'body' => 'login_body.html')
		);
		make_jumpbox('viewforum.'.$phpEx, $forum_id);

		include($phpbb_root_path . 'includes/page_tail.'.$phpEx);
	}
	else
	{
		header($header_location . "index.$phpEx$SID");
		exit;
	}

}

?>