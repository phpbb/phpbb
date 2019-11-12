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

class users
{
	var $u_action;
	var $p_master;

	function __construct($p_master)
	{
		$this->p_master = $p_master;
	}

	public function main($id, $mode)
	{
		$this->language->add_lang(['posting', 'ucp', 'acp/users']);
		$this->tpl_name = 'acp_users';

		$error		= [];
		$username	= $this->request->variable('username', '', true);
		$user_id	= $this->request->variable('u', 0);
		$action		= $this->request->variable('action', '');

		// Get referer to redirect user to the appropriate page after delete action
		$redirect		= $this->request->variable('redirect', '');
		$redirect_tag	= "redirect=$redirect";
		$redirect_url	= append_sid("{$this->admin_path}index.$this->php_ext", "i=$redirect");

		$submit		= ($this->request->is_set_post('update') && !$this->request->is_set_post('cancel')) ? true : false;

		$form_name = 'acp_users';
		add_form_key($form_name);

		// Whois (special case)
		if ($action == 'whois')
		{
			if (!function_exists('user_get_id_name'))
			{
				include($this->root_path . 'includes/functions_user.' . $this->php_ext);
			}

			$this->page_title = 'WHOIS';
			$this->tpl_name = 'simple_body';

			$user_ip = phpbb_ip_normalise($this->request->variable('user_ip', ''));
			$domain = gethostbyaddr($user_ip);
			$ipwhois = user_ipwhois($user_ip);

			$this->template->assign_vars([
				'MESSAGE_TITLE'		=> sprintf($this->language->lang('IP_WHOIS_FOR'), $domain),
				'MESSAGE_TEXT'		=> nl2br($ipwhois)]
			);

			return;
		}

		// Show user selection mask
		if (!$username && !$user_id)
		{
			$this->page_title = 'SELECT_USER';

			$this->template->assign_vars([
				'U_ACTION'			=> $this->u_action,
				'ANONYMOUS_USER_ID'	=> ANONYMOUS,

				'S_SELECT_USER'		=> true,
				'U_FIND_USERNAME'	=> append_sid("{$this->root_path}memberlist.$this->php_ext", 'mode=searchuser&amp;form=select_user&amp;field=username&amp;select_single=true'),
			]);

			return;
		}

		if (!$user_id)
		{
			$sql = 'SELECT user_id
				FROM ' . USERS_TABLE . "
				WHERE username_clean = '" . $this->db->sql_escape(utf8_clean_string($username)) . "'";
			$result = $this->db->sql_query($sql);
			$user_id = (int) $this->db->sql_fetchfield('user_id');
			$this->db->sql_freeresult($result);

			if (!$user_id)
			{
				trigger_error($this->language->lang('NO_USER') . adm_back_link($this->u_action), E_USER_WARNING);
			}
		}

		// Generate content for all modes
		$sql = 'SELECT u.*, s.*
			FROM ' . USERS_TABLE . ' u
				LEFT JOIN ' . SESSIONS_TABLE . ' s ON (s.session_user_id = u.user_id)
			WHERE u.user_id = ' . $user_id . '
			ORDER BY s.session_time DESC';
		$result = $this->db->sql_query_limit($sql, 1);
		$user_row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$user_row)
		{
			trigger_error($this->language->lang('NO_USER') . adm_back_link($this->u_action), E_USER_WARNING);
		}

		// Generate overall "header" for user admin
		$s_form_options = '';

		// Build modes dropdown list
		$sql = 'SELECT module_mode, module_auth
			FROM ' . MODULES_TABLE . "
			WHERE module_basename = 'acp_users'
				AND module_enabled = 1
				AND module_class = 'acp'
			ORDER BY left_id, module_mode";
		$result = $this->db->sql_query($sql);

		$dropdown_modes = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			if (!$this->p_master->module_auth_self($row['module_auth']))
			{
				continue;
			}

			$dropdown_modes[$row['module_mode']] = true;
		}
		$this->db->sql_freeresult($result);

		foreach ($dropdown_modes as $module_mode => $null)
		{
			$selected = ($mode == $module_mode) ? ' selected="selected"' : '';
			$s_form_options .= '<option value="' . $module_mode . '"' . $selected . '>' . $this->language->lang('ACP_USER_' . strtoupper($module_mode)) . '</option>';
		}

		$this->template->assign_vars([
			'U_BACK'			=> (empty($redirect)) ? $this->u_action : $redirect_url,
			'U_MODE_SELECT'		=> append_sid("{$this->admin_path}index.$this->php_ext", "i=$id&amp;u=$user_id"),
			'U_ACTION'			=> $this->u_action . '&amp;u=' . $user_id . ((empty($redirect)) ? '' : '&amp;' . $redirect_tag),
			'S_FORM_OPTIONS'	=> $s_form_options,
			'MANAGED_USERNAME'	=> $user_row['username']]
		);

		// Prevent normal users/admins change/view founders if they are not a founder by themselves
		if ($this->user->data['user_type'] != USER_FOUNDER && $user_row['user_type'] == USER_FOUNDER)
		{
			trigger_error($this->language->lang('NOT_MANAGE_FOUNDER') . adm_back_link($this->u_action), E_USER_WARNING);
		}

		$this->page_title = $user_row['username'] . ' :: ' . $this->language->lang('ACP_USER_' . strtoupper($mode));

		switch ($mode)
		{
			case 'overview':

				if (!function_exists('user_get_id_name'))
				{
					include($this->root_path . 'includes/functions_user.' . $this->php_ext);
				}

				$this->language->add_lang('acp/ban');

				$delete			= $this->request->variable('delete', 0);
				$delete_type	= $this->request->variable('delete_type', '');
				$ip				= $this->request->variable('ip', 'ip');

				/**
				 * Run code at beginning of ACP users overview
				 *
				 * @event core.acp_users_overview_before
				 * @var array	user_row	Current user data
				 * @var string	mode		Active module
				 * @var string	action		Module that should be run
				 * @var bool	submit		Do we display the form only or did the user press submit
				 * @var array	error		Array holding error messages
				 * @since 3.1.3-RC1
				 */
				$vars = ['user_row', 'mode', 'action', 'submit', 'error'];
				extract($this->dispatcher->trigger_event('core.acp_users_overview_before', compact($vars)));

				if ($submit)
				{
					if ($delete)
					{
						if (!$this->auth->acl_get('a_userdel'))
						{
							send_status_line(403, 'Forbidden');
							trigger_error($this->language->lang('NO_AUTH_OPERATION') . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
						}

						// Check if the user wants to remove himself or the guest user account
						if ($user_id == ANONYMOUS)
						{
							trigger_error($this->language->lang('CANNOT_REMOVE_ANONYMOUS') . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
						}

						// Founders can not be deleted.
						if ($user_row['user_type'] == USER_FOUNDER)
						{
							trigger_error($this->language->lang('CANNOT_REMOVE_FOUNDER') . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
						}

						if ($user_id == $this->user->data['user_id'])
						{
							trigger_error($this->language->lang('CANNOT_REMOVE_YOURSELF') . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
						}

						if ($delete_type)
						{
							if (confirm_box(true))
							{
								user_delete($delete_type, $user_id, $user_row['username']);

								$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_USER_DELETED', false, [$user_row['username']]);
								trigger_error($this->language->lang('USER_DELETED') . adm_back_link(
										(empty($redirect)) ? $this->u_action : $redirect_url
									)
								);
							}
							else
							{
								$delete_confirm_hidden_fields = [
									'u'				=> $user_id,
									'i'				=> $id,
									'mode'			=> $mode,
									'action'		=> $action,
									'update'		=> true,
									'delete'		=> 1,
									'delete_type'	=> $delete_type,
								];

								// Checks if the redirection page is specified
								if (!empty($redirect))
								{
									$delete_confirm_hidden_fields['redirect'] = $redirect;
								}

								confirm_box(false, $this->language->lang('CONFIRM_OPERATION'), build_hidden_fields($delete_confirm_hidden_fields));
							}
						}
						else
						{
							trigger_error($this->language->lang('NO_MODE') . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
						}
					}

					// Handle quicktool actions
					switch ($action)
					{
						case 'banuser':
						case 'banemail':
						case 'banip':

							if ($user_id == $this->user->data['user_id'])
							{
								trigger_error($this->language->lang('CANNOT_BAN_YOURSELF') . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
							}

							if ($user_id == ANONYMOUS)
							{
								trigger_error($this->language->lang('CANNOT_BAN_ANONYMOUS') . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
							}

							if ($user_row['user_type'] == USER_FOUNDER)
							{
								trigger_error($this->language->lang('CANNOT_BAN_FOUNDER') . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
							}

							if (!check_form_key($form_name))
							{
								trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
							}

							$ban = [];

							switch ($action)
							{
								case 'banuser':
									$ban[] = $user_row['username'];
									$reason = 'USER_ADMIN_BAN_NAME_REASON';
								break;

								case 'banemail':
									$ban[] = $user_row['user_email'];
									$reason = 'USER_ADMIN_BAN_EMAIL_REASON';
								break;

								case 'banip':
									$ban[] = $user_row['user_ip'];

									$sql = 'SELECT DISTINCT poster_ip
										FROM ' . POSTS_TABLE . "
										WHERE poster_id = $user_id";
									$result = $this->db->sql_query($sql);

									while ($row = $this->db->sql_fetchrow($result))
									{
										$ban[] = $row['poster_ip'];
									}
									$this->db->sql_freeresult($result);

									$reason = 'USER_ADMIN_BAN_IP_REASON';
								break;
							}

							$ban_reason = $this->request->variable('ban_reason', $this->language->lang($reason), true);
							$ban_give_reason = $this->request->variable('ban_give_reason', '', true);

							// Log not used at the moment, we simply utilize the ban function.
							$result = user_ban(substr($action, 3), $ban, 0, 0, 0, $ban_reason, $ban_give_reason);

							trigger_error((($result === false) ? $this->language->lang('BAN_ALREADY_ENTERED') : $this->language->lang('BAN_SUCCESSFUL')) . adm_back_link($this->u_action . '&amp;u=' . $user_id));

						break;

						case 'reactivate':

							if ($user_id == $this->user->data['user_id'])
							{
								trigger_error($this->language->lang('CANNOT_FORCE_REACT_YOURSELF') . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
							}

							if (!check_form_key($form_name))
							{
								trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
							}

							if ($user_row['user_type'] == USER_FOUNDER)
							{
								trigger_error($this->language->lang('CANNOT_FORCE_REACT_FOUNDER') . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
							}

							if ($user_row['user_type'] == USER_IGNORE)
							{
								trigger_error($this->language->lang('CANNOT_FORCE_REACT_BOT') . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
							}

							if ($this->config['email_enable'])
							{
								if (!class_exists('messenger'))
								{
									include($this->root_path . 'includes/functions_messenger.' . $this->php_ext);
								}

								$server_url = generate_board_url();

								$user_actkey = gen_rand_string(mt_rand(6, 10));
								$email_template = ($user_row['user_type'] == USER_NORMAL) ? 'user_reactivate_account' : 'user_resend_inactive';

								if ($user_row['user_type'] == USER_NORMAL)
								{
									user_active_flip('deactivate', $user_id, INACTIVE_REMIND);
								}
								else
								{
									// Grabbing the last confirm key - we only send a reminder
									$sql = 'SELECT user_actkey
										FROM ' . USERS_TABLE . '
										WHERE user_id = ' . $user_id;
									$result = $this->db->sql_query($sql);
									$user_activation_key = (string) $this->db->sql_fetchfield('user_actkey');
									$this->db->sql_freeresult($result);

									$user_actkey = empty($user_activation_key) ? $user_actkey : $user_activation_key;
								}

								if ($user_row['user_type'] == USER_NORMAL || empty($user_activation_key))
								{
									$sql = 'UPDATE ' . USERS_TABLE . "
										SET user_actkey = '" . $this->db->sql_escape($user_actkey) . "'
										WHERE user_id = $user_id";
									$this->db->sql_query($sql);
								}

								$messenger = new messenger(false);

								$messenger->template($email_template, $user_row['user_lang']);

								$messenger->set_addresses($user_row);

								$messenger->anti_abuse_headers($config, $user);

								$messenger->assign_vars([
									'WELCOME_MSG'	=> htmlspecialchars_decode(sprintf($this->language->lang('WELCOME_SUBJECT'), $this->config['sitename'])),
									'USERNAME'		=> htmlspecialchars_decode($user_row['username']),
									'U_ACTIVATE'	=> "$server_url/ucp.$this->php_ext?mode=activate&u={$user_row['user_id']}&k=$user_actkey"]
								);

								$messenger->send(NOTIFY_EMAIL);

								$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_USER_REACTIVATE', false, [$user_row['username']]);
								$this->log->add('user', $this->user->data['user_id'], $this->user->ip, 'LOG_USER_REACTIVATE_USER', false, [
									'reportee_id' => $user_id,
								]);

								trigger_error($this->language->lang('FORCE_REACTIVATION_SUCCESS') . adm_back_link($this->u_action . '&amp;u=' . $user_id));
							}

						break;

						case 'active':

							if ($user_id == $this->user->data['user_id'])
							{
								// It is only deactivation since the user is already activated (else he would not have reached this page)
								trigger_error($this->language->lang('CANNOT_DEACTIVATE_YOURSELF') . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
							}

							if (!check_form_key($form_name))
							{
								trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
							}

							if ($user_row['user_type'] == USER_FOUNDER)
							{
								trigger_error($this->language->lang('CANNOT_DEACTIVATE_FOUNDER') . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
							}

							if ($user_row['user_type'] == USER_IGNORE)
							{
								trigger_error($this->language->lang('CANNOT_DEACTIVATE_BOT') . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
							}

							user_active_flip('flip', $user_id);

							if ($user_row['user_type'] == USER_INACTIVE)
							{
								if ($this->config['require_activation'] == USER_ACTIVATION_ADMIN)
								{
									/* @var $phpbb_notifications \phpbb\notification\manager */
									$phpbb_notifications = $phpbb_container->get('notification_manager');
									$this->notifications_manager->delete_notifications('notification.type.admin_activate_user', $user_row['user_id']);

									if (!class_exists('messenger'))
									{
										include($this->root_path . 'includes/functions_messenger.' . $this->php_ext);
									}

									$messenger = new messenger(false);

									$messenger->template('admin_welcome_activated', $user_row['user_lang']);

									$messenger->set_addresses($user_row);

									$messenger->anti_abuse_headers($config, $user);

									$messenger->assign_vars([
										'USERNAME'	=> htmlspecialchars_decode($user_row['username'])]
									);

									$messenger->send(NOTIFY_EMAIL);
								}
							}

							$message = ($user_row['user_type'] == USER_INACTIVE) ? 'USER_ADMIN_ACTIVATED' : 'USER_ADMIN_DEACTIVED';
							$log = ($user_row['user_type'] == USER_INACTIVE) ? 'LOG_USER_ACTIVE' : 'LOG_USER_INACTIVE';

							$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, $log, false, [$user_row['username']]);
							$this->log->add('user', $this->user->data['user_id'], $this->user->ip, $log . '_USER', false, [
								'reportee_id' => $user_id,
							]);

							trigger_error($this->language->lang($message) . adm_back_link($this->u_action . '&amp;u=' . $user_id));

						break;

						case 'delsig':

							if (!check_form_key($form_name))
							{
								trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
							}

							$sql_ary = [
								'user_sig'					=> '',
								'user_sig_bbcode_uid'		=> '',
								'user_sig_bbcode_bitfield'	=> '',
							];

							$sql = 'UPDATE ' . USERS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . "
								WHERE user_id = $user_id";
							$this->db->sql_query($sql);

							$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_USER_DEL_SIG', false, [$user_row['username']]);
							$this->log->add('user', $this->user->data['user_id'], $this->user->ip, 'LOG_USER_DEL_SIG_USER', false, [
								'reportee_id' => $user_id,
							]);

							trigger_error($this->language->lang('USER_ADMIN_SIG_REMOVED') . adm_back_link($this->u_action . '&amp;u=' . $user_id));

						break;

						case 'delavatar':

							if (!check_form_key($form_name))
							{
								trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
							}

							// Delete old avatar if present
							/* @var $phpbb_avatar_manager \phpbb\avatar\manager */
							$phpbb_avatar_manager = $phpbb_container->get('avatar.manager');
							$this->avatar_manager->handle_avatar_delete($db, $user, $this->avatar_manager->clean_row($user_row, 'user'), USERS_TABLE, 'user_');

							$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_USER_DEL_AVATAR', false, [$user_row['username']]);
							$this->log->add('user', $this->user->data['user_id'], $this->user->ip, 'LOG_USER_DEL_AVATAR_USER', false, [
								'reportee_id' => $user_id,
							]);

							trigger_error($this->language->lang('USER_ADMIN_AVATAR_REMOVED') . adm_back_link($this->u_action . '&amp;u=' . $user_id));
						break;

						case 'delposts':

							if (confirm_box(true))
							{
								// Delete posts, attachments, etc.
								delete_posts('poster_id', $user_id);

								$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_USER_DEL_POSTS', false, [$user_row['username']]);
								trigger_error($this->language->lang('USER_POSTS_DELETED') . adm_back_link($this->u_action . '&amp;u=' . $user_id));
							}
							else
							{
								confirm_box(false, $this->language->lang('CONFIRM_OPERATION'), build_hidden_fields([
									'u'				=> $user_id,
									'i'				=> $id,
									'mode'			=> $mode,
									'action'		=> $action,
									'update'		=> true])
								);
							}

						break;

						case 'delattach':

							if (confirm_box(true))
							{
								/** @var \phpbb\attachment\manager $attachment_manager */
								$attachment_manager = $phpbb_container->get('attachment.manager');
								$attachment_manager->delete('user', $user_id);
								unset($attachment_manager);

								$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_USER_DEL_ATTACH', false, [$user_row['username']]);
								trigger_error($this->language->lang('USER_ATTACHMENTS_REMOVED') . adm_back_link($this->u_action . '&amp;u=' . $user_id));
							}
							else
							{
								confirm_box(false, $this->language->lang('CONFIRM_OPERATION'), build_hidden_fields([
									'u'				=> $user_id,
									'i'				=> $id,
									'mode'			=> $mode,
									'action'		=> $action,
									'update'		=> true])
								);
							}

						break;

						case 'deloutbox':

							if (confirm_box(true))
							{
								$msg_ids = [];
								$lang = 'EMPTY';

								$sql = 'SELECT msg_id
									FROM ' . PRIVMSGS_TO_TABLE . "
									WHERE author_id = $user_id
										AND folder_id = " . PRIVMSGS_OUTBOX;
								$result = $this->db->sql_query($sql);

								if ($row = $this->db->sql_fetchrow($result))
								{
									if (!function_exists('delete_pm'))
									{
										include($this->root_path . 'includes/functions_privmsgs.' . $this->php_ext);
									}

									do
									{
										$msg_ids[] = (int) $row['msg_id'];
									}
									while ($row = $this->db->sql_fetchrow($result));

									$this->db->sql_freeresult($result);

									delete_pm($user_id, $msg_ids, PRIVMSGS_OUTBOX);

									$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_USER_DEL_OUTBOX', false, [$user_row['username']]);

									$lang = 'EMPTIED';
								}
								$this->db->sql_freeresult($result);

								trigger_error($this->language->lang('USER_OUTBOX_' . $lang) . adm_back_link($this->u_action . '&amp;u=' . $user_id));
							}
							else
							{
								confirm_box(false, $this->language->lang('CONFIRM_OPERATION'), build_hidden_fields([
									'u'				=> $user_id,
									'i'				=> $id,
									'mode'			=> $mode,
									'action'		=> $action,
									'update'		=> true])
								);
							}
						break;

						case 'moveposts':

							if (!check_form_key($form_name))
							{
								trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
							}

							$this->language->add_lang('acp/forums');

							$new_forum_id = $this->request->variable('new_f', 0);

							if (!$new_forum_id)
							{
								$this->page_title = 'USER_ADMIN_MOVE_POSTS';

								$this->template->assign_vars([
									'S_SELECT_FORUM'		=> true,
									'U_ACTION'				=> $this->u_action . "&amp;action=$action&amp;u=$user_id",
									'U_BACK'				=> $this->u_action . "&amp;u=$user_id",
									'S_FORUM_OPTIONS'		=> make_forum_select(false, false, false, true)]
								);

								return;
							}

							// Is the new forum postable to?
							$sql = 'SELECT forum_name, forum_type
								FROM ' . FORUMS_TABLE . "
								WHERE forum_id = $new_forum_id";
							$result = $this->db->sql_query($sql);
							$forum_info = $this->db->sql_fetchrow($result);
							$this->db->sql_freeresult($result);

							if (!$forum_info)
							{
								trigger_error($this->language->lang('NO_FORUM') . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
							}

							if ($forum_info['forum_type'] != FORUM_POST)
							{
								trigger_error($this->language->lang('MOVE_POSTS_NO_POSTABLE_FORUM') . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
							}

							// Two stage?
							// Move topics comprising only posts from this user
							$topic_id_ary = $move_topic_ary = $move_post_ary = $new_topic_id_ary = [];
							$forum_id_ary = [$new_forum_id];

							$sql = 'SELECT topic_id, post_visibility, COUNT(post_id) AS total_posts
								FROM ' . POSTS_TABLE . "
								WHERE poster_id = $user_id
									AND forum_id <> $new_forum_id
								GROUP BY topic_id, post_visibility";
							$result = $this->db->sql_query($sql);

							while ($row = $this->db->sql_fetchrow($result))
							{
								$topic_id_ary[$row['topic_id']][$row['post_visibility']] = $row['total_posts'];
							}
							$this->db->sql_freeresult($result);

							if (count($topic_id_ary))
							{
								$sql = 'SELECT topic_id, forum_id, topic_title, topic_posts_approved, topic_posts_unapproved, topic_posts_softdeleted, topic_attachment
									FROM ' . TOPICS_TABLE . '
									WHERE ' . $this->db->sql_in_set('topic_id', array_keys($topic_id_ary));
								$result = $this->db->sql_query($sql);

								while ($row = $this->db->sql_fetchrow($result))
								{
									if ($topic_id_ary[$row['topic_id']][ITEM_APPROVED] == $row['topic_posts_approved']
										&& $topic_id_ary[$row['topic_id']][ITEM_UNAPPROVED] == $row['topic_posts_unapproved']
										&& $topic_id_ary[$row['topic_id']][ITEM_REAPPROVE] == $row['topic_posts_unapproved']
										&& $topic_id_ary[$row['topic_id']][ITEM_DELETED] == $row['topic_posts_softdeleted'])
									{
										$move_topic_ary[] = $row['topic_id'];
									}
									else
									{
										$move_post_ary[$row['topic_id']]['title'] = $row['topic_title'];
										$move_post_ary[$row['topic_id']]['attach'] = ($row['topic_attachment']) ? 1 : 0;
									}

									$forum_id_ary[] = $row['forum_id'];
								}
								$this->db->sql_freeresult($result);
							}

							// Entire topic comprises posts by this user, move these topics
							if (count($move_topic_ary))
							{
								move_topics($move_topic_ary, $new_forum_id, false);
							}

							if (count($move_post_ary))
							{
								// Create new topic
								// Update post_ids, report_ids, attachment_ids
								foreach ($move_post_ary as $topic_id => $post_ary)
								{
									// Create new topic
									$sql = 'INSERT INTO ' . TOPICS_TABLE . ' ' . $this->db->sql_build_array('INSERT', [
										'topic_poster'				=> $user_id,
										'topic_time'				=> time(),
										'forum_id' 					=> $new_forum_id,
										'icon_id'					=> 0,
										'topic_visibility'			=> ITEM_APPROVED,
										'topic_title' 				=> $post_ary['title'],
										'topic_first_poster_name'	=> $user_row['username'],
										'topic_type'				=> POST_NORMAL,
										'topic_time_limit'			=> 0,
										'topic_attachment'			=> $post_ary['attach']]
									);
									$this->db->sql_query($sql);

									$new_topic_id = $this->db->sql_nextid();

									// Move posts
									$sql = 'UPDATE ' . POSTS_TABLE . "
										SET forum_id = $new_forum_id, topic_id = $new_topic_id
										WHERE topic_id = $topic_id
											AND poster_id = $user_id";
									$this->db->sql_query($sql);

									if ($post_ary['attach'])
									{
										$sql = 'UPDATE ' . ATTACHMENTS_TABLE . "
											SET topic_id = $new_topic_id
											WHERE topic_id = $topic_id
												AND poster_id = $user_id";
										$this->db->sql_query($sql);
									}

									$new_topic_id_ary[] = $new_topic_id;
								}
							}

							$forum_id_ary = array_unique($forum_id_ary);
							$topic_id_ary = array_unique(array_merge(array_keys($topic_id_ary), $new_topic_id_ary));

							if (count($topic_id_ary))
							{
								sync('topic_reported', 'topic_id', $topic_id_ary);
								sync('topic', 'topic_id', $topic_id_ary);
							}

							if (count($forum_id_ary))
							{
								sync('forum', 'forum_id', $forum_id_ary, false, true);
							}

							$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_USER_MOVE_POSTS', false, [$user_row['username'], $forum_info['forum_name']]);
							$this->log->add('user', $this->user->data['user_id'], $this->user->ip, 'LOG_USER_MOVE_POSTS_USER', false, [
								'reportee_id' => $user_id,
								$forum_info['forum_name'],
							]);

							trigger_error($this->language->lang('USER_POSTS_MOVED') . adm_back_link($this->u_action . '&amp;u=' . $user_id));

						break;

						case 'leave_nr':

							if (confirm_box(true))
							{
								remove_newly_registered($user_id, $user_row);

								$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_USER_REMOVED_NR', false, [$user_row['username']]);
								trigger_error($this->language->lang('USER_LIFTED_NR') . adm_back_link($this->u_action . '&amp;u=' . $user_id));
							}
							else
							{
								confirm_box(false, $this->language->lang('CONFIRM_OPERATION'), build_hidden_fields([
									'u'				=> $user_id,
									'i'				=> $id,
									'mode'			=> $mode,
									'action'		=> $action,
									'update'		=> true])
								);
							}

						break;

						default:
							$u_action = $this->u_action;

							/**
							 * Run custom quicktool code
							 *
							 * @event core.acp_users_overview_run_quicktool
							 * @var string	action		Quick tool that should be run
							 * @var array	user_row	Current user data
							 * @var string	u_action	The u_action link
							 * @since 3.1.0-a1
							 * @changed 3.2.2-RC1 Added u_action
							 */
							$vars = ['action', 'user_row', 'u_action'];
							extract($this->dispatcher->trigger_event('core.acp_users_overview_run_quicktool', compact($vars)));

							unset($u_action);
						break;
					}

					// Handle registration info updates
					$data = [
						'username'			=> $this->request->variable('user', $user_row['username'], true),
						'user_founder'		=> $this->request->variable('user_founder', ($user_row['user_type'] == USER_FOUNDER) ? 1 : 0),
						'email'				=> strtolower($this->request->variable('user_email', $user_row['user_email'])),
						'new_password'		=> $this->request->variable('new_password', '', true),
						'password_confirm'	=> $this->request->variable('password_confirm', '', true),
					];

					// Validation data - we do not check the password complexity setting here
					$check_ary = [
						'new_password'		=> [
							['string', true, $this->config['min_pass_chars'], 0],
							['password']],
						'password_confirm'	=> ['string', true, $this->config['min_pass_chars'], 0],
					];

					// Check username if altered
					if ($data['username'] != $user_row['username'])
					{
						$check_ary += [
							'username'			=> [
								['string', false, $this->config['min_name_chars'], $this->config['max_name_chars']],
								['username', $user_row['username'], true],
							],
						];
					}

					// Check email if altered
					if ($data['email'] != $user_row['user_email'])
					{
						$check_ary += [
							'email'				=> [
								['string', false, 6, 60],
								['user_email', $user_row['user_email']],
							],
						];
					}

					$error = validate_data($data, $check_ary);

					if ($data['new_password'] && $data['password_confirm'] != $data['new_password'])
					{
						$error[] = 'NEW_PASSWORD_ERROR';
					}

					if (!check_form_key($form_name))
					{
						$error[] = 'FORM_INVALID';
					}

					// Instantiate passwords manager
					/* @var $passwords_manager \phpbb\passwords\manager */
					$passwords_manager = $phpbb_container->get('passwords.manager');

					// Which updates do we need to do?
					$update_username = ($user_row['username'] != $data['username']) ? $data['username'] : false;
					$update_password = $data['new_password'] && !$this->passwords_manager->check($data['new_password'], $user_row['user_password']);
					$update_email = ($data['email'] != $user_row['user_email']) ? $data['email'] : false;

					if (!count($error))
					{
						$sql_ary = [];

						if ($user_row['user_type'] != USER_FOUNDER || $this->user->data['user_type'] == USER_FOUNDER)
						{
							// Only allow founders updating the founder status...
							if ($this->user->data['user_type'] == USER_FOUNDER)
							{
								// Setting a normal member to be a founder
								if ($data['user_founder'] && $user_row['user_type'] != USER_FOUNDER)
								{
									// Make sure the user is not setting an Inactive or ignored user to be a founder
									if ($user_row['user_type'] == USER_IGNORE)
									{
										trigger_error($this->language->lang('CANNOT_SET_FOUNDER_IGNORED') . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
									}

									if ($user_row['user_type'] == USER_INACTIVE)
									{
										trigger_error($this->language->lang('CANNOT_SET_FOUNDER_INACTIVE') . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
									}

									$sql_ary['user_type'] = USER_FOUNDER;
								}
								else if (!$data['user_founder'] && $user_row['user_type'] == USER_FOUNDER)
								{
									// Check if at least one founder is present
									$sql = 'SELECT user_id
										FROM ' . USERS_TABLE . '
										WHERE user_type = ' . USER_FOUNDER . '
											AND user_id <> ' . $user_id;
									$result = $this->db->sql_query_limit($sql, 1);
									$row = $this->db->sql_fetchrow($result);
									$this->db->sql_freeresult($result);

									if ($row)
									{
										$sql_ary['user_type'] = USER_NORMAL;
									}
									else
									{
										trigger_error($this->language->lang('AT_LEAST_ONE_FOUNDER') . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
									}
								}
							}
						}

						/**
						 * Modify user data before we update it
						 *
						 * @event core.acp_users_overview_modify_data
						 * @var array	user_row	Current user data
						 * @var array	data		Submitted user data
						 * @var array	sql_ary		User data we udpate
						 * @since 3.1.0-a1
						 */
						$vars = ['user_row', 'data', 'sql_ary'];
						extract($this->dispatcher->trigger_event('core.acp_users_overview_modify_data', compact($vars)));

						if ($update_username !== false)
						{
							$sql_ary['username'] = $update_username;
							$sql_ary['username_clean'] = utf8_clean_string($update_username);

							$this->log->add('user', $this->user->data['user_id'], $this->user->ip, 'LOG_USER_UPDATE_NAME', false, [
								'reportee_id' => $user_id,
								$user_row['username'],
								$update_username,
							]);
						}

						if ($update_email !== false)
						{
							$sql_ary += ['user_email'		=> $update_email];

							$this->log->add('user', $this->user->data['user_id'], $this->user->ip, 'LOG_USER_UPDATE_EMAIL', false, [
								'reportee_id' => $user_id,
								$user_row['username'],
								$user_row['user_email'],
								$update_email,
							]);
						}

						if ($update_password)
						{
							$sql_ary += [
								'user_password'		=> $this->passwords_manager->hash($data['new_password']),
								'user_passchg'		=> time(),
							];

							$this->user->reset_login_keys($user_id);

							$this->log->add('user', $this->user->data['user_id'], $this->user->ip, 'LOG_USER_NEW_PASSWORD', false, [
								'reportee_id' => $user_id,
								$user_row['username'],
							]);
						}

						if (count($sql_ary))
						{
							$sql = 'UPDATE ' . USERS_TABLE . '
								SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
								WHERE user_id = ' . $user_id;
							$this->db->sql_query($sql);
						}

						if ($update_username)
						{
							user_update_name($user_row['username'], $update_username);
						}

						// Let the users permissions being updated
						$this->auth->acl_clear_prefetch($user_id);

						$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_USER_USER_UPDATE', false, [$data['username']]);

						trigger_error($this->language->lang('USER_OVERVIEW_UPDATED') . adm_back_link($this->u_action . '&amp;u=' . $user_id));
					}

					// Replace "error" strings with their real, localised form
					$error = array_map([$user, 'lang'], $error);
				}

				if ($user_id == $this->user->data['user_id'])
				{
					$quick_tool_ary = ['delsig' => 'DEL_SIG', 'delavatar' => 'DEL_AVATAR', 'moveposts' => 'MOVE_POSTS', 'delposts' => 'DEL_POSTS', 'delattach' => 'DEL_ATTACH', 'deloutbox' => 'DEL_OUTBOX'];
					if ($user_row['user_new'])
					{
						$quick_tool_ary['leave_nr'] = 'LEAVE_NR';
					}
				}
				else
				{
					$quick_tool_ary = [];

					if ($user_row['user_type'] != USER_FOUNDER)
					{
						$quick_tool_ary += ['banuser' => 'BAN_USER', 'banemail' => 'BAN_EMAIL', 'banip' => 'BAN_IP'];
					}

					if ($user_row['user_type'] != USER_FOUNDER && $user_row['user_type'] != USER_IGNORE)
					{
						$quick_tool_ary += ['active' => (($user_row['user_type'] == USER_INACTIVE) ? 'ACTIVATE' : 'DEACTIVATE')];
					}

					$quick_tool_ary += ['delsig' => 'DEL_SIG', 'delavatar' => 'DEL_AVATAR', 'moveposts' => 'MOVE_POSTS', 'delposts' => 'DEL_POSTS', 'delattach' => 'DEL_ATTACH', 'deloutbox' => 'DEL_OUTBOX'];

					if ($this->config['email_enable'] && ($user_row['user_type'] == USER_NORMAL || $user_row['user_type'] == USER_INACTIVE))
					{
						$quick_tool_ary['reactivate'] = 'FORCE';
					}

					if ($user_row['user_new'])
					{
						$quick_tool_ary['leave_nr'] = 'LEAVE_NR';
					}
				}

				if ($this->config['load_onlinetrack'])
				{
					$sql = 'SELECT MAX(session_time) AS session_time, MIN(session_viewonline) AS session_viewonline
						FROM ' . SESSIONS_TABLE . "
						WHERE session_user_id = $user_id";
					$result = $this->db->sql_query($sql);
					$row = $this->db->sql_fetchrow($result);
					$this->db->sql_freeresult($result);

					$user_row['session_time'] = (isset($row['session_time'])) ? $row['session_time'] : 0;
					$user_row['session_viewonline'] = (isset($row['session_viewonline'])) ? $row['session_viewonline'] : 0;
					unset($row);
				}

				/**
				 * Add additional quick tool options and overwrite user data
				 *
				 * @event core.acp_users_display_overview
				 * @var array	user_row			Array with user data
				 * @var array	quick_tool_ary		Ouick tool options
				 * @since 3.1.0-a1
				 */
				$vars = ['user_row', 'quick_tool_ary'];
				extract($this->dispatcher->trigger_event('core.acp_users_display_overview', compact($vars)));

				$s_action_options = '<option class="sep" value="">' . $this->language->lang('SELECT_OPTION') . '</option>';
				foreach ($quick_tool_ary as $value => $lang)
				{
					$s_action_options .= '<option value="' . $value . '">' . $this->language->lang('USER_ADMIN_' . $lang) . '</option>';
				}

				$last_active = (!empty($user_row['session_time'])) ? $user_row['session_time'] : $user_row['user_lastvisit'];

				$inactive_reason = '';
				if ($user_row['user_type'] == USER_INACTIVE)
				{
					$inactive_reason = $this->language->lang('INACTIVE_REASON_UNKNOWN');

					switch ($user_row['user_inactive_reason'])
					{
						case INACTIVE_REGISTER:
							$inactive_reason = $this->language->lang('INACTIVE_REASON_REGISTER');
						break;

						case INACTIVE_PROFILE:
							$inactive_reason = $this->language->lang('INACTIVE_REASON_PROFILE');
						break;

						case INACTIVE_MANUAL:
							$inactive_reason = $this->language->lang('INACTIVE_REASON_MANUAL');
						break;

						case INACTIVE_REMIND:
							$inactive_reason = $this->language->lang('INACTIVE_REASON_REMIND');
						break;
					}
				}

				// Posts in Queue
				$sql = 'SELECT COUNT(post_id) as posts_in_queue
					FROM ' . POSTS_TABLE . '
					WHERE poster_id = ' . $user_id . '
						AND ' . $this->db->sql_in_set('post_visibility', [ITEM_UNAPPROVED, ITEM_REAPPROVE]);
				$result = $this->db->sql_query($sql);
				$user_row['posts_in_queue'] = (int) $this->db->sql_fetchfield('posts_in_queue');
				$this->db->sql_freeresult($result);

				$sql = 'SELECT post_id
					FROM ' . POSTS_TABLE . '
					WHERE poster_id = '. $user_id;
				$result = $this->db->sql_query_limit($sql, 1);
				$user_row['user_has_posts'] = (bool) $this->db->sql_fetchfield('post_id');
				$this->db->sql_freeresult($result);

				$this->template->assign_vars([
					'L_NAME_CHARS_EXPLAIN'		=> $this->language->lang($this->config['allow_name_chars'] . '_EXPLAIN', $this->language->lang('CHARACTERS', (int) $this->config['min_name_chars']), $this->language->lang('CHARACTERS', (int) $this->config['max_name_chars'])),
					'L_CHANGE_PASSWORD_EXPLAIN'	=> $this->language->lang($this->config['pass_complex'] . '_EXPLAIN', $this->language->lang('CHARACTERS', (int) $this->config['min_pass_chars'])),
					'L_POSTS_IN_QUEUE'			=> $this->language->lang('NUM_POSTS_IN_QUEUE', $user_row['posts_in_queue']),
					'S_FOUNDER'					=> ($this->user->data['user_type'] == USER_FOUNDER) ? true : false,

					'S_OVERVIEW'		=> true,
					'S_USER_IP'			=> ($user_row['user_ip']) ? true : false,
					'S_USER_FOUNDER'	=> ($user_row['user_type'] == USER_FOUNDER) ? true : false,
					'S_ACTION_OPTIONS'	=> $s_action_options,
					'S_OWN_ACCOUNT'		=> ($user_id == $this->user->data['user_id']) ? true : false,
					'S_USER_INACTIVE'	=> ($user_row['user_type'] == USER_INACTIVE) ? true : false,

					'U_SHOW_IP'		=> $this->u_action . "&amp;u=$user_id&amp;ip=" . (($ip == 'ip') ? 'hostname' : 'ip'),
					'U_WHOIS'		=> $this->u_action . "&amp;action=whois&amp;user_ip={$user_row['user_ip']}",
					'U_MCP_QUEUE'	=> ($this->auth->acl_getf_global('m_approve')) ? append_sid("{$this->root_path}mcp.$this->php_ext", 'i=queue', true, $this->user->session_id) : '',
					'U_SEARCH_USER'	=> ($this->config['load_search'] && $this->auth->acl_get('u_search')) ? append_sid("{$this->root_path}search.$this->php_ext", "author_id={$user_row['user_id']}&amp;sr=posts") : '',

					'U_SWITCH_PERMISSIONS'	=> ($this->auth->acl_get('a_switchperm') && $this->user->data['user_id'] != $user_row['user_id']) ? append_sid("{$this->root_path}ucp.$this->php_ext", "mode=switch_perm&amp;u={$user_row['user_id']}&amp;hash=" . generate_link_hash('switchperm')) : '',

					'POSTS_IN_QUEUE'	=> $user_row['posts_in_queue'],
					'USER'				=> $user_row['username'],
					'USER_REGISTERED'	=> $this->user->format_date($user_row['user_regdate']),
					'REGISTERED_IP'		=> ($ip == 'hostname') ? gethostbyaddr($user_row['user_ip']) : $user_row['user_ip'],
					'USER_LASTACTIVE'	=> ($last_active) ? $this->user->format_date($last_active) : ' - ',
					'USER_EMAIL'		=> $user_row['user_email'],
					'USER_WARNINGS'		=> $user_row['user_warnings'],
					'USER_POSTS'		=> $user_row['user_posts'],
					'USER_HAS_POSTS'	=> $user_row['user_has_posts'],
					'USER_INACTIVE_REASON'	=> $inactive_reason,
				]);

			break;

			case 'feedback':

				$this->language->add_lang('mcp');

				// Set up general vars
				$start		= $this->request->variable('start', 0);
				$deletemark = ($this->request->is_set_post('delmarked')) ? true : false;
				$deleteall	= ($this->request->is_set_post('delall')) ? true : false;
				$marked		= $this->request->variable('mark', [0]);
				$message	= $this->request->variable('message', '', true);

				/* @var $pagination \phpbb\pagination */
				$pagination = $phpbb_container->get('pagination');

				// Sort keys
				$sort_days	= $this->request->variable('st', 0);
				$sort_key	= $this->request->variable('sk', 't');
				$sort_dir	= $this->request->variable('sd', 'd');

				// Delete entries if requested and able
				if (($deletemark || $deleteall) && $this->auth->acl_get('a_clearlogs'))
				{
					if (!check_form_key($form_name))
					{
						trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
					}

					$where_sql = '';
					if ($deletemark && $marked)
					{
						$sql_in = [];
						foreach ($marked as $mark)
						{
							$sql_in[] = $mark;
						}
						$where_sql = ' AND ' . $this->db->sql_in_set('log_id', $sql_in);
						unset($sql_in);
					}

					if ($where_sql || $deleteall)
					{
						$sql = 'DELETE FROM ' . LOG_TABLE . '
							WHERE log_type = ' . LOG_USERS . "
							AND reportee_id = $user_id
							$where_sql";
						$this->db->sql_query($sql);

						$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_CLEAR_USER', false, [$user_row['username']]);
					}
				}

				if ($submit && $message)
				{
					if (!check_form_key($form_name))
					{
						trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
					}

					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_USER_FEEDBACK', false, [$user_row['username']]);
					$this->log->add('mod', $this->user->data['user_id'], $this->user->ip, 'LOG_USER_FEEDBACK', false, [
						'forum_id' => 0,
						'topic_id' => 0,
						$user_row['username'],
					]);
					$this->log->add('user', $this->user->data['user_id'], $this->user->ip, 'LOG_USER_GENERAL', false, [
						'reportee_id' => $user_id,
						$message,
					]);

					trigger_error($this->language->lang('USER_FEEDBACK_ADDED') . adm_back_link($this->u_action . '&amp;u=' . $user_id));
				}

				// Sorting
				$limit_days = [0 => $this->language->lang('ALL_ENTRIES'), 1 => $this->language->lang('1_DAY'), 7 => $this->language->lang('7_DAYS'), 14 => $this->language->lang('2_WEEKS'), 30 => $this->language->lang('1_MONTH'), 90 => $this->language->lang('3_MONTHS'), 180 => $this->language->lang('6_MONTHS'), 365 => $this->language->lang('1_YEAR')];
				$sort_by_text = ['u' => $this->language->lang('SORT_USERNAME'), 't' => $this->language->lang('SORT_DATE'), 'i' => $this->language->lang('SORT_IP'), 'o' => $this->language->lang('SORT_ACTION')];
				$sort_by_sql = ['u' => 'u.username_clean', 't' => 'l.log_time', 'i' => 'l.log_ip', 'o' => 'l.log_operation'];

				$s_limit_days = $s_sort_key = $s_sort_dir = $u_sort_param = '';
				gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);

				// Define where and sort sql for use in displaying logs
				$sql_where = ($sort_days) ? (time() - ($sort_days * 86400)) : 0;
				$sql_sort = $sort_by_sql[$sort_key] . ' ' . (($sort_dir == 'd') ? 'DESC' : 'ASC');

				// Grab log data
				$log_data = [];
				$log_count = 0;
				$start = view_log('user', $log_data, $log_count, $this->config['topics_per_page'], $start, 0, 0, $user_id, $sql_where, $sql_sort);

				$base_url = $this->u_action . "&amp;u=$user_id&amp;$u_sort_param";
				$this->pagination->generate_template_pagination($base_url, 'pagination', 'start', $log_count, $this->config['topics_per_page'], $start);

				$this->template->assign_vars([
					'S_FEEDBACK'	=> true,

					'S_LIMIT_DAYS'	=> $s_limit_days,
					'S_SORT_KEY'	=> $s_sort_key,
					'S_SORT_DIR'	=> $s_sort_dir,
					'S_CLEARLOGS'	=> $this->auth->acl_get('a_clearlogs')]
				);

				foreach ($log_data as $row)
				{
					$this->template->assign_block_vars('log', [
						'USERNAME'		=> $row['username_full'],
						'IP'			=> $row['ip'],
						'DATE'			=> $this->user->format_date($row['time']),
						'ACTION'		=> nl2br($row['action']),
						'ID'			=> $row['id']]
					);
				}

			break;

			case 'warnings':
				$this->language->add_lang('mcp');

				// Set up general vars
				$deletemark	= ($this->request->is_set_post('delmarked')) ? true : false;
				$deleteall	= ($this->request->is_set_post('delall')) ? true : false;
				$confirm	= ($this->request->is_set_post('confirm')) ? true : false;
				$marked		= $this->request->variable('mark', [0]);

				// Delete entries if requested and able
				if ($deletemark || $deleteall || $confirm)
				{
					if (confirm_box(true))
					{
						$where_sql = '';
						$deletemark = $this->request->variable('delmarked', 0);
						$deleteall = $this->request->variable('delall', 0);
						if ($deletemark && $marked)
						{
							$where_sql = ' AND ' . $this->db->sql_in_set('warning_id', array_values($marked));
						}

						if ($where_sql || $deleteall)
						{
							$sql = 'DELETE FROM ' . WARNINGS_TABLE . "
								WHERE user_id = $user_id
									$where_sql";
							$this->db->sql_query($sql);

							if ($deleteall)
							{
								$log_warnings = $deleted_warnings = 0;
							}
							else
							{
								$num_warnings = (int) $this->db->sql_affectedrows();
								$deleted_warnings = ' user_warnings - ' . $num_warnings;
								$log_warnings = ($num_warnings > 2) ? 2 : $num_warnings;
							}

							$sql = 'UPDATE ' . USERS_TABLE . "
								SET user_warnings = $deleted_warnings
								WHERE user_id = $user_id";
							$this->db->sql_query($sql);

							if ($log_warnings)
							{
								$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_WARNINGS_DELETED', false, [$user_row['username'], $num_warnings]);
							}
							else
							{
								$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_WARNINGS_DELETED_ALL', false, [$user_row['username']]);
							}
						}
					}
					else
					{
						$s_hidden_fields = [
							'i'				=> $id,
							'mode'			=> $mode,
							'u'				=> $user_id,
							'mark'			=> $marked,
						];
						if ($this->request->is_set_post('delmarked'))
						{
							$s_hidden_fields['delmarked'] = 1;
						}
						if ($this->request->is_set_post('delall'))
						{
							$s_hidden_fields['delall'] = 1;
						}
						if ($this->request->is_set_post('delall') || ($this->request->is_set_post('delmarked') && count($marked)))
						{
							confirm_box(false, $this->language->lang('CONFIRM_OPERATION'), build_hidden_fields($s_hidden_fields));
						}
					}
				}

				$sql = 'SELECT w.warning_id, w.warning_time, w.post_id, l.log_operation, l.log_data, l.user_id AS mod_user_id, m.username AS mod_username, m.user_colour AS mod_user_colour
					FROM ' . WARNINGS_TABLE . ' w
					LEFT JOIN ' . LOG_TABLE . ' l
						ON (w.log_id = l.log_id)
					LEFT JOIN ' . USERS_TABLE . ' m
						ON (l.user_id = m.user_id)
					WHERE w.user_id = ' . $user_id . '
					ORDER BY w.warning_time DESC';
				$result = $this->db->sql_query($sql);

				while ($row = $this->db->sql_fetchrow($result))
				{
					if (!$row['log_operation'])
					{
						// We do not have a log-entry anymore, so there is no data available
						$row['action'] = $this->language->lang('USER_WARNING_LOG_DELETED');
					}
					else
					{
						$row['action'] = (isset($this->language->lang[$row['log_operation']])) ? $this->language->lang[$row['log_operation']] : '{' . ucfirst(str_replace('_', ' ', $row['log_operation'])) . '}';
						if (!empty($row['log_data']))
						{
							$log_data_ary = @unserialize($row['log_data']);
							$log_data_ary = ($log_data_ary === false) ? [] : $log_data_ary;

							if (isset($this->language->lang[$row['log_operation']]))
							{
								// Check if there are more occurrences of % than arguments, if there are we fill out the arguments array
								// It doesn't matter if we add more arguments than placeholders
								if ((substr_count($row['action'], '%') - count($log_data_ary)) > 0)
								{
									$log_data_ary = array_merge($log_data_ary, array_fill(0, substr_count($row['action'], '%') - count($log_data_ary), ''));
								}
								$row['action'] = vsprintf($row['action'], $log_data_ary);
								$row['action'] = bbcode_nl2br(censor_text($row['action']));
							}
							else if (!empty($log_data_ary))
							{
								$row['action'] .= '<br />' . implode('', $log_data_ary);
							}
						}
					}

					$this->template->assign_block_vars('warn', [
						'ID'		=> $row['warning_id'],
						'USERNAME'	=> ($row['log_operation']) ? get_username_string('full', $row['mod_user_id'], $row['mod_username'], $row['mod_user_colour']) : '-',
						'ACTION'	=> make_clickable($row['action']),
						'DATE'		=> $this->user->format_date($row['warning_time']),
					]);
				}
				$this->db->sql_freeresult($result);

				$this->template->assign_vars([
					'S_WARNINGS'	=> true,
				]);

			break;

			case 'profile':

				if (!function_exists('user_get_id_name'))
				{
					include($this->root_path . 'includes/functions_user.' . $this->php_ext);
				}

				/* @var $cp \phpbb\profilefields\manager */
				$cp = $phpbb_container->get('profilefields.manager');

				$cp_data = $cp_error = [];

				$sql = 'SELECT lang_id
					FROM ' . LANG_TABLE . "
					WHERE lang_iso = '" . $this->db->sql_escape($this->user->data['user_lang']) . "'";
				$result = $this->db->sql_query($sql);
				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				$user_row['iso_lang_id'] = $row['lang_id'];

				$data = [
					'jabber'		=> $this->request->variable('jabber', $user_row['user_jabber'], true),
					'bday_day'		=> 0,
					'bday_month'	=> 0,
					'bday_year'		=> 0,
				];

				if ($user_row['user_birthday'])
				{
					list($data['bday_day'], $data['bday_month'], $data['bday_year']) = explode('-', $user_row['user_birthday']);
				}

				$data['bday_day']		= $this->request->variable('bday_day', $data['bday_day']);
				$data['bday_month']		= $this->request->variable('bday_month', $data['bday_month']);
				$data['bday_year']		= $this->request->variable('bday_year', $data['bday_year']);
				$data['user_birthday']	= sprintf('%2d-%2d-%4d', $data['bday_day'], $data['bday_month'], $data['bday_year']);

				/**
				 * Modify user data on editing profile in ACP
				 *
				 * @event core.acp_users_modify_profile
				 * @var array	data		Array with user profile data
				 * @var bool	submit		Flag indicating if submit button has been pressed
				 * @var int		user_id		The user id
				 * @var array	user_row	Array with the full user data
				 * @since 3.1.4-RC1
				 */
				$vars = ['data', 'submit', 'user_id', 'user_row'];
				extract($this->dispatcher->trigger_event('core.acp_users_modify_profile', compact($vars)));

				if ($submit)
				{
					$error = validate_data($data, [
						'jabber'		=> [
							['string', true, 5, 255],
							['jabber']],
						'bday_day'		=> ['num', true, 1, 31],
						'bday_month'	=> ['num', true, 1, 12],
						'bday_year'		=> ['num', true, 1901, gmdate('Y', time())],
						'user_birthday'	=> ['date', true],
					]);

					// validate custom profile fields
					$cp->submit_cp_field('profile', $user_row['iso_lang_id'], $cp_data, $cp_error);

					if (count($cp_error))
					{
						$error = array_merge($error, $cp_error);
					}
					if (!check_form_key($form_name))
					{
						$error[] = 'FORM_INVALID';
					}

					/**
					 * Validate profile data in ACP before submitting to the database
					 *
					 * @event core.acp_users_profile_validate
					 * @var array	data		Array with user profile data
					 * @var int		user_id		The user id
					 * @var array	user_row	Array with the full user data
					 * @var array	error		Array with the form errors
					 * @since 3.1.4-RC1
					 * @changed 3.1.12-RC1		Removed submit, added user_id, user_row
					 */
					$vars = ['data', 'user_id', 'user_row', 'error'];
					extract($this->dispatcher->trigger_event('core.acp_users_profile_validate', compact($vars)));

					if (!count($error))
					{
						$sql_ary = [
							'user_jabber'	=> $data['jabber'],
							'user_birthday'	=> $data['user_birthday'],
						];

						/**
						 * Modify profile data in ACP before submitting to the database
						 *
						 * @event core.acp_users_profile_modify_sql_ary
						 * @var array	cp_data		Array with the user custom profile fields data
						 * @var array	data		Array with user profile data
						 * @var int		user_id		The user id
						 * @var array	user_row	Array with the full user data
						 * @var array	sql_ary		Array with sql data
						 * @since 3.1.4-RC1
						 */
						$vars = ['cp_data', 'data', 'user_id', 'user_row', 'sql_ary'];
						extract($this->dispatcher->trigger_event('core.acp_users_profile_modify_sql_ary', compact($vars)));

						$sql = 'UPDATE ' . USERS_TABLE . '
							SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . "
							WHERE user_id = $user_id";
						$this->db->sql_query($sql);

						// Update Custom Fields
						$cp->update_profile_field_data($user_id, $cp_data);

						trigger_error($this->language->lang('USER_PROFILE_UPDATED') . adm_back_link($this->u_action . '&amp;u=' . $user_id));
					}

					// Replace "error" strings with their real, localised form
					$error = array_map([$user, 'lang'], $error);
				}

				$s_birthday_day_options = '<option value="0"' . ((!$data['bday_day']) ? ' selected="selected"' : '') . '>--</option>';
				for ($i = 1; $i < 32; $i++)
				{
					$selected = ($i == $data['bday_day']) ? ' selected="selected"' : '';
					$s_birthday_day_options .= "<option value=\"$i\"$selected>$i</option>";
				}

				$s_birthday_month_options = '<option value="0"' . ((!$data['bday_month']) ? ' selected="selected"' : '') . '>--</option>';
				for ($i = 1; $i < 13; $i++)
				{
					$selected = ($i == $data['bday_month']) ? ' selected="selected"' : '';
					$s_birthday_month_options .= "<option value=\"$i\"$selected>$i</option>";
				}

				$now = getdate();
				$s_birthday_year_options = '<option value="0"' . ((!$data['bday_year']) ? ' selected="selected"' : '') . '>--</option>';
				for ($i = $now['year'] - 100; $i <= $now['year']; $i++)
				{
					$selected = ($i == $data['bday_year']) ? ' selected="selected"' : '';
					$s_birthday_year_options .= "<option value=\"$i\"$selected>$i</option>";
				}
				unset($now);

				$this->template->assign_vars([
					'JABBER'		=> $data['jabber'],
					'S_BIRTHDAY_DAY_OPTIONS'	=> $s_birthday_day_options,
					'S_BIRTHDAY_MONTH_OPTIONS'	=> $s_birthday_month_options,
					'S_BIRTHDAY_YEAR_OPTIONS'	=> $s_birthday_year_options,

					'S_PROFILE'		=> true]
				);

				// Get additional profile fields and assign them to the template block var 'profile_fields'
				$this->user->get_profile_fields($user_id);

				$cp->generate_profile_fields('profile', $user_row['iso_lang_id']);

			break;

			case 'prefs':

				if (!function_exists('user_get_id_name'))
				{
					include($this->root_path . 'includes/functions_user.' . $this->php_ext);
				}

				$data = [
					'dateformat'		=> $this->request->variable('dateformat', $user_row['user_dateformat'], true),
					'lang'				=> basename($this->request->variable('lang', $user_row['user_lang'])),
					'tz'				=> $this->request->variable('tz', $user_row['user_timezone']),
					'style'				=> $this->request->variable('style', $user_row['user_style']),
					'viewemail'			=> $this->request->variable('viewemail', $user_row['user_allow_viewemail']),
					'massemail'			=> $this->request->variable('massemail', $user_row['user_allow_massemail']),
					'hideonline'		=> $this->request->variable('hideonline', !$user_row['user_allow_viewonline']),
					'notifymethod'		=> $this->request->variable('notifymethod', $user_row['user_notify_type']),
					'notifypm'			=> $this->request->variable('notifypm', $user_row['user_notify_pm']),
					'allowpm'			=> $this->request->variable('allowpm', $user_row['user_allow_pm']),

					'topic_sk'			=> $this->request->variable('topic_sk', ($user_row['user_topic_sortby_type']) ? $user_row['user_topic_sortby_type'] : 't'),
					'topic_sd'			=> $this->request->variable('topic_sd', ($user_row['user_topic_sortby_dir']) ? $user_row['user_topic_sortby_dir'] : 'd'),
					'topic_st'			=> $this->request->variable('topic_st', ($user_row['user_topic_show_days']) ? $user_row['user_topic_show_days'] : 0),

					'post_sk'			=> $this->request->variable('post_sk', ($user_row['user_post_sortby_type']) ? $user_row['user_post_sortby_type'] : 't'),
					'post_sd'			=> $this->request->variable('post_sd', ($user_row['user_post_sortby_dir']) ? $user_row['user_post_sortby_dir'] : 'a'),
					'post_st'			=> $this->request->variable('post_st', ($user_row['user_post_show_days']) ? $user_row['user_post_show_days'] : 0),

					'view_images'		=> $this->request->variable('view_images', $this->optionget($user_row, 'viewimg')),
					'view_flash'		=> $this->request->variable('view_flash', $this->optionget($user_row, 'viewflash')),
					'view_smilies'		=> $this->request->variable('view_smilies', $this->optionget($user_row, 'viewsmilies')),
					'view_sigs'			=> $this->request->variable('view_sigs', $this->optionget($user_row, 'viewsigs')),
					'view_avatars'		=> $this->request->variable('view_avatars', $this->optionget($user_row, 'viewavatars')),
					'view_wordcensor'	=> $this->request->variable('view_wordcensor', $this->optionget($user_row, 'viewcensors')),

					'bbcode'	=> $this->request->variable('bbcode', $this->optionget($user_row, 'bbcode')),
					'smilies'	=> $this->request->variable('smilies', $this->optionget($user_row, 'smilies')),
					'sig'		=> $this->request->variable('sig', $this->optionget($user_row, 'attachsig')),
					'notify'	=> $this->request->variable('notify', $user_row['user_notify']),
				];

				/**
				 * Modify users preferences data
				 *
				 * @event core.acp_users_prefs_modify_data
				 * @var array	data			Array with users preferences data
				 * @var array	user_row		Array with user data
				 * @since 3.1.0-b3
				 */
				$vars = ['data', 'user_row'];
				extract($this->dispatcher->trigger_event('core.acp_users_prefs_modify_data', compact($vars)));

				if ($submit)
				{
					$error = validate_data($data, [
						'dateformat'	=> ['string', false, 1, 64],
						'lang'			=> ['match', false, '#^[a-z_\-]{2,}$#i'],
						'tz'			=> ['timezone'],

						'topic_sk'		=> ['string', false, 1, 1],
						'topic_sd'		=> ['string', false, 1, 1],
						'post_sk'		=> ['string', false, 1, 1],
						'post_sd'		=> ['string', false, 1, 1],
					]);

					if (!check_form_key($form_name))
					{
						$error[] = 'FORM_INVALID';
					}

					if (!count($error))
					{
						$this->optionset($user_row, 'viewimg', $data['view_images']);
						$this->optionset($user_row, 'viewflash', $data['view_flash']);
						$this->optionset($user_row, 'viewsmilies', $data['view_smilies']);
						$this->optionset($user_row, 'viewsigs', $data['view_sigs']);
						$this->optionset($user_row, 'viewavatars', $data['view_avatars']);
						$this->optionset($user_row, 'viewcensors', $data['view_wordcensor']);
						$this->optionset($user_row, 'bbcode', $data['bbcode']);
						$this->optionset($user_row, 'smilies', $data['smilies']);
						$this->optionset($user_row, 'attachsig', $data['sig']);

						$sql_ary = [
							'user_options'			=> $user_row['user_options'],

							'user_allow_pm'			=> $data['allowpm'],
							'user_allow_viewemail'	=> $data['viewemail'],
							'user_allow_massemail'	=> $data['massemail'],
							'user_allow_viewonline'	=> !$data['hideonline'],
							'user_notify_type'		=> $data['notifymethod'],
							'user_notify_pm'		=> $data['notifypm'],

							'user_dateformat'		=> $data['dateformat'],
							'user_lang'				=> $data['lang'],
							'user_timezone'			=> $data['tz'],
							'user_style'			=> $data['style'],

							'user_topic_sortby_type'	=> $data['topic_sk'],
							'user_post_sortby_type'		=> $data['post_sk'],
							'user_topic_sortby_dir'		=> $data['topic_sd'],
							'user_post_sortby_dir'		=> $data['post_sd'],

							'user_topic_show_days'	=> $data['topic_st'],
							'user_post_show_days'	=> $data['post_st'],

							'user_notify'	=> $data['notify'],
						];

						/**
						 * Modify SQL query before users preferences are updated
						 *
						 * @event core.acp_users_prefs_modify_sql
						 * @var array	data			Array with users preferences data
						 * @var array	user_row		Array with user data
						 * @var array	sql_ary			SQL array with users preferences data to update
						 * @var array	error			Array with errors data
						 * @since 3.1.0-b3
						 */
						$vars = ['data', 'user_row', 'sql_ary', 'error'];
						extract($this->dispatcher->trigger_event('core.acp_users_prefs_modify_sql', compact($vars)));

						if (!count($error))
						{
							$sql = 'UPDATE ' . USERS_TABLE . '
								SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . "
								WHERE user_id = $user_id";
							$this->db->sql_query($sql);

							// Check if user has an active session
							if ($user_row['session_id'])
							{
								// We'll update the session if user_allow_viewonline has changed and the user is a bot
								// Or if it's a regular user and the admin set it to hide the session
								if ($user_row['user_allow_viewonline'] != $sql_ary['user_allow_viewonline'] && $user_row['user_type'] == USER_IGNORE
									|| $user_row['user_allow_viewonline'] && !$sql_ary['user_allow_viewonline'])
								{
									// We also need to check if the user has the permission to cloak.
									$user_auth = new \phpbb\auth\auth();
									$user_auth->acl($user_row);

									$session_sql_ary = [
										'session_viewonline'	=> ($user_auth->acl_get('u_hideonline')) ? $sql_ary['user_allow_viewonline'] : true,
									];

									$sql = 'UPDATE ' . SESSIONS_TABLE . '
										SET ' . $this->db->sql_build_array('UPDATE', $session_sql_ary) . "
										WHERE session_user_id = $user_id";
									$this->db->sql_query($sql);

									unset($user_auth);
								}
							}

							trigger_error($this->language->lang('USER_PREFS_UPDATED') . adm_back_link($this->u_action . '&amp;u=' . $user_id));
						}
					}

					// Replace "error" strings with their real, localised form
					$error = array_map([$user, 'lang'], $error);
				}

				$dateformat_options = '';
				foreach ($this->language->lang('dateformats') as $format => $null)
				{
					$dateformat_options .= '<option value="' . $format . '"' . (($format == $data['dateformat']) ? ' selected="selected"' : '') . '>';
					$dateformat_options .= $this->user->format_date(time(), $format, false) . ((strpos($format, '|') !== false) ? $this->language->lang('VARIANT_DATE_SEPARATOR') . $this->user->format_date(time(), $format, true) : '');
					$dateformat_options .= '</option>';
				}

				$s_custom = false;

				$dateformat_options .= '<option value="custom"';
				if (!isset($this->language->lang('dateformats')[$data['dateformat']]))
				{
					$dateformat_options .= ' selected="selected"';
					$s_custom = true;
				}
				$dateformat_options .= '>' . $this->language->lang('CUSTOM_DATEFORMAT') . '</option>';

				$sort_dir_text = ['a' => $this->language->lang('ASCENDING'), 'd' => $this->language->lang('DESCENDING')];

				// Topic ordering options
				$limit_topic_days = [0 => $this->language->lang('ALL_TOPICS'), 1 => $this->language->lang('1_DAY'), 7 => $this->language->lang('7_DAYS'), 14 => $this->language->lang('2_WEEKS'), 30 => $this->language->lang('1_MONTH'), 90 => $this->language->lang('3_MONTHS'), 180 => $this->language->lang('6_MONTHS'), 365 => $this->language->lang('1_YEAR')];
				$sort_by_topic_text = ['a' => $this->language->lang('AUTHOR'), 't' => $this->language->lang('POST_TIME'), 'r' => $this->language->lang('REPLIES'), 's' => $this->language->lang('SUBJECT'), 'v' => $this->language->lang('VIEWS')];

				// Post ordering options
				$limit_post_days = [0 => $this->language->lang('ALL_POSTS'), 1 => $this->language->lang('1_DAY'), 7 => $this->language->lang('7_DAYS'), 14 => $this->language->lang('2_WEEKS'), 30 => $this->language->lang('1_MONTH'), 90 => $this->language->lang('3_MONTHS'), 180 => $this->language->lang('6_MONTHS'), 365 => $this->language->lang('1_YEAR')];
				$sort_by_post_text = ['a' => $this->language->lang('AUTHOR'), 't' => $this->language->lang('POST_TIME'), 's' => $this->language->lang('SUBJECT')];

				$_options = ['topic', 'post'];
				foreach ($_options as $sort_option)
				{
					${'s_limit_' . $sort_option . '_days'} = '<select name="' . $sort_option . '_st">';
					foreach (${'limit_' . $sort_option . '_days'} as $day => $text)
					{
						$selected = ($data[$sort_option . '_st'] == $day) ? ' selected="selected"' : '';
						${'s_limit_' . $sort_option . '_days'} .= '<option value="' . $day . '"' . $selected . '>' . $text . '</option>';
					}
					${'s_limit_' . $sort_option . '_days'} .= '</select>';

					${'s_sort_' . $sort_option . '_key'} = '<select name="' . $sort_option . '_sk">';
					foreach (${'sort_by_' . $sort_option . '_text'} as $key => $text)
					{
						$selected = ($data[$sort_option . '_sk'] == $key) ? ' selected="selected"' : '';
						${'s_sort_' . $sort_option . '_key'} .= '<option value="' . $key . '"' . $selected . '>' . $text . '</option>';
					}
					${'s_sort_' . $sort_option . '_key'} .= '</select>';

					${'s_sort_' . $sort_option . '_dir'} = '<select name="' . $sort_option . '_sd">';
					foreach ($sort_dir_text as $key => $value)
					{
						$selected = ($data[$sort_option . '_sd'] == $key) ? ' selected="selected"' : '';
						${'s_sort_' . $sort_option . '_dir'} .= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
					}
					${'s_sort_' . $sort_option . '_dir'} .= '</select>';
				}

				phpbb_timezone_select($template, $user, $data['tz'], true);
				$user_prefs_data = [
					'S_PREFS'			=> true,
					'S_JABBER_DISABLED'	=> ($this->config['jab_enable'] && $user_row['user_jabber'] && @extension_loaded('xml')) ? false : true,

					'VIEW_EMAIL'		=> $data['viewemail'],
					'MASS_EMAIL'		=> $data['massemail'],
					'ALLOW_PM'			=> $data['allowpm'],
					'HIDE_ONLINE'		=> $data['hideonline'],
					'NOTIFY_EMAIL'		=> ($data['notifymethod'] == NOTIFY_EMAIL) ? true : false,
					'NOTIFY_IM'			=> ($data['notifymethod'] == NOTIFY_IM) ? true : false,
					'NOTIFY_BOTH'		=> ($data['notifymethod'] == NOTIFY_BOTH) ? true : false,
					'NOTIFY_PM'			=> $data['notifypm'],
					'BBCODE'			=> $data['bbcode'],
					'SMILIES'			=> $data['smilies'],
					'ATTACH_SIG'		=> $data['sig'],
					'NOTIFY'			=> $data['notify'],
					'VIEW_IMAGES'		=> $data['view_images'],
					'VIEW_FLASH'		=> $data['view_flash'],
					'VIEW_SMILIES'		=> $data['view_smilies'],
					'VIEW_SIGS'			=> $data['view_sigs'],
					'VIEW_AVATARS'		=> $data['view_avatars'],
					'VIEW_WORDCENSOR'	=> $data['view_wordcensor'],

					'S_TOPIC_SORT_DAYS'		=> $s_limit_topic_days,
					'S_TOPIC_SORT_KEY'		=> $s_sort_topic_key,
					'S_TOPIC_SORT_DIR'		=> $s_sort_topic_dir,
					'S_POST_SORT_DAYS'		=> $s_limit_post_days,
					'S_POST_SORT_KEY'		=> $s_sort_post_key,
					'S_POST_SORT_DIR'		=> $s_sort_post_dir,

					'DATE_FORMAT'			=> $data['dateformat'],
					'S_DATEFORMAT_OPTIONS'	=> $dateformat_options,
					'S_CUSTOM_DATEFORMAT'	=> $s_custom,
					'DEFAULT_DATEFORMAT'	=> $this->config['default_dateformat'],
					'A_DEFAULT_DATEFORMAT'	=> addslashes($this->config['default_dateformat']),

					'S_LANG_OPTIONS'	=> language_select($data['lang']),
					'S_STYLE_OPTIONS'	=> style_select($data['style']),
				];

				/**
				 * Modify users preferences data before assigning it to the template
				 *
				 * @event core.acp_users_prefs_modify_template_data
				 * @var array	data				Array with users preferences data
				 * @var array	user_row			Array with user data
				 * @var array	user_prefs_data		Array with users preferences data to be assigned to the template
				 * @since 3.1.0-b3
				 */
				$vars = ['data', 'user_row', 'user_prefs_data'];
				extract($this->dispatcher->trigger_event('core.acp_users_prefs_modify_template_data', compact($vars)));

				$this->template->assign_vars($user_prefs_data);

			break;

			case 'avatar':

				$avatars_enabled = false;
				/** @var \phpbb\avatar\manager $phpbb_avatar_manager */
				$phpbb_avatar_manager = $phpbb_container->get('avatar.manager');

				if ($this->config['allow_avatar'])
				{
					$avatar_drivers = $this->avatar_manager->get_enabled_drivers();

					// This is normalised data, without the user_ prefix
					$avatar_data = \phpbb\avatar\manager::clean_row($user_row, 'user');

					if ($submit)
					{
						if (check_form_key($form_name))
						{
							$driver_name = $this->avatar_manager->clean_driver_name($this->request->variable('avatar_driver', ''));

							if (in_array($driver_name, $avatar_drivers) && !$this->request->is_set_post('avatar_delete'))
							{
								$driver = $this->avatar_manager->get_driver($driver_name);
								$result = $driver->process_form($request, $template, $user, $avatar_data, $error);

								if ($result && empty($error))
								{
									// Success! Lets save the result in the database
									$result = [
										'user_avatar_type' => $driver_name,
										'user_avatar' => $result['avatar'],
										'user_avatar_width' => $result['avatar_width'],
										'user_avatar_height' => $result['avatar_height'],
									];

									/**
									 * Modify users preferences data before assigning it to the template
									 *
									 * @event core.acp_users_avatar_sql
									 * @var array	user_row	Array with user data
									 * @var array	result		Array with user avatar data to be updated in the DB
									 * @since 3.2.4-RC1
									 */
									$vars = ['user_row', 'result'];
									extract($this->dispatcher->trigger_event('core.acp_users_avatar_sql', compact($vars)));

									$sql = 'UPDATE ' . USERS_TABLE . '
										SET ' . $this->db->sql_build_array('UPDATE', $result) . '
										WHERE user_id = ' . (int) $user_id;

									$this->db->sql_query($sql);
									trigger_error($this->language->lang('USER_AVATAR_UPDATED') . adm_back_link($this->u_action . '&amp;u=' . $user_id));
								}
							}
						}
						else
						{
							trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
						}
					}

					// Handle deletion of avatars
					if ($this->request->is_set_post('avatar_delete'))
					{
						if (!confirm_box(true))
						{
							confirm_box(false, $this->language->lang('CONFIRM_AVATAR_DELETE'), build_hidden_fields([
									'avatar_delete'	=> true])
							);
						}
						else
						{
							$this->avatar_manager->handle_avatar_delete($db, $user, $avatar_data, USERS_TABLE, 'user_');

							trigger_error($this->language->lang('USER_AVATAR_UPDATED') . adm_back_link($this->u_action . '&amp;u=' . $user_id));
						}
					}

					$selected_driver = $this->avatar_manager->clean_driver_name($this->request->variable('avatar_driver', $user_row['user_avatar_type']));

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

						if ($driver->prepare_form($request, $template, $user, $avatar_data, $error))
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

				// Avatar manager is not initialized if avatars are disabled
				if (isset($phpbb_avatar_manager))
				{
					// Replace "error" strings with their real, localised form
					$error = $this->avatar_manager->localize_errors($user, $error);
				}

				$avatar = phpbb_get_user_avatar($user_row, 'USER_AVATAR', true);

				$this->template->assign_vars([
					'S_AVATAR'	=> true,
					'ERROR'			=> (!empty($error)) ? implode('<br />', $error) : '',
					'AVATAR'		=> (empty($avatar) ? '<img src="' . $this->admin_path . 'images/no_avatar.gif" alt="" />' : $avatar),

					'S_FORM_ENCTYPE'	=> ' enctype="multipart/form-data"',

					'L_AVATAR_EXPLAIN'	=> $this->language->lang(($this->config['avatar_filesize'] == 0) ? 'AVATAR_EXPLAIN_NO_FILESIZE' : 'AVATAR_EXPLAIN', $this->config['avatar_max_width'], $this->config['avatar_max_height'], $this->config['avatar_filesize'] / 1024),

					'S_AVATARS_ENABLED'		=> ($this->config['allow_avatar'] && $avatars_enabled),
				]);

			break;

			case 'rank':

				if ($submit)
				{
					if (!check_form_key($form_name))
					{
						trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
					}

					$rank_id = $this->request->variable('user_rank', 0);

					$sql = 'UPDATE ' . USERS_TABLE . "
						SET user_rank = $rank_id
						WHERE user_id = $user_id";
					$this->db->sql_query($sql);

					trigger_error($this->language->lang('USER_RANK_UPDATED') . adm_back_link($this->u_action . '&amp;u=' . $user_id));
				}

				$sql = 'SELECT *
					FROM ' . RANKS_TABLE . '
					WHERE rank_special = 1
					ORDER BY rank_title';
				$result = $this->db->sql_query($sql);

				$s_rank_options = '<option value="0"' . ((!$user_row['user_rank']) ? ' selected="selected"' : '') . '>' . $this->language->lang('NO_SPECIAL_RANK') . '</option>';

				while ($row = $this->db->sql_fetchrow($result))
				{
					$selected = ($user_row['user_rank'] && $row['rank_id'] == $user_row['user_rank']) ? ' selected="selected"' : '';
					$s_rank_options .= '<option value="' . $row['rank_id'] . '"' . $selected . '>' . $row['rank_title'] . '</option>';
				}
				$this->db->sql_freeresult($result);

				$this->template->assign_vars([
					'S_RANK'			=> true,
					'S_RANK_OPTIONS'	=> $s_rank_options]
				);

			break;

			case 'sig':

				if (!function_exists('display_custom_bbcodes'))
				{
					include($this->root_path . 'includes/functions_display.' . $this->php_ext);
				}

				$enable_bbcode	= ($this->config['allow_sig_bbcode']) ? $this->optionget($user_row, 'sig_bbcode') : false;
				$enable_smilies	= ($this->config['allow_sig_smilies']) ? $this->optionget($user_row, 'sig_smilies') : false;
				$enable_urls	= ($this->config['allow_sig_links']) ? $this->optionget($user_row, 'sig_links') : false;

				$bbcode_flags = ($enable_bbcode ? OPTION_FLAG_BBCODE : 0) + ($enable_smilies ? OPTION_FLAG_SMILIES : 0) + ($enable_urls ? OPTION_FLAG_LINKS : 0);

				$decoded_message	= generate_text_for_edit($user_row['user_sig'], $user_row['user_sig_bbcode_uid'], $bbcode_flags);
				$signature			= $this->request->variable('signature', $decoded_message['text'], true);
				$signature_preview	= '';

				if ($submit || $this->request->is_set_post('preview'))
				{
					$enable_bbcode	= ($this->config['allow_sig_bbcode']) ? !$this->request->variable('disable_bbcode', false) : false;
					$enable_smilies	= ($this->config['allow_sig_smilies']) ? !$this->request->variable('disable_smilies', false) : false;
					$enable_urls	= ($this->config['allow_sig_links']) ? !$this->request->variable('disable_magic_url', false) : false;

					if (!check_form_key($form_name))
					{
						$error[] = 'FORM_INVALID';
					}
				}

				$bbcode_uid = $bbcode_bitfield = $bbcode_flags = '';
				$warn_msg = generate_text_for_storage(
					$signature,
					$bbcode_uid,
					$bbcode_bitfield,
					$bbcode_flags,
					$enable_bbcode,
					$enable_urls,
					$enable_smilies,
					$this->config['allow_sig_img'],
					$this->config['allow_sig_flash'],
					true,
					$this->config['allow_sig_links'],
					'sig'
				);

				if (count($warn_msg))
				{
					$error += $warn_msg;
				}

				if (!$submit)
				{
					// Parse it for displaying
					$signature_preview = generate_text_for_display($signature, $bbcode_uid, $bbcode_bitfield, $bbcode_flags);
				}
				else
				{
					if (!count($error))
					{
						$this->optionset($user_row, 'sig_bbcode', $enable_bbcode);
						$this->optionset($user_row, 'sig_smilies', $enable_smilies);
						$this->optionset($user_row, 'sig_links', $enable_urls);

						$sql_ary = [
							'user_sig'					=> $signature,
							'user_options'				=> $user_row['user_options'],
							'user_sig_bbcode_uid'		=> $bbcode_uid,
							'user_sig_bbcode_bitfield'	=> $bbcode_bitfield,
						];

						/**
						 * Modify user signature before it is stored in the DB
						 *
						 * @event core.acp_users_modify_signature_sql_ary
						 * @var array	user_row	Array with user data
						 * @var array	sql_ary		Array with user signature data to be updated in the DB
						 * @since 3.2.4-RC1
						 */
						$vars = ['user_row', 'sql_ary'];
						extract($this->dispatcher->trigger_event('core.acp_users_modify_signature_sql_ary', compact($vars)));

						$sql = 'UPDATE ' . USERS_TABLE . '
							SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
							WHERE user_id = ' . $user_id;
						$this->db->sql_query($sql);

						trigger_error($this->language->lang('USER_SIG_UPDATED') . adm_back_link($this->u_action . '&amp;u=' . $user_id));
					}
				}

				// Replace "error" strings with their real, localised form
				$error = array_map([$user, 'lang'], $error);

				if ($this->request->is_set_post('preview'))
				{
					$decoded_message = generate_text_for_edit($signature, $bbcode_uid, $bbcode_flags);
				}

				/** @var \phpbb\controller\helper $controller_helper */
				$controller_helper = $phpbb_container->get('controller.helper');

				$this->template->assign_vars([
					'S_SIGNATURE'		=> true,

					'SIGNATURE'			=> $decoded_message['text'],
					'SIGNATURE_PREVIEW'	=> $signature_preview,

					'S_BBCODE_CHECKED'		=> (!$enable_bbcode) ? ' checked="checked"' : '',
					'S_SMILIES_CHECKED'		=> (!$enable_smilies) ? ' checked="checked"' : '',
					'S_MAGIC_URL_CHECKED'	=> (!$enable_urls) ? ' checked="checked"' : '',

					'BBCODE_STATUS'			=> $this->language->lang(($this->config['allow_sig_bbcode'] ? 'BBCODE_IS_ON' : 'BBCODE_IS_OFF'), '<a href="' . $this->controller_helper->route('phpbb_help_bbcode_controller') . '">', '</a>'),
					'SMILIES_STATUS'		=> ($this->config['allow_sig_smilies']) ? $this->language->lang('SMILIES_ARE_ON') : $this->language->lang('SMILIES_ARE_OFF'),
					'IMG_STATUS'			=> ($this->config['allow_sig_img']) ? $this->language->lang('IMAGES_ARE_ON') : $this->language->lang('IMAGES_ARE_OFF'),
					'FLASH_STATUS'			=> ($this->config['allow_sig_flash']) ? $this->language->lang('FLASH_IS_ON') : $this->language->lang('FLASH_IS_OFF'),
					'URL_STATUS'			=> ($this->config['allow_sig_links']) ? $this->language->lang('URL_IS_ON') : $this->language->lang('URL_IS_OFF'),

					'L_SIGNATURE_EXPLAIN'	=> $this->language->lang('SIGNATURE_EXPLAIN', (int) $this->config['max_sig_chars']),

					'S_BBCODE_ALLOWED'		=> $this->config['allow_sig_bbcode'],
					'S_SMILIES_ALLOWED'		=> $this->config['allow_sig_smilies'],
					'S_BBCODE_IMG'			=> ($this->config['allow_sig_img']) ? true : false,
					'S_BBCODE_FLASH'		=> ($this->config['allow_sig_flash']) ? true : false,
					'S_LINKS_ALLOWED'		=> ($this->config['allow_sig_links']) ? true : false]
				);

				// Assigning custom bbcodes
				display_custom_bbcodes();

			break;

			case 'attach':
				/* @var $pagination \phpbb\pagination */
				$pagination = $phpbb_container->get('pagination');

				$start		= $this->request->variable('start', 0);
				$deletemark = ($this->request->is_set_post('delmarked')) ? true : false;
				$marked		= $this->request->variable('mark', [0]);

				// Sort keys
				$sort_key	= $this->request->variable('sk', 'a');
				$sort_dir	= $this->request->variable('sd', 'd');

				if ($deletemark && count($marked))
				{
					$sql = 'SELECT attach_id
						FROM ' . ATTACHMENTS_TABLE . '
						WHERE poster_id = ' . $user_id . '
							AND is_orphan = 0
							AND ' . $this->db->sql_in_set('attach_id', $marked);
					$result = $this->db->sql_query($sql);

					$marked = [];
					while ($row = $this->db->sql_fetchrow($result))
					{
						$marked[] = $row['attach_id'];
					}
					$this->db->sql_freeresult($result);
				}

				if ($deletemark && count($marked))
				{
					if (confirm_box(true))
					{
						$sql = 'SELECT real_filename
							FROM ' . ATTACHMENTS_TABLE . '
							WHERE ' . $this->db->sql_in_set('attach_id', $marked);
						$result = $this->db->sql_query($sql);

						$log_attachments = [];
						while ($row = $this->db->sql_fetchrow($result))
						{
							$log_attachments[] = $row['real_filename'];
						}
						$this->db->sql_freeresult($result);

						/** @var \phpbb\attachment\manager $attachment_manager */
						$attachment_manager = $phpbb_container->get('attachment.manager');
						$attachment_manager->delete('attach', $marked);
						unset($attachment_manager);

						$message = (count($log_attachments) == 1) ? $this->language->lang('ATTACHMENT_DELETED') : $this->language->lang('ATTACHMENTS_DELETED');

						$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_ATTACHMENTS_DELETED', false, [implode($this->language->lang('COMMA_SEPARATOR'), $log_attachments)]);
						trigger_error($message . adm_back_link($this->u_action . '&amp;u=' . $user_id));
					}
					else
					{
						confirm_box(false, $this->language->lang('CONFIRM_OPERATION'), build_hidden_fields([
							'u'				=> $user_id,
							'i'				=> $id,
							'mode'			=> $mode,
							'action'		=> $action,
							'delmarked'		=> true,
							'mark'			=> $marked])
						);
					}
				}

				$sk_text = ['a' => $this->language->lang('SORT_FILENAME'), 'c' => $this->language->lang('SORT_EXTENSION'), 'd' => $this->language->lang('SORT_SIZE'), 'e' => $this->language->lang('SORT_DOWNLOADS'), 'f' => $this->language->lang('SORT_POST_TIME'), 'g' => $this->language->lang('SORT_TOPIC_TITLE')];
				$sk_sql = ['a' => 'a.real_filename', 'c' => 'a.extension', 'd' => 'a.filesize', 'e' => 'a.download_count', 'f' => 'a.filetime', 'g' => 't.topic_title'];

				$sd_text = ['a' => $this->language->lang('ASCENDING'), 'd' => $this->language->lang('DESCENDING')];

				$s_sort_key = '';
				foreach ($sk_text as $key => $value)
				{
					$selected = ($sort_key == $key) ? ' selected="selected"' : '';
					$s_sort_key .= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
				}

				$s_sort_dir = '';
				foreach ($sd_text as $key => $value)
				{
					$selected = ($sort_dir == $key) ? ' selected="selected"' : '';
					$s_sort_dir .= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
				}

				if (!isset($sk_sql[$sort_key]))
				{
					$sort_key = 'a';
				}

				$order_by = $sk_sql[$sort_key] . ' ' . (($sort_dir == 'a') ? 'ASC' : 'DESC');

				$sql = 'SELECT COUNT(attach_id) as num_attachments
					FROM ' . ATTACHMENTS_TABLE . "
					WHERE poster_id = $user_id
						AND is_orphan = 0";
				$result = $this->db->sql_query_limit($sql, 1);
				$num_attachments = (int) $this->db->sql_fetchfield('num_attachments');
				$this->db->sql_freeresult($result);

				$sql = 'SELECT a.*, t.topic_title, p.message_subject as message_title
					FROM ' . ATTACHMENTS_TABLE . ' a
						LEFT JOIN ' . TOPICS_TABLE . ' t ON (a.topic_id = t.topic_id
							AND a.in_message = 0)
						LEFT JOIN ' . PRIVMSGS_TABLE . ' p ON (a.post_msg_id = p.msg_id
							AND a.in_message = 1)
					WHERE a.poster_id = ' . $user_id . "
						AND a.is_orphan = 0
					ORDER BY $order_by";
				$result = $this->db->sql_query_limit($sql, $this->config['topics_per_page'], $start);

				while ($row = $this->db->sql_fetchrow($result))
				{
					if ($row['in_message'])
					{
						$view_topic = append_sid("{$this->root_path}ucp.$this->php_ext", "i=pm&amp;p={$row['post_msg_id']}");
					}
					else
					{
						$view_topic = append_sid("{$this->root_path}viewtopic.$this->php_ext", "t={$row['topic_id']}&amp;p={$row['post_msg_id']}") . '#p' . $row['post_msg_id'];
					}

					$this->template->assign_block_vars('attach', [
						'REAL_FILENAME'		=> $row['real_filename'],
						'COMMENT'			=> nl2br($row['attach_comment']),
						'EXTENSION'			=> $row['extension'],
						'SIZE'				=> get_formatted_filesize($row['filesize']),
						'DOWNLOAD_COUNT'	=> $row['download_count'],
						'POST_TIME'			=> $this->user->format_date($row['filetime']),
						'TOPIC_TITLE'		=> ($row['in_message']) ? $row['message_title'] : $row['topic_title'],

						'ATTACH_ID'			=> $row['attach_id'],
						'POST_ID'			=> $row['post_msg_id'],
						'TOPIC_ID'			=> $row['topic_id'],

						'S_IN_MESSAGE'		=> $row['in_message'],

						'U_DOWNLOAD'		=> append_sid("{$this->root_path}download/file.$this->php_ext", 'mode=view&amp;id=' . $row['attach_id']),
						'U_VIEW_TOPIC'		=> $view_topic]
					);
				}
				$this->db->sql_freeresult($result);

				$base_url = $this->u_action . "&amp;u=$user_id&amp;sk=$sort_key&amp;sd=$sort_dir";
				$this->pagination->generate_template_pagination($base_url, 'pagination', 'start', $num_attachments, $this->config['topics_per_page'], $start);

				$this->template->assign_vars([
					'S_ATTACHMENTS'		=> true,
					'S_SORT_KEY'		=> $s_sort_key,
					'S_SORT_DIR'		=> $s_sort_dir,
				]);

			break;

			case 'groups':

				if (!function_exists('group_user_attributes'))
				{
					include($this->root_path . 'includes/functions_user.' . $this->php_ext);
				}

				$this->language->add_lang(['groups', 'acp/groups']);
				$group_id = $this->request->variable('g', 0);

				if ($group_id)
				{
					// Check the founder only entry for this group to make sure everything is well
					$sql = 'SELECT group_founder_manage
						FROM ' . GROUPS_TABLE . '
						WHERE group_id = ' . $group_id;
					$result = $this->db->sql_query($sql);
					$founder_manage = (int) $this->db->sql_fetchfield('group_founder_manage');
					$this->db->sql_freeresult($result);

					if ($this->user->data['user_type'] != USER_FOUNDER && $founder_manage)
					{
						trigger_error($this->language->lang('NOT_ALLOWED_MANAGE_GROUP') . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
					}
				}

				switch ($action)
				{
					case 'demote':
					case 'promote':
					case 'default':
						if (!$group_id)
						{
							trigger_error($this->language->lang('NO_GROUP') . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
						}

						if (!check_link_hash($this->request->variable('hash', ''), 'acp_users'))
						{
							trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
						}

						group_user_attributes($action, $group_id, $user_id);

						if ($action == 'default')
						{
							$user_row['group_id'] = $group_id;
						}
					break;

					case 'delete':

						if (confirm_box(true))
						{
							if (!$group_id)
							{
								trigger_error($this->language->lang('NO_GROUP') . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
							}

							if ($error = group_user_del($group_id, $user_id))
							{
								trigger_error($this->language->lang($error) . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
							}

							$error = [];

							// The delete action was successful - therefore update the user row...
							$sql = 'SELECT u.*, s.*
								FROM ' . USERS_TABLE . ' u
									LEFT JOIN ' . SESSIONS_TABLE . ' s ON (s.session_user_id = u.user_id)
								WHERE u.user_id = ' . $user_id . '
								ORDER BY s.session_time DESC';
							$result = $this->db->sql_query_limit($sql, 1);
							$user_row = $this->db->sql_fetchrow($result);
							$this->db->sql_freeresult($result);
						}
						else
						{
							confirm_box(false, $this->language->lang('CONFIRM_OPERATION'), build_hidden_fields([
								'u'				=> $user_id,
								'i'				=> $id,
								'mode'			=> $mode,
								'action'		=> $action,
								'g'				=> $group_id])
							);
						}

					break;

					case 'approve':

						if (confirm_box(true))
						{
							if (!$group_id)
							{
								trigger_error($this->language->lang('NO_GROUP') . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
							}
							group_user_attributes($action, $group_id, $user_id);
						}
						else
						{
							confirm_box(false, $this->language->lang('CONFIRM_OPERATION'), build_hidden_fields([
								'u'				=> $user_id,
								'i'				=> $id,
								'mode'			=> $mode,
								'action'		=> $action,
								'g'				=> $group_id])
							);
						}

					break;
				}

				// Add user to group?
				if ($submit)
				{
					if (!check_form_key($form_name))
					{
						trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
					}

					if (!$group_id)
					{
						trigger_error($this->language->lang('NO_GROUP') . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
					}

					// Add user/s to group
					if ($error = group_user_add($group_id, $user_id))
					{
						trigger_error($this->language->lang($error) . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
					}

					$error = [];
				}

				/** @var \phpbb\group\helper $group_helper */
				$group_helper = $phpbb_container->get('group_helper');

				$sql = 'SELECT ug.*, g.*
					FROM ' . GROUPS_TABLE . ' g, ' . USER_GROUP_TABLE . " ug
					WHERE ug.user_id = $user_id
						AND g.group_id = ug.group_id
					ORDER BY g.group_type DESC, ug.user_pending ASC, g.group_name";
				$result = $this->db->sql_query($sql);

				$i = 0;
				$group_data = $id_ary = [];
				while ($row = $this->db->sql_fetchrow($result))
				{
					$type = ($row['group_type'] == GROUP_SPECIAL) ? 'special' : (($row['user_pending']) ? 'pending' : 'normal');

					$group_data[$type][$i]['group_id']		= $row['group_id'];
					$group_data[$type][$i]['group_name']	= $row['group_name'];
					$group_data[$type][$i]['group_leader']	= ($row['group_leader']) ? 1 : 0;

					$id_ary[] = $row['group_id'];

					$i++;
				}
				$this->db->sql_freeresult($result);

				// Select box for other groups
				$sql = 'SELECT group_id, group_name, group_type, group_founder_manage
					FROM ' . GROUPS_TABLE . '
					' . ((count($id_ary)) ? 'WHERE ' . $this->db->sql_in_set('group_id', $id_ary, true) : '') . '
					ORDER BY group_type DESC, group_name ASC';
				$result = $this->db->sql_query($sql);

				$s_group_options = '';
				while ($row = $this->db->sql_fetchrow($result))
				{
					if (!$this->config['coppa_enable'] && $row['group_name'] == 'REGISTERED_COPPA')
					{
						continue;
					}

					// Do not display those groups not allowed to be managed
					if ($this->user->data['user_type'] != USER_FOUNDER && $row['group_founder_manage'])
					{
						continue;
					}

					$s_group_options .= '<option' . (($row['group_type'] == GROUP_SPECIAL) ? ' class="sep"' : '') . ' value="' . $row['group_id'] . '">' . $this->group_helper->get_name($row['group_name']) . '</option>';
				}
				$this->db->sql_freeresult($result);

				$current_type = '';
				foreach ($group_data as $group_type => $data_ary)
				{
					if ($current_type != $group_type)
					{
						$this->template->assign_block_vars('group', [
							'S_NEW_GROUP_TYPE'		=> true,
							'GROUP_TYPE'			=> $this->language->lang('USER_GROUP_' . strtoupper($group_type))]
						);
					}

					foreach ($data_ary as $data)
					{
						$this->template->assign_block_vars('group', [
							'U_EDIT_GROUP'		=> append_sid("{$this->admin_path}index.$this->php_ext", "i=groups&amp;mode=manage&amp;action=edit&amp;u=$user_id&amp;g={$data['group_id']}&amp;back_link=acp_users_groups"),
							'U_DEFAULT'			=> $this->u_action . "&amp;action=default&amp;u=$user_id&amp;g=" . $data['group_id'] . '&amp;hash=' . generate_link_hash('acp_users'),
							'U_DEMOTE_PROMOTE'	=> $this->u_action . '&amp;action=' . (($data['group_leader']) ? 'demote' : 'promote') . "&amp;u=$user_id&amp;g=" . $data['group_id'] . '&amp;hash=' . generate_link_hash('acp_users'),
							'U_DELETE'			=> $this->u_action . "&amp;action=delete&amp;u=$user_id&amp;g=" . $data['group_id'],
							'U_APPROVE'			=> ($group_type == 'pending') ? $this->u_action . "&amp;action=approve&amp;u=$user_id&amp;g=" . $data['group_id'] : '',

							'GROUP_NAME'		=> $this->group_helper->get_name($data['group_name']),
							'L_DEMOTE_PROMOTE'	=> ($data['group_leader']) ? $this->language->lang('GROUP_DEMOTE') : $this->language->lang('GROUP_PROMOTE'),

							'S_IS_MEMBER'		=> ($group_type != 'pending') ? true : false,
							'S_NO_DEFAULT'		=> ($user_row['group_id'] != $data['group_id']) ? true : false,
							'S_SPECIAL_GROUP'	=> ($group_type == 'special') ? true : false,
							]
						);
					}
				}

				$this->template->assign_vars([
					'S_GROUPS'			=> true,
					'S_GROUP_OPTIONS'	=> $s_group_options]
				);

			break;

			case 'perm':

				if (!class_exists('auth_admin'))
				{
					include($this->root_path . 'includes/acp/auth.' . $this->php_ext);
				}

				$auth_admin = new auth_admin();

				$this->language->add_lang('acp/permissions');
				add_permission_language();

				$forum_id = $this->request->variable('f', 0);

				// Global Permissions
				if (!$forum_id)
				{
					// Select auth options
					$sql = 'SELECT auth_option, is_local, is_global
						FROM ' . ACL_OPTIONS_TABLE . '
						WHERE auth_option ' . $this->db->sql_like_expression($this->db->get_any_char() . '_') . '
							AND is_						ORDER BY auth_option';
					$result = $this->db->sql_query($sql);

					$hold_ary = [];

					while ($row = $this->db->sql_fetchrow($result))
					{
						$hold_ary = $auth_admin->get_mask('view', $user_id, false, false, $row['auth_option'], 'global', ACL_NEVER);
						$auth_admin->display_mask('view', $row['auth_option'], $hold_ary, 'user', false, false);
					}
					$this->db->sql_freeresult($result);

					unset($hold_ary);
				}
				else
				{
					$sql = 'SELECT auth_option, is_local, is_global
						FROM ' . ACL_OPTIONS_TABLE . '
						WHERE auth_option ' . $db->sql_like_expression($db->get_any_char() . '_') . '
							AND is_global = 1
						ORDER BY auth_option';
					$result = $this->db->sql_query($sql);
					while ($row = $this->db->sql_fetchrow($result))
					{
						$hold_ary = $auth_admin->get_mask('view', $user_id, false, $forum_id, $row['auth_option'], 'local', ACL_NEVER);
						$auth_admin->display_mask('view', $row['auth_option'], $hold_ary, 'user', true, false);
					}
					$this->db->sql_freeresult($result);
				}

				$s_forum_options = '<option value="0"' . ((!$forum_id) ? ' selected="selected"' : '') . '>' . $this->language->lang('VIEW_GLOBAL_PERMS') . '</option>';
				$s_forum_options .= make_forum_select($forum_id, false, true, false, false, false);

				$this->template->assign_vars([
					'S_PERMISSIONS'				=> true,

					'S_GLOBAL'					=> (!$forum_id) ? true : false,
					'S_FORUM_OPTIONS'			=> $s_forum_options,

					'U_ACTION'					=> $this->u_action . '&amp;u=' . $user_id,
					'U_USER_PERMISSIONS'		=> append_sid("{$this->admin_path}index.$this->php_ext" ,'i=permissions&amp;mode=setting_user_global&amp;user_id[]=' . $user_id),
					'U_USER_FORUM_PERMISSIONS'	=> append_sid("{$this->admin_path}index.$this->php_ext", 'i=permissions&amp;mode=setting_user_local&amp;user_id[]=' . $user_id)]
				);

			break;

			default:

				/**
				 * Additional modes provided by extensions
				 *
				 * @event core.acp_users_mode_add
				 * @var string	mode			New mode
				 * @var int		user_id			User id of the user to manage
				 * @var array	user_row		Array with user data
				 * @var array	error			Array with errors data
				 * @since 3.2.2-RC1
				 */
				$vars = ['mode', 'user_id', 'user_row', 'error'];
				extract($this->dispatcher->trigger_event('core.acp_users_mode_add', compact($vars)));

			break;
		}

		// Assign general variables
		$this->template->assign_vars([
			'S_ERROR'			=> (count($error)) ? true : false,
			'ERROR_MSG'			=> (count($error)) ? implode('<br />', $error) : '']
		);
	}

	/**
	 * Set option bit field for user options in a user row array.
	 *
	 * Optionset replacement for this module based on $this->user->optionset.
	 *
	 * @param array $user_row Row from the users table.
	 * @param int $key Option key, as defined in $this->user->keyoptions property.
	 * @param bool $value True to set the option, false to clear the option.
	 * @param int $data Current bit field value, or false to use $user_row['user_options']
	 * @return int|bool If $data is false, the bit field is modified and
	 * 					written back to $user_row['user_options'], and
	 * 					return value is true if the bit field changed and
	 * 					false otherwise. If $data is not false, the new
	 * 					bitfield value is returned.
	 */
	function optionset(&$user_row, $key, $value, $data = false)
	{
		$var = ($data !== false) ? $data : $user_row['user_options'];

		$new_var = phpbb_optionset($this->user->keyoptions[$key], $value, $var);

		if ($data === false)
		{
			if ($new_var != $var)
			{
				$user_row['user_options'] = $new_var;
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return $new_var;
		}
	}

	/**
	 * Get option bit field from user options in a user row array.
	 *
	 * Optionget replacement for this module based on $this->user->optionget.
	 *
	 * @param array $user_row Row from the users table.
	 * @param int $key option key, as defined in $this->user->keyoptions property.
	 * @param int $data bit field value to use, or false to use $user_row['user_options']
	 * @return bool true if the option is set in the bit field, false otherwise
	 */
	function optionget(&$user_row, $key, $data = false)
	{
		$var = ($data !== false) ? $data : $user_row['user_options'];
		return phpbb_optionget($this->user->keyoptions[$key], $var);
	}
}
