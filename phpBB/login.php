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
if ( isset($login) || isset($logout)  )
{
	if ( isset($login) && !$user->data['user_id'] )
	{
		$autologin = ( !empty($autologin) ) ? true : false;

		//
		// Is the board disabled? Are we an admin? No, then back to the index we go
		//
		if ( $config['board_disable'] && !$auth->acl_get('a_') )
		{
			redirect("index.$phpEx$SID");
		}

		if ( !$auth->login($username, $password, $autologin) )
		{
			$template->assign_vars(array(
				'META' => '<meta http-equiv="refresh" content="3;url=' . "login.$phpEx$SID&amp;redirect=$redirect" . '">')
			);

			$message = $user->lang['Error_login'] . '<br /><br />' . sprintf($user->lang['Click_return_login'], '<a href="' . "login.$phpEx$SID&amp;redirect=$redirect" . '">', '</a>') . '<br /><br />' .  sprintf($user->lang['Click_return_index'], '<a href="' . "index.$phpEx$SID" . '">', '</a>');
			message_die(MESSAGE, $message);
		}
	}
	else if ( $user->data['user_id'] )
	{
		$user->destroy();
	}

	//
	// Redirect to wherever we're supposed to go ...
	//
	$redirect_url = ( $redirect ) ? preg_replace('/^.*?redirect=(.*?)&(.*?)$/', '\\1' . $SID . '&\\2', $redirect) : 'index.'.$phpEx;
	redirect($redirect_url);
}

if ( !$user->data['user_id'] )
{
	$template->assign_vars(array(
		'L_ENTER_PASSWORD'	=> $user->lang['Enter_password'],
		'L_SEND_PASSWORD' 	=> $user->lang['Forgotten_password'],

		'U_SEND_PASSWORD' 	=> "profile.$phpEx$SID&amp;mode=sendpassword",

		'S_HIDDEN_FIELDS' 	=> '<input type="hidden" name="redirect" value="' . $redirect . '" />')
	);

	$page_title = $user->lang['Login'];
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