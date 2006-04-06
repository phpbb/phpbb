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
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* @package phpBB3
* ACP Permission/Auth class
*/
class auth_admin extends auth
{
	var $option_ids = array();

	/**
	* Init auth settings
	*/
	function auth_admin()
	{
		global $db, $cache;

		if (($this->acl_options = $cache->get('acl_options')) === false)
		{
			$sql = 'SELECT auth_option, is_global, is_local
				FROM ' . ACL_OPTIONS_TABLE . '
				ORDER BY auth_option_id';
			$result = $db->sql_query($sql);

			$global = $local = 0;
			while ($row = $db->sql_fetchrow($result))
			{
				if ($row['is_global'])
				{
					$this->acl_options['global'][$row['auth_option']] = $global++;
				}

				if ($row['is_local'])
				{
					$this->acl_options['local'][$row['auth_option']] = $local++;
				}
			}
			$db->sql_freeresult($result);

			$cache->put('acl_options', $this->acl_options);
		}

		if (!sizeof($this->option_ids))
		{
			$sql = 'SELECT auth_option_id, auth_option
				FROM ' . ACL_OPTIONS_TABLE;
			$result = $db->sql_query($sql);

			$this->option_ids = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$this->option_ids[$row['auth_option']] = $row['auth_option_id'];
			}
			$db->sql_freeresult($result);
		}
	}
	
	/**
	* Get permission mask
	* This function only supports getting permissions of one type (for example a_)
	*
	* @param set|view $mode defines the permissions we get, view gets effective permissions (checking user AND group permissions), set only gets the user or group permission set alone
	* @param mixed $user_id user ids to search for (a user_id or a group_id has to be specified at least)
	* @param mixed $group_id group ids to search for, return group related settings (a user_id or a group_id has to be specified at least)
	* @param mixed $forum_id forum_ids to search for. Defining a forum id also means getting local settings
	* @param string $auth_option the auth_option defines the permission setting to look for (a_ for example)
	* @param local|global $scope the scope defines the permission scope. If local, a forum_id is additionally required
	* @param ACL_NO|ACL_UNSET|ACL_YES $acl_fill defines the mode those permissions not set are getting filled with
	*/
	function get_mask($mode, $user_id = false, $group_id = false, $forum_id = false, $auth_option = false, $scope = false, $acl_fill = ACL_NO)
	{
		global $db;

		$hold_ary = array();

		if ($auth_option === false || $scope === false)
		{
			return array();
		}

		$acl_user_function = ($mode == 'set') ? 'acl_user_raw_data' : 'acl_raw_data';

		if ($forum_id !== false)
		{
			$hold_ary = ($group_id !== false) ? $this->acl_group_raw_data($group_id, $auth_option . '%', $forum_id) : $this->$acl_user_function($user_id, $auth_option . '%', $forum_id);
		}
		else
		{
			$hold_ary = ($group_id !== false) ? $this->acl_group_raw_data($group_id, $auth_option . '%', ($scope == 'global') ? 0 : false) : $this->$acl_user_function($user_id, $auth_option . '%', ($scope == 'global') ? 0 : false);
		}

		// Make sure hold_ary is filled with every setting (prevents missing forums/users/groups)
		$ug_id = ($group_id !== false) ? ((!is_array($group_id)) ? array($group_id) : $group_id) : ((!is_array($user_id)) ? array($user_id) : $user_id);
		$forum_ids = ($forum_id !== false) ? ((!is_array($forum_id)) ? array($forum_id) : $forum_id) : (($scope == 'global') ? array(0) : array());

		// If forum_ids is false and the scope is local we actually want to have all forums within the array
		if ($scope == 'local' && !sizeof($forum_ids))
		{
			$sql = 'SELECT forum_id 
				FROM ' . FORUMS_TABLE;
			$result = $db->sql_query($sql, 120);

			while ($row = $db->sql_fetchrow($result))
			{
				$forum_ids[] = $row['forum_id'];
			}
			$db->sql_freeresult($result);
		}

		foreach ($ug_id as $_id)
		{
			if (!isset($hold_ary[$_id]))
			{
				$hold_ary[$_id] = array();
			}

			foreach ($forum_ids as $f_id)
			{
				if (!isset($hold_ary[$_id][$f_id]))
				{
					$hold_ary[$_id][$f_id] = array();
				}
			}
		}

		// Now, we need to fill the gaps with $acl_fill. ;)

		// Only those options we need
		$compare_options = array_diff(preg_replace('/^((?!' . $auth_option . ').+)|(' . $auth_option . ')$/', '', array_keys($this->acl_options[$scope])), array(''));

		// Now switch back to keys
		if (sizeof($compare_options))
		{
			$compare_options = array_combine($compare_options, array_fill(1, sizeof($compare_options), $acl_fill));
		}

		// Defining the user-function here to save some memory
		$return_acl_fill = create_function('$value', 'return ' . $acl_fill . ';');

		// Actually fill the gaps
		if (sizeof($hold_ary))
		{
			foreach ($hold_ary as $ug_id => $row)
			{
				foreach ($row as $id => $options)
				{
					// Do not include the global auth_option
					unset($options[$auth_option]);

					// Not a "fine" solution, but at all it's a 1-dimensional 
					// array_diff_key function filling the resulting array values with zeros
					// The differences get merged into $hold_ary (all permissions having $acl_fill set)
					$hold_ary[$ug_id][$id] = array_merge($options, 

						array_map($return_acl_fill,
							array_flip(
								array_diff(
									array_keys($compare_options), array_keys($options)
								)
							)
						)
					);
				}
			}
		}
		else
		{
			$hold_ary[($group_id !== false) ? $group_id : $user_id][(int) $forum_id] = $compare_options;
		}

		return $hold_ary;
	}

	/**
	* Get permission mask for roles
	* This function only supports getting masks for one role
	*/
	function get_role_mask($role_id)
	{
		global $db;

		$hold_ary = array();

		// Get users having this role set...
		$sql = 'SELECT user_id, forum_id
			FROM ' . ACL_USERS_TABLE . '
			WHERE auth_role_id = ' . $role_id . '
			ORDER BY forum_id';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$hold_ary[$row['forum_id']]['users'][] = $row['user_id'];
		}
		$db->sql_freeresult($result);

		// Now grab groups... 
		$sql = 'SELECT group_id, forum_id
			FROM ' . ACL_GROUPS_TABLE . '
			WHERE auth_role_id = ' . $role_id . '
			ORDER BY forum_id';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$hold_ary[$row['forum_id']]['groups'][] = $row['group_id'];
		}
		$db->sql_freeresult($result);		

		return $hold_ary;
	}

	/**
	* Display permission mask (assign to template)
	*/
	function display_mask($mode, $permission_type, &$hold_ary, $user_mode = 'user', $local = false, $group_display = true)
	{
		global $template, $user, $db, $phpbb_root_path, $phpEx, $SID;

		// Define names for template loops, might be able to be set
		$tpl_pmask = 'p_mask';
		$tpl_fmask = 'f_mask';
		$tpl_category = 'category';
		$tpl_mask = 'mask';

		$l_acl_type = (isset($user->lang['ACL_TYPE_' . (($local) ? 'LOCAL' : 'GLOBAL') . '_' . strtoupper($permission_type)])) ? $user->lang['ACL_TYPE_' . (($local) ? 'LOCAL' : 'GLOBAL') . '_' . strtoupper($permission_type)] : 'ACL_TYPE_' . (($local) ? 'LOCAL' : 'GLOBAL') . '_' . strtoupper($permission_type);
		
		// Get names
		if ($user_mode == 'user')
		{
			$sql = 'SELECT user_id as ug_id, username as ug_name
				FROM ' . USERS_TABLE . '
				WHERE user_id IN (' . implode(', ', array_keys($hold_ary)) . ')
				ORDER BY username ASC';
		}
		else
		{
			$sql = 'SELECT group_id as ug_id, group_name as ug_name, group_type
				FROM ' . GROUPS_TABLE . '
				WHERE group_id IN (' . implode(', ', array_keys($hold_ary)) . ')
				ORDER BY group_type DESC, group_name ASC';
		}
		$result = $db->sql_query($sql);

		$ug_names_ary = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$ug_names_ary[$row['ug_id']] = ($user_mode == 'user') ? $row['ug_name'] : (($row['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $row['ug_name']] : $row['ug_name']);
		}
		$db->sql_freeresult($result);

		// Get used forums
		$forum_ids = array();
		foreach ($hold_ary as $ug_id => $row)
		{
			$forum_ids = array_merge($forum_ids, array_keys($row));
		}
		$forum_ids = array_unique($forum_ids);

		$forum_names_ary = array();
		if ($local)
		{
			$forum_names_ary = make_forum_select(false, false, true, false, false, true);
		}
		else
		{
			$forum_names_ary[0] = $l_acl_type;
		}

		// Get available roles
		$sql = 'SELECT *
			FROM ' . ACL_ROLES_TABLE . "
			WHERE role_type = '" . $db->sql_escape($permission_type) . "'";
		$result = $db->sql_query($sql);

		$roles = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$roles[$row['role_id']] = $row;
			$roles[$row['role_id']]['groups'] = ($row['role_group_ids']) ? explode(':', $row['role_group_ids']) : array();
		}
		$db->sql_freeresult($result);

		$cur_roles = $this->acl_role_data($user_mode, $permission_type, array_keys($hold_ary));

		// Build js roles array (role data assignments)
		$s_role_js_array = '';
		
		if (sizeof($roles))
		{
			$sql = 'SELECT r.role_id, o.auth_option, r.auth_setting
				FROM ' . ACL_ROLES_DATA_TABLE . ' r, ' . ACL_OPTIONS_TABLE . ' o
				WHERE o.auth_option_id = r.auth_option_id
					AND r.role_id IN (' . implode(', ', array_keys($roles)) . ')';
			$result = $db->sql_query($sql);

			$s_role_js_array = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$flag = substr($row['auth_option'], 0, strpos($row['auth_option'], '_') + 1);
				if ($flag == $row['auth_option'])
				{
					continue;
				}

				if (!isset($s_role_js_array[$row['role_id']]))
				{
					$s_role_js_array[$row['role_id']] = "\n" . 'role_options[' . $row['role_id'] . '] = new Array();' . "\n";
				}
				$s_role_js_array[$row['role_id']] .= 'role_options[' . $row['role_id'] . '][\'' . $row['auth_option'] . '\'] = ' . $row['auth_setting'] . '; ';
			}
			$db->sql_freeresult($result);

			$s_role_js_array = implode('', $s_role_js_array);
		}

		$template->assign_var('S_ROLE_JS_ARRAY', $s_role_js_array);

		// Now obtain memberships
		$user_groups_default = $user_groups_custom = array();
		if ($user_mode == 'user' && $group_display)
		{
			$sql = 'SELECT group_id, group_name, group_type
				FROM ' . GROUPS_TABLE . '
				ORDER BY group_type DESC, group_name ASC';
			$result = $db->sql_query($sql);

			$groups = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$groups[$row['group_id']] = $row;
			}
			$db->sql_freeresult($result);
			
			$memberships = group_memberships(false, array_keys($hold_ary), false);
			
			// User is not a member of any group? Bad admin, bad bad admin...
			if ($memberships)
			{
				foreach ($memberships as $row)
				{
					if ($groups[$row['group_id']]['group_type'] == GROUP_SPECIAL)
					{
						$user_groups_default[$row['user_id']][] = $user->lang['G_' . $groups[$row['group_id']]['group_name']];
					}
					else
					{
						$user_groups_custom[$row['user_id']][] = $groups[$row['group_id']]['group_name'];
					}
				}
			}
			unset($memberships, $groups);
		}

		// If we only have one forum id to display or being in local mode and more than one user/group to display, 
		// we switch the complete interface to group by user/usergroup instead of grouping by forum
		// To achive this, we need to switch the array a bit
		if (sizeof($forum_ids) == 1 || ($local && sizeof($ug_names_ary) > 1))
		{
			$hold_ary_temp = $hold_ary;
			$hold_ary = array();
			foreach ($hold_ary_temp as $ug_id => $row)
			{
				foreach ($row as $forum_id => $auth_row)
				{
					$hold_ary[$forum_id][$ug_id] = $auth_row;
				}
			}
			unset($hold_ary_temp);

			foreach ($hold_ary as $forum_id => $forum_array)
			{
				$content_array = $categories = array();
				$this->build_permission_array($hold_ary[$forum_id], $content_array, $categories, array_keys($ug_names_ary));

				$template->assign_block_vars($tpl_pmask, array(
					'NAME'			=> ($forum_id == 0) ? $forum_names_ary[0] : $forum_names_ary[$forum_id]['forum_name'],
					'CATEGORIES'	=> implode('</th><th>', $categories),

					'L_ACL_TYPE'	=> $l_acl_type,

					'S_LOCAL'		=> ($local) ? true : false,
					'S_GLOBAL'		=> (!$local) ? true : false,
					'S_NUM_CATS'	=> sizeof($categories),
					'S_VIEW'		=> ($mode == 'view') ? true : false,
					'S_NUM_OBJECTS'	=> sizeof($content_array),
					'S_USER_MODE'	=> ($user_mode == 'user') ? true : false,
					'S_GROUP_MODE'	=> ($user_mode == 'group') ? true : false)
				);

				foreach ($content_array as $ug_id => $ug_array)
				{
					// Build role dropdown options
					$current_role_id = (isset($cur_roles[$ug_id][$forum_id])) ? $cur_roles[$ug_id][$forum_id] : 0;

					$s_role_options = '';
					foreach ($roles as $role_id => $role_row)
					{
						if ($role_id == $current_role_id || !sizeof($role_row['groups']) || ($user_mode == 'group' && in_array($ug_id, $role_row['groups'])))
						{
							$s_role_options .= '<option value="' . $role_id . '"' . (($role_id == $current_role_id) ? ' selected="selected"' : '') . '>' . $role_row['role_name'] . '</option>';
						}
					}
					
					if ($s_role_options)
					{
						$s_role_options = '<option value="0"' . ((!$current_role_id) ? ' selected="selected"' : '') . '>' . $user->lang['SELECT_ROLE'] . '</option>' . $s_role_options;
					}

					$template->assign_block_vars($tpl_pmask . '.' . $tpl_fmask, array(
						'NAME'				=> $ug_names_ary[$ug_id],
						'S_ROLE_OPTIONS'	=> $s_role_options,
						'UG_ID'				=> $ug_id,
						'FORUM_ID'			=> $forum_id)
					);

					$this->assign_cat_array($ug_array, $tpl_pmask . '.' . $tpl_fmask . '.' . $tpl_category, $tpl_mask, $ug_id, $forum_id);
				}
			}
		}
		else
		{
			foreach ($ug_names_ary as $ug_id => $ug_name)
			{
				if (!isset($hold_ary[$ug_id]))
				{
					continue;
				}

				$content_array = $categories = array();
				$this->build_permission_array($hold_ary[$ug_id], $content_array, $categories, array_keys($forum_names_ary));

				$template->assign_block_vars($tpl_pmask, array(
					'NAME'			=> $ug_name,
					'CATEGORIES'	=> implode('</th><th>', $categories),

					'USER_GROUPS_DEFAULT'	=> ($user_mode == 'user' && isset($user_groups_default[$ug_id]) && sizeof($user_groups_default[$ug_id])) ? implode(', ', $user_groups_default[$ug_id]) : '',
					'USER_GROUPS_CUSTOM'	=> ($user_mode == 'user' && isset($user_groups_custom[$ug_id]) && sizeof($user_groups_custom[$ug_id])) ? implode(', ', $user_groups_custom[$ug_id]) : '',
					'L_ACL_TYPE'			=> $l_acl_type,

					'S_LOCAL'		=> ($local) ? true : false,
					'S_GLOBAL'		=> (!$local) ? true : false,
					'S_NUM_CATS'	=> sizeof($categories),
					'S_VIEW'		=> ($mode == 'view') ? true : false,
					'S_NUM_OBJECTS'	=> sizeof($content_array),
					'S_USER_MODE'	=> ($user_mode == 'user') ? true : false,
					'S_GROUP_MODE'	=> ($user_mode == 'group') ? true : false)
				);

				foreach ($content_array as $forum_id => $forum_array)
				{
					// Build role dropdown options
					$current_role_id = (isset($cur_roles[$ug_id][$forum_id])) ? $cur_roles[$ug_id][$forum_id] : 0;

					$s_role_options = '';
					foreach ($roles as $role_id => $role_row)
					{
						if ($role_id == $current_role_id || !sizeof($role_row['groups']) || ($user_mode == 'group' && in_array($ug_id, $role_row['groups'])))
						{
							$s_role_options .= '<option value="' . $role_id . '"' . (($role_id == $current_role_id) ? ' selected="selected"' : '') . '>' . $role_row['role_name'] . '</option>';
						}
					}

					if ($s_role_options)
					{
						$s_role_options = '<option value="0"' . ((!$current_role_id) ? ' selected="selected"' : '') . '>' . $user->lang['SELECT_ROLE'] . '</option>' . $s_role_options;
					}

					$template->assign_block_vars($tpl_pmask . '.' . $tpl_fmask, array(
						'NAME'				=> ($forum_id == 0) ? $forum_names_ary[0] : $forum_names_ary[$forum_id]['forum_name'],
						'PADDING'			=> ($forum_id == 0) ? '' : $forum_names_ary[$forum_id]['padding'],
						'S_ROLE_OPTIONS'	=> $s_role_options,
						'UG_ID'				=> $ug_id,
						'FORUM_ID'			=> $forum_id)
					);

					$this->assign_cat_array($forum_array, $tpl_pmask . '.' . $tpl_fmask . '.' . $tpl_category, $tpl_mask, $ug_id, $forum_id);
				}
			}
		}
	}

	/**
	* Display permission mask for roles
	*/
	function display_role_mask(&$hold_ary)
	{
		global $db, $template, $user, $phpbb_root_path, $phpbb_admin_path, $phpEx, $SID;

		if (!sizeof($hold_ary))
		{
			return;
		}

		// Get forum names
		$sql = 'SELECT forum_id, forum_name
			FROM ' . FORUMS_TABLE . '
			WHERE forum_id IN (' . implode(', ', array_keys($hold_ary)) . ')';
		$result = $db->sql_query($sql);

		$forum_names = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$forum_names[$row['forum_id']] = $row['forum_name'];
		}
		$db->sql_freeresult($result);

		foreach ($hold_ary as $forum_id => $auth_ary)
		{
			$template->assign_block_vars('role_mask', array(
				'NAME'				=> ($forum_id == 0) ? $user->lang['GLOBAL_MASK'] : $forum_names[$forum_id],
				'FORUM_ID'			=> $forum_id)
			);
		
			if (isset($auth_ary['users']) && sizeof($auth_ary['users']))
			{
				$sql = 'SELECT user_id, username
					FROM ' . USERS_TABLE . '
					WHERE user_id IN (' . implode(', ', $auth_ary['users']) . ')
					ORDER BY username';
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					$template->assign_block_vars('role_mask.users', array(
						'USER_ID'		=> $row['user_id'],
						'USERNAME'		=> $row['username'],
						'U_PROFILE'		=> "{$phpbb_root_path}memberlist.$phpEx$SID&amp;mode=viewprofile&amp;u={$row['user_id']}")
					);
				}
				$db->sql_freeresult($result);
			}

			if (isset($auth_ary['groups']) && sizeof($auth_ary['groups']))
			{
				$sql = 'SELECT group_id, group_name, group_type
					FROM ' . GROUPS_TABLE . '
					WHERE group_id IN (' . implode(', ', $auth_ary['groups']) . ')
					ORDER BY group_type ASC, group_name';
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					$template->assign_block_vars('role_mask.groups', array(
						'GROUP_ID'		=> $row['group_id'],
						'GROUP_NAME'	=> ($row['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $row['group_name']] : $row['group_name'],
						'U_PROFILE'		=> $phpbb_root_path . "memberlist.$phpEx$SID&amp;mode=group&amp;g={$row['group_id']}")
					);
				}
				$db->sql_freeresult($result);
			}
		}
	}

	/**
	* NOTE: this function is not in use atm
	* Add a new option to the list ... $options is a hash of form ->
	* $options = array(
	*	'local'		=> array('option1', 'option2', ...),
	*	'global'	=> array('optionA', 'optionB', ...)
	* );
	*/
	function acl_add_option($options)
	{
		global $db, $cache;

		if (!is_array($options))
		{
			return false;
		}

		$cur_options = array();

		$sql = 'SELECT auth_option, is_global, is_local
			FROM ' . ACL_OPTIONS_TABLE . '
			ORDER BY auth_option_id';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			if ($row['is_global'])
			{
				$cur_options['global'][] = $row['auth_option'];
			}

			if ($row['is_local'])
			{
				$cur_options['local'][] = $row['auth_option'];
			}
		}
		$db->sql_freeresult($result);

		// Here we need to insert new options ... this requires discovering whether
		// an options is global, local or both and whether we need to add an permission
		// set flag (x_)
		$new_options = array('local' => array(), 'global' => array());

		foreach ($options as $type => $option_ary)
		{
			$option_ary = array_unique($option_ary);

			foreach ($option_ary as $option_value)
			{
				if (!in_array($option_value, $cur_options[$type]))
				{
					$new_options[$type][] = $option_value;
				}

				$flag = substr($option_value, 0, strpos($option_value, '_') + 1);

				if (!in_array($flag, $cur_options[$type]) && !in_array($flag, $new_options[$type]))
				{
					$new_options[$type][] = $flag;
				}
			}
		}
		unset($options);

		$options = array();
		$options['local'] = array_diff($new_options['local'], $new_options['global']);
		$options['global'] = array_diff($new_options['global'], $new_options['local']);
		$options['local_global'] = array_intersect($new_options['local'], $new_options['global']);

		$sql_ary = array();

		foreach ($options as $type => $option_ary)
		{
			foreach ($option_ary as $option)
			{
				$sql_ary[] = array(
					'auth_option'	=> $option,
					'is_global'		=> ($type == 'global' || $type == 'local_global') ? 1 : 0,
					'is_local'		=> ($type == 'local' || $type == 'local_global') ? 1 : 0
				);
			}
		}

		if (sizeof($sql_ary))
		{
			switch (SQL_LAYER)
			{
				case 'mysql':
				case 'mysql4':
				case 'mysqli':
					$db->sql_query('INSERT INTO ' . ACL_OPTIONS_TABLE . ' ' . $db->sql_build_array('MULTI_INSERT', $sql_ary));
				break;

				default:
					foreach ($sql_ary as $ary)
					{
						$db->sql_query('INSERT INTO ' . ACL_OPTIONS_TABLE . ' ' . $db->sql_build_array('INSERT', $ary));
					}
				break;
			}
		}

		$cache->destroy('acl_options');

		return true;
	}

	/**
	* Set a user or group ACL record
	*/
	function acl_set($ug_type, &$forum_id, &$ug_id, &$auth, $role_id = 0, $clear_prefetch = true)
	{
		global $db;

		// One or more forums
		if (!is_array($forum_id))
		{
			$forum_id = array($forum_id);
		}

		// One or more users
		if (!is_array($ug_id))
		{
			$ug_id = array($ug_id);
		}
		
		$ug_id_sql = 'IN (' . implode(', ', array_map('intval', $ug_id)) . ')';
		$forum_sql = 'IN (' . implode(', ', array_map('intval', $forum_id)) . ') ';

		// Instead of updating, inserting, removing we just remove all current settings and re-set everything...
		$table = ($ug_type == 'user') ? ACL_USERS_TABLE : ACL_GROUPS_TABLE;
		$id_field  = $ug_type . '_id';

		// Get any flags as required
		$flag = key($auth);
		$flag = substr($flag, 0, strpos($flag, '_') + 1);
		
		// This ID (the any-flag) is set if one or more permissions are true...
		$any_option_id = (int) $this->option_ids[$flag];

		// Remove any-flag from auth ary
		if (isset($auth[$flag]))
		{
			unset($auth[$flag]);
		}

		// Remove current auth options...
		$auth_option_ids = array();
		foreach ($auth as $auth_option => $auth_setting)
		{
			$auth_option_ids[] = (int) $this->option_ids[$auth_option];
		}

		$sql = "DELETE FROM $table
			WHERE forum_id $forum_sql
				AND $id_field $ug_id_sql
				AND auth_option_id IN ($any_option_id, " . implode(', ', $auth_option_ids) . ')';
		$db->sql_query($sql);

		// Remove those having a role assigned... the correct type of course...
		$sql = 'SELECT role_id
			FROM ' . ACL_ROLES_TABLE . "
			WHERE role_type = '" . $db->sql_escape($flag) . "'";
		$result = $db->sql_query($sql);

		$role_ids = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$role_ids[] = $row['role_id'];
		}
		$db->sql_freeresult($result);

		if (sizeof($role_ids))
		{
			$sql = "DELETE FROM $table
				WHERE forum_id $forum_sql
					AND $id_field $ug_id_sql
					AND auth_option_id = 0
					AND auth_role_id IN (" . implode(', ', $role_ids) . ')';
			$db->sql_query($sql);
		}

		// Ok, include the any-flag if one or more auth options are set to yes...
		foreach ($auth as $auth_option => $setting)
		{
			if ($setting == ACL_YES && (!isset($auth[$flag]) || $auth[$flag] == ACL_NO))
			{
				$auth[$flag] = ACL_YES;
			}
		}

		$sql_ary = array();
		foreach ($forum_id as $forum)
		{
			$forum = (int) $forum;

			if ($role_id)
			{
				foreach ($ug_id as $id)
				{
					$sql_ary[] = array(
						$id_field			=> (int) $id,
						'forum_id'			=> (int) $forum,
						'auth_option_id'	=> 0,
						'auth_setting'		=> 0,
						'auth_role_id'		=> $role_id
					);
				}
			}
			else
			{
				foreach ($auth as $auth_option => $setting)
				{
					$auth_option_id = (int) $this->option_ids[$auth_option];

					if ($setting != ACL_UNSET)
					{
						foreach ($ug_id as $id)
						{
							$sql_ary[] = array(
								$id_field			=> (int) $id,
								'forum_id'			=> (int) $forum,
								'auth_option_id'	=> (int) $auth_option_id,
								'auth_setting'		=> (int) $setting
							);
						}
					}
				}
			}
		}

		if (sizeof($sql_ary))
		{
			switch (SQL_LAYER)
			{
				case 'mysql':
				case 'mysql4':
				case 'mysqli':
					$db->sql_query("INSERT INTO $table " . $db->sql_build_array('MULTI_INSERT', $sql_ary));
				break;

				default:
					foreach ($sql_ary as $ary)
					{
						$db->sql_query("INSERT INTO $table " . $db->sql_build_array('INSERT', $ary));
					}
				break;
			}
		}

		if ($clear_prefetch)
		{
			$this->acl_clear_prefetch();
		}
	}

	/**
	* Set a role-specific ACL record
	*/
	function acl_set_role($role_id, &$auth)
	{
		global $db;

		// Get any-flag as required
		$flag = key($auth);
		$flag = substr($flag, 0, strpos($flag, '_') + 1);
		
		// Remove any-flag from auth ary
		if (isset($auth[$flag]))
		{
			unset($auth[$flag]);
		}

		// Re-set any flag...
		foreach ($auth as $auth_option => $setting)
		{
			if ($setting == ACL_YES && (!isset($auth[$flag]) || $auth[$flag] == ACL_NO))
			{
				$auth[$flag] = ACL_YES;
			}
		}

		$sql_ary = array();
		foreach ($auth as $auth_option => $setting)
		{
			$auth_option_id = (int) $this->option_ids[$auth_option];

			if ($setting != ACL_UNSET)
			{
				$sql_ary[] = array(
					'role_id'			=> (int) $role_id,
					'auth_option_id'	=> (int) $auth_option_id,
					'auth_setting'		=> (int) $setting
				);
			}
		}

		// If no data is there, we set the any-flag to ACL_NO...
		if (!sizeof($sql_ary))
		{
			$sql_ary[] = array(
				'role_id'			=> (int) $role_id,
				'auth_option_id'	=> $this->option_ids[$flag],
				'auth_setting'		=> ACL_NO
			);
		}

		// Remove current auth options...
		$sql = 'DELETE FROM ' . ACL_ROLES_DATA_TABLE . '
			WHERE role_id = ' . $role_id;
		$db->sql_query($sql);

		switch (SQL_LAYER)
		{
			case 'mysql':
			case 'mysql4':
			case 'mysqli':
				$db->sql_query('INSERT INTO ' . ACL_ROLES_DATA_TABLE . ' ' . $db->sql_build_array('MULTI_INSERT', $sql_ary));
			break;

			default:
				foreach ($sql_ary as $ary)
				{
					$db->sql_query('INSERT INTO ' . ACL_ROLES_DATA_TABLE . ' ' . $db->sql_build_array('INSERT', $ary));
				}
			break;
		}

		$this->acl_clear_prefetch();
	}

	/**
	* Remove local permission
	*/
	function acl_delete($mode, $ug_id = false, $forum_id = false, $permission_type = false)
	{
		global $db;

		if ($ug_id === false && $forum_id === false)
		{
			return;
		}

		$option_id_ary = array();
		$table = ($mode == 'user') ? ACL_USERS_TABLE : ACL_GROUPS_TABLE;
		$id_field  = $mode . '_id';

		$where_sql = array();

		if ($forum_id !== false)
		{
			$where_sql[] = (!is_array($forum_id)) ? 'forum_id = ' . (int) $forum_id : 'forum_id IN (' . implode(', ', array_map('intval', $forum_id)) . ')';
		}

		if ($ug_id !== false)
		{
			$where_sql[] = (!is_array($ug_id)) ? $id_field . ' = ' . (int) $ug_id : $id_field . ' IN (' . implode(', ', array_map('intval', $ug_id)) . ')';
		}

		// There seem to be auth options involved, therefore we need to go through the list and make sure we capture roles correctly
		if ($permission_type !== false)
		{
			// Get permission type
			$sql = 'SELECT auth_option, auth_option_id
				FROM ' . ACL_OPTIONS_TABLE . "
				WHERE auth_option LIKE '" . $db->sql_escape($permission_type) . "%'";
			$result = $db->sql_query($sql);

			$auth_id_ary = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$option_id_ary[] = $row['auth_option_id'];
				$auth_id_ary[$row['auth_option']] = ACL_UNSET;
			}
			$db->sql_freeresult($result);

			// First of all, lets grab the items having roles with the specified auth options assigned
			$sql = "SELECT auth_role_id, $id_field, forum_id
				FROM $table, " . ACL_ROLES_TABLE . " r
				WHERE auth_role_id <> 0
					AND auth_role_id = r.role_id
					AND r.role_type = '{$permission_type}'
					AND " . implode(' AND ', $where_sql) . '
				ORDER BY auth_role_id';
			$result = $db->sql_query($sql);

			$cur_role_auth = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$cur_role_auth[$row['auth_role_id']][$row['forum_id']][] = $row[$id_field];
			}
			$db->sql_freeresult($result);

			// Get role data for resetting data
			if (sizeof($cur_role_auth))
			{
				$sql = 'SELECT ao.auth_option, rd.role_id, rd.auth_setting
					FROM ' . ACL_OPTIONS_TABLE . ' ao, ' . ACL_ROLES_DATA_TABLE . ' rd
					WHERE ao.auth_option_id = rd.auth_option_id
						AND rd.role_id IN (' . implode(', ', array_keys($cur_role_auth)) . ')';
				$result = $db->sql_query($sql);

				$auth_settings = array();
				while ($row = $db->sql_fetchrow($result))
				{
					// We need to fill all auth_options, else setting it will fail...
					if (!isset($auth_settings[$row['role_id']]))
					{
						$auth_settings[$row['role_id']] = $auth_id_ary;
					}
					$auth_settings[$row['role_id']][$row['auth_option']] = $row['auth_setting'];
				}
				$db->sql_freeresult($result);

				// Set the options
				foreach ($cur_role_auth as $role_id => $auth_row)
				{
					foreach ($auth_row as $f_id => $ug_row)
					{
						$this->acl_set($mode, $f_id, $ug_row, $auth_settings[$role_id], 0, false);
					}
				}
			}
		}

		// Now, normally remove permissions...
		if ($permission_type !== false)
		{
			$where_sql[] = 'auth_option_id IN (' . implode(', ', array_map('intval', $option_id_ary)) . ')';
		}
		
		$sql = "DELETE FROM $table
			WHERE " . implode(' AND ', $where_sql);
		$db->sql_query($sql);

		$this->acl_clear_prefetch();
	}

	/**
	* Assign category to template
	* used by display_mask()
	*/
	function assign_cat_array(&$category_array, $tpl_cat, $tpl_mask, $ug_id, $forum_id)
	{
		global $template, $user;

		foreach ($category_array as $cat => $cat_array)
		{
			$template->assign_block_vars($tpl_cat, array(
				'S_YES'		=> ($cat_array['S_YES'] && !$cat_array['S_NO'] && !$cat_array['S_UNSET']) ? true : false,
				'S_NO'		=> ($cat_array['S_NO'] && !$cat_array['S_YES'] && !$cat_array['S_UNSET']) ? true : false,
				'S_UNSET'	=> ($cat_array['S_UNSET'] && !$cat_array['S_NO'] && !$cat_array['S_YES']) ? true : false,
							
				'CAT_NAME'	=> $user->lang['permission_cat'][$cat])
			);
				
			foreach ($cat_array['permissions'] as $permission => $allowed)
			{
				$template->assign_block_vars($tpl_cat . '.' . $tpl_mask, array(
					'S_YES'		=> ($allowed == ACL_YES) ? true : false,
					'S_NO'		=> ($allowed == ACL_NO) ? true : false,
					'S_UNSET'	=> ($allowed == ACL_UNSET) ? true : false,

					'UG_ID'			=> $ug_id,
					'FORUM_ID'		=> $forum_id,
					'FIELD_NAME'	=> $permission,
					'S_FIELD_NAME'	=> 'setting[' . $ug_id . '][' . $forum_id . '][' . $permission . ']',

					'PERMISSION'	=> $user->lang['acl_' . $permission]['lang'])
				);
			}
		}
	}

	/**
	* Building content array from permission rows with explicit key ordering
	* used by display_mask()
	*/
	function build_permission_array(&$permission_row, &$content_array, &$categories, $key_sort_array)
	{
		global $user;

		foreach ($key_sort_array as $forum_id)
		{
			if (!isset($permission_row[$forum_id]))
			{
				continue;
			}

			$permissions = $permission_row[$forum_id];
			ksort($permissions);

			foreach ($permissions as $permission => $auth_setting)
			{
				if (!isset($user->lang['acl_' . $permission]))
				{
					$user->lang['acl_' . $permission] = array(
						'cat'	=> 'misc',
						'lang'	=> '{ acl_' . $permission . ' }'
					);
				}
			
				$cat = $user->lang['acl_' . $permission]['cat'];
			
				// Build our categories array
				if (!isset($categories[$cat]))
				{
					$categories[$cat] = $user->lang['permission_cat'][$cat];
				}

				// Build our content array
				if (!isset($content_array[$forum_id]))
				{
					$content_array[$forum_id] = array();
				}

				if (!isset($content_array[$forum_id][$cat]))
				{
					$content_array[$forum_id][$cat] = array(
						'S_YES'			=> false,
						'S_NO'			=> false,
						'S_UNSET'		=> false,
						'permissions'	=> array(),
					);
				}

				$content_array[$forum_id][$cat]['S_YES'] |= ($auth_setting == ACL_YES) ? true : false;
				$content_array[$forum_id][$cat]['S_NO'] |= ($auth_setting == ACL_NO) ? true : false;
				$content_array[$forum_id][$cat]['S_UNSET'] |= ($auth_setting == ACL_UNSET) ? true : false;

				$content_array[$forum_id][$cat]['permissions'][$permission] = $auth_setting;
			}
		}
	}
}

?>