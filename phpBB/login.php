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
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

define('IN_LOGIN', true);
define('IN_PHPBB', true);

$phpbb_root_path = './';
include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'common.'.$phpEx);

// Set page ID for session management
$user->start();
$user->setup();
$auth->acl($user->data);
// End session management

extract($_GET);
extract($_POST);

$redirect = (!empty($redirect)) ? $_SERVER['QUERY_STRING'] : '';

// Do the login/logout/form/whatever
if (isset($login) || isset($logout))
{
	if (isset($login) && $user->data['user_id'] == ANONYMOUS)
	{
		$autologin = (!empty($autologin)) ? true : false;
		$viewonline = (!empty($viewonline)) ? 0 : 1;

		// Is the board disabled? Are we an admin? No, then back to the index we go
		if (!empty($config['board_disable']) && !$auth->acl_get('a_'))
		{
			redirect("index.$phpEx$SID");
		}

		if (($result = $auth->login($username, $password, $autologin, $viewonline)) !== true)
		{
			// If we get a non-numeric (e.g. string) value we output an error
			if (!is_numeric($result))
			{
				trigger_error($result, E_USER_ERROR);
			}

			// If we get an integer zero then we are inactive, else the username/password is wrong
			$message = ($result === 0) ? $user->lang['ACTIVE_ERROR'] :  $user->lang['LOGIN_ERROR'];
			$message .=  '<br /><br />' . sprintf($user->lang['RETURN_LOGIN'], '<a href="' . "login.$phpEx$SID&amp;redirect=$redirect" . '">', '</a>') . '<br /><br />' .  sprintf($user->lang['RETURN_INDEX'], '<a href="' . "index.$phpEx$SID" . '">', '</a>');

			trigger_error($message);
		}
	}
	else if ($user->data['user_id'] != ANONYMOUS)
	{
		$user->destroy();
	}

	// Redirect to wherever we're supposed to go ...
	$redirect_url = ($redirect) ? preg_replace('#^.*?redirect=(.*?)&(.*?)$#', '\1' . $SID . '&\2', $redirect) : 'index.'.$phpEx;
	redirect($redirect_url);
}

if ($user->data['user_id'] == ANONYMOUS)
{
	$template->assign_vars(array(
		'U_SEND_PASSWORD' 	=> "ucp.$phpEx$SID&amp;mode=sendpassword",
		'U_TERMS_USE'		=> "ucp.$phpEx$SID&amp;mode=terms", 
		'U_PRIVACY'			=> "ucp.$phpEx$SID&amp;mode=privacy", 

		'S_HIDDEN_FIELDS' 	=> '<input type="hidden" name="redirect" value="' . $redirect . '" />')
	);

	$page_title = $user->lang['LOGIN'];
	include($phpbb_root_path . 'includes/page_header.'.$phpEx);

	$template->set_filenames(array(
		'body' => 'login_body.html')
	);
	make_jumpbox('viewforum.'.$phpEx, $forum_id);

	include($phpbb_root_path . 'includes/page_tail.'.$phpEx);
}
else
{
	redirect("index.$phpEx$SID");
}

?>