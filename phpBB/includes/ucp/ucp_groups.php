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

/**
* ucp_groups
*/
class ucp_groups
{
	var $u_action;

	function main($id, $mode)
	{
		global $config, $phpbb_root_path, $phpEx, $phpbb_admin_path;
		global $db, $user, $auth, $cache, $template;
		global $request, $phpbb_container, $phpbb_log;

		$user->add_lang('groups');

		$return_page = '<br /><br />' . sprintf($user->lang['RETURN_PAGE'], '<a href="' . $this->u_action . '">', '</a>');

		$mark_ary	= $request->variable('mark', array(0));
		$submit		= $request->variable('submit', false, false, \phpbb\request\request_interface::POST);

		/** @var \phpbb\group\helper $group_helper */
		$group_helper = $phpbb_container->get('group_helper');

		switch ($mode)
		{
			case 'membership':

				$this->page_title = 'UCP_USERGROUPS_MEMBER';

				if ($submit || isset($_POST['change_default']))
				{
					$action = (isset($_POST['change_default'])) ? 'change_default' : $request->variable('action', '');
					$group_id = ($action == 'change_default') ? $request->variable('default', 0) : $request->variable('selected', 0);

					if (!$group_id)
					{
						trigger_error('NO_GROUP_SELECTED');
					}

					$sql = 'SELECT group_id, group_name, group_type
						FROM ' . GROUPS_TABLE . "
						WHERE group_id IN ($group_id, {$user->data['group_id']})";
					$result = $db->sql_query($sql);

					$group_row = array();
					while ($row = $db->sql_fetchrow($result))
					{
						$row['group_name'] = $group_helper->get_name($row['group_name']);
						$group_row[$row['group_id']] = $row;
					}
					$db->sql_freeresult($result);

					if (!sizeof($group_row))
					{
						trigger_error('GROUP_NOT_EXIST');
					}

					switch ($action)
					{
						case 'change_default':
							// User already having this group set as default?
							if ($group_id == $user->data['group_id'])
							{
								trigger_error($user->lang['ALREADY_DEFAULT_GROUP'] . $return_page);
							}

							if (!$auth->acl_get('u_chggrp'))
							{
								send_status_line(403, 'Forbidden');
								trigger_error($user->lang['NOT_AUTHORISED'] . $return_page);
							}

							// User needs to be member of the group in order to make it default
							if (!group_memberships($group_id, $user->data['user_id'], true))
							{
								trigger_error($user->lang['NOT_MEMBER_OF_GROUP'] . $return_page);
							}

							if (confirm_box(true))
							{
								group_user_attributes('default', $group_id, $user->data['user_id']);

								$phpbb_log->add('user', $user->data['user_id'], $user->ip, 'LOG_USER_GROUP_CHANGE', false, array(
									'reportee_id' => $user->data['user_id'],
									sprintf($user->lang['USER_GROUP_CHANGE'], $group_row[$user->data['group_id']]['group_name'], $group_row[$group_id]['group_name'])
								));

								meta_refresh(3, $this->u_action);
								trigger_error($user->lang['CHANGED_DEFAULT_GROUP'] . $return_page);
							}
							else
							{
								$s_hidden_fields = array(
									'default'		=> $group_id,
									'change_default'=> true
								);

								confirm_box(false, sprintf($user->lang['GROUP_CHANGE_DEFAULT'], $group_row[$group_id]['group_name']), build_hidden_fields($s_hidden_fields));
							}

						break;

						case 'resign':

							// User tries to resign from default group but is not allowed to change it?
							if ($group_id == $user->data['group_id'] && !$auth->acl_get('u_chggrp'))
							{
								trigger_error($user->lang['NOT_RESIGN_FROM_DEFAULT_GROUP'] . $return_page);
							}

							if (!($row = group_memberships($group_id, $user->data['user_id'])))
							{
								trigger_error($user->lang['NOT_MEMBER_OF_GROUP'] . $return_page);
							}
							list(, $row) = each($row);

							$sql = 'SELECT group_type
								FROM ' . GROUPS_TABLE . '
								WHERE group_id = ' . $group_id;
							$result = $db->sql_query($sql);
							$group_type = (int) $db->sql_fetchfield('group_type');
							$db->sql_freeresult($result);

							if ($group_type != GROUP_OPEN && $group_type != GROUP_FREE)
							{
								trigger_error($user->lang['CANNOT_RESIGN_GROUP'] . $return_page);
							}

							if (confirm_box(true))
							{
								group_user_del($group_id, $user->data['user_id']);

								$phpbb_log->add('user', $user->data['user_id'], $user->ip, 'LOG_USER_GROUP_RESIGN', false, array(
									'reportee_id' => $user->data['user_id'],
									$group_row[$group_id]['group_name']
								));

								meta_refresh(3, $this->u_action);
								trigger_error($user->lang[($row['user_pending']) ? 'GROUP_RESIGNED_PENDING' : 'GROUP_RESIGNED_MEMBERSHIP'] . $return_page);
							}
							else
							{
								$s_hidden_fields = array(
									'selected'		=> $group_id,
									'action'		=> 'resign',
									'submit'		=> true
								);

								confirm_box(false, ($row['user_pending']) ? 'GROUP_RESIGN_PENDING' : 'GROUP_RESIGN_MEMBERSHIP', build_hidden_fields($s_hidden_fields));
							}

						break;

						case 'join':

							$sql = 'SELECT ug.*, u.username, u.username_clean, u.user_email
								FROM ' . USER_GROUP_TABLE . ' ug, ' . USERS_TABLE . ' u
								WHERE ug.user_id = u.user_id
									AND ug.group_id = ' . $group_id . '
									AND ug.user_id = ' . $user->data['user_id'];
							$result = $db->sql_query($sql);
							$row = $db->sql_fetchrow($result);
							$db->sql_freeresult($result);

							if ($row)
							{
								if ($row['user_pending'])
								{
									trigger_error($user->lang['ALREADY_IN_GROUP_PENDING'] . $return_page);
								}

								trigger_error($user->lang['ALREADY_IN_GROUP'] . $return_page);
							}

							// Check permission to join (open group or request)
							if ($group_row[$group_id]['group_type'] != GROUP_OPEN && $group_row[$group_id]['group_type'] != GROUP_FREE)
							{
								trigger_error($user->lang['CANNOT_JOIN_GROUP'] . $return_page);
							}

							if (confirm_box(true))
							{
								if ($group_row[$group_id]['group_type'] == GROUP_FREE)
								{
									group_user_add($group_id, $user->data['user_id']);
								}
								else
								{
									group_user_add($group_id, $user->data['user_id'], false, false, false, 0, 1);
								}

								$phpbb_log->add('user', $user->data['user_id'], $user->ip, 'LOG_USER_GROUP_JOIN' . (($group_row[$group_id]['group_type'] == GROUP_FREE) ? '' : '_PENDING'), false, array(
									'reportee_id' => $user->data['user_id'],
									$group_row[$group_id]['group_name']
								));

								meta_refresh(3, $this->u_action);
								trigger_error($user->lang[($group_row[$group_id]['group_type'] == GROUP_FREE) ? 'GROUP_JOINED' : 'GROUP_JOINED_PENDING'] . $return_page);
							}
							else
							{
								$s_hidden_fields = array(
									'selected'		=> $group_id,
									'action'		=> 'join',
									'submit'		=> true
								);

								confirm_box(false, ($group_row[$group_id]['group_type'] == GROUP_FREE) ? 'GROUP_JOIN' : 'GROUP_JOIN_PENDING', build_hidden_fields($s_hidden_fields));
							}

						break;

						case 'demote':

							if (!($row = group_memberships($group_id, $user->data['user_id'])))
							{
								trigger_error($user->lang['NOT_MEMBER_OF_GROUP'] . $return_page);
							}
							list(, $row) = each($row);

							if (!$row['group_leader'])
							{
								trigger_error($user->lang['NOT_LEADER_OF_GROUP'] . $return_page);
							}

							if (confirm_box(true))
							{
								group_user_attributes('demote', $group_id, $user->data['user_id']);

								$phpbb_log->add('user', $user->data['user_id'], $user->ip, 'LOG_USER_GROUP_DEMOTE', false, array(
									'reportee_id' => $user->data['user_id'],
									$group_row[$group_id]['group_name']
								));

								meta_refresh(3, $this->u_action);
								trigger_error($user->lang['USER_GROUP_DEMOTED'] . $return_page);
							}
							else
							{
								$s_hidden_fields = array(
									'selected'		=> $group_id,
									'action'		=> 'demote',
									'submit'		=> true
								);

								confirm_box(false, 'USER_GROUP_DEMOTE', build_hidden_fields($s_hidden_fields));
							}

						break;
					}
				}

				$sql = 'SELECT g.*, ug.group_leader, ug.user_pending
					FROM ' . GROUPS_TABLE . ' g, ' . USER_GROUP_TABLE . ' ug
					WHERE ug.user_id = ' . $user->data['user_id'] . '
						AND g.group_id = ug.group_id
					ORDER BY g.group_type DESC, g.group_name';
				$result = $db->sql_query($sql);

				$group_id_ary = array();
				$leader_count = $member_count = $pending_count = 0;
				while ($row = $db->sql_fetchrow($result))
				{
					$block = ($row['group_leader']) ? 'leader' : (($row['user_pending']) ? 'pending' : 'member');

					switch ($row['group_type'])
					{
						case GROUP_OPEN:
							$group_status = 'OPEN';
						break;

						case GROUP_CLOSED:
							$group_status = 'CLOSED';
						break;

						case GROUP_HIDDEN:
							$group_status = 'HIDDEN';
						break;

						case GROUP_SPECIAL:
							$group_status = 'SPECIAL';
						break;

						case GROUP_FREE:
							$group_status = 'FREE';
						break;
					}

					$template->assign_block_vars($block, array(
						'GROUP_ID'		=> $row['group_id'],
						'GROUP_NAME'	=> $group_helper->get_name($row['group_name']),
						'GROUP_DESC'	=> ($row['group_type'] <> GROUP_SPECIAL) ? generate_text_for_display($row['group_desc'], $row['group_desc_uid'], $row['group_desc_bitfield'], $row['group_desc_options']) : $user->lang['GROUP_IS_SPECIAL'],
						'GROUP_SPECIAL'	=> ($row['group_type'] <> GROUP_SPECIAL) ? false : true,
						'GROUP_STATUS'	=> $user->lang['GROUP_IS_' . $group_status],
						'GROUP_COLOUR'	=> $row['group_colour'],

						'U_VIEW_GROUP'	=> append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=group&amp;g=' . $row['group_id']),

						'S_GROUP_DEFAULT'	=> ($row['group_id'] == $user->data['group_id']) ? true : false,
						'S_ROW_COUNT'		=> ${$block . '_count'}++)
					);

					$group_id_ary[] = (int) $row['group_id'];
				}
				$db->sql_freeresult($result);

				// Hide hidden groups unless user is an admin with group privileges
				$sql_and = ($auth->acl_gets('a_group', 'a_groupadd', 'a_groupdel')) ? '<> ' . GROUP_SPECIAL : 'NOT IN (' . GROUP_SPECIAL . ', ' . GROUP_HIDDEN . ')';

				$sql = 'SELECT group_id, group_name, group_colour, group_desc, group_desc_uid, group_desc_bitfield, group_desc_options, group_type, group_founder_manage
					FROM ' . GROUPS_TABLE . '
					WHERE ' . ((sizeof($group_id_ary)) ? $db->sql_in_set('group_id', $group_id_ary, true) . ' AND ' : '') . "
						group_type $sql_and
					ORDER BY group_type DESC, group_name";
				$result = $db->sql_query($sql);

				$nonmember_count = 0;
				while ($row = $db->sql_fetchrow($result))
				{
					switch ($row['group_type'])
					{
						case GROUP_OPEN:
							$group_status = 'OPEN';
						break;

						case GROUP_CLOSED:
							$group_status = 'CLOSED';
						break;

						case GROUP_HIDDEN:
							$group_status = 'HIDDEN';
						break;

						case GROUP_SPECIAL:
							$group_status = 'SPECIAL';
						break;

						case GROUP_FREE:
							$group_status = 'FREE';
						break;
					}

					$template->assign_block_vars('nonmember', array(
						'GROUP_ID'		=> $row['group_id'],
						'GROUP_NAME'	=> $group_helper->get_name($row['group_name']),
						'GROUP_DESC'	=> ($row['group_type'] <> GROUP_SPECIAL) ? generate_text_for_display($row['group_desc'], $row['group_desc_uid'], $row['group_desc_bitfield'], $row['group_desc_options']) : $user->lang['GROUP_IS_SPECIAL'],
						'GROUP_SPECIAL'	=> ($row['group_type'] <> GROUP_SPECIAL) ? false : true,
						'GROUP_CLOSED'	=> ($row['group_type'] <> GROUP_CLOSED || $auth->acl_gets('a_group', 'a_groupadd', 'a_groupdel')) ? false : true,
						'GROUP_STATUS'	=> $user->lang['GROUP_IS_' . $group_status],
						'S_CAN_JOIN'	=> ($row['group_type'] == GROUP_OPEN || $row['group_type'] == GROUP_FREE) ? true : false,
						'GROUP_COLOUR'	=> $row['group_colour'],

						'U_VIEW_GROUP'	=> append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=group&amp;g=' . $row['group_id']),

						'S_ROW_COUNT'	=> $nonmember_count++)
					);
				}
				$db->sql_freeresult($result);

				$template->assign_vars(array(
					'S_CHANGE_DEFAULT'	=> ($auth->acl_get('u_chggrp')) ? true : false,
					'S_LEADER_COUNT'	=> $leader_count,
					'S_MEMBER_COUNT'	=> $member_count,
					'S_PENDING_COUNT'	=> $pending_count,
					'S_NONMEMBER_COUNT'	=> $nonmember_count,

					'S_UCP_ACTION'			=> $this->u_action)
				);

			break;

			case 'manage':

				$this->page_title = 'UCP_USERGROUPS_MANAGE';
				$action		= (isset($_POST['addusers'])) ? 'addusers' : $request->variable('action', '');
				$group_id	= $request->variable('g', 0);

				include($phpbb_root_path . 'includes/functions_display.' . $phpEx);

				add_form_key('ucp_groups');

				if ($group_id)
				{
					$sql = 'SELECT g.*, t.teampage_position AS group_teampage
						FROM ' . GROUPS_TABLE . ' g
						LEFT JOIN ' . TEAMPAGE_TABLE . ' t
							ON (t.group_id = g.group_id)
						WHERE g.group_id = ' . $group_id;
					$result = $db->sql_query($sql);
					$group_row = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);

					if (!$group_row)
					{
						trigger_error($user->lang['NO_GROUP'] . $return_page);
					}

					// Check if the user is allowed to manage this group if set to founder only.
					if ($user->data['user_type'] != USER_FOUNDER && $group_row['group_founder_manage'])
					{
						trigger_error($user->lang['NOT_ALLOWED_MANAGE_GROUP'] . $return_page, E_USER_WARNING);
					}

					$group_name = $group_row['group_name'];
					$group_type = $group_row['group_type'];

					$avatar = phpbb_get_group_avatar($group_row, 'GROUP_AVATAR', true);

					$template->assign_vars(array(
						'GROUP_NAME'			=> $group_helper->get_name($group_name),
						'GROUP_INTERNAL_NAME'	=> $group_name,
						'GROUP_COLOUR'			=> (isset($group_row['group_colour'])) ? $group_row['group_colour'] : '',
						'GROUP_DESC_DISP'		=> generate_text_for_display($group_row['group_desc'], $group_row['group_desc_uid'], $group_row['group_desc_bitfield'], $group_row['group_desc_options']),
						'GROUP_TYPE'			=> $group_row['group_type'],

						'AVATAR'				=> (empty($avatar) ? '<img src="' . $phpbb_admin_path . 'images/no_avatar.gif" alt="" />' : $avatar),
						'AVATAR_IMAGE'			=> (empty($avatar) ? '<img src="' . $phpbb_admin_path . 'images/no_avatar.gif" alt="" />' : $avatar),
						'AVATAR_WIDTH'			=> (isset($group_row['group_avatar_width'])) ? $group_row['group_avatar_width'] : '',
						'AVATAR_HEIGHT'			=> (isset($group_row['group_avatar_height'])) ? $group_row['group_avatar_height'] : '',
					));
				}

				switch ($action)
				{
					case 'edit':

						if (!$group_id)
						{
							trigger_error($user->lang['NO_GROUP'] . $return_page);
						}

						if (!($row = group_memberships($group_id, $user->data['user_id'])))
						{
							trigger_error($user->lang['NOT_MEMBER_OF_GROUP'] . $return_page);
						}
						list(, $row) = each($row);

						if (!$row['group_leader'])
						{
							trigger_error($user->lang['NOT_LEADER_OF_GROUP'] . $return_page);
						}

						$user->add_lang(array('acp/groups', 'acp/common'));

						$update	= (isset($_POST['update'])) ? true : false;

						$error = array();

						// Setup avatar data for later
						$avatars_enabled = false;
						$avatar_drivers = null;
						$avatar_data = null;
						$avatar_error = array();

						/** @var \phpbb\avatar\manager $phpbb_avatar_manager */
						$phpbb_avatar_manager = $phpbb_container->get('avatar.manager');

						if ($config['allow_avatar'])
						{
							$avatar_drivers = $phpbb_avatar_manager->get_enabled_drivers();

							// This is normalised data, without the group_ prefix
							$avatar_data = \phpbb\avatar\manager::clean_row($group_row, 'group');
						}

						// Handle deletion of avatars
						if ($request->is_set_post('avatar_delete'))
						{
							if (confirm_box(true))
							{
								$phpbb_avatar_manager->handle_avatar_delete($db, $user, $avatar_data, GROUPS_TABLE, 'group_');
								$cache->destroy('sql', GROUPS_TABLE);

								$message = ($action == 'edit') ? 'GROUP_UPDATED' : 'GROUP_CREATED';
								trigger_error($user->lang[$message] . $return_page);
							}
							else
							{
								confirm_box(false, $user->lang('CONFIRM_AVATAR_DELETE'), build_hidden_fields(array(
										'avatar_delete'     => true,
										'i'                 => $id,
										'mode'              => $mode,
										'g'			        => $group_id,
										'action'            => $action))
								);
							}
						}

						// Did we submit?
						if ($update)
						{
							$group_name	= $request->variable('group_name', '', true);
							$group_desc = $request->variable('group_desc', '', true);
							$group_type	= $request->variable('group_type', GROUP_FREE);

							$allow_desc_bbcode	= $request->variable('desc_parse_bbcode', false);
							$allow_desc_urls	= $request->variable('desc_parse_urls', false);
							$allow_desc_smilies	= $request->variable('desc_parse_smilies', false);

							$submit_ary = array(
								'colour'		=> $request->variable('group_colour', ''),
								'rank'			=> $request->variable('group_rank', 0),
								'receive_pm'	=> isset($_REQUEST['group_receive_pm']) ? 1 : 0,
								'message_limit'	=> $request->variable('group_message_limit', 0),
								'max_recipients'=> $request->variable('group_max_recipients', 0),
								'legend'	=> $group_row['group_legend'],
								'teampage'	=> $group_row['group_teampage'],
							);

							if ($config['allow_avatar'])
							{
								// Handle avatar
								$driver_name = $phpbb_avatar_manager->clean_driver_name($request->variable('avatar_driver', ''));

								if (in_array($driver_name, $avatar_drivers) && !$request->is_set_post('avatar_delete'))
								{
									$driver = $phpbb_avatar_manager->get_driver($driver_name);
									$result = $driver->process_form($request, $template, $user, $avatar_data, $avatar_error);

									if ($result && empty($avatar_error))
									{
										$result['avatar_type'] = $driver_name;

										$submit_ary = array_merge($submit_ary, $result);
									}
								}

								// Merge any avatars errors into the primary error array
								$error = array_merge($error, $phpbb_avatar_manager->localize_errors($user, $avatar_error));
							}

							if (!check_form_key('ucp_groups'))
							{
								$error[] = $user->lang['FORM_INVALID'];
							}

							// Validate submitted colour value
							if ($colour_error = validate_data($submit_ary, array('colour'	=> array('hex_colour', true))))
							{
								// Replace "error" string with its real, localised form
								$error = array_merge($error, $colour_error);
							}

							if (!sizeof($error))
							{
								// Only set the rank, colour, etc. if it's changed or if we're adding a new
								// group. This prevents existing group members being updated if no changes
								// were made.
								// However there are some attributes that need to be set everytime,
								// otherwise the group gets removed from the feature.
								$set_attributes = array('legend', 'teampage');

								$group_attributes = array();
								$test_variables = array(
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
								);

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
									$cache->destroy('sql', GROUPS_TABLE);
									$cache->destroy('sql', TEAMPAGE_TABLE);

									$message = ($action == 'edit') ? 'GROUP_UPDATED' : 'GROUP_CREATED';
									trigger_error($user->lang[$message] . $return_page);
								}
							}

							if (sizeof($error))
							{
								$error = array_map(array(&$user, 'lang'), $error);
								$group_rank = $submit_ary['rank'];

								$group_desc_data = array(
									'text'			=> $group_desc,
									'allow_bbcode'	=> $allow_desc_bbcode,
									'allow_smilies'	=> $allow_desc_smilies,
									'allow_urls'	=> $allow_desc_urls
								);
							}
						}
						else if (!$group_id)
						{
							$group_desc_data = array(
								'text'			=> '',
								'allow_bbcode'	=> true,
								'allow_smilies'	=> true,
								'allow_urls'	=> true
							);
							$group_rank = 0;
							$group_type = GROUP_OPEN;
						}
						else
						{
							$group_desc_data = generate_text_for_edit($group_row['group_desc'], $group_row['group_desc_uid'], $group_row['group_desc_options']);
							$group_rank = $group_row['group_rank'];
						}

						$sql = 'SELECT *
							FROM ' . RANKS_TABLE . '
							WHERE rank_special = 1
							ORDER BY rank_title';
						$result = $db->sql_query($sql);

						$rank_options = '<option value="0"' . ((!$group_rank) ? ' selected="selected"' : '') . '>' . $user->lang['USER_DEFAULT'] . '</option>';
						while ($row = $db->sql_fetchrow($result))
						{
							$selected = ($group_rank && $row['rank_id'] == $group_rank) ? ' selected="selected"' : '';
							$rank_options .= '<option value="' . $row['rank_id'] . '"' . $selected . '>' . $row['rank_title'] . '</option>';
						}
						$db->sql_freeresult($result);

						$type_free		= ($group_type == GROUP_FREE) ? ' checked="checked"' : '';
						$type_open		= ($group_type == GROUP_OPEN) ? ' checked="checked"' : '';
						$type_closed	= ($group_type == GROUP_CLOSED) ? ' checked="checked"' : '';
						$type_hidden	= ($group_type == GROUP_HIDDEN) ? ' checked="checked"' : '';

						// Load up stuff for avatars
						if ($config['allow_avatar'])
						{
							$avatars_enabled = false;
							$selected_driver = $phpbb_avatar_manager->clean_driver_name($request->variable('avatar_driver', $avatar_data['avatar_type']));

							// Assign min and max values before generating avatar driver html
							$template->assign_vars(array(
									'AVATAR_MIN_WIDTH'		=> $config['avatar_min_width'],
									'AVATAR_MAX_WIDTH'		=> $config['avatar_max_width'],
									'AVATAR_MIN_HEIGHT'		=> $config['avatar_min_height'],
									'AVATAR_MAX_HEIGHT'		=> $config['avatar_max_height'],
							));

							foreach ($avatar_drivers as $current_driver)
							{
								$driver = $phpbb_avatar_manager->get_driver($current_driver);

								$avatars_enabled = true;
								$template->set_filenames(array(
									'avatar' => $driver->get_template_name(),
								));

								if ($driver->prepare_form($request, $template, $user, $avatar_data, $avatar_error))
								{
									$driver_name = $phpbb_avatar_manager->prepare_driver_name($current_driver);
									$driver_upper = strtoupper($driver_name);
									$template->assign_block_vars('avatar_drivers', array(
										'L_TITLE' => $user->lang($driver_upper . '_TITLE'),
										'L_EXPLAIN' => $user->lang($driver_upper . '_EXPLAIN'),

										'DRIVER' => $driver_name,
										'SELECTED' => $current_driver == $selected_driver,
										'OUTPUT' => $template->assign_display('avatar'),
									));
								}
							}
						}

						if (isset($phpbb_avatar_manager) && !$update)
						{
							// Merge any avatars errors into the primary error array
							$error = array_merge($error, $phpbb_avatar_manager->localize_errors($user, $avatar_error));
						}

						$template->assign_vars(array(
							'S_EDIT'			=> true,
							'S_INCLUDE_SWATCH'	=> true,
							'S_FORM_ENCTYPE'	=> ' enctype="multipart/form-data"',
							'S_ERROR'			=> (sizeof($error)) ? true : false,
							'S_SPECIAL_GROUP'	=> ($group_type == GROUP_SPECIAL) ? true : false,
							'S_AVATARS_ENABLED'	=> ($config['allow_avatar'] && $avatars_enabled),
							'S_GROUP_MANAGE'	=> true,

							'ERROR_MSG'				=> (sizeof($error)) ? implode('<br />', $error) : '',
							'GROUP_RECEIVE_PM'		=> (isset($group_row['group_receive_pm']) && $group_row['group_receive_pm']) ? ' checked="checked"' : '',
							'GROUP_MESSAGE_LIMIT'	=> (isset($group_row['group_message_limit'])) ? $group_row['group_message_limit'] : 0,
							'GROUP_MAX_RECIPIENTS'	=> (isset($group_row['group_max_recipients'])) ? $group_row['group_max_recipients'] : 0,

							'GROUP_DESC'			=> $group_desc_data['text'],
							'S_DESC_BBCODE_CHECKED'	=> $group_desc_data['allow_bbcode'],
							'S_DESC_URLS_CHECKED'	=> $group_desc_data['allow_urls'],
							'S_DESC_SMILIES_CHECKED'=> $group_desc_data['allow_smilies'],

							'S_RANK_OPTIONS'		=> $rank_options,

							'GROUP_TYPE_FREE'		=> GROUP_FREE,
							'GROUP_TYPE_OPEN'		=> GROUP_OPEN,
							'GROUP_TYPE_CLOSED'		=> GROUP_CLOSED,
							'GROUP_TYPE_HIDDEN'		=> GROUP_HIDDEN,
							'GROUP_TYPE_SPECIAL'	=> GROUP_SPECIAL,

							'GROUP_FREE'		=> $type_free,
							'GROUP_OPEN'		=> $type_open,
							'GROUP_CLOSED'		=> $type_closed,
							'GROUP_HIDDEN'		=> $type_hidden,

							'S_UCP_ACTION'		=> $this->u_action . "&amp;action=$action&amp;g=$group_id",
							'L_AVATAR_EXPLAIN'	=> phpbb_avatar_explanation_string(),
						));

					break;

					case 'list':

						if (!$group_id)
						{
							trigger_error($user->lang['NO_GROUP'] . $return_page);
						}

						if (!($row = group_memberships($group_id, $user->data['user_id'])))
						{
							trigger_error($user->lang['NOT_MEMBER_OF_GROUP'] . $return_page);
						}
						list(, $row) = each($row);

						if (!$row['group_leader'])
						{
							trigger_error($user->lang['NOT_LEADER_OF_GROUP'] . $return_page);
						}

						$user->add_lang(array('acp/groups', 'acp/common'));
						$start = $request->variable('start', 0);

						// Grab the leaders - always, on every page...
						$sql = 'SELECT u.user_id, u.username, u.username_clean, u.user_colour, u.user_regdate, u.user_posts, u.group_id, ug.group_leader, ug.user_pending
							FROM ' . USERS_TABLE . ' u, ' . USER_GROUP_TABLE . " ug
							WHERE ug.group_id = $group_id
								AND u.user_id = ug.user_id
								AND ug.group_leader = 1
							ORDER BY ug.user_pending DESC, u.username_clean";
						$result = $db->sql_query($sql);

						while ($row = $db->sql_fetchrow($result))
						{
							$template->assign_block_vars('leader', array(
								'USERNAME'			=> $row['username'],
								'USERNAME_COLOUR'	=> $row['user_colour'],
								'USERNAME_FULL'		=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
								'U_USER_VIEW'		=> get_username_string('profile', $row['user_id'], $row['username']),
								'S_GROUP_DEFAULT'	=> ($row['group_id'] == $group_id) ? true : false,
								'JOINED'			=> ($row['user_regdate']) ? $user->format_date($row['user_regdate']) : ' - ',
								'USER_POSTS'		=> $row['user_posts'],
								'USER_ID'			=> $row['user_id'])
							);
						}
						$db->sql_freeresult($result);

						// Total number of group members (non-leaders)
						$sql = 'SELECT COUNT(user_id) AS total_members
							FROM ' . USER_GROUP_TABLE . "
							WHERE group_id = $group_id
								AND group_leader = 0";
						$result = $db->sql_query($sql);
						$total_members = (int) $db->sql_fetchfield('total_members');
						$db->sql_freeresult($result);

						// Grab the members
						$sql = 'SELECT u.user_id, u.username, u.username_clean, u.user_colour, u.user_regdate, u.user_posts, u.group_id, ug.group_leader, ug.user_pending
							FROM ' . USERS_TABLE . ' u, ' . USER_GROUP_TABLE . " ug
							WHERE ug.group_id = $group_id
								AND u.user_id = ug.user_id
								AND ug.group_leader = 0
							ORDER BY ug.user_pending DESC, u.username_clean";
						$result = $db->sql_query_limit($sql, $config['topics_per_page'], $start);

						$pending = false;
						$approved = false;

						while ($row = $db->sql_fetchrow($result))
						{
							if ($row['user_pending'] && !$pending)
							{
								$template->assign_block_vars('member', array(
									'S_PENDING'		=> true)
								);
								$template->assign_var('S_PENDING_SET', true);

								$pending = true;
							}
							else if (!$row['user_pending'] && !$approved)
							{
								$template->assign_block_vars('member', array(
									'S_APPROVED'		=> true)
								);
								$template->assign_var('S_APPROVED_SET', true);

								$approved = true;
							}

							$template->assign_block_vars('member', array(
								'USERNAME'			=> $row['username'],
								'USERNAME_COLOUR'	=> $row['user_colour'],
								'USERNAME_FULL'		=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
								'U_USER_VIEW'		=> get_username_string('profile', $row['user_id'], $row['username']),
								'S_GROUP_DEFAULT'	=> ($row['group_id'] == $group_id) ? true : false,
								'JOINED'			=> ($row['user_regdate']) ? $user->format_date($row['user_regdate']) : ' - ',
								'USER_POSTS'		=> $row['user_posts'],
								'USER_ID'			=> $row['user_id'])
							);
						}
						$db->sql_freeresult($result);

						$s_action_options = '';
						$options = array('default' => 'DEFAULT', 'approve' => 'APPROVE', 'deleteusers' => 'DELETE');

						foreach ($options as $option => $lang)
						{
							$s_action_options .= '<option value="' . $option . '">' . $user->lang['GROUP_' . $lang] . '</option>';
						}

						/* @var $pagination \phpbb\pagination */
						$pagination = $phpbb_container->get('pagination');
						$base_url = $this->u_action . "&amp;action=$action&amp;g=$group_id";
						$start = $pagination->validate_start($start, $config['topics_per_page'], $total_members);
						$pagination->generate_template_pagination($base_url, 'pagination', 'start', $total_members, $config['topics_per_page'], $start);

						$template->assign_vars(array(
							'S_LIST'			=> true,
							'S_ACTION_OPTIONS'	=> $s_action_options,

							'U_ACTION'			=> $this->u_action . "&amp;g=$group_id",
							'S_UCP_ACTION'		=> $this->u_action . "&amp;g=$group_id",
							'U_FIND_USERNAME'	=> append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=searchuser&amp;form=ucp&amp;field=usernames'),
						));

					break;

					case 'approve':

						if (!$group_id)
						{
							trigger_error($user->lang['NO_GROUP'] . $return_page);
						}

						if (!($row = group_memberships($group_id, $user->data['user_id'])))
						{
							trigger_error($user->lang['NOT_MEMBER_OF_GROUP'] . $return_page);
						}
						list(, $row) = each($row);

						if (!$row['group_leader'])
						{
							trigger_error($user->lang['NOT_LEADER_OF_GROUP'] . $return_page);
						}

						$user->add_lang('acp/groups');

						// Approve, demote or promote
						group_user_attributes('approve', $group_id, $mark_ary, false, false);

						trigger_error($user->lang['USERS_APPROVED'] . '<br /><br />' . sprintf($user->lang['RETURN_PAGE'], '<a href="' . $this->u_action . '&amp;action=list&amp;g=' . $group_id . '">', '</a>'));

					break;

					case 'default':

						if (!$group_id)
						{
							trigger_error($user->lang['NO_GROUP'] . $return_page);
						}

						if (!($row = group_memberships($group_id, $user->data['user_id'])))
						{
							trigger_error($user->lang['NOT_MEMBER_OF_GROUP'] . $return_page);
						}
						list(, $row) = each($row);

						if (!$row['group_leader'])
						{
							trigger_error($user->lang['NOT_LEADER_OF_GROUP'] . $return_page);
						}

						$group_row['group_name'] = $group_helper->get_name($group_row['group_name']);

						if (confirm_box(true))
						{
							if (!sizeof($mark_ary))
							{
								$start = 0;

								do
								{
									$sql = 'SELECT user_id
										FROM ' . USER_GROUP_TABLE . "
										WHERE group_id = $group_id
										ORDER BY user_id";
									$result = $db->sql_query_limit($sql, 200, $start);

									$mark_ary = array();
									if ($row = $db->sql_fetchrow($result))
									{
										do
										{
											$mark_ary[] = $row['user_id'];
										}
										while ($row = $db->sql_fetchrow($result));

										group_user_attributes('default', $group_id, $mark_ary, false, $group_row['group_name'], $group_row);

										$start = (sizeof($mark_ary) < 200) ? 0 : $start + 200;
									}
									else
									{
										$start = 0;
									}
									$db->sql_freeresult($result);
								}
								while ($start);
							}
							else
							{
								group_user_attributes('default', $group_id, $mark_ary, false, $group_row['group_name'], $group_row);
							}

							$user->add_lang('acp/groups');

							trigger_error($user->lang['GROUP_DEFS_UPDATED'] . '<br /><br />' . sprintf($user->lang['RETURN_PAGE'], '<a href="' . $this->u_action . '&amp;action=list&amp;g=' . $group_id . '">', '</a>'));
						}
						else
						{
							$user->add_lang('acp/common');

							confirm_box(false, $user->lang['CONFIRM_OPERATION'], build_hidden_fields(array(
								'mark'		=> $mark_ary,
								'g'			=> $group_id,
								'i'			=> $id,
								'mode'		=> $mode,
								'action'	=> $action))
							);
						}

						// redirect to last screen
						redirect($this->u_action . '&amp;action=list&amp;g=' . $group_id);

					break;

					case 'deleteusers':

						$user->add_lang(array('acp/groups', 'acp/common'));

						if (!($row = group_memberships($group_id, $user->data['user_id'])))
						{
							trigger_error($user->lang['NOT_MEMBER_OF_GROUP'] . $return_page);
						}
						list(, $row) = each($row);

						if (!$row['group_leader'])
						{
							trigger_error($user->lang['NOT_LEADER_OF_GROUP'] . $return_page);
						}

						$group_row['group_name'] = $group_helper->get_name($group_row['group_name']);

						if (confirm_box(true))
						{
							if (!$group_id)
							{
								trigger_error($user->lang['NO_GROUP'] . $return_page);
							}

							$error = group_user_del($group_id, $mark_ary, false, $group_row['group_name']);

							if ($error)
							{
								trigger_error($user->lang[$error] . '<br /><br />' . sprintf($user->lang['RETURN_PAGE'], '<a href="' . $this->u_action . '&amp;action=list&amp;g=' . $group_id . '">', '</a>'));
							}

							trigger_error($user->lang['GROUP_USERS_REMOVE'] . '<br /><br />' . sprintf($user->lang['RETURN_PAGE'], '<a href="' . $this->u_action . '&amp;action=list&amp;g=' . $group_id . '">', '</a>'));
						}
						else
						{
							confirm_box(false, $user->lang['CONFIRM_OPERATION'], build_hidden_fields(array(
								'mark'		=> $mark_ary,
								'g'			=> $group_id,
								'i'			=> $id,
								'mode'		=> $mode,
								'action'	=> $action))
							);
						}

						// redirect to last screen
						redirect($this->u_action . '&amp;action=list&amp;g=' . $group_id);

					break;

					case 'addusers':

						$user->add_lang(array('acp/groups', 'acp/common'));

						$names = $request->variable('usernames', '', true);

						if (!$group_id)
						{
							trigger_error($user->lang['NO_GROUP'] . $return_page);
						}

						if (!$names)
						{
							trigger_error($user->lang['NO_USERS'] . $return_page);
						}

						if (!($row = group_memberships($group_id, $user->data['user_id'])))
						{
							trigger_error($user->lang['NOT_MEMBER_OF_GROUP'] . $return_page);
						}
						list(, $row) = each($row);

						if (!$row['group_leader'])
						{
							trigger_error($user->lang['NOT_LEADER_OF_GROUP'] . $return_page);
						}

						$name_ary = array_unique(explode("\n", $names));
						$group_name = $group_helper->get_name($group_row['group_name']);

						$default = $request->variable('default', 0);

						if (confirm_box(true))
						{
							// Add user/s to group
							if ($error = group_user_add($group_id, false, $name_ary, $group_name, $default, 0, 0, $group_row))
							{
								trigger_error($user->lang[$error] . $return_page);
							}

							trigger_error($user->lang['GROUP_USERS_ADDED'] . '<br /><br />' . sprintf($user->lang['RETURN_PAGE'], '<a href="' . $this->u_action . '&amp;action=list&amp;g=' . $group_id . '">', '</a>'));
						}
						else
						{
							$s_hidden_fields = array(
								'default'	=> $default,
								'usernames'	=> $names,
								'g'			=> $group_id,
								'i'			=> $id,
								'mode'		=> $mode,
								'action'	=> $action
							);

							confirm_box(false, $user->lang('GROUP_CONFIRM_ADD_USERS', sizeof($name_ary), implode($user->lang['COMMA_SEPARATOR'], $name_ary)), build_hidden_fields($s_hidden_fields));
						}

						trigger_error($user->lang['NO_USERS_ADDED'] . '<br /><br />' . sprintf($user->lang['RETURN_PAGE'], '<a href="' . $this->u_action . '&amp;action=list&amp;g=' . $group_id . '">', '</a>'));

					break;

					default:
						$user->add_lang('acp/common');

						$sql = 'SELECT g.group_id, g.group_name, g.group_colour, g.group_desc, g.group_desc_uid, g.group_desc_bitfield, g.group_desc_options, g.group_type, ug.group_leader
							FROM ' . GROUPS_TABLE . ' g, ' . USER_GROUP_TABLE . ' ug
							WHERE ug.user_id = ' . $user->data['user_id'] . '
								AND g.group_id = ug.group_id
								AND ug.group_leader = 1
							ORDER BY g.group_type DESC, g.group_name';
						$result = $db->sql_query($sql);

						while ($value = $db->sql_fetchrow($result))
						{
							$template->assign_block_vars('leader', array(
								'GROUP_NAME'	=> $group_helper->get_name($value['group_name']),
								'GROUP_DESC'	=> generate_text_for_display($value['group_desc'], $value['group_desc_uid'], $value['group_desc_bitfield'], $value['group_desc_options']),
								'GROUP_TYPE'	=> $value['group_type'],
								'GROUP_ID'		=> $value['group_id'],
								'GROUP_COLOUR'	=> $value['group_colour'],

								'U_LIST'	=> $this->u_action . "&amp;action=list&amp;g={$value['group_id']}",
								'U_EDIT'	=> $this->u_action . "&amp;action=edit&amp;g={$value['group_id']}")
							);
						}
						$db->sql_freeresult($result);

					break;
				}

			break;
		}

		$this->tpl_name = 'ucp_groups_' . $mode;
	}
}
