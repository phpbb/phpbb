<?php
// -------------------------------------------------------------
//
// $Id$
//
// FILENAME  : mcp.php 
// STARTED   : Mon May 5, 2003
// COPYRIGHT : © 2001, 2003 phpBB Group
// WWW       : http://www.phpbb.com/
// LICENCE   : GPL vs2.0 [ see /docs/COPYING ] 
// 
// -------------------------------------------------------------

define('IN_PHPBB', true);
$phpbb_root_path = './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.'.$phpEx);
include($phpbb_root_path . 'includes/functions_admin.'.$phpEx);

// ---------
// FUNCTIONS
//
class module
{
	var $id = 0;
	var $type;
	var $name;
	var $mode;
	var $url;

	// Private methods, should not be overwritten
	function create($module_type, $module_url, $post_id, $topic_id, $forum_id, $selected_mod = false, $selected_submod = false)
	{
		global $template, $auth, $db, $user, $config;
		global $phpbb_root_path, $phpEx;

		if ($post_id)
		{
			if (!$topic_id || !$forum_id)
			{
				$sql = 'SELECT topic_id, forum_id
					FROM ' . POSTS_TABLE . "
					WHERE post_id = $post_id";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				$topic_id = (int) $row['topic_id'];
				$forum_id = (int) $row['forum_id'];
			}
		}

		if ($topic_id && !$forum_id)
		{
			$sql = 'SELECT forum_id
				FROM ' . TOPICS_TABLE . "
				WHERE topic_id = $topic_id";
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			$forum_id = (int) $row['forum_id'];
		}

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
						$submodule = explode(',', trim($submodule));
						$submodule_title = array_shift($submodule);

						$is_auth = true;
						foreach ($submodule as $auth_option)
						{
							if (!$auth->acl_get($auth_option))
							{
								$is_auth = false;
							}
						}

						if (!$is_auth || empty($submodule_title))
						{
							continue;
						}

						// Only show those rows we are able to access
						if (($submodule_title == 'post_details' && !$post_id) || 
							($submodule_title == 'topic_view' && !$topic_id) ||
							($submodule_title == 'forum_view' && !$forum_id))
						{
							continue;
						}
			
						$suffix = ($post_id) ? "&amp;p=$post_id" : '';
						$suffix .= ($topic_id) ? "&amp;t=$topic_id" : '';
						$suffix .= ($forum_id) ? "&amp;f=$forum_id" : '';
											
						$selected = ($submodule_title == $selected_submod || (!$selected_submod && !$j)) ? true : false;

						// Get the localised lang string if available, or make up our own otherwise
						$module_lang = strtoupper($module_type . '_' . $module_name . '_' . $submodule_title);

						$template->assign_block_vars("{$module_type}_section.{$module_type}_subsection", array(
							'L_TITLE'		=> (isset($user->lang[$module_lang])) ? $user->lang[$module_lang] : ucfirst(str_replace('_', ' ', strtolower($module_lang))),
							'S_SELECTED'	=> $selected, 
							'U_TITLE'		=> $module_url . '&amp;i=' . $module_id . '&amp;mode=' . $submodule_title . $suffix)
						);

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
		$this->url = "{$phpbb_root_path}mcp.$phpEx?sid={$user->session_id}";
		$this->url .= ($post_id) ? "&amp;p=$post_id" : '';
		$this->url .= ($topic_id) ? "&amp;t=$topic_id" : '';
		$this->url .= ($forum_id) ? "&amp;f=$forum_id" : '';
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

				eval("\$this->module = new {$this->type}_{$this->name}(\$this->id, \$this->mode, \$this->url);");
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
$user->setup('mcp');

$mcp = new module();

// Basic parameter data
$mode	= request_var('mode', '');
$mode2	= (isset($_REQUEST['quick'])) ? request_var('mode2', '') : '';
$module = request_var('i', '');

if ($mode2)
{
	$mode = $mode2;
	$action = '';
	unset($mode2);
}

// Only Moderators can go beyond this point
if ($user->data['user_id'] == ANONYMOUS || !$auth->acl_get('m_'))
{
	if ($user->data['user_id'] != ANONYMOUS)
	{
		redirect("index.$phpEx$SID");
	}
	
	login_box("{$phpbb_root_path}mcp.$phpEx$SID&amp;mode=$mode&amp;i=$module", '', $user->lang['LOGIN_EXPLAIN_MCP']);
}

$quickmod = (isset($_REQUEST['quickmod'])) ? true : false;
$action = request_var('action', '');

if (is_array($action))
{
	list($action, ) = each($action);
}

if ($action == 'merge_select')
{
	$mode = 'forum_view';
}

if (!$quickmod)
{
	$post_id = request_var('p', 0);
	$topic_id = request_var('t', 0);
	$forum_id = request_var('f', 0);

	// Instantiate module system and generate list of available modules
	$mcp->create('mcp', "mcp.$phpEx$SID", $post_id, $topic_id, $forum_id, $module, $mode);

	// Load and execute the relevant module
	$mcp->load('mcp', 'main', $mode);
	exit;
}

switch ($mode)
{
	case 'lock':
	case 'unlock':
	case 'lock_post':
	case 'unlock_post':
		$mcp->load('mcp', 'main', $mode);
		break;
	case 'make_sticky':
	case 'make_announce':
	case 'make_global':
	case 'make_normal':
		$mcp->load('mcp', 'main', $mode);
		break;
	case 'move':
		$mcp->load('mcp', 'main', $mode);
		break;
	case 'delete_topic':
		$mcp->load('mcp', 'main', $mode);
		break;
	case 'delete_post':
		$mcp->load('mcp', 'main', $mode);
		break;
	case 'split':
	case 'merge':
	case 'fork':
	case 'viewlogs':
		break;
	default:
		trigger_error("$mode not allowed as quickmod");
}

?>