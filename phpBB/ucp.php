<?php 
// -------------------------------------------------------------
//
// $Id$
//
// FILENAME  : bbcode.php 
// STARTED   : Thu Nov 21, 2002
// COPYRIGHT : © 2001, 2003 phpBB Group
// WWW       : http://www.phpbb.com/
// LICENCE   : GPL vs2.0 [ see /docs/COPYING ] 
// 
// -------------------------------------------------------------

// TODO for 2.2:
//
// * Registration
//    * Link to (additional?) registration conditions
//    * Admin forced revalidation of given user/s from ACP

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

// * Permissions?
//    * List permissions granted to this user (in UCP and ACP UCP)

define('IN_PHPBB', true);
$phpbb_root_path = './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.'.$phpEx);
include($phpbb_root_path . '/includes/functions_user.'.$phpEx);


// ---------
// FUNCTIONS
//

// Handles manipulation of user data. Primary used in registration
// and user profile manipulation
class ucp extends user
{
	var $modules = array();
	var $error = array();

	// Loads a given module (if it isn't already available), instantiates
	// a new object, and where appropriate calls the modules init method
	function load_module($module_name)
	{
		if (!class_exists('ucp_' . $module_name))
		{
			global $phpbb_root_path, $phpEx;

			require_once($phpbb_root_path . 'includes/ucp/ucp_' . $module_name . '.'.$phpEx);
			eval('$this->module = new ucp_' . $module_name . '();');

			if (method_exists($this->module, 'init'))
			{
				$this->module->init();
			}
		}
	}

	// This is replaced by the loaded module
	function main($module_id = false)
	{
		return false;
	}

	// This generates the block template variable for outputting the list
	// of submodules, should be called with an associative array of modules
	// in the form 'LANG_STRING' => 'LINK'
	function menu(&$id, &$module_ary, &$selected_module)
	{
		global $template, $user, $phpEx, $SID, $s_modules;

		foreach ($s_modules as $module_id => $section_data)
		{
			$template->assign_block_vars('ucp_section', array(
				'L_TITLE'	=> $section_data['title'],

				'S_SELECTED'=> $section_data['selected'], 

				'U_TITLE'	=> $section_data['url'])
			);

			if ($module_id == $id)
			{
				foreach ($module_ary as $section_title => $module_link)
				{
					$template->assign_block_vars('ucp_section.ucp_subsection', array(
						'L_TITLE'	=> $user->lang['UCP_' . $section_title],

						'S_SELECTED'=> ($section_title == strtoupper($selected_module)) ? true : false, 

						'U_TITLE'	=> "ucp.$phpEx$SID&amp;$module_link")
					);
				}
			}
		}

		foreach ($module_ary as $section_title => $module_link)
		{
			$template->assign_block_vars('ucp_subsection', array(
				'L_TITLE'	=> $user->lang['UCP_' . $section_title],

				'S_SELECTED'=> ($section_title == strtoupper($selected_module)) ? true : false, 

				'U_TITLE'	=> "ucp.$phpEx$SID&amp;$module_link")
			);
		}
	}

	// Displays the appropriate template with the given title
	function display(&$page_title, $tpl_name)
	{
		global $template, $phpEx;

		page_header($page_title);

		$template->set_filenames(array(
			'body' => $tpl_name)
		);
		make_jumpbox('viewforum.'.$phpEx);

		page_footer();
	}

	// Normalises supplied data dependant on required type/length, errors
	// on incorrect data
}
//
// FUNCTIONS
// ---------


// Start session management
$user->start();
$auth->acl($user->data);
$user->setup();

// Basic parameter data
$mode = (!empty($_REQUEST['mode'])) ? htmlspecialchars($_REQUEST['mode']) : '';
$module = (!empty($_REQUEST['i'])) ? intval($_REQUEST['i']) : 1;


// Instantiate a new ucp object
$ucp = new ucp();


// Basic "global" modes
switch ($mode)
{
	case 'activate':
		$ucp->load_module('activate');
		$ucp->module->main();
		break;

	case 'remind':
		$ucp->load_module('remind');
		$ucp->module->main();
		break;

	case 'register':
		if ($user->data['user_id'] != ANONYMOUS)
		{
			redirect("index.$phpEx$SID");
		}

		$ucp->load_module('register');
		$ucp->module->main();
		break;

	case 'confirm':
		$ucp->load_module('confirm');
		$ucp->module->main();
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
$sql = 'SELECT module_id, module_title, module_filename 
	FROM ' . UCP_MODULES_TABLE . ' 
	ORDER BY module_order ASC';
$result = $db->sql_query($sql);

$s_modules = array();
while ($row = $db->sql_fetchrow($result))
{
	$template->assign_block_vars('ucp_sections', array(
		'SECTION'	=> $user->lang['UCP_' . $row['module_title']], 

		'U_SECTION'	=> "ucp.$phpEx$SID&amp;i=" . $row['module_id'],

		'S_IS_TAB'	=> ($row['module_id'] == $module) ? true : false)
	);

	$s_modules[$row['module_id']]['title'] = $user->lang['UCP_' . $row['module_title']];
	$s_modules[$row['module_id']]['url'] = "ucp.$phpEx$SID&amp;i=" . $row['module_id'];
	$s_modules[$row['module_id']]['selected'] = ($row['module_id'] == $module) ? true : false;

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
	$ucp->module->main($selected_id);
}

?>