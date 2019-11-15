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

namespace phpbb\acp\controller;

class permissions
{
	var $u_action;
	var $permission_dropdown;

	/**
	 * @var $phpbb_permissions \phpbb\permissions
	 */
	protected $permissions;

	public function main($id, $mode)
	{
		if (!function_exists('user_get_id_name'))
		{
			include($this->root_path . 'includes/functions_user.' . $this->php_ext);
		}

		if (!class_exists('auth_admin'))
		{
			include($this->root_path . 'includes/acp/auth.' . $this->php_ext);
		}

		$this->permissions = $phpbb_container->get('acl.permissions');

		$auth_admin = new auth_admin();

		$this->language->add_lang('acp/permissions');
		add_permission_language();

		$this->tpl_name = 'acp_permissions';

		// Trace has other vars
		if ($mode == 'trace')
		{
			$user_id = $this->request->variable('u', 0);
			$forum_id = $this->request->variable('f', 0);
			$permission = $this->request->variable('auth', '');

			$this->tpl_name = 'permission_trace';

			if ($user_id && isset($auth_admin->acl_options['id'][$permission]) && $this->auth->acl_get('a_viewauth'))
			{
				$this->page_title = sprintf($this->language->lang('TRACE_PERMISSION'), $this->permissions->get_permission_lang($permission));
				$this->permission_trace($user_id, $forum_id, $permission);
				return;
			}
			trigger_error('NO_MODE', E_USER_ERROR);
		}

		// Copy forum permissions
		if ($mode == 'setting_forum_copy')
		{
			$this->tpl_name = 'permission_forum_copy';

			if ($this->auth->acl_get('a_fauth') && $this->auth->acl_get('a_authusers') && $this->auth->acl_get('a_authgroups') && $this->auth->acl_get('a_mauth'))
			{
				$this->page_title = 'ACP_FORUM_PERMISSIONS_COPY';
				$this->copy_forum_permissions();
				return;
			}

			trigger_error('NO_MODE', E_USER_ERROR);
		}

		// Set some vars
		$action = $this->request->variable('action', ['' => 0]);
		$action = key($action);
		$action = ($this->request->is_set_post('psubmit')) ? 'apply_permissions' : $action;

		$all_forums = $this->request->variable('all_forums', 0);
		$subforum_id = $this->request->variable('subforum_id', 0);
		$forum_id = $this->request->variable('forum_id', [0]);

		$username = $this->request->variable('username', [''], true);
		$usernames = $this->request->variable('usernames', '', true);
		$user_id = $this->request->variable('user_id', [0]);

		$group_id = $this->request->variable('group_id', [0]);
		$select_all_groups = $this->request->variable('select_all_groups', 0);

		$form_key = 'acp_permissions';
		add_form_key($form_key);

		// If select all groups is set, we pre-build the group id array (this option is used for other screens to link to the permission settings screen)
		if ($select_all_groups)
		{
			// Add default groups to selection
			$sql_and = (!$this->config['coppa_enable']) ? " AND group_name <> 'REGISTERED_COPPA'" : '';

			$sql = 'SELECT group_id
				FROM ' . GROUPS_TABLE . '
				WHERE group_type = ' . GROUP_SPECIAL . "
				$sql_and";
			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$group_id[] = $row['group_id'];
			}
			$this->db->sql_freeresult($result);
		}

		// Map usernames to ids and vice versa
		if ($usernames)
		{
			$username = explode("\n", $usernames);
		}
		unset($usernames);

		if (count($username) && !count($user_id))
		{
			user_get_id_name($user_id, $username);

			if (!count($user_id))
			{
				trigger_error($this->language->lang('SELECTED_USER_NOT_EXIST') . adm_back_link($this->u_action), E_USER_WARNING);
			}
		}
		unset($username);

		// Build forum ids (of all forums are checked or subforum listing used)
		if ($all_forums)
		{
			$sql = 'SELECT forum_id
				FROM ' . FORUMS_TABLE . '
				ORDER BY left_id';
			$result = $this->db->sql_query($sql);

			$forum_id = [];
			while ($row = $this->db->sql_fetchrow($result))
			{
				$forum_id[] = (int) $row['forum_id'];
			}
			$this->db->sql_freeresult($result);
		}
		else if ($subforum_id)
		{
			$forum_id = [];
			foreach (get_forum_branch($subforum_id, 'children') as $row)
			{
				$forum_id[] = (int) $row['forum_id'];
			}
		}

		// Define some common variables for every mode
		$permission_scope = (strpos($mode, '_global') !== false) ? 'global' : 'local';

		// Showing introductionary page?
		if ($mode == 'intro')
		{
			$this->page_title = 'ACP_PERMISSIONS';

			$this->template->assign_vars([
				'S_INTRO'		=> true]
			);

			return;
		}

		switch ($mode)
		{
			case 'setting_user_global':
			case 'setting_group_global':
				$this->permission_dropdown = ['u_', 'm_', 'a_'];
				$permission_victim = ($mode == 'setting_user_global') ? ['user'] : ['group'];
				$this->page_title = ($mode == 'setting_user_global') ? 'ACP_USERS_PERMISSIONS' : 'ACP_GROUPS_PERMISSIONS';
			break;

			case 'setting_user_local':
			case 'setting_group_local':
				$this->permission_dropdown = ['f_', 'm_'];
				$permission_victim = ($mode == 'setting_user_local') ? ['user', 'forums'] : ['group', 'forums'];
				$this->page_title = ($mode == 'setting_user_local') ? 'ACP_USERS_FORUM_PERMISSIONS' : 'ACP_GROUPS_FORUM_PERMISSIONS';
			break;

			case 'setting_admin_global':
			case 'setting_mod_global':
				$this->permission_dropdown = (strpos($mode, '_admin_') !== false) ? ['a_'] : ['m_'];
				$permission_victim = ['usergroup'];
				$this->page_title = ($mode == 'setting_admin_global') ? 'ACP_ADMINISTRATORS' : 'ACP_GLOBAL_MODERATORS';
			break;

			case 'setting_mod_local':
			case 'setting_forum_local':
				$this->permission_dropdown = ($mode == 'setting_mod_local') ? ['m_'] : ['f_'];
				$permission_victim = ['forums', 'usergroup'];
				$this->page_title = ($mode == 'setting_mod_local') ? 'ACP_FORUM_MODERATORS' : 'ACP_FORUM_PERMISSIONS';
			break;

			case 'view_admin_global':
			case 'view_user_global':
			case 'view_mod_global':
				$this->permission_dropdown = ($mode == 'view_admin_global') ? ['a_'] : (($mode == 'view_user_global') ? ['u_'] : ['m_']);
				$permission_victim = ['usergroup_view'];
				$this->page_title = ($mode == 'view_admin_global') ? 'ACP_VIEW_ADMIN_PERMISSIONS' : (($mode == 'view_user_global') ? 'ACP_VIEW_USER_PERMISSIONS' : 'ACP_VIEW_GLOBAL_MOD_PERMISSIONS');
			break;

			case 'view_mod_local':
			case 'view_forum_local':
				$this->permission_dropdown = ($mode == 'view_mod_local') ? ['m_'] : ['f_'];
				$permission_victim = ['forums', 'usergroup_view'];
				$this->page_title = ($mode == 'view_mod_local') ? 'ACP_VIEW_FORUM_MOD_PERMISSIONS' : 'ACP_VIEW_FORUM_PERMISSIONS';
			break;

			default:
				trigger_error('NO_MODE', E_USER_ERROR);
			break;
		}

		$this->template->assign_vars([
			'L_TITLE'		=> $this->language->lang[$this->page_title],
			'L_EXPLAIN'		=> $this->language->lang[$this->page_title . '_EXPLAIN']]
		);

		// Get permission type
		$permission_type = $this->request->variable('type', $this->permission_dropdown[0]);

		if (!in_array($permission_type, $this->permission_dropdown))
		{
			trigger_error($this->language->lang('WRONG_PERMISSION_TYPE') . adm_back_link($this->u_action), E_USER_WARNING);
		}

		// Handle actions
		if (strpos($mode, 'setting_') === 0 && $action)
		{
			switch ($action)
			{
				case 'delete':
					if (confirm_box(true))
					{
						// All users/groups selected?
						$all_users = ($this->request->is_set_post('all_users')) ? true : false;
						$all_groups = ($this->request->is_set_post('all_groups')) ? true : false;

						if ($all_users || $all_groups)
						{
							$items = $this->retrieve_defined_user_groups($permission_scope, $forum_id, $permission_type);

							if ($all_users && count($items['user_ids']))
							{
								$user_id = $items['user_ids'];
							}
							else if ($all_groups && count($items['group_ids']))
							{
								$group_id = $items['group_ids'];
							}
						}

						if (count($user_id) || count($group_id))
						{
							$this->remove_permissions($mode, $permission_type, $auth_admin, $user_id, $group_id, $forum_id);
						}
						else
						{
							trigger_error($this->language->lang('NO_USER_GROUP_SELECTED') . adm_back_link($this->u_action), E_USER_WARNING);
						}
					}
					else
					{
						if ($this->request->is_set_post('cancel'))
						{
							$u_redirect = $this->u_action . '&amp;type=' . $permission_type;
							foreach ($forum_id as $fid)
							{
								$u_redirect .= '&amp;forum_id[]=' . $fid;
							}
							redirect($u_redirect);
						}

						$s_hidden_fields = [
							'i'				=> $id,
							'mode'			=> $mode,
							'action'		=> [$action => 1],
							'user_id'		=> $user_id,
							'group_id'		=> $group_id,
							'forum_id'		=> $forum_id,
							'type'			=> $permission_type,
						];
						if ($this->request->is_set_post('all_users'))
						{
							$s_hidden_fields['all_users'] = 1;
						}
						if ($this->request->is_set_post('all_groups'))
						{
							$s_hidden_fields['all_groups'] = 1;
						}
						confirm_box(false, $this->language->lang('CONFIRM_OPERATION'), build_hidden_fields($s_hidden_fields));
					}
				break;

				case 'apply_permissions':
					if (!$this->request->is_set_post('setting'))
					{
						send_status_line(403, 'Forbidden');
						trigger_error($this->language->lang('NO_AUTH_SETTING_FOUND') . adm_back_link($this->u_action), E_USER_WARNING);
					}
					if (!check_form_key($form_key))
					{
						trigger_error($this->language->lang('FORM_INVALID'). adm_back_link($this->u_action), E_USER_WARNING);
					}

					$this->set_permissions($mode, $permission_type, $auth_admin, $user_id, $group_id);
				break;

				case 'apply_all_permissions':
					if (!$this->request->is_set_post('setting'))
					{
						send_status_line(403, 'Forbidden');
						trigger_error($this->language->lang('NO_AUTH_SETTING_FOUND') . adm_back_link($this->u_action), E_USER_WARNING);
					}
					if (!check_form_key($form_key))
					{
						trigger_error($this->language->lang('FORM_INVALID'). adm_back_link($this->u_action), E_USER_WARNING);
					}

					$this->set_all_permissions($mode, $permission_type, $auth_admin, $user_id, $group_id);
				break;
			}
		}

		// Go through the screens/options needed and present them in correct order
		foreach ($permission_victim as $victim)
		{
			switch ($victim)
			{
				case 'forum_dropdown':

					if (count($forum_id))
					{
						$this->check_existence('forum', $forum_id);
						continue 2;
					}

					$this->template->assign_vars([
						'S_SELECT_FORUM'		=> true,
						'S_FORUM_OPTIONS'		=> make_forum_select(false, false, true, false, false)]
					);

				break;

				case 'forums':

					if (count($forum_id))
					{
						$this->check_existence('forum', $forum_id);
						continue 2;
					}

					$forum_list = make_forum_select(false, false, true, false, false, false, true);

					// Build forum options
					$s_forum_options = '';
					foreach ($forum_list as $f_id => $f_row)
					{
						$s_forum_options .= '<option value="' . $f_id . '"' . (($f_row['selected']) ? ' selected="selected"' : '') . (($f_row['disabled']) ? ' disabled="disabled" class="disabled-option"' : '') . '>' . $f_row['padding'] . $f_row['forum_name'] . '</option>';
					}

					// Build subforum options
					$s_subforum_options = $this->build_subforum_options($forum_list);

					$this->template->assign_vars([
						'S_SELECT_FORUM'		=> true,
						'S_FORUM_OPTIONS'		=> $s_forum_options,
						'S_SUBFORUM_OPTIONS'	=> $s_subforum_options,
						'S_FORUM_ALL'			=> true,
						'S_FORUM_MULTIPLE'		=> true]
					);

				break;

				case 'user':

					if (count($user_id))
					{
						$this->check_existence('user', $user_id);
						continue 2;
					}

					$this->template->assign_vars([
						'S_SELECT_USER'			=> true,
						'U_FIND_USERNAME'		=> append_sid("{$this->root_path}memberlist.$this->php_ext", 'mode=searchuser&amp;form=select_victim&amp;field=username&amp;select_single=true'),
					]);

				break;

				case 'group':

					if (count($group_id))
					{
						$this->check_existence('group', $group_id);
						continue 2;
					}

					$this->template->assign_vars([
						'S_SELECT_GROUP'		=> true,
						'S_GROUP_OPTIONS'		=> group_select_options(false, false, false), // Show all groups
					]);

				break;

				case 'usergroup':
				case 'usergroup_view':

					$all_users = ($this->request->is_set_post('all_users')) ? true : false;
					$all_groups = ($this->request->is_set_post('all_groups')) ? true : false;

					if ((count($user_id) && !$all_users) || (count($group_id) && !$all_groups))
					{
						if (count($user_id))
						{
							$this->check_existence('user', $user_id);
						}

						if (count($group_id))
						{
							$this->check_existence('group', $group_id);
						}

						continue 2;
					}

					// Now we check the users... because the "all"-selection is different here (all defined users/groups)
					$items = $this->retrieve_defined_user_groups($permission_scope, $forum_id, $permission_type);

					if ($all_users && count($items['user_ids']))
					{
						$user_id = $items['user_ids'];
						continue 2;
					}

					if ($all_groups && count($items['group_ids']))
					{
						$group_id = $items['group_ids'];
						continue 2;
					}

					$this->template->assign_vars([
						'S_SELECT_USERGROUP'		=> ($victim == 'usergroup') ? true : false,
						'S_SELECT_USERGROUP_VIEW'	=> ($victim == 'usergroup_view') ? true : false,
						'S_DEFINED_USER_OPTIONS'	=> $items['user_ids_options'],
						'S_DEFINED_GROUP_OPTIONS'	=> $items['group_ids_options'],
						'S_ADD_GROUP_OPTIONS'		=> group_select_options(false, $items['group_ids'], false),	// Show all groups
						'U_FIND_USERNAME'			=> append_sid("{$this->root_path}memberlist.$this->php_ext", 'mode=searchuser&amp;form=add_user&amp;field=username&amp;select_single=true'),
					]);

				break;
			}

			// The S_ALLOW_SELECT parameter below is a measure to lower memory usage.
			// If there are more than 5 forums selected the admin is not able to select all users/groups too.
			// We need to see if the number of forums can be increased or need to be decreased.

			// Setting permissions screen
			$s_hidden_fields = build_hidden_fields([
					'user_id'		=> $user_id,
					'group_id'		=> $group_id,
					'forum_id'		=> $forum_id,
					'type'			=> $permission_type,
			]);

			$this->template->assign_vars([
				'U_ACTION'				=> $this->u_action,
				'ANONYMOUS_USER_ID'		=> ANONYMOUS,

				'S_SELECT_VICTIM'		=> true,
				'S_ALLOW_ALL_SELECT'	=> (count($forum_id) > 5) ? false : true,
				'S_CAN_SELECT_USER'		=> ($this->auth->acl_get('a_authusers')) ? true : false,
				'S_CAN_SELECT_GROUP'	=> ($this->auth->acl_get('a_authgroups')) ? true : false,
				'S_HIDDEN_FIELDS'		=> $s_hidden_fields]
			);

			// Let the forum names being displayed
			if (count($forum_id))
			{
				$sql = 'SELECT forum_name
					FROM ' . FORUMS_TABLE . '
					WHERE ' . $this->db->sql_in_set('forum_id', $forum_id) . '
					ORDER BY left_id ASC';
				$result = $this->db->sql_query($sql);

				$forum_names = [];
				while ($row = $this->db->sql_fetchrow($result))
				{
					$forum_names[] = $row['forum_name'];
				}
				$this->db->sql_freeresult($result);

				$this->template->assign_vars([
					'S_FORUM_NAMES'		=> (count($forum_names)) ? true : false,
					'FORUM_NAMES'		=> implode($this->language->lang('COMMA_SEPARATOR'), $forum_names)]
				);
			}

			return;
		}

		// Setting permissions screen
		$s_hidden_fields = build_hidden_fields([
				'user_id'		=> $user_id,
				'group_id'		=> $group_id,
				'forum_id'		=> $forum_id,
				'type'			=> $permission_type,
		]);

		// Do not allow forum_ids being set and no other setting defined (will bog down the server too much)
		if (count($forum_id) && !count($user_id) && !count($group_id))
		{
			trigger_error($this->language->lang('ONLY_FORUM_DEFINED') . adm_back_link($this->u_action), E_USER_WARNING);
		}

		$this->template->assign_vars([
			'S_PERMISSION_DROPDOWN'		=> (count($this->permission_dropdown) > 1) ? $this->build_permission_dropdown($this->permission_dropdown, $permission_type, $permission_scope) : false,
			'L_PERMISSION_TYPE'			=> $this->permissions->get_type_lang($permission_type),

			'U_ACTION'					=> $this->u_action,
			'S_HIDDEN_FIELDS'			=> $s_hidden_fields]
		);

		if (strpos($mode, 'setting_') === 0)
		{
			$this->template->assign_vars([
				'S_SETTING_PERMISSIONS'		=> true]
			);

			$hold_ary = $auth_admin->get_mask('set', (count($user_id)) ? $user_id : false, (count($group_id)) ? $group_id : false, (count($forum_id)) ? $forum_id : false, $permission_type, $permission_scope, ACL_NO);
			$auth_admin->display_mask('set', $permission_type, $hold_ary, ((count($user_id)) ? 'user' : 'group'), (($permission_scope == 'local') ? true : false));
		}
		else
		{
			$this->template->assign_vars([
				'S_VIEWING_PERMISSIONS'		=> true]
			);

			$hold_ary = $auth_admin->get_mask('view', (count($user_id)) ? $user_id : false, (count($group_id)) ? $group_id : false, (count($forum_id)) ? $forum_id : false, $permission_type, $permission_scope, ACL_NEVER);
			$auth_admin->display_mask('view', $permission_type, $hold_ary, ((count($user_id)) ? 'user' : 'group'), (($permission_scope == 'local') ? true : false));
		}
	}

	/**
	 * Build +subforum options
	 */
	function build_subforum_options($forum_list)
	{
		$s_options = '';

		$forum_list = array_merge($forum_list);

		foreach ($forum_list as $key => $row)
		{
			if ($row['disabled'])
			{
				continue;
			}

			$s_options .= '<option value="' . $row['forum_id'] . '"' . (($row['selected']) ? ' selected="selected"' : '') . '>' . $row['padding'] . $row['forum_name'];

			// We check if a branch is there...
			$branch_there = false;

			foreach (array_slice($forum_list, $key + 1) as $temp_row)
			{
				if ($temp_row['left_id'] > $row['left_id'] && $temp_row['left_id'] < $row['right_id'])
				{
					$branch_there = true;
					break;
				}
				continue;
			}

			if ($branch_there)
			{
				$s_options .= ' [' . $this->language->lang('PLUS_SUBFORUMS') . ']';
			}

			$s_options .= '</option>';
		}

		return $s_options;
	}

	/**
	 * Build dropdown field for changing permission types
	 */
	function build_permission_dropdown($options, $default_option, $permission_scope)
	{
		$s_dropdown_options = '';
		foreach ($options as $setting)
		{
			if (!$this->auth->acl_get('a_' . str_replace('_', '', $setting) . 'auth'))
			{
				continue;
			}

			$selected = ($setting == $default_option) ? ' selected="selected"' : '';
			$l_setting = $this->permissions->get_type_lang($setting, $permission_scope);
			$s_dropdown_options .= '<option value="' . $setting . '"' . $selected . '>' . $l_setting . '</option>';
		}

		return $s_dropdown_options;
	}

	/**
	 * Check if selected items exist. Remove not found ids and if empty return error.
	 */
	function check_existence($mode, &$ids)
	{
		switch ($mode)
		{
			case 'user':
				$table = USERS_TABLE;
				$sql_id = 'user_id';
			break;

			case 'group':
				$table = GROUPS_TABLE;
				$sql_id = 'group_id';
			break;

			case 'forum':
				$table = FORUMS_TABLE;
				$sql_id = 'forum_id';
			break;
		}

		if (count($ids))
		{
			$sql = "SELECT $sql_id
				FROM $table
				WHERE " . $this->db->sql_in_set($sql_id, $ids);
			$result = $this->db->sql_query($sql);

			$ids = [];
			while ($row = $this->db->sql_fetchrow($result))
			{
				$ids[] = (int) $row[$sql_id];
			}
			$this->db->sql_freeresult($result);
		}

		if (!count($ids))
		{
			trigger_error($this->language->lang('SELECTED_' . strtoupper($mode) . '_NOT_EXIST') . adm_back_link($this->u_action), E_USER_WARNING);
		}
	}

	/**
	 * Apply permissions
	 */
	function set_permissions($mode, $permission_type, $auth_admin, &$user_id, &$group_id)
	{
		$psubmit = $this->request->variable('psubmit', [0 => [0 => 0]]);

		// User or group to be set?
		$ug_type = (count($user_id)) ? 'user' : 'group';

		// Check the permission setting again
		if (!$this->auth->acl_get('a_' . str_replace('_', '', $permission_type) . 'auth') || !$this->auth->acl_get('a_auth' . $ug_type . 's'))
		{
			send_status_line(403, 'Forbidden');
			trigger_error($this->language->lang('NO_AUTH_OPERATION') . adm_back_link($this->u_action), E_USER_WARNING);
		}

		// We loop through the auth settings defined in our submit
		$ug_id = key($psubmit);
		$forum_id = key($psubmit[$ug_id]);

		$settings = $this->request->variable('setting', [0 => [0 => ['' => 0]]], false, \phpbb\request\request_interface::POST);
		if (empty($settings) || empty($settings[$ug_id]) || empty($settings[$ug_id][$forum_id]))
		{
			trigger_error('WRONG_PERMISSION_SETTING_FORMAT', E_USER_WARNING);
		}

		$auth_settings = $settings[$ug_id][$forum_id];

		// Do we have a role we want to set?
		$roles = $this->request->variable('role', [0 => [0 => 0]], false, \phpbb\request\request_interface::POST);
		$assigned_role = (isset($roles[$ug_id][$forum_id])) ? (int) $roles[$ug_id][$forum_id] : 0;

		// Do the admin want to set these permissions to other items too?
		$inherit = $this->request->variable('inherit', [0 => [0]]);

		$ug_id = [$ug_id];
		$forum_id = [$forum_id];

		if (count($inherit))
		{
			foreach ($inherit as $_ug_id => $forum_id_ary)
			{
				// Inherit users/groups?
				if (!in_array($_ug_id, $ug_id))
				{
					$ug_id[] = $_ug_id;
				}

				// Inherit forums?
				$forum_id = array_merge($forum_id, array_keys($forum_id_ary));
			}
		}

		$forum_id = array_unique($forum_id);

		// If the auth settings differ from the assigned role, then do not set a role...
		if ($assigned_role)
		{
			if (!$this->check_assigned_role($assigned_role, $auth_settings))
			{
				$assigned_role = 0;
			}
		}

		// Update the permission set...
		$auth_admin->acl_set($ug_type, $forum_id, $ug_id, $auth_settings, $assigned_role);

		// Do we need to recache the moderator lists?
		if ($permission_type == 'm_')
		{
			phpbb_cache_moderators($db, $cache, $auth);
		}

		// Remove users who are now moderators or admins from everyones foes list
		if ($permission_type == 'm_' || $permission_type == 'a_')
		{
			phpbb_update_foes($db, $auth, $group_id, $user_id);
		}

		$this->log_action($mode, 'add', $permission_type, $ug_type, $ug_id, $forum_id);

		meta_refresh(5, $this->u_action);
		trigger_error($this->language->lang('AUTH_UPDATED') . adm_back_link($this->u_action));
	}

	/**
	 * Apply all permissions
	 */
	function set_all_permissions($mode, $permission_type, $auth_admin, &$user_id, &$group_id)
	{
		// User or group to be set?
		$ug_type = (count($user_id)) ? 'user' : 'group';

		// Check the permission setting again
		if (!$this->auth->acl_get('a_' . str_replace('_', '', $permission_type) . 'auth') || !$this->auth->acl_get('a_auth' . $ug_type . 's'))
		{
			send_status_line(403, 'Forbidden');
			trigger_error($this->language->lang('NO_AUTH_OPERATION') . adm_back_link($this->u_action), E_USER_WARNING);
		}

		$auth_settings = $this->request->variable('setting', [0 => [0 => ['' => 0]]], false, \phpbb\request\request_interface::POST);
		$auth_roles = $this->request->variable('role', [0 => [0 => 0]], false, \phpbb\request\request_interface::POST);
		$ug_ids = $forum_ids = [];

		// We need to go through the auth settings
		foreach ($auth_settings as $ug_id => $forum_auth_row)
		{
			$ug_id = (int) $ug_id;
			$ug_ids[] = $ug_id;

			foreach ($forum_auth_row as $forum_id => $auth_options)
			{
				$forum_id = (int) $forum_id;
				$forum_ids[] = $forum_id;

				// Check role...
				$assigned_role = (isset($auth_roles[$ug_id][$forum_id])) ? (int) $auth_roles[$ug_id][$forum_id] : 0;

				// If the auth settings differ from the assigned role, then do not set a role...
				if ($assigned_role)
				{
					if (!$this->check_assigned_role($assigned_role, $auth_options))
					{
						$assigned_role = 0;
					}
				}

				// Update the permission set...
				$auth_admin->acl_set($ug_type, $forum_id, $ug_id, $auth_options, $assigned_role, false);
			}
		}

		$auth_admin->acl_clear_prefetch();

		// Do we need to recache the moderator lists?
		if ($permission_type == 'm_')
		{
			phpbb_cache_moderators($db, $cache, $auth);
		}

		// Remove users who are now moderators or admins from everyones foes list
		if ($permission_type == 'm_' || $permission_type == 'a_')
		{
			phpbb_update_foes($db, $auth, $group_id, $user_id);
		}

		$this->log_action($mode, 'add', $permission_type, $ug_type, $ug_ids, $forum_ids);

		if ($mode == 'setting_forum_local' || $mode == 'setting_mod_local')
		{
			meta_refresh(5, $this->u_action . '&amp;forum_id[]=' . implode('&amp;forum_id[]=', $forum_ids));
			trigger_error($this->language->lang('AUTH_UPDATED') . adm_back_link($this->u_action . '&amp;forum_id[]=' . implode('&amp;forum_id[]=', $forum_ids)));
		}
		else
		{
			meta_refresh(5, $this->u_action);
			trigger_error($this->language->lang('AUTH_UPDATED') . adm_back_link($this->u_action));
		}
	}

	/**
	 * Compare auth settings with auth settings from role
	 * returns false if they differ, true if they are equal
	 */
	function check_assigned_role($role_id, &$auth_settings)
	{
		$sql = 'SELECT o.auth_option, r.auth_setting
			FROM ' . ACL_OPTIONS_TABLE . ' o, ' . ACL_ROLES_DATA_TABLE . ' r
			WHERE o.auth_option_id = r.auth_option_id
				AND r.role_id = ' . $role_id;
		$result = $this->db->sql_query($sql);

		$test_auth_settings = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$test_auth_settings[$row['auth_option']] = $row['auth_setting'];
		}
		$this->db->sql_freeresult($result);

		// We need to add any ACL_NO setting from auth_settings to compare correctly
		foreach ($auth_settings as $option => $setting)
		{
			if ($setting == ACL_NO)
			{
				$test_auth_settings[$option] = $setting;
			}
		}

		if (count(array_diff_assoc($auth_settings, $test_auth_settings)))
		{
			return false;
		}

		return true;
	}

	/**
	 * Remove permissions
	 */
	function remove_permissions($mode, $permission_type, $auth_admin, &$user_id, &$group_id, &$forum_id)
	{
		// User or group to be set?
		$ug_type = (count($user_id)) ? 'user' : 'group';

		// Check the permission setting again
		if (!$this->auth->acl_get('a_' . str_replace('_', '', $permission_type) . 'auth') || !$this->auth->acl_get('a_auth' . $ug_type . 's'))
		{
			send_status_line(403, 'Forbidden');
			trigger_error($this->language->lang('NO_AUTH_OPERATION') . adm_back_link($this->u_action), E_USER_WARNING);
		}

		$auth_admin->acl_delete($ug_type, (($ug_type == 'user') ? $user_id : $group_id), (count($forum_id) ? $forum_id : false), $permission_type);

		// Do we need to recache the moderator lists?
		if ($permission_type == 'm_')
		{
			phpbb_cache_moderators($db, $cache, $auth);
		}

		$this->log_action($mode, 'del', $permission_type, $ug_type, (($ug_type == 'user') ? $user_id : $group_id), (count($forum_id) ? $forum_id : [0 => 0]));

		if ($mode == 'setting_forum_local' || $mode == 'setting_mod_local')
		{
			meta_refresh(5, $this->u_action . '&amp;forum_id[]=' . implode('&amp;forum_id[]=', $forum_id));
			trigger_error($this->language->lang('AUTH_UPDATED') . adm_back_link($this->u_action . '&amp;forum_id[]=' . implode('&amp;forum_id[]=', $forum_id)));
		}
		else
		{
			meta_refresh(5, $this->u_action);
			trigger_error($this->language->lang('AUTH_UPDATED') . adm_back_link($this->u_action));
		}
	}

	/**
	 * Log permission changes
	 */
	function log_action($mode, $action, $permission_type, $ug_type, $ug_id, $forum_id)
	{
		if (!is_array($ug_id))
		{
			$ug_id = [$ug_id];
		}

		if (!is_array($forum_id))
		{
			$forum_id = [$forum_id];
		}

		// Logging ... first grab user or groupnames ...
		$sql = ($ug_type == 'group') ? 'SELECT group_name as name, group_type FROM ' . GROUPS_TABLE . ' WHERE ' : 'SELECT username as name FROM ' . USERS_TABLE . ' WHERE ';
		$sql .= $this->db->sql_in_set(($ug_type == 'group') ? 'group_id' : 'user_id', array_map('intval', $ug_id));
		$result = $this->db->sql_query($sql);

		/** @var \phpbb\group\helper $group_helper */
		$group_helper = $phpbb_container->get('group_helper');

		$l_ug_list = '';
		while ($row = $this->db->sql_fetchrow($result))
		{
			$group_name = $this->group_helper->get_name($row['name']);
			$l_ug_list .= (($l_ug_list != '') ? ', ' : '') . ((isset($row['group_type']) && $row['group_type'] == GROUP_SPECIAL) ? '<span class="sep">' . $group_name . '</span>' : $group_name);
		}
		$this->db->sql_freeresult($result);

		$mode = str_replace('setting_', '', $mode);

		if ($forum_id[0] == 0)
		{
			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_ACL_' . strtoupper($action) . '_' . strtoupper($mode) . '_' . strtoupper($permission_type), false, [$l_ug_list]);
		}
		else
		{
			// Grab the forum details if non-zero forum_id
			$sql = 'SELECT forum_name
				FROM ' . FORUMS_TABLE . '
				WHERE ' . $this->db->sql_in_set('forum_id', $forum_id);
			$result = $this->db->sql_query($sql);

			$l_forum_list = '';
			while ($row = $this->db->sql_fetchrow($result))
			{
				$l_forum_list .= (($l_forum_list != '') ? ', ' : '') . $row['forum_name'];
			}
			$this->db->sql_freeresult($result);

			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_ACL_' . strtoupper($action) . '_' . strtoupper($mode) . '_' . strtoupper($permission_type), false, [$l_forum_list, $l_ug_list]);
		}
	}

	/**
	 * Display a complete trace tree for the selected permission to determine where settings are set/unset
	 */
	function permission_trace($user_id, $forum_id, $permission)
	{
		if ($user_id != $this->user->data['user_id'])
		{
			$userdata = $this->auth->obtain_user_data($user_id);
		}
		else
		{
			$userdata = $this->user->data;
		}

		if (!$userdata)
		{
			trigger_error('NO_USERS', E_USER_ERROR);
		}

		/** @var \phpbb\group\helper $group_helper */
		$group_helper = $phpbb_container->get('group_helper');

		$forum_name = false;

		if ($forum_id)
		{
			$sql = 'SELECT forum_name
				FROM ' . FORUMS_TABLE . "
				WHERE forum_id = $forum_id";
			$result = $this->db->sql_query($sql, 3600);
			$forum_name = $this->db->sql_fetchfield('forum_name');
			$this->db->sql_freeresult($result);
		}

		$back = $this->request->variable('back', 0);

		$this->template->assign_vars([
			'PERMISSION'			=> $this->permissions->get_permission_lang($permission),
			'PERMISSION_USERNAME'	=> $userdata['username'],
			'FORUM_NAME'			=> $forum_name,

			'S_GLOBAL_TRACE'		=> ($forum_id) ? false : true,

			'U_BACK'				=> ($back) ? build_url(['f', 'back']) . "&amp;f=$back" : '']
		);

		$this->template->assign_block_vars('trace', [
			'WHO'			=> $this->language->lang('DEFAULT'),
			'INFORMATION'	=> $this->language->lang('TRACE_DEFAULT'),

			'S_SETTING_NO'		=> true,
			'S_TOTAL_NO'		=> true]
		);

		$sql = 'SELECT DISTINCT g.group_name, g.group_id, g.group_type
			FROM ' . GROUPS_TABLE . ' g
				LEFT JOIN ' . USER_GROUP_TABLE . ' ug ON (ug.group_id = g.group_id)
			WHERE ug.user_id = ' . $user_id . '
				AND ug.user_pending = 0
				AND NOT (ug.group_leader = 1 AND g.group_skip_auth = 1)
			ORDER BY g.group_type DESC, g.group_id DESC';
		$result = $this->db->sql_query($sql);

		$groups = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$groups[$row['group_id']] = [
				'auth_setting'		=> ACL_NO,
				'group_name'		=> $this->group_helper->get_name($row['group_name']),
			];
		}
		$this->db->sql_freeresult($result);

		$total = ACL_NO;
		$add_key = (($forum_id) ? '_LOCAL' : '');

		if (count($groups))
		{
			// Get group auth settings
			$hold_ary = $this->auth->acl_group_raw_data(array_keys($groups), $permission, $forum_id);

			foreach ($hold_ary as $group_id => $forum_ary)
			{
				$groups[$group_id]['auth_setting'] = $hold_ary[$group_id][$forum_id][$permission];
			}
			unset($hold_ary);

			foreach ($groups as $id => $row)
			{
				switch ($row['auth_setting'])
				{
					case ACL_NO:
						$information = $this->language->lang('TRACE_GROUP_NO' . $add_key);
					break;

					case ACL_YES:
						$information = ($total == ACL_YES) ? $this->language->lang('TRACE_GROUP_YES_TOTAL_YES' . $add_key) : (($total == ACL_NEVER) ? $this->language->lang('TRACE_GROUP_YES_TOTAL_NEVER' . $add_key) : $this->language->lang('TRACE_GROUP_YES_TOTAL_NO' . $add_key));
						$total = ($total == ACL_NO) ? ACL_YES : $total;
					break;

					case ACL_NEVER:
						$information = ($total == ACL_YES) ? $this->language->lang('TRACE_GROUP_NEVER_TOTAL_YES' . $add_key) : (($total == ACL_NEVER) ? $this->language->lang('TRACE_GROUP_NEVER_TOTAL_NEVER' . $add_key) : $this->language->lang('TRACE_GROUP_NEVER_TOTAL_NO' . $add_key));
						$total = ACL_NEVER;
					break;
				}

				$this->template->assign_block_vars('trace', [
					'WHO'			=> $row['group_name'],
					'INFORMATION'	=> $information,

					'S_SETTING_NO'		=> ($row['auth_setting'] == ACL_NO) ? true : false,
					'S_SETTING_YES'		=> ($row['auth_setting'] == ACL_YES) ? true : false,
					'S_SETTING_NEVER'	=> ($row['auth_setting'] == ACL_NEVER) ? true : false,
					'S_TOTAL_NO'		=> ($total == ACL_NO) ? true : false,
					'S_TOTAL_YES'		=> ($total == ACL_YES) ? true : false,
					'S_TOTAL_NEVER'		=> ($total == ACL_NEVER) ? true : false]
				);
			}
		}

		// Get user specific permission... globally or for this forum
		$hold_ary = $this->auth->acl_user_raw_data($user_id, $permission, $forum_id);
		$auth_setting = (!count($hold_ary)) ? ACL_NO : $hold_ary[$user_id][$forum_id][$permission];

		switch ($auth_setting)
		{
			case ACL_NO:
				$information = ($total == ACL_NO) ? $this->language->lang('TRACE_USER_NO_TOTAL_NO' . $add_key) : $this->language->lang('TRACE_USER_KEPT' . $add_key);
				$total = ($total == ACL_NO) ? ACL_NEVER : $total;
			break;

			case ACL_YES:
				$information = ($total == ACL_YES) ? $this->language->lang('TRACE_USER_YES_TOTAL_YES' . $add_key) : (($total == ACL_NEVER) ? $this->language->lang('TRACE_USER_YES_TOTAL_NEVER' . $add_key) : $this->language->lang('TRACE_USER_YES_TOTAL_NO' . $add_key));
				$total = ($total == ACL_NO) ? ACL_YES : $total;
			break;

			case ACL_NEVER:
				$information = ($total == ACL_YES) ? $this->language->lang('TRACE_USER_NEVER_TOTAL_YES' . $add_key) : (($total == ACL_NEVER) ? $this->language->lang('TRACE_USER_NEVER_TOTAL_NEVER' . $add_key) : $this->language->lang('TRACE_USER_NEVER_TOTAL_NO' . $add_key));
				$total = ACL_NEVER;
			break;
		}

		$this->template->assign_block_vars('trace', [
			'WHO'			=> $userdata['username'],
			'INFORMATION'	=> $information,

			'S_SETTING_NO'		=> ($auth_setting == ACL_NO) ? true : false,
			'S_SETTING_YES'		=> ($auth_setting == ACL_YES) ? true : false,
			'S_SETTING_NEVER'	=> ($auth_setting == ACL_NEVER) ? true : false,
			'S_TOTAL_NO'		=> false,
			'S_TOTAL_YES'		=> ($total == ACL_YES) ? true : false,
			'S_TOTAL_NEVER'		=> ($total == ACL_NEVER) ? true : false]
		);

		if ($forum_id != 0 && isset($this->auth->acl_options['global'][$permission]))
		{
			if ($user_id != $this->user->data['user_id'])
			{
				$auth2 = new \phpbb\auth\auth();
				$auth2->acl($userdata);
				$auth_setting = $auth2->acl_get($permission);
			}
			else
			{
				$auth_setting = $this->auth->acl_get($permission);
			}

			if ($auth_setting)
			{
				$information = ($total == ACL_YES) ? $this->language->lang('TRACE_USER_GLOBAL_YES_TOTAL_YES') : $this->language->lang('TRACE_USER_GLOBAL_YES_TOTAL_NEVER');
				$total = ACL_YES;
			}
			else
			{
				$information = $this->language->lang('TRACE_USER_GLOBAL_NEVER_TOTAL_KEPT');
			}

			// If there is no auth information we do not need to worry the user by showing non-relevant data.
			if ($auth_setting)
			{
				$this->template->assign_block_vars('trace', [
					'WHO'			=> sprintf($this->language->lang('TRACE_GLOBAL_SETTING'), $userdata['username']),
					'INFORMATION'	=> sprintf($information, '<a href="' . $this->u_action . "&amp;u=$user_id&amp;f=0&amp;auth=$permission&amp;back=$forum_id\">", '</a>'),

					'S_SETTING_NO'		=> false,
					'S_SETTING_YES'		=> $auth_setting,
					'S_SETTING_NEVER'	=> !$auth_setting,
					'S_TOTAL_NO'		=> false,
					'S_TOTAL_YES'		=> ($total == ACL_YES) ? true : false,
					'S_TOTAL_NEVER'		=> ($total == ACL_NEVER) ? true : false]
				);
			}
		}

		// Take founder status into account, overwriting the default values
		if ($userdata['user_type'] == USER_FOUNDER && strpos($permission, 'a_') === 0)
		{
			$this->template->assign_block_vars('trace', [
				'WHO'			=> $userdata['username'],
				'INFORMATION'	=> $this->language->lang('TRACE_USER_FOUNDER'),

				'S_SETTING_NO'		=> ($auth_setting == ACL_NO) ? true : false,
				'S_SETTING_YES'		=> ($auth_setting == ACL_YES) ? true : false,
				'S_SETTING_NEVER'	=> ($auth_setting == ACL_NEVER) ? true : false,
				'S_TOTAL_NO'		=> false,
				'S_TOTAL_YES'		=> true,
				'S_TOTAL_NEVER'		=> false]
			);

			$total = ACL_YES;
		}

		// Total value...
		$this->template->assign_vars([
			'S_RESULT_NO'		=> ($total == ACL_NO) ? true : false,
			'S_RESULT_YES'		=> ($total == ACL_YES) ? true : false,
			'S_RESULT_NEVER'	=> ($total == ACL_NEVER) ? true : false,
		]);
	}

	/**
	 * Handles copying permissions from one forum to others
	 */
	function copy_forum_permissions()
	{
		$this->language->add_lang('acp/forums');

		$submit = $this->request->is_set_post('submit') ? true : false;

		if ($submit)
		{
			$src = $this->request->variable('src_forum_id', 0);
			$dest = $this->request->variable('dest_forum_ids', [0]);

			if (confirm_box(true))
			{
				if (copy_forum_permissions($src, $dest))
				{
					phpbb_cache_moderators($db, $cache, $auth);

					$this->auth->acl_clear_prefetch();
					$this->cache->destroy('sql', FORUMS_TABLE);

					trigger_error($this->language->lang('AUTH_UPDATED') . adm_back_link($this->u_action));
				}
				else
				{
					trigger_error($this->language->lang('SELECTED_FORUM_NOT_EXIST') . adm_back_link($this->u_action), E_USER_WARNING);
				}
			}
			else
			{
				$s_hidden_fields = [
					'submit'			=> $submit,
					'src_forum_id'		=> $src,
					'dest_forum_ids'	=> $dest,
				];

				$s_hidden_fields = build_hidden_fields($s_hidden_fields);

				confirm_box(false, $this->language->lang('COPY_PERMISSIONS_CONFIRM'), $s_hidden_fields);
			}
		}

		$this->template->assign_vars([
			'S_FORUM_OPTIONS' => make_forum_select(false, false, false, false, false),
		]);
	}

	/**
	 * Get already assigned users/groups
	 */
	function retrieve_defined_user_groups($permission_scope, $forum_id, $permission_type)
	{
		/** @var \phpbb\group\helper $group_helper */
		$group_helper = $phpbb_container->get('group_helper');

		$sql_forum_id = ($permission_scope == 'global') ? 'AND a.forum_id = 0' : ((count($forum_id)) ? 'AND ' . $this->db->sql_in_set('a.forum_id', $forum_id) : 'AND a.forum_id <> 0');

		// Permission options are only able to be a permission set... therefore we will pre-fetch the possible options and also the possible roles
		$option_ids = $role_ids = [];

		$sql = 'SELECT auth_option_id
			FROM ' . ACL_OPTIONS_TABLE . '
			WHERE auth_option ' . $this->db->sql_like_expression($permission_type . $this->db->get_any_char());
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$option_ids[] = (int) $row['auth_option_id'];
		}
		$this->db->sql_freeresult($result);

		if (count($option_ids))
		{
			$sql = 'SELECT DISTINCT role_id
				FROM ' . ACL_ROLES_DATA_TABLE . '
				WHERE ' . $this->db->sql_in_set('auth_option_id', $option_ids);
			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$role_ids[] = (int) $row['role_id'];
			}
			$this->db->sql_freeresult($result);
		}

		if (count($option_ids) && count($role_ids))
		{
			$sql_where = 'AND (' . $this->db->sql_in_set('a.auth_option_id', $option_ids) . ' OR ' . $this->db->sql_in_set('a.auth_role_id', $role_ids) . ')';
		}
		else if (count($role_ids))
		{
			$sql_where = 'AND ' . $this->db->sql_in_set('a.auth_role_id', $role_ids);
		}
		else if (count($option_ids))
		{
			$sql_where = 'AND ' . $this->db->sql_in_set('a.auth_option_id', $option_ids);
		}

		// Not ideal, due to the filesort, non-use of indexes, etc.
		$sql = 'SELECT DISTINCT u.user_id, u.username, u.username_clean, u.user_regdate
			FROM ' . USERS_TABLE . ' u, ' . ACL_USERS_TABLE . " a
			WHERE u.user_id = a.user_id
				$sql_forum_id
				$sql_where
			ORDER BY u.username_clean, u.user_regdate ASC";
		$result = $this->db->sql_query($sql);

		$s_defined_user_options = '';
		$defined_user_ids = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$s_defined_user_options .= '<option value="' . $row['user_id'] . '">' . $row['username'] . '</option>';
			$defined_user_ids[] = $row['user_id'];
		}
		$this->db->sql_freeresult($result);

		$sql = 'SELECT DISTINCT g.group_type, g.group_name, g.group_id
			FROM ' . GROUPS_TABLE . ' g, ' . ACL_GROUPS_TABLE . " a
			WHERE g.group_id = a.group_id
				$sql_forum_id
				$sql_where
			ORDER BY g.group_type DESC, g.group_name ASC";
		$result = $this->db->sql_query($sql);

		$s_defined_group_options = '';
		$defined_group_ids = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$s_defined_group_options .= '<option' . (($row['group_type'] == GROUP_SPECIAL) ? ' class="sep"' : '') . ' value="' . $row['group_id'] . '">' . $this->group_helper->get_name($row['group_name']) . '</option>';
			$defined_group_ids[] = $row['group_id'];
		}
		$this->db->sql_freeresult($result);

		return [
			'group_ids'			=> $defined_group_ids,
			'group_ids_options'	=> $s_defined_group_options,
			'user_ids'			=> $defined_user_ids,
			'user_ids_options'	=> $s_defined_user_options,
		];
	}
}
