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

// * Opening tab:
//    * Last visit time
//    * Last active in
//    * Most active in
//    * New PM counter
//    * Unread PM counter
//    * Link/s to MCP if applicable?

// * PM system
//    * See privmsg

// * Permissions?
//    * List permissions granted to this user (in UCP and ACP UCP)

define('IN_PHPBB', true);
$phpbb_root_path = './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);

// This small snippet is required to let admins login if the board is disabled...
if ($_REQUEST['mode'] == 'login')
{
	define('IN_LOGIN', true);
}
include($phpbb_root_path . 'common.'.$phpEx);
include($phpbb_root_path . '/includes/functions_user.'.$phpEx);

// ---------
// FUNCTIONS
//
class module
{
	var $id = 0;
	var $type;
	var $name;
	var $mode;

	// Private methods, should not be overwritten
	function create($module_type, $module_url, $selected_mod = false, $selected_submod = false)
	{
		global $template, $auth, $db, $user, $config;

		$sql = 'SELECT module_id, module_title, module_filename, module_subs, module_acl
			FROM ' . MODULES_TABLE . "
			WHERE module_type = '{$module_type}'
				AND module_enabled = 1
			ORDER BY module_order ASC";
		$result = $db->sql_query($sql);

		$i = 0;
		while ($row = $db->sql_fetchrow($result))
		{
			// Authorisation is required for the basic module
			if ($row['module_acl'])
			{
				$is_auth = false;
				eval('$is_auth = (' . preg_replace(array('#acl_([a-z_]+)#e', '#cfg_([a-z_]+)#e'), array('(int) $auth->acl_get("\\1")', '(int) $config["\\1"]'), trim($row['module_acl'])) . ');');

				// The user is not authorised to use this module, skip it
				if (!$is_auth)
				{
					continue;
				}
			}

			$selected = ($row['module_filename'] == $selected_mod || $row['module_id'] == $selected_mod || (!$selected_mod && !$i)) ?  true : false;

			// Get the localised lang string if available, or make up our own otherwise
			$module_lang = strtoupper($module_type) . '_' . $row['module_title'];
			$template->assign_block_vars($module_type . '_section', array(
				'L_TITLE'		=> (isset($user->lang[$module_lang])) ? $user->lang[$module_lang] : ucfirst(str_replace('_', ' ', strtolower($row['module_title']))),
				'S_SELECTED'	=> $selected, 
				'U_TITLE'		=> $module_url . '&amp;i=' . $row['module_id'])
			);

			if ($selected)
			{
				$module_id = $row['module_id'];
				$module_name = $row['module_filename'];

				if ($row['module_subs'])
				{
					$j = 0;
					$submodules_ary = explode("\n", $row['module_subs']);
					foreach ($submodules_ary as $submodule)
					{
						if (!trim($submodule))
						{
							continue;
						}

						$submodule = explode(',', trim($submodule));
						$submodule_title = array_shift($submodule);

						$is_auth = true;
						foreach ($submodule as $auth_option)
						{
							eval('$is_auth = (' . preg_replace(array('#acl_([a-z_]+)#e', '#cfg_([a-z_]+)#e'), array('(int) $auth->acl_get("\\1")', '(int) $config["\\1"]'), trim($auth_option)) . ');');

							if (!$is_auth)
							{
								break;
							}
						}

						if (!$is_auth)
						{
							continue;
						}

						$selected = ($submodule_title == $selected_submod || (!$selected_submod && !$j)) ? true : false;

						// Get the localised lang string if available, or make up our own otherwise
						$module_lang = strtoupper($module_type . '_' . $module_name . '_' . $submodule_title);

						$template->assign_block_vars("{$module_type}_section.{$module_type}_subsection", array(
							'L_TITLE'		=> (isset($user->lang[$module_lang])) ? $user->lang[$module_lang] : ucfirst(str_replace('_', ' ', strtolower($module_lang))),
							'S_SELECTED'	=> $selected, 
							'U_TITLE'		=> $module_url . '&amp;i=' . $module_id . '&amp;mode=' . $submodule_title
						));

						if ($selected)
						{
							$this->mode = $submodule_title;
						}

						$j++;
					}
				}
			}

			$i++;
		}
		$db->sql_freeresult($result);

		if (!$module_id)
		{
			trigger_error('MODULE_NOT_EXIST');
		}

		$this->type = $module_type;
		$this->id = $module_id;
		$this->name = $module_name;
	}

	function load($type = false, $name = false, $mode = false, $run = true)
	{
		global $phpbb_root_path, $phpEx;

		if ($type)
		{
			$this->type = $type;
		}

		if ($name)
		{
			$this->name = $name;
		}

		if (!class_exists($this->type . '_' . $this->name))
		{
			require_once($phpbb_root_path . "includes/{$this->type}/{$this->type}_{$this->name}.$phpEx");

			if ($run)
			{
				if (!isset($this->mode))
				{
					$this->mode = $mode;
				}

				eval("\$this->module = new {$this->type}_{$this->name}(\$this->id, \$this->mode);");
				if (method_exists($this->module, 'init'))
				{
					$this->module->init();
				}
			}
		}
	}

	// Displays the appropriate template with the given title
	function display($page_title, $tpl_name)
	{
		global $template;

		page_header($page_title);

		$template->set_filenames(array(
			'body' => $tpl_name)
		);

		page_footer();
	}


	// Public methods to be overwritten by modules
	function module()
	{
		// Module name
		// Module filename
		// Module description
		// Module version
		// Module compatibility
		return false;
	}

	function init()
	{
		return false;
	}

	function install()
	{
		return false;
	}

	function uninstall()
	{
		return false;
	}
}
//
// FUNCTIONS
// ---------


// Start session management
$user->start();
$auth->acl($user->data);
$user->setup('ucp');

$ucp = new module();

// Basic parameter data
$mode	= request_var('mode', '');
$module = request_var('i', '');

// Basic "global" modes
switch ($mode)
{
	case 'activate':
		$ucp->load('ucp', 'activate');
		$ucp->module->ucp_activate();
		redirect("index.$phpEx$SID");
		break;

	case 'sendpassword':
		$ucp->load('ucp', 'remind');
		$ucp->module->ucp_remind();
		break;

	case 'register':
		if ($user->data['user_id'] != ANONYMOUS || isset($_REQUEST['not_agreed']))
		{
			redirect("index.$phpEx$SID");
		}

		$ucp->load('ucp', 'register');
		$ucp->module->ucp_register();
		break;

	case 'confirm':
		$ucp->load('ucp', 'confirm');
		$ucp->module->ucp_confirm();
		break;

	case 'login':
		if ($user->data['user_id'] != ANONYMOUS)
		{
			redirect("index.$phpEx$SID");
		}

		login_box("ucp.$phpEx$SID&amp;mode=login", '', '', true);

		$redirect = request_var('redirect', "index.$phpEx$SID");
		meta_refresh(3, $redirect);

		$message = $user->lang['LOGIN_REDIRECT'] . '<br /><br />' . sprintf($user->lang['RETURN_PAGE'], '<a href="' . $redirect . '">', '</a> ');
		trigger_error($message);
		break;

	case 'logout':
		if ($user->data['user_id'] != ANONYMOUS)
		{
			$user->destroy();
		}

		$redirect = (!empty($_SERVER['HTTP_REFERER'])) ? htmlspecialchars($_SERVER['HTTP_REFERER']) : "index.$phpEx$SID";
		meta_refresh(3, $redirect);

		$message = $user->lang['LOGOUT_REDIRECT'] . '<br /><br />' . sprintf($user->lang['RETURN_PAGE'], '<a href="' . $redirect . '">', '</a> ');
		trigger_error($message);
		break;

	case 'delete_cookies':
		// Delete Cookies with dynamic names (do NOT delete poll cookies)
		if (confirm_box(true))
		{
			$set_time = time() - 31536000;
			foreach ($_COOKIE as $cookie_name => $cookie_data)
			{
				$cookie_name = str_replace($config['cookie_name'] . '_', '', $cookie_name);
				if (!strstr($cookie_name, '_poll'))
				{
					$user->set_cookie($cookie_name, '', $set_time);
				}
			}
			$user->set_cookie('track', '', $set_time);
			$user->set_cookie('data', '', $set_time);
			$user->set_cookie('sid', '', $set_time);

			// We destroy the session here, the user will be logged out nevertheless
			$user->destroy();

			meta_refresh(3, "{$phpbb_root_path}index.$phpEx$SID");

			$message = $user->lang['COOKIES_DELETED'] . '<br /><br />' . sprintf($user->lang['RETURN_INDEX'], "<a href=\"{$phpbb_root_path}index.$phpEx$SID\">", '</a>');
			trigger_error($message);
		}
		else
		{
			confirm_box(false, 'DELETE_COOKIES', '');
		}
		redirect("index.$phpEx$SID");
		break;
}


// Only registered users can go beyond this point
if ($user->data['user_id'] == ANONYMOUS || $user->data['user_type'] == USER_INACTIVE || $user->data['user_type'] == USER_IGNORE)
{
	if ($user->data['user_id'] != ANONYMOUS)
	{
		redirect("index.$phpEx$SID");
	}
	
	login_box($user->cur_page, '', $user->lang['LOGIN_EXPLAIN_UCP']);
}


// Output listing of friends online
$update_time = $config['load_online_time'] * 60;

$sql = 'SELECT DISTINCT u.user_id, u.username, MAX(s.session_time) as online_time, MIN(s.session_allow_viewonline) AS viewonline 
	FROM ((' . ZEBRA_TABLE . ' z 
	LEFT JOIN ' . SESSIONS_TABLE . ' s ON s.session_user_id = z.zebra_id), ' . USERS_TABLE . ' u)
	WHERE z.user_id = ' . $user->data['user_id'] . ' 
		AND z.friend = 1 
		AND u.user_id = z.zebra_id  
	GROUP BY z.zebra_id';
$result = $db->sql_query($sql);

while ($row = $db->sql_fetchrow($result))
{
	$which = (time() - $update_time < $row['online_time']) ? 'online' : 'offline';

	$template->assign_block_vars("friends_{$which}", array(
		'U_PROFILE'	=> "memberlist.$phpEx$SID&amp;mode=viewprofile&amp;u=" . $row['user_id'],
		
		'USER_ID'	=> $row['user_id'],
		'USERNAME'	=> $row['username'])
	);
}
$db->sql_freeresult($result);

// Output PM_TO box if message composing
if ($mode == 'compose' && request_var('action', '') != 'edit')
{
	if ($config['allow_mass_pm'])
	{
		$sql = 'SELECT group_id, group_name, group_type   
			FROM ' . GROUPS_TABLE . ' 
			WHERE group_type NOT IN (' . GROUP_HIDDEN . ', ' . GROUP_CLOSED . ')
				AND group_receive_pm = 1
			ORDER BY group_type DESC';
		$result = $db->sql_query($sql);

		$group_options = '';
		while ($row = $db->sql_fetchrow($result))
		{
			$group_options .= '<option' . (($row['group_type'] == GROUP_SPECIAL) ? ' class="blue"' : '') . ' value="' . $row['group_id'] . '">' . (($row['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $row['group_name']] : $row['group_name']) . '</option>';
		}
		$db->sql_freeresult($result);
	}

	$template->assign_vars(array(
		'S_SHOW_PM_BOX'		=> true,
		'S_ALLOW_MASS_PM'	=> ($config['allow_mass_pm']),
		'S_GROUP_OPTIONS'	=> ($config['allow_mass_pm']) ? $group_options : '',
		'U_SEARCH_USER'		=> "{$phpbb_root_path}memberlist.$phpEx$SID&amp;mode=searchuser&amp;form=post&amp;field=username_list")
	);
}

// Instantiate module system and generate list of available modules
$ucp->create('ucp', "ucp.$phpEx$SID", $module, $mode);

// Load and execute the relevant module
$ucp->load();

?>