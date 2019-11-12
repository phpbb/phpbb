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

class groups
{
	var $u_action;

	public function main($id, $mode)
	{
		/** @var \phpbb\language\language $language Language object */
		$language = $phpbb_container->get('language');

		$this->language->add_lang('acp/groups');
		$this->tpl_name = 'acp_groups';
		$this->page_title = 'ACP_GROUPS_MANAGE';

		$form_key = 'acp_groups';
		add_form_key($form_key);

		if ($mode == 'position')
		{
			$this->manage_position();
			return;
		}

		if (!function_exists('group_user_attributes'))
		{
			include($this->root_path . 'includes/functions_user.' . $this->php_ext);
		}

		// Check and set some common vars
		$action		= ($this->request->is_set_post('add')) ? 'add' : (($this->request->is_set_post('addusers')) ? 'addusers' : $this->request->variable('action', ''));
		$group_id	= $this->request->variable('g', 0);
		$mark_ary	= $this->request->variable('mark', [0]);
		$name_ary	= $this->request->variable('usernames', '', true);
		$leader		= $this->request->variable('leader', 0);
		$default	= $this->request->variable('default', 0);
		$start		= $this->request->variable('start', 0);
		$update		= ($this->request->is_set_post('update')) ? true : false;

		/** @var \phpbb\group\helper $group_helper */
		$group_helper = $phpbb_container->get('group_helper');

		// Clear some vars
		$group_row = [];

		// Grab basic data for group, if group_id is set and exists
		if ($group_id)
		{
			$sql = 'SELECT g.*, t.teampage_position AS group_teampage
				FROM ' . GROUPS_TABLE . ' g
				LEFT JOIN ' . TEAMPAGE_TABLE . ' t
					ON (t.group_id = g.group_id)
				WHERE g.group_id = ' . $group_id;
			$result = $this->db->sql_query($sql);
			$group_row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if (!$group_row)
			{
				trigger_error($this->language->lang('NO_GROUP') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			// Check if the user is allowed to manage this group if set to founder only.
			if ($this->user->data['user_type'] != USER_FOUNDER && $group_row['group_founder_manage'])
			{
				trigger_error($this->language->lang('NOT_ALLOWED_MANAGE_GROUP') . adm_back_link($this->u_action), E_USER_WARNING);
			}
		}

		// Which page?
		switch ($action)
		{
			case 'approve':
			case 'demote':
			case 'promote':
				if (!check_form_key($form_key))
				{
					trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
				}

				if (!$group_id)
				{
					trigger_error($this->language->lang('NO_GROUP') . adm_back_link($this->u_action), E_USER_WARNING);
				}

				// Approve, demote or promote
				$group_name = $this->group_helper->get_name($group_row['group_name']);
				$error = group_user_attributes($action, $group_id, $mark_ary, false, $group_name);

				if (!$error)
				{
					switch ($action)
					{
						case 'demote':
							$message = 'GROUP_MODS_DEMOTED';
						break;

						case 'promote':
							$message = 'GROUP_MODS_PROMOTED';
						break;

						case 'approve':
							$message = 'USERS_APPROVED';
						break;
					}

					trigger_error($this->language->lang($message) . adm_back_link($this->u_action . '&amp;action=list&amp;g=' . $group_id));
				}
				else
				{
					trigger_error($this->language->lang($error) . adm_back_link($this->u_action . '&amp;action=list&amp;g=' . $group_id), E_USER_WARNING);
				}

			break;

			case 'default':
				if (!$group_id)
				{
					trigger_error($this->language->lang('NO_GROUP') . adm_back_link($this->u_action), E_USER_WARNING);
				}
				else if (empty($mark_ary))
				{
					trigger_error($this->language->lang('NO_USERS') . adm_back_link($this->u_action . '&amp;action=list&amp;g=' . $group_id), E_USER_WARNING);
				}

				if (confirm_box(true))
				{
					$group_name = $this->group_helper->get_name($group_row['group_name']);
					group_user_attributes('default', $group_id, $mark_ary, false, $group_name, $group_row);
					trigger_error($this->language->lang('GROUP_DEFS_UPDATED') . adm_back_link($this->u_action . '&amp;action=list&amp;g=' . $group_id));
				}
				else
				{
					confirm_box(false, $this->language->lang('CONFIRM_OPERATION'), build_hidden_fields([
						'mark'		=> $mark_ary,
						'g'			=> $group_id,
						'i'			=> $id,
						'mode'		=> $mode,
						'action'	=> $action])
					);
				}
			break;

			case 'set_default_on_all':
				if (confirm_box(true))
				{
					$group_name = $this->group_helper->get_name($group_row['group_name']);

					$start = 0;

					do
					{
						$sql = 'SELECT user_id
							FROM ' . USER_GROUP_TABLE . "
							WHERE group_id = $group_id
							ORDER BY user_id";
						$result = $this->db->sql_query_limit($sql, 200, $start);

						$mark_ary = [];
						if ($row = $this->db->sql_fetchrow($result))
						{
							do
							{
								$mark_ary[] = $row['user_id'];
							}
							while ($row = $this->db->sql_fetchrow($result));

							group_user_attributes('default', $group_id, $mark_ary, false, $group_name, $group_row);

							$start = (count($mark_ary) < 200) ? 0 : $start + 200;
						}
						else
						{
							$start = 0;
						}
						$this->db->sql_freeresult($result);
					}
					while ($start);

					trigger_error($this->language->lang('GROUP_DEFS_UPDATED') . adm_back_link($this->u_action . '&amp;action=list&amp;g=' . $group_id));
				}
				else
				{
					confirm_box(false, $this->language->lang('CONFIRM_OPERATION'), build_hidden_fields([
						'mark'		=> $mark_ary,
						'g'			=> $group_id,
						'i'			=> $id,
						'mode'		=> $mode,
						'action'	=> $action])
					);
				}
			break;

			case 'deleteusers':
				if (empty($mark_ary))
				{
					trigger_error($this->language->lang('NO_USERS') . adm_back_link($this->u_action . '&amp;action=list&amp;g=' . $group_id), E_USER_WARNING);
				}
			case 'delete':
				if (!$group_id)
				{
					trigger_error($this->language->lang('NO_GROUP') . adm_back_link($this->u_action), E_USER_WARNING);
				}
				else if ($action === 'delete' && $group_row['group_type'] == GROUP_SPECIAL)
				{
					send_status_line(403, 'Forbidden');
					trigger_error($this->language->lang('NO_AUTH_OPERATION') . adm_back_link($this->u_action), E_USER_WARNING);
				}

				if (confirm_box(true))
				{
					$error = '';

					switch ($action)
					{
						case 'delete':
							if (!$this->auth->acl_get('a_groupdel'))
							{
								send_status_line(403, 'Forbidden');
								trigger_error($this->language->lang('NO_AUTH_OPERATION') . adm_back_link($this->u_action), E_USER_WARNING);
							}

							$error = group_delete($group_id, $group_row['group_name']);
						break;

						case 'deleteusers':
							$group_name = $this->group_helper->get_name($group_row['group_name']);
							$error = group_user_del($group_id, $mark_ary, false, $group_name);
						break;
					}

					$back_link = ($action == 'delete') ? $this->u_action : $this->u_action . '&amp;action=list&amp;g=' . $group_id;

					if ($error)
					{
						trigger_error($this->language->lang($error) . adm_back_link($back_link), E_USER_WARNING);
					}

					$message = ($action == 'delete') ? 'GROUP_DELETED' : 'GROUP_USERS_REMOVE';
					trigger_error($this->language->lang($message) . adm_back_link($back_link));
				}
				else
				{
					confirm_box(false, $this->language->lang('CONFIRM_OPERATION'), build_hidden_fields([
						'mark'		=> $mark_ary,
						'g'			=> $group_id,
						'i'			=> $id,
						'mode'		=> $mode,
						'action'	=> $action])
					);
				}
			break;

			case 'addusers':
				if (!check_form_key($form_key))
				{
					trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
				}

				if (!$group_id)
				{
					trigger_error($this->language->lang('NO_GROUP') . adm_back_link($this->u_action), E_USER_WARNING);
				}

				if (!$name_ary)
				{
					trigger_error($this->language->lang('NO_USERS') . adm_back_link($this->u_action . '&amp;action=list&amp;g=' . $group_id), E_USER_WARNING);
				}

				$name_ary = array_unique(explode("\n", $name_ary));
				$group_name = $this->group_helper->get_name($group_row['group_name']);

				// Add user/s to group
				if ($error = group_user_add($group_id, false, $name_ary, $group_name, $default, $leader, 0, $group_row))
				{
					$display_message = $this->language->lang($error);

					if ($error == 'GROUP_USERS_INVALID')
					{
						// Find which users don't exist
						$actual_name_ary = $name_ary;
						$actual_user_id_ary = [];
						user_get_id_name($actual_user_id_ary, $actual_name_ary, false, true);

						$display_message = $this->language->lang('GROUP_USERS_INVALID', implode($this->language->lang('COMMA_SEPARATOR'), array_udiff($name_ary, $actual_name_ary, 'strcasecmp')));
					}

					trigger_error($display_message . adm_back_link($this->u_action . '&amp;action=list&amp;g=' . $group_id), E_USER_WARNING);
				}

				$message = ($leader) ? 'GROUP_MODS_ADDED' : 'GROUP_USERS_ADDED';
				trigger_error($this->language->lang($message) . adm_back_link($this->u_action . '&amp;action=list&amp;g=' . $group_id));
			break;

			case 'edit':
			case 'add':

				if (!function_exists('display_forums'))
				{
					include($this->root_path . 'includes/functions_display.' . $this->php_ext);
				}

				if ($action == 'edit' && !$group_id)
				{
					trigger_error($this->language->lang('NO_GROUP') . adm_back_link($this->u_action), E_USER_WARNING);
				}

				if ($action == 'add' && !$this->auth->acl_get('a_groupadd'))
				{
					send_status_line(403, 'Forbidden');
					trigger_error($this->language->lang('NO_AUTH_OPERATION') . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$error = [];
				$this->language->add_lang('ucp');

				// Setup avatar data for later
				$avatars_enabled = false;
				$avatar_drivers = null;
				$avatar_data = null;
				$avatar_error = [];

				/** @var \phpbb\avatar\manager $phpbb_avatar_manager */
				$phpbb_avatar_manager = $phpbb_container->get('avatar.manager');

				if ($this->config['allow_avatar'])
				{
					$avatar_drivers = $this->avatar_manager->get_enabled_drivers();

					// This is normalised data, without the group_ prefix
					$avatar_data = \phpbb\avatar\manager::clean_row($group_row, 'group');
					if (!isset($avatar_data['id']))
					{
						$avatar_data['id'] = 'g' . $group_id;
					}
				}

				if ($this->request->is_set_post('avatar_delete'))
				{
					if (confirm_box(true))
					{
						$avatar_data['id'] = substr($avatar_data['id'], 1);
						$this->avatar_manager->handle_avatar_delete($db, $user, $avatar_data, GROUPS_TABLE, 'group_');

						$message = ($action == 'edit') ? 'GROUP_UPDATED' : 'GROUP_CREATED';
						trigger_error($this->language->lang($message) . adm_back_link($this->u_action));
					}
					else
					{
						confirm_box(false, $this->language->lang('CONFIRM_AVATAR_DELETE'), build_hidden_fields([
								'avatar_delete'     => true,
								'i'                 => $id,
								'mode'              => $mode,
								'g'			        => $group_id,
								'action'            => $action])
						);
					}
				}

				// Did we submit?
				if ($update)
				{
					if (!check_form_key($form_key))
					{
						trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
					}

					$group_name	= $this->request->variable('group_name', '', true);
					$group_desc = $this->request->variable('group_desc', '', true);
					$group_type	= $this->request->variable('group_type', GROUP_FREE);

					$allow_desc_bbcode	= $this->request->variable('desc_parse_bbcode', false);
					$allow_desc_urls	= $this->request->variable('desc_parse_urls', false);
					$allow_desc_smilies	= $this->request->variable('desc_parse_smilies', false);

					$submit_ary = [
						'colour'			=> $this->request->variable('group_colour', ''),
						'rank'				=> $this->request->variable('group_rank', 0),
						'receive_pm'		=> $this->request->is_set('group_receive_pm') ? 1 : 0,
						'legend'			=> $this->request->is_set('group_legend') ? 1 : 0,
						'teampage'			=> $this->request->is_set('group_teampage') ? 1 : 0,
						'message_limit'		=> $this->request->variable('group_message_limit', 0),
						'max_recipients'	=> $this->request->variable('group_max_recipients', 0),
						'founder_manage'	=> 0,
						'skip_auth'			=> $this->request->variable('group_skip_auth', 0),
					];

					if ($this->user->data['user_type'] == USER_FOUNDER)
					{
						$submit_ary['founder_manage'] = $this->request->is_set('group_founder_manage') ? 1 : 0;
					}

					if ($this->config['allow_avatar'])
					{
						// Handle avatar
						$driver_name = $this->avatar_manager->clean_driver_name($this->request->variable('avatar_driver', ''));

						if (in_array($driver_name, $avatar_drivers) && !$this->request->is_set_post('avatar_delete'))
						{
							$driver = $this->avatar_manager->get_driver($driver_name);
							$result = $driver->process_form($request, $template, $user, $avatar_data, $avatar_error);

							if ($result && empty($avatar_error))
							{
								$result['avatar_type'] = $driver_name;
								$submit_ary = array_merge($submit_ary, $result);
							}
						}
						else
						{
							$driver = $this->avatar_manager->get_driver($avatar_data['avatar_type']);
							if ($driver)
							{
								$driver->delete($avatar_data);
							}

							// Removing the avatar
							$submit_ary['avatar_type'] = '';
							$submit_ary['avatar'] = '';
							$submit_ary['avatar_width'] = 0;
							$submit_ary['avatar_height'] = 0;
						}

						// Merge any avatar errors into the primary error array
						$error = array_merge($error, $this->avatar_manager->localize_errors($user, $avatar_error));
					}

					/*
					 * Validate the length of "Maximum number of allowed recipients per
					 * private message" setting. We use 16777215 as a maximum because it matches
					 * MySQL unsigned mediumint maximum value which is the lowest amongst DBMSes
					 * supported by phpBB3. Also validate the submitted colour value.
					 */
					$validation_checks = [
						'max_recipients' => ['num', false, 0, 16777215],
						'colour'	=> ['hex_colour', true],
					];

					/**
					 * Request group data and operate on it
					 *
					 * @event core.acp_manage_group_request_data
					 * @var	string	action				Type of the action: add|edit
					 * @var	int		group_id			The group id
					 * @var	array	group_row			Array with new group data
					 * @var	array	error				Array of errors, if you add errors
					 *							ensure to update the template variables
					 *							S_ERROR and ERROR_MSG to display it
					 * @var	string	group_name			The group name
					 * @var	string	group_desc			The group description
					 * @var	int		group_type			The group type
					 * @var	bool	allow_desc_bbcode	Allow bbcode in group description: true|false
					 * @var	bool	allow_desc_urls		Allow urls in group description: true|false
					 * @var	bool	allow_desc_smilies	Allow smiles in group description: true|false
					 * @var	array	submit_ary			Array with new group data
					 * @var	array	validation_checks	Array with validation data
					 * @since 3.1.0-b5
					 */
					$vars = [
						'action',
						'group_id',
						'group_row',
						'error',
						'group_name',
						'group_desc',
						'group_type',
						'allow_desc_bbcode',
						'allow_desc_urls',
						'allow_desc_smilies',
						'submit_ary',
						'validation_checks',
					];
					extract($this->dispatcher->trigger_event('core.acp_manage_group_request_data', compact($vars)));

					if ($validation_error = validate_data($submit_ary, $validation_checks))
					{
						// Replace "error" string with its real, localised form
						$error = array_merge($error, $validation_error);
					}

					if (!count($error))
					{
						// Only set the rank, colour, etc. if it's changed or if we're adding a new
						// group. This prevents existing group members being updated if no changes
						// were made.
						// However there are some attributes that need to be set everytime,
						// otherwise the group gets removed from the feature.
						$set_attributes = ['legend', 'teampage'];

						$group_attributes = [];
						$test_variables = [
							'rank'			=> 'int',
							'colour'		=> 'string',
							'avatar'		=> 'string',
							'avatar_type'	=> 'string',
							'avatar_width'	=> 'int',
							'avatar_height'	=> 'int',
							'receive_pm'	=> 'int',
							'legend'		=> 'int',
							'teampage'		=> 'int',
							'message_limit'	=> 'int',
							'max_recipients'=> 'int',
							'founder_manage'=> 'int',
							'skip_auth'		=> 'int',
						];

						/**
						 * Initialise data before we display the add/edit form
						 *
						 * @event core.acp_manage_group_initialise_data
						 * @var	string	action				Type of the action: add|edit
						 * @var	int		group_id			The group id
						 * @var	array	group_row			Array with new group data
						 * @var	array	error				Array of errors, if you add errors
						 *							ensure to update the template variables
						 *							S_ERROR and ERROR_MSG to display it
						 * @var	string	group_name			The group name
						 * @var	string	group_desc			The group description
						 * @var	int		group_type			The group type
						 * @var	bool	allow_desc_bbcode	Allow bbcode in group description: true|false
						 * @var	bool	allow_desc_urls		Allow urls in group description: true|false
						 * @var	bool	allow_desc_smilies	Allow smiles in group description: true|false
						 * @var	array	submit_ary			Array with new group data
						 * @var	array	test_variables		Array with variables for test
						 * @since 3.1.0-b5
						 */
						$vars = [
							'action',
							'group_id',
							'group_row',
							'error',
							'group_name',
							'group_desc',
							'group_type',
							'allow_desc_bbcode',
							'allow_desc_urls',
							'allow_desc_smilies',
							'submit_ary',
							'test_variables',
						];
						extract($this->dispatcher->trigger_event('core.acp_manage_group_initialise_data', compact($vars)));

						foreach ($test_variables as $test => $type)
						{
							if (isset($submit_ary[$test]) && ($action == 'add' || $group_row['group_' . $test] != $submit_ary[$test] || isset($group_attributes['group_avatar']) && strpos($test, 'avatar') === 0 || in_array($test, $set_attributes)))
							{
								settype($submit_ary[$test], $type);
								$group_attributes['group_' . $test] = $group_row['group_' . $test] = $submit_ary[$test];
							}
						}

						if (!($error = group_create($group_id, $group_type, $group_name, $group_desc, $group_attributes, $allow_desc_bbcode, $allow_desc_urls, $allow_desc_smilies)))
						{
							$group_perm_from = $this->request->variable('group_perm_from', 0);

							// Copy permissions?
							// If the user has the a_authgroups permission and at least one additional permission ability set the permissions are fully transferred.
							// We do not limit on one auth category because this can lead to incomplete permissions being tricky to fix for the admin, roles being assigned or added non-default permissions.
							// Since the user only has the option to copy permissions from non leader managed groups this seems to be a good compromise.
							if ($group_perm_from && $action == 'add' && $this->auth->acl_get('a_authgroups') && $this->auth->acl_gets('a_aauth', 'a_fauth', 'a_mauth', 'a_uauth'))
							{
								$sql = 'SELECT group_founder_manage
									FROM ' . GROUPS_TABLE . '
									WHERE group_id = ' . $group_perm_from;
								$result = $this->db->sql_query($sql);
								$check_row = $this->db->sql_fetchrow($result);
								$this->db->sql_freeresult($result);

								// Check the group if non-founder
								if ($check_row && ($this->user->data['user_type'] == USER_FOUNDER || $check_row['group_founder_manage'] == 0))
								{
									// From the mysql documentation:
									// Prior to MySQL 4.0.14, the target table of the INSERT statement cannot appear in the FROM clause of the SELECT part of the query. This limitation is lifted in 4.0.14.
									// Due to this we stay on the safe side if we do the insertion "the manual way"

									// Copy permisisons from/to the acl groups table (only group_id gets changed)
									$sql = 'SELECT forum_id, auth_option_id, auth_role_id, auth_setting
										FROM ' . ACL_GROUPS_TABLE . '
										WHERE group_id = ' . $group_perm_from;
									$result = $this->db->sql_query($sql);

									$groups_sql_ary = [];
									while ($row = $this->db->sql_fetchrow($result))
									{
										$groups_sql_ary[] = [
											'group_id'			=> (int) $group_id,
											'forum_id'			=> (int) $row['forum_id'],
											'auth_option_id'	=> (int) $row['auth_option_id'],
											'auth_role_id'		=> (int) $row['auth_role_id'],
											'auth_setting'		=> (int) $row['auth_setting'],
										];
									}
									$this->db->sql_freeresult($result);

									// Now insert the data
									$this->db->sql_multi_insert(ACL_GROUPS_TABLE, $groups_sql_ary);

									$this->auth->acl_clear_prefetch();
								}
							}

							$this->cache->destroy('sql', [GROUPS_TABLE, TEAMPAGE_TABLE]);

							$message = ($action == 'edit') ? 'GROUP_UPDATED' : 'GROUP_CREATED';
							trigger_error($this->language->lang($message) . adm_back_link($this->u_action));
						}
					}

					if (count($error))
					{
						$error = array_map([&$user, 'lang'], $error);
						$group_rank = $submit_ary['rank'];

						$group_desc_data = [
							'text'			=> $group_desc,
							'allow_bbcode'	=> $allow_desc_bbcode,
							'allow_smilies'	=> $allow_desc_smilies,
							'allow_urls'	=> $allow_desc_urls,
						];
					}
				}
				else if (!$group_id)
				{
					$group_name = $this->request->variable('group_name', '', true);
					$group_desc_data = [
						'text'			=> '',
						'allow_bbcode'	=> true,
						'allow_smilies'	=> true,
						'allow_urls'	=> true,
					];
					$group_rank = 0;
					$group_type = GROUP_OPEN;
				}
				else
				{
					$group_name = $group_row['group_name'];
					$group_desc_data = generate_text_for_edit($group_row['group_desc'], $group_row['group_desc_uid'], $group_row['group_desc_options']);
					$group_type = $group_row['group_type'];
					$group_rank = $group_row['group_rank'];
				}

				$sql = 'SELECT *
					FROM ' . RANKS_TABLE . '
					WHERE rank_special = 1
					ORDER BY rank_title';
				$result = $this->db->sql_query($sql);

				$rank_options = '<option value="0"' . ((!$group_rank) ? ' selected="selected"' : '') . '>' . $this->language->lang('USER_DEFAULT') . '</option>';

				while ($row = $this->db->sql_fetchrow($result))
				{
					$selected = ($group_rank && $row['rank_id'] == $group_rank) ? ' selected="selected"' : '';
					$rank_options .= '<option value="' . $row['rank_id'] . '"' . $selected . '>' . $row['rank_title'] . '</option>';
				}
				$this->db->sql_freeresult($result);

				$type_free		= ($group_type == GROUP_FREE) ? ' checked="checked"' : '';
				$type_open		= ($group_type == GROUP_OPEN) ? ' checked="checked"' : '';
				$type_closed	= ($group_type == GROUP_CLOSED) ? ' checked="checked"' : '';
				$type_hidden	= ($group_type == GROUP_HIDDEN) ? ' checked="checked"' : '';

				// Load up stuff for avatars
				if ($this->config['allow_avatar'])
				{
					$avatars_enabled = false;
					$selected_driver = $this->avatar_manager->clean_driver_name($this->request->variable('avatar_driver', $avatar_data['avatar_type']));

					// Assign min and max values before generating avatar driver html
					$this->template->assign_vars([
							'AVATAR_MIN_WIDTH'		=> $this->config['avatar_min_width'],
							'AVATAR_MAX_WIDTH'		=> $this->config['avatar_max_width'],
							'AVATAR_MIN_HEIGHT'		=> $this->config['avatar_min_height'],
							'AVATAR_MAX_HEIGHT'		=> $this->config['avatar_max_height'],
					]);

					foreach ($avatar_drivers as $current_driver)
					{
						$driver = $this->avatar_manager->get_driver($current_driver);

						$avatars_enabled = true;
						$this->template->set_filenames([
							'avatar' => $driver->get_acp_template_name(),
						]);

						if ($driver->prepare_form($request, $template, $user, $avatar_data, $avatar_error))
						{
							$driver_name = $this->avatar_manager->prepare_driver_name($current_driver);
							$driver_upper = strtoupper($driver_name);
							$this->template->assign_block_vars('avatar_drivers', [
								'L_TITLE' => $this->language->lang($driver_upper . '_TITLE'),
								'L_EXPLAIN' => $this->language->lang($driver_upper . '_EXPLAIN'),

								'DRIVER' => $driver_name,
								'SELECTED' => $current_driver == $selected_driver,
								'OUTPUT' => $this->template->assign_display('avatar'),
							]);
						}
					}
				}

				$avatar = phpbb_get_group_avatar($group_row, 'GROUP_AVATAR', true);

				if (isset($phpbb_avatar_manager) && !$update)
				{
					// Merge any avatar errors into the primary error array
					$error = array_merge($error, $this->avatar_manager->localize_errors($user, $avatar_error));
				}

				$back_link = $this->request->variable('back_link', '');

				switch ($back_link)
				{
					case 'acp_users_groups':
						$u_back = append_sid("{$this->admin_path}index.$this->php_ext", 'i=users&amp;mode=groups&amp;u=' . $this->request->variable('u', 0));
					break;

					default:
						$u_back = $this->u_action;
					break;
				}

				$this->template->assign_vars([
					'S_EDIT'			=> true,
					'S_ADD_GROUP'		=> ($action == 'add') ? true : false,
					'S_GROUP_PERM'		=> ($action == 'add' && $this->auth->acl_get('a_authgroups') && $this->auth->acl_gets('a_aauth', 'a_fauth', 'a_mauth', 'a_uauth')) ? true : false,
					'S_INCLUDE_SWATCH'	=> true,
					'S_ERROR'			=> (count($error)) ? true : false,
					'S_SPECIAL_GROUP'	=> ($group_type == GROUP_SPECIAL) ? true : false,
					'S_USER_FOUNDER'	=> ($this->user->data['user_type'] == USER_FOUNDER) ? true : false,
					'S_AVATARS_ENABLED'		=> ($this->config['allow_avatar'] && $avatars_enabled),

					'ERROR_MSG'				=> (count($error)) ? implode('<br />', $error) : '',
					'GROUP_NAME'			=> $this->group_helper->get_name($group_name),
					'GROUP_INTERNAL_NAME'	=> $group_name,
					'GROUP_DESC'			=> $group_desc_data['text'],
					'GROUP_RECEIVE_PM'		=> (isset($group_row['group_receive_pm']) && $group_row['group_receive_pm']) ? ' checked="checked"' : '',
					'GROUP_FOUNDER_MANAGE'	=> (isset($group_row['group_founder_manage']) && $group_row['group_founder_manage']) ? ' checked="checked"' : '',
					'GROUP_LEGEND'			=> (isset($group_row['group_legend']) && $group_row['group_legend']) ? ' checked="checked"' : '',
					'GROUP_TEAMPAGE'		=> (isset($group_row['group_teampage']) && $group_row['group_teampage']) ? ' checked="checked"' : '',
					'GROUP_MESSAGE_LIMIT'	=> (isset($group_row['group_message_limit'])) ? $group_row['group_message_limit'] : 0,
					'GROUP_MAX_RECIPIENTS'	=> (isset($group_row['group_max_recipients'])) ? $group_row['group_max_recipients'] : 0,
					'GROUP_COLOUR'			=> (isset($group_row['group_colour'])) ? $group_row['group_colour'] : '',
					'GROUP_SKIP_AUTH'		=> (!empty($group_row['group_skip_auth'])) ? ' checked="checked"' : '',

					'S_DESC_BBCODE_CHECKED'	=> $group_desc_data['allow_bbcode'],
					'S_DESC_URLS_CHECKED'	=> $group_desc_data['allow_urls'],
					'S_DESC_SMILIES_CHECKED'=> $group_desc_data['allow_smilies'],

					'S_RANK_OPTIONS'		=> $rank_options,
					'S_GROUP_OPTIONS'		=> group_select_options(false, false, (($this->user->data['user_type'] == USER_FOUNDER) ? false : 0)),
					'AVATAR'				=> empty($avatar) ? '<img src="' . $this->admin_path . 'images/no_avatar.gif" alt="" />' : $avatar,
					'AVATAR_MAX_FILESIZE'	=> $this->config['avatar_filesize'],
					'AVATAR_WIDTH'			=> (isset($group_row['group_avatar_width'])) ? $group_row['group_avatar_width'] : '',
					'AVATAR_HEIGHT'			=> (isset($group_row['group_avatar_height'])) ? $group_row['group_avatar_height'] : '',

					'GROUP_TYPE_FREE'		=> GROUP_FREE,
					'GROUP_TYPE_OPEN'		=> GROUP_OPEN,
					'GROUP_TYPE_CLOSED'		=> GROUP_CLOSED,
					'GROUP_TYPE_HIDDEN'		=> GROUP_HIDDEN,
					'GROUP_TYPE_SPECIAL'	=> GROUP_SPECIAL,

					'GROUP_FREE'		=> $type_free,
					'GROUP_OPEN'		=> $type_open,
					'GROUP_CLOSED'		=> $type_closed,
					'GROUP_HIDDEN'		=> $type_hidden,

					'U_BACK'			=> $u_back,
					'U_ACTION'			=> "{$this->u_action}&amp;action=$action&amp;g=$group_id",
					'L_AVATAR_EXPLAIN'	=> phpbb_avatar_explanation_string(),
				]);

				/**
				 * Modify group template data before we display the form
				 *
				 * @event core.acp_manage_group_display_form
				 * @var	string	action				Type of the action: add|edit
				 * @var	bool	update				Do we display the form only
				 *							or did the user press submit
				 * @var	int		group_id			The group id
				 * @var	array	group_row			Array with new group data
				 * @var	string	group_name			The group name
				 * @var	int		group_type			The group type
				 * @var	array	group_desc_data		The group description data
				 * @var	string	group_rank			The group rank
				 * @var	string	rank_options		The rank options
				 * @var	array	error				Array of errors, if you add errors
				 *							ensure to update the template variables
				 *							S_ERROR and ERROR_MSG to display it
				 * @since 3.1.0-b5
				 */
				$vars = [
					'action',
					'update',
					'group_id',
					'group_row',
					'group_desc_data',
					'group_name',
					'group_type',
					'group_rank',
					'rank_options',
					'error',
				];
				extract($this->dispatcher->trigger_event('core.acp_manage_group_display_form', compact($vars)));

				return;
			break;

			case 'list':

				if (!$group_id)
				{
					trigger_error($this->language->lang('NO_GROUP') . adm_back_link($this->u_action), E_USER_WARNING);
				}

				/* @var $pagination \phpbb\pagination */
				$pagination = $phpbb_container->get('pagination');
				$this->page_title = 'GROUP_MEMBERS';

				// Grab the leaders - always, on every page...
				$sql = 'SELECT u.user_id, u.username, u.username_clean, u.user_regdate, u.user_colour, u.user_posts, u.group_id, ug.group_leader, ug.user_pending
					FROM ' . USERS_TABLE . ' u, ' . USER_GROUP_TABLE . " ug
					WHERE ug.group_id = $group_id
						AND u.user_id = ug.user_id
						AND ug.group_leader = 1
					ORDER BY ug.group_leader DESC, ug.user_pending ASC, u.username_clean";
				$result = $this->db->sql_query($sql);

				while ($row = $this->db->sql_fetchrow($result))
				{
					$this->template->assign_block_vars('leader', [
						'U_USER_EDIT'		=> append_sid("{$this->admin_path}index.$this->php_ext", "i=users&amp;action=edit&amp;u={$row['user_id']}"),

						'USERNAME'			=> $row['username'],
						'USERNAME_COLOUR'	=> $row['user_colour'],
						'S_GROUP_DEFAULT'	=> ($row['group_id'] == $group_id) ? true : false,
						'JOINED'			=> ($row['user_regdate']) ? $this->user->format_date($row['user_regdate']) : ' - ',
						'USER_POSTS'		=> $row['user_posts'],
						'USER_ID'			=> $row['user_id'],
					]);
				}
				$this->db->sql_freeresult($result);

				// Total number of group members (non-leaders)
				$sql = 'SELECT COUNT(user_id) AS total_members
					FROM ' . USER_GROUP_TABLE . "
					WHERE group_id = $group_id
						AND group_leader = 0";
				$result = $this->db->sql_query($sql);
				$total_members = (int) $this->db->sql_fetchfield('total_members');
				$this->db->sql_freeresult($result);

				$s_action_options = '';
				$options = ['default' => 'DEFAULT', 'approve' => 'APPROVE', 'demote' => 'DEMOTE', 'promote' => 'PROMOTE', 'deleteusers' => 'DELETE'];

				foreach ($options as $option => $lang)
				{
					$s_action_options .= '<option value="' . $option . '">' . $this->language->lang('GROUP_' . $lang) . '</option>';
				}

				$base_url = $this->u_action . "&amp;action=$action&amp;g=$group_id";
				$this->pagination->generate_template_pagination($base_url, 'pagination', 'start', $total_members, $this->config['topics_per_page'], $start);

				$this->template->assign_vars([
					'S_LIST'			=> true,
					'S_GROUP_SPECIAL'	=> ($group_row['group_type'] == GROUP_SPECIAL) ? true : false,
					'S_ACTION_OPTIONS'	=> $s_action_options,

					'GROUP_NAME'	=> $this->group_helper->get_name($group_row['group_name']),

					'U_ACTION'			=> $this->u_action . "&amp;g=$group_id",
					'U_BACK'			=> $this->u_action,
					'U_FIND_USERNAME'	=> append_sid("{$this->root_path}memberlist.$this->php_ext", 'mode=searchuser&amp;form=list&amp;field=usernames'),
					'U_DEFAULT_ALL'		=> "{$this->u_action}&amp;action=set_default_on_all&amp;g=$group_id",
				]);

				// Grab the members
				$sql = 'SELECT u.user_id, u.username, u.username_clean, u.user_colour, u.user_regdate, u.user_posts, u.group_id, ug.group_leader, ug.user_pending
					FROM ' . USERS_TABLE . ' u, ' . USER_GROUP_TABLE . " ug
					WHERE ug.group_id = $group_id
						AND u.user_id = ug.user_id
						AND ug.group_leader = 0
					ORDER BY ug.group_leader DESC, ug.user_pending ASC, u.username_clean";
				$result = $this->db->sql_query_limit($sql, $this->config['topics_per_page'], $start);

				$pending = false;

				while ($row = $this->db->sql_fetchrow($result))
				{
					if ($row['user_pending'] && !$pending)
					{
						$this->template->assign_block_vars('member', [
							'S_PENDING'		=> true]
						);

						$pending = true;
					}

					$this->template->assign_block_vars('member', [
						'U_USER_EDIT'		=> append_sid("{$this->admin_path}index.$this->php_ext", "i=users&amp;action=edit&amp;u={$row['user_id']}"),

						'USERNAME'			=> $row['username'],
						'USERNAME_COLOUR'	=> $row['user_colour'],
						'S_GROUP_DEFAULT'	=> ($row['group_id'] == $group_id) ? true : false,
						'JOINED'			=> ($row['user_regdate']) ? $this->user->format_date($row['user_regdate']) : ' - ',
						'USER_POSTS'		=> $row['user_posts'],
						'USER_ID'			=> $row['user_id']]
					);
				}
				$this->db->sql_freeresult($result);

				return;
			break;
		}

		$this->template->assign_vars([
			'U_ACTION'		=> $this->u_action,
			'S_GROUP_ADD'	=> ($this->auth->acl_get('a_groupadd')) ? true : false]
		);

		// Get us all the groups
		$sql = 'SELECT g.group_id, g.group_name, g.group_type, g.group_colour
			FROM ' . GROUPS_TABLE . ' g
			ORDER BY g.group_type ASC, g.group_name';
		$result = $this->db->sql_query($sql);

		$lookup = $cached_group_data = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$type = ($row['group_type'] == GROUP_SPECIAL) ? 'special' : 'normal';

			// used to determine what type a group is
			$lookup[$row['group_id']] = $type;

			// used for easy access to the data within a group
			$cached_group_data[$type][$row['group_id']] = $row;
			$cached_group_data[$type][$row['group_id']]['total_members'] = 0;
			$cached_group_data[$type][$row['group_id']]['pending_members'] = 0;
		}
		$this->db->sql_freeresult($result);

		// How many people are in which group?
		$sql = 'SELECT COUNT(ug.user_id) AS total_members, SUM(ug.user_pending) AS pending_members, ug.group_id
			FROM ' . USER_GROUP_TABLE . ' ug
			WHERE ' . $this->db->sql_in_set('ug.group_id', array_keys($lookup)) . '
			GROUP BY ug.group_id';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$type = $lookup[$row['group_id']];
			$cached_group_data[$type][$row['group_id']]['total_members'] = $row['total_members'];
			$cached_group_data[$type][$row['group_id']]['pending_members'] = $row['pending_members'];
		}
		$this->db->sql_freeresult($result);

		// The order is... normal, then special
		ksort($cached_group_data);

		foreach ($cached_group_data as $type => $row_ary)
		{
			if ($type == 'special')
			{
				$this->template->assign_block_vars('groups', [
					'S_SPECIAL'			=> true]
				);
			}

			foreach ($row_ary as $group_id => $row)
			{
				$group_name = (!empty($this->language->lang['G_' . $row['group_name']]))? $this->language->lang['G_' . $row['group_name']] : $row['group_name'];

				$this->template->assign_block_vars('groups', [
					'U_LIST'		=> "{$this->u_action}&amp;action=list&amp;g=$group_id",
					'U_EDIT'		=> "{$this->u_action}&amp;action=edit&amp;g=$group_id",
					'U_DELETE'		=> ($this->auth->acl_get('a_groupdel')) ? "{$this->u_action}&amp;action=delete&amp;g=$group_id" : '',

					'S_GROUP_SPECIAL'	=> ($row['group_type'] == GROUP_SPECIAL) ? true : false,

					'GROUP_NAME'	=> $group_name,
					'GROUP_COLOR'	=> $row['group_colour'],
					'TOTAL_MEMBERS'	=> $row['total_members'],
					'PENDING_MEMBERS' => $row['pending_members'],
				]);
			}
		}
	}

	public function manage_position()
	{
		$this->tpl_name = 'acp_groups_position';
		$this->page_title = 'ACP_GROUPS_POSITION';

		$field = $this->request->variable('field', '');
		$action = $this->request->variable('action', '');
		$group_id = $this->request->variable('g', 0);
		$teampage_id = $this->request->variable('t', 0);
		$category_id = $this->request->variable('c', 0);

		/** @var \phpbb\group\helper $group_helper */
		$group_helper = $phpbb_container->get('group_helper');

		if ($field && !in_array($field, ['legend', 'teampage']))
		{
			// Invalid mode
			trigger_error($this->language->lang('NO_MODE') . adm_back_link($this->u_action), E_USER_WARNING);
		}
		else if ($field && in_array($field, ['legend', 'teampage']))
		{
			/* @var $group_position \phpbb\groupposition\groupposition_interface */
			$group_position = $phpbb_container->get('groupposition.' . $field);
		}

		if ($field == 'teampage')
		{
			try
			{
				switch ($action)
				{
					case 'add':
						$group_position->add_group_teampage($group_id, $category_id);
					break;

					case 'add_category':
						$group_position->add_category_teampage($this->request->variable('category_name', '', true));
					break;

					case 'delete':
						$group_position->delete_teampage($teampage_id);
					break;

					case 'move_up':
						$group_position->move_up_teampage($teampage_id);
					break;

					case 'move_down':
						$group_position->move_down_teampage($teampage_id);
					break;
				}
			}
			catch (\phpbb\groupposition\exception $exception)
			{
				trigger_error($this->language->lang($exception->getMessage()) . adm_back_link($this->u_action), E_USER_WARNING);
			}
		}
		else if ($field == 'legend')
		{
			try
			{
				switch ($action)
				{
					case 'add':
						$group_position->add_group($group_id);
					break;

					case 'delete':
						$group_position->delete_group($group_id);
					break;

					case 'move_up':
						$group_position->move_up($group_id);
					break;

					case 'move_down':
						$group_position->move_down($group_id);
					break;
				}
			}
			catch (\phpbb\groupposition\exception $exception)
			{
				trigger_error($this->language->lang($exception->getMessage()) . adm_back_link($this->u_action), E_USER_WARNING);
			}
		}
		else
		{
			switch ($action)
			{
				case 'set_config_teampage':
					$this->config->set('teampage_forums', $this->request->variable('teampage_forums', 0));
					$this->config->set('teampage_memberships', $this->request->variable('teampage_memberships', 0));
					trigger_error($this->language->lang('CONFIG_UPDATED') . adm_back_link($this->u_action));
				break;

				case 'set_config_legend':
					$this->config->set('legend_sort_groupname', $this->request->variable('legend_sort_groupname', 0));
					trigger_error($this->language->lang('CONFIG_UPDATED') . adm_back_link($this->u_action));
				break;
			}
		}

		if (($action == 'move_up' || $action == 'move_down') && $this->request->is_ajax())
		{
			$json_response = new \phpbb\json_response;
			$json_response->send(['success' => true]);
		}

		$sql = 'SELECT group_id, group_name, group_colour, group_type, group_legend
			FROM ' . GROUPS_TABLE . '
			ORDER BY group_legend ASC, group_type DESC, group_name ASC';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$group_name = $this->group_helper->get_name($row['group_name']);
			if ($row['group_legend'])
			{
				$this->template->assign_block_vars('legend', [
					'GROUP_NAME'	=> $group_name,
					'GROUP_COLOUR'	=> ($row['group_colour']) ? '#' . $row['group_colour'] : '',
					'GROUP_TYPE'	=> $this->language->lang[\phpbb\groupposition\legend::group_type_language($row['group_type'])],

					'U_MOVE_DOWN'	=> "{$this->u_action}&amp;field=legend&amp;action=move_down&amp;g=" . $row['group_id'],
					'U_MOVE_UP'		=> "{$this->u_action}&amp;field=legend&amp;action=move_up&amp;g=" . $row['group_id'],
					'U_DELETE'		=> "{$this->u_action}&amp;field=legend&amp;action=delete&amp;g=" . $row['group_id'],
				]);
			}
			else
			{
				$this->template->assign_block_vars('add_legend', [
					'GROUP_ID'		=> (int) $row['group_id'],
					'GROUP_NAME'	=> $group_name,
					'GROUP_SPECIAL'	=> ($row['group_type'] == GROUP_SPECIAL),
				]);
			}
		}
		$this->db->sql_freeresult($result);

		$category_url_param = (($category_id) ? '&amp;c=' . $category_id : '');

		$sql = 'SELECT t.*, g.group_name, g.group_colour, g.group_type
			FROM ' . TEAMPAGE_TABLE . ' t
			LEFT JOIN ' . GROUPS_TABLE . ' g
				ON (t.group_id = g.group_id)
			WHERE t.teampage_parent = ' . $category_id . '
				OR t.teampage_id = ' . $category_id . '
			ORDER BY t.teampage_position ASC';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			if ($row['teampage_id'] == $category_id)
			{
				$this->template->assign_vars([
					'CURRENT_CATEGORY_NAME'		=> $row['teampage_name'],
				]);
				continue;
			}

			if ($row['group_id'])
			{
				$group_name = $this->group_helper->get_name($row['group_name']);
				$group_type = $this->language->lang[\phpbb\groupposition\teampage::group_type_language($row['group_type'])];
			}
			else
			{
				$group_name = $row['teampage_name'];
				$group_type = '';
			}

			$this->template->assign_block_vars('teampage', [
				'GROUP_NAME'	=> $group_name,
				'GROUP_COLOUR'	=> ($row['group_colour']) ? '#' . $row['group_colour'] : '',
				'GROUP_TYPE'	=> $group_type,

				'U_CATEGORY'	=> (!$row['group_id']) ? "{$this->u_action}&amp;c=" . $row['teampage_id'] : '',
				'U_MOVE_DOWN'	=> "{$this->u_action}&amp;field=teampage&amp;action=move_down{$category_url_param}&amp;t=" . $row['teampage_id'],
				'U_MOVE_UP'		=> "{$this->u_action}&amp;field=teampage&amp;action=move_up{$category_url_param}&amp;t=" . $row['teampage_id'],
				'U_DELETE'		=> "{$this->u_action}&amp;field=teampage&amp;action=delete{$category_url_param}&amp;t=" . $row['teampage_id'],
			]);
		}
		$this->db->sql_freeresult($result);

		$sql = 'SELECT g.group_id, g.group_name, g.group_colour, g.group_type
			FROM ' . GROUPS_TABLE . ' g
			LEFT JOIN ' . TEAMPAGE_TABLE . ' t
				ON (t.group_id = g.group_id)
			WHERE t.teampage_id IS NULL
			ORDER BY g.group_type DESC, g.group_name ASC';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$group_name = $this->group_helper->get_name($row['group_name']);
			$this->template->assign_block_vars('add_teampage', [
				'GROUP_ID'		=> (int) $row['group_id'],
				'GROUP_NAME'	=> $group_name,
				'GROUP_SPECIAL'	=> ($row['group_type'] == GROUP_SPECIAL),
			]);
		}
		$this->db->sql_freeresult($result);

		$this->template->assign_vars([
			'U_ACTION'					=> $this->u_action,
			'U_ACTION_LEGEND'			=> $this->u_action . '&amp;field=legend',
			'U_ACTION_TEAMPAGE'			=> $this->u_action . '&amp;field=teampage' . $category_url_param,
			'U_ACTION_TEAMPAGE_CAT'		=> $this->u_action . '&amp;field=teampage_cat',

			'S_TEAMPAGE_CATEGORY'		=> $category_id,
			'DISPLAY_FORUMS'			=> ($this->config['teampage_forums']) ? true : false,
			'DISPLAY_MEMBERSHIPS'		=> $this->config['teampage_memberships'],
			'LEGEND_SORT_GROUPNAME'		=> ($this->config['legend_sort_groupname']) ? true : false,
		]);
	}
}
