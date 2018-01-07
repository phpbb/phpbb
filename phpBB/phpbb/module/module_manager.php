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

namespace phpbb\module;

use phpbb\module\exception\module_exception;
use phpbb\module\exception\module_not_found_exception;

class module_manager
{
	/**
	 * @var \phpbb\cache\driver\driver_interface
	 */
	protected $cache;

	/**
	 * @var \phpbb\db\driver\driver_interface
	 */
	protected $db;

	/**
	 * @var \phpbb\extension\manager
	 */
	protected $extension_manager;

	/**
	 * @var string
	 */
	protected $modules_table;

	/**
	 * @var string
	 */
	protected $phpbb_root_path;

	/**
	 * @var string
	 */
	protected $php_ext;

	/**
	 * Constructor
	 *
	 * @param \phpbb\cache\driver\driver_interface	$cache				Cache driver
	 * @param \phpbb\db\driver\driver_interface		$db					Database driver
	 * @param \phpbb\extension\manager				$ext_manager		Extension manager
	 * @param string								$modules_table		Module database table's name
	 * @param string								$phpbb_root_path	Path to phpBB's root
	 * @param string								$php_ext			Extension of PHP files
	 */
	public function __construct(\phpbb\cache\driver\driver_interface $cache, \phpbb\db\driver\driver_interface $db, \phpbb\extension\manager $ext_manager, $modules_table, $phpbb_root_path, $php_ext)
	{
		$this->cache				= $cache;
		$this->db					= $db;
		$this->extension_manager	= $ext_manager;
		$this->modules_table		= $modules_table;
		$this->phpbb_root_path		= $phpbb_root_path;
		$this->php_ext				= $php_ext;
	}

	/**
	 * Get row for specified module
	 *
	 * @param int		$module_id		ID of the module
	 * @param string	$module_class	Class of the module (acp, ucp, mcp etc...)
	 *
	 * @return array	Array of data fetched from the database
	 *
	 * @throws \phpbb\module\exception\module_not_found_exception	When there is no module with $module_id
	 */
	public function get_module_row($module_id, $module_class)
	{
		$module_id = (int) $module_id;

		$sql = 'SELECT *
			FROM ' . $this->modules_table . "
			WHERE module_class = '" . $this->db->sql_escape($module_class) . "'
				AND module_id = $module_id";
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$row)
		{
			throw new module_not_found_exception('NO_MODULE');
		}

		return $row;
	}

	/**
	 * Get available module information from module files
	 *
	 * @param string	$module_class		Class of the module (acp, ucp, mcp etc...)
	 * @param string	$module				ID of module
	 * @param bool		$use_all_available	Use all available instead of just all
	 *										enabled extensions
	 *
	 * @return array	Array with module information gathered from module info files.
	 */
	public function get_module_infos($module_class, $module = '', $use_all_available = false)
	{
		$directory = $this->phpbb_root_path . 'includes/' . $module_class . '/info/';
		$fileinfo = array();

		$finder = $this->extension_manager->get_finder($use_all_available);

		$modules = $finder
			->extension_suffix('_module')
			->extension_directory("/$module_class")
			->core_path("includes/$module_class/info/")
			->core_prefix($module_class . '_')
			->get_classes(true);

		foreach ($modules as $cur_module)
		{
			// Skip entries we do not need if we know the module we are
			// looking for
			if ($module && strpos(str_replace('\\', '_', $cur_module), $module) === false && $module !== $cur_module)
			{
				continue;
			}

			$info_class = preg_replace('/_module$/', '_info', $cur_module);

			// If the class does not exist it might be following the old
			// format. phpbb_acp_info_acp_foo needs to be turned into
			// acp_foo_info and the respective file has to be included
			// manually because it does not support auto loading
			$old_info_class_file = str_replace("phpbb_{$module_class}_info_", '', $cur_module);
			$old_info_class = $old_info_class_file . '_info';

			if (class_exists($old_info_class))
			{
				$info_class = $old_info_class;
			}
			else if (!class_exists($info_class))
			{
				$info_class = $old_info_class;

				// need to check class exists again because previous checks triggered autoloading
				if (!class_exists($info_class) && file_exists($directory . $old_info_class_file . '.' . $this->php_ext))
				{
					include($directory . $old_info_class_file . '.' . $this->php_ext);
				}
			}

			if (class_exists($info_class))
			{
				$info = new $info_class();
				$module_info = $info->module();

				$main_class = (isset($module_info['filename'])) ? $module_info['filename'] : $cur_module;

				$fileinfo[$main_class] = $module_info;
			}
		}

		ksort($fileinfo);

		return $fileinfo;
	}

	/**
	 * Get module branch
	 *
	 * @param int		$module_id		ID of the module
	 * @param string	$module_class	Class of the module (acp, ucp, mcp etc...)
	 * @param string	$type			Type of branch (Expected values: all, parents or children)
	 * @param bool		$include_module	Whether or not to include the specified module with $module_id
	 *
	 * @return array	Returns an array containing the modules in the specified branch type.
	 */
	public function get_module_branch($module_id, $module_class, $type = 'all', $include_module = true)
	{
		$module_id = (int) $module_id;

		switch ($type)
		{
			case 'parents':
				$condition = 'm1.left_id BETWEEN m2.left_id AND m2.right_id';
			break;

			case 'children':
				$condition = 'm2.left_id BETWEEN m1.left_id AND m1.right_id';
			break;

			default:
				$condition = 'm2.left_id BETWEEN m1.left_id AND m1.right_id OR m1.left_id BETWEEN m2.left_id AND m2.right_id';
			break;
		}

		$rows = array();

		$sql = 'SELECT m2.*
			FROM ' . $this->modules_table . ' m1
			LEFT JOIN ' . $this->modules_table . " m2 ON ($condition)
			WHERE m1.module_class = '" . $this->db->sql_escape($module_class) . "'
				AND m2.module_class = '" . $this->db->sql_escape($module_class) . "'
				AND m1.module_id = $module_id
			ORDER BY m2.left_id";
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			if (!$include_module && $row['module_id'] == $module_id)
			{
				continue;
			}

			$rows[] = $row;
		}
		$this->db->sql_freeresult($result);

		return $rows;
	}

	/**
	 * Remove modules cache file
	 *
	 * @param string	$module_class	Class of the module (acp, ucp, mcp etc...)
	 */
	public function remove_cache_file($module_class)
	{
		// Sanitise for future path use, it's escaped as appropriate for queries
		$cache_class = str_replace(array('.', '/', '\\'), '', basename($module_class));
		$this->cache->destroy('_modules_' . $cache_class);
		$this->cache->destroy('sql', $this->modules_table);
	}

	/**
	 * Update/Add module
	 *
	 * @param array	&$module_data	The module data
	 *
	 * @throws \phpbb\module\exception\module_not_found_exception	When parent module or the category is not exist
	 */
	public function update_module_data(&$module_data)
	{
		if (!isset($module_data['module_id']))
		{
			// no module_id means we're creating a new category/module
			if ($module_data['parent_id'])
			{
				$sql = 'SELECT left_id, right_id
					FROM ' . $this->modules_table . "
					WHERE module_class = '" . $this->db->sql_escape($module_data['module_class']) . "'
						AND module_id = " . (int) $module_data['parent_id'];
				$result = $this->db->sql_query($sql);
				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				if (!$row)
				{
					throw new module_not_found_exception('PARENT_NOT_EXIST');
				}

				// Workaround
				$row['left_id'] = (int) $row['left_id'];
				$row['right_id'] = (int) $row['right_id'];

				$sql = 'UPDATE ' . $this->modules_table . "
					SET left_id = left_id + 2, right_id = right_id + 2
					WHERE module_class = '" . $this->db->sql_escape($module_data['module_class']) . "'
						AND left_id > {$row['right_id']}";
				$this->db->sql_query($sql);

				$sql = 'UPDATE ' . $this->modules_table . "
					SET right_id = right_id + 2
					WHERE module_class = '" . $this->db->sql_escape($module_data['module_class']) . "'
						AND {$row['left_id']} BETWEEN left_id AND right_id";
				$this->db->sql_query($sql);

				$module_data['left_id'] = (int) $row['right_id'];
				$module_data['right_id'] = (int) $row['right_id'] + 1;
			}
			else
			{
				$sql = 'SELECT MAX(right_id) AS right_id
					FROM ' . $this->modules_table . "
					WHERE module_class = '" . $this->db->sql_escape($module_data['module_class']) . "'";
				$result = $this->db->sql_query($sql);
				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				$module_data['left_id'] = (int) $row['right_id'] + 1;
				$module_data['right_id'] = (int) $row['right_id'] + 2;
			}

			$sql = 'INSERT INTO ' . $this->modules_table . ' ' . $this->db->sql_build_array('INSERT', $module_data);
			$this->db->sql_query($sql);

			$module_data['module_id'] = $this->db->sql_nextid();
		}
		else
		{
			$row = $this->get_module_row($module_data['module_id'], $module_data['module_class']);

			if ($module_data['module_basename'] && !$row['module_basename'])
			{
				// we're turning a category into a module
				$branch = $this->get_module_branch($module_data['module_id'], $module_data['module_class'], 'children', false);

				if (count($branch))
				{
					throw new module_not_found_exception('NO_CATEGORY_TO_MODULE');
				}
			}

			if ($row['parent_id'] != $module_data['parent_id'])
			{
				$this->move_module($module_data['module_id'], $module_data['parent_id'], $module_data['module_class']);
			}

			$update_ary = $module_data;
			unset($update_ary['module_id']);

			$sql = 'UPDATE ' . $this->modules_table . '
				SET ' . $this->db->sql_build_array('UPDATE', $update_ary) . "
				WHERE module_class = '" . $this->db->sql_escape($module_data['module_class']) . "'
					AND module_id = " . (int) $module_data['module_id'];
			$this->db->sql_query($sql);
		}
	}

	/**
	 * Move module around the tree
	 *
	 * @param int		$from_module_id	ID of the current parent module
	 * @param int		$to_parent_id	ID of the target parent module
	 * @param string	$module_class	Class of the module (acp, ucp, mcp etc...)
	 *
	 * @throws \phpbb\module\exception\module_not_found_exception	If the module specified to move modules from does not
	 * 																have any children.
	 */
	public function move_module($from_module_id, $to_parent_id, $module_class)
	{
		$moved_modules = $this->get_module_branch($from_module_id, $module_class, 'children');

		if (empty($moved_modules))
		{
			throw new module_not_found_exception();
		}

		$from_data = $moved_modules[0];
		$diff = count($moved_modules) * 2;

		$moved_ids = array();
		for ($i = 0, $size = count($moved_modules); $i < $size; ++$i)
		{
			$moved_ids[] = $moved_modules[$i]['module_id'];
		}

		// Resync parents
		$sql = 'UPDATE ' . $this->modules_table . "
			SET right_id = right_id - $diff
			WHERE module_class = '" . $this->db->sql_escape($module_class) . "'
				AND left_id < " . (int) $from_data['right_id'] . '
				AND right_id > ' . (int) $from_data['right_id'];
		$this->db->sql_query($sql);

		// Resync righthand side of tree
		$sql = 'UPDATE ' . $this->modules_table . "
			SET left_id = left_id - $diff, right_id = right_id - $diff
			WHERE module_class = '" . $this->db->sql_escape($module_class) . "'
				AND left_id > " . (int) $from_data['right_id'];
		$this->db->sql_query($sql);

		if ($to_parent_id > 0)
		{
			$to_data = $this->get_module_row($to_parent_id, $module_class);

			// Resync new parents
			$sql = 'UPDATE ' . $this->modules_table . "
				SET right_id = right_id + $diff
				WHERE module_class = '" . $this->db->sql_escape($module_class) . "'
					AND " . (int) $to_data['right_id'] . ' BETWEEN left_id AND right_id
					AND ' . $this->db->sql_in_set('module_id', $moved_ids, true);
			$this->db->sql_query($sql);

			// Resync the righthand side of the tree
			$sql = 'UPDATE ' . $this->modules_table . "
				SET left_id = left_id + $diff, right_id = right_id + $diff
				WHERE module_class = '" . $this->db->sql_escape($module_class) . "'
					AND left_id > " . (int) $to_data['right_id'] . '
					AND ' . $this->db->sql_in_set('module_id', $moved_ids, true);
			$this->db->sql_query($sql);

			// Resync moved branch
			$to_data['right_id'] += $diff;
			if ($to_data['right_id'] > $from_data['right_id'])
			{
				$diff = '+ ' . ($to_data['right_id'] - $from_data['right_id'] - 1);
			}
			else
			{
				$diff = '- ' . abs($to_data['right_id'] - $from_data['right_id'] - 1);
			}
		}
		else
		{
			$sql = 'SELECT MAX(right_id) AS right_id
				FROM ' . $this->modules_table . "
				WHERE module_class = '" . $this->db->sql_escape($module_class) . "'
					AND " . $this->db->sql_in_set('module_id', $moved_ids, true);
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			$diff = '+ ' . (int) ($row['right_id'] - $from_data['left_id'] + 1);
		}

		$sql = 'UPDATE ' . $this->modules_table . "
			SET left_id = left_id $diff, right_id = right_id $diff
			WHERE module_class = '" . $this->db->sql_escape($module_class) . "'
				AND " . $this->db->sql_in_set('module_id', $moved_ids);
		$this->db->sql_query($sql);
	}

	/**
	 * Remove module from tree
	 *
	 * @param int		$module_id		ID of the module to delete
	 * @param string	$module_class	Class of the module (acp, ucp, mcp etc...)
	 *
	 * @throws \phpbb\module\exception\module_exception	When the specified module cannot be removed
	 */
	public function delete_module($module_id, $module_class)
	{
		$module_id = (int) $module_id;

		$row = $this->get_module_row($module_id, $module_class);

		$branch = $this->get_module_branch($module_id, $module_class, 'children', false);

		if (count($branch))
		{
			throw new module_exception('CANNOT_REMOVE_MODULE');
		}

		// If not move
		$diff = 2;
		$sql = 'DELETE FROM ' . $this->modules_table . "
			WHERE module_class = '" . $this->db->sql_escape($module_class) . "'
				AND module_id = $module_id";
		$this->db->sql_query($sql);

		$row['right_id'] = (int) $row['right_id'];
		$row['left_id'] = (int) $row['left_id'];

		// Resync tree
		$sql = 'UPDATE ' . $this->modules_table . "
			SET right_id = right_id - $diff
			WHERE module_class = '" . $this->db->sql_escape($module_class) . "'
				AND left_id < {$row['right_id']} AND right_id > {$row['right_id']}";
		$this->db->sql_query($sql);

		$sql = 'UPDATE ' . $this->modules_table . "
			SET left_id = left_id - $diff, right_id = right_id - $diff
			WHERE module_class = '" . $this->db->sql_escape($module_class) . "'
				AND left_id > {$row['right_id']}";
		$this->db->sql_query($sql);
	}

	/**
	 * Move module position by $steps up/down
	 *
	 * @param array		$module_row		Array of module data
	 * @param string	$module_class	Class of the module (acp, ucp, mcp etc...)
	 * @param string	$action			Direction of moving (valid values: move_up or move_down)
	 * @param int		$steps			Number of steps to move module
	 *
	 * @return string	Returns the language name of the module
	 *
	 * @throws \phpbb\module\exception\module_not_found_exception	When the specified module does not exists
	 */
	public function move_module_by($module_row, $module_class, $action = 'move_up', $steps = 1)
	{
		/**
		 * Fetch all the siblings between the module's current spot
		 * and where we want to move it to. If there are less than $steps
		 * siblings between the current spot and the target then the
		 * module will move as far as possible
		 */
		$sql = 'SELECT module_id, left_id, right_id, module_langname
			FROM ' . $this->modules_table . "
			WHERE module_class = '" . $this->db->sql_escape($module_class) . "'
				AND parent_id = " . (int) $module_row['parent_id'] . '
				AND ' . (($action == 'move_up') ? 'right_id < ' . (int) $module_row['right_id'] . ' ORDER BY right_id DESC' : 'left_id > ' . (int) $module_row['left_id'] . ' ORDER BY left_id ASC');
		$result = $this->db->sql_query_limit($sql, $steps);

		$target = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$target = $row;
		}
		$this->db->sql_freeresult($result);

		if (!count($target))
		{
			// The module is already on top or bottom
			throw new module_not_found_exception();
		}

		/**
		 * $left_id and $right_id define the scope of the nodes that are affected by the move.
		 * $diff_up and $diff_down are the values to substract or add to each node's left_id
		 * and right_id in order to move them up or down.
		 * $move_up_left and $move_up_right define the scope of the nodes that are moving
		 * up. Other nodes in the scope of ($left_id, $right_id) are considered to move down.
		 */
		if ($action == 'move_up')
		{
			$left_id = (int) $target['left_id'];
			$right_id = (int) $module_row['right_id'];

			$diff_up = (int) ($module_row['left_id'] - $target['left_id']);
			$diff_down = (int) ($module_row['right_id'] + 1 - $module_row['left_id']);

			$move_up_left = (int) $module_row['left_id'];
			$move_up_right = (int) $module_row['right_id'];
		}
		else
		{
			$left_id = (int) $module_row['left_id'];
			$right_id = (int) $target['right_id'];

			$diff_up = (int) ($module_row['right_id'] + 1 - $module_row['left_id']);
			$diff_down = (int) ($target['right_id'] - $module_row['right_id']);

			$move_up_left = (int) ($module_row['right_id'] + 1);
			$move_up_right = (int) $target['right_id'];
		}

		// Now do the dirty job
		$sql = 'UPDATE ' . $this->modules_table . "
			SET left_id = left_id + CASE
				WHEN left_id BETWEEN {$move_up_left} AND {$move_up_right} THEN -{$diff_up}
				ELSE {$diff_down}
			END,
			right_id = right_id + CASE
				WHEN right_id BETWEEN {$move_up_left} AND {$move_up_right} THEN -{$diff_up}
				ELSE {$diff_down}
			END
			WHERE module_class = '" . $this->db->sql_escape($module_class) . "'
				AND left_id BETWEEN {$left_id} AND {$right_id}
				AND right_id BETWEEN {$left_id} AND {$right_id}";
		$this->db->sql_query($sql);

		$this->remove_cache_file($module_class);

		return $target['module_langname'];
	}
}
