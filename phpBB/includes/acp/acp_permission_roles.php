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

class acp_permission_roles
{
	var $u_action;
	protected $auth_admin;

	function main($id, $mode)
	{
		global $db, $user, $template, $phpbb_container;
		global $phpbb_root_path, $phpEx;
		global $request, $phpbb_log;

		if (!function_exists('user_get_id_name'))
		{
			include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
		}

		if (!class_exists('auth_admin'))
		{
			include($phpbb_root_path . 'includes/acp/auth.' . $phpEx);
		}

		$this->auth_admin = new auth_admin();

		$user->add_lang('acp/permissions');
		add_permission_language();

		$this->tpl_name = 'acp_permission_roles';

		$submit = (isset($_POST['submit'])) ? true : false;
		$role_id = $request->variable('role_id', 0);
		$action = $request->variable('action', '');
		$action = (isset($_POST['add'])) ? 'add' : $action;

		$form_name = 'acp_permissions';
		add_form_key($form_name);

		if (!$role_id && in_array($action, array('remove', 'edit', 'move_up', 'move_down')))
		{
			trigger_error($user->lang['NO_ROLE_SELECTED'] . adm_back_link($this->u_action), E_USER_WARNING);
		}

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
				trigger_error('NO_MODE', E_USER_ERROR);
			break;
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

					$sql = 'SELECT *
						FROM ' . ACL_ROLES_TABLE . '
						WHERE role_id = ' . $role_id;
					$result = $db->sql_query($sql);
					$role_row = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);

					if (!$role_row)
					{
						trigger_error($user->lang['NO_ROLE_SELECTED'] . adm_back_link($this->u_action), E_USER_WARNING);
					}

					if (confirm_box(true))
					{
						$this->remove_role($role_id, $permission_type);

						$role_name = (!empty($user->lang[$role_row['role_name']])) ? $user->lang[$role_row['role_name']] : $role_row['role_name'];
						$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_' . strtoupper($permission_type) . 'ROLE_REMOVED', false, array($role_name));
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

					// Get role we edit
					$sql = 'SELECT *
						FROM ' . ACL_ROLES_TABLE . '
						WHERE role_id = ' . $role_id;
					$result = $db->sql_query($sql);
					$role_row = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);

					if (!$role_row)
					{
						trigger_error($user->lang['NO_ROLE_SELECTED'] . adm_back_link($this->u_action), E_USER_WARNING);
					}

				// no break;

				case 'add':

					if (!check_form_key($form_name))
					{
						trigger_error($user->lang['FORM_INVALID']. adm_back_link($this->u_action), E_USER_WARNING);
					}

					$role_name = $request->variable('role_name', '', true);
					$role_description = $request->variable('role_description', '', true);
					$auth_settings = $request->variable('setting', array('' => 0));

					if (!$role_name)
					{
						trigger_error($user->lang['NO_ROLE_NAME_SPECIFIED'] . adm_back_link($this->u_action), E_USER_WARNING);
					}

					if (utf8_strlen($role_description) > 4000)
					{
						trigger_error($user->lang['ROLE_DESCRIPTION_LONG'] . adm_back_link($this->u_action), E_USER_WARNING);
					}

					// if we add/edit a role we check the name to be unique among the settings...
					$sql = 'SELECT role_id
						FROM ' . ACL_ROLES_TABLE . "
						WHERE role_type = '" . $db->sql_escape($permission_type) . "'
							AND role_name = '" . $db->sql_escape($role_name) . "'";
					$result = $db->sql_query($sql);
					$row = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);

					// Make sure we only print out the error if we add the role or change it's name
					if ($row && ($action == 'add' || ($action == 'edit' && $role_row['role_name'] != $role_name)))
					{
						trigger_error(sprintf($user->lang['ROLE_NAME_ALREADY_EXIST'], $role_name) . adm_back_link($this->u_action), E_USER_WARNING);
					}

					$sql_ary = array(
						'role_name'			=> (string) $role_name,
						'role_description'	=> (string) $role_description,
						'role_type'			=> (string) $permission_type,
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
						// Get maximum role order for inserting a new role...
						$sql = 'SELECT MAX(role_order) as max_order
							FROM ' . ACL_ROLES_TABLE . "
							WHERE role_type = '" . $db->sql_escape($permission_type) . "'";
						$result = $db->sql_query($sql);
						$max_order = (int) $db->sql_fetchfield('max_order');
						$db->sql_freeresult($result);

						$sql_ary['role_order'] = $max_order + 1;

						$sql = 'INSERT INTO ' . ACL_ROLES_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
						$db->sql_query($sql);

						$role_id = $db->sql_nextid();
					}

					// Now add the auth settings
					$this->auth_admin->acl_set_role($role_id, $auth_settings);

					$role_name = (!empty($user->lang[$role_name])) ? $user->lang[$role_name] : $role_name;
					$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_' . strtoupper($permission_type) . 'ROLE_' . strtoupper($action), false, array($role_name));

					trigger_error($user->lang['ROLE_' . strtoupper($action) . '_SUCCESS'] . adm_back_link($this->u_action));

				break;
			}
		}

		// Display screens
		switch ($action)
		{
			case 'add':

				$options_from = $request->variable('options_from', 0);

				$role_row = array(
					'role_name'			=> $request->variable('role_name', '', true),
					'role_description'	=> $request->variable('role_description', '', true),
					'role_type'			=> $permission_type,
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
						WHERE auth_option " . $db->sql_like_expression($permission_type . $db->get_any_char()) . "
							AND auth_option <> '{$permission_type}'
						ORDER BY auth_option_id";
					$result = $db->sql_query($sql);

					$auth_options = array();
					while ($row = $db->sql_fetchrow($result))
					{
						$auth_options[$row['auth_option']] = ACL_NO;
					}
					$db->sql_freeresult($result);
				}

			// no break;

			case 'edit':

				if ($action == 'edit')
				{
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
					trigger_error($user->lang['NO_ROLE_SELECTED'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

				/* @var $phpbb_permissions \phpbb\permissions */
				$phpbb_permissions = $phpbb_container->get('acl.permissions');

				$template->assign_vars(array(
					'S_EDIT'			=> true,

					'U_ACTION'			=> $this->u_action . "&amp;action={$action}&amp;role_id={$role_id}",
					'U_BACK'			=> $this->u_action,

					'ROLE_NAME'			=> $role_row['role_name'],
					'ROLE_DESCRIPTION'	=> $role_row['role_description'],
					'L_ACL_TYPE'		=> $phpbb_permissions->get_type_lang($permission_type),
				));

				// We need to fill the auth options array with ACL_NO options ;)
				$sql = 'SELECT auth_option_id, auth_option
					FROM ' . ACL_OPTIONS_TABLE . "
					WHERE auth_option " . $db->sql_like_expression($permission_type . $db->get_any_char()) . "
						AND auth_option <> '{$permission_type}'
					ORDER BY auth_option_id";
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					if (!isset($auth_options[$row['auth_option']]))
					{
						$auth_options[$row['auth_option']] = ACL_NO;
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
					$hold_ary = $this->auth_admin->get_role_mask($role_id);

					if (count($hold_ary))
					{
						$role_name = (!empty($user->lang[$role_row['role_name']])) ? $user->lang[$role_row['role_name']] : $role_row['role_name'];

						$template->assign_vars(array(
							'S_DISPLAY_ROLE_MASK'	=> true,
							'L_ROLE_ASSIGNED_TO'	=> sprintf($user->lang['ROLE_ASSIGNED_TO'], $role_name))
						);

						$this->auth_admin->display_role_mask($hold_ary);
					}
				}

				return;
			break;

			case 'move_up':
			case 'move_down':

				if (!check_link_hash($request->variable('hash', ''), 'acp_permission_roles'))
				{
					trigger_error($user->lang['FORM_INVALID'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$sql = 'SELECT role_order
					FROM ' . ACL_ROLES_TABLE . "
					WHERE role_id = $role_id";
				$result = $db->sql_query($sql);
				$order = $db->sql_fetchfield('role_order');
				$db->sql_freeresult($result);

				if ($order === false || ($order == 0 && $action == 'move_up'))
				{
					break;
				}
				$order = (int) $order;
				$order_total = $order * 2 + (($action == 'move_up') ? -1 : 1);

				$sql = 'UPDATE ' . ACL_ROLES_TABLE . '
					SET role_order = ' . $order_total . " - role_order
					WHERE role_type = '" . $db->sql_escape($permission_type) . "'
						AND role_order IN ($order, " . (($action == 'move_up') ? $order - 1 : $order + 1) . ')';
				$db->sql_query($sql);

				if ($request->is_ajax())
				{
					$json_response = new \phpbb\json_response;
					$json_response->send(array(
						'success'	=> (bool) $db->sql_affectedrows(),
					));
				}

			break;
		}

		// By default, check that role_order is valid and fix it if necessary
		$sql = 'SELECT role_id, role_order
			FROM ' . ACL_ROLES_TABLE . "
			WHERE role_type = '" . $db->sql_escape($permission_type) . "'
			ORDER BY role_order ASC";
		$result = $db->sql_query($sql);

		if ($row = $db->sql_fetchrow($result))
		{
			$order = 0;
			do
			{
				$order++;
				if ($row['role_order'] != $order)
				{
					$db->sql_query('UPDATE ' . ACL_ROLES_TABLE . " SET role_order = $order WHERE role_id = {$row['role_id']}");
				}
			}
			while ($row = $db->sql_fetchrow($result));
		}
		$db->sql_freeresult($result);

		// Display assigned items?
		$display_item = $request->variable('display_item', 0);

		// Select existing roles
		$sql = 'SELECT *
			FROM ' . ACL_ROLES_TABLE . "
			WHERE role_type = '" . $db->sql_escape($permission_type) . "'
			ORDER BY role_order ASC";
		$result = $db->sql_query($sql);

		$s_role_options = '';
		while ($row = $db->sql_fetchrow($result))
		{
			$role_name = (!empty($user->lang[$row['role_name']])) ? $user->lang[$row['role_name']] : $row['role_name'];

			$template->assign_block_vars('roles', array(
				'ROLE_NAME'				=> $role_name,
				'ROLE_DESCRIPTION'		=> (!empty($user->lang[$row['role_description']])) ? $user->lang[$row['role_description']] : nl2br($row['role_description']),

				'U_EDIT'			=> $this->u_action . '&amp;action=edit&amp;role_id=' . $row['role_id'],
				'U_REMOVE'			=> $this->u_action . '&amp;action=remove&amp;role_id=' . $row['role_id'],
				'U_MOVE_UP'			=> $this->u_action . '&amp;action=move_up&amp;role_id=' . $row['role_id'] . '&amp;hash=' . generate_link_hash('acp_permission_roles'),
				'U_MOVE_DOWN'		=> $this->u_action . '&amp;action=move_down&amp;role_id=' . $row['role_id'] . '&amp;hash=' . generate_link_hash('acp_permission_roles'),
				'U_DISPLAY_ITEMS'	=> ($row['role_id'] == $display_item) ? '' : $this->u_action . '&amp;display_item=' . $row['role_id'] . '#assigned_to')
			);

			$s_role_options .= '<option value="' . $row['role_id'] . '">' . $role_name . '</option>';

			if ($display_item == $row['role_id'])
			{
				$template->assign_vars(array(
					'L_ROLE_ASSIGNED_TO'	=> sprintf($user->lang['ROLE_ASSIGNED_TO'], $role_name))
				);
			}
		}
		$db->sql_freeresult($result);

		$template->assign_vars(array(
			'S_ROLE_OPTIONS'		=> $s_role_options)
		);

		if ($display_item)
		{
			$template->assign_vars(array(
				'S_DISPLAY_ROLE_MASK'	=> true)
			);

			$hold_ary = $this->auth_admin->get_role_mask($display_item);
			$this->auth_admin->display_role_mask($hold_ary);
		}
	}

	/**
	* Display permission settings able to be set
	*/
	function display_auth_options($auth_options)
	{
		global $template, $phpbb_container;

		/* @var $phpbb_permissions \phpbb\permissions */
		$phpbb_permissions = $phpbb_container->get('acl.permissions');

		$content_array = $categories = array();
		$key_sort_array = array(0);
		$auth_options = array(0 => $auth_options);

		// Making use of auth_admin method here (we do not really want to change two similar code fragments)
		$this->auth_admin->build_permission_array($auth_options, $content_array, $categories, $key_sort_array);

		$content_array = $content_array[0];

		$template->assign_var('S_NUM_PERM_COLS', count($categories));

		// Assign to template
		foreach ($content_array as $cat => $cat_array)
		{
			$template->assign_block_vars('auth', array(
				'CAT_NAME'	=> $phpbb_permissions->get_category_lang($cat),

				'S_YES'		=> ($cat_array['S_YES'] && !$cat_array['S_NEVER'] && !$cat_array['S_NO']) ? true : false,
				'S_NEVER'	=> ($cat_array['S_NEVER'] && !$cat_array['S_YES'] && !$cat_array['S_NO']) ? true : false,
				'S_NO'		=> ($cat_array['S_NO'] && !$cat_array['S_NEVER'] && !$cat_array['S_YES']) ? true : false)
			);

			foreach ($cat_array['permissions'] as $permission => $allowed)
			{
				$template->assign_block_vars('auth.mask', array(
					'S_YES'		=> ($allowed == ACL_YES) ? true : false,
					'S_NEVER'	=> ($allowed == ACL_NEVER) ? true : false,
					'S_NO'		=> ($allowed == ACL_NO) ? true : false,

					'FIELD_NAME'	=> $permission,
					'PERMISSION'	=> $phpbb_permissions->get_permission_lang($permission),
				));
			}
		}
	}

	/**
	* Remove role
	*/
	function remove_role($role_id, $permission_type)
	{
		global $db;

		// Get complete auth array
		$sql = 'SELECT auth_option, auth_option_id
			FROM ' . ACL_OPTIONS_TABLE . "
			WHERE auth_option " . $db->sql_like_expression($permission_type . $db->get_any_char());
		$result = $db->sql_query($sql);

		$auth_settings = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$auth_settings[$row['auth_option']] = ACL_NO;
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
		$hold_ary = $this->auth_admin->get_role_mask($role_id);

		// Re-assign permissions
		foreach ($hold_ary as $forum_id => $forum_ary)
		{
			if (isset($forum_ary['users']))
			{
				$this->auth_admin->acl_set('user', $forum_id, $forum_ary['users'], $auth_settings, 0, false);
			}

			if (isset($forum_ary['groups']))
			{
				$this->auth_admin->acl_set('group', $forum_id, $forum_ary['groups'], $auth_settings, 0, false);
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

		$this->auth_admin->acl_clear_prefetch();
	}
}
