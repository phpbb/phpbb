<?php
/** 
*
* @package acp
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* @package acp
*/
class acp_permission_roles
{
	var $u_action;
	var $pre_selection_array;

	function main($id, $mode)
	{
		global $db, $user, $auth, $template, $cache;
		global $config, $SID, $phpbb_root_path, $phpbb_admin_path, $phpEx;

		include_once($phpbb_root_path . 'includes/functions_user.' . $phpEx);
		include_once($phpbb_root_path . 'includes/acp/auth.' . $phpEx);

		$auth_admin = new auth_admin();

		$user->add_lang('acp/permissions');
		$user->add_lang('acp/permissions_phpbb');

		$this->tpl_name = 'acp_permission_roles';

		$submit = (isset($_POST['submit'])) ? true : false;
		$role_id = request_var('role_id', 0);
		$action = request_var('action', '');
		$action = (isset($_POST['add'])) ? 'add' : $action;

		// Define pre-selection array
		$this->pre_selection_array = array(
			1		=> array('lang' => 'PRE_ONLY_SPECIAL_GUEST', 'type' => GROUP_SPECIAL, 'name' => array('BOTS', 'GUESTS', 'INACTIVE', 'INACTIVE_COPPA'), 'negate' => false),
			2		=> array('lang' => 'PRE_ONLY_SPECIAL_REGISTERED', 'type' => GROUP_SPECIAL, 'name' => array('ADMINISTRATORS', 'SUPER_MODERATORS', 'REGISTERED', 'REGISTERED_COPPA'), 'negate' => false),
			3		=> array('lang' => 'PRE_NOT_SPECIAL_GUEST', 'type' => GROUP_SPECIAL, 'name' => array('BOTS', 'GUESTS', 'INACTIVE', 'INACTIVE_COPPA'), 'negate' => true),
			4		=> array('lang' => 'PRE_NOT_SPECIAL_REGISTERED', 'type' => GROUP_SPECIAL, 'name' => array('ADMINISTRATORS', 'SUPER_MODERATORS', 'REGISTERED', 'REGISTERED_COPPA'), 'negate' => true),
			5		=> array('lang' => 'PRE_ALL_SPECIAL', 'type' => GROUP_SPECIAL, 'negate' => false),
			6		=> array('lang' => 'PRE_NOT_SPECIAL', 'type' => GROUP_SPECIAL, 'negate' => true),
			7		=> array('lang' => 'PRE_ALL_FREE', 'type' => GROUP_FREE, 'negate' => false),
			8		=> array('lang' => 'PRE_NOT_FREE', 'type' => GROUP_FREE, 'negate' => true),
			9		=> array('lang' => 'PRE_ALL_CLOSED', 'type' => GROUP_CLOSED, 'negate' => false),
			10		=> array('lang' => 'PRE_NOT_CLOSED', 'type' => GROUP_CLOSED, 'negate' => true),
			11		=> array('lang' => 'PRE_ALL_HIDDEN', 'type' => GROUP_HIDDEN, 'negate' => false),
			12		=> array('lang' => 'PRE_NOT_HIDDEN', 'type' => GROUP_HIDDEN, 'negate' => true),
			13		=> array('lang' => 'PRE_ALL_OPEN', 'type' => GROUP_OPEN, 'negate' => false),
			14		=> array('lang' => 'PRE_NOT_OPEN', 'type' => GROUP_OPEN, 'negate' => true),
		);

		switch ($mode)
		{
			case 'admin_roles':
				$permission_type = 'a_';
				$this->page_title = 'ACP_ADMIN_ROLES';
			break;

			case 'user_roles':
				$permission_type = 'u_';
				$this->page_title = 'ACP_USER_ROLES';
			break;

			case 'mod_roles':
				$permission_type = 'm_';
				$this->page_title = 'ACP_MOD_ROLES';
			break;

			case 'forum_roles':
				$permission_type = 'f_';
				$this->page_title = 'ACP_FORUM_ROLES';
			break;

			default:
				trigger_error('INVALID_MODE');
		}

		$template->assign_vars(array(
			'L_TITLE'		=> $user->lang[$this->page_title],
			'L_EXPLAIN'		=> $user->lang[$this->page_title . '_EXPLAIN'])
		);

		// Take action... admin submitted something
		if ($submit || $action == 'remove')
		{
			switch ($action)
			{
				case 'remove':

					if (!$role_id)
					{
						trigger_error($user->lang['NO_ROLE_SELECTED'] . adm_back_link($this->u_action));
					}

					$sql = 'SELECT *
						FROM ' . ACL_ROLES_TABLE . '
						WHERE role_id = ' . $role_id;
					$result = $db->sql_query($sql);
					$role_row = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);

					if (!$role_row)
					{
						trigger_error($user->lang['NO_ROLE_SELECTED'] . adm_back_link($this->u_action));
					}

					if (confirm_box(true))
					{
						$this->remove_role($role_id, $permission_type);

						add_log('admin', 'LOG_' . strtoupper($permission_type) . 'ROLE_REMOVED', $role_row['role_name']);
						trigger_error($user->lang['ROLE_DELETED'] . adm_back_link($this->u_action));
					}
					else
					{
						confirm_box(false, 'DELETE_ROLE', build_hidden_fields(array(
							'i'			=> $id,
							'mode'		=> $mode,
							'role_id'	=> $role_id,
							'action'	=> $action,
						)));
					}

				break;

				case 'edit':
					if (!$role_id)
					{
						trigger_error($user->lang['NO_ROLE_SELECTED'] . adm_back_link($this->u_action));
					}

					// Get role we edit
					$sql = 'SELECT *
						FROM ' . ACL_ROLES_TABLE . '
						WHERE role_id = ' . $role_id;
					$result = $db->sql_query($sql);
					$role_row = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);

					if (!$role_row)
					{
						trigger_error($user->lang['NO_ROLE_SELECTED'] . adm_back_link($this->u_action));
					}

				case 'add':

					$role_name = request_var('role_name', '');
					$role_group_ids = request_var('role_group_ids', array(0));
					$pre_select = request_var('pre_select', 'custom');
					$auth_settings = request_var('setting', array('' => 0));

					if (!$role_name)
					{
						trigger_error($user->lang['NO_ROLE_NAME_SPECIFIED'] . adm_back_link($this->u_action));
					}

					// Adjust group array if we have a pre-selection
					if ($pre_select != 'custom')
					{
						$pre_select = (int) $pre_select;

						if (!$pre_select || !isset($this->pre_selection_array[$pre_select]))
						{
							$role_group_ids = array(0);
						}
						else
						{
							$sql = 'SELECT group_id, group_name, group_type
								FROM ' . GROUPS_TABLE . '
								ORDER BY group_type DESC, group_name ASC';
							$result = $db->sql_query($sql);

							$groups = array();
							while ($row = $db->sql_fetchrow($result))
							{
								$groups[$row['group_type']][$row['group_id']] = $row['group_name'];
							}
							$db->sql_freeresult($result);

							// Build role_group_ids
							$role_group_ids = array();

							$row = $this->pre_selection_array[$pre_select];

							if (!$row['negate'] && !isset($row['name']))
							{
								if (isset($groups[$row['type']]))
								{
									foreach ($groups[$row['type']] as $group_id => $group_name)
									{
										$role_group_ids[] = $group_id;
									}
								}
							}
							else if ($row['negate'] && !isset($row['name']))
							{
								$group_types = array(GROUP_OPEN, GROUP_CLOSED, GROUP_HIDDEN, GROUP_SPECIAL, GROUP_FREE);
								unset($group_types[array_search($row['type'], $group_types)]);

								foreach ($group_types as $type)
								{
									if (!isset($groups[$type]))
									{
										continue;
									}

									foreach ($groups[$type] as $group_id => $group_name)
									{
										$role_group_ids[] = $group_id;
									}
								}
							}
							else if (!$row['negate'] && isset($row['name']))
							{
								foreach ($groups[$row['type']] as $group_id => $group_name)
								{
									if (in_array($group_name, $row['name']))
									{
										$role_group_ids[] = $group_id;
									}
								}
							}
							else if ($row['negate'] && isset($row['name']))
							{
								$group_types = array(GROUP_OPEN, GROUP_CLOSED, GROUP_HIDDEN, GROUP_SPECIAL, GROUP_FREE);

								foreach ($group_types as $type)
								{
									if (!isset($groups[$type]))
									{
										continue;
									}

									foreach ($groups[$type] as $group_id => $group_name)
									{
										if ($type != $row['type'])
										{
											$role_group_ids[] = $group_id;
										}
										else if (!in_array($group_name, $row['name']))
										{
											$role_group_ids[] = $group_id;
										}
									}
								}
							}
						}
					}

					// if we add/edit a role we check the name to be unique among the settings...
					$sql = 'SELECT role_id
						FROM ' . ACL_ROLES_TABLE . "
						WHERE role_type = '" . $db->sql_escape($permission_type) . "'
							AND LOWER(role_name) = '" . $db->sql_escape(strtolower($role_name)) . "'";
					$result = $db->sql_query($sql);
					$row = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);

					// Make sure we only print out the error if we add the role or change it's name
					if ($row && ($mode == 'add' || ($mode == 'edit' && strtolower($role_row['role_name']) != strtolower($role_name))))
					{
						trigger_error(sprintf($user->lang['ROLE_NAME_ALREADY_EXIST'], $role_name) . adm_back_link($this->u_action));
					}

					// If role_group_ids include "every user/group" we do not need to set it...
					if (in_array(0, $role_group_ids))
					{
						$role_group_ids = array(0);
					}
					
					$sql_ary = array(
						'role_name'			=> (string) $role_name,
						'role_type'			=> (string) $permission_type,
						'role_group_ids'	=> (string) implode(':', $role_group_ids),
					);

					if ($action == 'edit')
					{
						$sql = 'UPDATE ' . ACL_ROLES_TABLE . ' 
							SET ' . $db->sql_build_array('UPDATE', $sql_ary) . ' 
							WHERE role_id = ' . $role_id;
						$db->sql_query($sql);
					}
					else
					{
						$sql = 'INSERT INTO ' . ACL_ROLES_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
						$db->sql_query($sql);

						$role_id = $db->sql_nextid();
					}

					// Now add the auth settings
					$auth_admin->acl_set_role($role_id, $auth_settings);

					add_log('admin', 'LOG_' . strtoupper($permission_type) . 'ROLE_' . strtoupper($action), $role_name);

					trigger_error($user->lang['ROLE_' . strtoupper($action) . '_SUCCESS'] . adm_back_link($this->u_action));

				break;
			}
		}

		// Display screens
		switch ($action)
		{
			case 'add':

				$options_from = request_var('options_from', 0);

				$role_row = array(
					'role_name'			=> request_var('role_name', ''),
					'role_type'			=> $permission_type,
					'role_group_ids'	=> implode(':', request_var('role_group_ids', array(0))),
				);

				if ($options_from)
				{
					$sql = 'SELECT p.auth_option_id, p.auth_setting, o.auth_option
						FROM ' . ACL_ROLES_DATA_TABLE . ' p, ' . ACL_OPTIONS_TABLE . ' o
						WHERE o.auth_option_id = p.auth_option_id
							AND p.role_id = ' . $options_from . '
						ORDER BY p.auth_option_id';
					$result = $db->sql_query($sql);

					$auth_options = array();
					while ($row = $db->sql_fetchrow($result))
					{
						$auth_options[$row['auth_option']] = $row['auth_setting'];
					}
					$db->sql_freeresult($result);
				}
				else
				{
					$sql = 'SELECT auth_option_id, auth_option
						FROM ' . ACL_OPTIONS_TABLE . "
						WHERE auth_option LIKE '{$permission_type}%'
							AND auth_option <> '{$permission_type}'
						ORDER BY auth_option_id";
					$result = $db->sql_query($sql);

					$auth_options = array();
					while ($row = $db->sql_fetchrow($result))
					{
						$auth_options[$row['auth_option']] = ACL_UNSET;
					}
					$db->sql_freeresult($result);
				}

			case 'edit':

				if ($action == 'edit')
				{
					if (!$role_id)
					{
						trigger_error($user->lang['NO_ROLE_SELECTED'] . adm_back_link($this->u_action));
					}
					
					$sql = 'SELECT *
						FROM ' . ACL_ROLES_TABLE . '
						WHERE role_id = ' . $role_id;
					$result = $db->sql_query($sql);
					$role_row = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);

					$sql = 'SELECT p.auth_option_id, p.auth_setting, o.auth_option
						FROM ' . ACL_ROLES_DATA_TABLE . ' p, ' . ACL_OPTIONS_TABLE . ' o
						WHERE o.auth_option_id = p.auth_option_id
							AND p.role_id = ' . $role_id . '
						ORDER BY p.auth_option_id';
					$result = $db->sql_query($sql);

					$auth_options = array();
					while ($row = $db->sql_fetchrow($result))
					{
						$auth_options[$row['auth_option']] = $row['auth_setting'];
					}
					$db->sql_freeresult($result);
				}

				if (!$role_row)
				{
					trigger_error($user->lang['NO_PRESET_SELECTED'] . adm_back_link($this->u_action));
				}

				// Build group options array (with pre-selection)
				$s_preselect_options = $s_group_options = array();
				$this->build_group_options($role_row['role_group_ids'], $s_preselect_options, $s_group_options);

				$template->assign_vars(array(
					'S_EDIT'				=> true,
					'S_PRESELECT_OPTIONS'	=> $s_preselect_options,
					'S_GROUP_OPTIONS'		=> $s_group_options,

					'U_ACTION'			=> $this->u_action . "&amp;action={$action}&amp;role_id={$role_id}",
					'U_BACK'			=> $this->u_action,
					
					'ROLE_NAME'			=> $role_row['role_name'],
					'L_ACL_TYPE'		=> $user->lang['ACL_TYPE_' . strtoupper($permission_type)],
					)
				);

				// We need to fill the auth options array with ACL_UNSET options ;)
				$sql = 'SELECT auth_option_id, auth_option
					FROM ' . ACL_OPTIONS_TABLE . "
					WHERE auth_option LIKE '{$permission_type}%'
						AND auth_option <> '{$permission_type}'
					ORDER BY auth_option_id";
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					if (!isset($auth_options[$row['auth_option']]))
					{
						$auth_options[$row['auth_option']] = ACL_UNSET;
					}
				}
				$db->sql_freeresult($result);

				// Unset global permission option
				unset($auth_options[$permission_type]);

				// Display auth options
				$this->display_auth_options($auth_options);

				// Get users/groups/forums using this preset...
				if ($action == 'edit')
				{
					$hold_ary = $auth_admin->get_role_mask($role_id);

					if (sizeof($hold_ary))
					{
						$template->assign_var(array(
							'S_DISPLAY_ROLE_MASK'	=> true,
							'L_ROLE_ASSIGNED_TO'	=> sprintf($user->lang['ROLE_ASSIGNED_TO'], $role_row['role_name']))
						);

						$auth_admin->display_role_mask($hold_ary);
					}
				}

				return;
			break;
		}

		// Select existing roles
		$sql = 'SELECT *
			FROM ' . ACL_ROLES_TABLE . "
			WHERE role_type = '" . $db->sql_escape($permission_type) . "'
			ORDER BY role_name ASC";
		$result = $db->sql_query($sql);

		$roles = $groups = $group_ids = $group_info = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$roles[] = $row;
			if ($row['role_group_ids'])
			{
				$groups[$row['role_id']] = explode(':', $row['role_group_ids']);
				$group_ids = array_merge($group_ids, $groups[$row['role_id']]);
			}
		}
		$db->sql_freeresult($result);

		if (sizeof($group_ids))
		{
			$sql = 'SELECT group_id, group_type, group_name
				FROM ' . GROUPS_TABLE . '
				WHERE group_id IN (' . implode(', ', array_map('intval', $group_ids)) . ')';
			$result = $db->sql_query($sql);
			
			while ($row = $db->sql_fetchrow($result))
			{
				$group_info[$row['group_id']] = array(
					'group_name'	=> ($row['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $row['group_name']] : $row['group_name'],
					'group_special'	=> ($row['group_type'] == GROUP_SPECIAL) ? true : false,
				);
			}
			$db->sql_freeresult($result);
		}
		
		// Display assigned items?
		$display_item = request_var('display_item', 0);

		$s_role_options = '';
		foreach ($roles as $row)
		{
			$template->assign_block_vars('roles', array(
				'NAME'				=> $row['role_name'],

				'S_GROUP'			=> ($row['role_group_ids']) ? true : false,
				
				'U_EDIT'			=> $this->u_action . '&amp;action=edit&amp;role_id=' . $row['role_id'],
				'U_REMOVE'			=> $this->u_action . '&amp;action=remove&amp;role_id=' . $row['role_id'],
				'U_DISPLAY_ITEMS'	=> ($row['role_id'] == $display_item) ? '' : $this->u_action . '&amp;display_item=' . $row['role_id'] . '#assigned_to')
			);

			if (isset($groups[$row['role_id']]) && sizeof($groups[$row['role_id']]))
			{
				foreach ($groups[$row['role_id']] as $group_id)
				{
					$template->assign_block_vars('roles.groups', array(
						'S_SPECIAL_GROUP'	=> $group_info[$group_id]['group_special'],
						'GROUP_NAME'		=> $group_info[$group_id]['group_name'],
						'U_GROUP'			=> $phpbb_root_path . "memberlist.$phpEx$SID&amp;mode=group&amp;g=$group_id")
					);
				}
			}
			
			$s_role_options .= '<option value="' . $row['role_id'] . '">' . $row['role_name'] . '</option>';

			if ($display_item == $row['role_id'])
			{
				$template->assign_vars(array(
					'L_ROLE_ASSIGNED_TO'	=> sprintf($user->lang['ROLE_ASSIGNED_TO'], $row['role_name']))
				);
			}
		}

		$template->assign_vars(array(
			'S_ROLE_OPTIONS'		=> $s_role_options)
		);

		if ($display_item)
		{
			$template->assign_vars(array(
				'S_DISPLAY_ROLE_MASK'	=> true)
			);

			$hold_ary = $auth_admin->get_role_mask($display_item);
			$auth_admin->display_role_mask($hold_ary);
		}
	}

	/**
	* Display permission settings able to be set
	*/
	function display_auth_options($auth_options)
	{
		global $template, $user;

		$content_array = $categories = array();
		$key_sort_array = array(0);
		$auth_options = array(0 => $auth_options);
		
		// Making use of auth_admin method here (we do not really want to change two similar code fragments)
		auth_admin::build_permission_array($auth_options, $content_array, $categories, $key_sort_array);

		$content_array = $content_array[0];
		
		$template->assign_var('S_NUM_PERM_COLS', sizeof($categories));

		// Assign to template
		foreach ($content_array as $cat => $cat_array)
		{
			$template->assign_block_vars('auth', array(
				'CAT_NAME'	=> $user->lang['permission_cat'][$cat],

				'S_YES'		=> ($cat_array['S_YES'] && !$cat_array['S_NO'] && !$cat_array['S_UNSET']) ? true : false,
				'S_NO'		=> ($cat_array['S_NO'] && !$cat_array['S_YES'] && !$cat_array['S_UNSET']) ? true : false,
				'S_UNSET'	=> ($cat_array['S_UNSET'] && !$cat_array['S_NO'] && !$cat_array['S_YES']) ? true : false)
			);
				
			foreach ($cat_array['permissions'] as $permission => $allowed)
			{
				$template->assign_block_vars('auth.mask', array(
					'S_YES'		=> ($allowed == ACL_YES) ? true : false,
					'S_NO'		=> ($allowed == ACL_NO) ? true : false,
					'S_UNSET'	=> ($allowed == ACL_UNSET) ? true : false,

					'FIELD_NAME'	=> $permission,
					'PERMISSION'	=> $user->lang['acl_' . $permission]['lang'])
				);
			}
		}
	}

	
	/**
	* Build user-friendly group options
	*/
	function build_group_options($role_group_ids, &$s_preselect_options, &$s_group_options)
	{
		global $db, $user, $template;

		$groups = $selected_groups = array();

		$sql = 'SELECT group_id, group_name, group_type
			FROM ' . GROUPS_TABLE . '
			ORDER BY group_type DESC, group_name ASC';
		$result = $db->sql_query($sql);

		$groups = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$groups[$row['group_type']][$row['group_id']] = $row['group_name'];
		}
		$db->sql_freeresult($result);

		$selected_group_ids = explode(':', $role_group_ids);

		// First of all, build the group options for the custom interface...
		$s_group_options = '';
		foreach ($groups as $group_type => $group_row)
		{
			foreach ($group_row as $group_id => $group_name)
			{
				if (in_array($group_id, $selected_group_ids))
				{
					$selected_groups[$group_type][$group_id] = $group_name;
				}
				$s_group_options .= '<option value="' . $group_id . '"' . ((in_array($group_id, $selected_group_ids)) ? ' selected="selected"' : '') . (($group_type == GROUP_SPECIAL) ? ' class="sep"' : '') . '>' . (($group_type == GROUP_SPECIAL) ? $user->lang['G_' . $group_name] : $group_name) . '</option>';
			}
		}

		// Build preselect array...
		$one_selected_item = false;

		$s_preselect_options = '<option value="0"' . ((!$role_group_ids) ? ' selected="selected"' : '') . '>' . $user->lang['EVERY_USER_GROUP'] . '</option>';
		if (!$role_group_ids)
		{
			$one_selected_item = true;
		}

		// Build pre-selection dropdown field
		foreach ($this->pre_selection_array as $option_id => $row)
		{
			if (!$row['negate'] && !isset($row['name']))
			{
				$s_selected = false;
				if (sizeof($selected_groups) == 1 && isset($selected_groups[$row['type']]) && sizeof($selected_groups[$row['type']]) == sizeof($groups[$row['type']]))
				{
					$s_selected = true;
				}
			}
			else if ($row['negate'] && !isset($row['name']))
			{
				$group_types = array(GROUP_OPEN, GROUP_CLOSED, GROUP_HIDDEN, GROUP_SPECIAL, GROUP_FREE);
				unset($group_types[array_search($row['type'], $group_types)]);

				$s_selected = true;
				if (isset($selected_groups[$row['type']]))
				{
					$s_selected = false;
				}
				
				foreach ($group_types as $type)
				{
					if (!isset($selected_groups[$type]) || sizeof($selected_groups[$type]) != sizeof($groups[$type]))
					{
						$s_selected = false;
					}
				}
			}
			else if (!$row['negate'] && isset($row['name']))
			{
				$s_selected = false;
				if (sizeof($selected_groups) == 1 && isset($selected_groups[$row['type']]) && sizeof($selected_groups[$row['type']]) == sizeof($row['name']))
				{
					$s_selected = true;

					foreach ($row['name'] as $name)
					{
						if (!in_array($name, $selected_groups[$row['type']]))
						{
							$s_selected = false;
						}
					}
				}
			}
			else if ($row['negate'] && isset($row['name']))
			{
				$group_types = array(GROUP_OPEN, GROUP_CLOSED, GROUP_HIDDEN, GROUP_SPECIAL, GROUP_FREE);
				unset($group_types[array_search($row['type'], $group_types)]);

				$s_selected = true;
				if (isset($selected_groups[$row['type']]))
				{
					foreach ($row['name'] as $name)
					{
						if (in_array($name, $selected_groups[$row['type']]))
						{
							$s_selected = false;
						}
					}
				}

				if ($s_selected)
				{
					foreach ($group_types as $type)
					{
						if (!isset($groups[$type]))
						{
							continue;
						}

						if (!isset($selected_groups[$type]) || sizeof($selected_groups[$type]) != sizeof($groups[$type]))
						{
							$s_selected = false;
						}
					}
				}
			}

			if ($s_selected)
			{
				$one_selected_item = true;
			}

			$s_preselect_options .= '<option value="' . $option_id . '"' . (($s_selected) ? ' selected="selected"' : '') . '>' . $user->lang[$row['lang']] . '</option>';
		}

		$s_preselect_options .= '<option value="custom"' . ((!$one_selected_item) ? ' selected="selected"' : '') . '>' . $user->lang['CUSTOM'] . '</option>';

		$template->assign_var('S_CUSTOM_GROUP_IDS', ($one_selected_item) ? false : true);
	}

	/**
	* Remove role
	*/
	function remove_role($role_id, $permission_type)
	{
		global $db;

		$auth_admin = new auth_admin();
		
		// Get complete auth array
		$sql = 'SELECT auth_option, auth_option_id
			FROM ' . ACL_OPTIONS_TABLE . "
			WHERE auth_option LIKE '" . $db->sql_escape($permission_type) . "%'";
		$result = $db->sql_query($sql);

		$auth_settings = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$auth_settings[$row['auth_option']] = ACL_UNSET;
		}
		$db->sql_freeresult($result);

		// Get the role auth settings we need to re-set...
		$sql = 'SELECT o.auth_option, r.auth_setting
			FROM ' . ACL_ROLES_DATA_TABLE . ' r, ' . ACL_OPTIONS_TABLE . ' o
			WHERE o.auth_option_id = r.auth_option_id
				AND r.role_id = ' . $role_id;
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$auth_settings[$row['auth_option']] = $row['auth_setting'];
		}
		$db->sql_freeresult($result);

		// Get role assignments
		$hold_ary = $auth_admin->get_role_mask($role_id);

		// Re-assign permisisons
		foreach ($hold_ary as $forum_id => $forum_ary)
		{
			if (isset($forum_ary['users']))
			{
				$auth_admin->acl_set('user', $forum_id, $forum_ary['users'], $auth_settings, 0, false);
			}

			if (isset($forum_ary['groups']))
			{
				$auth_admin->acl_set('group', $forum_id, $forum_ary['groups'], $auth_settings, 0, false);
			}
		}

		// Remove role from users and groups just to be sure (happens through acl_set)
		$sql = 'DELETE FROM ' . ACL_USERS_TABLE . '
			WHERE auth_role_id = ' . $role_id;
		$db->sql_query($sql);

		$sql = 'DELETE FROM ' . ACL_GROUPS_TABLE . '
			WHERE auth_role_id = ' . $role_id;
		$db->sql_query($sql);

		// Remove role data and role
		$sql = 'DELETE FROM ' . ACL_ROLES_DATA_TABLE . '
			WHERE role_id = ' . $role_id;
		$db->sql_query($sql);

		$sql = 'DELETE FROM ' . ACL_ROLES_TABLE . '
			WHERE role_id = ' . $role_id;
		$db->sql_query($sql);

		$auth_admin->acl_clear_prefetch();
	}
}

?>