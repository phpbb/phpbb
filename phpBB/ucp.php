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
//    * Link to (additional?) registration conditions
//    * Admin defineable characters allowed in usernames?
//    * Admin forced revalidation of given user/s from ACP
//    * Simple registration (option or always?), i.e. username, email address, password

// * Opening tab:
//    * Last visit time
//    * Last active in
//    * Most active in
//    * Current Karma
//    * New PM counter
//    * Unread PM counter
//    * Link/s to MCP if applicable?

// * Black and White lists
//    * Add buddy/ignored user
//    * Group buddies/ignored users?
//    * Mark posts/PM's of buddies different colour?

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
include($phpbb_root_path . '/includes/functions_user.'.$phpEx);


// Start session management
$user->start();
$user->setup();
$auth->acl($user->data);


// Basic parameter data
$mode = (!empty($_REQUEST['mode'])) ? htmlspecialchars($_REQUEST['mode']) : '';
$module = (!empty($_REQUEST['i'])) ? intval($_REQUEST['i']) : 1;


// Instantiate a new ucp object
$ucp = new ucp();


// Basic "global" modes
switch ($mode)
{
	case 'activate':
		$ucp->module('activate');
		$ucp->modules['activate']->main();
		break;

	case 'remind':
		$ucp->module('remind');
		$ucp->modules['remind']->main();
		break;


	case 'register':
		if ($user->data['user_id'] != ANONYMOUS)
		{
			redirect("index.$phpEx$SID");
		}

		$ucp->module('register');
		$ucp->modules['register']->main();
		break;

	case 'confirm':
		$ucp->module('confirm');
		$ucp->modules['confirm']->main();
		break;

	case 'login':
		if ($user->data['user_id'] != ANONYMOUS)
		{
			redirect("index.$phpEx$SID");
		}

		define('IN_LOGIN', true);
		login_box("ucp.$phpEx$SID&amp;mode=login");
		redirect("index.$phpEx$SID");
		break;

	case 'logout':
		if ($user->data['user_id'] != ANONYMOUS)
		{
			$user->destroy();
		}

		redirect("index.$phpEx$SID");
		break;
}


// Only registered users can go beyond this point
if ($user->data['user_id'] == ANONYMOUS)
{
	redirect("index.$phpEx");
}


// Word censors $censors['match'] & $censors['replace']
$censors = array();
obtain_word_list($censors);


// Grab the other enabled UCP modules
$sql = "SELECT module_id, module_name, module_filename 
	FROM " . UCP_MODULES_TABLE . " 
	ORDER BY module_order ASC";
$result = $db->sql_query($sql);

while ($row = $db->sql_fetchrow($result))
{
	$template->assign_block_vars('ucp_sections', array(
		'SECTION'	=> $user->lang['UCP_' . $row['module_name']], 

		'U_SECTION'	=> "ucp.$phpEx$SID&amp;i=" . $row['module_id'],

		'S_IS_TAB'	=> ($row['module_id'] == $module) ? true : false)
	);
	
	if ($row['module_id'] == $module)
	{
		$selected_module = $row['module_filename'];
		$selected_id = $row['module_id'];
	}
}
$db->sql_freeresult($result);

if ($selected_module)
{
	$ucp->load_module($selected_module);
	$ucp->module[$selected_module]->main($selected_id);
}

exit;

?>