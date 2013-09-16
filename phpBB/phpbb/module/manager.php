<?php

class phpbb_module_manager
{
	// @todo move somewhere...
	protected $module_categories = array(
		'acp'	=> array(
			'ACP_CAT_GENERAL'		=> array(
				'ACP_QUICK_ACCESS' => array(),
				'ACP_BOARD_CONFIGURATION' => array(),
				'ACP_CLIENT_COMMUNICATION' => array(),
				'ACP_SERVER_CONFIGURATION' => array(),
			),
			'ACP_CAT_FORUMS'		=> array(
				'ACP_MANAGE_FORUMS' => array(),
				'ACP_FORUM_BASED_PERMISSIONS' => array(),
			),
			'ACP_CAT_POSTING'		=> array(
				'ACP_MESSAGES' => array(),
				'ACP_ATTACHMENTS' => array(),
			),
			'ACP_CAT_USERGROUP'		=> array(
				'ACP_CAT_USERS' => array(),
				'ACP_GROUPS' => array(),
				'ACP_USER_SECURITY' => array(),
			),
			'ACP_CAT_PERMISSIONS'	=> array(
				'ACP_GLOBAL_PERMISSIONS' => array(),
				'ACP_FORUM_BASED_PERMISSIONS' => array(),
				'ACP_PERMISSION_ROLES' => array(),
				'ACP_PERMISSION_MASKS' => array(),
			),
			'ACP_CAT_CUSTOMISE'		=> array(
				'ACP_STYLE_MANAGEMENT' => array(),
				'ACP_EXTENSION_MANAGEMENT' => array(),
				'ACP_LANGUAGE',
			),
			'ACP_CAT_MAINTENANCE'	=> array(
				'ACP_FORUM_LOGS' => array(),
				'ACP_CAT_DATABASE' => array(),
			),
			'ACP_CAT_SYSTEM'		=> array(
				'ACP_AUTOMATION' => array(),
				'ACP_GENERAL_TASKS' => array(),
				'ACP_MODULE_MANAGEMENT' => array(),
			),
			'ACP_CAT_DOT_MODS'		=> array(),
		),
		'mcp'	=> array(
			'MCP_MAIN'			=> array(),
			'MCP_QUEUE'			=> array(),
			'MCP_REPORTS'		=> array(),
			'MCP_NOTES'			=> array(),
			'MCP_WARN'			=> array(),
			'MCP_LOGS'			=> array(),
			'MCP_BAN'			=> array(),
		),
		'ucp'	=> array(
			'UCP_MAIN'			=> array(),
			'UCP_PROFILE'		=> array(),
			'UCP_PREFS'			=> array(),
			'UCP_PM'			=> array(),
			'UCP_USERGROUPS'	=> array(),
			'UCP_ZEBRA'			=> array(),
		),
	);

	/**
	* Get a tree of modules
	*/
	public function get_tree($class)
	{
		global $phpbb_root_path, $phpEx;

		$tree = $this->module_categories[$class];

		if (!class_exists('acp_modules'))
		{
			include($phpbb_root_path . 'includes/acp/acp_modules.' . $phpEx);
		}

		$acp_modules = new acp_modules();
		$modules = $acp_modules->get_module_infos('', $class);

		foreach ($modules as $module)
		{
			foreach ($module['modes'] as $mode_name => $mode)
			{
				foreach ($mode['cat'] as $cat)
				{
					$this->add_module_to_tree($tree, $cat, array(
						'filename'	=> $module['filename'],
						'mode'		=> $mode_name,
						'title'		=> $mode['title'],
						'auth'		=> $mode['auth'],
					));
				}
			}
		}

		return $tree;
	}

	/**
	* Get a flat list of modules (emulate DB output)
	*/
	public function get_flat($class)
	{
		$tree = $this->get_tree($class);

		$left_id = 0;
		$module_id = 142;
		$list = $this->_get_flat($tree, $class, $module_id, $left_id);
		foreach ($list as $id => &$row)
		{
			foreach ($row as $name => &$data)
			{
				$data = (string) $data;
			}
		}

		return $list;
	}

	protected function _get_flat($tree, $class, &$module_id, &$left_id, $parent_id = 0)
	{
		$list = array();

		foreach ($tree as $parent => $children)
		{
			if (isset($children['title']))
			{
				// a module
				$list[++$module_id] = array(
					'module_id'			=> $module_id,
					'module_enabled'	=> true,
					'module_display'	=> true,
					'module_basename'	=> $children['filename'],
					'module_class'		=> $class,
					'parent_id'			=> $parent_id,
					'left_id'			=> ++$left_id,
					'right_id'			=> ++$left_id,
					'module_langname'	=> $children['title'],
					'module_mode'		=> $children['mode'],
					'module_auth'		=> $children['auth'],
				);
			}
			else
			{
				// Category
				$cat_module_id = ++$module_id;
				$cat_left_id = ++$left_id;

				$child_rows = $this->_get_flat($children, $class, $module_id, $left_id, $module_id);

				$list[$cat_module_id] = array(
					'module_id'			=> $cat_module_id,
					'module_enabled'	=> true,
					'module_display'	=> true,
					'module_basename'	=> '',
					'module_class'		=> $class,
					'parent_id'			=> $parent_id,
					'left_id'			=> $cat_left_id,
					'right_id'			=> ++$left_id,
					'module_langname'	=> $parent,
					'module_mode'		=> '',
					'module_auth'		=> '',
				);

				$list = array_merge($list, $child_rows);
			}
		}

		return $list;
	}

	protected function add_module_to_tree(&$tree, $parent_name, $module)
	{
		foreach ($tree as $parent => &$children)
		{
			if (isset($children['title']))
			{
				// a module
			}
			else
			{
				// a category
				if ($parent === $parent_name)
				{
					$children[] = $module;

					return true;
				}

				if (($found = $this->add_module_to_tree($children, $parent_name, $module)) !== false)
				{
					return $found;
				}
			}
		}

		// not found
		return false;
	}
}
