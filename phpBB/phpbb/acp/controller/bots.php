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

class bots
{
	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\acp\helper\controller */
	protected $helper;

	/** @var \phpbb\language\language */
	protected $lang;

	/** @var \phpbb\log\log */
	protected $log;

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
	 * @param \phpbb\cache\driver\driver_interface	$cache		Cache object
	 * @param \phpbb\config\config					$config		Config object
	 * @param \phpbb\db\driver\driver_interface		$db			Database object
	 * @param \phpbb\acp\helper\controller			$helper		ACP Controller helper object
	 * @param \phpbb\language\language				$lang		Language object
	 * @param \phpbb\log\log						$log		Log object
	 * @param \phpbb\request\request				$request	Request object
	 * @param \phpbb\template\template				$template	Template object
	 * @param \phpbb\user							$user		User object
	 * @param string								$root_path	phpBB root path
	 * @param string								$php_ext	php File extension
	 * @param array									$tables		phpBB tables
	 */
	public function __construct(
		\phpbb\cache\driver\driver_interface $cache,
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\acp\helper\controller $helper,
		\phpbb\language\language $lang,
		\phpbb\log\log $log,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		$root_path,
		$php_ext,
		$tables
	)
	{
		$this->cache		= $cache;
		$this->config		= $config;
		$this->db			= $db;
		$this->helper		= $helper;
		$this->lang			= $lang;
		$this->log			= $log;
		$this->request		= $request;
		$this->template		= $template;
		$this->user			= $user;

		$this->root_path	= $root_path;
		$this->php_ext		= $php_ext;
		$this->tables		= $tables;
	}

	public function main()
	{
		$this->lang->add_lang('acp/bots');

		$mark	= $this->request->variable('mark', [0]);
		$bot_id	= $this->request->variable('id', 0);
		$submit = $this->request->is_set_post('submit');
		$action = $this->request->variable('action', '');
		$action = $this->request->is_set_post('add') ? 'add' : $action;

		$errors = [];

		$form_key = 'acp_bots';
		add_form_key($form_key);

		if ($submit && !check_form_key($form_key))
		{
			$errors[] = $this->lang->lang('FORM_INVALID');
		}

		// User wants to do something, how inconsiderate of them!
		switch ($action)
		{
			case 'activate':
			case 'deactivate':
				if ($bot_id || !empty($mark))
				{
					$sql = 'UPDATE ' . $this->tables['bots'] . '
						SET bot_active = ' . ($action === 'activate') . '
						WHERE ' . ($bot_id ? 'bot_id = ' . (int) $bot_id : $this->db->sql_in_set('bot_id', $mark));
					$this->db->sql_query($sql);
				}

				$this->cache->destroy('_bots');
			break;

			case 'delete':
				if ($bot_id || !empty($mark))
				{
					if (confirm_box(true))
					{
						$user_id_ary = $bot_name_ary = [];

						// We need to delete the relevant user, usergroup and bot entries ...
						$sql = 'SELECT bot_name, user_id
							FROM ' . $this->tables['bots'] . '
							WHERE ' . ($bot_id ? 'bot_id = ' . (int) $bot_id : $this->db->sql_in_set('bot_id', $mark));
						$result = $this->db->sql_query($sql);
						while ($row = $this->db->sql_fetchrow($result))
						{
							$user_id_ary[] = (int) $row['user_id'];
							$bot_name_ary[] = $row['bot_name'];
						}
						$this->db->sql_freeresult($result);

						$this->db->sql_transaction('begin');

						$sql = 'DELETE FROM ' . $this->tables['bots'] . '
							WHERE ' . ($bot_id ? 'bot_id = ' . (int) $bot_id : $this->db->sql_in_set('bot_id', $mark));
						$this->db->sql_query($sql);

						if (!empty($user_id_ary))
						{
							foreach ([$this->tables['users'], $this->tables['user_group']] as $table)
							{
								$sql = 'DELETE FROM ' . $table . '
									WHERE ' . $this->db->sql_in_set('user_id', $user_id_ary);
								$this->db->sql_query($sql);
							}
						}

						$this->db->sql_transaction('commit');

						$this->cache->destroy('_bots');

						$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_BOT_DELETE', false, [implode($this->lang->lang('COMMA_SEPARATOR'), $bot_name_ary)]);

						return $this->helper->message_back('BOT_DELETED', 'acp_bots');
					}
					else
					{
						confirm_box(false, $this->lang->lang('CONFIRM_OPERATION'), build_hidden_fields([
							'action'	=> $action,
							'id'		=> $bot_id,
							'mark'		=> $mark,
						]));

						return redirect($this->helper->route('acp_bots'));
					}
				}
			break;

			case 'edit':
			case 'add':
				if (!function_exists('user_update_name'))
				{
					include($this->root_path . 'includes/functions_user.' . $this->php_ext);
				}

				$bot_row = [
					'bot_name'		=> $this->request->variable('bot_name', '', true),
					'bot_agent'		=> $this->request->variable('bot_agent', ''),
					'bot_ip'		=> $this->request->variable('bot_ip', ''),
					'bot_active'	=> $this->request->variable('bot_active', true),
					'bot_lang'		=> $this->request->variable('bot_lang', $this->config['default_lang']),
					'bot_style'		=> $this->request->variable('bot_style' , $this->config['default_style']),
				];

				if ($submit)
				{
					if (!$bot_row['bot_agent'] && !$bot_row['bot_ip'])
					{
						$errors[] = $this->lang->lang('ERR_BOT_NO_MATCHES');
					}

					if ($bot_row['bot_ip'] && !preg_match('#^[\d\.,:]+$#', $bot_row['bot_ip']))
					{
						if (!$ip_list = gethostbynamel($bot_row['bot_ip']))
						{
							$errors[] = $this->lang->lang('ERR_BOT_NO_IP');
						}
						else
						{
							$bot_row['bot_ip'] = implode(',', $ip_list);
						}
					}

					$bot_row['bot_ip'] = str_replace(' ', '', $bot_row['bot_ip']);

					// Make sure the admin is not adding a bot with an user agent similar to his one
					if ($bot_row['bot_agent'] && substr($this->user->data['session_browser'], 0, 149) === substr($bot_row['bot_agent'], 0, 149))
					{
						$errors[] = $this->lang->lang('ERR_BOT_AGENT_MATCHES_UA');
					}

					$bot_name = false;

					if ($bot_id)
					{
						$sql = 'SELECT u.username_clean
							FROM ' . $this->tables['bots'] . ' b, ' . $this->tables['users'] . ' u
							WHERE u.user_id = b.user_id
								AND b.bot_id = ' . (int) $bot_id;
						$result = $this->db->sql_query($sql);
						$row = $this->db->sql_fetchrow($result);
						$this->db->sql_freeresult($result);

						if (!$bot_row)
						{
							$errors[] = $this->lang->lang('NO_BOT');
						}
						else
						{
							$bot_name = $row['username_clean'];
						}
					}
					if (!$this->validate_bot_name($bot_row['bot_name'], $bot_name))
					{
						$errors[] = $this->lang->lang('BOT_NAME_TAKEN');
					}

					if (empty($errors))
					{
						// New bot? Create a new user and group entry
						if ($action === 'add')
						{
							$sql = 'SELECT group_id, group_colour
								FROM ' . $this->tables['groups'] . "
								WHERE group_name = 'BOTS'
									AND group_type = " . GROUP_SPECIAL;
							$result = $this->db->sql_query($sql);
							$group_row = $this->db->sql_fetchrow($result);
							$this->db->sql_freeresult($result);

							if ($group_row === false)
							{
								throw new back_exception(404, 'NO_BOT_GROUP', ['acp_bots', 'action' => $action, 'id' => $bot_id]);
							}

							$user_id = user_add([
								'user_type'				=> (int) USER_IGNORE,
								'group_id'				=> (int) $group_row['group_id'],
								'username'				=> (string) $bot_row['bot_name'],
								'user_regdate'			=> time(),
								'user_password'			=> '',
								'user_colour'			=> (string) $group_row['group_colour'],
								'user_email'			=> '',
								'user_lang'				=> (string) $bot_row['bot_lang'],
								'user_style'			=> (int) $bot_row['bot_style'],
								'user_allow_massemail'	=> 0,
							]);

							$sql = 'INSERT INTO ' . $this->tables['bots'] . ' ' . $this->db->sql_build_array('INSERT', [
								'user_id'		=> (int) $user_id,
								'bot_name'		=> (string) $bot_row['bot_name'],
								'bot_active'	=> (int) $bot_row['bot_active'],
								'bot_agent'		=> (string) $bot_row['bot_agent'],
								'bot_ip'		=> (string) $bot_row['bot_ip'],
							]);
							$this->db->sql_query($sql);
						}
						else if ($bot_id)
						{
							$sql = 'SELECT user_id, bot_name
								FROM ' . $this->tables['bots'] . '
								WHERE bot_id = ' . (int) $bot_id;
							$result = $this->db->sql_query($sql);
							$row = $this->db->sql_fetchrow($result);
							$this->db->sql_freeresult($result);

							if ($row === false)
							{
								throw new back_exception(404, 'NO_BOT', ['acp_bots', 'action' => $action, 'id' => $bot_id]);
							}

							$sql_ary = [
								'user_style'	=> (int) $bot_row['bot_style'],
								'user_lang'		=> (string) $bot_row['bot_lang'],
							];

							if ($bot_row['bot_name'] !== $row['bot_name'])
							{
								$sql_ary['username'] = (string) $bot_row['bot_name'];
								$sql_ary['username_clean'] = (string) utf8_clean_string($bot_row['bot_name']);
							}

							$sql = 'UPDATE ' . $this->tables['users'] . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . " WHERE user_id = {$row['user_id']}";
							$this->db->sql_query($sql);

							$sql = 'UPDATE ' . $this->tables['bots'] . ' SET ' . $this->db->sql_build_array('UPDATE', [
								'bot_name'		=> (string) $bot_row['bot_name'],
								'bot_active'	=> (int) $bot_row['bot_active'],
								'bot_agent'		=> (string) $bot_row['bot_agent'],
								'bot_ip'		=> (string) $bot_row['bot_ip'],
							]) . ' WHERE bot_id = ' . (int) $bot_id;
							$this->db->sql_query($sql);

							// Updated username?
							if ($bot_row['bot_name'] !== $row['bot_name'])
							{
								user_update_name($row['bot_name'], $bot_row['bot_name']);
							}
						}

						$this->cache->destroy('_bots');

						$log_action = $action === 'add' ? 'ADDED' : 'UPDATED';

						$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_BOT_' . $log_action, false, [$bot_row['bot_name']]);

						return $this->helper->message_back('BOT_' . $log_action, 'acp_bots');
					}
				}
				else if ($bot_id)
				{
					$sql = 'SELECT b.*, u.user_lang, u.user_style
						FROM ' . $this->tables['bots'] . ' b, ' . $this->tables['users'] . ' u
						WHERE u.user_id = b.user_id
							AND b.bot_id = ' . (int) $bot_id;
					$result = $this->db->sql_query($sql);
					$bot_row = $this->db->sql_fetchrow($result);
					$this->db->sql_freeresult($result);

					if ($bot_row === false)
					{
						throw new back_exception(404, 'NO_BOT', ['acp_bots', 'action' => $action, 'id' => $bot_id]);
					}

					$bot_row['bot_lang'] = $bot_row['user_lang'];
					$bot_row['bot_style'] = $bot_row['user_style'];
					unset($bot_row['user_lang'], $bot_row['user_style']);
				}

				$s_active_options = '';
				foreach (['0' => 'NO', '1' => 'YES'] as $value => $lang)
				{
					$selected = ($bot_row['bot_active'] == $value) ? ' selected="selected"' : '';
					$s_active_options .= '<option value="' . $value . '"' . $selected . '>' . $this->lang->lang($lang) . '</option>';
				}

				$l_title = $action === 'edit' ? 'EDIT' : 'ADD';
				$s_error = !empty($errors);

				$this->template->assign_vars([
					'L_TITLE'		=> $this->lang->lang('BOT_' . $l_title),
					'U_ACTION'		=> $this->helper->route('acp_bots', ['action' => $action, 'id' => $bot_id]),
					'U_BACK'		=> $this->helper->route('acp_bots'),
					'ERROR_MSG'		=> $s_error ? implode('<br />', $errors) : '',

					'BOT_NAME'		=> $bot_row['bot_name'],
					'BOT_IP'		=> $bot_row['bot_ip'],
					'BOT_AGENT'		=> $bot_row['bot_agent'],

					'S_EDIT_BOT'		=> true,
					'S_ACTIVE_OPTIONS'	=> $s_active_options,
					'S_STYLE_OPTIONS'	=> style_select($bot_row['bot_style'], true),
					'S_LANG_OPTIONS'	=> language_select($bot_row['bot_lang']),
					'S_ERROR'			=> $s_error,
				]);

				return $this->helper->render('acp_bots.html', 'BOT_' . $l_title);
			break;
		}

		if ($this->request->is_ajax() && ($action === 'activate' || $action === 'deactivate'))
		{
			$json_response = new \phpbb\json_response;
			$json_response->send([
				'text'	=> $this->lang->lang('BOT_' . strtoupper($action)),
			]);
		}

		$s_options = '';
		$options = ['activate' => 'BOT_ACTIVATE', 'deactivate' => 'BOT_DEACTIVATE', 'delete' => 'DELETE'];
		foreach ($options as $value => $lang)
		{
			$s_options .= '<option value="' . $value . '">' . $this->lang->lang($lang) . '</option>';
		}

		$this->template->assign_vars([
			'U_ACTION'		=> $this->helper->route('acp_bots'),
			'S_BOT_OPTIONS'	=> $s_options,
		]);

		$sql = 'SELECT b.bot_id, b.bot_name, b.bot_active, u.user_lastvisit
			FROM ' . $this->tables['bots'] . ' b, ' . $this->tables['users'] . ' u
			WHERE u.user_id = b.user_id
			ORDER BY u.user_lastvisit DESC, b.bot_name ASC';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$active_lang = empty($row['bot_active']) ? 'BOT_ACTIVATE' : 'BOT_DEACTIVATE';
			$active_value = empty($row['bot_active']) ? 'activate' : 'deactivate';

			$this->template->assign_block_vars('bots', [
				'BOT_NAME'		=> $row['bot_name'],
				'BOT_ID'		=> $row['bot_id'],
				'LAST_VISIT'	=> $row['user_lastvisit'] ? $this->user->format_date($row['user_lastvisit']) : $this->lang->lang('BOT_NEVER'),

				'L_ACTIVATE_DEACTIVATE'	=> $this->lang->lang($active_lang),
				'U_ACTIVATE_DEACTIVATE'	=> $this->helper->route('acp_bots', ['action' => $active_value, 'id' => $row['bot_id']]),
				'U_DELETE'				=> $this->helper->route('acp_bots', ['action' => 'delete', 'id' => $row['bot_id']]),
				'U_EDIT'				=> $this->helper->route('acp_bots', ['action' => 'edit', 'id' => $row['bot_id']]),
			]);
		}
		$this->db->sql_freeresult($result);

		return $this->helper->render('acp_bots.html', 'ACP_BOTS');
	}

	/**
	 * Validate bot name against username table
	 *
	 * @param string		$new_name
	 * @param string|false	$old_name
	 * @return bool
	 */
	protected function validate_bot_name($new_name, $old_name = false)
	{
		if ($old_name && utf8_clean_string($new_name) === $old_name)
		{
			return true;
		}

		// Admins might want to use names otherwise forbidden, thus we only check for duplicates.
		$sql = 'SELECT username
			FROM ' . $this->tables['users'] . "
			WHERE username_clean = '" . $this->db->sql_escape(utf8_clean_string($new_name)) . "'";
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row === false;
	}
}
