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

namespace phpbb\db\migration\tool;

/**
* Migration module management tool
*/
class module implements \phpbb\db\migration\tool\tool_interface
{
	/** @var \phpbb\cache\service */
	protected $cache;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\user */
	protected $user;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $php_ext;

	/** @var string */
	protected $modules_table;

	/**
	* Constructor
	*
	* @param \phpbb\db\driver\driver_interface $db
	* @param \phpbb\cache\service $cache
	* @param \phpbb\user $user
	* @param string $phpbb_root_path
	* @param string $php_ext
	* @param string $modules_table
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\cache\service $cache, \phpbb\user $user, $phpbb_root_path, $php_ext, $modules_table)
	{
		$this->db = $db;
		$this->cache = $cache;
		$this->user = $user;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		$this->modules_table = $modules_table;
	}

	/**
	* {@inheritdoc}
	*/
	public function get_name()
	{
		return 'module';
	}

	/**
	* Module Exists
	*
	* Check if a module exists
	*
	* @param string $class The module class(acp|mcp|ucp)
	* @param int|string|bool $parent The parent module_id|module_langname (0 for no parent).
	*		Use false to ignore the parent check and check class wide.
	* @param int|string $module The module_id|module_langname you would like to
	* 		check for to see if it exists
	* @return bool true/false if module exists
	*/
	public function exists($class, $parent, $module)
	{
		// the main root directory should return true
		if (!$module)
		{
			return true;
		}

		$parent_sql = '';
		if ($parent !== false)
		{
			// Allows '' to be sent as 0
			$parent = $parent ?: 0;

			if (!is_numeric($parent))
			{
				$sql = 'SELECT module_id
					FROM ' . $this->modules_table . "
					WHERE module_langname = '" . $this->db->sql_escape($parent) . "'
						AND module_class = '" . $this->db->sql_escape($class) . "'";
				$result = $this->db->sql_query($sql);
				$module_id = $this->db->sql_fetchfield('module_id');
				$this->db->sql_freeresult($result);

				if (!$module_id)
				{
					return false;
				}

				$parent_sql = 'AND parent_id = ' . (int) $module_id;
			}
			else
			{
				$parent_sql = 'AND parent_id = ' . (int) $parent;
			}
		}

		$sql = 'SELECT module_id
			FROM ' . $this->modules_table . "
			WHERE module_class = '" . $this->db->sql_escape($class) . "'
				$parent_sql
				AND " . ((is_numeric($module)) ? 'module_id = ' . (int) $module : "module_langname = '" . $this->db->sql_escape($module) . "'");
		$result = $this->db->sql_query($sql);
		$module_id = $this->db->sql_fetchfield('module_id');
		$this->db->sql_freeresult($result);

		if ($module_id)
		{
			return true;
		}

		return false;
	}

	/**
	* Module Add
	*
	* Add a new module
	*
	* @param string $class The module class(acp|mcp|ucp)
	* @param int|string $parent The parent module_id|module_langname (0 for no parent)
	* @param array $data an array of the data on the new \module.
	* 	This can be setup in two different ways.
	*	1. The "manual" way.  For inserting a category or one at a time.
	*		It will be merged with the base array shown a bit below,
	*			but at the least requires 'module_langname' to be sent, and,
	*			if you want to create a module (instead of just a category) you must
	*			send module_basename and module_mode.
	*		array(
	*			'module_enabled'	=> 1,
	*			'module_display'	=> 1,
	*	   		'module_basename'	=> '',
	*			'module_class'		=> $class,
	*	   		'parent_id'			=> (int) $parent,
	*			'module_langname'	=> '',
	*	   		'module_mode'		=> '',
	*	   		'module_auth'		=> '',
	*		)
	*	2. The "automatic" way.  For inserting multiple at a time based on the
	*			specs in the info file for the module(s).  For this to work the
	*			modules must be correctly setup in the info file.
	*		An example follows (this would insert the settings, log, and flag
	*			modes from the includes/acp/info/acp_asacp.php file):
	* 		array(
	* 			'module_basename'	=> 'asacp',
	* 			'modes'				=> array('settings', 'log', 'flag'),
	* 		)
	* 		Optionally you may not send 'modes' and it will insert all of the
	* 			modules in that info file.
	* 	path, specify that here
	* @return null
	* @throws \phpbb\db\migration\exception
	*/
	public function add($class, $parent = 0, $data = array())
	{
		// Allows '' to be sent as 0
		$parent = $parent ?: 0;

		// allow sending the name as a string in $data to create a category
		if (!is_array($data))
		{
			$data = array('module_langname' => $data);
		}

		if (!isset($data['module_langname']))
		{
			// The "automatic" way
			$basename = (isset($data['module_basename'])) ? $data['module_basename'] : '';
			$module = $this->get_module_info($class, $basename);

			$result = '';
			foreach ($module['modes'] as $mode => $module_info)
			{
				if (!isset($data['modes']) || in_array($mode, $data['modes']))
				{
					$new_module = array(
						'module_basename'	=> $basename,
						'module_langname'	=> $module_info['title'],
						'module_mode'		=> $mode,
						'module_auth'		=> $module_info['auth'],
						'module_display'	=> (isset($module_info['display'])) ? $module_info['display'] : true,
						'before'			=> (isset($module_info['before'])) ? $module_info['before'] : false,
						'after'				=> (isset($module_info['after'])) ? $module_info['after'] : false,
					);

					// Run the "manual" way with the data we've collected.
					$this->add($class, $parent, $new_module);
				}
			}

			return;
		}

		// The "manual" way
		if (!is_numeric($parent))
		{
			$sql = 'SELECT module_id
				FROM ' . $this->modules_table . "
				WHERE module_langname = '" . $this->db->sql_escape($parent) . "'
					AND module_class = '" . $this->db->sql_escape($class) . "'";
			$result = $this->db->sql_query($sql);
			$module_id = $this->db->sql_fetchfield('module_id');
			$this->db->sql_freeresult($result);

			if (!$module_id)
			{
				throw new \phpbb\db\migration\exception('MODULE_NOT_EXIST', $parent);
			}

			$parent = $data['parent_id'] = $module_id;
		}
		else if (!$this->exists($class, false, $parent))
		{
			throw new \phpbb\db\migration\exception('MODULE_NOT_EXIST', $parent);
		}

		if ($this->exists($class, $parent, $data['module_langname']))
		{
			return;
		}

		if (!class_exists('acp_modules'))
		{
			include($this->phpbb_root_path . 'includes/acp/acp_modules.' . $this->php_ext);
			$this->user->add_lang('acp/modules');
		}
		$acp_modules = new \acp_modules();

		$module_data = array(
			'module_enabled'	=> (isset($data['module_enabled'])) ? $data['module_enabled'] : 1,
			'module_display'	=> (isset($data['module_display'])) ? $data['module_display'] : 1,
			'module_basename'	=> (isset($data['module_basename'])) ? $data['module_basename'] : '',
			'module_class'		=> $class,
			'parent_id'			=> (int) $parent,
			'module_langname'	=> (isset($data['module_langname'])) ? $data['module_langname'] : '',
			'module_mode'		=> (isset($data['module_mode'])) ? $data['module_mode'] : '',
			'module_auth'		=> (isset($data['module_auth'])) ? $data['module_auth'] : '',
		);
		$result = $acp_modules->update_module_data($module_data, true);

		// update_module_data can either return a string or an empty array...
		if (is_string($result))
		{
			// Error
			throw new \phpbb\db\migration\exception('MODULE_ERROR', $result);
		}
		else
		{
			// Success
			$module_log_name = ((isset($this->user->lang[$data['module_langname']])) ? $this->user->lang[$data['module_langname']] : $data['module_langname']);
			add_log('admin', 'LOG_MODULE_ADD', $module_log_name);

			// Move the module if requested above/below an existing one
			if (isset($data['before']) && $data['before'])
			{
				$sql = 'SELECT left_id
					FROM ' . $this->modules_table . "
					WHERE module_class = '" . $this->db->sql_escape($class) . "'
						AND parent_id = " . (int) $parent . "
						AND module_langname = '" . $this->db->sql_escape($data['before']) . "'";
				$this->db->sql_query($sql);
				$to_left = (int) $this->db->sql_fetchfield('left_id');

				$sql = 'UPDATE ' . $this->modules_table . "
					SET left_id = left_id + 2, right_id = right_id + 2
					WHERE module_class = '" . $this->db->sql_escape($class) . "'
						AND left_id >= $to_left
						AND left_id < {$module_data['left_id']}";
				$this->db->sql_query($sql);

				$sql = 'UPDATE ' . $this->modules_table . "
					SET left_id = $to_left, right_id = " . ($to_left + 1) . "
					WHERE module_class = '" . $this->db->sql_escape($class) . "'
						AND module_id = {$module_data['module_id']}";
				$this->db->sql_query($sql);
			}
			else if (isset($data['after']) && $data['after'])
			{
				$sql = 'SELECT right_id
					FROM ' . $this->modules_table . "
					WHERE module_class = '" . $this->db->sql_escape($class) . "'
						AND parent_id = " . (int) $parent . "
						AND module_langname = '" . $this->db->sql_escape($data['after']) . "'";
				$this->db->sql_query($sql);
				$to_right = (int) $this->db->sql_fetchfield('right_id');

				$sql = 'UPDATE ' . $this->modules_table . "
					SET left_id = left_id + 2, right_id = right_id + 2
					WHERE module_class = '" . $this->db->sql_escape($class) . "'
						AND left_id >= $to_right
						AND left_id < {$module_data['left_id']}";
				$this->db->sql_query($sql);

				$sql = 'UPDATE ' . $this->modules_table . '
					SET left_id = ' . ($to_right + 1) . ', right_id = ' . ($to_right + 2) . "
					WHERE module_class = '" . $this->db->sql_escape($class) . "'
						AND module_id = {$module_data['module_id']}";
				$this->db->sql_query($sql);
			}
		}

		// Clear the Modules Cache
		$this->cache->destroy("_modules_$class");
	}

	/**
	* Module Remove
	*
	* Remove a module
	*
	* @param string $class The module class(acp|mcp|ucp)
	* @param int|string|bool $parent The parent module_id|module_langname(0 for no parent).
	* 	Use false to ignore the parent check and check class wide.
	* @param int|string $module The module id|module_langname
	* 	specify that here
	* @return null
	* @throws \phpbb\db\migration\exception
	*/
	public function remove($class, $parent = 0, $module = '')
	{
		// Imitation of module_add's "automatic" and "manual" method so the uninstaller works from the same set of instructions for umil_auto
		if (is_array($module))
		{
			if (isset($module['module_langname']))
			{
				// Manual Method
				return $this->remove($class, $parent, $module['module_langname']);
			}

			// Failed.
			if (!isset($module['module_basename']))
			{
				throw new \phpbb\db\migration\exception('MODULE_NOT_EXIST');
			}

			// Automatic method
			$basename = $module['module_basename'];
			$module_info = $this->get_module_info($class, $basename);

			foreach ($module_info['modes'] as $mode => $info)
			{
				if (!isset($module['modes']) || in_array($mode, $module['modes']))
				{
					$this->remove($class, $parent, $info['title']);
				}
			}
		}
		else
		{
			if (!$this->exists($class, $parent, $module))
			{
				return;
			}

			$parent_sql = '';
			if ($parent !== false)
			{
				// Allows '' to be sent as 0
				$parent = ($parent) ?: 0;

				if (!is_numeric($parent))
				{
					$sql = 'SELECT module_id
						FROM ' . $this->modules_table . "
						WHERE module_langname = '" . $this->db->sql_escape($parent) . "'
							AND module_class = '" . $this->db->sql_escape($class) . "'";
					$result = $this->db->sql_query($sql);
					$module_id = $this->db->sql_fetchfield('module_id');
					$this->db->sql_freeresult($result);

					// we know it exists from the module_exists check
					$parent_sql = 'AND parent_id = ' . (int) $module_id;
				}
				else
				{
					$parent_sql = 'AND parent_id = ' . (int) $parent;
				}
			}

			$module_ids = array();
			if (!is_numeric($module))
			{
				$sql = 'SELECT module_id
					FROM ' . $this->modules_table . "
					WHERE module_langname = '" . $this->db->sql_escape($module) . "'
						AND module_class = '" . $this->db->sql_escape($class) . "'
						$parent_sql";
				$result = $this->db->sql_query($sql);
				while ($module_id = $this->db->sql_fetchfield('module_id'))
				{
					$module_ids[] = (int) $module_id;
				}
				$this->db->sql_freeresult($result);
			}
			else
			{
				$module_ids[] = (int) $module;
			}

			if (!class_exists('acp_modules'))
			{
				include($this->phpbb_root_path . 'includes/acp/acp_modules.' . $this->php_ext);
				$this->user->add_lang('acp/modules');
			}
			$acp_modules = new \acp_modules();
			$acp_modules->module_class = $class;

			foreach ($module_ids as $module_id)
			{
				$result = $acp_modules->delete_module($module_id);
				if (!empty($result))
				{
					return;
				}
			}

			$this->cache->destroy("_modules_$class");
		}
	}

	/**
	* {@inheritdoc}
	*/
	public function reverse()
	{
		$arguments = func_get_args();
		$original_call = array_shift($arguments);

		$call = false;
		switch ($original_call)
		{
			case 'add':
				$call = 'remove';
			break;

			case 'remove':
				$call = 'add';
			break;
		}

		if ($call)
		{
			return call_user_func_array(array(&$this, $call), $arguments);
		}
	}

	/**
	* Wrapper for \acp_modules::get_module_infos()
	*
	* @param string $class Module Class
	* @param string $basename Module Basename
	* @return array Module Information
	* @throws \phpbb\db\migration\exception
	*/
	protected function get_module_info($class, $basename)
	{
		if (!class_exists('acp_modules'))
		{
			include($this->phpbb_root_path . 'includes/acp/acp_modules.' . $this->php_ext);
			$this->user->add_lang('acp/modules');
		}
		$acp_modules = new \acp_modules();
		$module = $acp_modules->get_module_infos($basename, $class, true);

		if (empty($module))
		{
			throw new \phpbb\db\migration\exception('MODULE_INFO_FILE_NOT_EXIST', $class, $basename);
		}

		return array_pop($module);
	}
}
