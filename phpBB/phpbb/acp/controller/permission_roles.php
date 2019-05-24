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

use phpbb\exception\back_exception;
use phpbb\exception\form_invalid_exception;

class permission_roles
{
	/** @var \phpbb\acp\helper\auth_admin */
	protected $auth_admin;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\acp\helper\controller */
	protected $helper;

	/** @var \phpbb\language\language */
	protected $lang;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \phpbb\permissions */
	protected $permissions;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string php File extension */
	protected $php_ext;

	/** @var array phpBB tables */
	protected $tables;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\acp\helper\auth_admin		$auth_admin		Auth admin object
	 * @param \phpbb\db\driver\driver_interface	$db				Database object
	 * @param \phpbb\acp\helper\controller		$helper			ACP Controller helper object
	 * @param \phpbb\language\language			$lang			Language object
	 * @param \phpbb\log\log					$log			Log object
	 * @param \phpbb\permissions				$permissions	Permissions object
	 * @param \phpbb\request\request			$request		Request object
	 * @param \phpbb\template\template			$template		Template object
	 * @param \phpbb\user						$user			User object
	 * @param string							$root_path		phpBB root path
	 * @param string							$php_ext		php File extension
	 * @param array								$tables			phpBB tables
	 */
	public function __construct(
		\phpbb\acp\helper\auth_admin $auth_admin,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\acp\helper\controller $helper,
		\phpbb\language\language $lang,
		\phpbb\log\log $log,
		\phpbb\permissions $permissions,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		$root_path,
		$php_ext,
		$tables
	)
	{
		$this->auth_admin	= $auth_admin;
		$this->db			= $db;
		$this->helper		= $helper;
		$this->lang			= $lang;
		$this->log			= $log;
		$this->permissions	= $permissions;
		$this->request		= $request;
		$this->template		= $template;
		$this->user			= $user;

		$this->root_path	= $root_path;
		$this->php_ext		= $php_ext;
		$this->tables		= $tables;
	}

	function main($mode)
	{
		$this->lang->add_lang('acp/permissions');
		add_permission_language();

		if (!function_exists('user_get_id_name'))
		{
			include($this->root_path . 'includes/functions_user.' . $this->php_ext);
		}

		$action = $this->request->variable('action', '');
		$action = $this->request->is_set_post('add') ? 'add' : $action;
		$submit	= $this->request->is_set_post('submit');
		$role_id = $this->request->variable('role_id', 0);

		$form_key = 'acp_permissions';
		add_form_key($form_key);

		switch ($mode)
		{
			case 'admin_roles':
				$permission_type = 'a_';
				$l_mode = 'ACP_ADMIN_ROLES';
				$u_mode = 'acp_permissions_roles_admin';
			break;

			case 'user_roles':
				$permission_type = 'u_';
				$l_mode = 'ACP_USER_ROLES';
				$u_mode = 'acp_permissions_roles_user';
			break;

			case 'mod_roles':
				$permission_type = 'm_';
				$l_mode = 'ACP_MOD_ROLES';
				$u_mode = 'acp_permissions_roles_mod';
			break;

			case 'forum_roles':
				$permission_type = 'f_';
				$l_mode = 'ACP_FORUM_ROLES';
				$u_mode = 'acp_permissions_roles_forum';
			break;

			default:
				throw new back_exception(400, 'NO_MODE', 'acp_permissions');
			break;
		}

		if (!$role_id && in_array($action, ['remove', 'edit', 'move_up', 'move_down']))
		{
			throw new back_exception(400, 'NO_ROLE_SELECTED', $u_mode);
		}

		$this->template->assign_vars([
			'L_TITLE'		=> $this->lang->lang($l_mode),
			'L_EXPLAIN'		=> $this->lang->lang($l_mode . '_EXPLAIN'),
		]);

		// Take action... admin submitted something
		if ($submit || $action === 'remove')
		{
			switch ($action)
			{
				case 'remove':
					$sql = 'SELECT *
						FROM ' . $this->tables['acl_roles'] . '
						WHERE role_id = ' . $role_id;
					$result = $this->db->sql_query($sql);
					$role_row = $this->db->sql_fetchrow($result);
					$this->db->sql_freeresult($result);

					if ($role_row === false)
					{
						throw new back_exception(404, 'NO_ROLE_SELECTED', $u_mode);
					}

					if (confirm_box(true))
					{
						$this->remove_role($role_id, $permission_type);

						$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_' . strtoupper($permission_type) . 'ROLE_REMOVED', false, [$this->lang->lang($role_row['role_name'])]);

						return $this->helper->message_back('ROLE_DELETED', $u_mode);
					}
					else
					{
						confirm_box(false, 'DELETE_ROLE', build_hidden_fields([
							'action'	=> $action,
							'role_id'	=> $role_id,
						]));

						return redirect($this->helper->route($u_mode));
					}
				break;

				/** @noinspection PhpMissingBreakStatementInspection */
				case 'edit':
					// Get role we edit
					$sql = 'SELECT *
						FROM ' . $this->tables['acl_roles'] . '
						WHERE role_id = ' . $role_id;
					$result = $this->db->sql_query($sql);
					$role_row = $this->db->sql_fetchrow($result);
					$this->db->sql_freeresult($result);

					if ($role_row === false)
					{
						throw new back_exception(404, 'NO_ROLE_SELECTED', $u_mode);
					}
				// no break;

				case 'add':
					if (!check_form_key($form_key))
					{
						throw new form_invalid_exception($u_mode);
					}

					$role_row = !empty($role_row) ? $role_row : [];
					$role_name = $this->request->variable('role_name', '', true);
					$role_desc = $this->request->variable('role_description', '', true);
					$auth_settings= $this->request->variable('setting', ['' => 0]);

					if (!$role_name)
					{
						throw new back_exception(400, 'NO_ROLE_NAME_SPECIFIED', $u_mode);
					}

					if (utf8_strlen($role_desc) > 4000)
					{
						throw new back_exception(400, 'ROLE_DESCRIPTION_LONG', $u_mode);
					}

					// if we add/edit a role we check the name to be unique among the settings...
					$sql = 'SELECT role_id
						FROM ' . $this->tables['acl_roles'] . "
						WHERE role_type = '" . $this->db->sql_escape($permission_type) . "'
							AND role_name = '" . $this->db->sql_escape($role_name) . "'";
					$result = $this->db->sql_query($sql);
					$row = $this->db->sql_fetchrow($result);
					$this->db->sql_freeresult($result);

					// Make sure we only print out the error if we add the role or change it's name
					if ($row && ($mode === 'add' || ($mode === 'edit' && $role_row['role_name'] != $role_name)))
					{
						throw new back_exception(400, 'ROLE_NAME_ALREADY_EXIST', $u_mode, $role_name);
					}

					$sql_ary = [
						'role_name'			=> (string) $role_name,
						'role_description'	=> (string) $role_desc,
						'role_type'			=> (string) $permission_type,
					];

					if ($action === 'edit')
					{
						$sql = 'UPDATE ' . $this->tables['acl_roles'] . '
							SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
							WHERE role_id = ' . $role_id;
						$this->db->sql_query($sql);
					}
					else
					{
						// Get maximum role order for inserting a new role...
						$sql = 'SELECT MAX(role_order) as max_order
							FROM ' . $this->tables['acl_roles'] . "
							WHERE role_type = '" . $this->db->sql_escape($permission_type) . "'";
						$result = $this->db->sql_query($sql);
						$max_order = (int) $this->db->sql_fetchfield('max_order');
						$this->db->sql_freeresult($result);

						$sql_ary['role_order'] = $max_order + 1;

						$sql = 'INSERT INTO ' . $this->tables['acl_roles'] . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
						$this->db->sql_query($sql);

						$role_id = $this->db->sql_nextid();
					}

					// Now add the auth settings
					$this->auth_admin->acl_set_role($role_id, $auth_settings);

					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_' . strtoupper($permission_type) . 'ROLE_' . strtoupper($action), false, [$this->lang->lang($role_name)]);

					return $this->helper->message_back('ROLE_' . utf8_strtoupper($action) . '_SUCCESS', $u_mode);
				break;
			}
		}

		// Display screens
		switch ($action)
		{
			/** @noinspection PhpMissingBreakStatementInspection */
			case 'add':
				$options_from = $this->request->variable('options_from', 0);

				$role_row = [
					'role_name'			=> $this->request->variable('role_name', '', true),
					'role_description'	=> $this->request->variable('role_description', '', true),
					'role_type'			=> $permission_type,
				];

				if ($options_from)
				{
					$auth_options = [];

					$sql = 'SELECT p.auth_option_id, p.auth_setting, o.auth_option
						FROM ' . $this->tables['acl_roles_data'] . ' p, ' . $this->tables['acl_options'] . ' o
						WHERE o.auth_option_id = p.auth_option_id
							AND p.role_id = ' . (int) $options_from . '
						ORDER BY p.auth_option_id';
					$result = $this->db->sql_query($sql);
					while ($row = $this->db->sql_fetchrow($result))
					{
						$auth_options[$row['auth_option']] = $row['auth_setting'];
					}
					$this->db->sql_freeresult($result);
				}
				else
				{
					$auth_options = [];

					$sql = 'SELECT auth_option_id, auth_option
						FROM ' . $this->tables['acl_options'] . "
						WHERE auth_option " . $this->db->sql_like_expression($permission_type . $this->db->get_any_char()) . "
							AND auth_option <> '{$permission_type}'
						ORDER BY auth_option_id";
					$result = $this->db->sql_query($sql);
					while ($row = $this->db->sql_fetchrow($result))
					{
						$auth_options[$row['auth_option']] = ACL_NO;
					}
					$this->db->sql_freeresult($result);
				}
			// no break;

			case 'edit':
				if ($action === 'edit')
				{
					$sql = 'SELECT *
						FROM ' . $this->tables['acl_roles'] . '
						WHERE role_id = ' . $role_id;
					$result = $this->db->sql_query($sql);
					$role_row = $this->db->sql_fetchrow($result);
					$this->db->sql_freeresult($result);

					$auth_options = [];

					$sql = 'SELECT p.auth_option_id, p.auth_setting, o.auth_option
						FROM ' . $this->tables['acl_roles_data'] . ' p, ' . $this->tables['acl_options'] . ' o
						WHERE o.auth_option_id = p.auth_option_id
							AND p.role_id = ' . $role_id . '
						ORDER BY p.auth_option_id';
					$result = $this->db->sql_query($sql);
					while ($row = $this->db->sql_fetchrow($result))
					{
						$auth_options[$row['auth_option']] = $row['auth_setting'];
					}
					$this->db->sql_freeresult($result);
				}

				if (empty($role_row))
				{
					throw new back_exception(404, 'NO_ROLE_SELECTED', $u_mode);
				}

				$this->template->assign_vars([
					'S_EDIT'			=> true,

					'U_ACTION'			=> $this->helper->route($u_mode, ['action' => $action, 'role_id' => $role_id]),
					'U_BACK'			=> $this->helper->route($u_mode),

					'ROLE_NAME'			=> $role_row['role_name'],
					'ROLE_DESCRIPTION'	=> $role_row['role_description'],
					'L_ACL_TYPE'		=> $this->permissions->get_type_lang($permission_type),
				]);

				// We need to fill the auth options array with ACL_NO options ;)
				$sql = 'SELECT auth_option_id, auth_option
					FROM ' . $this->tables['acl_options'] . "
					WHERE auth_option " . $this->db->sql_like_expression($permission_type . $this->db->get_any_char()) . "
						AND auth_option <> '{$permission_type}'
					ORDER BY auth_option_id";
				$result = $this->db->sql_query($sql);
				while ($row = $this->db->sql_fetchrow($result))
				{
					if (!isset($auth_options[$row['auth_option']]))
					{
						$auth_options[$row['auth_option']] = ACL_NO;
					}
				}
				$this->db->sql_freeresult($result);

				// Unset global permission option
				unset($auth_options[$permission_type]);

				// Display auth options
				$this->display_auth_options($auth_options);

				// Get users/groups/forums using this preset...
				if ($action === 'edit')
				{
					$hold_ary = $this->auth_admin->get_role_mask($role_id);

					if (!empty($hold_ary))
					{
						$this->template->assign_vars([
							'S_DISPLAY_ROLE_MASK'	=> true,
							'L_ROLE_ASSIGNED_TO'	=> $this->lang->lang('ROLE_ASSIGNED_TO', $this->lang->lang($role_row['role_name'])),
						]);

						$this->auth_admin->display_role_mask($hold_ary);
					}
				}

				return $this->helper->render('acp_permission_roles.html', $l_mode);
			break;

			case 'move_up':
			case 'move_down':
				if (!check_link_hash($this->request->variable('hash', ''), 'acp_permission_roles'))
				{
					throw new form_invalid_exception($u_mode);
				}

				$sql = 'SELECT role_order
					FROM ' . $this->tables['acl_roles'] . '
					WHERE role_id = '. (int) $role_id;
				$result = $this->db->sql_query($sql);
				$order = (int) $this->db->sql_fetchfield('role_order');
				$this->db->sql_freeresult($result);

				if ($order === false || ($order === 0 && $action === 'move_up'))
				{
					break;
				}
				$order = (int) $order;
				$order_total = $order * 2 + ($action === 'move_up' ? -1 : 1);

				$sql = 'UPDATE ' . $this->tables['acl_roles'] . '
					SET role_order = ' . $order_total . " - role_order
					WHERE role_type = '" . $this->db->sql_escape($permission_type) . "'
						AND " . $this->db->sql_in_set('role_order', [$order, ($action === 'move_up' ? $order - 1 : $order + 1)]);
				$this->db->sql_query($sql);

				if ($this->request->is_ajax())
				{
					$json_response = new \phpbb\json_response;
					$json_response->send([
						'success'	=> (bool) $this->db->sql_affectedrows(),
					]);
				}
			break;
		}

		// By default, check that role_order is valid and fix it if necessary
		$sql = 'SELECT role_id, role_order
			FROM ' . $this->tables['acl_roles'] . "
			WHERE role_type = '" . $this->db->sql_escape($permission_type) . "'
			ORDER BY role_order ASC";
		$result = $this->db->sql_query($sql);

		if ($row = $this->db->sql_fetchrow($result))
		{
			$order = 0;
			do
			{
				$order++;
				if ($row['role_order'] != $order)
				{
					$this->db->sql_query('UPDATE ' . $this->tables['acl_roles'] . " SET role_order = $order WHERE role_id = {$row['role_id']}");
				}
			}
			while ($row = $this->db->sql_fetchrow($result));
		}
		$this->db->sql_freeresult($result);

		// Display assigned items?
		$display_item = $this->request->variable('display_item', 0);

		$s_role_options = '';

		// Select existing roles
		$sql = 'SELECT *
			FROM ' . $this->tables['acl_roles'] . "
			WHERE role_type = '" . $this->db->sql_escape($permission_type) . "'
			ORDER BY role_order ASC";
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->template->assign_block_vars('roles', [
				'ROLE_NAME'				=> $this->lang->lang($row['role_name']),
				'ROLE_DESCRIPTION'		=> $this->lang->is_set($row['role_description']) ? $this->lang->lang($row['role_description']) : nl2br($row['role_description']),

				'U_EDIT'			=> $this->helper->route($u_mode, ['action' => 'edit', 'role_id' => $row['role_id']]),
				'U_REMOVE'			=> $this->helper->route($u_mode, ['action' => 'remove', 'role_id' => $row['role_id']]),
				'U_MOVE_UP'			=> $this->helper->route($u_mode, ['action' => 'move_up', 'role_id' => $row['role_id'], 'hash' => generate_link_hash('acp_permission_roles')]),
				'U_MOVE_DOWN'		=> $this->helper->route($u_mode, ['action' => 'move_down', 'role_id' => $row['role_id'], 'hash' => generate_link_hash('acp_permission_roles')]),
				'U_DISPLAY_ITEMS'	=> $row['role_id'] == $display_item ? '' : $this->helper->route($u_mode, ['display_item' => $row['role_id'], '#' => 'assigned_to']),
			]);

			$s_role_options .= '<option value="' . $row['role_id'] . '">' . $this->lang->lang($row['role_name']) . '</option>';

			if ($display_item == $row['role_id'])
			{
				$this->template->assign_var('L_ROLE_ASSIGNED_TO', $this->lang->lang('ROLE_ASSIGNED_TO', $this->lang->lang($row['role_name'])));
			}
		}
		$this->db->sql_freeresult($result);

		$this->template->assign_var('S_ROLE_OPTIONS', $s_role_options);

		if ($display_item)
		{
			$this->template->assign_var('S_DISPLAY_ROLE_MASK', true);

			$hold_ary = $this->auth_admin->get_role_mask($display_item);
			$this->auth_admin->display_role_mask($hold_ary);
		}

		return $this->helper->render('acp_permission_roles.html', $l_mode);
	}

	/**
	 * Display permission settings able to be set.
	 *
	 * @param array		$auth_options
	 * @return void
	 */
	protected function display_auth_options(array $auth_options)
	{
		$content_array = $categories = [];
		$key_sort_array = [0];
		$auth_options = [0 => $auth_options];

		// Making use of auth_admin method here (we do not really want to change two similar code fragments)
		$this->auth_admin->build_permission_array($auth_options, $content_array, $categories, $key_sort_array);

		$content_array = $content_array[0];

		$this->template->assign_var('S_NUM_PERM_COLS', count($categories));

		// Assign to template
		foreach ($content_array as $cat => $cat_array)
		{
			$this->template->assign_block_vars('auth', [
				'CAT_NAME'	=> $this->permissions->get_category_lang($cat),

				'S_YES'		=> ($cat_array['S_YES'] && !$cat_array['S_NEVER'] && !$cat_array['S_NO']) ? true : false,
				'S_NEVER'	=> ($cat_array['S_NEVER'] && !$cat_array['S_YES'] && !$cat_array['S_NO']) ? true : false,
				'S_NO'		=> ($cat_array['S_NO'] && !$cat_array['S_NEVER'] && !$cat_array['S_YES']) ? true : false,
			]);

			foreach ($cat_array['permissions'] as $permission => $allowed)
			{
				$this->template->assign_block_vars('auth.mask', [
					'S_YES'		=> $allowed == ACL_YES,
					'S_NEVER'	=> $allowed == ACL_NEVER,
					'S_NO'		=> $allowed == ACL_NO,

					'FIELD_NAME'	=> $permission,
					'PERMISSION'	=> $this->permissions->get_permission_lang($permission),
				]);
			}
		}
	}

	/**
	 * Remove role.
	 *
	 * @param int		$role_id			The permission role identifier
	 * @param string	$permission_type	The permission type (a_|m_|u_|f_)
	 * @return void
	 */
	protected function remove_role($role_id, $permission_type)
	{
		$auth_settings = [];

		// Get complete auth array
		$sql = 'SELECT auth_option, auth_option_id
			FROM ' . $this->tables['acl_options'] . "
			WHERE auth_option " . $this->db->sql_like_expression($permission_type . $this->db->get_any_char());
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$auth_settings[$row['auth_option']] = ACL_NO;
		}
		$this->db->sql_freeresult($result);

		// Get the role auth settings we need to re-set...
		$sql = 'SELECT o.auth_option, r.auth_setting
			FROM ' . $this->tables['acl_roles_data'] . ' r, ' . $this->tables['acl_options'] . ' o
			WHERE o.auth_option_id = r.auth_option_id
				AND r.role_id = ' . $role_id;
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$auth_settings[$row['auth_option']] = $row['auth_setting'];
		}
		$this->db->sql_freeresult($result);

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
		$this->db->sql_transaction('begin');

		$sql = 'DELETE FROM ' . $this->tables['acl_users'] . '
			WHERE auth_role_id = ' . (int) $role_id;
		$this->db->sql_query($sql);

		$sql = 'DELETE FROM ' . $this->tables['acl_groups'] . '
			WHERE auth_role_id = ' . (int) $role_id;
		$this->db->sql_query($sql);

		// Remove role data and role
		$sql = 'DELETE FROM ' . $this->tables['acl_roles_data'] . '
			WHERE role_id = ' . (int) $role_id;
		$this->db->sql_query($sql);

		$sql = 'DELETE FROM ' . $this->tables['acl_roles'] . '
			WHERE role_id = ' . (int) $role_id;
		$this->db->sql_query($sql);

		$this->db->sql_transaction('commit');

		$this->auth_admin->acl_clear_prefetch();
	}
}
