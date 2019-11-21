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

use phpbb\exception\http_exception;

class pm_settings
{
	/** @var \phpbb\auth\auth */
	protected $auth;

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
	 * @param \phpbb\auth\auth					$auth				Auth object
	 * @param \phpbb\config\config				$config				Config object
	 * @param \phpbb\db\driver\driver_interface	$db					Database object
	 * @param \phpbb\group\helper				$group_helper		Group helper object
	 * @param \phpbb\controller\helper			$helper				Controller helper object
	 * @param \phpbb\language\language			$language			Language object
	 * @param \phpbb\request\request			$request			Request object
	 * @param \phpbb\template\template			$template			Template object
	 * @param \phpbb\user						$user				User object
	 * @param string							$root_path			phpBB root path
	 * @param string							$php_ext			php File extensions
	 * @param array								$tables				phpBB tables
	 */
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\group\helper $group_helper,
		\phpbb\controller\helper $helper,
		\phpbb\language\language $language,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		$root_path,
		$php_ext,
		$tables
	)
	{
		$this->auth			= $auth;
		$this->config		= $config;
		$this->db			= $db;
		$this->group_helper	= $group_helper;
		$this->helper		= $helper;
		$this->language		= $language;
		$this->request		= $request;
		$this->template		= $template;
		$this->user			= $user;

		$this->root_path	= $root_path;
		$this->php_ext		= $php_ext;
		$this->tables		= $tables;
	}

	public function main()
	{
		if (!$this->user->data['is_registered'])
		{
			throw new http_exception(400, 'NO_MESSAGE');
		}

		// Is PM disabled?
		if (!$this->config['allow_privmsg'])
		{
			throw new http_exception(400, 'PM_DISABLED');
		}

		if (!function_exists('get_folder'))
		{
			include($this->root_path . 'includes/functions_privmsgs.' . $this->php_ext);
		}

		$this->language->add_lang('posting');
		$this->template->assign_var('S_PRIVMSGS', true);

		// Global variables defined in functions_privmsgs.php
		global $global_privmsgs_rules, $global_rule_conditions;

		set_user_message_limit();
		get_folder($this->user->data['user_id']);

		$redirect_url = $this->helper->route('ucp_pm_settings');

		$form_key = 'ucp_pm_options';
		add_form_key($form_key);

		// Change "full folder" setting - what to do if folder is full
		if ($this->request->is_set_post('fullfolder'))
		{
			if (!check_form_key($form_key))
			{
				trigger_error('FORM_INVALID', E_USER_WARNING);
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

				$message = $this->language->lang('FULL_FOLDER_OPTION_CHANGED') . '<br /><br />' . $this->language->lang('RETURN_UCP', '<a href="' . $redirect_url . '">', '</a>');
				meta_refresh(3, $redirect_url);
				trigger_error($message);
			}
		}

		// Add Folder
		if ($this->request->is_set_post('addfolder'))
		{
			if (check_form_key($form_key))
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
						trigger_error($this->language->lang('FOLDER_NAME_EXIST', $folder_name), E_USER_WARNING);
					}

					$sql = 'SELECT COUNT(folder_id) as num_folder
						FROM ' . $this->tables['privmsgs_folder'] . '
							WHERE user_id = ' . $this->user->data['user_id'];
					$result = $this->db->sql_query($sql);
					$num_folder = (int) $this->db->sql_fetchfield('num_folder');
					$this->db->sql_freeresult($result);

					if ($num_folder >= $this->config['pm_max_boxes'])
					{
						trigger_error('MAX_FOLDER_REACHED', E_USER_WARNING);
					}

					$sql = 'INSERT INTO ' . $this->tables['privmsgs_folder'] . ' ' . $this->db->sql_build_array('INSERT', [
						'user_id'		=> (int) $this->user->data['user_id'],
						'folder_name'	=> $folder_name,
					]);
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
			$message = $msg . '<br /><br />' . $this->language->lang('RETURN_UCP', '<a href="' . $redirect_url . '">', '</a>');
			meta_refresh(3, $redirect_url);
			trigger_error($message);
		}

		// Rename folder
		if ($this->request->is_set_post('rename_folder'))
		{
			if (check_form_key($form_key))
			{
				$new_folder_name = $this->request->variable('new_folder_name', '', true);
				$rename_folder_id= $this->request->variable('rename_folder_id', 0);

				if (!$new_folder_name)
				{
					trigger_error('NO_NEW_FOLDER_NAME', E_USER_WARNING);
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
					trigger_error('CANNOT_RENAME_FOLDER', E_USER_WARNING);
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

			$message = $msg . '<br /><br />' . $this->language->lang('RETURN_UCP', '<a href="' . $redirect_url . '">', '</a>');

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
				trigger_error('CANNOT_MOVE_TO_SAME_FOLDER', E_USER_WARNING);
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
				trigger_error('CANNOT_REMOVE_FOLDER', E_USER_WARNING);
			}

			$s_hidden_fields = [
				'remove_folder_id'	=> $remove_folder_id,
				'remove_action'		=> $remove_action,
				'move_to'			=> $move_to,
				'remove_folder'		=> 1
			];

			// Do we need to confirm?
			if (confirm_box(true))
			{
				// Gather message ids
				$sql = 'SELECT msg_id
					FROM ' . $this->tables['privmsgs_to'] . '
					WHERE user_id = ' . $this->user->data['user_id'] . "
						AND folder_id = $remove_folder_id";
				$result = $this->db->sql_query($sql);

				$msg_ids = [];
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
							trigger_error($this->language->lang('MOVE_PM_ERROR', $this->language->lang('MESSAGES_COUNT', (int) $folder_row['pm_count']), $num_moved), E_USER_WARNING);
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

				$message = $this->language->lang('FOLDER_REMOVED');

				meta_refresh(3, $redirect_url);
				$message .= '<br /><br />' . $this->language->lang('RETURN_UCP', '<a href="' . $redirect_url . '">', '</a>');
				trigger_error($message);
			}
			else
			{
				confirm_box(false, 'REMOVE_FOLDER', build_hidden_fields($s_hidden_fields));

				return redirect($redirect_url);
			}
		}

		// Add Rule
		if ($this->request->is_set_post('add_rule'))
		{
			if (check_form_key($form_key))
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
					trigger_error('RULE_NOT_DEFINED', E_USER_WARNING);
				}

				if (($cond_option == 'user' && !$rule_user_id) || ($cond_option == 'group' && !$rule_group_id))
				{
					trigger_error('RULE_NOT_DEFINED', E_USER_WARNING);
				}

				$rule_ary = [
					'user_id'			=> $this->user->data['user_id'],
					'rule_check'		=> $check_option,
					'rule_connection'	=> $rule_option,
					'rule_string'		=> $rule_string,
					'rule_user_id'		=> $rule_user_id,
					'rule_group_id'		=> $rule_group_id,
					'rule_action'		=> $action,
					'rule_folder_id'	=> $folder_id
				];

				$sql = 'SELECT rule_id
					FROM ' . $this->tables['privmsgs_rules'] . '
					WHERE ' . $this->db->sql_build_array('SELECT', $rule_ary);
				$result = $this->db->sql_query($sql);
				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				if ($row)
				{
					trigger_error('RULE_ALREADY_DEFINED', E_USER_WARNING);
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
					trigger_error('RULE_LIMIT_REACHED', E_USER_WARNING);
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
			$message = $msg . '<br /><br />' . $this->language->lang('RETURN_UCP', '<a href="' . $redirect_url . '">', '</a>');
			meta_refresh(3, $redirect_url);
			trigger_error($message);
		}

		// Remove Rule
		if ($this->request->is_set_post('delete_rule') && !$this->request->is_set_post('cancel'))
		{
			$delete_id = array_keys($this->request->variable('delete_rule', [0 => 0]));
			$delete_id = (!empty($delete_id[0])) ? $delete_id[0] : 0;

			if (!$delete_id)
			{
				redirect($redirect_url);
			}

			// Do we need to confirm?
			if (confirm_box(true))
			{
				$sql = 'DELETE FROM ' . $this->tables['privmsgs_rules'] . '
					WHERE user_id = ' . $this->user->data['user_id'] . "
						AND rule_id = $delete_id";
				$this->db->sql_query($sql);

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

				meta_refresh(3, $redirect_url);
				$message .= '<br /><br />' . $this->language->lang('RETURN_UCP', '<a href="' . $redirect_url . '">', '</a>');
				trigger_error($message);
			}
			else
			{
				confirm_box(false, 'DELETE_RULE', build_hidden_fields(['delete_rule' => [$delete_id => 1]]));

				return redirect($redirect_url);
			}
		}

		$folder = [];

		$sql = 'SELECT COUNT(msg_id) as num_messages
			FROM ' . $this->tables['privmsgs_to'] . '
			WHERE user_id = ' . $this->user->data['user_id'] . '
				AND folder_id = ' . PRIVMSGS_INBOX;
		$result = $this->db->sql_query($sql);
		$num_messages = (int) $this->db->sql_fetchfield('num_messages');
		$this->db->sql_freeresult($result);

		$folder[PRIVMSGS_INBOX] = [
			'folder_name'		=> $this->language->lang('PM_INBOX'),
			'message_status'	=> $this->language->lang('FOLDER_MESSAGE_STATUS', $this->language->lang('MESSAGES_COUNT', (int) $this->user->data['message_limit']), $num_messages),
		];

		$sql = 'SELECT folder_id, folder_name, pm_count
			FROM ' . $this->tables['privmsgs_folder'] . '
				WHERE user_id = ' . $this->user->data['user_id'];
		$result = $this->db->sql_query($sql);

		$num_user_folder = 0;
		while ($row = $this->db->sql_fetchrow($result))
		{
			$num_user_folder++;
			$folder[$row['folder_id']] = [
				'folder_name'		=> $row['folder_name'],
				'message_status'	=> $this->language->lang('FOLDER_MESSAGE_STATUS', $this->language->lang('MESSAGES_COUNT', (int) $this->user->data['message_limit']), (int) $row['pm_count']),
			];
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

		$this->template->assign_vars([
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
		]);

		$rule_lang = $action_lang = $check_lang = [];

		$lang = $this->language->get_lang_array();

		// Build all three language arrays
		preg_replace_callback('#^((RULE|ACTION|CHECK)_([A-Z0-9_]+))$#', function ($match) use(&$rule_lang, &$action_lang, &$check_lang, $lang) {
			${strtolower($match[2]) . '_lang'}[constant($match[1])] = $lang['PM_' . $match[2]][$match[3]];
		}, array_keys(get_defined_constants()));

		/*
			Rule Ordering:
				-> CHECK_* -> RULE_* [IN $global_privmsgs_rules:CHECK_*] -> [IF $rule_conditions[RULE_*] [|text|bool|user|group|own_group]] -> ACTION_*
		 */

		$check_option	= $this->request->variable('check_option', 0);
		$rule_option	= $this->request->variable('rule_option', 0);
		$cond_option	= $this->request->variable('cond_option', '');
		$action_option	= $this->request->variable('action_option', '');
		$back = ($this->request->is_set('back')) ? $this->request->variable('back', ['' => 0]) : [];

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

		$this->define_check_option(($check_option && !isset($back['rule'])) ? true : false, $check_option, $check_lang);

		if ($check_option && !isset($back['rule']))
		{
			$this->define_rule_option(($rule_option && !isset($back['cond'])) ? true : false, $rule_option, $rule_lang, $global_privmsgs_rules[$check_option]);
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
				$this->define_cond_option(($cond_option && !isset($back['action'])) ? true : false, $rule_option, $global_rule_conditions);
			}
		}

		if ($cond_option && !isset($back['action']))
		{
			$this->define_action_option(false, $action_option, $action_lang, $folder);
		}

		$this->show_defined_rules($this->user->data['user_id'], $check_lang, $rule_lang, $action_lang, $folder);

		return $this->helper->render('ucp_pm_options.html', $this->language->lang('UCP_PM_OPTIONS'));
	}

	/**
	 * Defining check option for message rules
	 */
	protected function define_check_option($hardcoded, $check_option, $check_lang)
	{

		$s_check_options = '';
		if (!$hardcoded)
		{
			foreach ($check_lang as $value => $lang)
			{
				$s_check_options .= '<option value="' . $value . '"' . (($value == $check_option) ? ' selected="selected"' : '') . '>' . $lang . '</option>';
			}
		}

		$this->template->assign_vars([
			'S_CHECK_DEFINED'	=> true,
			'S_CHECK_SELECT'	=> ($hardcoded) ? false : true,
			'CHECK_CURRENT'		=> isset($check_lang[$check_option]) ? $check_lang[$check_option] : '',
			'S_CHECK_OPTIONS'	=> $s_check_options,
			'CHECK_OPTION'		=> $check_option,
		]);
	}

	/**
	 * Defining action option for message rules
	 */
	protected function define_action_option($hardcoded, $action_option, $action_lang, $folder)
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

		$this->template->assign_vars([
			'S_ACTION_DEFINED'	=> true,
			'S_ACTION_SELECT'	=> ($hardcoded) ? false : true,
			'ACTION_CURRENT'	=> $l_action,
			'S_ACTION_OPTIONS'	=> $s_action_options,
			'ACTION_OPTION'		=> $action_option,
		]);
	}

	/**
	 * Defining rule option for message rules
	 */
	protected function define_rule_option($hardcoded, $rule_option, $rule_lang, $check_ary)
	{
		$s_rule_options = '';
		if (!$hardcoded)
		{
			foreach ($check_ary as $value => $_check)
			{
				$s_rule_options .= '<option value="' . $value . '"' . (($value == $rule_option) ? ' selected="selected"' : '') . '>' . $rule_lang[$value] . '</option>';
			}
		}

		$this->template->assign_vars([
			'S_RULE_DEFINED'	=> true,
			'S_RULE_SELECT'		=> !$hardcoded,
			'RULE_CURRENT'		=> isset($rule_lang[$rule_option]) ? $rule_lang[$rule_option] : '',
			'S_RULE_OPTIONS'	=> $s_rule_options,
			'RULE_OPTION'		=> $rule_option,
		]);
	}

	/**
	 * Defining condition option for message rules
	 */
	protected function define_cond_option($hardcoded, $rule_option, $global_rule_conditions)
	{
		$this->template->assign_vars([
			'S_COND_DEFINED'	=> true,
			'S_COND_SELECT'		=> (!$hardcoded && isset($global_rule_conditions[$rule_option])) ? true : false,
		]);

		// Define COND_OPTION
		if (!isset($global_rule_conditions[$rule_option]))
		{
			$this->template->assign_vars([
				'COND_OPTION'	=> 'none',
				'COND_CURRENT'	=> false,
			]);
			return;
		}

		// Define Condition
		$condition = $global_rule_conditions[$rule_option];

		switch ($condition)
		{
			case 'text':
				$rule_string = $this->request->variable('rule_string', '', true);

				$this->template->assign_vars([
					'S_TEXT_CONDITION'	=> true,
					'CURRENT_STRING'	=> $rule_string,
					'CURRENT_USER_ID'	=> 0,
					'CURRENT_GROUP_ID'	=> 0,
				]);

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

				$this->template->assign_vars([
					'S_USER_CONDITION'	=> true,
					'CURRENT_STRING'	=> $rule_string,
					'CURRENT_USER_ID'	=> $rule_user_id,
					'CURRENT_GROUP_ID'	=> 0,
				]);

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

				$this->template->assign_vars([
					'S_GROUP_CONDITION'	=> true,
					'S_GROUP_OPTIONS'	=> $s_group_options,
					'CURRENT_STRING'	=> $rule_string,
					'CURRENT_USER_ID'	=> 0,
					'CURRENT_GROUP_ID'	=> $rule_group_id,
				]);

				$current_value = $rule_string;
			break;

			default:
				return;
		}

		$this->template->assign_vars([
			'COND_OPTION'	=> $condition,
			'COND_CURRENT'	=> $current_value,
		]);
	}

	/**
	 * Display defined message rules
	 */
	protected function show_defined_rules($user_id, $check_lang, $rule_lang, $action_lang, $folder)
	{
		$sql = 'SELECT *
			FROM ' . $this->tables['privmsgs_rules'] . '
			WHERE user_id = ' . $user_id . '
			ORDER BY rule_id ASC';
		$result = $this->db->sql_query($sql);

		$count = 0;
		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->template->assign_block_vars('rule', [
				'COUNT'		=> ++$count,
				'RULE_ID'	=> $row['rule_id'],
				'CHECK'		=> $check_lang[$row['rule_check']],
				'RULE'		=> $rule_lang[$row['rule_connection']],
				'STRING'	=> $row['rule_string'],
				'ACTION'	=> $action_lang[$row['rule_action']],
				'FOLDER'	=> ($row['rule_action'] == ACTION_PLACE_INTO_FOLDER) ? $folder[$row['rule_folder_id']]['folder_name'] : '',
			]);
		}
		$this->db->sql_freeresult($result);
	}
}
