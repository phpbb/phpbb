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

class acp_email
{
	var $u_action;

	function main($id, $mode)
	{
		global $config, $db, $user, $template, $phpbb_log, $request;
		global $phpbb_root_path, $phpbb_admin_path, $phpEx, $phpbb_dispatcher;

		$user->add_lang('acp/email');
		$this->tpl_name = 'acp_email';
		$this->page_title = 'ACP_MASS_EMAIL';

		$form_key = 'acp_email';
		add_form_key($form_key);

		// Set some vars
		$submit = (isset($_POST['submit'])) ? true : false;
		$error = array();

		$usernames	= $request->variable('usernames', '', true);
		$usernames	= (!empty($usernames)) ? explode("\n", $usernames) : array();
		$group_id	= $request->variable('g', 0);
		$subject	= $request->variable('subject', '', true);
		$message	= $request->variable('message', '', true);

		// Do the job ...
		if ($submit)
		{
			// Error checking needs to go here ... if no subject and/or no message then skip
			// over the send and return to the form
			$use_queue		= (isset($_POST['send_immediately'])) ? false : true;
			$priority		= $request->variable('mail_priority_flag', MAIL_NORMAL_PRIORITY);

			if (!check_form_key($form_key))
			{
				$error[] = $user->lang['FORM_INVALID'];
			}

			if (!$subject)
			{
				$error[] = $user->lang['NO_EMAIL_SUBJECT'];
			}

			if (!$message)
			{
				$error[] = $user->lang['NO_EMAIL_MESSAGE'];
			}

			if (!count($error))
			{
				if (!empty($usernames))
				{
					// If giving usernames the admin is able to email inactive users too...
					$sql_ary = array(
						'SELECT'	=> 'username, user_email, user_jabber, user_notify_type, user_lang',
						'FROM'		=> array(
							USERS_TABLE		=> '',
						),
						'WHERE'		=> $db->sql_in_set('username_clean', array_map('utf8_clean_string', $usernames)) . '
							AND user_allow_massemail = 1',
						'ORDER_BY'	=> 'user_lang, user_notify_type',
					);
				}
				else
				{
					if ($group_id)
					{
						$sql_ary = array(
							'SELECT'	=> 'u.user_email, u.username, u.username_clean, u.user_lang, u.user_jabber, u.user_notify_type',
							'FROM'		=> array(
								USERS_TABLE			=> 'u',
								USER_GROUP_TABLE	=> 'ug',
							),
							'WHERE'		=> 'ug.group_id = ' . $group_id . '
								AND ug.user_pending = 0
								AND u.user_id = ug.user_id
								AND u.user_allow_massemail = 1
								AND u.user_type IN (' . USER_NORMAL . ', ' . USER_FOUNDER . ')',
							'ORDER_BY'	=> 'u.user_lang, u.user_notify_type',
						);
					}
					else
					{
						$sql_ary = array(
							'SELECT'	=> 'u.username, u.username_clean, u.user_email, u.user_jabber, u.user_lang, u.user_notify_type',
							'FROM'		=> array(
								USERS_TABLE	=> 'u',
							),
							'WHERE'		=> 'u.user_allow_massemail = 1
								AND u.user_type IN (' . USER_NORMAL . ', ' . USER_FOUNDER . ')',
							'ORDER_BY'	=> 'u.user_lang, u.user_notify_type',
						);
					}

					// Mail banned or not
					if (!isset($_REQUEST['mail_banned_flag']))
					{
						$sql_ary['WHERE'] .= ' AND (b.ban_id IS NULL
						        OR b.ban_exclude = 1)';
						$sql_ary['LEFT_JOIN'] = array(
							array(
								'FROM'	=> array(
									BANLIST_TABLE	=> 'b',
								),
								'ON'	=> 'u.user_id = b.ban_userid',
							),
						);
					}
				}
				/**
				* Modify sql query to change the list of users the email is sent to
				*
				* @event core.acp_email_modify_sql
				* @var	array	sql_ary		Array which is used to build the sql query
				* @since 3.1.2-RC1
				*/
				$vars = array('sql_ary');
				extract($phpbb_dispatcher->trigger_event('core.acp_email_modify_sql', compact($vars)));

				$sql = $db->sql_build_query('SELECT', $sql_ary);
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);

				if (!$row)
				{
					$db->sql_freeresult($result);
					trigger_error($user->lang['NO_USER'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$i = $j = 0;

				// Send with BCC
				// Maximum number of bcc recipients
				$max_chunk_size = (int) $config['email_max_chunk_size'];
				$email_list = array();
				$old_lang = $row['user_lang'];
				$old_notify_type = $row['user_notify_type'];

				do
				{
					if (($row['user_notify_type'] == NOTIFY_EMAIL && $row['user_email']) ||
						($row['user_notify_type'] == NOTIFY_IM && $row['user_jabber']) ||
						($row['user_notify_type'] == NOTIFY_BOTH && ($row['user_email'] || $row['user_jabber'])))
					{
						if ($i == $max_chunk_size || $row['user_lang'] != $old_lang || $row['user_notify_type'] != $old_notify_type)
						{
							$i = 0;

							if (count($email_list))
							{
								$j++;
							}

							$old_lang = $row['user_lang'];
							$old_notify_type = $row['user_notify_type'];
						}

						$email_list[$j][$i]['lang']		= $row['user_lang'];
						$email_list[$j][$i]['method']	= $row['user_notify_type'];
						$email_list[$j][$i]['email']	= $row['user_email'];
						$email_list[$j][$i]['name']		= $row['username'];
						$email_list[$j][$i]['jabber']	= $row['user_jabber'];
						$i++;
					}
				}
				while ($row = $db->sql_fetchrow($result));
				$db->sql_freeresult($result);

				// Send the messages
				if (!class_exists('messenger'))
				{
					include($phpbb_root_path . 'includes/functions_messenger.' . $phpEx);
				}

				if (!function_exists('get_group_name'))
				{
					include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
				}
				$messenger = new messenger($use_queue);

				$errored = false;

				$email_template = 'admin_send_email';
				$template_data = array(
					'CONTACT_EMAIL' => phpbb_get_board_contact($config, $phpEx),
					'MESSAGE'		=> htmlspecialchars_decode($message),
				);
				$generate_log_entry = true;

				/**
				* Modify email template data before the emails are sent
				*
				* @event core.acp_email_send_before
				* @var	string	email_template		The template to be used for sending the email
				* @var	string	subject				The subject of the email
				* @var	array	template_data		Array with template data assigned to email template
				* @var	bool	generate_log_entry	If false, no log entry will be created
				* @var	array	usernames			Usernames which will be displayed in log entry, if it will be created
				* @var	int		group_id			The group this email will be sent to
				* @var	bool	use_queue			If true, email queue will be used for sending
				* @var	int		priority			Priority of sent emails
				* @since 3.1.3-RC1
				*/
				$vars = array(
					'email_template',
					'subject',
					'template_data',
					'generate_log_entry',
					'usernames',
					'group_id',
					'use_queue',
					'priority',
				);
				extract($phpbb_dispatcher->trigger_event('core.acp_email_send_before', compact($vars)));

				for ($i = 0, $size = count($email_list); $i < $size; $i++)
				{
					$used_lang = $email_list[$i][0]['lang'];
					$used_method = $email_list[$i][0]['method'];

					for ($j = 0, $list_size = count($email_list[$i]); $j < $list_size; $j++)
					{
						$email_row = $email_list[$i][$j];

						$messenger->{((count($email_list[$i]) == 1) ? 'to' : 'bcc')}($email_row['email'], $email_row['name']);
						$messenger->im($email_row['jabber'], $email_row['name']);
					}

					$messenger->template($email_template, $used_lang);

					$messenger->anti_abuse_headers($config, $user);

					$messenger->subject(htmlspecialchars_decode($subject));
					$messenger->set_mail_priority($priority);

					$messenger->assign_vars($template_data);

					if (!($messenger->send($used_method)))
					{
						$errored = true;
					}
				}
				unset($email_list);

				$messenger->save_queue();

				if ($generate_log_entry)
				{
					if (!empty($usernames))
					{
						$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_MASS_EMAIL', false, array(implode(', ', utf8_normalize_nfc($usernames))));
					}
					else
					{
						if ($group_id)
						{
							$group_name = get_group_name($group_id);
						}
						else
						{
							// Not great but the logging routine doesn't cope well with localising on the fly
							$group_name = $user->lang['ALL_USERS'];
						}

						$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_MASS_EMAIL', false, array($group_name));
					}
				}

				if (!$errored)
				{
					$message = ($use_queue) ? $user->lang['EMAIL_SENT_QUEUE'] : $user->lang['EMAIL_SENT'];
					trigger_error($message . adm_back_link($this->u_action));
				}
				else
				{
					$message = sprintf($user->lang['EMAIL_SEND_ERROR'], '<a href="' . append_sid("{$phpbb_admin_path}index.$phpEx", 'i=logs&amp;mode=critical') . '">', '</a>');
					trigger_error($message . adm_back_link($this->u_action), E_USER_WARNING);
				}
			}
		}

		// Exclude bots and guests...
		$sql = 'SELECT group_id
			FROM ' . GROUPS_TABLE . "
			WHERE group_name IN ('BOTS', 'GUESTS')";
		$result = $db->sql_query($sql);

		$exclude = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$exclude[] = $row['group_id'];
		}
		$db->sql_freeresult($result);

		$select_list = '<option value="0"' . ((!$group_id) ? ' selected="selected"' : '') . '>' . $user->lang['ALL_USERS'] . '</option>';
		$select_list .= group_select_options($group_id, $exclude);

		$s_priority_options = '<option value="' . MAIL_LOW_PRIORITY . '">' . $user->lang['MAIL_LOW_PRIORITY'] . '</option>';
		$s_priority_options .= '<option value="' . MAIL_NORMAL_PRIORITY . '" selected="selected">' . $user->lang['MAIL_NORMAL_PRIORITY'] . '</option>';
		$s_priority_options .= '<option value="' . MAIL_HIGH_PRIORITY . '">' . $user->lang['MAIL_HIGH_PRIORITY'] . '</option>';

		$template_data = array(
			'S_WARNING'				=> (count($error)) ? true : false,
			'WARNING_MSG'			=> (count($error)) ? implode('<br />', $error) : '',
			'U_ACTION'				=> $this->u_action,
			'S_GROUP_OPTIONS'		=> $select_list,
			'USERNAMES'				=> implode("\n", $usernames),
			'U_FIND_USERNAME'		=> append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=searchuser&amp;form=acp_email&amp;field=usernames'),
			'SUBJECT'				=> $subject,
			'MESSAGE'				=> $message,
			'S_PRIORITY_OPTIONS'	=> $s_priority_options,
		);

		/**
		* Modify custom email template data before we display the form
		*
		* @event core.acp_email_display
		* @var	array	template_data		Array with template data assigned to email template
		* @var	array	exclude				Array with groups which are excluded from group selection
		* @var	array	usernames			Usernames which will be displayed in form
		*
		* @since 3.1.4-RC1
		*/
		$vars = array('template_data', 'exclude', 'usernames');
		extract($phpbb_dispatcher->trigger_event('core.acp_email_display', compact($vars)));

		$template->assign_vars($template_data);
	}
}
