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

class pm_options
{
	/**
	 * Execute message options
	 */
	function message_options($id, $mode, $global_privmsgs_rules, $global_rule_conditions)
	{

		$redirect_url = append_sid("{$this->root_path}ucp.$this->php_ext", "i=pm&amp;mode=options");

		add_form_key('ucp_pm_options');
		// Change "full folder" setting - what to do if folder is full
		if ($this->request->is_set_post('fullfolder'))
		{
			if (!check_form_key('ucp_pm_options'))
			{
				trigger_error('FORM_INVALID');
			}

			$full_action = $this->request->variable('full_action', 0);

			$set_folder_id = 0;
			switch ($full_action)
			{
				case 1:
					$set_folder_id = FULL_FOLDER_DELETE;
				break;

				case 2:
					$set_folder_id = $this->request->variable('full_move_to', PRIVMSGS_INBOX);
				break;

				case 3:
					$set_folder_id = FULL_FOLDER_HOLD;
				break;

				default:
					$full_action = 0;
				break;
			}

			if ($full_action)
			{
				$sql = 'UPDATE ' . $this->tables['users'] . '
				SET user_full_folder = ' . $set_folder_id . '
				WHERE user_id = ' . $this->user->data['user_id'];
				$this->db->sql_query($sql);

				$this->user->data['user_full_folder'] = $set_folder_id;

				$message = $this->language->lang('FULL_FOLDER_OPTION_CHANGED') . '<br /><br />' . sprintf($this->language->lang('RETURN_UCP'), '<a href="' . $redirect_url . '">', '</a>');
				meta_refresh(3, $redirect_url);
				trigger_error($message);
			}
		}

		// Add Folder
		if ($this->request->is_set_post('addfolder'))
		{
			if (check_form_key('ucp_pm_options'))
			{
				$folder_name = $this->request->variable('foldername', '', true);

				if ($folder_name)
				{
					$sql = 'SELECT folder_name
					FROM ' . $this->tables['privmsgs_folder'] . "
					WHERE folder_name = '" . $this->db->sql_escape($folder_name) . "'
						AND user_id = " . $this->user->data['user_id'];
					$result = $this->db->sql_query_limit($sql, 1);
					$row = $this->db->sql_fetchrow($result);
					$this->db->sql_freeresult($result);

					if ($row)
					{
						trigger_error(sprintf($this->language->lang('FOLDER_NAME_EXIST'), $folder_name));
					}

					$sql = 'SELECT COUNT(folder_id) as num_folder
					FROM ' . $this->tables['privmsgs_folder'] . '
						WHERE user_id = ' . $this->user->data['user_id'];
					$result = $this->db->sql_query($sql);
					$num_folder = (int) $this->db->sql_fetchfield('num_folder');
					$this->db->sql_freeresult($result);

					if ($num_folder >= $this->config['pm_max_boxes'])
					{
						trigger_error('MAX_FOLDER_REACHED');
					}

					$sql = 'INSERT INTO ' . $this->tables['privmsgs_folder'] . ' ' . $this->db->sql_build_array('INSERT', array(
								'user_id'		=> (int) $this->user->data['user_id'],
								'folder_name'	=> $folder_name)
						);
					$this->db->sql_query($sql);
					$msg = $this->language->lang('FOLDER_ADDED');
				}
				else
				{
					$msg = $this->language->lang('FOLDER_NAME_EMPTY');
				}
			}
			else
			{
				$msg = $this->language->lang('FORM_INVALID');
			}
			$message = $msg . '<br /><br />' . sprintf($this->language->lang('RETURN_UCP'), '<a href="' . $redirect_url . '">', '</a>');
			meta_refresh(3, $redirect_url);
			trigger_error($message);
		}

		// Rename folder
		if ($this->request->is_set_post('rename_folder'))
		{
			if (check_form_key('ucp_pm_options'))
			{
				$new_folder_name = $this->request->variable('new_folder_name', '', true);
				$rename_folder_id= $this->request->variable('rename_folder_id', 0);

				if (!$new_folder_name)
				{
					trigger_error('NO_NEW_FOLDER_NAME');
				}

				// Select custom folder
				$sql = 'SELECT folder_name, pm_count
				FROM ' . $this->tables['privmsgs_folder'] . "
				WHERE user_id = {$this->user->data['user_id']}
					AND folder_id = $rename_folder_id";
				$result = $this->db->sql_query_limit($sql, 1);
				$folder_row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				if (!$folder_row)
				{
					trigger_error('CANNOT_RENAME_FOLDER');
				}

				$sql = 'UPDATE ' . $this->tables['privmsgs_folder'] . "
				SET folder_name = '" . $this->db->sql_escape($new_folder_name) . "'
				WHERE folder_id = $rename_folder_id
					AND user_id = {$this->user->data['user_id']}";
				$this->db->sql_query($sql);
				$msg = $this->language->lang('FOLDER_RENAMED');
			}
			else
			{
				$msg = $this->language->lang('FORM_INVALID');
			}

			$message = $msg . '<br /><br />' . sprintf($this->language->lang('RETURN_UCP'), '<a href="' . $redirect_url . '">', '</a>');

			meta_refresh(3, $redirect_url);
			trigger_error($message);
		}

		// Remove Folder
		if ($this->request->is_set_post('remove_folder'))
		{
			$remove_folder_id = $this->request->variable('remove_folder_id', 0);

			// Default to "move all messages to inbox"
			$remove_action = $this->request->variable('remove_action', 1);
			$move_to = $this->request->variable('move_to', PRIVMSGS_INBOX);

			// Move to same folder?
			if ($remove_action == 1 && $remove_folder_id == $move_to)
			{
				trigger_error('CANNOT_MOVE_TO_SAME_FOLDER');
			}

			// Select custom folder
			$sql = 'SELECT folder_name, pm_count
			FROM ' . $this->tables['privmsgs_folder'] . "
			WHERE user_id = {$this->user->data['user_id']}
				AND folder_id = $remove_folder_id";
			$result = $this->db->sql_query_limit($sql, 1);
			$folder_row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if (!$folder_row)
			{
				trigger_error('CANNOT_REMOVE_FOLDER');
			}

			$s_hidden_fields = array(
				'remove_folder_id'	=> $remove_folder_id,
				'remove_action'		=> $remove_action,
				'move_to'			=> $move_to,
				'remove_folder'		=> 1
			);

			// Do we need to confirm?
			if (confirm_box(true))
			{
				// Gather message ids
				$sql = 'SELECT msg_id
				FROM ' . $this->tables['privmsgs_to'] . '
				WHERE user_id = ' . $this->user->data['user_id'] . "
					AND folder_id = $remove_folder_id";
				$result = $this->db->sql_query($sql);

				$msg_ids = array();
				while ($row = $this->db->sql_fetchrow($result))
				{
					$msg_ids[] = (int) $row['msg_id'];
				}
				$this->db->sql_freeresult($result);

				// First of all, copy all messages to another folder... or delete all messages
				switch ($remove_action)
				{
					// Move Messages
					case 1:
						$num_moved = move_pm($this->user->data['user_id'], $this->user->data['message_limit'], $msg_ids, $move_to, $remove_folder_id);

						// Something went wrong, only partially moved?
						if ($num_moved != $folder_row['pm_count'])
						{
							trigger_error($this->language->lang('MOVE_PM_ERROR', $this->language->lang('MESSAGES_COUNT', (int) $folder_row['pm_count']), $num_moved));
						}
					break;

					// Remove Messages
					case 2:
						delete_pm($this->user->data['user_id'], $msg_ids, $remove_folder_id);
					break;
				}

				// Remove folder
				$sql = 'DELETE FROM ' . $this->tables['privmsgs_folder'] . "
				WHERE user_id = {$this->user->data['user_id']}
					AND folder_id = $remove_folder_id";
				$this->db->sql_query($sql);

				// Check full folder option. If the removed folder has been specified as destination switch back to inbox
				if ($this->user->data['user_full_folder'] == $remove_folder_id)
				{
					$sql = 'UPDATE ' . $this->tables['users'] . '
					SET user_full_folder = ' . PRIVMSGS_INBOX . '
					WHERE user_id = ' . $this->user->data['user_id'];
					$this->db->sql_query($sql);

					$this->user->data['user_full_folder'] = PRIVMSGS_INBOX;
				}

				// Now make sure the folder is not used for rules
				// We assign another folder id (the one the messages got moved to) or assign the INBOX (to not have to remove any rule)
				$sql = 'UPDATE ' . $this->tables['privmsgs_rules'] . ' SET rule_folder_id = ';
				$sql .= ($remove_action == 1) ? $move_to : PRIVMSGS_INBOX;
				$sql .= ' WHERE rule_folder_id = ' . $remove_folder_id;

				$this->db->sql_query($sql);

				$meta_info = append_sid("{$this->root_path}ucp.$this->php_ext", "i=pm&amp;mode=$mode");
				$message = $this->language->lang('FOLDER_REMOVED');

				meta_refresh(3, $meta_info);
				$message .= '<br /><br />' . sprintf($this->language->lang('RETURN_UCP'), '<a href="' . $meta_info . '">', '</a>');
				trigger_error($message);
			}
			else
			{
				confirm_box(false, 'REMOVE_FOLDER', build_hidden_fields($s_hidden_fields));
			}
		}

		// Add Rule
		if ($this->request->is_set_post('add_rule'))
		{
			if (check_form_key('ucp_pm_options'))
			{
				$check_option	= $this->request->variable('check_option', 0);
				$rule_option	= $this->request->variable('rule_option', 0);
				$cond_option	= $this->request->variable('cond_option', '');
				$action_option	= explode('|', $this->request->variable('action_option', ''));
				$rule_string	= ($cond_option != 'none') ? $this->request->variable('rule_string', '', true) : '';
				$rule_user_id	= ($cond_option != 'none') ? $this->request->variable('rule_user_id', 0) : 0;
				$rule_group_id	= ($cond_option != 'none') ? $this->request->variable('rule_group_id', 0) : 0;

				$action = (int) $action_option[0];
				$folder_id = (int) $action_option[1];

				if (!$action || !$check_option || !$rule_option || !$cond_option || ($cond_option != 'none' && !$rule_string))
				{
					trigger_error('RULE_NOT_DEFINED');
				}

				if (($cond_option == 'user' && !$rule_user_id) || ($cond_option == 'group' && !$rule_group_id))
				{
					trigger_error('RULE_NOT_DEFINED');
				}

				$rule_ary = array(
					'user_id'			=> $this->user->data['user_id'],
					'rule_check'		=> $check_option,
					'rule_connection'	=> $rule_option,
					'rule_string'		=> $rule_string,
					'rule_user_id'		=> $rule_user_id,
					'rule_group_id'		=> $rule_group_id,
					'rule_action'		=> $action,
					'rule_folder_id'	=> $folder_id
				);

				$sql = 'SELECT rule_id
				FROM ' . $this->tables['privmsgs_rules'] . '
				WHERE ' . $this->db->sql_build_array('SELECT', $rule_ary);
				$result = $this->db->sql_query($sql);
				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				if ($row)
				{
					trigger_error('RULE_ALREADY_DEFINED');
				}

				// Prevent users from flooding the rules table
				$sql = 'SELECT COUNT(rule_id) AS num_rules
				FROM ' . $this->tables['privmsgs_rules'] . '
				WHERE user_id = ' . (int) $this->user->data['user_id'];
				$result = $this->db->sql_query($sql);
				$num_rules = (int) $this->db->sql_fetchfield('num_rules');
				$this->db->sql_freeresult($result);

				if ($num_rules >= 5000)
				{
					trigger_error('RULE_LIMIT_REACHED');
				}

				$sql = 'INSERT INTO ' . $this->tables['privmsgs_rules'] . ' ' . $this->db->sql_build_array('INSERT', $rule_ary);
				$this->db->sql_query($sql);

				// Set the user_message_rules bit
				$sql = 'UPDATE ' . $this->tables['users'] . '
				SET user_message_rules = 1
				WHERE user_id = ' . $this->user->data['user_id'];
				$this->db->sql_query($sql);

				$msg = $this->language->lang('RULE_ADDED');
			}
			else
			{
				$msg = $this->language->lang('FORM_INVALID');
			}
			$message = $msg . '<br /><br />' . sprintf($this->language->lang('RETURN_UCP'), '<a href="' . $redirect_url . '">', '</a>');
			meta_refresh(3, $redirect_url);
			trigger_error($message);
		}

		// Remove Rule
		if ($this->request->is_set_post('delete_rule') && !$this->request->is_set_post('cancel'))
		{
			$delete_id = array_keys($this->request->variable('delete_rule', array(0 => 0)));
			$delete_id = (!empty($delete_id[0])) ? $delete_id[0] : 0;

			if (!$delete_id)
			{
				redirect(append_sid("{$this->root_path}ucp.$this->php_ext", 'i=pm&amp;mode=' . $mode));
			}

			// Do we need to confirm?
			if (confirm_box(true))
			{
				$sql = 'DELETE FROM ' . $this->tables['privmsgs_rules'] . '
				WHERE user_id = ' . $this->user->data['user_id'] . "
					AND rule_id = $delete_id";
				$this->db->sql_query($sql);

				$meta_info = append_sid("{$this->root_path}ucp.$this->php_ext", 'i=pm&amp;mode=' . $mode);
				$message = $this->language->lang('RULE_DELETED');

				// Reset user_message_rules if no more assigned
				$sql = 'SELECT rule_id
				FROM ' . $this->tables['privmsgs_rules'] . '
				WHERE user_id = ' . $this->user->data['user_id'];
				$result = $this->db->sql_query_limit($sql, 1);
				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				// Unset the user_message_rules bit
				if (!$row)
				{
					$sql = 'UPDATE ' . $this->tables['users'] . '
					SET user_message_rules = 0
					WHERE user_id = ' . $this->user->data['user_id'];
					$this->db->sql_query($sql);
				}

				meta_refresh(3, $meta_info);
				$message .= '<br /><br />' . sprintf($this->language->lang('RETURN_UCP'), '<a href="' . $meta_info . '">', '</a>');
				trigger_error($message);
			}
			else
			{
				confirm_box(false, 'DELETE_RULE', build_hidden_fields(array('delete_rule' => array($delete_id => 1))));
			}
		}

		$folder = array();

		$sql = 'SELECT COUNT(msg_id) as num_messages
		FROM ' . $this->tables['privmsgs_to'] . '
		WHERE user_id = ' . $this->user->data['user_id'] . '
			AND folder_id = ' . PRIVMSGS_INBOX;
		$result = $this->db->sql_query($sql);
		$num_messages = (int) $this->db->sql_fetchfield('num_messages');
		$this->db->sql_freeresult($result);

		$folder[PRIVMSGS_INBOX] = array(
			'folder_name'		=> $this->language->lang('PM_INBOX'),
			'message_status'	=> $this->language->lang('FOLDER_MESSAGE_STATUS', $this->language->lang('MESSAGES_COUNT', (int) $this->user->data['message_limit']), $num_messages),
		);

		$sql = 'SELECT folder_id, folder_name, pm_count
		FROM ' . $this->tables['privmsgs_folder'] . '
			WHERE user_id = ' . $this->user->data['user_id'];
		$result = $this->db->sql_query($sql);

		$num_user_folder = 0;
		while ($row = $this->db->sql_fetchrow($result))
		{
			$num_user_folder++;
			$folder[$row['folder_id']] = array(
				'folder_name'		=> $row['folder_name'],
				'message_status'	=> $this->language->lang('FOLDER_MESSAGE_STATUS', $this->language->lang('MESSAGES_COUNT', (int) $this->user->data['message_limit']), (int) $row['pm_count']),
			);
		}
		$this->db->sql_freeresult($result);

		$s_full_folder_options = $s_to_folder_options = $s_folder_options = '';

		if ($this->user->data['user_full_folder'] == FULL_FOLDER_NONE)
		{
			// -3 here to let the correct folder id be selected
			$to_folder_id = $this->config['full_folder_action'] - 3;
		}
		else
		{
			$to_folder_id = $this->user->data['user_full_folder'];
		}

		foreach ($folder as $folder_id => $folder_ary)
		{
			$s_full_folder_options .= '<option value="' . $folder_id . '"' . (($this->user->data['user_full_folder'] == $folder_id) ? ' selected="selected"' : '') . '>' . $folder_ary['folder_name'] . ' (' . $folder_ary['message_status'] . ')</option>';
			$s_to_folder_options .= '<option value="' . $folder_id . '"' . (($to_folder_id == $folder_id) ? ' selected="selected"' : '') . '>' . $folder_ary['folder_name'] . ' (' . $folder_ary['message_status'] . ')</option>';

			if ($folder_id != PRIVMSGS_INBOX)
			{
				$s_folder_options .= '<option value="' . $folder_id . '">' . $folder_ary['folder_name'] . ' (' . $folder_ary['message_status'] . ')</option>';
			}
		}

		$s_delete_checked = ($this->user->data['user_full_folder'] == FULL_FOLDER_DELETE) ? ' checked="checked"' : '';
		$s_hold_checked = ($this->user->data['user_full_folder'] == FULL_FOLDER_HOLD) ? ' checked="checked"' : '';
		$s_move_checked = ($this->user->data['user_full_folder'] >= 0) ? ' checked="checked"' : '';

		if ($this->user->data['user_full_folder'] == FULL_FOLDER_NONE)
		{
			switch ($this->config['full_folder_action'])
			{
				case 1:
					$s_delete_checked = ' checked="checked"';
				break;

				case 2:
					$s_hold_checked = ' checked="checked"';
				break;
			}
		}

		$this->template->assign_vars(array(
			'S_FULL_FOLDER_OPTIONS'	=> $s_full_folder_options,
			'S_TO_FOLDER_OPTIONS'	=> $s_to_folder_options,
			'S_FOLDER_OPTIONS'		=> $s_folder_options,
			'S_DELETE_CHECKED'		=> $s_delete_checked,
			'S_HOLD_CHECKED'		=> $s_hold_checked,
			'S_MOVE_CHECKED'		=> $s_move_checked,
			'S_MAX_FOLDER_REACHED'	=> ($num_user_folder >= $this->config['pm_max_boxes']) ? true : false,
			'S_MAX_FOLDER_ZERO'		=> ($this->config['pm_max_boxes'] == 0) ? true : false,

			'DEFAULT_ACTION'		=> ($this->config['full_folder_action'] == 1) ? $this->language->lang('DELETE_OLDEST_MESSAGES') : $this->language->lang('HOLD_NEW_MESSAGES'),

			'U_FIND_USERNAME'		=> append_sid("{$this->root_path}memberlist.$this->php_ext", 'mode=searchuser&amp;form=ucp&amp;field=rule_string&amp;select_single=true'),
		));

		$rule_lang = $action_lang = $check_lang = array();

		// Build all three language arrays
		preg_replace_callback('#^((RULE|ACTION|CHECK)_([A-Z0-9_]+))$#', function ($match) use(&$rule_lang, &$action_lang, &$check_lang, $user) {
			${strtolower($match[2]) . '_lang'}[constant($match[1])] = $this->language->lang['PM_' . $match[2]][$match[3]];
		}, array_keys(get_defined_constants()));

		/*
			Rule Ordering:
				-> CHECK_* -> RULE_* [IN $global_privmsgs_rules:CHECK_*] -> [IF $rule_conditions[RULE_*] [|text|bool|user|group|own_group]] -> ACTION_*
		 */

		$check_option	= $this->request->variable('check_option', 0);
		$rule_option	= $this->request->variable('rule_option', 0);
		$cond_option	= $this->request->variable('cond_option', '');
		$action_option	= $this->request->variable('action_option', '');
		$back = ($this->request->is_set('back')) ? $this->request->variable('back', array('' => 0)) : array();

		if (count($back))
		{
			if ($action_option)
			{
				$action_option = '';
			}
			else if ($cond_option)
			{
				$cond_option = '';
			}
			else if ($rule_option)
			{
				$rule_option = 0;
			}
			else if ($check_option)
			{
				$check_option = 0;
			}
		}

		if (isset($back['action']) && $cond_option == 'none')
		{
			$back['cond'] = true;
		}

		// Check
		if (!isset($global_privmsgs_rules[$check_option]))
		{
			$check_option = 0;
		}

		define_check_option(($check_option && !isset($back['rule'])) ? true : false, $check_option, $check_lang);

		if ($check_option && !isset($back['rule']))
		{
			define_rule_option(($rule_option && !isset($back['cond'])) ? true : false, $rule_option, $rule_lang, $global_privmsgs_rules[$check_option]);
		}

		if ($rule_option && !isset($back['cond']))
		{
			if (!isset($global_rule_conditions[$rule_option]))
			{
				$cond_option = 'none';
				$this->template->assign_var('NONE_CONDITION', true);
			}
			else
			{
				define_cond_option(($cond_option && !isset($back['action'])) ? true : false, $cond_option, $rule_option, $global_rule_conditions);
			}
		}

		if ($cond_option && !isset($back['action']))
		{
			define_action_option(false, $action_option, $action_lang, $folder);
		}

		show_defined_rules($this->user->data['user_id'], $check_lang, $rule_lang, $action_lang, $folder);
	}

	/**
	 * Defining check option for message rules
	 */
	function define_check_option($hardcoded, $check_option, $check_lang)
	{

		$s_check_options = '';
		if (!$hardcoded)
		{
			foreach ($check_lang as $value => $lang)
			{
				$s_check_options .= '<option value="' . $value . '"' . (($value == $check_option) ? ' selected="selected"' : '') . '>' . $lang . '</option>';
			}
		}

		$this->template->assign_vars(array(
				'S_CHECK_DEFINED'	=> true,
				'S_CHECK_SELECT'	=> ($hardcoded) ? false : true,
				'CHECK_CURRENT'		=> isset($check_lang[$check_option]) ? $check_lang[$check_option] : '',
				'S_CHECK_OPTIONS'	=> $s_check_options,
				'CHECK_OPTION'		=> $check_option)
		);
	}

	/**
	 * Defining action option for message rules
	 */
	function define_action_option($hardcoded, $action_option, $action_lang, $folder)
	{

		$l_action = $s_action_options = '';
		if ($hardcoded)
		{
			$option = explode('|', $action_option);
			$action = (int) $option[0];
			$folder_id = (int) $option[1];

			$l_action = $action_lang[$action];
			if ($action == ACTION_PLACE_INTO_FOLDER)
			{
				$l_action .= ' -> ' . $folder[$folder_id]['folder_name'];
			}
		}
		else
		{
			foreach ($action_lang as $action => $lang)
			{
				if ($action == ACTION_PLACE_INTO_FOLDER)
				{
					foreach ($folder as $folder_id => $folder_ary)
					{
						$s_action_options .= '<option value="' . $action . '|' . $folder_id . '"' . (($action_option == $action . '|' . $folder_id) ? ' selected="selected"' : '') . '>' . $lang . ' -> ' . $folder_ary['folder_name'] . '</option>';
					}
				}
				else
				{
					$s_action_options .= '<option value="' . $action . '|0"' . (($action_option == $action . '|0') ? ' selected="selected"' : '') . '>' . $lang . '</option>';
				}
			}
		}

		$this->template->assign_vars(array(
				'S_ACTION_DEFINED'	=> true,
				'S_ACTION_SELECT'	=> ($hardcoded) ? false : true,
				'ACTION_CURRENT'	=> $l_action,
				'S_ACTION_OPTIONS'	=> $s_action_options,
				'ACTION_OPTION'		=> $action_option)
		);
	}

	/**
	 * Defining rule option for message rules
	 */
	function define_rule_option($hardcoded, $rule_option, $rule_lang, $check_ary)
	{

		$exclude = array();

		if (!$module->loaded('zebra', 'friends'))
		{
			$exclude[RULE_IS_FRIEND] = true;
		}

		if (!$module->loaded('zebra', 'foes'))
		{
			$exclude[RULE_IS_FOE] = true;
		}

		$s_rule_options = '';
		if (!$hardcoded)
		{
			foreach ($check_ary as $value => $_check)
			{
				if (isset($exclude[$value]))
				{
					continue;
				}
				$s_rule_options .= '<option value="' . $value . '"' . (($value == $rule_option) ? ' selected="selected"' : '') . '>' . $rule_lang[$value] . '</option>';
			}
		}

		$this->template->assign_vars(array(
				'S_RULE_DEFINED'	=> true,
				'S_RULE_SELECT'		=> !$hardcoded,
				'RULE_CURRENT'		=> isset($rule_lang[$rule_option]) ? $rule_lang[$rule_option] : '',
				'S_RULE_OPTIONS'	=> $s_rule_options,
				'RULE_OPTION'		=> $rule_option)
		);
	}

	/**
	 * Defining condition option for message rules
	 */
	function define_cond_option($hardcoded, $cond_option, $rule_option, $global_rule_conditions)
	{

		/** @var \phpbb\group\helper $group_helper */
		$group_helper = $phpbb_container->get('group_helper');

		$this->template->assign_vars(array(
				'S_COND_DEFINED'	=> true,
				'S_COND_SELECT'		=> (!$hardcoded && isset($global_rule_conditions[$rule_option])) ? true : false)
		);

		// Define COND_OPTION
		if (!isset($global_rule_conditions[$rule_option]))
		{
			$this->template->assign_vars(array(
					'COND_OPTION'	=> 'none',
					'COND_CURRENT'	=> false)
			);
			return;
		}

		// Define Condition
		$condition = $global_rule_conditions[$rule_option];

		switch ($condition)
		{
			case 'text':
				$rule_string = $this->request->variable('rule_string', '', true);

				$this->template->assign_vars(array(
						'S_TEXT_CONDITION'	=> true,
						'CURRENT_STRING'	=> $rule_string,
						'CURRENT_USER_ID'	=> 0,
						'CURRENT_GROUP_ID'	=> 0)
				);

				$current_value = $rule_string;
			break;

			case 'user':
				$rule_user_id = $this->request->variable('rule_user_id', 0);
				$rule_string = $this->request->variable('rule_string', '', true);

				if ($rule_string && !$rule_user_id)
				{
					$sql = 'SELECT user_id
					FROM ' . $this->tables['users'] . "
					WHERE username_clean = '" . $this->db->sql_escape(utf8_clean_string($rule_string)) . "'";
					$result = $this->db->sql_query($sql);
					$rule_user_id = (int) $this->db->sql_fetchfield('user_id');
					$this->db->sql_freeresult($result);

					if (!$rule_user_id)
					{
						$rule_string = '';
					}
				}
				else if (!$rule_string && $rule_user_id)
				{
					$sql = 'SELECT username
					FROM ' . $this->tables['users'] . "
					WHERE user_id = $rule_user_id";
					$result = $this->db->sql_query($sql);
					$rule_string = $this->db->sql_fetchfield('username');
					$this->db->sql_freeresult($result);

					if (!$rule_string)
					{
						$rule_user_id = 0;
					}
				}

				$this->template->assign_vars(array(
						'S_USER_CONDITION'	=> true,
						'CURRENT_STRING'	=> $rule_string,
						'CURRENT_USER_ID'	=> $rule_user_id,
						'CURRENT_GROUP_ID'	=> 0)
				);

				$current_value = $rule_string;
			break;

			case 'group':
				$rule_group_id = $this->request->variable('rule_group_id', 0);
				$rule_string = $this->request->variable('rule_string', '', true);

				$sql = 'SELECT g.group_id, g.group_name, g.group_type
					FROM ' . $this->tables['groups'] . ' g ';

				if (!$this->auth->acl_gets('a_group', 'a_groupadd', 'a_groupdel'))
				{
					$sql .= 'LEFT JOIN ' . $this->tables['user_group'] . ' ug
					ON (
						g.group_id = ug.group_id
						AND ug.user_id = ' . $this->user->data['user_id'] . '
						AND ug.user_pending = 0
					)
					WHERE (ug.user_id = ' . $this->user->data['user_id'] . ' OR g.group_type <> ' . GROUP_HIDDEN . ')
					AND';
				}
				else
				{
					$sql .= 'WHERE';
				}

				$sql .= " (g.group_name NOT IN ('GUESTS', 'BOTS') OR g.group_type <> " . GROUP_SPECIAL . ')
				ORDER BY g.group_type DESC, g.group_name ASC';

				$result = $this->db->sql_query($sql);

				$s_group_options = '';
				while ($row = $this->db->sql_fetchrow($result))
				{
					if ($rule_group_id && ($row['group_id'] == $rule_group_id))
					{
						$rule_string = $this->group_helper->get_name($row['group_name']);
					}

					$s_class	= ($row['group_type'] == GROUP_SPECIAL) ? ' class="sep"' : '';
					$s_selected	= ($row['group_id'] == $rule_group_id) ? ' selected="selected"' : '';

					$s_group_options .= '<option value="' . $row['group_id'] . '"' . $s_class . $s_selected . '>' . $this->group_helper->get_name($row['group_name']) . '</option>';
				}
				$this->db->sql_freeresult($result);

				$this->template->assign_vars(array(
						'S_GROUP_CONDITION'	=> true,
						'S_GROUP_OPTIONS'	=> $s_group_options,
						'CURRENT_STRING'	=> $rule_string,
						'CURRENT_USER_ID'	=> 0,
						'CURRENT_GROUP_ID'	=> $rule_group_id)
				);

				$current_value = $rule_string;
			break;

			default:
				return;
		}

		$this->template->assign_vars(array(
				'COND_OPTION'	=> $condition,
				'COND_CURRENT'	=> $current_value)
		);
	}

	/**
	 * Display defined message rules
	 */
	function show_defined_rules($user_id, $check_lang, $rule_lang, $action_lang, $folder)
	{

		$sql = 'SELECT *
		FROM ' . $this->tables['privmsgs_rules'] . '
		WHERE user_id = ' . $user_id . '
		ORDER BY rule_id ASC';
		$result = $this->db->sql_query($sql);

		$count = 0;
		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->template->assign_block_vars('rule', array(
					'COUNT'		=> ++$count,
					'RULE_ID'	=> $row['rule_id'],
					'CHECK'		=> $check_lang[$row['rule_check']],
					'RULE'		=> $rule_lang[$row['rule_connection']],
					'STRING'	=> $row['rule_string'],
					'ACTION'	=> $action_lang[$row['rule_action']],
					'FOLDER'	=> ($row['rule_action'] == ACTION_PLACE_INTO_FOLDER) ? $folder[$row['rule_folder_id']]['folder_name'] : '')
			);
		}
		$this->db->sql_freeresult($result);
	}
}
