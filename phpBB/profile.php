<?php
/***************************************************************************
 *                                profile.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id: profile.php,v 1.1 2010/10/10 15:01:18 orynider Exp $
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


if ((isset($_GET['mode']) && ($_GET['mode'] == 'viewprofile')) || (isset($_POST['mode']) && ($_POST['mode'] == 'viewprofile')))
{
	//
	
	
	//
	// Added to optimize memory for attachments
	define('ATTACH_PROFILE', true);
	define('ATTACH_POSTING', true);
}
else
{
	//
}
define("IN_PROFILE", true);
define("IN_UCP", true);

define('IN_PHPBB', true);
$phpbb_root_path = './';
include($phpbb_root_path . 'extension.inc');
include_once($phpbb_root_path . 'common.'.$phpEx);
// Adding CPL_NAV only if needed
define('PARSE_CPL_NAV', true);
//
// Start session management
//
$userdata = session_pagestart($user_ip, PAGE_PROFILE);
init_userprefs($userdata);
//
// End session management
//

$meta_content['page_title'] = $lang['Profile'];
$meta_content['description'] = '';
$meta_content['keywords'] = '';











//
// Set default email variables
//
$script_name = preg_replace('/^\/?(.*?)\/?$/', '\1', trim($board_config['script_path']));
$script_name = ( $script_name != '' ) ? $script_name . '/profile.'.$phpEx : 'profile.'.$phpEx;
$server_name = trim($board_config['server_name']);
$server_protocol = ( $board_config['cookie_secure'] ) ? 'https://' : 'http://';
$server_port = ( $board_config['server_port'] <> 80 ) ? ':' . trim($board_config['server_port']) . '/' : '/';

$server_url = $server_protocol . $server_name . $server_port . $script_name;

// session id check
$sid = request_var('sid', '');
$mode = request_var('mode', '');

// -----------------------
// Page specific functions
//
if (!function_exists('gen_rand_string'))
{
	function gen_rand_string($hash)
	{
		$rand_str = dss_rand();

		return ( $hash ) ? md5($rand_str) : substr($rand_str, 0, 8);
	}
}
//
// End page specific functions
// ---------------------------

//
// Start of program proper
//
if ( !is_empty_get('mode') || !is_empty_post('mode') )
{
	$mode = ( !empty($mode) ) ? $mode : request_get_var('mode', '');
	$mode = htmlspecialchars($mode);

	if ($mode != 'viewprofile')
	{
		include_once($phpbb_root_path . 'includes/users_zebra_block.' . $phpEx);
	}

	if ( $mode == 'viewprofile' )
	{
		$page_id = 'profile';
		
		

		//check_page_auth($page_id, $userdata['user_level']);

		include_once($phpbb_root_path . 'includes/usercp_viewprofile.'.$phpEx);
		exit;
	}
	elseif ( $mode == 'editprofile' || $mode == 'register' )
	{
		if ( !$userdata['session_logged_in'] && $mode == 'editprofile' )
		{
			redirect(append_sid("login.$phpEx?redirect=profile.$phpEx&mode=editprofile", true));
			//redirect(append_sid("login.$phpEx?redirect=profile.$phpEx&mode=editprofile&cpl_mode=reg_info", true));		
		}
		include_once($phpbb_root_path . 'includes/usercp_register.'.$phpEx);
		exit;
	}
	elseif ($mode == 'signature')
	{
		if (!$user->data['session_logged_in'] && ($mode == 'signature'))
		{
			$header_location = (@preg_match("/Microsoft|WebSTAR|Xitami/", getenv("SERVER_SOFTWARE"))) ? "Refresh: 0; URL=" : "Location: ";
			header($header_location . append_sid("login".$phpEx."?redirect=profile".$phpEx."&mode=signature", true));
			exit;
		}

		include($phpbb_root_path . 'includes/usercp_signature.'.$phpEx);
		exit;
	}
	elseif ( $mode == 'confirm' )
	{
		// Visual Confirmation
		$force_captcha = request_var('force_captcha', 0);
		if (empty($force_captcha) && $user->data['session_logged_in'] && ($_GET['confirm_id'] != 'Admin'))
		{
			exit;
		}
		include_once($phpbb_root_path . 'includes/usercp_confirm.'.$phpEx);
		exit;
	}
	elseif ( $mode == 'sendpassword' )
	{
		include_once($phpbb_root_path . 'includes/usercp_sendpasswd.'.$phpEx);
		exit;
	}
	elseif ( $mode == 'activate' )
	{
		include_once($phpbb_root_path . 'includes/usercp_activate.'.$phpEx);
		exit;
	}
	elseif ($mode == 'resend')
	{
		include($phpbb_root_path . 'includes/usercp_resend.' . $phpEx);
		exit;
	}
	elseif ( $mode == 'email' )
	{
		include_once($phpbb_root_path . 'includes/usercp_email.'.$phpEx);
		exit;
	}
	elseif ($mode == 'zebra')
	{
		include($phpbb_root_path . 'includes/usercp_zebra.' . $phpEx);
		exit;
	}
}

redirect(append_sid("index.$phpEx", true));

?>