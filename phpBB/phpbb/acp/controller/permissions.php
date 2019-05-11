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
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\acp\helper\auth_admin */
	protected $auth_admin;

	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\group\helper */
	protected $group_helper;

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

	/** @var array permission types */
	protected $permission_dropdown;

	/** @todo */
	public $page_title;
	public $tpl_name;
	public $u_action;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\auth\auth						$auth			Auth object
	 * @param \phpbb\acp\helper\auth_admin			$auth_admin		Auth admin object
	 * @param \phpbb\cache\driver\driver_interface	$cache			Cache object
	 * @param \phpbb\config\config					$config			Config object
	 * @param \phpbb\db\driver\driver_interface		$db				Database object
	 * @param \phpbb\group\helper					$group_helper	Group helper object
	 * @param \phpbb\language\language				$lang			Language object
	 * @param \phpbb\log\log						$log			Log object
	 * @param \phpbb\permissions					$permissions	Permissions object
	 * @param \phpbb\request\request				$request		Request object
	 * @param \phpbb\template\template				$template		Template object
	 * @param \phpbb\user							$user			User object
	 * @param string								$root_path		phpBB root path
	 * @param string								$php_ext		php File extension
	 * @param array									$tables			phpBB tables
	 */
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\acp\helper\auth_admin $auth_admin,
		\phpbb\cache\driver\driver_interface $cache,
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\group\helper $group_helper,
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
		$this->auth			= $auth;
		$this->auth_admin	= $auth_admin;
		$this->cache		= $cache;
		$this->config		= $config;
		$this->db			= $db;
		$this->group_helper	= $group_helper;
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

	function main($id, $mode)
	{
		$this->lang->add_lang('acp/permissions');
		add_permission_language();

		if (!function_exists('user_get_id_name'))
		{
			include($this->root_path . 'includes/functions_user.' . $this->php_ext);
		}

		$this->tpl_name = 'acp_permissions';

		// Trace has other vars
		if ($mode === 'trace')
		{
			$user_id = $this->request->variable('u', 0);
			$forum_id = $this->request->variable('f', 0);
			$permission = $this->request->variable('auth', '');

			$this->tpl_name = 'permission_trace';

			if ($user_id && isset($this->auth_admin->acl_options['id'][$permission]) && $this->auth->acl_get('a_viewauth'))
			{
				$this->page_title = $this->lang->lang('TRACE_PERMISSION', $this->permissions->get_permission_lang($permission));
				$this->permission_trace($user_id, $forum_id, $permission);
				return;
			}
			trigger_error('NO_MODE', E_USER_ERROR);
		}

		// Copy forum permissions
		if ($mode === 'setting_forum_copy')
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
		$action = $this->request->is_set_post('psubmit') ? 'apply_permissions' : $action;

		$forum_ids		= $this->request->variable('forum_id', [0]);
		$all_forums		= $this->request->variable('all_forums', 0);
		$subforum_id	= $this->request->variable('subforum_id', 0);

		$user_ids		= $this->request->variable('user_id', [0]);
		$username		= $this->request->variable('username', [''], true);
		$usernames		= $this->request->variable('usernames', '', true);

		$group_ids		= $this->request->variable('group_id', [0]);
		$select_all_groups = $this->request->variable('select_all_groups', 0);

		$form_key = 'acp_permissions';
		add_form_key($form_key);

		// If select all groups is set, we pre-build the group id array (this option is used for other screens to link to the permission settings screen)
		if ($select_all_groups)
		{
			// Add default groups to selection
			$sql_and = (!$this->config['coppa_enable']) ? " AND group_name <> 'REGISTERED_COPPA'" : '';

			$sql = 'SELECT group_id
				FROM ' . $this->tables['groups'] . '
				WHERE group_type = ' . GROUP_SPECIAL . "
				$sql_and";
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$group_ids[] = $row['group_id'];
			}
			$this->db->sql_freeresult($result);
		}

		// Map usernames to ids and vice versa
		if ($usernames)
		{
			$username = explode("\n", $usernames);
		}
		unset($usernames);

		if (!empty($username) && empty($user_ids))
		{
			user_get_id_name($user_ids, $username);

			if (empty($user_ids))
			{
				trigger_error($this->lang->lang('SELECTED_USER_NOT_EXIST') . adm_back_link($this->u_action), E_USER_WARNING);
			}
		}
		unset($username);

		// Build forum ids (of all forums are checked or subforum listing used)
		if ($all_forums)
		{
			$forum_ids = [];

			$sql = 'SELECT forum_id
				FROM ' . $this->tables['forums'] . '
				ORDER BY left_id';
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$forum_ids[] = (int) $row['forum_id'];
			}
			$this->db->sql_freeresult($result);
		}
		else if ($subforum_id)
		{
			$forum_ids = [];

			foreach (get_forum_branch($subforum_id, 'children') as $row)
			{
				$forum_ids[] = (int) $row['forum_id'];
			}
		}

		// Define some common variables for every mode
		$permission_scope = (strpos($mode, '_global') !== false) ? 'global' : 'local';

		// Showing introduction page?
		if ($mode === 'intro')
		{
			$this->page_title = 'ACP_PERMISSIONS';

			$this->template->assign_var('S_INTRO', true);

			return;
		}

		switch ($mode)
		{
			case 'setting_user_global':
			case 'setting_group_global':
				$this->permission_dropdown = ['u_', 'm_', 'a_'];
				$permission_victim = ($mode === 'setting_user_global') ? ['user'] : ['group'];
				$this->page_title = ($mode === 'setting_user_global') ? 'ACP_USERS_PERMISSIONS' : 'ACP_GROUPS_PERMISSIONS';
			break;

			case 'setting_user_local':
			case 'setting_group_local':
				$this->permission_dropdown = ['f_', 'm_'];
				$permission_victim = ($mode === 'setting_user_local') ? ['user', 'forums'] : ['group', 'forums'];
				$this->page_title = ($mode === 'setting_user_local') ? 'ACP_USERS_FORUM_PERMISSIONS' : 'ACP_GROUPS_FORUM_PERMISSIONS';
			break;

			case 'setting_admin_global':
			case 'setting_mod_global':
				$this->permission_dropdown = (strpos($mode, '_admin_') !== false) ? ['a_'] : ['m_'];
				$permission_victim = ['usergroup'];
				$this->page_title = ($mode === 'setting_admin_global') ? 'ACP_ADMINISTRATORS' : 'ACP_GLOBAL_MODERATORS';
			break;

			case 'setting_mod_local':
			case 'setting_forum_local':
				$this->permission_dropdown = ($mode === 'setting_mod_local') ? ['m_'] : ['f_'];
				$permission_victim = ['forums', 'usergroup'];
				$this->page_title = ($mode === 'setting_mod_local') ? 'ACP_FORUM_MODERATORS' : 'ACP_FORUM_PERMISSIONS';
			break;

			case 'view_admin_global':
			case 'view_user_global':
			case 'view_mod_global':
				$this->permission_dropdown = ($mode === 'view_admin_global') ? ['a_'] : (($mode === 'view_user_global') ? ['u_'] : ['m_']);
				$permission_victim = ['usergroup_view'];
				$this->page_title = ($mode === 'view_admin_global') ? 'ACP_VIEW_ADMIN_PERMISSIONS' : (($mode === 'view_user_global') ? 'ACP_VIEW_USER_PERMISSIONS' : 'ACP_VIEW_GLOBAL_MOD_PERMISSIONS');
			break;

			case 'view_mod_local':
			case 'view_forum_local':
				$this->permission_dropdown = ($mode === 'view_mod_local') ? ['m_'] : ['f_'];
				$permission_victim = ['forums', 'usergroup_view'];
				$this->page_title = ($mode === 'view_mod_local') ? 'ACP_VIEW_FORUM_MOD_PERMISSIONS' : 'ACP_VIEW_FORUM_PERMISSIONS';
			break;

			default:
				trigger_error('NO_MODE', E_USER_ERROR);
			break;
		}

		$this->template->assign_vars([
			'L_TITLE'		=> $this->lang->lang($this->page_title),
			'L_EXPLAIN'		=> $this->lang->lang($this->page_title . '_EXPLAIN'),
		]);

		// Get permission type
		$permission_type = $this->request->variable('type', $this->permission_dropdown[0]);

		if (!in_array($permission_type, $this->permission_dropdown))
		{
			trigger_error($this->lang->lang('WRONG_PERMISSION_TYPE') . adm_back_link($this->u_action), E_USER_WARNING);
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
						$all_users = $this->request->is_set_post('all_users');
						$all_groups = $this->request->is_set_post('all_groups');

						if ($all_users || $all_groups)
						{
							$items = $this->retrieve_defined_user_groups($permission_scope, $forum_ids, $permission_type);

							if ($all_users && !empty($items['user_ids']))
							{
								$user_ids = $items['user_ids'];
							}
							else if ($all_groups && !empty($items['group_ids']))
							{
								$group_ids = $items['group_ids'];
							}
						}

						if (!empty($user_ids) || !empty($group_ids))
						{
							$this->remove_permissions($mode, $permission_type, $user_ids, $group_ids, $forum_ids);
						}
						else
						{
							trigger_error($this->lang->lang('NO_USER_GROUP_SELECTED') . adm_back_link($this->u_action), E_USER_WARNING);
						}
					}
					else
					{
						if ($this->request->is_set_post('cancel'))
						{
							$u_redirect = $this->u_action . '&amp;type=' . $permission_type;
							foreach ($forum_ids as $forum_id)
							{
								$u_redirect .= '&amp;forum_id[]=' . $forum_id;
							}
							redirect($u_redirect);
						}

						$s_hidden_fields = [
							'i'				=> $id,
							'mode'			=> $mode,
							'action'		=> [$action => 1],
							'user_id'		=> $user_ids,
							'group_id'		=> $group_ids,
							'forum_id'		=> $forum_ids,
							'type'			=> $permission_type,
						];
						if ($this->request->is_set_post('all_user'))
						{
							$s_hidden_fields['all_users'] = 1;
						}
						if ($this->request->is_set_post('all_groups'))
						{
							$s_hidden_fields['all_groups'] = 1;
						}
						confirm_box(false, $this->lang->lang('CONFIRM_OPERATION'), build_hidden_fields($s_hidden_fields));
					}
				break;

				case 'apply_permissions':
					if (!$this->request->is_set_post('setting'))
					{
						send_status_line(403, 'Forbidden');
						trigger_error($this->lang->lang('NO_AUTH_SETTING_FOUND') . adm_back_link($this->u_action), E_USER_WARNING);
					}
					if (!check_form_key($form_key))
					{
						trigger_error($this->lang->lang('FORM_INVALID'). adm_back_link($this->u_action), E_USER_WARNING);
					}

					$this->set_permissions($mode, $permission_type, $user_ids, $group_ids);
				break;

				case 'apply_all_permissions':
					if (!$this->request->is_set_post('setting'))
					{
						send_status_line(403, 'Forbidden');
						trigger_error($this->lang->lang('NO_AUTH_SETTING_FOUND') . adm_back_link($this->u_action), E_USER_WARNING);
					}
					if (!check_form_key($form_key))
					{
						trigger_error($this->lang->lang('FORM_INVALID'). adm_back_link($this->u_action), E_USER_WARNING);
					}

					$this->set_all_permissions($mode, $permission_type, $user_ids, $group_ids);
				break;
			}
		}

		// Go through the screens/options needed and present them in correct order
		foreach ($permission_victim as $victim)
		{
			switch ($victim)
			{
				case 'forum_dropdown':
					if (!empty($forum_ids))
					{
						$this->check_existence('forum', $forum_ids);
						continue 2;
					}

					$this->template->assign_vars([
						'S_SELECT_FORUM'		=> true,
						'S_FORUM_OPTIONS'		=> make_forum_select(false, false, true, false, false),
					]);
				break;

				case 'forums':
					if (!empty($forum_ids))
					{
						$this->check_existence('forum', $forum_ids);
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
						'S_FORUM_MULTIPLE'		=> true,
					]);
				break;

				case 'user':
					if (!empty($user_ids))
					{
						$this->check_existence('user', $user_ids);
						continue 2;
					}

					$this->template->assign_vars([
						'S_SELECT_USER'			=> true,
						'U_FIND_USERNAME'		=> append_sid("{$this->root_path}memberlist.$this->php_ext", 'mode=searchuser&amp;form=select_victim&amp;field=username&amp;select_single=true'),
					]);
				break;

				case 'group':
					if (!empty($group_ids))
					{
						$this->check_existence('group', $group_ids);
						continue 2;
					}

					$this->template->assign_vars([
						'S_SELECT_GROUP'		=> true,
						'S_GROUP_OPTIONS'		=> group_select_options(false, false, false), // Show all groups
					]);
				break;

				case 'usergroup':
				case 'usergroup_view':
					$all_users = $this->request->is_set_post('all_users');
					$all_groups = $this->request->is_set_post('all_groups');

					if ((!empty($user_ids) && !$all_users) || (!empty($group_ids) && !$all_groups))
					{
						if (!empty($user_ids))
						{
							$this->check_existence('user', $user_ids);
						}

						if (!empty($group_ids))
						{
							$this->check_existence('group', $group_ids);
						}

						continue 2;
					}

					// Now we check the users... because the "all"-selection is different here (all defined users/groups)
					$items = $this->retrieve_defined_user_groups($permission_scope, $forum_ids, $permission_type);

					if ($all_users && !empty($items['user_ids']))
					{
						$user_ids = $items['user_ids'];
						continue 2;
					}

					if ($all_groups && !empty($items['group_ids']))
					{
						$group_ids = $items['group_ids'];
						continue 2;
					}

					$this->template->assign_vars([
						'S_SELECT_USERGROUP'		=> $victim === 'usergroup',
						'S_SELECT_USERGROUP_VIEW'	=> $victim === 'usergroup_view',
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
				'user_id'		=> $user_ids,
				'group_id'		=> $group_ids,
				'forum_id'		=> $forum_ids,
				'type'			=> $permission_type,
			]);

			$this->template->assign_vars([
				'U_ACTION'				=> $this->u_action,
				'ANONYMOUS_USER_ID'		=> ANONYMOUS,

				'S_SELECT_VICTIM'		=> true,
				'S_ALLOW_ALL_SELECT'	=> count($forum_ids) > 5 ? false : true,
				'S_CAN_SELECT_USER'		=> $this->auth->acl_get('a_authusers'),
				'S_CAN_SELECT_GROUP'	=> $this->auth->acl_get('a_authgroups'),
				'S_HIDDEN_FIELDS'		=> $s_hidden_fields,
			]);

			// Let the forum names being displayed
			if (!empty($forum_ids))
			{
				$forum_names = [];

				$sql = 'SELECT forum_name
					FROM ' . $this->tables['forums'] . '
					WHERE ' . $this->db->sql_in_set('forum_id', $forum_ids) . '
					ORDER BY left_id ASC';
				$result = $this->db->sql_query($sql);
				while ($row = $this->db->sql_fetchrow($result))
				{
					$forum_names[] = $row['forum_name'];
				}
				$this->db->sql_freeresult($result);

				$this->template->assign_vars([
					'S_FORUM_NAMES'		=> !empty($forum_names),
					'FORUM_NAMES'		=> implode($this->lang->lang('COMMA_SEPARATOR'), $forum_names),
				]);
			}

			return;
		}

		// Setting permissions screen
		$s_hidden_fields = build_hidden_fields([
			'user_id'		=> $user_ids,
			'group_id'		=> $group_ids,
			'forum_id'		=> $forum_ids,
			'type'			=> $permission_type,
		]);

		// Do not allow forum_ids being set and no other setting defined (will bog down the server too much)
		if (!empty($forum_ids) && empty($user_ids) && empty($group_ids))
		{
			trigger_error($this->lang->lang('ONLY_FORUM_DEFINED') . adm_back_link($this->u_action), E_USER_WARNING);
		}

		$this->template->assign_vars([
			'S_PERMISSION_DROPDOWN'		=> count($this->permission_dropdown) > 1 ? $this->build_permission_dropdown($this->permission_dropdown, $permission_type, $permission_scope) : false,
			'L_PERMISSION_TYPE'			=> $this->permissions->get_type_lang($permission_type),

			'U_ACTION'					=> $this->u_action,
			'S_HIDDEN_FIELDS'			=> $s_hidden_fields,
		]);

		if (strpos($mode, 'setting_') === 0)
		{
			$this->template->assign_var('S_SETTING_PERMISSIONS', true);

			$hold_ary = $this->auth_admin->get_mask('set', !empty($user_ids) ? $user_ids : false, !empty($group_ids) ? $group_ids : false, !empty($forum_ids) ? $forum_ids : false, $permission_type, $permission_scope, ACL_NO
			);
			$this->auth_admin->display_mask('set', $permission_type, $hold_ary, !empty($user_ids) ? 'user' : 'group', $permission_scope === 'local');
		}
		else
		{
			$this->template->assign_var('S_VIEWING_PERMISSIONS', true);

			$hold_ary = $this->auth_admin->get_mask('view', !empty($user_ids) ? $user_ids : false, !empty($group_ids) ? $group_ids : false, !empty($forum_ids) ? $forum_ids : false, $permission_type, $permission_scope, ACL_NEVER);
			$this->auth_admin->display_mask('view', $permission_type, $hold_ary, (!empty($user_ids) ? 'user' : 'group'), $permission_scope === 'local');
		}
	}

	/**
	 * Build subforum options
	 *
	 * @param array		$forum_list		The forums
	 * @return string					The <select> options
	 */
	function build_subforum_options(array $forum_list)
	{
		$s_options = '';

		// Reset keys
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
				$s_options .= ' [' . $this->lang->lang('PLUS_SUBFORUMS') . ']';
			}

			$s_options .= '</option>';
		}

		return $s_options;
	}

	/**
	 * Build dropdown field for changing permission types.
	 *
	 * @param array		$options			The permission options
	 * @param string	$default_option		The selected (default) option
	 * @param string	$permission_scope	The permission scope (local|global)
	 * @return string						The <option> list
	 */
	function build_permission_dropdown(array $options, $default_option, $permission_scope)
	{
		$s_dropdown_options = '';

		foreach ($options as $setting)
		{
			if (!$this->auth->acl_get('a_' . str_replace('_', '', $setting) . 'auth'))
			{
				continue;
			}

			$selected = $setting == $default_option ? ' selected="selected"' : '';
			$l_setting = $this->permissions->get_type_lang($setting, $permission_scope);
			$s_dropdown_options .= '<option value="' . $setting . '"' . $selected . '>' . $l_setting . '</option>';
		}

		return $s_dropdown_options;
	}

	/**
	 * Check if selected items exist. Remove not found ids and if empty return error.
	 *
	 * @param string	$mode		The module mode (forum|group|user)
	 * @param array		$ids		The mode's identifiers
	 * @return void
	 */
	function check_existence($mode, array &$ids)
	{
		if (!empty($ids))
		{
			$ids = [];

			$sql_id	= "{$mode}_id";
			$table	= "{$mode}s";

			$sql = "SELECT $sql_id
				FROM " . $this->tables[$table] . '
				WHERE ' . $this->db->sql_in_set($sql_id, $ids);
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$ids[] = (int) $row[$sql_id];
			}
			$this->db->sql_freeresult($result);
		}

		if (empty($ids))
		{
			trigger_error($this->lang->lang('SELECTED_' . strtoupper($mode) . '_NOT_EXIST') . adm_back_link($this->u_action), E_USER_WARNING);
		}
	}

	/**
	 * Apply permissions.
	 *
	 * @param string		$mode				The module mode
	 * @param string		$permission_type	The permission type (a_|m_|u_|f_)
	 * @param array			$user_ids			The user identifiers
	 * @param array			$group_ids			The group identifiers
	 * @return void
	 */
	function set_permissions($mode, $permission_type, array &$user_ids, array &$group_ids)
	{
		$psubmit = $this->request->variable('psubmit', [0 => [0 => 0]]);

		// User or group to be set?
		$ug_type = !empty($user_ids) ? 'user' : 'group';

		// Check the permission setting again
		if (!$this->auth->acl_get('a_' . str_replace('_', '', $permission_type) . 'auth') || !$this->auth->acl_get('a_auth' . $ug_type . 's'))
		{
			send_status_line(403, 'Forbidden');
			trigger_error($this->lang->lang('NO_AUTH_OPERATION') . adm_back_link($this->u_action), E_USER_WARNING);
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

		if (!empty($inherit))
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
		$this->auth_admin->acl_set($ug_type, $forum_id, $ug_id, $auth_settings, $assigned_role);

		// Do we need to re-cache the moderator lists?
		if ($permission_type === 'm_')
		{
			phpbb_cache_moderators($this->db, $this->cache, $this->auth);
		}

		// Remove users who are now moderators or admins from everyone's foes list
		if ($permission_type === 'm_' || $permission_type === 'a_')
		{
			phpbb_update_foes($this->db, $this->auth, $group_ids, $user_ids);
		}

		$this->log_action($mode, 'add', $permission_type, $ug_type, $ug_id, $forum_id);

		meta_refresh(5, $this->u_action);
		trigger_error($this->lang->lang('AUTH_UPDATED') . adm_back_link($this->u_action));
	}

	/**
	 * Apply all permissions.
	 *
	 * @param string		$mode				The module mode
	 * @param string		$permission_type	The permission type (a_|m_|u_|f_)
	 * @param array			$user_ids			The user identifiers
	 * @param array			$group_ids			The group identifiers
	 * @return void
	 */
	function set_all_permissions($mode, $permission_type, array &$user_ids, array &$group_ids)
	{
		// User or group to be set?
		$ug_type = !empty($user_ids) ? 'user' : 'group';

		// Check the permission setting again
		if (!$this->auth->acl_get('a_' . str_replace('_', '', $permission_type) . 'auth') || !$this->auth->acl_get('a_auth' . $ug_type . 's'))
		{
			send_status_line(403, 'Forbidden');
			trigger_error($this->lang->lang('NO_AUTH_OPERATION') . adm_back_link($this->u_action), E_USER_WARNING);
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
				$this->auth_admin->acl_set($ug_type, $forum_id, $ug_id, $auth_options, $assigned_role, false);
			}
		}

		$this->auth_admin->acl_clear_prefetch();

		// Do we need to re-cache the moderator lists?
		if ($permission_type === 'm_')
		{
			phpbb_cache_moderators($this->db, $this->cache, $this->auth);
		}

		// Remove users who are now moderators or admins from everyone's foes list
		if ($permission_type === 'm_' || $permission_type === 'a_')
		{
			phpbb_update_foes($this->db, $this->auth, $group_ids, $user_ids);
		}

		$this->log_action($mode, 'add', $permission_type, $ug_type, $ug_ids, $forum_ids);

		if ($mode === 'setting_forum_local' || $mode === 'setting_mod_local')
		{
			meta_refresh(5, $this->u_action . '&amp;forum_id[]=' . implode('&amp;forum_id[]=', $forum_ids));
			trigger_error($this->lang->lang('AUTH_UPDATED') . adm_back_link($this->u_action . '&amp;forum_id[]=' . implode('&amp;forum_id[]=', $forum_ids)));
		}
		else
		{
			meta_refresh(5, $this->u_action);
			trigger_error($this->lang->lang('AUTH_UPDATED') . adm_back_link($this->u_action));
		}
	}

	/**
	 * Compare auth settings with auth settings from role.
	 *
	 * @param int		$role_id		The role identifier
	 * @param array		$auth_settings	The auth settings
	 * @return bool						false if they differ, true if they are equal
	 */
	function check_assigned_role($role_id, array &$auth_settings)
	{
		$test_auth_settings = [];

		$sql = 'SELECT o.auth_option, r.auth_setting
			FROM ' . $this->tables['acl_options'] . ' o, ' . $this->tables['acl_roles_data'] . ' r
			WHERE o.auth_option_id = r.auth_option_id
				AND r.role_id = ' . (int) $role_id;
		$result = $this->db->sql_query($sql);
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

		$auth_diff = array_diff_assoc($auth_settings, $test_auth_settings);

		if (!empty($auth_diff))
		{
			return false;
		}

		return true;
	}

	/**
	 * Remove permissions
	 *
	 * @param string		$mode				The module mode
	 * @param string		$permission_type	The permission type (a_|m_|u_|f_)
	 * @param array			$user_ids			The user identifiers
	 * @param array			$group_ids			The group identifiers
	 * @param array			$forum_ids			The forum identifiers
	 * @return void
	 */
	function remove_permissions($mode, $permission_type, array &$user_ids, array &$group_ids, array &$forum_ids)
	{
		// User or group to be set?
		$ug_type = !empty($user_ids) ? 'user' : 'group';

		// Check the permission setting again
		if (!$this->auth->acl_get('a_' . str_replace('_', '', $permission_type) . 'auth') || !$this->auth->acl_get('a_auth' . $ug_type . 's'))
		{
			send_status_line(403, 'Forbidden');
			trigger_error($this->lang->lang('NO_AUTH_OPERATION') . adm_back_link($this->u_action), E_USER_WARNING);
		}

		$this->auth_admin->acl_delete($ug_type, ($ug_type === 'user' ? $user_ids : $group_ids), (!empty($forum_ids) ? $forum_ids : false), $permission_type);

		// Do we need to re-cache the moderator lists?
		if ($permission_type === 'm_')
		{
			phpbb_cache_moderators($this->db, $this->cache, $this->auth);
		}

		$this->log_action($mode, 'del', $permission_type, $ug_type, ($ug_type === 'user' ? $user_ids : $group_ids), (!empty($forum_ids) ? $forum_ids : [0 => 0]));

		if ($mode === 'setting_forum_local' || $mode === 'setting_mod_local')
		{
			meta_refresh(5, $this->u_action . '&amp;forum_id[]=' . implode('&amp;forum_id[]=', $forum_ids));
			trigger_error($this->lang->lang('AUTH_UPDATED') . adm_back_link($this->u_action . '&amp;forum_id[]=' . implode('&amp;forum_id[]=', $forum_ids)));
		}
		else
		{
			meta_refresh(5, $this->u_action);
			trigger_error($this->lang->lang('AUTH_UPDATED') . adm_back_link($this->u_action));
		}
	}

	/**
	 * Log permission changes
	 *
	 * @param string		$mode				The module mode
	 * @param string		$action				The action
	 * @param string		$permission_type	The permission type (a_|m_|u_|f_)
	 * @param int			$ug_type			The type (user|group)
	 * @param int|array		$ug_id				The user or group identifiers
	 * @param int|array		$forum_ids			The forum identifiers
	 * @return void
	 */
	function log_action($mode, $action, $permission_type, $ug_type, $ug_id, $forum_ids)
	{
		if (!is_array($ug_id))
		{
			$ug_id = [$ug_id];
		}

		if (!is_array($forum_ids))
		{
			$forum_ids = [$forum_ids];
		}

		$l_ug_list = '';

		// Logging ... first grab user or group names ...
		$sql = $ug_type === 'group' ? 'SELECT group_name as name, group_type FROM ' . $this->tables['groups'] . ' WHERE ' : 'SELECT username as name FROM ' . $this->tables['users'] . ' WHERE ';
		$sql .= $this->db->sql_in_set($ug_type === 'group' ? 'group_id' : 'user_id', array_map('intval', $ug_id));
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$group_name = $this->group_helper->get_name($row['name']);
			$l_ug_list .= ($l_ug_list !== '' ? ', ' : '') . ((isset($row['group_type']) && $row['group_type'] == GROUP_SPECIAL) ? '<span class="sep">' . $group_name . '</span>' : $group_name);
		}
		$this->db->sql_freeresult($result);

		$mode = str_replace('setting_', '', $mode);

		if ($forum_ids[0] == 0)
		{
			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_ACL_' . strtoupper($action) . '_' . strtoupper($mode) . '_' . strtoupper($permission_type), false, [$l_ug_list]);
		}
		else
		{
			// Grab the forum details if non-zero forum_id
			$l_forum_list = '';

			$sql = 'SELECT forum_name
				FROM ' . $this->tables['forums'] . '
				WHERE ' . $this->db->sql_in_set('forum_id', $forum_ids);
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$l_forum_list .= (($l_forum_list != '') ? ', ' : '') . $row['forum_name'];
			}
			$this->db->sql_freeresult($result);

			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_ACL_' . strtoupper($action) . '_' . strtoupper($mode) . '_' . strtoupper($permission_type), false, [$l_forum_list, $l_ug_list]);
		}
	}

	/**
	 * Display a complete trace tree for the selected permission to determine where settings are set/unset.
	 *
	 * @param int		$user_id		The user identifier
	 * @param int		$forum_id		The forum identifier
	 * @param string	$permission		The permission name
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

		$forum_name = false;

		if ($forum_id)
		{
			$sql = 'SELECT forum_name
				FROM ' . $this->tables['forums'] . '
				WHERE forum_id = ' . (int) $forum_id;
			$result = $this->db->sql_query($sql, 3600);
			$forum_name = $this->db->sql_fetchfield('forum_name');
			$this->db->sql_freeresult($result);
		}

		$back = $this->request->variable('back', 0);

		$this->template->assign_vars([
			'PERMISSION'			=> $this->permissions->get_permission_lang($permission),
			'PERMISSION_USERNAME'	=> $userdata['username'],
			'FORUM_NAME'			=> $forum_name,

			'S_GLOBAL_TRACE'		=> empty($forum_id),

			'U_BACK'				=> $back ? build_url(['f', 'back']) . "&amp;f=$back" : '',
		]);

		$this->template->assign_block_vars('trace', [
			'WHO'			=> $this->lang->lang('DEFAULT'),
			'INFORMATION'	=> $this->lang->lang('TRACE_DEFAULT'),

			'S_SETTING_NO'		=> true,
			'S_TOTAL_NO'		=> true,
		]);

		$groups = [];

		$sql = 'SELECT DISTINCT g.group_name, g.group_id, g.group_type
			FROM ' . $this->tables['groups'] . ' g
				LEFT JOIN ' . $this->tables['user_group'] . ' ug ON (ug.group_id = g.group_id)
			WHERE ug.user_id = ' . (int) $user_id . '
				AND ug.user_pending = 0
				AND NOT (ug.group_leader = 1 AND g.group_skip_auth = 1)
			ORDER BY g.group_type DESC, g.group_id DESC';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$groups[$row['group_id']] = [
				'auth_setting'		=> ACL_NO,
				'group_name'		=> $this->group_helper->get_name($row['group_name']),
			];
		}
		$this->db->sql_freeresult($result);

		$total = ACL_NO;
		$add_key = $forum_id ? '_LOCAL' : '';
		$information = '';

		if (!empty($groups))
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
						$information = 'TRACE_GROUP_NO' . $add_key;
					break;

					case ACL_YES:
						$information = 'TRACE_GROUP_YES_TOTAL_' . $this->get_constant_string($total) . $add_key;
						$total = $total == ACL_NO ? ACL_YES : $total;
					break;

					case ACL_NEVER:
						$information = 'TRACE_GROUP_NEVER_TOTAL_' . $this->get_constant_string($total) . $add_key;
						$total = ACL_NEVER;
					break;
				}

				$this->template->assign_block_vars('trace', [
					'WHO'			=> $row['group_name'],
					'INFORMATION'	=> $this->lang->lang($information),

					'S_SETTING_NO'		=> $row['auth_setting'] == ACL_NO,
					'S_SETTING_YES'		=> $row['auth_setting'] == ACL_YES,
					'S_SETTING_NEVER'	=> $row['auth_setting'] == ACL_NEVER,
					'S_TOTAL_NO'		=> $total == ACL_NO,
					'S_TOTAL_YES'		=> $total == ACL_YES,
					'S_TOTAL_NEVER'		=> $total == ACL_NEVER,
				]);
			}
		}

		// Get user specific permission... globally or for this forum
		$hold_ary = $this->auth->acl_user_raw_data($user_id, $permission, $forum_id);
		$auth_setting = empty($hold_ary) ? ACL_NO : $hold_ary[$user_id][$forum_id][$permission];

		switch ($auth_setting)
		{
			case ACL_NO:
				$information = $total == ACL_NO ? 'TRACE_USER_NO_TOTAL_NO' . $add_key : 'TRACE_USER_KEPT' . $add_key;
				$total = ($total == ACL_NO) ? ACL_NEVER : $total;
			break;

			case ACL_YES:
				$information = 'TRACE_USER_YES_TOTAL_' . $this->get_constant_string($total) . $add_key;
				$total = ($total == ACL_NO) ? ACL_YES : $total;
			break;

			case ACL_NEVER:
				$information = 'TRACE_USER_NEVER_TOTAL_' . $this->get_constant_string($total) . $add_key;
				$total = ACL_NEVER;
			break;
		}

		$this->template->assign_block_vars('trace', [
			'WHO'			=> $userdata['username'],
			'INFORMATION'	=> $this->lang->lang($information),

			'S_SETTING_NO'		=> $auth_setting == ACL_NO,
			'S_SETTING_YES'		=> $auth_setting == ACL_YES,
			'S_SETTING_NEVER'	=> $auth_setting == ACL_NEVER,
			'S_TOTAL_NO'		=> false,
			'S_TOTAL_YES'		=> $total == ACL_YES,
			'S_TOTAL_NEVER'		=> $total == ACL_NEVER,
		]);

		if ($forum_id != 0 && isset($this->auth->acl_options['global'][$permission]))
		{
			if ($user_id != $this->user->data['user_id'])
			{
				$auth2 = $this->auth;
				$auth2->acl($userdata);

				$auth_setting = $auth2->acl_get($permission);
			}
			else
			{
				$auth_setting = $this->auth->acl_get($permission);
			}

			if ($auth_setting)
			{
				$information = $total == ACL_YES ? 'TRACE_USER_GLOBAL_YES_TOTAL_YES': 'TRACE_USER_GLOBAL_YES_TOTAL_NEVER';
				$total = ACL_YES;
			}
			else
			{
				$information = 'TRACE_USER_GLOBAL_NEVER_TOTAL_KEPT';
			}

			// If there is no auth information we do not need to worry the user by showing non-relevant data.
			if ($auth_setting)
			{
				$this->template->assign_block_vars('trace', [
					'WHO'			=> $this->lang->lang('TRACE_GLOBAL_SETTING', $userdata['username']),
					'INFORMATION'	=> $this->lang->lang($information, '<a href="' . $this->u_action . "&amp;u=$user_id&amp;f=0&amp;auth=$permission&amp;back=$forum_id\">", '</a>'),

					'S_SETTING_NO'		=> false,
					'S_SETTING_YES'		=> $auth_setting,
					'S_SETTING_NEVER'	=> !$auth_setting,
					'S_TOTAL_NO'		=> false,
					'S_TOTAL_YES'		=> $total == ACL_YES,
					'S_TOTAL_NEVER'		=> $total == ACL_NEVER,
				]);
			}
		}

		// Take founder status into account, overwriting the default values
		if ($userdata['user_type'] == USER_FOUNDER && strpos($permission, 'a_') === 0)
		{
			$this->template->assign_block_vars('trace', [
				'WHO'			=> $userdata['username'],
				'INFORMATION'	=> $this->lang->lang('TRACE_USER_FOUNDER'),

				'S_SETTING_NO'		=> $auth_setting == ACL_NO,
				'S_SETTING_YES'		=> $auth_setting == ACL_YES,
				'S_SETTING_NEVER'	=> $auth_setting == ACL_NEVER,
				'S_TOTAL_NO'		=> false,
				'S_TOTAL_YES'		=> true,
				'S_TOTAL_NEVER'		=> false,
			]);

			$total = ACL_YES;
		}

		// Total value...
		$this->template->assign_vars([
			'S_RESULT_NO'		=> $total == ACL_NO,
			'S_RESULT_YES'		=> $total == ACL_YES,
			'S_RESULT_NEVER'	=> $total == ACL_NEVER,
		]);
	}

	/**
	 * Handles copying permissions from one forum to others
	 */
	function copy_forum_permissions()
	{
		$this->lang->add_lang('acp/forums');

		$submit = $this->request->is_set_post('submit');

		if ($submit)
		{
			$src = $this->request->variable('src_forum_id', 0);
			$dest = $this->request->variable('dest_forum_ids', [0]);

			if (confirm_box(true))
			{
				if (copy_forum_permissions($src, $dest))
				{
					phpbb_cache_moderators($this->db, $this->cache, $this->auth);

					$this->auth->acl_clear_prefetch();
					$this->cache->destroy('sql', $this->tables['forums']);

					trigger_error($this->lang->lang('AUTH_UPDATED') . adm_back_link($this->u_action));
				}
				else
				{
					trigger_error($this->lang->lang('SELECTED_FORUM_NOT_EXIST') . adm_back_link($this->u_action), E_USER_WARNING);
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

				confirm_box(false, $this->lang->lang('COPY_PERMISSIONS_CONFIRM'), $s_hidden_fields);
			}
		}

		$this->template->assign_var('S_FORUM_OPTIONS', make_forum_select(false, false, false, false, false));
	}

	/**
	 * Get already assigned users/groups.
	 *
	 * @param string	$permission_scope	The permission scope (global|local)
	 * @param array		$forum_ids			The forum identifiers
	 * @param string	$permission_type	The permission type (a_|m_|u_|f_)
	 * @return array						The assigned users / groups identifiers and <select> options
	 */
	function retrieve_defined_user_groups($permission_scope, array $forum_ids, $permission_type)
	{
		$sql_forum_id = ($permission_scope === 'global') ? 'AND a.forum_id = 0' : (!empty($forum_id) ? 'AND ' . $this->db->sql_in_set('a.forum_id', $forum_ids) : 'AND a.forum_id <> 0');

		// Permission options are only able to be a permission set... therefore we will pre-fetch the possible options and also the possible roles
		$option_ids = $role_ids = [];

		$sql = 'SELECT auth_option_id
			FROM ' . $this->tables['acl_options'] . '
			WHERE auth_option ' . $this->db->sql_like_expression($permission_type . $this->db->get_any_char());
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$option_ids[] = (int) $row['auth_option_id'];
		}
		$this->db->sql_freeresult($result);

		if (!empty($option_ids))
		{
			$sql = 'SELECT DISTINCT role_id
				FROM ' . $this->tables['acl_roles_data'] . '
				WHERE ' . $this->db->sql_in_set('auth_option_id', $option_ids);
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$role_ids[] = (int) $row['role_id'];
			}
			$this->db->sql_freeresult($result);
		}

		$sql_where = '';

		if (!empty($option_ids) && !empty($role_ids))
		{
			$sql_where = 'AND (' . $this->db->sql_in_set('a.auth_option_id', $option_ids) . ' OR ' . $this->db->sql_in_set('a.auth_role_id', $role_ids) . ')';
		}
		else if (!empty($role_ids))
		{
			$sql_where = 'AND ' . $this->db->sql_in_set('a.auth_role_id', $role_ids);
		}
		else if (!empty($option_ids))
		{
			$sql_where = 'AND ' . $this->db->sql_in_set('a.auth_option_id', $option_ids);
		}

		$s_defined_user_options = '';
		$defined_user_ids = [];

		// Not ideal, due to the file sort, non-use of indexes, etc.
		$sql = 'SELECT DISTINCT u.user_id, u.username, u.username_clean, u.user_regdate
			FROM ' . $this->tables['users'] . ' u, ' . $this->tables['acl_users'] . " a
			WHERE u.user_id = a.user_id
				$sql_forum_id
				$sql_where
			ORDER BY u.username_clean, u.user_regdate ASC";
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$s_defined_user_options .= '<option value="' . $row['user_id'] . '">' . $row['username'] . '</option>';
			$defined_user_ids[] = $row['user_id'];
		}
		$this->db->sql_freeresult($result);

		$s_defined_group_options = '';
		$defined_group_ids = [];

		$sql = 'SELECT DISTINCT g.group_type, g.group_name, g.group_id
			FROM ' . $this->tables['groups'] . ' g, ' . $this->tables['acl_groups'] . " a
			WHERE g.group_id = a.group_id
				$sql_forum_id
				$sql_where
			ORDER BY g.group_type DESC, g.group_name ASC";
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$s_defined_group_options .= '<option' . ($row['group_type'] == GROUP_SPECIAL ? ' class="sep"' : '') . ' value="' . $row['group_id'] . '">' . $this->group_helper->get_name($row['group_name']) . '</option>';
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

	/**
	 * Get the string value for an ACL constant.
	 *
	 * @param int		$value	The value to get the constant string for.
	 * @return string			The constant string
	 */
	protected function get_constant_string($value)
	{
		switch ($value)
		{
			case ACL_NEVER:
				return 'NEVER';

			case ACL_NO:
				return 'NO';

			case ACL_YES:
				return 'YES';

			default:
				return '';
		}
	}
}
