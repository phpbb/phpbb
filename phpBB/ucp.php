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
	function normalise_data(&$data, &$normalise)
	{
		$valid_data = array();
		foreach ($normalise as $var_type => $var_ary)
		{
			foreach ($var_ary as $var_name => $var_limits)
			{
				$var_name = (is_string($var_name)) ? $var_name : $var_limits; 

				if (isset($data[$var_name]))
				{
					switch ($var_type)
					{
						case 'int':
							$valid_data[$var_name] = (int) $data[$var_name];
							break;

						case 'float':
							$valid_data[$var_name] = (double) $data[$var_name];
							break;

						case 'bool':
							$valid_data[$var_name] = ($data[$var_name] <= 0) ? 0 : 1;
							break;

						case 'string':
							// Cleanup data, remove excess spaces, run entites
							$valid_data[$var_name] = htmlentities(trim(preg_replace('#\s{2,}#s', ' ', strtr((string) $data[$var_name], array_flip(get_html_translation_table(HTML_ENTITIES))))));

							// How should we check this data?
							if (!is_array($var_limits))
							{
								// Is the match a string? If it is, process it further, else we'll
								// assume it's a maximum length
								if (is_string($var_limits))
								{
									if (strstr($var_limits, ','))
									{
										list($min_value, $max_value) = explode(',', $var_limits);
										if (!empty($valid_data[$var_name]) && strlen($valid_data[$var_name]) < $min_value)
										{
											$this->error[] = strtoupper($var_name) . '_TOO_SHORT';
										}

										if (strlen($valid_data[$var_name]) > $max_value)
										{
											$this->error[] = strtoupper($var_name) . '_TOO_LONG';
										}
									}
								}
								else
								{
									if (strlen($valid_data[$var_name]) > $var_limits)
									{
										$this->error[] = strtoupper($var_name) . '_TOO_LONG';
									}
								}
							}
							break;
					}
				}
			}
		}

		return $valid_data;
	}

	// Validates data subject to supplied requirements, errors appropriately
	function validate_data(&$data, &$validate)
	{
		global $db, $user, $config;

		foreach ($validate as $operation => $var_ary)
		{
			foreach ($var_ary as $var_name => $compare)
			{
				if (!empty($compare))
				{
					switch ($operation)
					{
						case 'match':
							if (is_array($compare))
							{
								foreach ($compare as $match)
								{
									if (!preg_match($match, $data[$var_name]))
									{
										$this->error[] = strtoupper($var_name) . '_WRONG_DATA';
									}
								}
							}
							else if (!preg_match($compare, $data[$var_name]))
							{
								$this->error[] = strtoupper($var_name) . '_WRONG_DATA';
							}
							break;

						case 'compare':
							if (is_array($compare))
							{
								if (!in_array($data[$var_name], $compare))
								{
									$this->error[] = strtoupper($var_name) . '_MISMATCH';
								}
							}
							else if ($data[$var_name] != $compare)
							{
								$this->error[] = strtoupper($var_name) . '_MISMATCH';
							}
							break;

						case 'function':
							if ($result = $compare($data[$var_name]))
							{
								$this->error[] = $result;
							}

							break;

						case 'reqd':
							if (!isset($data[$compare]) || (is_string($data[$compare]) && $data[$compare] === ''))
							{
								$this->error[] = strtoupper($compare) . '_MISSING_DATA';
							}
							break;
					}
				}
			}
		}
	}
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