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

class acp_users
{
	var $u_action;
	var $p_master;

	function acp_users(&$p_master)
	{
		$this->p_master = &$p_master;
	}

	function main($id, $mode)
	{
		global $config, $db, $user, $auth, $template, $cache;
		global $phpbb_root_path, $phpbb_admin_path, $phpEx, $table_prefix, $file_uploads;
		global $phpbb_dispatcher, $request;
		global $phpbb_container;

		$user->add_lang(array('posting', 'ucp', 'acp/users'));
		$this->tpl_name = 'acp_users';

		$error		= array();
		$username	= utf8_normalize_nfc(request_var('username', '', true));
		$user_id	= request_var('u', 0);
		$action		= request_var('action', '');

		$submit		= (isset($_POST['update']) && !isset($_POST['cancel'])) ? true : false;

		$form_name = 'acp_users';
		add_form_key($form_name);

		// Whois (special case)
		if ($action == 'whois')
		{
			include($phpbb_root_path . 'includes/functions_user.' . $phpEx);

			$this->page_title = 'WHOIS';
			$this->tpl_name = 'simple_body';

			$user_ip = phpbb_ip_normalise(request_var('user_ip', ''));
			$domain = gethostbyaddr($user_ip);
			$ipwhois = user_ipwhois($user_ip);

			$template->assign_vars(array(
				'MESSAGE_TITLE'		=> sprintf($user->lang['IP_WHOIS_FOR'], $domain),
				'MESSAGE_TEXT'		=> nl2br($ipwhois))
			);

			return;
		}

		// Show user selection mask
		if (!$username && !$user_id)
		{
			$this->page_title = 'SELECT_USER';

			$template->assign_vars(array(
				'U_ACTION'			=> $this->u_action,
				'ANONYMOUS_USER_ID'	=> ANONYMOUS,

				'S_SELECT_USER'		=> true,
				'U_FIND_USERNAME'	=> append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=searchuser&amp;form=select_user&amp;field=username&amp;select_single=true'),
			));

			return;
		}

		if (!$user_id)
		{
			$sql = 'SELECT user_id
				FROM ' . USERS_TABLE . "
				WHERE username_clean = '" . $db->sql_escape(utf8_clean_string($username)) . "'";
			$result = $db->sql_query($sql);
			$user_id = (int) $db->sql_fetchfield('user_id');
			$db->sql_freeresult($result);

			if (!$user_id)
			{
				trigger_error($user->lang['NO_USER'] . adm_back_link($this->u_action), E_USER_WARNING);
			}
		}

		// Generate content for all modes
		$sql = 'SELECT u.*, s.*
			FROM ' . USERS_TABLE . ' u
				LEFT JOIN ' . SESSIONS_TABLE . ' s ON (s.session_user_id = u.user_id)
			WHERE u.user_id = ' . $user_id . '
			ORDER BY s.session_time DESC';
		$result = $db->sql_query_limit($sql, 1);
		$user_row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if (!$user_row)
		{
			trigger_error($user->lang['NO_USER'] . adm_back_link($this->u_action), E_USER_WARNING);
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
		$result = $db->sql_query($sql);

		$dropdown_modes = array();
		while ($row = $db->sql_fetchrow($result))
		{
			if (!$this->p_master->module_auth_self($row['module_auth']))
			{
				continue;
			}

			$dropdown_modes[$row['module_mode']] = true;
		}
		$db->sql_freeresult($result);

		foreach ($dropdown_modes as $module_mode => $null)
		{
			$selected = ($mode == $module_mode) ? ' selected="selected"' : '';
			$s_form_options .= '<option value="' . $module_mode . '"' . $selected . '>' . $user->lang['ACP_USER_' . strtoupper($module_mode)] . '</option>';
		}

		$template->assign_vars(array(
			'U_BACK'			=> $this->u_action,
			'U_MODE_SELECT'		=> append_sid("{$phpbb_admin_path}index.$phpEx", "i=$id&amp;u=$user_id"),
			'U_ACTION'			=> $this->u_action . '&amp;u=' . $user_id,
			'S_FORM_OPTIONS'	=> $s_form_options,
			'MANAGED_USERNAME'	=> $user_row['username'])
		);

		// Prevent normal users/admins change/view founders if they are not a founder by themselves
		if ($user->data['user_type'] != USER_FOUNDER && $user_row['user_type'] == USER_FOUNDER)
		{
			trigger_error($user->lang['NOT_MANAGE_FOUNDER'] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		$this->page_title = $user_row['username'] . ' :: ' . $user->lang('ACP_USER_' . strtoupper($mode));

		switch ($mode)
		{
			case 'overview':

				include($phpbb_root_path . 'includes/functions_user.' . $phpEx);

				$user->add_lang('acp/ban');

				$delete			= request_var('delete', 0);
				$delete_type	= request_var('delete_type', '');
				$ip				= request_var('ip', 'ip');

				/**
				 * Run code at beginning of ACP users overview
				 *
				 * @event core.acp_users_overview_before
				 * @var	array   user_row    Current user data
				 * @var	string  mode        Active module
				 * @var	string  action      Module that should be run
				 * @var	bool    submit      Do we display the form only
				 *                          or did the user press submit
				 * @var	array   error       Array holding error messages
				 * @since 3.1.3-RC1
				 */
				$vars = array('user_row', 'mode', 'action', 'submit', 'error');
				extract($phpbb_dispatcher->trigger_event('core.acp_users_overview_before', compact($vars)));

				if ($submit)
				{
					if ($delete)
					{
						if (!$auth->acl_get('a_userdel'))
						{
							trigger_error($user->lang['NO_AUTH_OPERATION'] . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
						}

						// Check if the user wants to remove himself or the guest user account
						if ($user_id == ANONYMOUS)
						{
							trigger_error($user->lang['CANNOT_REMOVE_ANONYMOUS'] . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
						}

						// Founders can not be deleted.
						if ($user_row['user_type'] == USER_FOUNDER)
						{
							trigger_error($user->lang['CANNOT_REMOVE_FOUNDER'] . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
						}

						if ($user_id == $user->data['user_id'])
						{
							trigger_error($user->lang['CANNOT_REMOVE_YOURSELF'] . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
						}

						if ($delete_type)
						{
							if (confirm_box(true))
							{
								user_delete($delete_type, $user_id, $user_row['username']);

								add_log('admin', 'LOG_USER_DELETED', $user_row['username']);
								trigger_error($user->lang['USER_DELETED'] . adm_back_link($this->u_action));
							}
							else
							{
								confirm_box(false, $user->lang['CONFIRM_OPERATION'], build_hidden_fields(array(
									'u'				=> $user_id,
									'i'				=> $id,
									'mode'			=> $mode,
									'action'		=> $action,
									'update'		=> true,
									'delete'		=> 1,
									'delete_type'	=> $delete_type))
								);
							}
						}
						else
						{
							trigger_error($user->lang['NO_MODE'] . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
						}
					}

					// Handle quicktool actions
					switch ($action)
					{
						case 'banuser':
						case 'banemail':
						case 'banip':

							if ($user_id == $user->data['user_id'])
							{
								trigger_error($user->lang['CANNOT_BAN_YOURSELF'] . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
							}

							if ($user_id == ANONYMOUS)
							{
								trigger_error($user->lang['CANNOT_BAN_ANONYMOUS'] . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
							}

							if ($user_row['user_type'] == USER_FOUNDER)
							{
								trigger_error($user->lang['CANNOT_BAN_FOUNDER'] . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
							}

							if (!check_form_key($form_name))
							{
								trigger_error($user->lang['FORM_INVALID'] . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
							}

							$ban = array();

							switch ($action)
							{
								case 'banuser':
									$ban[] = $user_row['username'];
									$reason = 'USER_ADMIN_BAN_NAME_REASON';
									$log = 'LOG_USER_BAN_USER';
								break;

								case 'banemail':
									$ban[] = $user_row['user_email'];
									$reason = 'USER_ADMIN_BAN_EMAIL_REASON';
									$log = 'LOG_USER_BAN_EMAIL';
								break;

								case 'banip':
									$ban[] = $user_row['user_ip'];

									$sql = 'SELECT DISTINCT poster_ip
										FROM ' . POSTS_TABLE . "
										WHERE poster_id = $user_id";
									$result = $db->sql_query($sql);

									while ($row = $db->sql_fetchrow($result))
									{
										$ban[] = $row['poster_ip'];
									}
									$db->sql_freeresult($result);

									$reason = 'USER_ADMIN_BAN_IP_REASON';
									$log = 'LOG_USER_BAN_IP';
								break;
							}

							$ban_reason = utf8_normalize_nfc(request_var('ban_reason', $user->lang[$reason], true));
							$ban_give_reason = utf8_normalize_nfc(request_var('ban_give_reason', '', true));

							// Log not used at the moment, we simply utilize the ban function.
							$result = user_ban(substr($action, 3), $ban, 0, 0, 0, $ban_reason, $ban_give_reason);

							trigger_error((($result === false) ? $user->lang['BAN_ALREADY_ENTERED'] : $user->lang['BAN_SUCCESSFUL']) . adm_back_link($this->u_action . '&amp;u=' . $user_id));

						break;

						case 'reactivate':

							if ($user_id == $user->data['user_id'])
							{
								trigger_error($user->lang['CANNOT_FORCE_REACT_YOURSELF'] . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
							}

							if (!check_form_key($form_name))
							{
								trigger_error($user->lang['FORM_INVALID'] . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
							}

							if ($user_row['user_type'] == USER_FOUNDER)
							{
								trigger_error($user->lang['CANNOT_FORCE_REACT_FOUNDER'] . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
							}

							if ($user_row['user_type'] == USER_IGNORE)
							{
								trigger_error($user->lang['CANNOT_FORCE_REACT_BOT'] . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
							}

							if ($config['email_enable'])
							{
								include_once($phpbb_root_path . 'includes/functions_messenger.' . $phpEx);

								$server_url = generate_board_url();

								$user_actkey = gen_rand_string(mt_rand(6, 10));
								$email_template = ($user_row['user_type'] == USER_NORMAL) ? 'user_reactivate_account' : 'user_resend_inactive';

								if ($user_row['user_type'] == USER_NORMAL)
								{
									user_active_flip('deactivate', $user_id, INACTIVE_REMIND);

									$sql = 'UPDATE ' . USERS_TABLE . "
										SET user_actkey = '" . $db->sql_escape($user_actkey) . "'
										WHERE user_id = $user_id";
									$db->sql_query($sql);
								}
								else
								{
									// Grabbing the last confirm key - we only send a reminder
									$sql = 'SELECT user_actkey
										FROM ' . USERS_TABLE . '
										WHERE user_id = ' . $user_id;
									$result = $db->sql_query($sql);
									$user_actkey = (string) $db->sql_fetchfield('user_actkey');
									$db->sql_freeresult($result);
								}

								$messenger = new messenger(false);

								$messenger->template($email_template, $user_row['user_lang']);

								$messenger->set_addresses($user_row);

								$messenger->anti_abuse_headers($config, $user);

								$messenger->assign_vars(array(
									'WELCOME_MSG'	=> htmlspecialchars_decode(sprintf($user->lang['WELCOME_SUBJECT'], $config['sitename'])),
									'USERNAME'		=> htmlspecialchars_decode($user_row['username']),
									'U_ACTIVATE'	=> "$server_url/ucp.$phpEx?mode=activate&u={$user_row['user_id']}&k=$user_actkey")
								);

								$messenger->send(NOTIFY_EMAIL);

								add_log('admin', 'LOG_USER_REACTIVATE', $user_row['username']);
								add_log('user', $user_id, 'LOG_USER_REACTIVATE_USER');

								trigger_error($user->lang['FORCE_REACTIVATION_SUCCESS'] . adm_back_link($this->u_action . '&amp;u=' . $user_id));
							}

						break;

						case 'active':

							if ($user_id == $user->data['user_id'])
							{
								// It is only deactivation since the user is already activated (else he would not have reached this page)
								trigger_error($user->lang['CANNOT_DEACTIVATE_YOURSELF'] . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
							}

							if (!check_form_key($form_name))
							{
								trigger_error($user->lang['FORM_INVALID'] . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
							}

							if ($user_row['user_type'] == USER_FOUNDER)
							{
								trigger_error($user->lang['CANNOT_DEACTIVATE_FOUNDER'] . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
							}

							if ($user_row['user_type'] == USER_IGNORE)
							{
								trigger_error($user->lang['CANNOT_DEACTIVATE_BOT'] . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
							}

							user_active_flip('flip', $user_id);

							if ($user_row['user_type'] == USER_INACTIVE)
							{
								if ($config['require_activation'] == USER_ACTIVATION_ADMIN)
								{
									$phpbb_notifications = $phpbb_container->get('notification_manager');
									$phpbb_notifications->delete_notifications('notification.type.admin_activate_user', $user_row['user_id']);

									include_once($phpbb_root_path . 'includes/functions_messenger.' . $phpEx);

									$messenger = new messenger(false);

									$messenger->template('admin_welcome_activated', $user_row['user_lang']);

									$messenger->set_addresses($user_row);

									$messenger->anti_abuse_headers($config, $user);

									$messenger->assign_vars(array(
										'USERNAME'	=> htmlspecialchars_decode($user_row['username']))
									);

									$messenger->send(NOTIFY_EMAIL);
								}
							}

							$message = ($user_row['user_type'] == USER_INACTIVE) ? 'USER_ADMIN_ACTIVATED' : 'USER_ADMIN_DEACTIVED';
							$log = ($user_row['user_type'] == USER_INACTIVE) ? 'LOG_USER_ACTIVE' : 'LOG_USER_INACTIVE';

							add_log('admin', $log, $user_row['username']);
							add_log('user', $user_id, $log . '_USER');

							trigger_error($user->lang[$message] . adm_back_link($this->u_action . '&amp;u=' . $user_id));

						break;

						case 'delsig':

							if (!check_form_key($form_name))
							{
								trigger_error($user->lang['FORM_INVALID'] . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
							}

							$sql_ary = array(
								'user_sig'					=> '',
								'user_sig_bbcode_uid'		=> '',
								'user_sig_bbcode_bitfield'	=> ''
							);

							$sql = 'UPDATE ' . USERS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
								WHERE user_id = $user_id";
							$db->sql_query($sql);

							add_log('admin', 'LOG_USER_DEL_SIG', $user_row['username']);
							add_log('user', $user_id, 'LOG_USER_DEL_SIG_USER');

							trigger_error($user->lang['USER_ADMIN_SIG_REMOVED'] . adm_back_link($this->u_action . '&amp;u=' . $user_id));

						break;

						case 'delavatar':

							if (!check_form_key($form_name))
							{
								trigger_error($user->lang['FORM_INVALID'] . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
							}

							// Delete old avatar if present
							$phpbb_avatar_manager = $phpbb_container->get('avatar.manager');
							$phpbb_avatar_manager->handle_avatar_delete($db, $user, $phpbb_avatar_manager->clean_row($user_row, 'user'), USERS_TABLE, 'user_');

							add_log('admin', 'LOG_USER_DEL_AVATAR', $user_row['username']);
							add_log('user', $user_id, 'LOG_USER_DEL_AVATAR_USER');

							trigger_error($user->lang['USER_ADMIN_AVATAR_REMOVED'] . adm_back_link($this->u_action . '&amp;u=' . $user_id));
						break;

						case 'delposts':

							if (confirm_box(true))
							{
								// Delete posts, attachments, etc.
								delete_posts('poster_id', $user_id);

								add_log('admin', 'LOG_USER_DEL_POSTS', $user_row['username']);
								trigger_error($user->lang['USER_POSTS_DELETED'] . adm_back_link($this->u_action . '&amp;u=' . $user_id));
							}
							else
							{
								confirm_box(false, $user->lang['CONFIRM_OPERATION'], build_hidden_fields(array(
									'u'				=> $user_id,
									'i'				=> $id,
									'mode'			=> $mode,
									'action'		=> $action,
									'update'		=> true))
								);
							}

						break;

						case 'delattach':

							if (confirm_box(true))
							{
								delete_attachments('user', $user_id);

								add_log('admin', 'LOG_USER_DEL_ATTACH', $user_row['username']);
								trigger_error($user->lang['USER_ATTACHMENTS_REMOVED'] . adm_back_link($this->u_action . '&amp;u=' . $user_id));
							}
							else
							{
								confirm_box(false, $user->lang['CONFIRM_OPERATION'], build_hidden_fields(array(
									'u'				=> $user_id,
									'i'				=> $id,
									'mode'			=> $mode,
									'action'		=> $action,
									'update'		=> true))
								);
							}

						break;

						case 'deloutbox':

							if (confirm_box(true))
							{
								$msg_ids = array();
								$lang = 'EMPTY';

								$sql = 'SELECT msg_id
									FROM ' . PRIVMSGS_TO_TABLE . "
									WHERE author_id = $user_id
										AND folder_id = " . PRIVMSGS_OUTBOX;
								$result = $db->sql_query($sql);

								if ($row = $db->sql_fetchrow($result))
								{
									if (!function_exists('delete_pm'))
									{
										include($phpbb_root_path . 'includes/functions_privmsgs.' . $phpEx);
									}

									do
									{
										$msg_ids[] = (int) $row['msg_id'];
									}
									while ($row = $db->sql_fetchrow($result));

									$db->sql_freeresult($result);

									delete_pm($user_id, $msg_ids, PRIVMSGS_OUTBOX);

									add_log('admin', 'LOG_USER_DEL_OUTBOX', $user_row['username']);

									$lang = 'EMPTIED';
								}
								$db->sql_freeresult($result);

								trigger_error($user->lang['USER_OUTBOX_' . $lang] . adm_back_link($this->u_action . '&amp;u=' . $user_id));
							}
							else
							{
								confirm_box(false, $user->lang['CONFIRM_OPERATION'], build_hidden_fields(array(
									'u'				=> $user_id,
									'i'				=> $id,
									'mode'			=> $mode,
									'action'		=> $action,
									'update'		=> true))
								);
							}
						break;

						case 'moveposts':

							if (!check_form_key($form_name))
							{
								trigger_error($user->lang['FORM_INVALID'] . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
							}

							$user->add_lang('acp/forums');

							$new_forum_id = request_var('new_f', 0);

							if (!$new_forum_id)
							{
								$this->page_title = 'USER_ADMIN_MOVE_POSTS';

								$template->assign_vars(array(
									'S_SELECT_FORUM'		=> true,
									'U_ACTION'				=> $this->u_action . "&amp;action=$action&amp;u=$user_id",
									'U_BACK'				=> $this->u_action . "&amp;u=$user_id",
									'S_FORUM_OPTIONS'		=> make_forum_select(false, false, false, true))
								);

								return;
							}

							// Is the new forum postable to?
							$sql = 'SELECT forum_name, forum_type
								FROM ' . FORUMS_TABLE . "
								WHERE forum_id = $new_forum_id";
							$result = $db->sql_query($sql);
							$forum_info = $db->sql_fetchrow($result);
							$db->sql_freeresult($result);

							if (!$forum_info)
							{
								trigger_error($user->lang['NO_FORUM'] . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
							}

							if ($forum_info['forum_type'] != FORUM_POST)
							{
								trigger_error($user->lang['MOVE_POSTS_NO_POSTABLE_FORUM'] . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
							}

							// Two stage?
							// Move topics comprising only posts from this user
							$topic_id_ary = $move_topic_ary = $move_post_ary = $new_topic_id_ary = array();
							$forum_id_ary = array($new_forum_id);

							$sql = 'SELECT topic_id, post_visibility, COUNT(post_id) AS total_posts
								FROM ' . POSTS_TABLE . "
								WHERE poster_id = $user_id
									AND forum_id <> $new_forum_id
								GROUP BY topic_id, post_visibility";
							$result = $db->sql_query($sql);

							while ($row = $db->sql_fetchrow($result))
							{
								$topic_id_ary[$row['topic_id']][$row['post_visibility']] = $row['total_posts'];
							}
							$db->sql_freeresult($result);

							if (sizeof($topic_id_ary))
							{
								$sql = 'SELECT topic_id, forum_id, topic_title, topic_posts_approved, topic_posts_unapproved, topic_posts_softdeleted, topic_attachment
									FROM ' . TOPICS_TABLE . '
									WHERE ' . $db->sql_in_set('topic_id', array_keys($topic_id_ary));
								$result = $db->sql_query($sql);

								while ($row = $db->sql_fetchrow($result))
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
								$db->sql_freeresult($result);
							}

							// Entire topic comprises posts by this user, move these topics
							if (sizeof($move_topic_ary))
							{
								move_topics($move_topic_ary, $new_forum_id, false);
							}

							if (sizeof($move_post_ary))
							{
								// Create new topic
								// Update post_ids, report_ids, attachment_ids
								foreach ($move_post_ary as $topic_id => $post_ary)
								{
									// Create new topic
									$sql = 'INSERT INTO ' . TOPICS_TABLE . ' ' . $db->sql_build_array('INSERT', array(
										'topic_poster'				=> $user_id,
										'topic_time'				=> time(),
										'forum_id' 					=> $new_forum_id,
										'icon_id'					=> 0,
										'topic_visibility'			=> ITEM_APPROVED,
										'topic_title' 				=> $post_ary['title'],
										'topic_first_poster_name'	=> $user_row['username'],
										'topic_type'				=> POST_NORMAL,
										'topic_time_limit'			=> 0,
										'topic_attachment'			=> $post_ary['attach'])
									);
									$db->sql_query($sql);

									$new_topic_id = $db->sql_nextid();

									// Move posts
									$sql = 'UPDATE ' . POSTS_TABLE . "
										SET forum_id = $new_forum_id, topic_id = $new_topic_id
										WHERE topic_id = $topic_id
											AND poster_id = $user_id";
									$db->sql_query($sql);

									if ($post_ary['attach'])
									{
										$sql = 'UPDATE ' . ATTACHMENTS_TABLE . "
											SET topic_id = $new_topic_id
											WHERE topic_id = $topic_id
												AND poster_id = $user_id";
										$db->sql_query($sql);
									}

									$new_topic_id_ary[] = $new_topic_id;
								}
							}

							$forum_id_ary = array_unique($forum_id_ary);
							$topic_id_ary = array_unique(array_merge(array_keys($topic_id_ary), $new_topic_id_ary));

							if (sizeof($topic_id_ary))
							{
								sync('topic_reported', 'topic_id', $topic_id_ary);
								sync('topic', 'topic_id', $topic_id_ary);
							}

							if (sizeof($forum_id_ary))
							{
								sync('forum', 'forum_id', $forum_id_ary, false, true);
							}

							add_log('admin', 'LOG_USER_MOVE_POSTS', $user_row['username'], $forum_info['forum_name']);
							add_log('user', $user_id, 'LOG_USER_MOVE_POSTS_USER', $forum_info['forum_name']);

							trigger_error($user->lang['USER_POSTS_MOVED'] . adm_back_link($this->u_action . '&amp;u=' . $user_id));

						break;

						case 'leave_nr':

							if (confirm_box(true))
							{
								remove_newly_registered($user_id, $user_row);

								add_log('admin', 'LOG_USER_REMOVED_NR', $user_row['username']);
								trigger_error($user->lang['USER_LIFTED_NR'] . adm_back_link($this->u_action . '&amp;u=' . $user_id));
							}
							else
							{
								confirm_box(false, $user->lang['CONFIRM_OPERATION'], build_hidden_fields(array(
									'u'				=> $user_id,
									'i'				=> $id,
									'mode'			=> $mode,
									'action'		=> $action,
									'update'		=> true))
								);
							}

						break;

						default:
							/**
							* Run custom quicktool code
							*
							* @event core.acp_users_overview_run_quicktool
							* @var	array	user_row	Current user data
							* @var	string	action		Quick tool that should be run
							* @since 3.1.0-a1
							*/
							$vars = array('action', 'user_row');
							extract($phpbb_dispatcher->trigger_event('core.acp_users_overview_run_quicktool', compact($vars)));
						break;
					}

					// Handle registration info updates
					$data = array(
						'username'			=> utf8_normalize_nfc(request_var('user', $user_row['username'], true)),
						'user_founder'		=> request_var('user_founder', ($user_row['user_type'] == USER_FOUNDER) ? 1 : 0),
						'email'				=> strtolower(request_var('user_email', $user_row['user_email'])),
						'new_password'		=> $request->variable('new_password', '', true),
						'password_confirm'	=> $request->variable('password_confirm', '', true),
					);

					// Validation data - we do not check the password complexity setting here
					$check_ary = array(
						'new_password'		=> array(
							array('string', true, $config['min_pass_chars'], $config['max_pass_chars']),
							array('password')),
						'password_confirm'	=> array('string', true, $config['min_pass_chars'], $config['max_pass_chars']),
					);

					// Check username if altered
					if ($data['username'] != $user_row['username'])
					{
						$check_ary += array(
							'username'			=> array(
								array('string', false, $config['min_name_chars'], $config['max_name_chars']),
								array('username', $user_row['username'])
							),
						);
					}

					// Check email if altered
					if ($data['email'] != $user_row['user_email'])
					{
						$check_ary += array(
							'email'				=> array(
								array('string', false, 6, 60),
								array('user_email', $user_row['user_email']),
							),
						);
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
					$passwords_manager = $phpbb_container->get('passwords.manager');

					// Which updates do we need to do?
					$update_username = ($user_row['username'] != $data['username']) ? $data['username'] : false;
					$update_password = $data['new_password'] && !$passwords_manager->check($data['new_password'], $user_row['user_password']);
					$update_email = ($data['email'] != $user_row['user_email']) ? $data['email'] : false;

					if (!sizeof($error))
					{
						$sql_ary = array();

						if ($user_row['user_type'] != USER_FOUNDER || $user->data['user_type'] == USER_FOUNDER)
						{
							// Only allow founders updating the founder status...
							if ($user->data['user_type'] == USER_FOUNDER)
							{
								// Setting a normal member to be a founder
								if ($data['user_founder'] && $user_row['user_type'] != USER_FOUNDER)
								{
									// Make sure the user is not setting an Inactive or ignored user to be a founder
									if ($user_row['user_type'] == USER_IGNORE)
									{
										trigger_error($user->lang['CANNOT_SET_FOUNDER_IGNORED'] . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
									}

									if ($user_row['user_type'] == USER_INACTIVE)
									{
										trigger_error($user->lang['CANNOT_SET_FOUNDER_INACTIVE'] . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
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
									$result = $db->sql_query_limit($sql, 1);
									$row = $db->sql_fetchrow($result);
									$db->sql_freeresult($result);

									if ($row)
									{
										$sql_ary['user_type'] = USER_NORMAL;
									}
									else
									{
										trigger_error($user->lang['AT_LEAST_ONE_FOUNDER'] . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
									}
								}
							}
						}

						/**
						* Modify user data before we update it
						*
						* @event core.acp_users_overview_modify_data
						* @var	array	user_row	Current user data
						* @var	array	data		Submitted user data
						* @var	array	sql_ary		User data we udpate
						* @since 3.1.0-a1
						*/
						$vars = array('user_row', 'data', 'sql_ary');
						extract($phpbb_dispatcher->trigger_event('core.acp_users_overview_modify_data', compact($vars)));

						if ($update_username !== false)
						{
							$sql_ary['username'] = $update_username;
							$sql_ary['username_clean'] = utf8_clean_string($update_username);

							add_log('user', $user_id, 'LOG_USER_UPDATE_NAME', $user_row['username'], $update_username);
						}

						if ($update_email !== false)
						{
							$sql_ary += array(
								'user_email'		=> $update_email,
								'user_email_hash'	=> phpbb_email_hash($update_email),
							);

							add_log('user', $user_id, 'LOG_USER_UPDATE_EMAIL', $user_row['username'], $user_row['user_email'], $update_email);
						}

						if ($update_password)
						{
							$sql_ary += array(
								'user_password'		=> $passwords_manager->hash($data['new_password']),
								'user_passchg'		=> time(),
							);

							$user->reset_login_keys($user_id);
							add_log('user', $user_id, 'LOG_USER_NEW_PASSWORD', $user_row['username']);
						}

						if (sizeof($sql_ary))
						{
							$sql = 'UPDATE ' . USERS_TABLE . '
								SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
								WHERE user_id = ' . $user_id;
							$db->sql_query($sql);
						}

						if ($update_username)
						{
							user_update_name($user_row['username'], $update_username);
						}

						// Let the users permissions being updated
						$auth->acl_clear_prefetch($user_id);

						add_log('admin', 'LOG_USER_USER_UPDATE', $data['username']);

						trigger_error($user->lang['USER_OVERVIEW_UPDATED'] . adm_back_link($this->u_action . '&amp;u=' . $user_id));
					}

					// Replace "error" strings with their real, localised form
					$error = array_map(array($user, 'lang'), $error);
				}

				if ($user_id == $user->data['user_id'])
				{
					$quick_tool_ary = array('delsig' => 'DEL_SIG', 'delavatar' => 'DEL_AVATAR', 'moveposts' => 'MOVE_POSTS', 'delposts' => 'DEL_POSTS', 'delattach' => 'DEL_ATTACH', 'deloutbox' => 'DEL_OUTBOX');
					if ($user_row['user_new'])
					{
						$quick_tool_ary['leave_nr'] = 'LEAVE_NR';
					}
				}
				else
				{
					$quick_tool_ary = array();

					if ($user_row['user_type'] != USER_FOUNDER)
					{
						$quick_tool_ary += array('banuser' => 'BAN_USER', 'banemail' => 'BAN_EMAIL', 'banip' => 'BAN_IP');
					}

					if ($user_row['user_type'] != USER_FOUNDER && $user_row['user_type'] != USER_IGNORE)
					{
						$quick_tool_ary += array('active' => (($user_row['user_type'] == USER_INACTIVE) ? 'ACTIVATE' : 'DEACTIVATE'));
					}

					$quick_tool_ary += array('delsig' => 'DEL_SIG', 'delavatar' => 'DEL_AVATAR', 'moveposts' => 'MOVE_POSTS', 'delposts' => 'DEL_POSTS', 'delattach' => 'DEL_ATTACH', 'deloutbox' => 'DEL_OUTBOX');

					if ($config['email_enable'] && ($user_row['user_type'] == USER_NORMAL || $user_row['user_type'] == USER_INACTIVE))
					{
						$quick_tool_ary['reactivate'] = 'FORCE';
					}

					if ($user_row['user_new'])
					{
						$quick_tool_ary['leave_nr'] = 'LEAVE_NR';
					}
				}

				if ($config['load_onlinetrack'])
				{
					$sql = 'SELECT MAX(session_time) AS session_time, MIN(session_viewonline) AS session_viewonline
						FROM ' . SESSIONS_TABLE . "
						WHERE session_user_id = $user_id";
					$result = $db->sql_query($sql);
					$row = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);

					$user_row['session_time'] = (isset($row['session_time'])) ? $row['session_time'] : 0;
					$user_row['session_viewonline'] = (isset($row['session_viewonline'])) ? $row['session_viewonline'] : 0;
					unset($row);
				}

				/**
				* Add additional quick tool options and overwrite user data
				*
				* @event core.acp_users_display_overview
				* @var	array	user_row			Array with user data
				* @var	array	quick_tool_ary		Ouick tool options
				* @since 3.1.0-a1
				*/
				$vars = array('user_row', 'quick_tool_ary');
				extract($phpbb_dispatcher->trigger_event('core.acp_users_display_overview', compact($vars)));

				$s_action_options = '<option class="sep" value="">' . $user->lang['SELECT_OPTION'] . '</option>';
				foreach ($quick_tool_ary as $value => $lang)
				{
					$s_action_options .= '<option value="' . $value . '">' . $user->lang['USER_ADMIN_' . $lang] . '</option>';
				}

				$last_active = (!empty($user_row['session_time'])) ? $user_row['session_time'] : $user_row['user_lastvisit'];

				$inactive_reason = '';
				if ($user_row['user_type'] == USER_INACTIVE)
				{
					$inactive_reason = $user->lang['INACTIVE_REASON_UNKNOWN'];

					switch ($user_row['user_inactive_reason'])
					{
						case INACTIVE_REGISTER:
							$inactive_reason = $user->lang['INACTIVE_REASON_REGISTER'];
						break;

						case INACTIVE_PROFILE:
							$inactive_reason = $user->lang['INACTIVE_REASON_PROFILE'];
						break;

						case INACTIVE_MANUAL:
							$inactive_reason = $user->lang['INACTIVE_REASON_MANUAL'];
						break;

						case INACTIVE_REMIND:
							$inactive_reason = $user->lang['INACTIVE_REASON_REMIND'];
						break;
					}
				}

				// Posts in Queue
				$sql = 'SELECT COUNT(post_id) as posts_in_queue
					FROM ' . POSTS_TABLE . '
					WHERE poster_id = ' . $user_id . '
						AND ' . $db->sql_in_set('post_visibility', array(ITEM_UNAPPROVED, ITEM_REAPPROVE));
				$result = $db->sql_query($sql);
				$user_row['posts_in_queue'] = (int) $db->sql_fetchfield('posts_in_queue');
				$db->sql_freeresult($result);

				$sql = 'SELECT post_id
					FROM ' . POSTS_TABLE . '
					WHERE poster_id = '. $user_id;
				$result = $db->sql_query_limit($sql, 1);
				$user_row['user_has_posts'] = (bool) $db->sql_fetchfield('post_id');
				$db->sql_freeresult($result);

				$template->assign_vars(array(
					'L_NAME_CHARS_EXPLAIN'		=> $user->lang($config['allow_name_chars'] . '_EXPLAIN', $user->lang('CHARACTERS', (int) $config['min_name_chars']), $user->lang('CHARACTERS', (int) $config['max_name_chars'])),
					'L_CHANGE_PASSWORD_EXPLAIN'	=> $user->lang($config['pass_complex'] . '_EXPLAIN', $user->lang('CHARACTERS', (int) $config['min_pass_chars']), $user->lang('CHARACTERS', (int) $config['max_pass_chars'])),
					'L_POSTS_IN_QUEUE'			=> $user->lang('NUM_POSTS_IN_QUEUE', $user_row['posts_in_queue']),
					'S_FOUNDER'					=> ($user->data['user_type'] == USER_FOUNDER) ? true : false,

					'S_OVERVIEW'		=> true,
					'S_USER_IP'			=> ($user_row['user_ip']) ? true : false,
					'S_USER_FOUNDER'	=> ($user_row['user_type'] == USER_FOUNDER) ? true : false,
					'S_ACTION_OPTIONS'	=> $s_action_options,
					'S_OWN_ACCOUNT'		=> ($user_id == $user->data['user_id']) ? true : false,
					'S_USER_INACTIVE'	=> ($user_row['user_type'] == USER_INACTIVE) ? true : false,

					'U_SHOW_IP'		=> $this->u_action . "&amp;u=$user_id&amp;ip=" . (($ip == 'ip') ? 'hostname' : 'ip'),
					'U_WHOIS'		=> $this->u_action . "&amp;action=whois&amp;user_ip={$user_row['user_ip']}",
					'U_MCP_QUEUE'	=> ($auth->acl_getf_global('m_approve')) ? append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=queue', true, $user->session_id) : '',
					'U_SEARCH_USER'	=> ($config['load_search'] && $auth->acl_get('u_search')) ? append_sid("{$phpbb_root_path}search.$phpEx", "author_id={$user_row['user_id']}&amp;sr=posts") : '',

					'U_SWITCH_PERMISSIONS'	=> ($auth->acl_get('a_switchperm') && $user->data['user_id'] != $user_row['user_id']) ? append_sid("{$phpbb_root_path}ucp.$phpEx", "mode=switch_perm&amp;u={$user_row['user_id']}&amp;hash=" . generate_link_hash('switchperm')) : '',

					'POSTS_IN_QUEUE'	=> $user_row['posts_in_queue'],
					'USER'				=> $user_row['username'],
					'USER_REGISTERED'	=> $user->format_date($user_row['user_regdate']),
					'REGISTERED_IP'		=> ($ip == 'hostname') ? gethostbyaddr($user_row['user_ip']) : $user_row['user_ip'],
					'USER_LASTACTIVE'	=> ($last_active) ? $user->format_date($last_active) : ' - ',
					'USER_EMAIL'		=> $user_row['user_email'],
					'USER_WARNINGS'		=> $user_row['user_warnings'],
					'USER_POSTS'		=> $user_row['user_posts'],
					'USER_HAS_POSTS'	=> $user_row['user_has_posts'],
					'USER_INACTIVE_REASON'	=> $inactive_reason,
				));

			break;

			case 'feedback':

				$user->add_lang('mcp');

				// Set up general vars
				$start		= request_var('start', 0);
				$deletemark = (isset($_POST['delmarked'])) ? true : false;
				$deleteall	= (isset($_POST['delall'])) ? true : false;
				$marked		= request_var('mark', array(0));
				$message	= utf8_normalize_nfc(request_var('message', '', true));
				$pagination = $phpbb_container->get('pagination');

				// Sort keys
				$sort_days	= request_var('st', 0);
				$sort_key	= request_var('sk', 't');
				$sort_dir	= request_var('sd', 'd');

				// Delete entries if requested and able
				if (($deletemark || $deleteall) && $auth->acl_get('a_clearlogs'))
				{
					if (!check_form_key($form_name))
					{
						trigger_error($user->lang['FORM_INVALID'] . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
					}

					$where_sql = '';
					if ($deletemark && $marked)
					{
						$sql_in = array();
						foreach ($marked as $mark)
						{
							$sql_in[] = $mark;
						}
						$where_sql = ' AND ' . $db->sql_in_set('log_id', $sql_in);
						unset($sql_in);
					}

					if ($where_sql || $deleteall)
					{
						$sql = 'DELETE FROM ' . LOG_TABLE . '
							WHERE log_type = ' . LOG_USERS . "
							AND reportee_id = $user_id
							$where_sql";
						$db->sql_query($sql);

						add_log('admin', 'LOG_CLEAR_USER', $user_row['username']);
					}
				}

				if ($submit && $message)
				{
					if (!check_form_key($form_name))
					{
						trigger_error($user->lang['FORM_INVALID'] . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
					}

					add_log('admin', 'LOG_USER_FEEDBACK', $user_row['username']);
					add_log('mod', 0, 0, 'LOG_USER_FEEDBACK', $user_row['username']);
					add_log('user', $user_id, 'LOG_USER_GENERAL', $message);

					trigger_error($user->lang['USER_FEEDBACK_ADDED'] . adm_back_link($this->u_action . '&amp;u=' . $user_id));
				}

				// Sorting
				$limit_days = array(0 => $user->lang['ALL_ENTRIES'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 365 => $user->lang['1_YEAR']);
				$sort_by_text = array('u' => $user->lang['SORT_USERNAME'], 't' => $user->lang['SORT_DATE'], 'i' => $user->lang['SORT_IP'], 'o' => $user->lang['SORT_ACTION']);
				$sort_by_sql = array('u' => 'u.username_clean', 't' => 'l.log_time', 'i' => 'l.log_ip', 'o' => 'l.log_operation');

				$s_limit_days = $s_sort_key = $s_sort_dir = $u_sort_param = '';
				gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);

				// Define where and sort sql for use in displaying logs
				$sql_where = ($sort_days) ? (time() - ($sort_days * 86400)) : 0;
				$sql_sort = $sort_by_sql[$sort_key] . ' ' . (($sort_dir == 'd') ? 'DESC' : 'ASC');

				// Grab log data
				$log_data = array();
				$log_count = 0;
				$start = view_log('user', $log_data, $log_count, $config['topics_per_page'], $start, 0, 0, $user_id, $sql_where, $sql_sort);

				$base_url = $this->u_action . "&amp;u=$user_id&amp;$u_sort_param";
				$pagination->generate_template_pagination($base_url, 'pagination', 'start', $log_count, $config['topics_per_page'], $start);

				$template->assign_vars(array(
					'S_FEEDBACK'	=> true,

					'S_LIMIT_DAYS'	=> $s_limit_days,
					'S_SORT_KEY'	=> $s_sort_key,
					'S_SORT_DIR'	=> $s_sort_dir,
					'S_CLEARLOGS'	=> $auth->acl_get('a_clearlogs'))
				);

				foreach ($log_data as $row)
				{
					$template->assign_block_vars('log', array(
						'USERNAME'		=> $row['username_full'],
						'IP'			=> $row['ip'],
						'DATE'			=> $user->format_date($row['time']),
						'ACTION'		=> nl2br($row['action']),
						'ID'			=> $row['id'])
					);
				}

			break;

			case 'warnings':
				$user->add_lang('mcp');

				// Set up general vars
				$start		= request_var('start', 0);
				$deletemark	= (isset($_POST['delmarked'])) ? true : false;
				$deleteall	= (isset($_POST['delall'])) ? true : false;
				$confirm	= (isset($_POST['confirm'])) ? true : false;
				$marked		= request_var('mark', array(0));
				$message	= utf8_normalize_nfc(request_var('message', '', true));

				// Sort keys
				$sort_days	= request_var('st', 0);
				$sort_key	= request_var('sk', 't');
				$sort_dir	= request_var('sd', 'd');

				// Delete entries if requested and able
				if ($deletemark || $deleteall || $confirm)
				{
					if (confirm_box(true))
					{
						$where_sql = '';
						$deletemark = request_var('delmarked', 0);
						$deleteall = request_var('delall', 0);
						if ($deletemark && $marked)
						{
							$where_sql = ' AND ' . $db->sql_in_set('warning_id', array_values($marked));
						}

						if ($where_sql || $deleteall)
						{
							$sql = 'DELETE FROM ' . WARNINGS_TABLE . "
								WHERE user_id = $user_id
									$where_sql";
							$db->sql_query($sql);

							if ($deleteall)
							{
								$log_warnings = $deleted_warnings = 0;
							}
							else
							{
								$num_warnings = (int) $db->sql_affectedrows();
								$deleted_warnings = ' user_warnings - ' . $num_warnings;
								$log_warnings = ($num_warnings > 2) ? 2 : $num_warnings;
							}

							$sql = 'UPDATE ' . USERS_TABLE . "
								SET user_warnings = $deleted_warnings
								WHERE user_id = $user_id";
							$db->sql_query($sql);

							if ($log_warnings)
							{
								add_log('admin', 'LOG_WARNINGS_DELETED', $user_row['username'], $num_warnings);
							}
							else
							{
								add_log('admin', 'LOG_WARNINGS_DELETED_ALL', $user_row['username']);
							}
						}
					}
					else
					{
						$s_hidden_fields = array(
							'i'				=> $id,
							'mode'			=> $mode,
							'u'				=> $user_id,
							'mark'			=> $marked,
						);
						if (isset($_POST['delmarked']))
						{
							$s_hidden_fields['delmarked'] = 1;
						}
						if (isset($_POST['delall']))
						{
							$s_hidden_fields['delall'] = 1;
						}
						if (isset($_POST['delall']) || (isset($_POST['delmarked']) && sizeof($marked)))
						{
							confirm_box(false, $user->lang['CONFIRM_OPERATION'], build_hidden_fields($s_hidden_fields));
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
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					if (!$row['log_operation'])
					{
						// We do not have a log-entry anymore, so there is no data available
						$row['action'] = $user->lang['USER_WARNING_LOG_DELETED'];
					}
					else
					{
						$row['action'] = (isset($user->lang[$row['log_operation']])) ? $user->lang[$row['log_operation']] : '{' . ucfirst(str_replace('_', ' ', $row['log_operation'])) . '}';
						if (!empty($row['log_data']))
						{
							$log_data_ary = @unserialize($row['log_data']);
							$log_data_ary = ($log_data_ary === false) ? array() : $log_data_ary;

							if (isset($user->lang[$row['log_operation']]))
							{
								// Check if there are more occurrences of % than arguments, if there are we fill out the arguments array
								// It doesn't matter if we add more arguments than placeholders
								if ((substr_count($row['action'], '%') - sizeof($log_data_ary)) > 0)
								{
									$log_data_ary = array_merge($log_data_ary, array_fill(0, substr_count($row['action'], '%') - sizeof($log_data_ary), ''));
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

					$template->assign_block_vars('warn', array(
						'ID'		=> $row['warning_id'],
						'USERNAME'	=> ($row['log_operation']) ? get_username_string('full', $row['mod_user_id'], $row['mod_username'], $row['mod_user_colour']) : '-',
						'ACTION'	=> make_clickable($row['action']),
						'DATE'		=> $user->format_date($row['warning_time']),
					));
				}
				$db->sql_freeresult($result);

				$template->assign_vars(array(
					'S_WARNINGS'	=> true,
				));

			break;

			case 'profile':

				include($phpbb_root_path . 'includes/functions_user.' . $phpEx);

				$cp = $phpbb_container->get('profilefields.manager');

				$cp_data = $cp_error = array();

				$sql = 'SELECT lang_id
					FROM ' . LANG_TABLE . "
					WHERE lang_iso = '" . $db->sql_escape($user->data['user_lang']) . "'";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				$user_row['iso_lang_id'] = $row['lang_id'];

				$data = array(
					'jabber'		=> utf8_normalize_nfc(request_var('jabber', $user_row['user_jabber'], true)),
					'bday_day'		=> 0,
					'bday_month'	=> 0,
					'bday_year'		=> 0,
				);

				if ($user_row['user_birthday'])
				{
					list($data['bday_day'], $data['bday_month'], $data['bday_year']) = explode('-', $user_row['user_birthday']);
				}

				$data['bday_day']		= request_var('bday_day', $data['bday_day']);
				$data['bday_month']		= request_var('bday_month', $data['bday_month']);
				$data['bday_year']		= request_var('bday_year', $data['bday_year']);
				$data['user_birthday']	= sprintf('%2d-%2d-%4d', $data['bday_day'], $data['bday_month'], $data['bday_year']);

				if ($submit)
				{
					$error = validate_data($data, array(
						'jabber'		=> array(
							array('string', true, 5, 255),
							array('jabber')),
						'bday_day'		=> array('num', true, 1, 31),
						'bday_month'	=> array('num', true, 1, 12),
						'bday_year'		=> array('num', true, 1901, gmdate('Y', time())),
						'user_birthday'	=> array('date', true),
					));

					// validate custom profile fields
					$cp->submit_cp_field('profile', $user_row['iso_lang_id'], $cp_data, $cp_error);

					if (sizeof($cp_error))
					{
						$error = array_merge($error, $cp_error);
					}
					if (!check_form_key($form_name))
					{
						$error[] = 'FORM_INVALID';
					}

					if (!sizeof($error))
					{
						$sql_ary = array(
							'user_jabber'	=> $data['jabber'],
							'user_birthday'	=> $data['user_birthday'],
						);

						$sql = 'UPDATE ' . USERS_TABLE . '
							SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
							WHERE user_id = $user_id";
						$db->sql_query($sql);

						// Update Custom Fields
						$cp->update_profile_field_data($user_id, $cp_data);

						trigger_error($user->lang['USER_PROFILE_UPDATED'] . adm_back_link($this->u_action . '&amp;u=' . $user_id));
					}

					// Replace "error" strings with their real, localised form
					$error = array_map(array($user, 'lang'), $error);
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
				$s_birthday_year_options = '';

				$now = getdate();
				$s_birthday_year_options = '<option value="0"' . ((!$data['bday_year']) ? ' selected="selected"' : '') . '>--</option>';
				for ($i = $now['year'] - 100; $i <= $now['year']; $i++)
				{
					$selected = ($i == $data['bday_year']) ? ' selected="selected"' : '';
					$s_birthday_year_options .= "<option value=\"$i\"$selected>$i</option>";
				}
				unset($now);

				$template->assign_vars(array(
					'JABBER'		=> $data['jabber'],
					'S_BIRTHDAY_DAY_OPTIONS'	=> $s_birthday_day_options,
					'S_BIRTHDAY_MONTH_OPTIONS'	=> $s_birthday_month_options,
					'S_BIRTHDAY_YEAR_OPTIONS'	=> $s_birthday_year_options,

					'S_PROFILE'		=> true)
				);

				// Get additional profile fields and assign them to the template block var 'profile_fields'
				$user->get_profile_fields($user_id);

				$cp->generate_profile_fields('profile', $user_row['iso_lang_id']);

			break;

			case 'prefs':

				include($phpbb_root_path . 'includes/functions_user.' . $phpEx);

				$data = array(
					'dateformat'		=> utf8_normalize_nfc(request_var('dateformat', $user_row['user_dateformat'], true)),
					'lang'				=> basename(request_var('lang', $user_row['user_lang'])),
					'tz'				=> request_var('tz', $user_row['user_timezone']),
					'style'				=> request_var('style', $user_row['user_style']),
					'viewemail'			=> request_var('viewemail', $user_row['user_allow_viewemail']),
					'massemail'			=> request_var('massemail', $user_row['user_allow_massemail']),
					'hideonline'		=> request_var('hideonline', !$user_row['user_allow_viewonline']),
					'notifymethod'		=> request_var('notifymethod', $user_row['user_notify_type']),
					'notifypm'			=> request_var('notifypm', $user_row['user_notify_pm']),
					'allowpm'			=> request_var('allowpm', $user_row['user_allow_pm']),

					'topic_sk'			=> request_var('topic_sk', ($user_row['user_topic_sortby_type']) ? $user_row['user_topic_sortby_type'] : 't'),
					'topic_sd'			=> request_var('topic_sd', ($user_row['user_topic_sortby_dir']) ? $user_row['user_topic_sortby_dir'] : 'd'),
					'topic_st'			=> request_var('topic_st', ($user_row['user_topic_show_days']) ? $user_row['user_topic_show_days'] : 0),

					'post_sk'			=> request_var('post_sk', ($user_row['user_post_sortby_type']) ? $user_row['user_post_sortby_type'] : 't'),
					'post_sd'			=> request_var('post_sd', ($user_row['user_post_sortby_dir']) ? $user_row['user_post_sortby_dir'] : 'a'),
					'post_st'			=> request_var('post_st', ($user_row['user_post_show_days']) ? $user_row['user_post_show_days'] : 0),

					'view_images'		=> request_var('view_images', $this->optionget($user_row, 'viewimg')),
					'view_flash'		=> request_var('view_flash', $this->optionget($user_row, 'viewflash')),
					'view_smilies'		=> request_var('view_smilies', $this->optionget($user_row, 'viewsmilies')),
					'view_sigs'			=> request_var('view_sigs', $this->optionget($user_row, 'viewsigs')),
					'view_avatars'		=> request_var('view_avatars', $this->optionget($user_row, 'viewavatars')),
					'view_wordcensor'	=> request_var('view_wordcensor', $this->optionget($user_row, 'viewcensors')),

					'bbcode'	=> request_var('bbcode', $this->optionget($user_row, 'bbcode')),
					'smilies'	=> request_var('smilies', $this->optionget($user_row, 'smilies')),
					'sig'		=> request_var('sig', $this->optionget($user_row, 'attachsig')),
					'notify'	=> request_var('notify', $user_row['user_notify']),
				);

				/**
				* Modify users preferences data
				*
				* @event core.acp_users_prefs_modify_data
				* @var	array	data			Array with users preferences data
				* @var	array	user_row		Array with user data
				* @since 3.1.0-b3
				*/
				$vars = array('data', 'user_row');
				extract($phpbb_dispatcher->trigger_event('core.acp_users_prefs_modify_data', compact($vars)));

				if ($submit)
				{
					$error = validate_data($data, array(
						'dateformat'	=> array('string', false, 1, 30),
						'lang'			=> array('match', false, '#^[a-z_\-]{2,}$#i'),
						'tz'			=> array('timezone'),

						'topic_sk'		=> array('string', false, 1, 1),
						'topic_sd'		=> array('string', false, 1, 1),
						'post_sk'		=> array('string', false, 1, 1),
						'post_sd'		=> array('string', false, 1, 1),
					));

					if (!check_form_key($form_name))
					{
						$error[] = 'FORM_INVALID';
					}

					if (!sizeof($error))
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

						$sql_ary = array(
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
						);

						/**
						* Modify SQL query before users preferences are updated
						*
						* @event core.acp_users_prefs_modify_sql
						* @var	array	data			Array with users preferences data
						* @var	array	user_row		Array with user data
						* @var	array	sql_ary			SQL array with users preferences data to update
						* @var	array	error			Array with errors data
						* @since 3.1.0-b3
						*/
						$vars = array('data', 'user_row', 'sql_ary', 'error');
						extract($phpbb_dispatcher->trigger_event('core.acp_users_prefs_modify_sql', compact($vars)));

						if (!sizeof($error))
						{
							$sql = 'UPDATE ' . USERS_TABLE . '
								SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
								WHERE user_id = $user_id";
							$db->sql_query($sql);

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

									$session_sql_ary = array(
										'session_viewonline'	=> ($user_auth->acl_get('u_hideonline')) ? $sql_ary['user_allow_viewonline'] : true,
									);

									$sql = 'UPDATE ' . SESSIONS_TABLE . '
										SET ' . $db->sql_build_array('UPDATE', $session_sql_ary) . "
										WHERE session_user_id = $user_id";
									$db->sql_query($sql);

									unset($user_auth);
								}
							}

							trigger_error($user->lang['USER_PREFS_UPDATED'] . adm_back_link($this->u_action . '&amp;u=' . $user_id));
						}
					}

					// Replace "error" strings with their real, localised form
					$error = array_map(array($user, 'lang'), $error);
				}

				$dateformat_options = '';
				foreach ($user->lang['dateformats'] as $format => $null)
				{
					$dateformat_options .= '<option value="' . $format . '"' . (($format == $data['dateformat']) ? ' selected="selected"' : '') . '>';
					$dateformat_options .= $user->format_date(time(), $format, false) . ((strpos($format, '|') !== false) ? $user->lang['VARIANT_DATE_SEPARATOR'] . $user->format_date(time(), $format, true) : '');
					$dateformat_options .= '</option>';
				}

				$s_custom = false;

				$dateformat_options .= '<option value="custom"';
				if (!isset($user->lang['dateformats'][$data['dateformat']]))
				{
					$dateformat_options .= ' selected="selected"';
					$s_custom = true;
				}
				$dateformat_options .= '>' . $user->lang['CUSTOM_DATEFORMAT'] . '</option>';

				$sort_dir_text = array('a' => $user->lang['ASCENDING'], 'd' => $user->lang['DESCENDING']);

				// Topic ordering options
				$limit_topic_days = array(0 => $user->lang['ALL_TOPICS'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 365 => $user->lang['1_YEAR']);
				$sort_by_topic_text = array('a' => $user->lang['AUTHOR'], 't' => $user->lang['POST_TIME'], 'r' => $user->lang['REPLIES'], 's' => $user->lang['SUBJECT'], 'v' => $user->lang['VIEWS']);

				// Post ordering options
				$limit_post_days = array(0 => $user->lang['ALL_POSTS'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 365 => $user->lang['1_YEAR']);
				$sort_by_post_text = array('a' => $user->lang['AUTHOR'], 't' => $user->lang['POST_TIME'], 's' => $user->lang['SUBJECT']);

				$_options = array('topic', 'post');
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
				$user_prefs_data = array(
					'S_PREFS'			=> true,
					'S_JABBER_DISABLED'	=> ($config['jab_enable'] && $user_row['user_jabber'] && @extension_loaded('xml')) ? false : true,

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
					'DEFAULT_DATEFORMAT'	=> $config['default_dateformat'],
					'A_DEFAULT_DATEFORMAT'	=> addslashes($config['default_dateformat']),

					'S_LANG_OPTIONS'	=> language_select($data['lang']),
					'S_STYLE_OPTIONS'	=> style_select($data['style']),
				);

				/**
				* Modify users preferences data before assigning it to the template
				*
				* @event core.acp_users_prefs_modify_template_data
				* @var	array	data				Array with users preferences data
				* @var	array	user_row			Array with user data
				* @var	array	user_prefs_data		Array with users preferences data to be assigned to the template
				* @since 3.1.0-b3
				*/
				$vars = array('data', 'user_row', 'user_prefs_data');
				extract($phpbb_dispatcher->trigger_event('core.acp_users_prefs_modify_template_data', compact($vars)));

				$template->assign_vars($user_prefs_data);

			break;

			case 'avatar':

				include($phpbb_root_path . 'includes/functions_display.' . $phpEx);

				$avatars_enabled = false;

				if ($config['allow_avatar'])
				{
					$phpbb_avatar_manager = $phpbb_container->get('avatar.manager');
					$avatar_drivers = $phpbb_avatar_manager->get_enabled_drivers();

					// This is normalised data, without the user_ prefix
					$avatar_data = \phpbb\avatar\manager::clean_row($user_row, 'user');

					if ($submit)
					{
						if (check_form_key($form_name))
						{
							$driver_name = $phpbb_avatar_manager->clean_driver_name($request->variable('avatar_driver', ''));

							if (in_array($driver_name, $avatar_drivers) && !$request->is_set_post('avatar_delete'))
							{
								$driver = $phpbb_avatar_manager->get_driver($driver_name);
								$result = $driver->process_form($request, $template, $user, $avatar_data, $error);

								if ($result && empty($error))
								{
									// Success! Lets save the result in the database
									$result = array(
										'user_avatar_type' => $driver_name,
										'user_avatar' => $result['avatar'],
										'user_avatar_width' => $result['avatar_width'],
										'user_avatar_height' => $result['avatar_height'],
									);

									$sql = 'UPDATE ' . USERS_TABLE . '
										SET ' . $db->sql_build_array('UPDATE', $result) . '
										WHERE user_id = ' . (int) $user_id;

									$db->sql_query($sql);
									trigger_error($user->lang['USER_AVATAR_UPDATED'] . adm_back_link($this->u_action . '&amp;u=' . $user_id));
								}
							}
						}
						else
						{
							trigger_error($user->lang['FORM_INVALID'] . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
						}
					}

					// Handle deletion of avatars
					if ($request->is_set_post('avatar_delete'))
					{
						if (!confirm_box(true))
						{
							confirm_box(false, $user->lang('CONFIRM_AVATAR_DELETE'), build_hidden_fields(array(
									'avatar_delete'     => true))
							);
						}
						else
						{
							$phpbb_avatar_manager->handle_avatar_delete($db, $user, $avatar_data, USERS_TABLE, 'user_');

							trigger_error($user->lang['USER_AVATAR_UPDATED'] . adm_back_link($this->u_action . '&amp;u=' . $user_id));
						}
					}

					$selected_driver = $phpbb_avatar_manager->clean_driver_name($request->variable('avatar_driver', $user_row['user_avatar_type']));

					foreach ($avatar_drivers as $current_driver)
					{
						$driver = $phpbb_avatar_manager->get_driver($current_driver);

						$avatars_enabled = true;
						$config_name = $phpbb_avatar_manager->get_driver_config_name($driver);
						$template->set_filenames(array(
							'avatar' => "acp_avatar_options_{$config_name}.html",
						));

						if ($driver->prepare_form($request, $template, $user, $avatar_data, $error))
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

				// Replace "error" strings with their real, localised form
				$error = $phpbb_avatar_manager->localize_errors($user, $error);

				$avatar = phpbb_get_user_avatar($user_row, 'USER_AVATAR', true);

				$template->assign_vars(array(
					'S_AVATAR'	=> true,
					'ERROR'			=> (!empty($error)) ? implode('<br />', $error) : '',
					'AVATAR'		=> (empty($avatar) ? '<img src="' . $phpbb_admin_path . 'images/no_avatar.gif" alt="" />' : $avatar),

					'S_FORM_ENCTYPE'	=> ' enctype="multipart/form-data"',

					'L_AVATAR_EXPLAIN'	=> sprintf($user->lang['AVATAR_EXPLAIN'], $config['avatar_max_width'], $config['avatar_max_height'], $config['avatar_filesize'] / 1024),

					'S_AVATARS_ENABLED'		=> ($config['allow_avatar'] && $avatars_enabled),
				));

			break;

			case 'rank':

				if ($submit)
				{
					if (!check_form_key($form_name))
					{
						trigger_error($user->lang['FORM_INVALID'] . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
					}

					$rank_id = request_var('user_rank', 0);

					$sql = 'UPDATE ' . USERS_TABLE . "
						SET user_rank = $rank_id
						WHERE user_id = $user_id";
					$db->sql_query($sql);

					trigger_error($user->lang['USER_RANK_UPDATED'] . adm_back_link($this->u_action . '&amp;u=' . $user_id));
				}

				$sql = 'SELECT *
					FROM ' . RANKS_TABLE . '
					WHERE rank_special = 1
					ORDER BY rank_title';
				$result = $db->sql_query($sql);

				$s_rank_options = '<option value="0"' . ((!$user_row['user_rank']) ? ' selected="selected"' : '') . '>' . $user->lang['NO_SPECIAL_RANK'] . '</option>';

				while ($row = $db->sql_fetchrow($result))
				{
					$selected = ($user_row['user_rank'] && $row['rank_id'] == $user_row['user_rank']) ? ' selected="selected"' : '';
					$s_rank_options .= '<option value="' . $row['rank_id'] . '"' . $selected . '>' . $row['rank_title'] . '</option>';
				}
				$db->sql_freeresult($result);

				$template->assign_vars(array(
					'S_RANK'			=> true,
					'S_RANK_OPTIONS'	=> $s_rank_options)
				);

			break;

			case 'sig':

				include_once($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
				include_once($phpbb_root_path . 'includes/functions_display.' . $phpEx);

				$enable_bbcode	= ($config['allow_sig_bbcode']) ? (bool) $this->optionget($user_row, 'sig_bbcode') : false;
				$enable_smilies	= ($config['allow_sig_smilies']) ? (bool) $this->optionget($user_row, 'sig_smilies') : false;
				$enable_urls	= ($config['allow_sig_links']) ? (bool) $this->optionget($user_row, 'sig_links') : false;
				$signature		= utf8_normalize_nfc(request_var('signature', (string) $user_row['user_sig'], true));

				$preview		= (isset($_POST['preview'])) ? true : false;

				if ($submit || $preview)
				{
					include_once($phpbb_root_path . 'includes/message_parser.' . $phpEx);

					$enable_bbcode	= ($config['allow_sig_bbcode']) ? ((request_var('disable_bbcode', false)) ? false : true) : false;
					$enable_smilies	= ($config['allow_sig_smilies']) ? ((request_var('disable_smilies', false)) ? false : true) : false;
					$enable_urls	= ($config['allow_sig_links']) ? ((request_var('disable_magic_url', false)) ? false : true) : false;

					$message_parser = new parse_message($signature);

					// Allowing Quote BBCode
					$message_parser->parse($enable_bbcode, $enable_urls, $enable_smilies, $config['allow_sig_img'], $config['allow_sig_flash'], true, $config['allow_sig_links'], true, 'sig');

					if (sizeof($message_parser->warn_msg))
					{
						$error[] = implode('<br />', $message_parser->warn_msg);
					}

					if (!check_form_key($form_name))
					{
						$error = 'FORM_INVALID';
					}

					if (!sizeof($error) && $submit)
					{
						$this->optionset($user_row, 'sig_bbcode', $enable_bbcode);
						$this->optionset($user_row, 'sig_smilies', $enable_smilies);
						$this->optionset($user_row, 'sig_links', $enable_urls);

						$sql_ary = array(
							'user_sig'					=> (string) $message_parser->message,
							'user_options'				=> $user_row['user_options'],
							'user_sig_bbcode_uid'		=> (string) $message_parser->bbcode_uid,
							'user_sig_bbcode_bitfield'	=> (string) $message_parser->bbcode_bitfield
						);

						$sql = 'UPDATE ' . USERS_TABLE . '
							SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
							WHERE user_id = ' . $user_id;
						$db->sql_query($sql);

						trigger_error($user->lang['USER_SIG_UPDATED'] . adm_back_link($this->u_action . '&amp;u=' . $user_id));
					}

					// Replace "error" strings with their real, localised form
					$error = array_map(array($user, 'lang'), $error);
				}

				$signature_preview = '';

				if ($preview)
				{
					// Now parse it for displaying
					$signature_preview = $message_parser->format_display($enable_bbcode, $enable_urls, $enable_smilies, false);
					unset($message_parser);
				}

				decode_message($signature, $user_row['user_sig_bbcode_uid']);

				$template->assign_vars(array(
					'S_SIGNATURE'		=> true,

					'SIGNATURE'			=> $signature,
					'SIGNATURE_PREVIEW'	=> $signature_preview,

					'S_BBCODE_CHECKED'		=> (!$enable_bbcode) ? ' checked="checked"' : '',
					'S_SMILIES_CHECKED'		=> (!$enable_smilies) ? ' checked="checked"' : '',
					'S_MAGIC_URL_CHECKED'	=> (!$enable_urls) ? ' checked="checked"' : '',

					'BBCODE_STATUS'			=> ($config['allow_sig_bbcode']) ? sprintf($user->lang['BBCODE_IS_ON'], '<a href="' . append_sid("{$phpbb_root_path}faq.$phpEx", 'mode=bbcode') . '">', '</a>') : sprintf($user->lang['BBCODE_IS_OFF'], '<a href="' . append_sid("{$phpbb_root_path}faq.$phpEx", 'mode=bbcode') . '">', '</a>'),
					'SMILIES_STATUS'		=> ($config['allow_sig_smilies']) ? $user->lang['SMILIES_ARE_ON'] : $user->lang['SMILIES_ARE_OFF'],
					'IMG_STATUS'			=> ($config['allow_sig_img']) ? $user->lang['IMAGES_ARE_ON'] : $user->lang['IMAGES_ARE_OFF'],
					'FLASH_STATUS'			=> ($config['allow_sig_flash']) ? $user->lang['FLASH_IS_ON'] : $user->lang['FLASH_IS_OFF'],
					'URL_STATUS'			=> ($config['allow_sig_links']) ? $user->lang['URL_IS_ON'] : $user->lang['URL_IS_OFF'],

					'L_SIGNATURE_EXPLAIN'	=> $user->lang('SIGNATURE_EXPLAIN', (int) $config['max_sig_chars']),

					'S_BBCODE_ALLOWED'		=> $config['allow_sig_bbcode'],
					'S_SMILIES_ALLOWED'		=> $config['allow_sig_smilies'],
					'S_BBCODE_IMG'			=> ($config['allow_sig_img']) ? true : false,
					'S_BBCODE_FLASH'		=> ($config['allow_sig_flash']) ? true : false,
					'S_LINKS_ALLOWED'		=> ($config['allow_sig_links']) ? true : false)
				);

				// Assigning custom bbcodes
				display_custom_bbcodes();

			break;

			case 'attach':

				$start		= request_var('start', 0);
				$deletemark = (isset($_POST['delmarked'])) ? true : false;
				$marked		= request_var('mark', array(0));
				$pagination = $phpbb_container->get('pagination');

				// Sort keys
				$sort_key	= request_var('sk', 'a');
				$sort_dir	= request_var('sd', 'd');

				if ($deletemark && sizeof($marked))
				{
					$sql = 'SELECT attach_id
						FROM ' . ATTACHMENTS_TABLE . '
						WHERE poster_id = ' . $user_id . '
							AND is_orphan = 0
							AND ' . $db->sql_in_set('attach_id', $marked);
					$result = $db->sql_query($sql);

					$marked = array();
					while ($row = $db->sql_fetchrow($result))
					{
						$marked[] = $row['attach_id'];
					}
					$db->sql_freeresult($result);
				}

				if ($deletemark && sizeof($marked))
				{
					if (confirm_box(true))
					{
						$sql = 'SELECT real_filename
							FROM ' . ATTACHMENTS_TABLE . '
							WHERE ' . $db->sql_in_set('attach_id', $marked);
						$result = $db->sql_query($sql);

						$log_attachments = array();
						while ($row = $db->sql_fetchrow($result))
						{
							$log_attachments[] = $row['real_filename'];
						}
						$db->sql_freeresult($result);

						delete_attachments('attach', $marked);

						$message = (sizeof($log_attachments) == 1) ? $user->lang['ATTACHMENT_DELETED'] : $user->lang['ATTACHMENTS_DELETED'];

						add_log('admin', 'LOG_ATTACHMENTS_DELETED', implode($user->lang['COMMA_SEPARATOR'], $log_attachments));
						trigger_error($message . adm_back_link($this->u_action . '&amp;u=' . $user_id));
					}
					else
					{
						confirm_box(false, $user->lang['CONFIRM_OPERATION'], build_hidden_fields(array(
							'u'				=> $user_id,
							'i'				=> $id,
							'mode'			=> $mode,
							'action'		=> $action,
							'delmarked'		=> true,
							'mark'			=> $marked))
						);
					}
				}

				$sk_text = array('a' => $user->lang['SORT_FILENAME'], 'c' => $user->lang['SORT_EXTENSION'], 'd' => $user->lang['SORT_SIZE'], 'e' => $user->lang['SORT_DOWNLOADS'], 'f' => $user->lang['SORT_POST_TIME'], 'g' => $user->lang['SORT_TOPIC_TITLE']);
				$sk_sql = array('a' => 'a.real_filename', 'c' => 'a.extension', 'd' => 'a.filesize', 'e' => 'a.download_count', 'f' => 'a.filetime', 'g' => 't.topic_title');

				$sd_text = array('a' => $user->lang['ASCENDING'], 'd' => $user->lang['DESCENDING']);

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
				$result = $db->sql_query_limit($sql, 1);
				$num_attachments = (int) $db->sql_fetchfield('num_attachments');
				$db->sql_freeresult($result);

				$sql = 'SELECT a.*, t.topic_title, p.message_subject as message_title
					FROM ' . ATTACHMENTS_TABLE . ' a
						LEFT JOIN ' . TOPICS_TABLE . ' t ON (a.topic_id = t.topic_id
							AND a.in_message = 0)
						LEFT JOIN ' . PRIVMSGS_TABLE . ' p ON (a.post_msg_id = p.msg_id
							AND a.in_message = 1)
					WHERE a.poster_id = ' . $user_id . "
						AND a.is_orphan = 0
					ORDER BY $order_by";
				$result = $db->sql_query_limit($sql, $config['topics_per_page'], $start);

				while ($row = $db->sql_fetchrow($result))
				{
					if ($row['in_message'])
					{
						$view_topic = append_sid("{$phpbb_root_path}ucp.$phpEx", "i=pm&amp;p={$row['post_msg_id']}");
					}
					else
					{
						$view_topic = append_sid("{$phpbb_root_path}viewtopic.$phpEx", "t={$row['topic_id']}&amp;p={$row['post_msg_id']}") . '#p' . $row['post_msg_id'];
					}

					$template->assign_block_vars('attach', array(
						'REAL_FILENAME'		=> $row['real_filename'],
						'COMMENT'			=> nl2br($row['attach_comment']),
						'EXTENSION'			=> $row['extension'],
						'SIZE'				=> get_formatted_filesize($row['filesize']),
						'DOWNLOAD_COUNT'	=> $row['download_count'],
						'POST_TIME'			=> $user->format_date($row['filetime']),
						'TOPIC_TITLE'		=> ($row['in_message']) ? $row['message_title'] : $row['topic_title'],

						'ATTACH_ID'			=> $row['attach_id'],
						'POST_ID'			=> $row['post_msg_id'],
						'TOPIC_ID'			=> $row['topic_id'],

						'S_IN_MESSAGE'		=> $row['in_message'],

						'U_DOWNLOAD'		=> append_sid("{$phpbb_root_path}download/file.$phpEx", 'mode=view&amp;id=' . $row['attach_id']),
						'U_VIEW_TOPIC'		=> $view_topic)
					);
				}
				$db->sql_freeresult($result);

				$base_url = $this->u_action . "&amp;u=$user_id&amp;sk=$sort_key&amp;sd=$sort_dir";
				$pagination->generate_template_pagination($base_url, 'pagination', 'start', $num_attachments, $config['topics_per_page'], $start);

				$template->assign_vars(array(
					'S_ATTACHMENTS'		=> true,
					'S_SORT_KEY'		=> $s_sort_key,
					'S_SORT_DIR'		=> $s_sort_dir,
				));

			break;

			case 'groups':

				include($phpbb_root_path . 'includes/functions_user.' . $phpEx);

				$user->add_lang(array('groups', 'acp/groups'));
				$group_id = request_var('g', 0);

				if ($group_id)
				{
					// Check the founder only entry for this group to make sure everything is well
					$sql = 'SELECT group_founder_manage
						FROM ' . GROUPS_TABLE . '
						WHERE group_id = ' . $group_id;
					$result = $db->sql_query($sql);
					$founder_manage = (int) $db->sql_fetchfield('group_founder_manage');
					$db->sql_freeresult($result);

					if ($user->data['user_type'] != USER_FOUNDER && $founder_manage)
					{
						trigger_error($user->lang['NOT_ALLOWED_MANAGE_GROUP'] . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
					}
				}
				else
				{
					$founder_manage = 0;
				}

				switch ($action)
				{
					case 'demote':
					case 'promote':
					case 'default':
						if (!$group_id)
						{
							trigger_error($user->lang['NO_GROUP'] . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
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
								trigger_error($user->lang['NO_GROUP'] . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
							}

							if ($error = group_user_del($group_id, $user_id))
							{
								trigger_error($user->lang[$error] . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
							}

							$error = array();

							// The delete action was successful - therefore update the user row...
							$sql = 'SELECT u.*, s.*
								FROM ' . USERS_TABLE . ' u
									LEFT JOIN ' . SESSIONS_TABLE . ' s ON (s.session_user_id = u.user_id)
								WHERE u.user_id = ' . $user_id . '
								ORDER BY s.session_time DESC';
							$result = $db->sql_query_limit($sql, 1);
							$user_row = $db->sql_fetchrow($result);
							$db->sql_freeresult($result);
						}
						else
						{
							confirm_box(false, $user->lang['CONFIRM_OPERATION'], build_hidden_fields(array(
								'u'				=> $user_id,
								'i'				=> $id,
								'mode'			=> $mode,
								'action'		=> $action,
								'g'				=> $group_id))
							);
						}

					break;

					case 'approve':

						if (confirm_box(true))
						{
							if (!$group_id)
							{
								trigger_error($user->lang['NO_GROUP'] . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
							}
							group_user_attributes($action, $group_id, $user_id);
						}
						else
						{
							confirm_box(false, $user->lang['CONFIRM_OPERATION'], build_hidden_fields(array(
								'u'				=> $user_id,
								'i'				=> $id,
								'mode'			=> $mode,
								'action'		=> $action,
								'g'				=> $group_id))
							);
						}

					break;
				}

				// Add user to group?
				if ($submit)
				{

					if (!check_form_key($form_name))
					{
						trigger_error($user->lang['FORM_INVALID'] . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
					}

					if (!$group_id)
					{
						trigger_error($user->lang['NO_GROUP'] . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
					}

					// Add user/s to group
					if ($error = group_user_add($group_id, $user_id))
					{
						trigger_error($user->lang[$error] . adm_back_link($this->u_action . '&amp;u=' . $user_id), E_USER_WARNING);
					}

					$error = array();
				}

				$sql = 'SELECT ug.*, g.*
					FROM ' . GROUPS_TABLE . ' g, ' . USER_GROUP_TABLE . " ug
					WHERE ug.user_id = $user_id
						AND g.group_id = ug.group_id
					ORDER BY g.group_type DESC, ug.user_pending ASC, g.group_name";
				$result = $db->sql_query($sql);

				$i = 0;
				$group_data = $id_ary = array();
				while ($row = $db->sql_fetchrow($result))
				{
					$type = ($row['group_type'] == GROUP_SPECIAL) ? 'special' : (($row['user_pending']) ? 'pending' : 'normal');

					$group_data[$type][$i]['group_id']		= $row['group_id'];
					$group_data[$type][$i]['group_name']	= $row['group_name'];
					$group_data[$type][$i]['group_leader']	= ($row['group_leader']) ? 1 : 0;

					$id_ary[] = $row['group_id'];

					$i++;
				}
				$db->sql_freeresult($result);

				// Select box for other groups
				$sql = 'SELECT group_id, group_name, group_type, group_founder_manage
					FROM ' . GROUPS_TABLE . '
					' . ((sizeof($id_ary)) ? 'WHERE ' . $db->sql_in_set('group_id', $id_ary, true) : '') . '
					ORDER BY group_type DESC, group_name ASC';
				$result = $db->sql_query($sql);

				$s_group_options = '';
				while ($row = $db->sql_fetchrow($result))
				{
					if (!$config['coppa_enable'] && $row['group_name'] == 'REGISTERED_COPPA')
					{
						continue;
					}

					// Do not display those groups not allowed to be managed
					if ($user->data['user_type'] != USER_FOUNDER && $row['group_founder_manage'])
					{
						continue;
					}

					$s_group_options .= '<option' . (($row['group_type'] == GROUP_SPECIAL) ? ' class="sep"' : '') . ' value="' . $row['group_id'] . '">' . (($row['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $row['group_name']] : $row['group_name']) . '</option>';
				}
				$db->sql_freeresult($result);

				$current_type = '';
				foreach ($group_data as $group_type => $data_ary)
				{
					if ($current_type != $group_type)
					{
						$template->assign_block_vars('group', array(
							'S_NEW_GROUP_TYPE'		=> true,
							'GROUP_TYPE'			=> $user->lang['USER_GROUP_' . strtoupper($group_type)])
						);
					}

					foreach ($data_ary as $data)
					{
						$template->assign_block_vars('group', array(
							'U_EDIT_GROUP'		=> append_sid("{$phpbb_admin_path}index.$phpEx", "i=groups&amp;mode=manage&amp;action=edit&amp;u=$user_id&amp;g={$data['group_id']}&amp;back_link=acp_users_groups"),
							'U_DEFAULT'			=> $this->u_action . "&amp;action=default&amp;u=$user_id&amp;g=" . $data['group_id'],
							'U_DEMOTE_PROMOTE'	=> $this->u_action . '&amp;action=' . (($data['group_leader']) ? 'demote' : 'promote') . "&amp;u=$user_id&amp;g=" . $data['group_id'],
							'U_DELETE'			=> $this->u_action . "&amp;action=delete&amp;u=$user_id&amp;g=" . $data['group_id'],
							'U_APPROVE'			=> ($group_type == 'pending') ? $this->u_action . "&amp;action=approve&amp;u=$user_id&amp;g=" . $data['group_id'] : '',

							'GROUP_NAME'		=> ($group_type == 'special') ? $user->lang['G_' . $data['group_name']] : $data['group_name'],
							'L_DEMOTE_PROMOTE'	=> ($data['group_leader']) ? $user->lang['GROUP_DEMOTE'] : $user->lang['GROUP_PROMOTE'],

							'S_IS_MEMBER'		=> ($group_type != 'pending') ? true : false,
							'S_NO_DEFAULT'		=> ($user_row['group_id'] != $data['group_id']) ? true : false,
							'S_SPECIAL_GROUP'	=> ($group_type == 'special') ? true : false,
							)
						);
					}
				}

				$template->assign_vars(array(
					'S_GROUPS'			=> true,
					'S_GROUP_OPTIONS'	=> $s_group_options)
				);

			break;

			case 'perm':

				include_once($phpbb_root_path . 'includes/acp/auth.' . $phpEx);

				$auth_admin = new auth_admin();

				$user->add_lang('acp/permissions');
				add_permission_language();

				$forum_id = request_var('f', 0);

				// Global Permissions
				if (!$forum_id)
				{
					// Select auth options
					$sql = 'SELECT auth_option, is_local, is_global
						FROM ' . ACL_OPTIONS_TABLE . '
						WHERE auth_option ' . $db->sql_like_expression($db->get_any_char() . '_') . '
							AND is_global = 1
						ORDER BY auth_option';
					$result = $db->sql_query($sql);

					$hold_ary = array();

					while ($row = $db->sql_fetchrow($result))
					{
						$hold_ary = $auth_admin->get_mask('view', $user_id, false, false, $row['auth_option'], 'global', ACL_NEVER);
						$auth_admin->display_mask('view', $row['auth_option'], $hold_ary, 'user', false, false);
					}
					$db->sql_freeresult($result);

					unset($hold_ary);
				}
				else
				{
					$sql = 'SELECT auth_option, is_local, is_global
						FROM ' . ACL_OPTIONS_TABLE . "
						WHERE auth_option " . $db->sql_like_expression($db->get_any_char() . '_') . "
							AND is_local = 1
						ORDER BY is_global DESC, auth_option";
					$result = $db->sql_query($sql);

					while ($row = $db->sql_fetchrow($result))
					{
						$hold_ary = $auth_admin->get_mask('view', $user_id, false, $forum_id, $row['auth_option'], 'local', ACL_NEVER);
						$auth_admin->display_mask('view', $row['auth_option'], $hold_ary, 'user', true, false);
					}
					$db->sql_freeresult($result);
				}

				$s_forum_options = '<option value="0"' . ((!$forum_id) ? ' selected="selected"' : '') . '>' . $user->lang['VIEW_GLOBAL_PERMS'] . '</option>';
				$s_forum_options .= make_forum_select($forum_id, false, true, false, false, false);

				$template->assign_vars(array(
					'S_PERMISSIONS'				=> true,

					'S_GLOBAL'					=> (!$forum_id) ? true : false,
					'S_FORUM_OPTIONS'			=> $s_forum_options,

					'U_ACTION'					=> $this->u_action . '&amp;u=' . $user_id,
					'U_USER_PERMISSIONS'		=> append_sid("{$phpbb_admin_path}index.$phpEx" ,'i=permissions&amp;mode=setting_user_global&amp;user_id[]=' . $user_id),
					'U_USER_FORUM_PERMISSIONS'	=> append_sid("{$phpbb_admin_path}index.$phpEx", 'i=permissions&amp;mode=setting_user_local&amp;user_id[]=' . $user_id))
				);

			break;

		}

		// Assign general variables
		$template->assign_vars(array(
			'S_ERROR'			=> (sizeof($error)) ? true : false,
			'ERROR_MSG'			=> (sizeof($error)) ? implode('<br />', $error) : '')
		);
	}

	/**
	* Set option bit field for user options in a user row array.
	*
	* Optionset replacement for this module based on $user->optionset.
	*
	* @param array $user_row Row from the users table.
	* @param int $key Option key, as defined in $user->keyoptions property.
	* @param bool $value True to set the option, false to clear the option.
	* @param int $data Current bit field value, or false to use $user_row['user_options']
	* @return int|bool If $data is false, the bit field is modified and
	*                  written back to $user_row['user_options'], and
	*                  return value is true if the bit field changed and
	*                  false otherwise. If $data is not false, the new
	*                  bitfield value is returned.
	*/
	function optionset(&$user_row, $key, $value, $data = false)
	{
		global $user;

		$var = ($data !== false) ? $data : $user_row['user_options'];

		$new_var = phpbb_optionset($user->keyoptions[$key], $value, $var);

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
	* Optionget replacement for this module based on $user->optionget.
	*
	* @param array $user_row Row from the users table.
	* @param int $key option key, as defined in $user->keyoptions property.
	* @param int $data bit field value to use, or false to use $user_row['user_options']
	* @return bool true if the option is set in the bit field, false otherwise
	*/
	function optionget(&$user_row, $key, $data = false)
	{
		global $user;

		$var = ($data !== false) ? $data : $user_row['user_options'];
		return phpbb_optionget($user->keyoptions[$key], $var);
	}
}
