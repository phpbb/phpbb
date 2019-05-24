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

namespace phpbb\acp\helper;

/**
 * ACP Permission/Auth class
 */
class auth_admin extends \phpbb\auth\auth
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\cache\driver\driver_interface */
	public $cache;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\group\helper */
	protected $group_helper;

	/** @var controller */
	protected $helper;

	/** @var \phpbb\language\language */
	protected $lang;

	/** @var \phpbb\permissions */
	protected $permissions;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var string phpBB admin path */
	protected $admin_path;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string php File extension */
	protected $php_ext;

	/** @var array phpBB tables */
	protected $tables;

	/** @var array */
	public $acl_options;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\auth\auth						$auth			Auth object
	 * @param \phpbb\cache\driver\driver_interface	$cache			Cache object
	 * @param \phpbb\db\driver\driver_interface		$db				Database object
	 * @param \phpbb\group\helper					$group_helper	Group helper object
	 * @param controller							$helper			ACP Controller helper object
	 * @param \phpbb\language\language				$lang			Language object
	 * @param \phpbb\permissions					$permissions	Permissions object
	 * @param \phpbb\template\template				$template		Template object
	 * @param \phpbb\user							$user			User object
	 * @param string								$admin_path		phpBB admin path
	 * @param string								$root_path		phpBB root path
	 * @param string								$php_ext		php File extension
	 * @param array									$tables			phpBB tables
	 */
	function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\cache\driver\driver_interface $cache,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\group\helper $group_helper,
		controller $helper,
		\phpbb\language\language $lang,
		\phpbb\permissions $permissions,
		\phpbb\template\template $template,
		\phpbb\user $user,
		$admin_path,
		$root_path,
		$php_ext,
		$tables
	)
	{
		$this->auth			= $auth;
		$this->cache		= $cache;
		$this->db			= $db;
		$this->group_helper	= $group_helper;
		$this->helper		= $helper;
		$this->lang			= $lang;
		$this->permissions	= $permissions;
		$this->template		= $template;
		$this->user			= $user;

		$this->admin_path	= $admin_path;
		$this->root_path	= $root_path;
		$this->php_ext		= $php_ext;
		$this->tables		= $tables;

		$this->init();
	}

	/**
	 * Init auth settings.
	 *
	 * @return void
	 */
	protected function init()
	{
		if (($this->acl_options = $this->cache->get('_acl_options')) === false)
		{
			$global = $local = 0;
			$this->acl_options = [];

			$sql = 'SELECT auth_option_id, auth_option, is_global, is_local
				FROM ' . $this->tables['acl_options'] . '
				ORDER BY auth_option_id';
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				if ($row['is_global'])
				{
					$this->acl_options['global'][$row['auth_option']] = $global++;
				}

				if ($row['is_local'])
				{
					$this->acl_options['local'][$row['auth_option']] = $local++;
				}

				$this->acl_options['id'][$row['auth_option']] = (int) $row['auth_option_id'];
				$this->acl_options['option'][(int) $row['auth_option_id']] = $row['auth_option'];
			}
			$this->db->sql_freeresult($result);

			$this->cache->put('_acl_options', $this->acl_options);
		}
	}

	/**
	 * Get permission mask.
	 *
	 * This function only supports getting permissions of one type (for example a_).
	 * Either user_id(s) or group_id(s) has to be supplied.
	 *
	 * @param string			$mode			Defines the permissions we get (set|view)
	 * 												'view' gets effective permissions (both user AND group permissions),
	 * 												'set' only gets the user or group permission set alone
	 * @param int|array|false	$user_id		The user identifiers to search for
	 * @param int|array|false	$group_id		The group identifiers to search for,
	 * 												return group related settings
	 * @param int|array|false	$forum_id		The forum identifiers to search for,
	 * 												defining a forum id also means getting local settings
	 * @param string|false		$auth_option	The auth_option defines the permission setting to look for (eg: a_)
	 * @param string|false		$scope			The scope defines the permission scope (local|global),
	 * 												If 'local', a forum_id is additionally required
	 * @param int				$acl_fill		Defines the mode those permissions not set
	 * 												are getting filled with (ACL_NEVER|ACL_NO|ACL_YES)
	 * @return array
	 */
	function get_mask($mode, $user_id = false, $group_id = false, $forum_id = false, $auth_option = false, $scope = false, $acl_fill = ACL_NEVER)
	{
		$hold_ary = [];
		$view_user_mask = ($mode === 'view' && $group_id === false) ? true : false;

		if ($auth_option === false || $scope === false)
		{
			return [];
		}

		$acl_user_function = $mode === 'set' ? 'acl_user_raw_data' : 'acl_raw_data';

		if (!$view_user_mask)
		{
			if ($forum_id !== false)
			{
				$hold_ary = $group_id !== false ? $this->acl_group_raw_data($group_id, $auth_option . '%', $forum_id) : $this->$acl_user_function($user_id, $auth_option . '%', $forum_id);
			}
			else
			{
				$hold_ary = $group_id !== false ? $this->acl_group_raw_data($group_id, $auth_option . '%', $scope === 'global' ? 0 : false) : $this->$acl_user_function($user_id, $auth_option . '%', $scope === 'global' ? 0 : false);
			}
		}

		// Make sure hold_ary is filled with every setting (prevents missing forums/users/groups)
		$ug_id = $group_id !== false ? (!is_array($group_id) ? [$group_id] : $group_id) : (!is_array($user_id) ? [$user_id] : $user_id);
		$forum_ids = $forum_id !== false ? (!is_array($forum_id) ? [$forum_id] : $forum_id) : ($scope === 'global' ? [0] : []);

		// Only those options we need
		$compare_options = array_diff(preg_replace('/^((?!' . $auth_option . ').+)|(' . $auth_option . ')$/', '', array_keys($this->acl_options[$scope])), ['']);

		// If forum_ids is false and the scope is local we actually want to have all forums within the array
		if ($scope === 'local' && empty($forum_ids))
		{
			$sql = 'SELECT forum_id
				FROM ' . $this->tables['forums'];
			$result = $this->db->sql_query($sql, 120);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$forum_ids[] = (int) $row['forum_id'];
			}
			$this->db->sql_freeresult($result);
		}

		if ($view_user_mask)
		{
			$auth2 = null;

			$sql = 'SELECT user_id, user_permissions, user_type
				FROM ' . $this->tables['users'] . '
				WHERE ' . $this->db->sql_in_set('user_id', $ug_id);
			$result = $this->db->sql_query($sql);
			while ($userdata = $this->db->sql_fetchrow($result))
			{
				if ($this->user->data['user_id'] != $userdata['user_id'])
				{
					$auth2 = new \phpbb\auth\auth();
					$auth2->acl($userdata);
				}
				else
				{
					$auth2 = &$this->auth;
				}

				$hold_ary[$userdata['user_id']] = [];
				foreach ($forum_ids as $f_id)
				{
					$hold_ary[$userdata['user_id']][$f_id] = [];
					foreach ($compare_options as $option)
					{
						$hold_ary[$userdata['user_id']][$f_id][$option] = $auth2->acl_get($option, $f_id);
					}
				}
			}
			$this->db->sql_freeresult($result);

			unset($userdata);
			unset($auth2);
		}

		foreach ($ug_id as $_id)
		{
			if (!isset($hold_ary[$_id]))
			{
				$hold_ary[$_id] = [];
			}

			foreach ($forum_ids as $f_id)
			{
				if (!isset($hold_ary[$_id][$f_id]))
				{
					$hold_ary[$_id][$f_id] = [];
				}
			}
		}

		// Now, we need to fill the gaps with $acl_fill. ;)

		// Now switch back to keys
		if (!empty($compare_options))
		{
			$compare_options = array_combine($compare_options, array_fill(1, count($compare_options), $acl_fill));
		}

		// Defining the user-function here to save some memory
		$return_acl_fill = function () use ($acl_fill)
		{
			return $acl_fill;
		};

		// Actually fill the gaps
		if (!empty($hold_ary))
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
			$hold_ary[$group_id !== false ? $group_id : $user_id][(int) $forum_id] = $compare_options;
		}

		return $hold_ary;
	}

	/**
	 * Get permission mask for roles.
	 * This function only supports getting masks for one role.
	 *
	 * @param int		$role_id	The role identifier
	 * @return array
	 */
	function get_role_mask($role_id)
	{
		$hold_ary = [];

		// Get users having this role set...
		$sql = 'SELECT user_id, forum_id
			FROM ' . $this->tables['acl_users'] . '
			WHERE auth_role_id = ' . $role_id . '
			ORDER BY forum_id';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$hold_ary[$row['forum_id']]['users'][] = $row['user_id'];
		}
		$this->db->sql_freeresult($result);

		// Now grab groups...
		$sql = 'SELECT group_id, forum_id
			FROM ' . $this->tables['acl_groups'] . '
			WHERE auth_role_id = ' . $role_id . '
			ORDER BY forum_id';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$hold_ary[$row['forum_id']]['groups'][] = $row['group_id'];
		}
		$this->db->sql_freeresult($result);

		return $hold_ary;
	}

	/**
	 * Display permission mask (assign to template)
	 *
	 * @param string	$mode
	 * @param string	$permission_type
	 * @param array		$hold_ary
	 * @param string	$user_mode
	 * @param bool		$local
	 * @param bool		$group_display
	 */
	function display_mask($mode, $permission_type, array &$hold_ary, $user_mode = 'user', $local = false, $group_display = true)
	{
		// Define names for template loops, might be able to be set
		$tpl_mask = 'mask';
		$tpl_mask_p = 'p_mask';
		$tpl_mask_f = 'f_mask';
		$tpl_category = 'category';

		$l_acl_type = $this->permissions->get_type_lang($permission_type, ($local ? 'local' : 'global'));

		// Allow trace for viewing permissions and in user mode
		$show_trace = ($mode === 'view' && $user_mode === 'user') ? true : false;

		$ug_names_ary = [];

		// Get names
		if ($user_mode === 'user')
		{
			$sql = 'SELECT user_id as ug_id, username as ug_name
				FROM ' . $this->tables['users'] . '
				WHERE ' . $this->db->sql_in_set('user_id', array_keys($hold_ary)) . '
				ORDER BY username_clean ASC';
		}
		else
		{
			$sql = 'SELECT group_id as ug_id, group_name as ug_name, group_type
				FROM ' . $this->tables['groups'] . '
				WHERE ' . $this->db->sql_in_set('group_id', array_keys($hold_ary)) . '
				ORDER BY group_type DESC, group_name ASC';
		}
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$ug_names_ary[$row['ug_id']] = ($user_mode === 'user') ? $row['ug_name'] : $this->group_helper->get_name($row['ug_name']);
		}
		$this->db->sql_freeresult($result);

		// Get used forums
		$forum_ids = [];
		foreach ($hold_ary as $ug_id => $row)
		{
			$forum_ids = array_merge($forum_ids, array_keys($row));
		}
		$forum_ids = array_unique($forum_ids);

		$forum_names_ary = [];
		if ($local)
		{
			$forum_names_ary = make_forum_select(false, false, true, false, false, false, true);

			// Remove the disabled ones, since we do not create an option field here...
			foreach ($forum_names_ary as $key => $value)
			{
				if (!$value['disabled'])
				{
					continue;
				}
				unset($forum_names_ary[$key]);
			}
		}
		else
		{
			$forum_names_ary[0] = $l_acl_type;
		}

		// Get available roles
		$roles = [];

		$sql = 'SELECT *
			FROM ' . $this->tables['acl_roles'] . "
			WHERE role_type = '" . $this->db->sql_escape($permission_type) . "'
			ORDER BY role_order ASC";
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$roles[$row['role_id']] = $row;
		}
		$this->db->sql_freeresult($result);

		$cur_roles = $this->acl_role_data($user_mode, $permission_type, array_keys($hold_ary));

		// Build js roles array (role data assignments)
		$s_role_js_array = '';

		if (!empty($roles))
		{
			$s_role_js_array = [];

			// Make sure every role (even if empty) has its array defined
			foreach ($roles as $_role_id => $null)
			{
				$s_role_js_array[$_role_id] = "\n" . 'role_options[' . $_role_id . '] = new Array();' . "\n";
			}

			$sql = 'SELECT r.role_id, o.auth_option, r.auth_setting
				FROM ' . $this->tables['acl_roles_data'] . ' r, ' . $this->tables['acl_options'] . ' o
				WHERE o.auth_option_id = r.auth_option_id
					AND ' . $this->db->sql_in_set('r.role_id', array_keys($roles));
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$flag = substr($row['auth_option'], 0, strpos($row['auth_option'], '_') + 1);
				if ($flag == $row['auth_option'])
				{
					continue;
				}

				$s_role_js_array[$row['role_id']] .= 'role_options[' . $row['role_id'] . '][\'' . addslashes($row['auth_option']) . '\'] = ' . $row['auth_setting'] . '; ';
			}
			$this->db->sql_freeresult($result);

			$s_role_js_array = implode('', $s_role_js_array);
		}

		$this->template->assign_var('S_ROLE_JS_ARRAY', $s_role_js_array);
		unset($s_role_js_array);

		// Now obtain memberships
		$user_groups_default = $user_groups_custom = [];
		if ($user_mode === 'user' && $group_display)
		{
			$groups = [];

			$sql = 'SELECT group_id, group_name, group_type
				FROM ' . $this->tables['groups'] . '
				ORDER BY group_type DESC, group_name ASC';
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$groups[$row['group_id']] = $row;
			}
			$this->db->sql_freeresult($result);

			$memberships = group_memberships(false, array_keys($hold_ary), false);

			// User is not a member of any group? Bad admin, bad bad admin...
			if ($memberships)
			{
				foreach ($memberships as $row)
				{
					$user_groups_default[$row['user_id']][] = $this->group_helper->get_name($groups[$row['group_id']]['group_name']);
				}
			}
			unset($memberships, $groups);
		}

		// If we only have one forum id to display or being in local mode and more than one user/group to display,
		// we switch the complete interface to group by user/usergroup instead of grouping by forum
		// To achieve this, we need to switch the array a bit
		if (count($forum_ids) === 1 || ($local && count($ug_names_ary) > 1))
		{
			$hold_ary_temp = $hold_ary;
			$hold_ary = [];
			foreach ($hold_ary_temp as $ug_id => $row)
			{
				foreach ($forum_names_ary as $forum_id => $forum_row)
				{
					if (isset($row[$forum_id]))
					{
						$hold_ary[$forum_id][$ug_id] = $row[$forum_id];
					}
				}
			}
			unset($hold_ary_temp);

			foreach ($hold_ary as $forum_id => $forum_array)
			{
				$content_array = $categories = [];
				$this->build_permission_array($hold_ary[$forum_id], $content_array, $categories, array_keys($ug_names_ary));

				$this->template->assign_block_vars($tpl_mask_p, [
					'NAME'			=> $forum_id == 0 ? $forum_names_ary[0] : $forum_names_ary[$forum_id]['forum_name'],
					'PADDING'		=> $forum_id == 0 ? '' : $forum_names_ary[$forum_id]['padding'],

					'CATEGORIES'	=> implode('</th><th>', $categories),

					'L_ACL_TYPE'	=> $l_acl_type,

					'S_LOCAL'		=> $local,
					'S_GLOBAL'		=> !$local,
					'S_NUM_CATS'	=> count($categories),
					'S_VIEW'		=> $mode === 'view',
					'S_NUM_OBJECTS'	=> count($content_array),
					'S_USER_MODE'	=> $user_mode === 'user',
					'S_GROUP_MODE'	=> $user_mode === 'group',
				]);

				reset($content_array);
				foreach ($content_array as $ug_id => $ug_array)
				{
					$role_options = [];

					$s_role_options = '';
					$current_role_id = isset($cur_roles[$ug_id][$forum_id]) ? $cur_roles[$ug_id][$forum_id] : 0;

					reset($roles);
					foreach ($roles as $role_id => $role_row)
					{
						$role_name = $this->lang->lang($role_row['role_name']);
						$role_desc = $this->lang->is_set($role_row['role_description']) ? $this->lang->lang($role_row['role_description']) : nl2br($role_row['role_description']);

						$title = $role_desc ? ' title="' . $role_desc . '"' : '';
						$s_role_options .= '<option value="' . $role_id . '"' . ($role_id == $current_role_id ? ' selected="selected"' : '') . $title . '>' . $role_name . '</option>';

						$role_options[] = [
							'ID'		=> $role_id,
							'ROLE_NAME'	=> $role_name,
							'TITLE'		=> $role_desc,
							'SELECTED'	=> $role_id == $current_role_id,
						];
					}

					if ($s_role_options)
					{
						$s_role_options = '<option value="0"' . (!$current_role_id ? ' selected="selected"' : '') . ' title="' . htmlspecialchars($this->lang->lang('NO_ROLE_ASSIGNED_EXPLAIN')) . '">' . $this->lang->lang('NO_ROLE_ASSIGNED') . '</option>' . $s_role_options;
					}

					if (!$current_role_id && $mode !== 'view')
					{
						$s_custom_permissions = false;

						foreach ($ug_array as $key => $value)
						{
							if ($value['S_NEVER'] || $value['S_YES'])
							{
								$s_custom_permissions = true;
								break;
							}
						}
					}
					else
					{
						$s_custom_permissions = false;
					}

					$this->template->assign_block_vars($tpl_mask_p . '.' . $tpl_mask_f, [
						'NAME'				=> $ug_names_ary[$ug_id],
						'UG_ID'				=> $ug_id,
						'S_ROLE_OPTIONS'	=> $s_role_options,
						'S_CUSTOM'			=> $s_custom_permissions,
						'FORUM_ID'			=> $forum_id,
						'S_ROLE_ID'			=> $current_role_id,
					]);

					$this->template->assign_block_vars_array($tpl_mask_p . '.' . $tpl_mask_f . '.role_options', $role_options);

					$this->assign_cat_array($ug_array, $tpl_mask_p . '.' . $tpl_mask_f . '.' . $tpl_category, $tpl_mask, $ug_id, $forum_id, ($mode === 'view'), $show_trace);

					unset($content_array[$ug_id]);
				}

				unset($hold_ary[$forum_id]);
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

				$content_array = $categories = [];
				$this->build_permission_array($hold_ary[$ug_id], $content_array, $categories, array_keys($forum_names_ary));

				$this->template->assign_block_vars($tpl_mask_p, [
					'NAME'			=> $ug_name,
					'CATEGORIES'	=> implode('</th><th>', $categories),

					'USER_GROUPS_DEFAULT'	=> ($user_mode === 'user' && !empty($user_groups_default[$ug_id])) ? implode($this->lang->lang('COMMA_SEPARATOR'), $user_groups_default[$ug_id]) : '',
					'USER_GROUPS_CUSTOM'	=> ($user_mode === 'user' && !empty($user_groups_custom[$ug_id])) ? implode($this->lang->lang('COMMA_SEPARATOR'), $user_groups_custom[$ug_id]) : '',
					'L_ACL_TYPE'			=> $l_acl_type,

					'S_LOCAL'		=> $local,
					'S_GLOBAL'		=> !$local,
					'S_NUM_CATS'	=> count($categories),
					'S_VIEW'		=> $mode === 'view',
					'S_NUM_OBJECTS'	=> count($content_array),
					'S_USER_MODE'	=> $user_mode === 'user',
					'S_GROUP_MODE'	=> $user_mode === 'group',
				]);

				reset($content_array);
				foreach ($content_array as $forum_id => $forum_array)
				{
					$role_options = [];

					$current_role_id = (isset($cur_roles[$ug_id][$forum_id])) ? $cur_roles[$ug_id][$forum_id] : 0;
					$s_role_options = '';

					reset($roles);
					foreach ($roles as $role_id => $role_row)
					{
						$role_name = $this->lang->lang($role_row['role_name']);
						$role_desc = $this->lang->is_set($role_row['role_description']) ? $this->lang->lang($role_row['role_description']) : nl2br($role_row['role_description']);

						$title = $role_desc ? ' title="' . $role_desc . '"' : '';
						$s_role_options .= '<option value="' . $role_id . '"' . ($role_id == $current_role_id ? ' selected="selected"' : '') . $title . '>' . $role_name . '</option>';

						$role_options[] = [
							'ID'		=> $role_id,
							'ROLE_NAME'	=> $role_name,
							'TITLE'		=> $role_desc,
							'SELECTED'	=> $role_id == $current_role_id,
						];
					}

					if ($s_role_options)
					{
						$s_role_options = '<option value="0"' . ((!$current_role_id) ? ' selected="selected"' : '') . ' title="' . htmlspecialchars($this->lang->lang('NO_ROLE_ASSIGNED_EXPLAIN')) . '">' . $this->lang->lang('NO_ROLE_ASSIGNED') . '</option>' . $s_role_options;
					}

					if (!$current_role_id && $mode !== 'view')
					{
						$s_custom_permissions = false;

						foreach ($forum_array as $key => $value)
						{
							if ($value['S_NEVER'] || $value['S_YES'])
							{
								$s_custom_permissions = true;
								break;
							}
						}
					}
					else
					{
						$s_custom_permissions = false;
					}

					$this->template->assign_block_vars($tpl_mask_p . '.' . $tpl_mask_f, [
						'NAME'				=> $forum_id == 0 ? $forum_names_ary[0] : $forum_names_ary[$forum_id]['forum_name'],
						'PADDING'			=> $forum_id == 0 ? '' : $forum_names_ary[$forum_id]['padding'],
						'S_CUSTOM'			=> $s_custom_permissions,
						'UG_ID'				=> $ug_id,
						'S_ROLE_OPTIONS'	=> $s_role_options,
						'FORUM_ID'			=> $forum_id,
					]);

					$this->template->assign_block_vars_array($tpl_mask_p . '.' . $tpl_mask_f . '.role_options', $role_options);

					$this->assign_cat_array($forum_array, $tpl_mask_p . '.' . $tpl_mask_f . '.' . $tpl_category, $tpl_mask, $ug_id, $forum_id, ($mode === 'view'), $show_trace);
				}

				unset($hold_ary[$ug_id], $ug_names_ary[$ug_id]);
			}
		}
	}

	/**
	 * Display permission mask for roles
	 *
	 * @param array		$hold_ary
	 * @return void
	 */
	function display_role_mask(array &$hold_ary)
	{
		if (empty($hold_ary))
		{
			return;
		}

		// Get forum names
		// If the role is used globally, then reflect that
		$forum_names = isset($hold_ary[0]) ? [0 => ''] : [];

		$sql = 'SELECT forum_id, forum_name
			FROM ' . $this->tables['forums'] . '
			WHERE ' . $this->db->sql_in_set('forum_id', array_keys($hold_ary)) . '
			ORDER BY left_id';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$forum_names[$row['forum_id']] = $row['forum_name'];
		}
		$this->db->sql_freeresult($result);

		foreach ($forum_names as $forum_id => $forum_name)
		{
			$auth_ary = $hold_ary[$forum_id];

			$this->template->assign_block_vars('role_mask', [
				'NAME'				=> $forum_id == 0 ? $this->lang->lang('GLOBAL_MASK') : $forum_name,
				'FORUM_ID'			=> $forum_id,
			]);

			if (!empty($auth_ary['users']))
			{
				$sql = 'SELECT user_id, username
					FROM ' . $this->tables['users'] . '
					WHERE ' . $this->db->sql_in_set('user_id', $auth_ary['users']) . '
					ORDER BY username_clean ASC';
				$result = $this->db->sql_query($sql);
				while ($row = $this->db->sql_fetchrow($result))
				{
					$this->template->assign_block_vars('role_mask.users', [
						'USER_ID'		=> $row['user_id'],
						'USERNAME'		=> get_username_string('username', $row['user_id'], $row['username']),
						'U_PROFILE'		=> get_username_string('profile', $row['user_id'], $row['username']),
					]);
				}
				$this->db->sql_freeresult($result);
			}

			if (!empty($auth_ary['groups']))
			{
				$sql = 'SELECT group_id, group_name, group_type
					FROM ' . $this->tables['groups'] . '
					WHERE ' . $this->db->sql_in_set('group_id', $auth_ary['groups']) . '
					ORDER BY group_type ASC, group_name';
				$result = $this->db->sql_query($sql);
				while ($row = $this->db->sql_fetchrow($result))
				{
					$this->template->assign_block_vars('role_mask.groups', [
						'GROUP_ID'		=> $row['group_id'],
						'GROUP_NAME'	=> $this->group_helper->get_name($row['group_name']),
						'U_PROFILE'		=> append_sid("{$this->root_path}memberlist.$this->php_ext", "mode=group&amp;g={$row['group_id']}"),
					]);
				}
				$this->db->sql_freeresult($result);
			}
		}
	}

	/**
	 * Set a user or group ACL record
	 *
	 * @param string		$ug_type			The type (user|group)
	 * @param array|int		$forum_id			The forum identifiers
	 * @param array|int		$ug_id				The type's identifiers
	 * @param array			$auth				The auth data
	 * @param int			$role_id			The role identifier
	 * @param bool			$clear_prefetch
	 * @return void
	 */
	function acl_set($ug_type, $forum_id, $ug_id, array $auth, $role_id = 0, $clear_prefetch = true)
	{
		$forum_id = !is_array($forum_id) ? [$forum_id] : $forum_id;
		$ug_id = !is_array($ug_id) ? [$ug_id] : $ug_id;

		$ug_id_sql = $this->db->sql_in_set($ug_type . '_id', array_map('intval', $ug_id));
		$forum_sql = $this->db->sql_in_set('forum_id', array_map('intval', $forum_id));

		// Instead of updating, inserting, removing we just remove all current settings and re-set everything...
		$table = ($ug_type === 'user') ? $this->tables['acl_users'] : $this->tables['acl_groups'];
		$id_field = $ug_type . '_id';

		// Get any flags as required
		reset($auth);
		$flag = key($auth);
		$flag = substr($flag, 0, strpos($flag, '_') + 1);

		// This ID (the any-flag) is set if one or more permissions are true...
		$any_option_id = (int) $this->acl_options['id'][$flag];

		// Remove any-flag from auth ary
		if (isset($auth[$flag]))
		{
			unset($auth[$flag]);
		}

		// Remove current auth options...
		$auth_option_ids = [(int) $any_option_id];
		foreach ($auth as $auth_option => $auth_setting)
		{
			$auth_option_ids[] = (int) $this->acl_options['id'][$auth_option];
		}

		$sql = "DELETE FROM $table
			WHERE $forum_sql
				AND $ug_id_sql
				AND " . $this->db->sql_in_set('auth_option_id', $auth_option_ids);
		$this->db->sql_query($sql);

		// Remove those having a role assigned... the correct type of course...
		$role_ids = [];

		$sql = 'SELECT role_id
			FROM ' . $this->tables['acl_roles'] . "
			WHERE role_type = '" . $this->db->sql_escape($flag) . "'";
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$role_ids[] = $row['role_id'];
		}
		$this->db->sql_freeresult($result);

		if (!empty($role_ids))
		{
			$sql = "DELETE FROM $table
				WHERE $forum_sql
					AND $ug_id_sql
					AND auth_option_id = 0
					AND " . $this->db->sql_in_set('auth_role_id', $role_ids);
			$this->db->sql_query($sql);
		}

		// Ok, include the any-flag if one or more auth options are set to yes...
		foreach ($auth as $auth_option => $setting)
		{
			if ($setting == ACL_YES && (!isset($auth[$flag]) || $auth[$flag] == ACL_NEVER))
			{
				$auth[$flag] = ACL_YES;
			}
		}

		$sql_ary = [];
		foreach ($forum_id as $forum)
		{
			$forum = (int) $forum;

			if ($role_id)
			{
				foreach ($ug_id as $id)
				{
					$sql_ary[] = [
						$id_field			=> (int) $id,
						'forum_id'			=> (int) $forum,
						'auth_option_id'	=> 0,
						'auth_setting'		=> 0,
						'auth_role_id'		=> (int) $role_id,
					];
				}
			}
			else
			{
				foreach ($auth as $auth_option => $setting)
				{
					$auth_option_id = (int) $this->acl_options['id'][$auth_option];

					if ($setting != ACL_NO)
					{
						foreach ($ug_id as $id)
						{
							$sql_ary[] = [
								$id_field			=> (int) $id,
								'forum_id'			=> (int) $forum,
								'auth_option_id'	=> (int) $auth_option_id,
								'auth_setting'		=> (int) $setting,
							];
						}
					}
				}
			}
		}

		$this->db->sql_multi_insert($table, $sql_ary);

		if ($clear_prefetch)
		{
			$this->acl_clear_prefetch();
		}
	}

	/**
	 * Set a role-specific ACL record
	 *
	 * @param int		$role_id		The role identifier
	 * @param array		$auth			The auth data
	 * @return void
	 */
	function acl_set_role($role_id, array $auth)
	{
		// Get any-flag as required
		reset($auth);
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
			if ($setting == ACL_YES && (!isset($auth[$flag]) || $auth[$flag] == ACL_NEVER))
			{
				$auth[$flag] = ACL_YES;
			}
		}

		$sql_ary = [];
		foreach ($auth as $auth_option => $setting)
		{
			$auth_option_id = (int) $this->acl_options['id'][$auth_option];

			if ($setting != ACL_NO)
			{
				$sql_ary[] = [
					'role_id'			=> (int) $role_id,
					'auth_option_id'	=> (int) $auth_option_id,
					'auth_setting'		=> (int) $setting,
				];
			}
		}

		// If no data is there, we set the any-flag to ACL_NEVER...
		if (empty($sql_ary))
		{
			$sql_ary[] = [
				'role_id'			=> (int) $role_id,
				'auth_option_id'	=> (int) $this->acl_options['id'][$flag],
				'auth_setting'		=> ACL_NEVER,
			];
		}

		// Remove current auth options...
		$sql = 'DELETE FROM ' . $this->tables['acl_roles_data'] . '
			WHERE role_id = ' . (int) $role_id;
		$this->db->sql_query($sql);

		// Now insert the new values
		$this->db->sql_multi_insert($this->tables['acl_roles_data'], $sql_ary);

		$this->acl_clear_prefetch();
	}

	/**
	 * Remove local permission
	 *
	 * @param string			$mode				The mode (user|group)
	 * @param array|int|false	$ug_id				The mode's identifiers
	 * @param array|int|false	$forum_id			The forum identifiers
	 * @param string|false		$permission_type	The permission type
	 * @return void
	 */
	function acl_delete($mode, $ug_id = false, $forum_id = false, $permission_type = false)
	{
		if ($ug_id === false && $forum_id === false)
		{
			return;
		}

		$option_id_ary = [];
		$table = ($mode === 'user') ? $this->tables['acl_users'] : $this->tables['acl_groups'];
		$id_field = $mode . '_id';

		$where_sql = [];

		if ($forum_id !== false)
		{
			$where_sql[] = (!is_array($forum_id)) ? 'forum_id = ' . (int) $forum_id : $this->db->sql_in_set('forum_id', array_map('intval', $forum_id));
		}

		if ($ug_id !== false)
		{
			$where_sql[] = (!is_array($ug_id)) ? $id_field . ' = ' . (int) $ug_id : $this->db->sql_in_set($id_field, array_map('intval', $ug_id));
		}

		// There seem to be auth options involved, therefore we need to go through the list and make sure we capture roles correctly
		if ($permission_type !== false)
		{
			$auth_id_ary = [];

			// Get permission type
			$sql = 'SELECT auth_option, auth_option_id
				FROM ' . $this->tables['acl_options'] . "
				WHERE auth_option " . $this->db->sql_like_expression($permission_type . $this->db->get_any_char());
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$option_id_ary[] = $row['auth_option_id'];
				$auth_id_ary[$row['auth_option']] = ACL_NO;
			}
			$this->db->sql_freeresult($result);

			// First of all, lets grab the items having roles with the specified auth options assigned
			$cur_role_auth = [];

			$sql = "SELECT auth_role_id, $id_field, forum_id
				FROM $table, " . $this->tables['acl_roles'] . " r
				WHERE auth_role_id <> 0
					AND auth_role_id = r.role_id
					AND r.role_type = '{$permission_type}'
					AND " . implode(' AND ', $where_sql) . '
				ORDER BY auth_role_id';
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$cur_role_auth[$row['auth_role_id']][$row['forum_id']][] = $row[$id_field];
			}
			$this->db->sql_freeresult($result);

			// Get role data for resetting data
			if (!empty($cur_role_auth))
			{
				$auth_settings = [];

				$sql = 'SELECT ao.auth_option, rd.role_id, rd.auth_setting
					FROM ' . $this->tables['acl_options'] . ' ao, ' . $this->tables['acl_roles_data'] . ' rd
					WHERE ao.auth_option_id = rd.auth_option_id
						AND ' . $this->db->sql_in_set('rd.role_id', array_keys($cur_role_auth));
				$result = $this->db->sql_query($sql);
				while ($row = $this->db->sql_fetchrow($result))
				{
					// We need to fill all auth_options, else setting it will fail...
					if (!isset($auth_settings[$row['role_id']]))
					{
						$auth_settings[$row['role_id']] = $auth_id_ary;
					}
					$auth_settings[$row['role_id']][$row['auth_option']] = $row['auth_setting'];
				}
				$this->db->sql_freeresult($result);

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
			$where_sql[] = $this->db->sql_in_set('auth_option_id', array_map('intval', $option_id_ary));
		}

		$sql = "DELETE FROM $table
			WHERE " . implode(' AND ', $where_sql);
		$this->db->sql_query($sql);

		$this->acl_clear_prefetch();
	}

	/**
	 * Assign category to template.
	 *
	 * used by display_mask()
	 *
	 * @param array		$category_array
	 * @param string	$tpl_cat
	 * @param string	$tpl_mask
	 * @param int		$ug_id
	 * @param int		$forum_id
	 * @param bool		$s_view
	 * @param bool		$show_trace
	 * @return void
	 */
	function assign_cat_array(array &$category_array, $tpl_cat, $tpl_mask, $ug_id, $forum_id, $s_view, $show_trace = false)
	{
		reset($category_array);
		foreach ($category_array as $cat => $cat_array)
		{
			if (!$this->permissions->category_defined($cat))
			{
				continue;
			}

			$this->template->assign_block_vars($tpl_cat, [
				'S_YES'		=> ($cat_array['S_YES'] && !$cat_array['S_NEVER'] && !$cat_array['S_NO']) ? true : false,
				'S_NEVER'	=> ($cat_array['S_NEVER'] && !$cat_array['S_YES'] && !$cat_array['S_NO']) ? true : false,
				'S_NO'		=> ($cat_array['S_NO'] && !$cat_array['S_NEVER'] && !$cat_array['S_YES']) ? true : false,

				'CAT_NAME'	=> $this->permissions->get_category_lang($cat),
			]);

			/**
			 * @todo
			 * Sort permissions by name (more naturally and user friendly than sorting by a primary key)
			 *	Commented out due to it's memory consumption and time needed
			 *
			$key_array = array_intersect(array_keys($this->lang->lang), array_map(create_function('$a', 'return "acl_" . $a;'), array_keys($cat_array['permissions'])));
			$values_array = $cat_array['permissions'];

			$cat_array['permissions'] = array();

			foreach ($key_array as $key)
			{
				$key = str_replace('acl_', '', $key);
				$cat_array['permissions'][$key] = $values_array[$key];
			}
			unset($key_array, $values_array);
*/
			reset($cat_array['permissions']);
			foreach ($cat_array['permissions'] as $permission => $allowed)
			{
				if (!$this->permissions->permission_defined($permission))
				{
					continue;
				}

				if ($s_view)
				{
					$this->template->assign_block_vars($tpl_cat . '.' . $tpl_mask, [
						'S_YES'		=> $allowed == ACL_YES,
						'S_NEVER'	=> $allowed == ACL_NEVER,

						'UG_ID'			=> $ug_id,
						'FORUM_ID'		=> $forum_id,
						'FIELD_NAME'	=> $permission,
						'S_FIELD_NAME'	=> 'setting[' . $ug_id . '][' . $forum_id . '][' . $permission . ']',

						'U_TRACE'		=> $show_trace ? $this->helper->route('acp_permissions', ['mode' => 'trace', 'u' => $ug_id, 'f' => $forum_id, 'auth' => $permission]) : '',
						'UA_TRACE'		=> $show_trace ? addslashes($this->helper->route('acp_permissions', ['mode' => 'trace', 'u' => $ug_id, 'f' => $forum_id, 'auth' => $permission])) : '',

						'PERMISSION'	=> $this->permissions->get_permission_lang($permission),
					]);
				}
				else
				{
					$this->template->assign_block_vars($tpl_cat . '.' . $tpl_mask, [
						'S_YES'		=> $allowed == ACL_YES,
						'S_NEVER'	=> $allowed == ACL_NEVER,
						'S_NO'		=> $allowed == ACL_NO,

						'UG_ID'			=> $ug_id,
						'FORUM_ID'		=> $forum_id,
						'FIELD_NAME'	=> $permission,
						'S_FIELD_NAME'	=> 'setting[' . $ug_id . '][' . $forum_id . '][' . $permission . ']',

						'U_TRACE'		=> $show_trace ? $this->helper->route('acp_permissions', ['mode' => 'trace', 'u' => $ug_id, 'f' => $forum_id, 'auth' => $permission]) : '',
						'UA_TRACE'		=> $show_trace ? addslashes($this->helper->route('acp_permissions', ['mode' => 'trace', 'u' => $ug_id, 'f' => $forum_id, 'auth' => $permission])) : '',

						'PERMISSION'	=> $this->permissions->get_permission_lang($permission),
					]);
				}
			}
		}
	}

	/**
	 * Building content array from permission rows with explicit key ordering.
	 *
	 *
	 * used by display_mask()
	 *
	 * @param array		$permission_row
	 * @param array		$content_array
	 * @param array		$categories
	 * @param array		$key_sort_array
	 * @return void
	 */
	function build_permission_array(array &$permission_row, array &$content_array, array &$categories, array $key_sort_array)
	{
		foreach ($key_sort_array as $forum_id)
		{
			if (!isset($permission_row[$forum_id]))
			{
				continue;
			}

			$permissions = $permission_row[$forum_id];

			ksort($permissions);
			reset($permissions);

			foreach ($permissions as $permission => $auth_setting)
			{
				$cat = $this->permissions->get_permission_category($permission);

				// Build our categories array
				if (!isset($categories[$cat]))
				{
					$categories[$cat] = $this->permissions->get_category_lang($cat);
				}

				// Build our content array
				if (!isset($content_array[$forum_id]))
				{
					$content_array[$forum_id] = [];
				}

				if (!isset($content_array[$forum_id][$cat]))
				{
					$content_array[$forum_id][$cat] = [
						'S_YES'			=> false,
						'S_NEVER'		=> false,
						'S_NO'			=> false,
						'permissions'	=> [],
					];
				}

				$content_array[$forum_id][$cat]['S_YES'] |= ($auth_setting == ACL_YES) ? true : false;
				$content_array[$forum_id][$cat]['S_NEVER'] |= ($auth_setting == ACL_NEVER) ? true : false;
				$content_array[$forum_id][$cat]['S_NO'] |= ($auth_setting == ACL_NO) ? true : false;

				$content_array[$forum_id][$cat]['permissions'][$permission] = $auth_setting;
			}
		}
	}

	/**
	 * Use permissions from another user. This transfers a permission set from one user to another.
	 * The other user is always able to revert back to his permission set.
	 * This function does not check for lower/higher permissions, it is possible for the user to gain
	 * "more" permissions by this.
	 * Admin permissions will not be copied.
	 *
	 * @param int	$from_user_id
	 * @param int	$to_user_id
	 * @return bool
	 */
	function ghost_permissions($from_user_id, $to_user_id)
	{
		if ($to_user_id == ANONYMOUS)
		{
			return false;
		}

		$hold_ary = $this->acl_raw_data_single_user($from_user_id);

		// Key 0 in $hold_ary are global options, all others are forum_ids

		// We disallow copying admin permissions
		foreach ($this->acl_options['global'] as $opt => $id)
		{
			if (strpos($opt, 'a_') === 0)
			{
				$hold_ary[0][$this->acl_options['id'][$opt]] = ACL_NEVER;
			}
		}

		// Force a_switchperm to be allowed
		$hold_ary[0][$this->acl_options['id']['a_switchperm']] = ACL_YES;

		$user_permissions = $this->build_bitstring($hold_ary);

		if (!$user_permissions)
		{
			return false;
		}

		$sql = 'UPDATE ' . $this->tables['users'] . "
			SET user_permissions = '" . $this->db->sql_escape($user_permissions) . "',
				user_perm_from = $from_user_id
			WHERE user_id = " . $to_user_id;
		$this->db->sql_query($sql);

		return true;
	}
}
