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

//
// Set page ID for session management
//
$userdata = $session->start();
$auth = new auth($userdata);

$session->configure($userdata);
//
// End session management
//

//
// This appears to work for IIS5 CGI under Win2K. Uses getenv since this doesn't exist for
// ISAPI mode and therefore the normal Location redirector is used in preference
//
$header_location = ( @preg_match('/Microsoft|WebSTAR|Xitami/', getenv('SERVER_SOFTWARE')) ) ? 'Refresh: 0; URL=' : 'Location: ';

extract($HTTP_GET_VARS);
extract($HTTP_POST_VARS);

$redirect = ( !empty($redirect) ) ? $HTTP_SERVER_VARS['QUERY_STRING'] : '';

//
// Do the login/logout/form/whatever
//
if ( isset($login) || isset($logout)  )
{
	if ( isset($login) && $userdata['user_id'] == ANONYMOUS )
	{
		$autologin = ( !empty($autologin) ) ? true : false;

		//
		// Is the board disabled? Are we an admin? No, then back to the index we go
		//
		if ( $board_config['board_disable'] && !$auth->get_acl_admin() )
		{
			header($header_location . "index.$phpEx$SID");
			exit;
		}

		if ( !$auth->login($username, $password, $autologin) )
		{
			$template->assign_vars(array(
				'META' => '<meta http-equiv="refresh" content="3;url=' . "login.$phpEx$SID&amp;redirect=$redirect" . '">')
			);

			$message = $lang['Error_login'] . '<br /><br />' . sprintf($lang['Click_return_login'], '<a href="' . "login.$phpEx$SID&amp;redirect=$redirect" . '">', '</a>') . '<br /><br />' .  sprintf($lang['Click_return_index'], '<a href="' . "index.$phpEx$SID" . '">', '</a>');
			message_die(MESSAGE, $message);
		}
	}
	else if ( $userdata['user_id'] != ANONYMOUS )
	{
		$session->destroy($userdata);
	}

	//
	// Redirect to wherever we're supposed to go ...
	//
	$redirect_url = ( $redirect ) ? preg_replace('/^.*?redirect=(.*?)&(.*?)$/', '\\1' . $SID . '&\\2', $redirect) : 'index.'.$phpEx;
	header($header_location . $redirect_url);
	exit;
}

if ( $userdata['user_id'] == ANONYMOUS )
{
	$template->assign_vars(array(
		'L_ENTER_PASSWORD' => $lang['Enter_password'],
		'L_SEND_PASSWORD' => $lang['Forgotten_password'],

		'U_SEND_PASSWORD' => "profile.$phpEx$SID&amp;mode=sendpassword",

		'S_HIDDEN_FIELDS' => '<input type="hidden" name="redirect" value="' . $redirect . '" />')
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

?>