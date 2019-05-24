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

class email
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\event\dispatcher */
	protected $dispatcher;

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
	 * @param \phpbb\config\config				$config			Config object
	 * @param \phpbb\db\driver\driver_interface	$db				Database object
	 * @param \phpbb\event\dispatcher			$dispatcher		Event dispatcher object
	 * @param \phpbb\acp\helper\controller		$helper			ACP Controller helper object
	 * @param \phpbb\language\language			$lang			Language object
	 * @param \phpbb\log\log					$log			Log object
	 * @param \phpbb\request\request			$request		Request object
	 * @param \phpbb\template\template			$template		Template object
	 * @param \phpbb\user						$user			User object
	 * @param string							$admin_path		phpBB admin path
	 * @param string							$root_path		phpBB root path
	 * @param string							$php_ext		php File extension
	 * @param array								$tables			phpBB tables
	 */
	public function __construct(
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\event\dispatcher $dispatcher,
		\phpbb\acp\helper\controller $helper,
		\phpbb\language\language $lang,
		\phpbb\log\log $log,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		$admin_path,
		$root_path,
		$php_ext,
		$tables
	)
	{
		$this->config		= $config;
		$this->db			= $db;
		$this->dispatcher	= $dispatcher;
		$this->helper		= $helper;
		$this->lang			= $lang;
		$this->log			= $log;
		$this->request		= $request;
		$this->template		= $template;
		$this->user			= $user;

		$this->admin_path	= $admin_path;
		$this->root_path	= $root_path;
		$this->php_ext		= $php_ext;
		$this->tables		= $tables;
	}

	function main()
	{
		$this->lang->add_lang('acp/email');

		$errors = [];
		$submit = $this->request->is_set_post('submit');

		$group_id	= $this->request->variable('g', 0);
		$subject	= $this->request->variable('subject', '', true);
		$message	= $this->request->variable('message', '', true);
		$usernames	= $this->request->variable('usernames', '', true);
		$usernames	= !empty($usernames) ? explode("\n", $usernames) : [];

		$form_key = 'acp_email';
		add_form_key($form_key);

		if ($submit)
		{
			if (!check_form_key($form_key))
			{
				$errors[] = $this->lang->lang('FORM_INVALID');
			}

			// Error checking needs to go here ... if no subject and/or no message then skip
			// over the send and return to the form
			$use_queue	= $this->request->is_set_post('send_immediately');
			$priority	= $this->request->variable('mail_priority_flag', MAIL_NORMAL_PRIORITY);

			if (!$subject)
			{
				$errors[] = $this->lang->lang('NO_EMAIL_SUBJECT');
			}

			if (!$message)
			{
				$errors[] = $this->lang->lang('NO_EMAIL_MESSAGE');
			}

			if (empty($errors))
			{
				if (!empty($usernames))
				{
					// If giving usernames the admin is able to email inactive users too...
					$sql_ary = [
						'SELECT'	=> 'username, user_email, user_jabber, user_notify_type, user_lang',
						'FROM'		=> [
							$this->tables['users']	=> '',
						],
						'WHERE'		=> $this->db->sql_in_set('username_clean', array_map('utf8_clean_string', $usernames)) . '
							AND user_allow_massemail = 1',
						'ORDER_BY'	=> 'user_lang, user_notify_type',
					];
				}
				else
				{
					if ($group_id)
					{
						$sql_ary = [
							'SELECT'	=> 'u.user_email, u.username, u.username_clean, u.user_lang, u.user_jabber, u.user_notify_type',
							'FROM'		=> [
								$this->tables['users']		=> 'u',
								$this->tables['user_group']	=> 'ug',
							],
							'WHERE'		=> 'ug.group_id = ' . (int) $group_id . '
								AND ug.user_pending = 0
								AND u.user_id = ug.user_id
								AND u.user_allow_massemail = 1
								AND ' . $this->db->sql_in_set('u.user_type', [USER_NORMAL, USER_FOUNDER]),
							'ORDER_BY'	=> 'u.user_lang, u.user_notify_type',
						];
					}
					else
					{
						$sql_ary = [
							'SELECT'	=> 'u.username, u.username_clean, u.user_email, u.user_jabber, u.user_lang, u.user_notify_type',
							'FROM'		=> [$this->tables['users']	=> 'u'],
							'WHERE'		=> 'u.user_allow_massemail = 1
								AND ' . $this->db->sql_in_set('u.user_type', [USER_NORMAL, USER_FOUNDER]),
							'ORDER_BY'	=> 'u.user_lang, u.user_notify_type',
						];
					}

					// Mail banned or not
					if (!$this->request->is_set('mail_banned_flag'))
					{
						$sql_ary['WHERE'] .= ' AND (b.ban_id IS NULL OR b.ban_exclude = 1)';
						$sql_ary['LEFT_JOIN'] = [
							[
								'FROM'	=> [$this->tables['banlist']	=> 'b'],
								'ON'	=> 'u.user_id = b.ban_userid',
							],
						];
					}
				}
				/**
				 * Modify sql query to change the list of users the email is sent to
				 *
				 * @event core.acp_email_modify_sql
				 * @var array	sql_ary		Array which is used to build the sql query
				 * @since 3.1.2-RC1
				 */
				$vars = ['sql_ary'];
				extract($this->dispatcher->trigger_event('core.acp_email_modify_sql', compact($vars)));

				$sql = $this->db->sql_build_query('SELECT', $sql_ary);
				$result = $this->db->sql_query($sql);
				$row = $this->db->sql_fetchrow($result);

				if ($row === false)
				{
					$this->db->sql_freeresult($result);

					throw new back_exception(400, 'NO_USER', 'acp_mass_email');
				}

				$i = $j = 0;

				// Send with BCC
				// Maximum number of bcc recipients
				$max_chunk_size = (int) $this->config['email_max_chunk_size'];
				$old_notify_type = $row['user_notify_type'];
				$old_lang = $row['user_lang'];
				$email_list = [];

				do
				{
					if (($row['user_notify_type'] == NOTIFY_EMAIL && $row['user_email']) ||
						($row['user_notify_type'] == NOTIFY_IM && $row['user_jabber']) ||
						($row['user_notify_type'] == NOTIFY_BOTH && ($row['user_email'] || $row['user_jabber'])))
					{
						if ($i === $max_chunk_size || $row['user_lang'] !== $old_lang || $row['user_notify_type'] !== $old_notify_type)
						{
							$i = 0;

							if (!empty($email_list))
							{
								$j++;
							}

							$old_lang = $row['user_lang'];
							$old_notify_type = $row['user_notify_type'];
						}

						$email_list[$j][$i]['name']		= $row['username'];
						$email_list[$j][$i]['lang']		= $row['user_lang'];
						$email_list[$j][$i]['method']	= $row['user_notify_type'];
						$email_list[$j][$i]['email']	= $row['user_email'];
						$email_list[$j][$i]['jabber']	= $row['user_jabber'];

						$i++;
					}
				}
				while ($row = $this->db->sql_fetchrow($result));
				$this->db->sql_freeresult($result);

				// Send the messages
				if (!class_exists('messenger'))
				{
					include($this->root_path . 'includes/functions_messenger.' . $this->php_ext);
				}

				if (!function_exists('get_group_name'))
				{
					include($this->root_path . 'includes/functions_user.' . $this->php_ext);
				}

				$messenger = new \messenger($use_queue);

				$error_send = false;

				$email_template = 'admin_send_email';
				$template_data = [
					'CONTACT_EMAIL' => phpbb_get_board_contact($this->config, $this->php_ext),
					'MESSAGE'		=> htmlspecialchars_decode($message),
				];
				$generate_log_entry = true;

				/**
				 * Modify email template data before the emails are sent
				 *
				 * @event core.acp_email_send_before
				 * @var string	email_template		The template to be used for sending the email
				 * @var string	subject				The subject of the email
				 * @var array	template_data		Array with template data assigned to email template
				 * @var bool	generate_log_entry	If false, no log entry will be created
				 * @var array	usernames			Usernames which will be displayed in log entry, if it will be created
				 * @var int		group_id			The group this email will be sent to
				 * @var bool	use_queue			If true, email queue will be used for sending
				 * @var int		priority			Priority of sent emails
				 * @since 3.1.3-RC1
				 */
				$vars = [
					'email_template',
					'subject',
					'template_data',
					'generate_log_entry',
					'usernames',
					'group_id',
					'use_queue',
					'priority',
				];
				extract($this->dispatcher->trigger_event('core.acp_email_send_before', compact($vars)));

				for ($i = 0, $size = count($email_list); $i < $size; $i++)
				{
					$used_lang = $email_list[$i][0]['lang'];
					$used_method = $email_list[$i][0]['method'];

					for ($j = 0, $list_size = count($email_list[$i]); $j < $list_size; $j++)
					{
						$email_row = $email_list[$i][$j];
						$function = count($email_list[$i]) === 1 ? 'to' : 'bcc';

						$messenger->$function($email_row['email'], $email_row['name']);
						$messenger->im($email_row['jabber'], $email_row['name']);
					}

					$messenger->template($email_template, $used_lang);

					$messenger->anti_abuse_headers($this->config, $this->user);

					$messenger->subject(htmlspecialchars_decode($subject));
					$messenger->set_mail_priority($priority);

					$messenger->assign_vars($template_data);

					if (!($messenger->send($used_method)))
					{
						$error_send = true;
					}
				}
				unset($email_list);

				$messenger->save_queue();

				if ($generate_log_entry)
				{
					$log_data = !empty($usernames)
						? implode($this->lang->lang('COMMA_SEPARATOR'), utf8_normalize_nfc($usernames))
						: ($group_id ? get_group_name($group_id) : $this->lang->lang('ALL_USERS'));

					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_MASS_EMAIL', false, [$log_data]);
				}

				if ($error_send === false)
				{
					return $this->helper->message_back('EMAIL_SENT' . ($use_queue ? '_QUEUE' : ''), 'acp_mass_email');
				}
				else
				{
					throw new back_exception(503, 'EMAIL_SEND_ERROR', 'acp_mass_email', ['<a href="' . $this->helper->route('acp_logs_error') . '">', '</a>']);
				}
			}
		}

		$exclude = [];

		// Exclude bots and guests...
		$sql = 'SELECT group_id
			FROM ' . $this->tables['groups'] . '
			WHERE ' . $this->db->sql_in_set('group_name', ['BOTS', 'GUESTS']);
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$exclude[] = $row['group_id'];
		}
		$this->db->sql_freeresult($result);

		$select_list = '<option value="0"' . (!$group_id ? ' selected="selected"' : '') . '>' . $this->lang->lang('ALL_USERS') . '</option>';
		$select_list .= group_select_options($group_id, $exclude);

		$s_priority_options = '<option value="' . MAIL_LOW_PRIORITY . '">' . $this->lang->lang('MAIL_LOW_PRIORITY') . '</option>';
		$s_priority_options .= '<option value="' . MAIL_NORMAL_PRIORITY . '" selected="selected">' . $this->lang->lang('MAIL_NORMAL_PRIORITY') . '</option>';
		$s_priority_options .= '<option value="' . MAIL_HIGH_PRIORITY . '">' . $this->lang->lang('MAIL_HIGH_PRIORITY') . '</option>';

		$s_errors = !empty($errors);

		$template_data = [
			'S_WARNING'				=> $s_errors,
			'WARNING_MSG'			=> $s_errors ? implode('<br />', $errors) : '',

			'MESSAGE'				=> $message,
			'SUBJECT'				=> $subject,
			'USERNAMES'				=> implode("\n", $usernames),

			'S_PRIORITY_OPTIONS'	=> $s_priority_options,
			'S_GROUP_OPTIONS'		=> $select_list,

			'U_ACTION'				=> $this->helper->route('acp_mass_email'),
			'U_FIND_USERNAME'		=> append_sid("{$this->root_path}memberlist.$this->php_ext", 'mode=searchuser&amp;form=acp_email&amp;field=usernames'),
		];

		/**
		 * Modify custom email template data before we display the form
		 *
		 * @event core.acp_email_display
		 * @var array	template_data		Array with template data assigned to email template
		 * @var array	exclude				Array with groups which are excluded from group selection
		 * @var array	usernames			Usernames which will be displayed in form
		 * @since 3.1.4-RC1
		 */
		$vars = ['template_data', 'exclude', 'usernames'];
		extract($this->dispatcher->trigger_event('core.acp_email_display', compact($vars)));

		$this->template->assign_vars($template_data);

		return $this->helper->render('acp_email.html', 'ACP_MASS_EMAIL');
	}
}
