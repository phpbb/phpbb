<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Class handling all types of 'plugins' (a future term)
*/
class p_master
{
	var $p_id;
	var $p_class;
	var $p_name;
	var $p_mode;
	var $p_parent;

	var $include_path = false;
	var $active_module = false;
	var $active_module_row_id = false;
	var $acl_forum_id = false;
	var $module_ary = array();

	/**
	* Constuctor
	* Set module include path
	*/
	function __construct($include_path = false)
	{
		global $phpbb_root_path;

		$this->include_path = ($include_path !== false) ? $include_path : $phpbb_root_path . 'includes/';

		// Make sure the path ends with /
		if (substr($this->include_path, -1) !== '/')
		{
			$this->include_path .= '/';
		}
	}

	/**
	* Set custom include path for modules
	* Schema for inclusion is include_path . modulebase
	*
	* @param string $include_path include path to be used.
	* @access public
	*/
	function set_custom_include_path($include_path)
	{
		$this->include_path = $include_path;

		// Make sure the path ends with /
		if (substr($this->include_path, -1) !== '/')
		{
			$this->include_path .= '/';
		}
	}

	/**
	* List modules
	*
	* This creates a list, stored in $this->module_ary of all available
	* modules for the given class (ucp, mcp and acp). Additionally
	* $this->module_y_ary is created with indentation information for
	* displaying the module list appropriately. Only modules for which
	* the user has access rights are included in these lists.
	*/
	function list_modules($p_class)
	{
		global $db, $user, $cache;
		global $phpbb_dispatcher;

		// Sanitise for future path use, it's escaped as appropriate for queries
		$this->p_class = str_replace(array('.', '/', '\\'), '', basename($p_class));

		// Get cached modules
		if (($this->module_cache = $cache->get('_modules_' . $this->p_class)) === false)
		{
			// Get modules
			$sql = 'SELECT *
				FROM ' . MODULES_TABLE . "
				WHERE module_class = '" . $db->sql_escape($this->p_class) . "'
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

			$cache->put('_modules_' . $this->p_class, $this->module_cache);
		}

		if (empty($this->module_cache))
		{
			$this->module_cache = array('modules' => array(), 'parents' => array());
		}

		// We "could" build a true tree with this function - maybe mod authors want to use this...
		// Functions for traversing and manipulating the tree are not available though
		// We might re-structure the module system to use true trees in 4.0
		// $tree = $this->build_tree($this->module_cache['modules'], $this->module_cache['parents']);

		// Clean up module cache array to only let survive modules the user can access
		$right_id = false;

		$hide_categories = array();
		foreach ($this->module_cache['modules'] as $key => $row)
		{
			// When the module has no mode (category) we check whether it has visible children
			// before listing it as well.
			if (!$row['module_mode'])
			{
				$hide_categories[(int) $row['module_id']] = $key;
			}

			// Not allowed to view module?
			if (!$this->module_auth_self($row['module_auth']))
			{
				unset($this->module_cache['modules'][$key]);
				continue;
			}

			// Category with no members, ignore
			if (!$row['module_basename'] && ($row['left_id'] + 1 == $row['right_id']))
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

			if ($row['module_mode'])
			{
				// The parent category has a visible child
				// So remove it and all its parents from the hide array
				unset($hide_categories[(int) $row['parent_id']]);
				foreach ($this->module_cache['parents'][$row['module_id']] as $module_id => $row_id)
				{
					unset($hide_categories[$module_id]);
				}
			}
		}

		foreach ($hide_categories as $module_id => $row_id)
		{
			unset($this->module_cache['modules'][$row_id]);
		}

		// Re-index (this is needed, else we are not able to array_slice later)
		$this->module_cache['modules'] = array_merge($this->module_cache['modules']);

		// Include MOD _info files for populating language entries within the menus
		$this->add_mod_info($this->p_class);

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
			if (!$row['module_basename'])
			{
				$empty_category = true;

				// We go through the branch and look for an activated module
				foreach (array_slice($this->module_cache['modules'], $key + 1) as $temp_row)
				{
					if ($temp_row['left_id'] > $row['left_id'] && $temp_row['left_id'] < $row['right_id'])
					{
						// Module there
						if ($temp_row['module_basename'] && $temp_row['module_enabled'])
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

			$depth = count($this->module_cache['parents'][$row['module_id']]);

			// We need to prefix the functions to not create a naming conflict

			// Function for building 'url_extra'
			$short_name = $this->get_short_name($row['module_basename']);

			$url_func = 'phpbb_module_' . $short_name . '_url';
			if (!function_exists($url_func))
			{
				$url_func = '_module_' . $short_name . '_url';
			}

			// Function for building the language name
			$lang_func = 'phpbb_module_' . $short_name . '_lang';
			if (!function_exists($lang_func))
			{
				$lang_func = '_module_' . $short_name . '_lang';
			}

			// Custom function for calling parameters on module init (for example assigning template variables)
			$custom_func = 'phpbb_module_' . $short_name;
			if (!function_exists($custom_func))
			{
				$custom_func = '_module_' . $short_name;
			}

			$names[$row['module_basename'] . '_' . $row['module_mode']][] = true;

			$module_row = array(
				'depth'		=> $depth,

				'id'		=> (int) $row['module_id'],
				'parent'	=> (int) $row['parent_id'],
				'cat'		=> ($row['right_id'] > $row['left_id'] + 1) ? true : false,

				'is_duplicate'	=> ($row['module_basename'] && count($names[$row['module_basename'] . '_' . $row['module_mode']]) > 1) ? true : false,

				'name'		=> (string) $row['module_basename'],
				'mode'		=> (string) $row['module_mode'],
				'display'	=> (int) $row['module_display'],

				'url_extra'	=> (function_exists($url_func)) ? $url_func($row['module_mode'], $row) : '',

				'lang'		=> ($row['module_basename'] && function_exists($lang_func)) ? $lang_func($row['module_mode'], $row['module_langname']) : ((!empty($user->lang[$row['module_langname']])) ? $user->lang[$row['module_langname']] : $row['module_langname']),
				'langname'	=> $row['module_langname'],

				'left'		=> $row['left_id'],
				'right'		=> $row['right_id'],
			);

			if (function_exists($custom_func))
			{
				$custom_func($row['module_mode'], $module_row);
			}

			/**
			* This event allows to modify parameters for building modules list
			*
			* @event core.modify_module_row
			* @var	string		url_func		Function for building 'url_extra'
			* @var	string		lang_func		Function for building the language name
			* @var	string		custom_func		Custom function for calling parameters on module init
			* @var	array		row				Array holding the basic module data
			* @var	array		module_row		Array holding the module display parameters
			* @since 3.1.0-b3
			*/
			$vars = array('url_func', 'lang_func', 'custom_func', 'row', 'module_row');
			extract($phpbb_dispatcher->trigger_event('core.modify_module_row', compact($vars)));

			$this->module_ary[] = $module_row;
		}

		unset($this->module_cache['modules'], $names);
	}

	/**
	* Check if a certain main module is accessible/loaded
	* By giving the module mode you are able to additionally check for only one mode within the main module
	*
	* @param string $module_basename The module base name, for example logs, reports, main (for the mcp).
	* @param mixed $module_mode The module mode to check. If provided the mode will be checked in addition for presence.
	*
	* @return bool Returns true if module is loaded and accessible, else returns false
	*/
	function loaded($module_basename, $module_mode = false)
	{
		if (!$this->is_full_class($module_basename))
		{
			$module_basename = $this->p_class . '_' . $module_basename;
		}

		if (empty($this->loaded_cache))
		{
			$this->loaded_cache = array();

			foreach ($this->module_ary as $row)
			{
				if (!$row['name'])
				{
					continue;
				}

				if (!isset($this->loaded_cache[$row['name']]))
				{
					$this->loaded_cache[$row['name']] = array();
				}

				if (!$row['mode'])
				{
					continue;
				}

				$this->loaded_cache[$row['name']][$row['mode']] = true;
			}
		}

		if ($module_mode === false)
		{
			return (isset($this->loaded_cache[$module_basename])) ? true : false;
		}

		return (!empty($this->loaded_cache[$module_basename][$module_mode])) ? true : false;
	}

	/**
	* Check module authorisation.
	*
	* This is a non-static version that uses $this->acl_forum_id
	* for the forum id.
	*/
	function module_auth_self($module_auth)
	{
		return self::module_auth($module_auth, $this->acl_forum_id);
	}

	/**
	* Check module authorisation.
	*
	* This is a static version, it must be given $forum_id.
	* See also module_auth_self.
	*/
	static function module_auth($module_auth, $forum_id)
	{
		global $auth, $config;
		global $request, $phpbb_extension_manager, $phpbb_dispatcher;

		$module_auth = trim($module_auth);

		// Generally allowed to access module if module_auth is empty
		if (!$module_auth)
		{
			return true;
		}

		// With the code below we make sure only those elements get eval'd we really want to be checked
		preg_match_all('/(?:
			"[^"\\\\]*(?:\\\\.[^"\\\\]*)*"         |
			\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'     |
			[(),]                                  |
			[^\s(),]+)/x', $module_auth, $match);

		// Valid tokens for auth and their replacements
		$valid_tokens = array(
			'acl_([a-z0-9_]+)(,\$id)?'		=> '(int) $auth->acl_get(\'\\1\'\\2)',
			'\$id'							=> '(int) $forum_id',
			'aclf_([a-z0-9_]+)'				=> '(int) $auth->acl_getf_global(\'\\1\')',
			'cfg_([a-z0-9_]+)'				=> '(int) $config[\'\\1\']',
			'request_([a-zA-Z0-9_]+)'		=> '$request->variable(\'\\1\', false)',
			'ext_([a-zA-Z0-9_/]+)'			=> 'array_key_exists(\'\\1\', $phpbb_extension_manager->all_enabled())',
			'authmethod_([a-z0-9_\\\\]+)'		=> '($config[\'auth_method\'] === \'\\1\')',
		);

		/**
		* Alter tokens for module authorisation check
		*
		* @event core.module_auth
		* @var	array	valid_tokens		Valid tokens and their auth check
		*									replacements
		* @var	string	module_auth			The module_auth of the current
		* 									module
		* @var	int		forum_id			The current forum_id
		* @since 3.1.0-a3
		*/
		$vars = array('valid_tokens', 'module_auth', 'forum_id');
		extract($phpbb_dispatcher->trigger_event('core.module_auth', compact($vars)));

		$tokens = $match[0];
		for ($i = 0, $size = count($tokens); $i < $size; $i++)
		{
			$token = &$tokens[$i];

			switch ($token)
			{
				case ')':
				case '(':
				case '&&':
				case '||':
				case ',':
				break;

				default:
					if (!preg_match('#(?:' . implode(')|(?:', array_keys($valid_tokens)) . ')#', $token))
					{
						$token = '';
					}
				break;
			}
		}

		$module_auth = implode(' ', $tokens);

		// Make sure $id separation is working fine
		$module_auth = str_replace(' , ', ',', $module_auth);

		$module_auth = preg_replace(
			// Array keys with # prepended/appended
			array_map(function($value) {
				return '#' . $value . '#';
			}, array_keys($valid_tokens)),
			array_values($valid_tokens),
			$module_auth
		);

		$is_auth = false;
		// @codingStandardsIgnoreStart
		eval('$is_auth = (int) (' .	$module_auth . ');');
		// @codingStandardsIgnoreEnd

		return $is_auth;
	}

	/**
	* Set active module
	*/
	function set_active($id = false, $mode = false)
	{
		global $request;

		$icat = false;
		$this->active_module = false;

		if ($request->variable('icat', ''))
		{
			$icat = $id;
			$id = $request->variable('icat', '');
		}

		// Restore the backslashes in class names
		if (strpos($id, '-') !== false)
		{
			$id = str_replace('-', '\\', $id);
		}

		if ($id && !is_numeric($id) && !$this->is_full_class($id))
		{
			$id = $this->p_class . '_' . $id;
		}

		$category = false;
		foreach ($this->module_ary as $row_id => $item_ary)
		{
			// If this is a module and it's selected, active
			// If this is a category and the module is the first within it, active
			// If this is a module and no mode selected, select first mode
			// If no category or module selected, go active for first module in first category
			if (
				(($item_ary['name'] === $id || $item_ary['name'] === $this->p_class . '_' . $id || $item_ary['id'] === (int) $id) && (($item_ary['mode'] == $mode && !$item_ary['cat']) || ($icat && $item_ary['cat']))) ||
				($item_ary['parent'] === $category && !$item_ary['cat'] && !$icat && $item_ary['display']) ||
				(($item_ary['name'] === $id || $item_ary['name'] === $this->p_class . '_' . $id || $item_ary['id'] === (int) $id) && !$mode && !$item_ary['cat']) ||
				(!$id && !$mode && !$item_ary['cat'] && $item_ary['display'])
				)
			{
				if ($item_ary['cat'])
				{
					$id = $icat;
					$icat = false;

					continue;
				}

				$this->p_id		= $item_ary['id'];
				$this->p_parent	= $item_ary['parent'];
				$this->p_name	= $item_ary['name'];
				$this->p_mode 	= $item_ary['mode'];
				$this->p_left	= $item_ary['left'];
				$this->p_right	= $item_ary['right'];

				$this->module_cache['parents'] = $this->module_cache['parents'][$this->p_id];
				$this->active_module = $item_ary['id'];
				$this->active_module_row_id = $row_id;

				break;
			}
			else if (($item_ary['cat'] && $item_ary['id'] === (int) $id) || ($item_ary['parent'] === $category && $item_ary['cat']))
			{
				$category = $item_ary['id'];
			}
		}
	}

	/**
	* Loads currently active module
	*
	* This method loads a given module, passing it the relevant id and mode.
	*
	* @param string|false $mode mode, as passed through to the module
	* @param string|false $module_url If supplied, we use this module url
	* @param bool $execute_module If true, at the end we execute the main method for the new instance
	*/
	function load_active($mode = false, $module_url = false, $execute_module = true)
	{
		global $phpbb_root_path, $phpbb_admin_path, $phpEx, $user, $template, $request;

		$module_path = $this->include_path . $this->p_class;
		$icat = $request->variable('icat', '');

		if ($this->active_module === false)
		{
			trigger_error('MODULE_NOT_ACCESS', E_USER_ERROR);
		}

		// new modules use the full class names, old ones are always called <type>_<name>, e.g. acp_board
		if (!class_exists($this->p_name))
		{
			if (!file_exists("$module_path/{$this->p_name}.$phpEx"))
			{
				trigger_error($user->lang('MODULE_NOT_FIND', "$module_path/{$this->p_name}.$phpEx"), E_USER_ERROR);
			}

			include("$module_path/{$this->p_name}.$phpEx");

			if (!class_exists($this->p_name))
			{
				trigger_error($user->lang('MODULE_FILE_INCORRECT_CLASS', "$module_path/{$this->p_name}.$phpEx", $this->p_name), E_USER_ERROR);
			}
		}

		if (!empty($mode))
		{
			$this->p_mode = $mode;
		}

		// Create a new instance of the desired module ...
		$class_name = $this->p_name;

		$this->module = new $class_name($this);

		// We pre-define the action parameter we are using all over the place
		if (defined('IN_ADMIN'))
		{
			/*
			* If this is an extension module, we'll try to automatically set
			* the style paths for the extension (the ext author can change them
			* if necessary).
			*/
			$module_dir = explode('\\', get_class($this->module));

			// 0 vendor, 1 extension name, ...
			if (isset($module_dir[1]))
			{
				$module_style_dir = $phpbb_root_path . 'ext/' . $module_dir[0] . '/' . $module_dir[1] . '/adm/style';

				if (is_dir($module_style_dir))
				{
					$template->set_custom_style(array(
						array(
							'name' 		=> 'adm',
							'ext_path' 	=> 'adm/style/',
						),
					), array($module_style_dir, $phpbb_admin_path . 'style'));
				}
			}

			// Is first module automatically enabled a duplicate and the category not passed yet?
			if (!$icat && $this->module_ary[$this->active_module_row_id]['is_duplicate'])
			{
				$icat = $this->module_ary[$this->active_module_row_id]['parent'];
			}

			// Not being able to overwrite ;)
			$this->module->u_action = append_sid("{$phpbb_admin_path}index.$phpEx", 'i=' . $this->get_module_identifier($this->p_name)) . (($icat) ? '&amp;icat=' . $icat : '') . "&amp;mode={$this->p_mode}";
		}
		else
		{
			/*
			* If this is an extension module, we'll try to automatically set
			* the style paths for the extension (the ext author can change them
			* if necessary).
			*/
			$module_dir = explode('\\', get_class($this->module));

			// 0 vendor, 1 extension name, ...
			if (isset($module_dir[1]))
			{
				$module_style_dir = 'ext/' . $module_dir[0] . '/' . $module_dir[1] . '/styles';

				if (is_dir($phpbb_root_path . $module_style_dir))
				{
					$template->set_style(array($module_style_dir, 'styles'));
				}
			}

			// If user specified the module url we will use it...
			if ($module_url !== false)
			{
				$this->module->u_action = $module_url;
			}
			else
			{
				$this->module->u_action = $phpbb_root_path . (($user->page['page_dir']) ? $user->page['page_dir'] . '/' : '') . $user->page['page_name'];
			}

			$this->module->u_action = append_sid($this->module->u_action, 'i=' . $this->get_module_identifier($this->p_name)) . (($icat) ? '&amp;icat=' . $icat : '') . "&amp;mode={$this->p_mode}";
		}

		// Add url_extra parameter to u_action url
		if (!empty($this->module_ary) && $this->active_module !== false && $this->module_ary[$this->active_module_row_id]['url_extra'])
		{
			$this->module->u_action .= '&amp;' . $this->module_ary[$this->active_module_row_id]['url_extra'];
		}

		// Assign the module path for re-usage
		$this->module->module_path = $module_path . '/';

		// Execute the main method for the new instance, we send the module id and mode as parameters
		// Users are able to call the main method after this function to be able to assign additional parameters manually
		if ($execute_module)
		{
			$short_name = preg_replace("#^{$this->p_class}_#", '', $this->p_name);
			$this->module->main($short_name, $this->p_mode);
		}
	}

	/**
	* Appending url parameter to the currently active module.
	*
	* This function is called for adding specific url parameters while executing the current module.
	* It is doing the same as the _module_{name}_url() function, apart from being able to be called after
	* having dynamically parsed specific parameters. This allows more freedom in choosing additional parameters.
	* One example can be seen in /includes/mcp/mcp_notes.php - $this->p_master->adjust_url() call.
	*
	* @param string $url_extra Extra url parameters, e.g.: &amp;u=$user_id
	*
	*/
	function adjust_url($url_extra)
	{
		if (empty($this->module_ary[$this->active_module_row_id]))
		{
			return;
		}

		$row = &$this->module_ary[$this->active_module_row_id];

		// We check for the same url_extra in $row['url_extra'] to overcome doubled additions...
		if (strpos($row['url_extra'], $url_extra) === false)
		{
			$row['url_extra'] .= $url_extra;
		}
	}

	/**
	* Check if a module is active
	*/
	function is_active($id, $mode = false)
	{
		// If we find a name by this id and being enabled we have our active one...
		foreach ($this->module_ary as $row_id => $item_ary)
		{
			if (($item_ary['name'] === $id || $item_ary['id'] === (int) $id) && $item_ary['display'] || $item_ary['name'] === $this->p_class . '_' . $id)
			{
				if ($mode === false || $mode === $item_ary['mode'])
				{
					return true;
				}
			}
		}

		return false;
	}

	/**
	* Get parents
	*/
	function get_parents($parent_id, $left_id, $right_id, &$all_parents)
	{
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
	* Not in use
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

		$current_id = $right_id = false;

		// Make sure the module_url has a question mark set, effectively determining the delimiter to use
		$delim = (strpos($module_url, '?') === false) ? '?' : '&amp;';

		$current_depth = 0;
		$linear_offset 	= 'l_block1';
		$tabular_offset = 't_block2';

		// Generate the list of modules, we'll do this in two ways ...
		// 1) In a linear fashion
		// 2) In a combined tabbed + linear fashion ... tabs for the categories
		//    and a linear list for subcategories/items
		foreach ($this->module_ary as $row_id => $item_ary)
		{
			// Skip hidden modules
			if (!$item_ary['display'])
			{
				continue;
			}

			// Skip branch
			if ($right_id !== false)
			{
				if ($item_ary['left'] < $right_id)
				{
					continue;
				}

				$right_id = false;
			}

			// Category with no members on their way down (we have to check every level)
			if (!$item_ary['name'])
			{
				$empty_category = true;

				// We go through the branch and look for an activated module
				foreach (array_slice($this->module_ary, $row_id + 1) as $temp_row)
				{
					if ($temp_row['left'] > $item_ary['left'] && $temp_row['left'] < $item_ary['right'])
					{
						// Module there and displayed?
						if ($temp_row['name'] && $temp_row['display'])
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
					$right_id = $item_ary['right'];
					continue;
				}
			}

			// Select first id we can get
			if (!$current_id && (isset($this->module_cache['parents'][$item_ary['id']]) || $item_ary['id'] == $this->p_id))
			{
				$current_id = $item_ary['id'];
			}

			$depth = $item_ary['depth'];

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

			$u_title = $module_url . $delim . 'i=';
			// if the item has a name use it, else use its id
			if (empty($item_ary['name']))
			{
				$u_title .= $item_ary['id'];
			}
			else
			{
				// if the category has a name, then use it.
				$u_title .= $this->get_module_identifier($item_ary['name']);
			}
			// If the item is not a category append the mode
			if (!$item_ary['cat'])
			{
				if ($item_ary['is_duplicate'])
				{
					$u_title .= '&amp;icat=' . $current_id;
				}
				$u_title .= '&amp;mode=' . $item_ary['mode'];
			}

			// Was not allowed in categories before - /*!$item_ary['cat'] && */
			$u_title .= (isset($item_ary['url_extra']) && $item_ary['url_extra']) ? '&amp;' . $item_ary['url_extra'] : '';

			// Only output a categories items if it's currently selected
			if (!$depth || ($depth && (in_array($item_ary['parent'], array_values($this->module_cache['parents'])) || $item_ary['parent'] == $this->p_parent)))
			{
				$use_tabular_offset = (!$depth) ? 't_block1' : $tabular_offset;

				$tpl_ary = array(
					'L_TITLE'		=> $item_ary['lang'],
					'S_SELECTED'	=> (isset($this->module_cache['parents'][$item_ary['id']]) || $item_ary['id'] == $this->p_id) ? true : false,
					'U_TITLE'		=> $u_title
				);

				if (isset($this->module_cache['parents'][$item_ary['id']]) || $item_ary['id'] == $this->p_id)
				{
					$template->assign_block_vars('navlinks', array(
						'BREADCRUMB_NAME'	=> $item_ary['lang'],
						'U_BREADCRUMB'		=> $u_title,
					));
				}

				$template->assign_block_vars($use_tabular_offset, array_merge($tpl_ary, array_change_key_case($item_ary, CASE_UPPER)));
			}

			$tpl_ary = array(
				'L_TITLE'		=> $item_ary['lang'],
				'S_SELECTED'	=> (isset($this->module_cache['parents'][$item_ary['id']]) || $item_ary['id'] == $this->p_id) ? true : false,
				'U_TITLE'		=> $u_title
			);

			$template->assign_block_vars($linear_offset, array_merge($tpl_ary, array_change_key_case($item_ary, CASE_UPPER)));

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
	*
	* @param string $class module class (acp/mcp/ucp)
	* @param string $name module name (class name of the module, or its basename
	*                     phpbb_ext_foo_acp_bar_module, ucp_zebra or zebra)
	* @param string $mode mode, as passed through to the module
	*
	*/
	function load($class, $name, $mode = false)
	{
		// new modules use the full class names, old ones are always called <class>_<name>, e.g. acp_board
		// in the latter case this function may be called as load('acp', 'board')
		if (!class_exists($name) && substr($name, 0, strlen($class) + 1) !== $class . '_')
		{
			$name = $class . '_' . $name;
		}

		$this->p_class = $class;
		$this->p_name = $name;

		// Set active module to true instead of using the id
		$this->active_module = true;

		$this->load_active($mode);
	}

	/**
	* Display module
	*/
	function display($page_title, $display_online_list = false)
	{
		global $template, $user;

		// Generate the page
		if (defined('IN_ADMIN') && isset($user->data['session_admin']) && $user->data['session_admin'])
		{
			adm_page_header($page_title);
		}
		else
		{
			page_header($page_title, $display_online_list);
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
		foreach ($this->module_ary as $row_id => $item_ary)
		{
			if (($item_ary['name'] === $id || $item_ary['name'] === $this->p_class . '_' . $id || $item_ary['id'] === (int) $id) && (!$mode || $item_ary['mode'] === $mode))
			{
				$this->module_ary[$row_id]['display'] = (int) $display;
			}
		}
	}

	/**
	* Add custom MOD info language file
	*/
	function add_mod_info($module_class)
	{
		global $config, $user, $phpEx, $phpbb_extension_manager;

		$finder = $phpbb_extension_manager->get_finder();

		// We grab the language files from the default, English and user's language.
		// So we can fall back to the other files like we do when using add_lang()
		$default_lang_files = $english_lang_files = $user_lang_files = array();

		// Search for board default language if it's not the user language
		if ($config['default_lang'] != $user->lang_name)
		{
			$default_lang_files = $finder
				->prefix('info_' . strtolower($module_class) . '_')
				->suffix(".$phpEx")
				->extension_directory('/language/' . basename($config['default_lang']))
				->core_path('language/' . basename($config['default_lang']) . '/mods/')
				->find();
		}

		// Search for english, if its not the default or user language
		if ($config['default_lang'] != 'en' && $user->lang_name != 'en')
		{
			$english_lang_files = $finder
				->prefix('info_' . strtolower($module_class) . '_')
				->suffix(".$phpEx")
				->extension_directory('/language/en')
				->core_path('language/en/mods/')
				->find();
		}

		// Find files in the user's language
		$user_lang_files = $finder
			->prefix('info_' . strtolower($module_class) . '_')
			->suffix(".$phpEx")
			->extension_directory('/language/' . $user->lang_name)
			->core_path('language/' . $user->lang_name . '/mods/')
			->find();

		$lang_files = array_merge($english_lang_files, $default_lang_files, $user_lang_files);
		foreach ($lang_files as $lang_file => $ext_name)
		{
			$user->add_lang_ext($ext_name, $lang_file);
		}
	}

	/**
	* Retrieve shortened module basename for legacy basenames (with xcp_ prefix)
	*
	* @param string $basename A module basename
	* @return string The basename if it starts with phpbb_ or the basename with
	*                the current p_class (e.g. acp_) stripped.
	*/
	protected function get_short_name($basename)
	{
		if (substr($basename, 0, 6) === 'phpbb\\' || strpos($basename, '\\') !== false)
		{
			return $basename;
		}

		// strip xcp_ prefix from old classes
		return substr($basename, strlen($this->p_class) + 1);
	}

	/**
	* If the basename contains a \ we don't use that for the URL.
	*
	* Firefox is currently unable to correctly copy a urlencoded \
	* so users will be unable to post links to modules.
	* However we can replace them with dashes and re-replace them later
	*
	* @param	string	$basename	Basename of the module
	* @return		string	Identifier that should be used for
	*						module link creation
	*/
	protected function get_module_identifier($basename)
	{
		if (strpos($basename, '\\') === false)
		{
			return $basename;
		}

		return str_replace('\\', '-', $basename);
	}

	/**
	* Checks whether the given module basename is a correct class name
	*
	* @param string $basename A module basename
	* @return bool True if the basename starts with phpbb_ or (x)cp_, false otherwise
	*/
	protected function is_full_class($basename)
	{
		return (strpos($basename, '\\') !== false || preg_match('/^(ucp|mcp|acp)_/', $basename));
	}
}
