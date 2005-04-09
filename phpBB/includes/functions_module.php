<?php
/** 
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* @package phpBB3
* Module Class handling all types of modules
*/
class module
{
	var $id = 0;
	var $filename;
	var $mode;
	var $module_ary = array();
	var $module_y_ary = array();
	var $module_url = '';
	var $module_path = '';
	var $acl_forum_id = false;

	// Private methods, should not be overwritten
	function create($module_type, $module_path, $module_url, $selected_module = false)
	{
		global $template, $auth, $db, $user, $config, $phpbb_root_path, $phpEx;

		$active = $category = false;
		$this->module_url = htmlspecialchars($module_url);
		// TODO Do some checking here?
		$this->module_path = $module_path;

		if (file_exists($phpbb_root_path . 'cache/' . $module_type . '_modules.'.$phpEx))
		{
			include($phpbb_root_path . 'cache/' . $module_type . '_modules.'.$phpEx);
		}

		$sql = 'SELECT *
			FROM ' . MODULES_TABLE . "
			WHERE module_type = '" . $db->sql_escape($module_type) . "'
				AND module_enabled = 1
				$sql_and
			ORDER BY left_id";
		$result = $db->sql_query($sql);

		$padding = $i = 0;
		while ($row = $db->sql_fetchrow($result))
		{
			// Authorisation is required ... not authed, skip
			if ($row['module_auth'])
			{
				$is_auth = false;
				eval('$is_auth = (int) (' . preg_replace(array('#acl_([a-z_]+)(,\$id)?#e', '#\$id#', '#cfg_([a-z_]+)#e'), array('(int) $auth->acl_get("\\1"\\2)', '$this->acl_forum_id', '(int) $config["\\1"]'), trim($row['module_auth'])) . ');');
				if (!$is_auth)
				{
					continue;
				}
			}

			if ($row['module_cat'] && ($row['left_id'] + 1 == $row['right_id']))
			{
				// Categoriy with no members, ignore
				continue;
			}

			if ($row['left_id'] < $right)
			{
				$padding++;
				$padding_store[$row['parent_id']] = $padding;
			}
			else if ($row['left_id'] > $right + 1)
			{
				$padding = $padding_store[$row['parent_id']];
			}

			$right = $row['right_id'];

			$this->module_y_ary[$i] 			= ($padding) ? $padding : 0;

			$this->module_ary[$i]['id'] 		= (int) $row['module_id'];
			$this->module_ary[$i]['name'] 		= (function_exists($row['module_filename'])) ? $row['module_filename']($row['module_name']) : ((!empty($user->lang[$row['module_name']])) ? $user->lang[$row['module_name']] : ucfirst(str_replace('_', ' ', strtolower($row['module_name']))));
			$this->module_ary[$i]['filename']	= (string) $row['module_filename'];
			$this->module_ary[$i]['parent'] 	= (int) $row['parent_id'];
			$this->module_ary[$i]['category']	= (bool) $row['module_cat'];
			$this->module_ary[$i]['hilit'] 		= (bool) $row['module_hilit'];
			$this->module_ary[$i]['active'] 	= false;

			// If this is a module and it's selected, active
			// If this is a category and the module is the first within it, active
			// If no category or module selected, go active for first module in first category
			if (!$active && ((!$row['module_cat'] && $row['module_id'] == $selected_module) || $row['parent_id'] === $category || (!$selected_module && !$row['module_cat'])))
			{
				$this->module_ary[$i]['active'] = true;

				$this->id		= (int) $row['module_id'];
				$this->parent	= (int) $row['parent_id'];
				$this->filename = (string) $row['module_filename'];
				$this->mode 	= (string) $row['module_mode'];

				$active = true;
			}
			else if ($row['module_cat'] && $row['module_id'] == $selected_module)
			{
				$category = $row['module_id'];
			}

			$i++;
		}
		$db->sql_freeresult($result);
	}

	function load($mode = false, $run = true)
	{
		global $phpbb_root_path, $phpEx;

		if (!class_exists($this->filename))
		{
			require_once($phpbb_root_path . "$this->module_path/$this->filename.$phpEx");

			if ($run)
			{
				if (!empty($mode))
				{
					$this->mode = $mode;
				}

				eval("\$this->module = new $this->filename(\$this->id, \$this->mode);");
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

		$current_padding = 0;
		$linear_offset 	= 'l_block1';
		$tabular_offset = 't_block2';

		// Generate the list of modules, we'll do this in two ways ...
		// 1) In a linear fashion
		// 2) In a combined tabbed + linear fashion ... tabs for the categories
		//    and a linear list for subcategories/items
		foreach ($this->module_ary as $row_id => $item_ary)
		{
			$padding = $this->module_y_ary[$row_id];

			if ($padding > $current_padding)
			{
				$linear_offset = $linear_offset . '.l_block' . ($padding + 1);
				$tabular_offset = ($padding + 1 > 2) ? $tabular_offset . '.t_block' . ($padding + 1) : $tabular_offset;
			}
			else if ($padding < $current_padding)
			{
				for ($i = $current_padding - $padding; $i > 0; $i--)
				{
					$linear_offset = substr($linear_offset, 0, strrpos($linear_offset, '.'));
					$tabular_offset = ($i + $padding > 1) ? substr($tabular_offset, 0, strrpos($tabular_offset, '.')) : $tabular_offset;
				}
			}

			$current_padding = $padding;

			// Only output a categories items if it's currently selected
			if (!$padding || ($padding && $item_ary['parent'] == $this->parent))
			{
				$use_tabular_offset = (!$padding) ? 't_block1' : $tabular_offset;
				$template->assign_block_vars($use_tabular_offset, array(
					'L_TITLE'		=> $item_ary['name'],
					'S_SELECTED'	=> ($item_ary['id'] == $this->parent || $item_ary['active']) ? true : false,
					'U_TITLE'		=> $this->module_url . '&amp;i=' . $item_ary['id'])
				);
			}

			$template->assign_block_vars($linear_offset, array(
				'L_TITLE'		=> $item_ary['name'],
				'S_SELECTED'	=> ($item_ary['id'] == $this->parent || $item_ary['active']) ? true : false,
				'U_TITLE'		=> $this->module_url . '&amp;i=' . $item_ary['id'])
			);
		}

		page_header($page_title);

		$template->set_filenames(array(
			'body' => $tpl_name . '_' . $this->mode . '.html')
		);

		page_footer();
	}

	//
	// Public methods to be overwritten by modules
	//
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
}

?>