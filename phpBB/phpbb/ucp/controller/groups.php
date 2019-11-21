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

namespace phpbb\ucp\controller;

class groups
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\avatar\manager */
	protected $avatar_manager;

	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\group\helper */
	protected $group_helper;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \phpbb\pagination */
	protected $pagination;

	/** @var \phpbb\request\request */
	protected $request;

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

	/**
	 * Constructor.
	 *
	 * @param \phpbb\auth\auth						$auth			Auth object
	 * @param \phpbb\avatar\manager					$avatar_manager	Avatar manager object
	 * @param \phpbb\cache\driver\driver_interface	$cache			Cache object
	 * @param \phpbb\config\config					$config			Config object
	 * @param \phpbb\db\driver\driver_interface		$db				Database object
	 * @param \phpbb\group\helper					$group_helper	Group helper object
	 * @param \phpbb\controller\helper				$helper			Controller helper object
	 * @param \phpbb\language\language				$language		Language object
	 * @param \phpbb\log\log						$log			Log object
	 * @param \phpbb\pagination						$pagination		Pagination object
	 * @param \phpbb\request\request				$request		Request object
	 * @param \phpbb\template\template				$template		Template object
	 * @param \phpbb\user							$user			User object
	 * @param string								$admin_path		phpBB admin path
	 * @param string								$root_path		phpBB root path
	 * @param string								$php_ext		php File extension
	 * @param array									$tables			phpBB tables
	 */
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\avatar\manager $avatar_manager,
		\phpbb\cache\driver\driver_interface $cache,
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\group\helper $group_helper,
		\phpbb\controller\helper $helper,
		\phpbb\language\language $language,
		\phpbb\log\log $log,
		\phpbb\pagination $pagination,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		$admin_path,
		$root_path,
		$php_ext,
		$tables
	)
	{
		$this->auth				= $auth;
		$this->avatar_manager	= $avatar_manager;
		$this->cache			= $cache;
		$this->config			= $config;
		$this->db				= $db;
		$this->group_helper		= $group_helper;
		$this->helper			= $helper;
		$this->language			= $language;
		$this->log				= $log;
		$this->pagination		= $pagination;
		$this->request			= $request;
		$this->template			= $template;
		$this->user				= $user;

		$this->admin_path		= $admin_path;
		$this->root_path		= $root_path;
		$this->php_ext			= $php_ext;
		$this->tables			= $tables;
	}

	/**
	 * Display and handle the user groups page.
	 *
	 * @param string	$mode		The groups mode (membership|manage)
	 * @param string	$action		The groups action
	 * @param int		$g			The group identifier
	 * @param int		$page		The page number
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function main($mode, $action = 'list', $g = 0, $page = 1)
	{
		$this->language->add_lang('groups');

		$submit		= $this->request->is_set_post('submit');
		$mark_ary	= $this->request->variable('mark', [0]);

		$group_row	= [];

		$return_manage = '<br /><br />' . $this->language->lang('RETURN_PAGE', '<a href="' . $this->helper->route('ucp_groups_manage') . '">', '</a>');
		$return_member = '<br /><br />' . $this->language->lang('RETURN_PAGE', '<a href="' . $this->helper->route('ucp_groups_edit') . '">', '</a>');

		switch ($mode)
		{
			case 'membership':
				$default = $this->request->is_set_post('change_default');

				if ($submit || $default)
				{
					$action = $default ? 'change_default' : $action;
					$group_id = $action === 'change_default' ? $this->request->variable('default', 0) : $this->request->variable('selected', 0);

					if (!$group_id)
					{
						return trigger_error($this->language->lang('NO_GROUP_SELECTED') . $return_manage, E_USER_WARNING);
					}

					$sql = 'SELECT group_id, group_name, group_type
						FROM ' . $this->tables['groups'] . '
						WHERE ' . $this->db->sql_in_set('group_id', [$group_id, $this->user->data['group_id']]);
					$result = $this->db->sql_query($sql);
					while ($row = $this->db->sql_fetchrow($result))
					{
						$row['group_name'] = $this->group_helper->get_name($row['group_name']);
						$group_row[$row['group_id']] = $row;
					}
					$this->db->sql_freeresult($result);

					if (empty($group_row))
					{
						return trigger_error($this->language->lang('GROUP_NOT_EXIST') . $return_manage, E_USER_WARNING);
					}

					switch ($action)
					{
						case 'change_default':
							// User already having this group set as default?
							if ($group_id == $this->user->data['group_id'])
							{
								return trigger_error($this->language->lang('ALREADY_DEFAULT_GROUP') . $return_member, E_USER_WARNING);
							}

							if (!$this->auth->acl_get('u_chggrp'))
							{
								send_status_line(403, 'Forbidden');
								return trigger_error($this->language->lang('NOT_AUTHORISED') . $return_member, E_USER_WARNING);
							}

							// User needs to be member of the group in order to make it default
							if (!group_memberships($group_id, $this->user->data['user_id'], true))
							{
								return trigger_error($this->language->lang('NOT_MEMBER_OF_GROUP') . $return_member, E_USER_WARNING);
							}

							if (confirm_box(true))
							{
								group_user_attributes('default', $group_id, $this->user->data['user_id']);

								$this->log->add('user', $this->user->data['user_id'], $this->user->ip, 'LOG_USER_GROUP_CHANGE', false, [
									'reportee_id' => $this->user->data['user_id'],
									$this->language->lang('USER_GROUP_CHANGE', $group_row[$this->user->data['group_id']]['group_name'], $group_row[$group_id]['group_name']),
								]);

								$this->helper->assign_meta_refresh_var(3, $this->helper->route('ucp_groups_edit'));

								return $this->helper->message($this->language->lang('CHANGED_DEFAULT_GROUP') . $return_member);
							}
							else
							{
								confirm_box(false, $this->language->lang('GROUP_CHANGE_DEFAULT', $group_row[$group_id]['group_name']), build_hidden_fields([
									'default'			=> $group_id,
									'change_default'	=> true,
								]));

								return redirect($this->helper->route('ucp_groups_edit'));
							}
						break;

						case 'resign':
							// User tries to resign from default group but is not allowed to change it?
							if ($group_id == $this->user->data['group_id'] && !$this->auth->acl_get('u_chggrp'))
							{
								return trigger_error($this->language->lang('NOT_RESIGN_FROM_DEFAULT_GROUP') . $return_member, E_USER_WARNING);
							}

							if (!($row = group_memberships($group_id, $this->user->data['user_id'])))
							{
								return trigger_error($this->language->lang('NOT_MEMBER_OF_GROUP') . $return_manage, E_USER_WARNING);
							}

							$row = current($row);

							$sql = 'SELECT group_type
								FROM ' . $this->tables['groups'] . '
								WHERE group_id = ' . (int) $group_id;
							$result = $this->db->sql_query($sql);
							$group_type = (int) $this->db->sql_fetchfield('group_type');
							$this->db->sql_freeresult($result);

							if ($group_type != GROUP_OPEN && $group_type != GROUP_FREE)
							{
								return trigger_error($this->language->lang('CANNOT_RESIGN_GROUP') . $return_member, E_USER_WARNING);
							}

							if (confirm_box(true))
							{
								group_user_del($group_id, $this->user->data['user_id']);

								$this->log->add('user', $this->user->data['user_id'], $this->user->ip, 'LOG_USER_GROUP_RESIGN', false, [
									'reportee_id' => $this->user->data['user_id'],
									$group_row[$group_id]['group_name'],
								]);

								$this->helper->assign_meta_refresh_var(3, $this->helper->route('ucp_groups_edit'));

								$message = $row['user_pending'] ? 'GROUP_RESIGNED_PENDING' : 'GROUP_RESIGNED_MEMBERSHIP';

								return $this->helper->message($this->language->lang($message) . $return_member);
							}
							else
							{
								$message = $row['user_pending'] ? 'GROUP_RESIGN_PENDING' : 'GROUP_RESIGN_MEMBERSHIP';

								confirm_box(false, $message, build_hidden_fields([
									'selected'		=> $group_id,
									'action'		=> 'resign',
									'submit'		=> true,
								]));

								return redirect($this->helper->route('ucp_groups_edit'));
							}
						break;

						case 'join':
							$sql = 'SELECT ug.*, u.username, u.username_clean, u.user_email
								FROM ' . $this->tables['user_group'] . ' ug,
									' . $this->tables['users'] . ' u
								WHERE ug.user_id = u.user_id
									AND ug.group_id = ' . (int) $group_id . '
									AND ug.user_id = ' . (int) $this->user->data['user_id'];
							$result = $this->db->sql_query($sql);
							$row = $this->db->sql_fetchrow($result);
							$this->db->sql_freeresult($result);

							if ($row)
							{
								$l_pending = $row['user_pending'] ? '_PENDING' : '';

								return trigger_error($this->language->lang('ALREADY_IN_GROUP' . $l_pending) . $return_member);
							}

							// Check permission to join (open group or request)
							if ($group_row[$group_id]['group_type'] != GROUP_OPEN && $group_row[$group_id]['group_type'] != GROUP_FREE)
							{
								return trigger_error($this->language->lang('CANNOT_JOIN_GROUP') . $return_member);
							}

							$l_pending = $group_row[$group_id]['group_type'] != GROUP_FREE ? '_PENDING' : '';

							if (confirm_box(true))
							{
								if ($group_row[$group_id]['group_type'] == GROUP_FREE)
								{
									group_user_add($group_id, $this->user->data['user_id']);
								}
								else
								{
									group_user_add($group_id, $this->user->data['user_id'], false, false, false, 0, 1);
								}

								$this->log->add('user', $this->user->data['user_id'], $this->user->ip, 'LOG_USER_GROUP_JOIN' . $l_pending, false, [
									'reportee_id' => $this->user->data['user_id'],
									$group_row[$group_id]['group_name'],
								]);

								$this->helper->assign_meta_refresh_var(3, $this->helper->route('ucp_groups_edit'));

								return $this->helper->message($this->language->lang('GROUP_JOINED' . $l_pending) . $return_member);
							}
							else
							{
								confirm_box(false, 'GROUP_JOIN' . $l_pending, build_hidden_fields([
									'selected'		=> $group_id,
									'action'		=> 'join',
									'submit'		=> true,
								]));

								return redirect($this->helper->route('ucp_groups_edit'));
							}
						break;

						case 'demote':
							if (!($row = group_memberships($group_id, $this->user->data['user_id'])))
							{
								return trigger_error($this->language->lang('NOT_MEMBER_OF_GROUP') . $return_member, E_USER_WARNING);
							}

							$row = current($row);

							if (!$row['group_leader'])
							{
								return trigger_error($this->language->lang('NOT_LEADER_OF_GROUP') . $return_member, E_USER_WARNING);
							}

							if (confirm_box(true))
							{
								group_user_attributes('demote', $group_id, $this->user->data['user_id']);

								$this->log->add('user', $this->user->data['user_id'], $this->user->ip, 'LOG_USER_GROUP_DEMOTE', false, [
									'reportee_id' => $this->user->data['user_id'],
									$group_row[$group_id]['group_name'],
								]);

								$this->helper->assign_meta_refresh_var(3, $this->helper->route('ucp_groups_edit'));

								return $this->helper->message($this->language->lang('USER_GROUP_DEMOTED') . $return_member);
							}
							else
							{
								confirm_box(false, 'USER_GROUP_DEMOTE', build_hidden_fields([
									'selected'		=> $group_id,
									'action'		=> 'demote',
									'submit'		=> true,
								]));

								return redirect($this->helper->route('ucp_groups_edit'));
							}
						break;
					}
				}

				$group_id_ary = [];
				$leader_count = $member_count = $pending_count = 0;

				$sql = 'SELECT g.*, ug.group_leader, ug.user_pending
					FROM ' . $this->tables['groups'] . ' g,
						' . $this->tables['user_group'] . ' ug
					WHERE ug.user_id = ' . (int) $this->user->data['user_id'] . '
						AND g.group_id = ug.group_id
					ORDER BY g.group_type DESC, g.group_name';
				$result = $this->db->sql_query($sql);
				while ($row = $this->db->sql_fetchrow($result))
				{
					$block = $row['group_leader'] ? 'leader' : ($row['user_pending'] ? 'pending' : 'member');

					$this->template->assign_block_vars($block, [
						'GROUP_ID'			=> $row['group_id'],
						'GROUP_NAME'		=> $this->group_helper->get_name($row['group_name']),
						'GROUP_DESC'		=> $row['group_type'] <> GROUP_SPECIAL ? generate_text_for_display($row['group_desc'], $row['group_desc_uid'], $row['group_desc_bitfield'], $row['group_desc_options']) : $this->language->lang('GROUP_IS_SPECIAL'),
						'GROUP_COLOUR'		=> $row['group_colour'],

						'GROUP_SPECIAL'		=> $row['group_type'] == GROUP_SPECIAL,
						'GROUP_STATUS'		=> $this->language->lang('GROUP_IS_' . $this->get_constant_string($row['group_type'])),

						'S_GROUP_DEFAULT'	=> $row['group_id'] == $this->user->data['group_id'],
						'S_ROW_COUNT'		=> ${$block . '_count'}++,

						'U_VIEW_GROUP'		=> append_sid("{$this->root_path}memberlist.$this->php_ext", 'mode=group&amp;g=' . $row['group_id']),
					]);

					$group_id_ary[] = (int) $row['group_id'];
				}
				$this->db->sql_freeresult($result);

				$nonmember_count = 0;

				// Hide hidden groups unless user is an admin with group privileges
				$sql_and = $this->auth->acl_gets('a_group', 'a_groupadd', 'a_groupdel') ? '<> ' . GROUP_SPECIAL : 'NOT IN (' . GROUP_SPECIAL . ', ' . GROUP_HIDDEN . ')';

				$sql = 'SELECT group_id, group_name, group_colour, group_desc, group_desc_uid,
								group_desc_bitfield, group_desc_options, group_type, group_founder_manage
					FROM ' . $this->tables['groups'] . '
					WHERE ' . (!empty($group_id_ary) ? $this->db->sql_in_set('group_id', $group_id_ary, true) . ' AND ' : '') . "
						group_type $sql_and
					ORDER BY group_type DESC, group_name";
				$result = $this->db->sql_query($sql);
				while ($row = $this->db->sql_fetchrow($result))
				{
					$this->template->assign_block_vars('nonmember', [
						'GROUP_ID'		=> $row['group_id'],
						'GROUP_NAME'	=> $this->group_helper->get_name($row['group_name']),
						'GROUP_DESC'	=> $row['group_type'] <> GROUP_SPECIAL ? generate_text_for_display($row['group_desc'], $row['group_desc_uid'], $row['group_desc_bitfield'], $row['group_desc_options']) : $this->language->lang('GROUP_IS_SPECIAL'),
						'GROUP_COLOUR'	=> $row['group_colour'],

						'GROUP_CLOSED'	=> $row['group_type'] <> GROUP_CLOSED || $this->auth->acl_gets('a_group', 'a_groupadd', 'a_groupdel') ? false : true,
						'GROUP_SPECIAL'	=> $row['group_type'] == GROUP_SPECIAL,
						'GROUP_STATUS'	=> $this->language->lang('GROUP_IS_' . $this->get_constant_string($row['group_type'])),

						'S_CAN_JOIN'	=> $row['group_type'] == GROUP_OPEN || $row['group_type'] == GROUP_FREE,
						'S_ROW_COUNT'	=> $nonmember_count++,

						'U_VIEW_GROUP'	=> append_sid("{$this->root_path}memberlist.$this->php_ext", 'mode=group&amp;g=' . $row['group_id']),
					]);
				}
				$this->db->sql_freeresult($result);

				$this->template->assign_vars([
					'S_CHANGE_DEFAULT'	=> (bool) $this->auth->acl_get('u_chggrp'),
					'S_LEADER_COUNT'	=> $leader_count,
					'S_MEMBER_COUNT'	=> $member_count,
					'S_PENDING_COUNT'	=> $pending_count,
					'S_NONMEMBER_COUNT'	=> $nonmember_count,

					'S_UCP_ACTION'		=> $this->helper->route('ucp_groups_edit'),
				]);
			break;

			case 'manage':
				$action		= $this->request->is_set_post('addusers') ? 'addusers' : $action;
				$group_id	= (int) $g;

				$form_key = 'ucp_groups';
				add_form_key($form_key);

				if ($group_id)
				{
					$sql = 'SELECT g.*, t.teampage_position AS group_teampage
						FROM ' . $this->tables['groups'] . ' g
						LEFT JOIN ' . $this->tables['teampage'] . ' t
							ON (t.group_id = g.group_id)
						WHERE g.group_id = ' . (int) $group_id;
					$result = $this->db->sql_query($sql);
					$group_row = $this->db->sql_fetchrow($result);
					$this->db->sql_freeresult($result);

					if ($group_row === false)
					{
						return trigger_error($this->language->lang('NO_GROUP') . $return_manage, E_USER_WARNING);
					}

					// Check if the user is allowed to manage this group if set to founder only.
					if ($this->user->data['user_type'] != USER_FOUNDER && $group_row['group_founder_manage'])
					{
						return trigger_error($this->language->lang('NOT_ALLOWED_MANAGE_GROUP') . $return_manage, E_USER_WARNING);
					}

					$group_type = $group_row['group_type'];

					$avatar = $this->group_helper->get_avatar($group_row, 'GROUP_AVATAR', true);

					$this->template->assign_vars([
						'GROUP_NAME'			=> $this->group_helper->get_name($group_row['group_name']),
						'GROUP_INTERNAL_NAME'	=> $group_row['group_name'],
						'GROUP_COLOUR'			=> isset($group_row['group_colour']) ? $group_row['group_colour'] : '',
						'GROUP_DESC_DISP'		=> generate_text_for_display($group_row['group_desc'], $group_row['group_desc_uid'], $group_row['group_desc_bitfield'], $group_row['group_desc_options']),
						'GROUP_TYPE'			=> $group_row['group_type'],

						'AVATAR'				=> empty($avatar) ? '<img class="avatar" src="' . $this->admin_path . 'images/no_avatar.gif" alt="" />' : $avatar,
						'AVATAR_IMAGE'			=> empty($avatar) ? '<img class="avatar" src="' . $this->admin_path . 'images/no_avatar.gif" alt="" />' : $avatar,
						'AVATAR_WIDTH'			=> isset($group_row['group_avatar_width']) ? $group_row['group_avatar_width'] : '',
						'AVATAR_HEIGHT'			=> isset($group_row['group_avatar_height']) ? $group_row['group_avatar_height'] : '',
					]);
				}

				switch ($action)
				{
					case 'edit':
						if (!$group_id)
						{
							return trigger_error($this->language->lang('NO_GROUP') . $return_manage, E_USER_WARNING);
						}

						if (!($row = group_memberships($group_id, $this->user->data['user_id'])))
						{
							return trigger_error($this->language->lang('NOT_MEMBER_OF_GROUP') . $return_manage, E_USER_WARNING);
						}

						$row = current($row);

						if (!$row['group_leader'])
						{
							return trigger_error($this->language->lang('NOT_LEADER_OF_GROUP') . $return_manage, E_USER_WARNING);
						}

						$this->language->add_lang(['acp/groups', 'acp/common']);

						$update	= $this->request->is_set_post('update');
						$errors = [];

						// Setup avatar data for later
						$avatars_enabled = false;
						$avatar_drivers = null;
						$avatar_data = null;
						$avatar_error = [];

						if ($this->config['allow_avatar'])
						{
							$avatar_drivers = $this->avatar_manager->get_enabled_drivers();

							// This is normalised data, without the group_ prefix
							$avatar_data = $this->avatar_manager->clean_row($group_row, 'group');
						}

						// Handle deletion of avatars
						if ($this->request->is_set_post('avatar_delete'))
						{
							if (confirm_box(true))
							{
								$this->avatar_manager->handle_avatar_delete($this->db, $this->user, $avatar_data, $this->tables['groups'], 'group_');
								$this->cache->destroy('sql', $this->tables['groups']);

								$message = $action === 'edit' ? 'GROUP_UPDATED' : 'GROUP_CREATED';

								return $this->helper->message($this->language->lang($message) . $return_manage);
							}
							else
							{
								confirm_box(false, $this->language->lang('CONFIRM_AVATAR_DELETE'), build_hidden_fields([
									'avatar_delete'	=> true,
									'action'		=> $action,
									'g'				=> $group_id,
								]));

								return redirect($this->helper->route('ucp_groups_manage'));
							}
						}

						// Did we submit?
						if ($update)
						{
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
								'message_limit'		=> $this->request->variable('group_message_limit', 0),
								'max_recipients'	=> $this->request->variable('group_max_recipients', 0),
								'legend'			=> $group_row['group_legend'],
								'teampage'			=> $group_row['group_teampage'],
							];

							$group_desc_data = [];

							if (!check_form_key($form_key))
							{
								$errors[] = $this->language->lang('FORM_INVALID');
							}

							if (empty($errors) && $this->config['allow_avatar'])
							{
								// Handle avatar
								$driver_name = $this->avatar_manager->clean_driver_name($this->request->variable('avatar_driver', ''));

								if (in_array($driver_name, $avatar_drivers) && !$this->request->is_set_post('avatar_delete'))
								{
									$driver = $this->avatar_manager->get_driver($driver_name);
									$result = $driver->process_form($this->request, $this->template, $this->user, $avatar_data, $avatar_error);

									if ($result && empty($avatar_error))
									{
										$result['avatar_type'] = $driver_name;

										$submit_ary = array_merge($submit_ary, $result);
									}
								}

								// Merge any avatars errors into the primary error array
								$errors = array_merge($errors, $this->avatar_manager->localize_errors($this->user, $avatar_error));
							}

							// Validate submitted colour value
							if ($colour_error = validate_data($submit_ary, ['colour' => ['hex_colour', true]]))
							{
								// Replace "error" string with its real, localised form
								$errors = array_merge($errors, $colour_error);
							}

							if (empty($errors))
							{
								/**
								 * Only set the rank, colour, etc. if it's changed or if we're adding a new group.
								 * This prevents existing group members being updated if no changed were made.
								 * However there are some attributes that need to be set every time,
								 * otherwise the group gets removed from the feature.
								 */
								$set_attributes = ['legend', 'teampage'];
								$group_attributes = [];
								$test_variables = [
									'rank'				=> 'int',
									'colour'			=> 'string',
									'avatar'			=> 'string',
									'avatar_type'		=> 'string',
									'avatar_width'		=> 'int',
									'avatar_height'		=> 'int',
									'receive_pm'		=> 'int',
									'legend'			=> 'int',
									'teampage'			=> 'int',
									'message_limit'		=> 'int',
									'max_recipients'	=> 'int',
								];

								foreach ($test_variables as $test => $type)
								{
									if (isset($submit_ary[$test]) && ($action === 'add' || $group_row['group_' . $test] != $submit_ary[$test] || isset($group_attributes['group_avatar']) && strpos($test, 'avatar') === 0 || in_array($test, $set_attributes)))
									{
										settype($submit_ary[$test], $type);
										$group_attributes['group_' . $test] = $group_row['group_' . $test] = $submit_ary[$test];
									}
								}

								$errors = group_create($group_id, $group_type, $group_name, $group_desc, $group_attributes, $allow_desc_bbcode, $allow_desc_urls, $allow_desc_smilies);

								if (empty($errors))
								{
									$this->cache->destroy('sql', $this->tables['groups']);
									$this->cache->destroy('sql', $this->tables['teampage']);

									$message = $action === 'edit' ? 'GROUP_UPDATED' : 'GROUP_CREATED';

									return $this->helper->message($this->language->lang($message) . $return_manage);
								}
							}

							if (!empty($errors))
							{
								$errors = array_map([$this->language, 'lang'], $errors);
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
							$group_rank = 0;
							$group_type = GROUP_OPEN;
							$group_desc_data = [
								'text'			=> '',
								'allow_bbcode'	=> true,
								'allow_smilies'	=> true,
								'allow_urls'	=> true,
							];
						}
						else
						{
							$group_desc_data = generate_text_for_edit($group_row['group_desc'], $group_row['group_desc_uid'], $group_row['group_desc_options']);
							$group_rank = $group_row['group_rank'];
							$group_type = !empty($group_type) ? $group_type : GROUP_OPEN;
						}

						$rank_options = '<option value="0"' . (empty($group_rank) ? ' selected="selected"' : '') . '>' . $this->language->lang('USER_DEFAULT') . '</option>';

						$sql = 'SELECT *
							FROM ' . $this->tables['ranks'] . '
							WHERE rank_special = 1
							ORDER BY rank_title';
						$result = $this->db->sql_query($sql);
						while ($row = $this->db->sql_fetchrow($result))
						{
							$selected = !empty($group_rank) && $row['rank_id'] == $group_rank ? ' selected="selected"' : '';
							$rank_options .= '<option value="' . $row['rank_id'] . '"' . $selected . '>' . $row['rank_title'] . '</option>';
						}
						$this->db->sql_freeresult($result);

						$type_free		= $group_type == GROUP_FREE ? ' checked="checked"' : '';
						$type_open		= $group_type == GROUP_OPEN ? ' checked="checked"' : '';
						$type_closed	= $group_type == GROUP_CLOSED ? ' checked="checked"' : '';
						$type_hidden	= $group_type == GROUP_HIDDEN ? ' checked="checked"' : '';

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
									'avatar' => $driver->get_template_name(),
								]);

								if ($driver->prepare_form($this->request, $this->template, $this->user, $avatar_data, $avatar_error))
								{
									$driver_name = $this->avatar_manager->prepare_driver_name($current_driver);
									$driver_upper = strtoupper($driver_name);

									$this->template->assign_block_vars('avatar_drivers', [
										'L_TITLE'	=> $this->language->lang($driver_upper . '_TITLE'),
										'L_EXPLAIN'	=> $this->language->lang($driver_upper . '_EXPLAIN'),

										'DRIVER'	=> $driver_name,
										'OUTPUT'	=> $this->template->assign_display('avatar'),
										'SELECTED'	=> $current_driver == $selected_driver,
									]);
								}
							}
						}

						if (!$update)
						{
							// Merge any avatars errors into the primary error array
							$errors = array_merge($errors, $this->avatar_manager->localize_errors($this->user, $avatar_error));
						}

						$s_errors = !empty($errors);

						$this->template->assign_vars([
							'ERROR_MSG'				=> $s_errors ? implode('<br />', $errors) : '',
							'GROUP_RECEIVE_PM'		=> isset($group_row['group_receive_pm']) && $group_row['group_receive_pm'] ? ' checked="checked"' : '',
							'GROUP_MESSAGE_LIMIT'	=> isset($group_row['group_message_limit']) ? $group_row['group_message_limit'] : 0,
							'GROUP_MAX_RECIPIENTS'	=> isset($group_row['group_max_recipients']) ? $group_row['group_max_recipients'] : 0,

							'GROUP_TYPE_FREE'		=> GROUP_FREE,
							'GROUP_TYPE_OPEN'		=> GROUP_OPEN,
							'GROUP_TYPE_CLOSED'		=> GROUP_CLOSED,
							'GROUP_TYPE_HIDDEN'		=> GROUP_HIDDEN,
							'GROUP_TYPE_SPECIAL'	=> GROUP_SPECIAL,

							'GROUP_FREE'			=> $type_free,
							'GROUP_OPEN'			=> $type_open,
							'GROUP_CLOSED'			=> $type_closed,
							'GROUP_HIDDEN'			=> $type_hidden,

							'GROUP_DESC'			=> $group_desc_data['text'],
							'S_DESC_BBCODE_CHECKED'	=> $group_desc_data['allow_bbcode'],
							'S_DESC_URLS_CHECKED'	=> $group_desc_data['allow_urls'],
							'S_DESC_SMILIES_CHECKED'=> $group_desc_data['allow_smilies'],

							'S_RANK_OPTIONS'		=> $rank_options,

							'L_AVATAR_EXPLAIN'		=> phpbb_avatar_explanation_string(),

							'S_AVATARS_ENABLED'		=> $this->config['allow_avatar'] && $avatars_enabled,
							'S_EDIT'				=> true,
							'S_ERROR'				=> $s_errors,
							'S_FORM_ENCTYPE'		=> ' enctype="multipart/form-data"',
							'S_GROUP_MANAGE'		=> true,
							'S_INCLUDE_SWATCH'		=> true,
							'S_SPECIAL_GROUP'		=> $group_type == GROUP_SPECIAL,

							'S_UCP_ACTION'			=> $this->helper->route('ucp_groups_manage', ['action' => $action, 'g' => $group_id]),
						]);
					break;

					case 'list':
						if (!$group_id)
						{
							return trigger_error($this->language->lang('NO_GROUP') . $return_manage, E_USER_WARNING);
						}

						if (!($row = group_memberships($group_id, $this->user->data['user_id'])))
						{
							return trigger_error($this->language->lang('NOT_MEMBER_OF_GROUP') . $return_manage, E_USER_WARNING);
						}

						$row = current($row);

						if (!$row['group_leader'])
						{
							return trigger_error($this->language->lang('NOT_LEADER_OF_GROUP') . $return_manage, E_USER_WARNING);
						}

						$this->language->add_lang(['acp/groups', 'acp/common']);

						$limit = (int) $this->config['topics_per_page'];
						$start = ($page - 1) * $limit;

						// Grab the leaders - always, on every page...
						$sql = 'SELECT u.user_id, u.username, u.username_clean, u.user_colour, u.user_regdate,
										u.user_posts, u.group_id, ug.group_leader, ug.user_pending
							FROM ' . $this->tables['users'] . ' u,
								' . $this->tables['user_group'] . " ug
							WHERE ug.group_id = $group_id
								AND u.user_id = ug.user_id
								AND ug.group_leader = 1
							ORDER BY ug.user_pending DESC, u.username_clean";
						$result = $this->db->sql_query($sql);
						while ($row = $this->db->sql_fetchrow($result))
						{
							$this->template->assign_block_vars('leader', [
								'JOINED'			=> $row['user_regdate'] ? $this->user->format_date($row['user_regdate']) : ' - ',
								'USER_ID'			=> $row['user_id'],
								'USER_POSTS'		=> $row['user_posts'],
								'USERNAME'			=> $row['username'],
								'USERNAME_COLOUR'	=> $row['user_colour'],
								'USERNAME_FULL'		=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),

								'S_GROUP_DEFAULT'	=> $row['group_id'] == $group_id,

								'U_USER_VIEW'		=> get_username_string('profile', $row['user_id'], $row['username']),
							]);
						}
						$this->db->sql_freeresult($result);

						// Total number of group members (non-leaders)
						$sql = 'SELECT COUNT(user_id) AS total_members
							FROM ' . $this->tables['user_group'] . '
							WHERE group_leader = 0
								AND group_id = ' . (int) $group_id;
						$result = $this->db->sql_query($sql);
						$total_members = (int) $this->db->sql_fetchfield('total_members');
						$this->db->sql_freeresult($result);

						$pending = false;
						$approved = false;

						// Grab the members
						$sql = 'SELECT u.user_id, u.username, u.username_clean, u.user_colour, u.user_regdate,
										u.user_posts, u.group_id, ug.group_leader, ug.user_pending
							FROM ' . $this->tables['users'] . ' u, ' . $this->tables['user_group'] . " ug
							WHERE ug.group_id = $group_id
								AND u.user_id = ug.user_id
								AND ug.group_leader = 0
							ORDER BY ug.user_pending DESC, u.username_clean";
						$result = $this->db->sql_query_limit($sql, $limit, $start);
						while ($row = $this->db->sql_fetchrow($result))
						{
							if ($row['user_pending'] && !$pending)
							{
								$pending = true;

								$this->template->assign_var('S_PENDING_SET', true);
								$this->template->assign_block_vars('member', ['S_PENDING' => true]);
							}
							else if (!$row['user_pending'] && !$approved)
							{
								$approved = true;

								$this->template->assign_var('S_APPROVED_SET', true);
								$this->template->assign_block_vars('member', ['S_APPROVED' => true]);
							}

							$this->template->assign_block_vars('member', [
								'JOINED'			=> $row['user_regdate'] ? $this->user->format_date($row['user_regdate']) : ' - ',
								'USER_ID'			=> $row['user_id'],
								'USER_POSTS'		=> $row['user_posts'],
								'USERNAME'			=> $row['username'],
								'USERNAME_COLOUR'	=> $row['user_colour'],
								'USERNAME_FULL'		=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),

								'S_GROUP_DEFAULT'	=> $row['group_id'] == $group_id,

								'U_USER_VIEW'		=> get_username_string('profile', $row['user_id'], $row['username']),
							]);
						}
						$this->db->sql_freeresult($result);

						$s_action_options = '';
						$options = ['default' => 'DEFAULT', 'approve' => 'APPROVE', 'deleteusers' => 'DELETE'];

						foreach ($options as $option => $lang)
						{
							$s_action_options .= '<option value="' . $option . '">' . $this->language->lang('GROUP_' . $lang) . '</option>';
						}

						$start = $this->pagination->validate_start($start, $this->config['topics_per_page'], $total_members);
						$this->pagination->generate_template_pagination([
							'routes' => ['ucp_groups_manage', 'ucp_groups_manage_pagination'],
							'params' => ['action' => $action, 'g' => $group_id],
						], 'pagination', 'page', $total_members, $this->config['topics_per_page'], $start);

						$this->template->assign_vars([
							'S_LIST'			=> true,
							'S_ACTION_OPTIONS'	=> $s_action_options,

							'U_ACTION'			=> $this->helper->route('ucp_groups_manage', ['action' => 'list', 'g' => $group_id]),
							'S_UCP_ACTION'		=> $this->helper->route('ucp_groups_manage', ['action' => 'list', 'g' => $group_id]),
							'U_FIND_USERNAME'	=> append_sid("{$this->root_path}memberlist.$this->php_ext", 'mode=searchuser&amp;form=ucp&amp;field=usernames'),
						]);
					break;

					case 'approve':
						if (!check_form_key($form_key))
						{
							trigger_error($this->language->lang('FORM_INVALID') . $return_manage);
						}

						if (!$group_id)
						{
							return trigger_error($this->language->lang('NO_GROUP') . $return_manage, E_USER_WARNING);
						}

						if (!($row = group_memberships($group_id, $this->user->data['user_id'])))
						{
							return trigger_error($this->language->lang('NOT_MEMBER_OF_GROUP') . $return_manage, E_USER_WARNING);
						}

						$row = current($row);

						if (!$row['group_leader'])
						{
							return trigger_error($this->language->lang('NOT_LEADER_OF_GROUP') . $return_manage, E_USER_WARNING);
						}

						$this->language->add_lang('acp/groups');

						// Approve, demote or promote
						group_user_attributes('approve', $group_id, $mark_ary, false, false);

						$u_return = $this->helper->route('ucp_groups_manage', ['action' => 'list', 'g' => $group_id]);
						$return = '<br /><br />' . $this->language->lang('RETURN_PAGE', '<a href="' . $u_return . '">', '</a>');

						return $this->helper->message($this->language->lang('USERS_APPROVED') . $return);
					break;

					case 'default':
						if (!$group_id)
						{
							return trigger_error($this->language->lang('NO_GROUP') . $return_manage, E_USER_WARNING);
						}

						if (!($row = group_memberships($group_id, $this->user->data['user_id'])))
						{
							return trigger_error($this->language->lang('NOT_MEMBER_OF_GROUP') . $return_manage, E_USER_WARNING);
						}

						$row = current($row);

						if (!$row['group_leader'])
						{
							return trigger_error($this->language->lang('NOT_LEADER_OF_GROUP') . $return_manage, E_USER_WARNING);
						}

						$group_row['group_name'] = $this->group_helper->get_name($group_row['group_name']);

						if (confirm_box(true))
						{
							if (empty($mark_ary))
							{
								$start = 0;
								$batch = 200;

								do
								{
									$mark_ary = [];

									$sql = 'SELECT user_id
										FROM ' . $this->tables['user_group'] . '
										WHERE group_id = ' . (int) $group_id . '
										ORDER BY user_id';
									$result = $this->db->sql_query_limit($sql, $batch, $start);
									if ($row = $this->db->sql_fetchrow($result))
									{
										do
										{
											$mark_ary[] = (int) $row['user_id'];
										}
										while ($row = $this->db->sql_fetchrow($result));

										group_user_attributes('default', $group_id, $mark_ary, false, $group_row['group_name'], $group_row);

										$start = count($mark_ary) < $batch ? 0 : $start + $batch;
									}
									else
									{
										$start = 0;
									}
									$this->db->sql_freeresult($result);
								}
								while ($start);
							}
							else
							{
								group_user_attributes('default', $group_id, $mark_ary, false, $group_row['group_name'], $group_row);
							}

							$this->language->add_lang('acp/groups');

							$u_return = $this->helper->route('ucp_groups_manage', ['action' => 'list', 'g' => $group_id]);
							$return = '<br /><br />' . $this->language->lang('RETURN_PAGE', '<a href="' . $u_return . '">', '</a>');

							return $this->helper->message($this->language->lang('GROUP_DEFS_UPDATED') . $return);
						}
						else
						{
							$this->language->add_lang('acp/common');

							confirm_box(false, $this->language->lang('CONFIRM_OPERATION'), build_hidden_fields([
								'action'	=> $action,
								'mark'		=> $mark_ary,
								'g'			=> $group_id,
							]));

							return redirect($this->helper->route('ucp_groups_manage', ['action' => 'list', 'g' => $group_id]));
						}
					break;

					case 'deleteusers':
						$this->language->add_lang(['acp/groups', 'acp/common']);

						if (!($row = group_memberships($group_id, $this->user->data['user_id'])))
						{
							return trigger_error($this->language->lang('NOT_MEMBER_OF_GROUP') . $return_manage, E_USER_WARNING);
						}

						$row = current($row);

						if (!$row['group_leader'])
						{
							return trigger_error($this->language->lang('NOT_LEADER_OF_GROUP') . $return_manage, E_USER_WARNING);
						}

						$group_row['group_name'] = $this->group_helper->get_name($group_row['group_name']);

						if (confirm_box(true))
						{
							if (!$group_id)
							{
								return trigger_error($this->language->lang('NO_GROUP') . $return_manage, E_USER_WARNING);
							}

							$u_return = $this->helper->route('ucp_groups_manage', ['action' => 'list', 'g' => $group_id]);
							$return = '<br /><br />' . $this->language->lang('RETURN_PAGE', '<a href="' . $u_return . '">', '</a>');

							$errors = group_user_del($group_id, $mark_ary, false, $group_row['group_name']);

							if ($errors)
							{
								trigger_error($this->language->lang($errors) . $return, E_USER_WARNING);
							}

							return $this->helper->message($this->language->lang('GROUP_USERS_REMOVE') . $return);
						}
						else
						{
							confirm_box(false, $this->language->lang('CONFIRM_OPERATION'), build_hidden_fields([
								'action'	=> $action,
								'mark'		=> $mark_ary,
								'g'			=> $group_id,
							]));

							return redirect($this->helper->route('ucp_groups_manage', ['action' => 'list', 'g' => $group_id]));
						}
					break;

					case 'addusers':
						$this->language->add_lang(['acp/groups', 'acp/common']);

						$names = $this->request->variable('usernames', '', true);

						if (!$group_id)
						{
							return trigger_error($this->language->lang('NO_GROUP') . $return_manage, E_USER_WARNING);
						}

						if (!$names)
						{
							return trigger_error($this->language->lang('NO_USERS') . $return_manage, E_USER_WARNING);
						}

						if (!($row = group_memberships($group_id, $this->user->data['user_id'])))
						{
							return trigger_error($this->language->lang('NOT_MEMBER_OF_GROUP') . $return_manage, E_USER_WARNING);
						}

						$row = current($row);

						if (!$row['group_leader'])
						{
							return trigger_error($this->language->lang('NOT_LEADER_OF_GROUP') . $return_manage, E_USER_WARNING);
						}

						$name_ary = array_unique(explode("\n", $names));
						$group_name = $this->group_helper->get_name($group_row['group_name']);

						$default = $this->request->variable('default', 0);

						$u_return = $this->helper->route('ucp_groups_manage', ['action' => 'list', 'g' => $group_id]);
						$return = '<br /><br />' . $this->language->lang('RETURN_PAGE', '<a href="' . $u_return . '">', '</a>');

						if (confirm_box(true))
						{
							// Add user/s to group
							$errors = group_user_add($group_id, false, $name_ary, $group_name, $default, 0, 0, $group_row);

							if (!empty($errors))
							{
								$display_message = $this->language->lang($errors);

								if ($errors == 'GROUP_USERS_INVALID')
								{
									// Find which users don't exist
									$actual_name_ary = $name_ary;
									$actual_user_id_ary = [];
									user_get_id_name($actual_user_id_ary, $actual_name_ary, false, true);

									$display_message = $this->language->lang('GROUP_USERS_INVALID', implode($this->language->lang('COMMA_SEPARATOR'), array_udiff($name_ary, $actual_name_ary, 'strcasecmp')));
								}

								return trigger_error($display_message . $return, E_USER_WARNING);
							}

							return $this->helper->message($this->language->lang('GROUP_USERS_ADDED') . $return);
						}
						else
						{
							$message = $this->language->lang('GROUP_CONFIRM_ADD_USERS', count($name_ary), implode($this->language->lang('COMMA_SEPARATOR'), $name_ary));

							confirm_box(false, $message, build_hidden_fields([
								'action'	=> $action,
								'default'	=> $default,
								'usernames'	=> $names,
								'g'			=> $group_id,
							]));

							return trigger_error($this->language->lang('NO_USERS_ADDED') . $return);
						}
					break;

					default:
						$this->language->add_lang('acp/common');

						$sql = 'SELECT g.group_id, g.group_name, g.group_colour, g.group_desc, g.group_desc_uid,
										g.group_desc_bitfield, g.group_desc_options, g.group_type, ug.group_leader
							FROM ' . $this->tables['groups'] . ' g,
								' . $this->tables['user_group'] . ' ug
							WHERE ug.user_id = ' . (int) $this->user->data['user_id'] . '
								AND g.group_id = ug.group_id
								AND ug.group_leader = 1
							ORDER BY g.group_type DESC, g.group_name';
						$result = $this->db->sql_query($sql);
						while ($row = $this->db->sql_fetchrow($result))
						{
							$this->template->assign_block_vars('leader', [
								'GROUP_ID'		=> $row['group_id'],
								'GROUP_COLOUR'	=> $row['group_colour'],
								'GROUP_DESC'	=> generate_text_for_display($row['group_desc'], $row['group_desc_uid'], $row['group_desc_bitfield'], $row['group_desc_options']),
								'GROUP_NAME'	=> $this->group_helper->get_name($row['group_name']),
								'GROUP_TYPE'	=> $row['group_type'],

								'U_EDIT'		=> $this->helper->route('ucp_groups_manage', ['action' => 'edit', 'g' => $row['group_id']]),
								'U_LIST'		=> $this->helper->route('ucp_groups_manage', ['action' => 'list', 'g' => $row['group_id']]),
							]);
						}
						$this->db->sql_freeresult($result);
					break;
				}
			break;
		}

		$l_mode = $mode === 'membership' ? 'UCP_GROUPS_EDIT' : 'UCP_GROUPS_MANAGE';

		return $this->helper->render("ucp_groups_{$mode}.html", $this->language->lang($l_mode));
	}

	/**
	 * Get the string value for a group type constant.
	 *
	 * @param int		$group_type		The value to get the constant string for.
	 * @return string					The constant string
	 */
	protected function get_constant_string($group_type)
	{
		switch ($group_type)
		{
			case GROUP_OPEN:
				return 'OPEN';

			case GROUP_CLOSED:
				return 'CLOSED';

			case GROUP_HIDDEN:
				return 'HIDDEN';

			case GROUP_SPECIAL:
				return 'SPECIAL';

			case GROUP_FREE:
				return 'FREE';

			default:
				return '';
		}
	}
}
