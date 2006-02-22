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
* Class handling all types of 'plugins' (a future term)
*/
class p_master
{
	/**#@+
	* @access private
	*/
	var $p_id;
	var $p_class;
	var $p_name;
	var $p_mode;
	var $p_parent;

	var $acl_forup_id = false;
	/**#@-*/

	/**#@+
	* This array holds information on the list of modules
	*/
	var $module_ary = array();
	/**#@-*/

	/**
	* List modules
	*
	* This creates a list, stored in $this->module_ary of all available
	* modules for the given class (ucp, mcp and acp). Additionally
	* $this->module_y_ary is created with indentation information for
	* displaying the module list appropriately. Only modules for which
	* the user has access rights are included in these lists.
	*
	* @final
	*/
	function list_modules($p_class)
	{
		global $auth, $db, $user;
		global $config, $phpbb_root_path, $phpEx;

		$get_cache_data = true;

		// Empty cached contents
		$this->module_cache = array();

		// Sanitise for future path use, it's escaped as appropriate for queries
		$this->p_class = str_replace(array('.', '/', '\\'), '', basename($p_class));
		
		if (file_exists($phpbb_root_path . 'cache/' . $this->p_class . '_modules.' . $phpEx))
		{
			include($phpbb_root_path . 'cache/' . $this->p_class . '_modules.' . $phpEx);
			$get_cache_data = false;
		}

		if ($get_cache_data)
		{
			global $cache;
			
			// Get modules
			$sql = 'SELECT *
				FROM ' . MODULES_TABLE . "
				WHERE module_class = '" . $db->sql_escape($p_class) . "'
				ORDER BY left_id ASC";
			$result = $db->sql_query($sql);
			
			$rows = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$rows[$row['module_id']] = $row;
			}
			$db->sql_freeresult($result);		

			$this->module_cache = array();
			foreach ($rows as $module_id => $row)
			{
				$this->module_cache['modules'][] = $row;
				$this->module_cache['parents'][$row['module_id']] = $this->get_parents($row['parent_id'], $row['left_id'], $row['right_id'], $rows);
			}
			unset($rows);

			$file = '<?php $this->module_cache=' . $cache->format_array($this->module_cache) . "; ?>";

			if ($fp = @fopen($phpbb_root_path . 'cache/' . $this->p_class . '_modules.' . $phpEx, 'wb'))
			{
				@flock($fp, LOCK_EX);
				fwrite($fp, $file);
				@flock($fp, LOCK_UN);
				fclose($fp);
			}

			unset($file);
		}

		// We "could" build a true tree with this function - maybe mod authors want to use this...
		// Functions for traversing and manipulating the tree are not available though
		// We might re-structure the module system to use true trees in 3.2.x...
		// $tree = $this->build_tree($this->module_cache['modules'], $this->module_cache['parents']);

		// Clean up module cache array to only let survive modules the user can access
		$right_id = false;
		foreach ($this->module_cache['modules'] as $key => $row)
		{
			// Not allowed to view module?
			if (!$this->module_auth($row['module_auth']))
			{
				unset($this->module_cache['modules'][$key]);
				continue;
			}

			// Category with no members, ignore
			if (!$row['module_name'] && ($row['left_id'] + 1 == $row['right_id']))
			{
				unset($this->module_cache['modules'][$key]);
				continue;
			}

			// Skip branch
			if ($right_id !== false)
			{
				if ($row['left_id'] < $right_id)
				{
					unset($this->module_cache['modules'][$key]);
					continue;
				}
				
				$right_id = false;
			}

			// Not enabled?
			if (!$row['module_enabled'])
			{
				// If category is disabled then disable every child too
				unset($this->module_cache['modules'][$key]);
				$right_id = $row['right_id'];
				continue;
			}
		}

		// Re-index (this is needed, else we are not able to array_slice later)
		$this->module_cache['modules'] = array_merge($this->module_cache['modules']);

		// Now build the module array, but exclude completely empty categories...
		$right_id = false;
		$names = array();

		foreach ($this->module_cache['modules'] as $key => $row)
		{
			// Skip branch
			if ($right_id !== false)
			{
				if ($row['left_id'] < $right_id)
				{
					continue;
				}
				
				$right_id = false;
			}

			// Category with no members on their way down (we have to check every level)
			if (!$row['module_name'])
			{
				$empty_category = true;

				// We go through the branch and look for an activated module
				foreach (array_slice($this->module_cache['modules'], $key + 1) as $temp_row)
				{
					if ($temp_row['left_id'] > $row['left_id'] && $temp_row['left_id'] < $row['right_id'])
					{
						// Module there
						if ($temp_row['module_name'] && $temp_row['module_enabled'])
						{
							$empty_category = false;
							break;
						}
						continue;
					}
					break;
				}

				// Skip the branch
				if ($empty_category)
				{
					$right_id = $row['right_id'];
					continue;
				}
			}

			$depth = sizeof($this->module_cache['parents'][$row['module_id']]);

			// We need to prefix the functions to not create a naming conflict
			$url_func = '_module_' . $row['module_name'] . '_' . $row['module_mode'] . '_url';
			$lang_func = '_module_' . $row['module_name'];

			$names[$row['module_name'] . '_' . $row['module_mode']][] = true;
			
			$this->module_ary[] = array(
				'depth'		=> $depth,

				'id'		=> (int) $row['module_id'],
				'parent'	=> (int) $row['parent_id'],
				'cat'		=> ($row['right_id'] > $row['left_id'] + 1) ? true : false,

				'is_duplicate'	=> ($row['module_name'] && sizeof($names[$row['module_name'] . '_' . $row['module_mode']]) > 1) ? true : false,

				'name'		=> (string) $row['module_name'],
				'mode'		=> (string) $row['module_mode'],
				'display'	=> (int) $row['module_display'],

				'url_extra'	=> (function_exists($url_func)) ? $url_func() : '',
				
				'lang'		=> ($row['module_name'] && function_exists($lang_func)) ? $lang_func($row['module_mode'], $row['module_langname']) : ((!empty($user->lang[$row['module_langname']])) ? $user->lang[$row['module_langname']] : $row['module_langname']),
				'langname'	=> $row['module_langname'],

				'left'		=> $row['left_id'],
				'right'		=> $row['right_id'],
			);
		}

		unset($this->module_cache['modules'], $names);
	}

	/**
	* Check module authorisation
	* @todo implement $this->is_module_id
	*/
	function module_auth($module_auth)
	{
		global $auth, $config;
		
		$module_auth = trim($module_auth);

		// Generally allowed to access module if module_auth is empty
		if (!$module_auth)
		{
			return true;
		}

		$is_auth = false;
		eval('$is_auth = (int) (' . preg_replace(array('#acl_([a-z_]+)(,\$id)?#', '#\$id#', '#aclf_([a-z_]+)#', '#cfg_([a-z_]+)#'), array('(int) $auth->acl_get("\\1"\\2)', '(int) $this->acl_forup_id', '(int) $auth->acl_getf_global("\\1")', '(int) $config["\\1"]'), $module_auth) . ');');

		return $is_auth;
	}

	/**
	* Set active module
	*/
	function set_active($id = false, $mode = false)
	{
		$icat = false;

		if (request_var('icat', ''))
		{
			$icat = $id;
			$id = request_var('icat', '');
		}

		$category = false;
		foreach ($this->module_ary as $row_id => $itep_ary)
		{
			// If this is a module and it's selected, active
			// If this is a category and the module is the first within it, active
			// If this is a module and no mode selected, select first mode
			// If no category or module selected, go active for first module in first category
			if (
				(($itep_ary['name'] === $id || $itep_ary['id'] === (int) $id) && (($itep_ary['mode'] == $mode && !$itep_ary['cat']) || ($icat && $itep_ary['cat']))) ||
				($itep_ary['parent'] === $category && !$itep_ary['cat'] && !$icat) ||
				(($itep_ary['name'] === $id || $itep_ary['id'] === (int) $id) && !$mode && !$itep_ary['cat']) ||
				(!$id && !$mode && !$itep_ary['cat'])
				)
			{
				if ($itep_ary['cat'])
				{
					$id = $icat;
					$icat = false;

					continue;
				}

				$this->p_id		= $itep_ary['id'];
				$this->p_parent	= $itep_ary['parent'];
				$this->p_name	= $itep_ary['name'];
				$this->p_mode 	= $itep_ary['mode'];
				$this->p_left	= $itep_ary['left'];
				$this->p_right	= $itep_ary['right'];

				$this->module_cache['parents'] = $this->module_cache['parents'][$this->p_id];

				break;
			}
			else if (($itep_ary['cat'] && $itep_ary['id'] === (int) $id) || ($itep_ary['parent'] === $category && $itep_ary['cat']))
			{
				$category = $itep_ary['id'];
			}
		}
	}

	/**
	* Loads currently active module
	*
	* This method loads a given module, passing it the relevant id and mode.
	*
	* @final
	*/
	function load_active($mode = false)
	{
		global $phpbb_root_path, $phpbb_admin_path, $phpEx, $SID;

		$module_path = $phpbb_root_path . 'includes/' . $this->p_class;
		$icat = request_var('icat', '');

		if (!class_exists("{$this->p_class}_$this->p_name"))
		{
			if (!file_exists("$module_path/{$this->p_class}_$this->p_name.$phpEx"))
			{
				trigger_error('Cannot find module', E_USER_ERROR);
			}

			include("$module_path/{$this->p_class}_$this->p_name.$phpEx");

			if (!class_exists("{$this->p_class}_$this->p_name"))
			{
				trigger_error('Module does not contain correct class', E_USER_ERROR);
			}

			if (!empty($mode))
			{
				$this->p_mode = $mode;
			}

			// Create a new instance of the desired module ... if it has a
			// constructor it will of course be executed
			$instance = "{$this->p_class}_$this->p_name";

			$this->module = new $instance($this);

			// We pre-define the action parameter we are using all over the place
			$this->module->u_action = "{$phpbb_admin_path}index.$phpEx$SID" . (($icat) ? '&amp;icat=' . $icat : '') . "&amp;i={$this->p_id}&amp;mode={$this->p_mode}";

			// Execute the main method for the new instance, we send the module
			// id and mode as parameters
			$this->module->main(($this->p_name) ? $this->p_name : $this->p_id, $this->p_mode);

			return;
		}
	}

	/**
	* Get parents
	*/
	function get_parents($parent_id, $left_id, $right_id, &$all_parents)
	{
		global $db;

		$parents = array();

		if ($parent_id > 0)
		{
			foreach ($all_parents as $module_id => $row)
			{
				if ($row['left_id'] < $left_id && $row['right_id'] > $right_id)
				{
					$parents[$module_id] = $row['parent_id'];
				}

				if ($row['left_id'] > $left_id)
				{
					break;
				}
			}
		}

		return $parents;
	}

	/**
	* Get tree branch
	*/
	function get_branch($left_id, $right_id, $remaining)
	{
		$branch = array();

		foreach ($remaining as $key => $row)
		{
			if ($row['left_id'] > $left_id && $row['left_id'] < $right_id)
			{
				$branch[] = $row;
				continue;
			}
			break;
		}
		
		return $branch;
	}

	/**
	* Build true binary tree from given array
	*/
	function build_tree(&$modules, &$parents)
	{
		$tree = array();

		foreach ($modules as $row)
		{
			$branch = &$tree;

			if ($row['parent_id'])
			{
				// Go through the tree to find our branch
				$parent_tree = $parents[$row['module_id']];
					
				foreach ($parent_tree as $id => $value)
				{
					if (!isset($branch[$id]) && isset($branch['child']))
					{
						$branch = &$branch['child'];
					}
					$branch = &$branch[$id];
				}
				$branch = &$branch['child'];
			}

			$branch[$row['module_id']] = $row;
			if (!isset($branch[$row['module_id']]['child']))
			{
				$branch[$row['module_id']]['child'] = array();
			}
		}
		
		return $tree;
	}

	/**
	* Build navigation structure
	*/
	function assign_tpl_vars($module_url)
	{
		global $template;

		$current_id = false;

		$current_padding = $current_depth = 0;
		$linear_offset 	= 'l_block1';
		$tabular_offset = 't_block2';

		// Generate the list of modules, we'll do this in two ways ...
		// 1) In a linear fashion
		// 2) In a combined tabbed + linear fashion ... tabs for the categories
		//    and a linear list for subcategories/items
		foreach ($this->module_ary as $row_id => $itep_ary)
		{
			if (!$itep_ary['display'])
			{
				continue;
			}

			// Select first id we can get
			if (!$current_id && (in_array($itep_ary['id'], array_keys($this->module_cache['parents'])) || $itep_ary['id'] == $this->p_id))
			{
				$current_id = $itep_ary['id'];
			}

			$depth = $itep_ary['depth'];

			if ($depth > $current_depth)
			{
				$linear_offset = $linear_offset . '.l_block' . ($depth + 1);
				$tabular_offset = ($depth + 1 > 2) ? $tabular_offset . '.t_block' . ($depth + 1) : $tabular_offset;
			}
			else if ($depth < $current_depth)
			{
				for ($i = $current_depth - $depth; $i > 0; $i--)
				{
					$linear_offset = substr($linear_offset, 0, strrpos($linear_offset, '.'));
					$tabular_offset = ($i + $depth > 1) ? substr($tabular_offset, 0, strrpos($tabular_offset, '.')) : $tabular_offset;
				}
			}

			$u_title = $module_url . (($itep_ary['is_duplicate']) ? '&amp;icat=' . $current_id : '') . '&amp;i=' . (($itep_ary['cat']) ? $itep_ary['id'] : $itep_ary['name'] . '&amp;mode=' . $itep_ary['mode']);
			$u_title .= (!$itep_ary['cat'] && isset($itep_ary['url_extra'])) ? $itep_ary['url_extra'] : '';
			
			// Only output a categories items if it's currently selected
			if (!$depth || ($depth && (in_array($itep_ary['parent'], array_values($this->module_cache['parents'])) || $itep_ary['parent'] == $this->p_parent)))
			{
				$use_tabular_offset = (!$depth) ? 't_block1' : $tabular_offset;
				
				$tpl_ary = array(
					'L_TITLE'		=> $itep_ary['lang'],
					'S_SELECTED'	=> (in_array($itep_ary['id'], array_keys($this->module_cache['parents'])) || $itep_ary['id'] == $this->p_id) ? true : false,
					'U_TITLE'		=> $u_title
				);

				$template->assign_block_vars($use_tabular_offset, array_merge($tpl_ary, array_change_key_case($itep_ary, CASE_UPPER)));
			}

			$tpl_ary = array(
				'L_TITLE'		=> $itep_ary['lang'],
				'S_SELECTED'	=> (in_array($itep_ary['id'], array_keys($this->module_cache['parents'])) || $itep_ary['id'] == $this->p_id) ? true : false,
				'U_TITLE'		=> $u_title
			);

			$template->assign_block_vars($linear_offset, array_merge($tpl_ary, array_change_key_case($itep_ary, CASE_UPPER)));

			$current_depth = $depth;
		}
	}

	/**
	* Returns desired template name
	*/
	function get_tpl_name()
	{
		return $this->module->tpl_name . '.html';
	}

	/**
	* Returns the desired page title
	*/
	function get_page_title()
	{
		global $user;

		if (!isset($this->module->page_title))
		{
			return '';
		}

		return (isset($user->lang[$this->module->page_title])) ? $user->lang[$this->module->page_title] : $this->module->page_title;
	}

	/**
	* Load module as the current active one without the need for registering it
	*/
	function load($class, $name, $mode = false)
	{
		$this->p_class = $class;
		$this->p_name = $name;
		
		$this->load_active($mode);
	}

	/**
	* Display module
	*/
	function display($page_title)
	{
		global $template, $user;

		// Generate the page
		if (defined('IN_ADMIN') && isset($user->data['session_admin']) && $user->data['session_admin'])
		{
			adm_page_header($page_title);
		}
		else
		{
			page_header($page_title);
		}

		$template->set_filenames(array(
			'body' => $this->get_tpl_name())
		);

		if (defined('IN_ADMIN') && isset($user->data['session_admin']) && $user->data['session_admin'])
		{
			adm_page_footer();
		}
		else
		{
			page_footer();
		}
	}

	/**
	* Toggle whether this module will be displayed or not
	*/
	function set_display($id, $mode = false, $display = true)
	{
		foreach ($this->module_ary as $row_id => $itep_ary)
		{
			if (($itep_ary['name'] === $id || $itep_ary['id'] === (int) $id) && (!$mode || $itep_ary['mode'] === $mode))
			{
				$this->module_ary[$row_id]['display'] = (int) $display;
			}
		}
	}
}

?>