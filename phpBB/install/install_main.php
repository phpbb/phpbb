<?php
/** 
*
* @package install
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
*/

if (!empty($setmodules))
{
	$module[] = array(
		'module_type' => 'install',
		'module_title' => 'OVERVIEW',
		'module_filename' => substr(basename(__FILE__), 0, -strlen($phpEx)-1),
		'module_order' => 0,
		'module_subs' => array('INTRO', 'LICENSE', 'SUPPORT'),
		'module_stages' => '',
		'module_reqs' => ''
	);
}

class install_main extends module
{
	function install_main(&$p_master)
	{
		$this->p_master = &$p_master;
	}

	function main($mode, $sub)
	{
		global $user, $template;

		switch ($sub)
		{
			case 'intro' :
				$title = $user->lang['SUB_INTRO'];
				$body = $user->lang['OVERVIEW_BODY'];
			break;
			case 'license' :
				$title = $user->lang['GPL'];
				$body = implode("<br/>\n", file('../docs/COPYING'));
			break;
			case 'support' :
				$title = $user->lang['SUB_SUPPORT'];
				$body = $user->lang['SUPPORT_BODY'];
			break;
		}

		$this->tpl_name = 'install_main';
		$this->page_title = $title;

		$template->assign_vars(array(
			'TITLE'		=> $title,
			'BODY'		=> $body,
		));
	}
}

/**
* Add default modules
function add_default_modules()
{
	global $db, $phpbb_root_path, $phpEx;

	include_once($phpbb_root_path . 'includes/acp_modules.' . $phpEx);
	$module_class = 'acp';

	$_module = &new acp_modules();
	
	// Get the modules we want to add...
	$module_info = $_module->get_module_infos('', $module_class);

	foreach ($module_info as $module_name => $fileinfo)
	{
		foreach ($fileinfo['modes'] as $module_mode => $row)
		{
			$module_data = array(
				'module_name'		=> $module_name,
				'module_enabled'	=> 1,
				'module_display'	=> (isset($row['display'])) ? $row['display'] : 1,
				'parent_id'			=> $row['parent_id'],
				'module_class'		=> $module_class,
				'module_langname'	=> $row['title'],
				'module_mode'		=> $module_mode,
				'module_auth'		=> $row['auth'],
			);

			$_module->>update_module_data($module_data);
		}
	}

	// recalculate binary tree
	if (!function_exists('recalc_btree'))
	{
		include_once($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
	}

	recalc_btree('module_id', MODULES_TABLE, $module_class);
	$_module->remove_cache_file();
}
*/
?>