<?php
/***************************************************************************
 *                                ucp.php
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

// TODO for 2.2:
//
// * Registration
//    * Admin defineable use of COPPA
//    * Link to (additional?) registration conditions
//    * Form based click through rather than links
//    * Inform user of registration method i.e. if a valid email is required
//    * Admin defineable characters allowed in usernames?
//    * Admin forced revalidation of given user/s from ACP
//    * Simple registration (option or always?), i.e. username, email address, password
// * Tab based control panel
// * Modular/plug-in approach
// * Opening tab:
//    * Last visit time
//    * Last active in
//    * Most active in
//    * Current Karma
//    * New PM counter
//    * Unread PM counter
//    * Subscribed forum and topic lists + unsubscribe option, etc.
//    * (Unread?) Global announcements?
//    * Link/s to MCP if applicable?
// * Black and White lists
//    * Add buddy/ignored user
//    * Group buddies/ignored users?
//    * Mark posts/PM's of buddies different colour?
// * Preferences
//    * Username
//    * email address/es
//    * password
//    * Various flags
// * Profile
//    * As required
// * PM system
//    * See privmsg
// * Avatars
//    * as current but with definable width/height box?
// * Permissions?
//    * List permissions granted to this user (in UCP and ACP UCP)

define('IN_PHPBB', true);
$phpbb_root_path = './';
include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'common.'.$phpEx);

// Start session management
$user->start();
$user->setup();
$auth->acl($user->data);
// End session management

// Set default email variables
$script_name = preg_replace('/^\/?(.*?)\/?$/', '\1', trim($config['script_path']));
$script_name = ( $script_name != '' ) ? $script_name . '/ucp.'.$phpEx : 'ucp.'.$phpEx;
$server_name = trim($config['server_name']);
$server_protocol = ( $config['cookie_secure'] ) ? 'https://' : 'http://';
$server_port = ( $config['server_port'] <> 80 ) ? ':' . trim($config['server_port']) . '/' : '/';

$server_url = $server_protocol . $server_name . $server_port . $script_name;

// -----------------------
// Page specific functions
//
function gen_rand_string($hash)
{
	$chars = array( 'a', 'A', 'b', 'B', 'c', 'C', 'd', 'D', 'e', 'E', 'f', 'F', 'g', 'G', 'h', 'H', 'i', 'I', 'j', 'J',  'k', 'K', 'l', 'L', 'm', 'M', 'n', 'N', 'o', 'O', 'p', 'P', 'q', 'Q', 'r', 'R', 's', 'S', 't', 'T',  'u', 'U', 'v', 'V', 'w', 'W', 'x', 'X', 'y', 'Y', 'z', 'Z', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0');

	$max_chars = count($chars) - 1;
	srand( (double) microtime()*1000000);

	$rand_str = '';
	for($i = 0; $i < 8; $i++)
	{
		$rand_str = ( $i == 0 ) ? $chars[rand(0, $max_chars)] : $rand_str . $chars[rand(0, $max_chars)];
	}

	return ( $hash ) ? md5($rand_str) : $rand_str;
}
//
// End page specific functions
// ---------------------------

//
// Start of program proper
//
if ( isset($_GET['mode']) || isset($_POST['mode']) )
{
	$mode = ( isset($_GET['mode']) ) ? $_GET['mode'] : $_POST['mode'];

	if ( $mode == 'viewprofile' )
	{
		include($phpbb_root_path . 'includes/usercp_viewucp.'.$phpEx);
		exit;
	}
	else if ( $mode == 'editprofile' || $mode == 'register' )
	{
		if ( !$user->data['user_id'] && $mode == 'editprofile' )
		{
			redirect("login.$phpEx$SID&redirect=ucp.$phpEx&mode=editprofile");
		}
		else if ( $user->data['user_id'] && $mode == 'register' )
		{
			redirect("index.$phpEx$SID");
		}

		include($phpbb_root_path . 'includes/usercp_register.'.$phpEx);
		exit;
	}
	else if ( $mode == 'sendpassword' )
	{
		include($phpbb_root_path . 'includes/usercp_sendpasswd.'.$phpEx);
		exit;
	}
	else if ( $mode == 'activate' )
	{
		include($phpbb_root_path . 'includes/usercp_activate.'.$phpEx);
		exit;
	}
	else if ( $mode == 'email' )
	{
		include($phpbb_root_path . 'includes/usercp_email.'.$phpEx);
		exit;
	}
}
else
{
	redirect("index.$phpEx$SID");
}

?>