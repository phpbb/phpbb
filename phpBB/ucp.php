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


//
$mode = (!empty($_REQUEST['mode'])) ? htmlspecialchars($_REQUEST['mode']) : '';
$module = (!empty($_REQUEST['i'])) ? $_REQUEST['i'] : 1;


//
$ucp = new ucp();


//
switch ($mode)
{
	case 'activate':
		$ucp->module('activate');
		$ucp->modules['activate']->main();
		break;

	case 'register':
		if ($user->data['user_id'] != ANONYMOUS)
		{
			redirect("index.$phpEx$SID");
		}

		$ucp->module('register');
		$ucp->modules['register']->main();
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
	$ucp->module($selected_module);
	$ucp->modules[$selected_module]->main($selected_id);
}







// A wrapper class for ucp modules?
class ucp
{
	var $modules = array();

	function module($module_name)
	{
		if (!class_exists('ucp_' . $module_name))
		{
			$this->loadfile('ucp/ucp_' . $module_name);
			eval('$this->modules[' . $module_name . '] = new ucp_' . $module_name . '();');
		}
	}

	function loadfile($filename)
	{
		global $phpbb_root_path, $phpEx;

		return require($phpbb_root_path . $filename . '.' . $phpEx);
	}

	function main($module_id = false)
	{
		return false;
	}

	function subsection(&$module_ary, &$selected_module)
	{
		global $template, $user, $phpEx, $SID;

		foreach($module_ary as $section_title => $module_link)
		{
			$template->assign_block_vars('ucp_subsection', array(
				'L_TITLE'	=> $user->lang['UCP_' . $section_title],

				'S_SELECTED'=> ($section_title == strtoupper($selected_module)) ? true : false, 

				'U_TITLE'	=> "ucp.$phpEx$SID&amp;$module_link")
			);
		}
	}

	function output(&$page_title, $tpl_name)
	{
		global $config, $db, $template, $phpEx;

		page_header($page_title);

		$template->set_filenames(array(
			'body' => $tpl_name)
		);
		make_jumpbox('viewforum.'.$phpEx);

		page_footer();
	}

	function extra_fields($page)
	{
		return false;
	}

	function gen_rand_string($num_chars)
	{
		$chars = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J',  'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T',  'U', 'V', 'W', 'X', 'Y', 'Z', '1', '2', '3', '4', '5', '6', '7', '8', '9');

		list($usec, $sec) = explode(' ', microtime()); 
		mt_srand($sec * $usec); 

		$max_chars = count($chars) - 1;
		$rand_str = '';
		for ($i = 0; $i < $num_chars; $i++)
		{
			$rand_str .= $chars[mt_rand(0, $max_chars)];
		}

		return $rand_str;
	}
}

?>