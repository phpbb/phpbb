<?php
/** 
*
* @package acp
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* @package acp
*/
class acp_users
{
	function main($id, $mode)
	{
		global $config, $db, $user, $auth, $template, $cache;
		global $SID, $phpbb_root_path, $phpbb_admin_path, $phpEx, $table_prefix, $file_uploads;

		$user->add_lang(array('posting', 'ucp', 'acp/users'));
		$this->tpl_name = 'acp_users';
		$this->page_title = 'ACP_USER_' . strtoupper($mode);

		$u_action = "{$phpbb_admin_path}index.$phpEx$SID&amp;i=$id&amp;mode=$mode";

		include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
		include($phpbb_root_path . 'includes/functions_profile_fields.' . $phpEx);

		$error		= array();
		$username	= request_var('username', '');
		$user_id	= request_var('u', 0);
		$action		= request_var('action', '');
		
		$submit		= (isset($_POST['update'])) ? true : false;

		// Whois (special case)
		if ($action == 'whois')
		{
			$this->page_title = 'WHOIS';
			$this->tpl_name = 'simple_body';

			$user_ip = request_var('user_ip', '');
			$domain = gethostbyaddr($user_ip);
			$ipwhois = '';

			if ($ipwhois = user_ipwhois($user_ip))
			{
				$ipwhois = trim(preg_replace('#(\s+?)([\w\-\._\+]+?@[\w\-\.]+?)(\s+?)#s', '\1<a href="mailto:\2">\2</a>\3', $ipwhois));
			}

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
				'U_ACTION'			=> $u_action,
				'S_SELECT_USER'		=> true,
				'U_FIND_USERNAME'	=> $phpbb_root_path . "memberlist.$phpEx$SID&amp;mode=searchuser&amp;form=select_user&amp;field=username",
				)
			);

			return;
		}

		if (!$user_id)
		{
			$sql = 'SELECT user_id
				FROM ' . USERS_TABLE . "
				WHERE username = '" . $db->sql_escape($username) . "'";
			$result = $db->sql_query($sql);
			$user_id = (int) $db->sql_fetchfield('user_id', false, $result);
			$db->sql_freeresult($result);

			if (!$user_id)
			{
				trigger_error($user->lang['NO_USER'] . adm_back_link($u_action));
			}
		}

		// Generate content for all modes
		$sql = 'SELECT u.*, s.*
			FROM ' . USERS_TABLE . ' u
				LEFT JOIN ' . SESSIONS_TABLE . ' s ON (s.session_user_id = u.user_id)
			WHERE u.user_id = ' . $user_id . '
			ORDER BY s.session_time DESC';
		$result = $db->sql_query($sql);
		$user_row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if (!$user_row)
		{
			trigger_error($user->lang['NO_USER'] . adm_back_link($u_action));
		}

		// Generate overall "header" for user admin
		$s_form_options = '';
		$forms_ary = array('overview', 'feedback', 'profile', 'prefs', 'avatar', 'sig', 'groups', 'perm', 'attach');

		foreach ($forms_ary as $value)
		{
			$selected = ($mode == $value) ? ' selected="selected"' : '';
			$s_form_options .= '<option value="' . $value . '"' . $selected . '>' . $user->lang['ACP_USER_' . strtoupper($value)]  . '</option>';
		}

		$template->assign_vars(array(
			'U_BACK'			=> $u_action,
			'U_MODE_SELECT'		=> "{$phpbb_admin_path}index.$phpEx$SID&amp;i=$id&amp;u=$user_id",
			'U_ACTION'			=> $u_action . '&amp;u=' . $user_id,
			'S_FORM_OPTIONS'	=> $s_form_options)
		);

		switch ($mode)
		{
			case 'overview':
				
				$delete			= request_var('delete', 0);
				$delete_type	= request_var('delete_type', '');
				$ip				= request_var('ip', 'ip');

				if ($submit)
				{
					// You can't delete the founder
					if ($delete && $user_row['user_type'] != USER_FOUNDER)
					{
						if (!$auth->acl_get('a_userdel'))
						{
							trigger_error($user->lang['NO_ADMIN'] . adm_back_link($u_action . '&amp;u=' . $user_id));
						}

						if (confirm_box(true))
						{
							user_delete($delete_type, $user_id);

							add_log('admin', 'LOG_USER_DELETED', $user_row['username']);
							trigger_error($user->lang['USER_DELETED'] . adm_back_link($u_action));
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

					// Handle quicktool actions
					switch ($action)
					{
						case 'banuser':
						case 'banemail':
						case 'banip':
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

							user_ban(substr($action, 3), $ban, 0, 0, 0, $user->lang[$reason]);

							add_log('admin', $log, $user->lang['reason']);
							add_log('user', $user_id, $log, $user->lang['reason']);

							trigger_error($user->lang['BAN_SUCCESSFULL'] . adm_back_link($u_action));

						break;

						case 'reactivate':

							if ($config['email_enable'])
							{
								include_once($phpbb_root_path . 'includes/functions_messenger.' . $phpEx);

								$server_url = generate_board_url();

								$user_actkey = gen_rand_string(10);
								$key_len = 54 - (strlen($server_url));
								$key_len = ($key_len > 6) ? $key_len : 6;
								$user_actkey = substr($user_actkey, 0, $key_len);

								if ($user_row['user_type'] != USER_INACTIVE)
								{
									user_active_flip($user_id, $user_row['user_type'], $user_actkey, $user_row['username']);
								}

								$messenger = new messenger(false);

								$messenger->template('user_resend_inactive', $user_row['user_lang']);

								$messenger->replyto($config['board_contact']);
								$messenger->to($user_row['user_email'], $user_row['username']);

								$messenger->headers('X-AntiAbuse: Board servername - ' . $config['server_name']);
								$messenger->headers('X-AntiAbuse: User_id - ' . $user->data['user_id']);
								$messenger->headers('X-AntiAbuse: Username - ' . $user->data['username']);
								$messenger->headers('X-AntiAbuse: User IP - ' . $user->ip);

								$messenger->assign_vars(array(
									'SITENAME'		=> $config['sitename'],
									'WELCOME_MSG'	=> sprintf($user->lang['WELCOME_SUBJECT'], $config['sitename']),
									'USERNAME'		=> $user_row['username'],
									'EMAIL_SIG'		=> str_replace('<br />', "\n", "-- \n" . $config['board_email_sig']),

									'U_ACTIVATE'	=> "$server_url/ucp.$phpEx?mode=activate&u={$user_row['user_id']}&k=$user_actkey")
								);

								$messenger->send(NOTIFY_EMAIL);

								add_log('admin', 'LOG_USER_REACTIVATE', $user_row['username']);
								add_log('user', $user_id, 'LOG_USER_REACTIVATE_USER');

								trigger_error($user->lang['FORCE_REACTIVATION_SUCCESS'] . adm_back_link($u_action));
							}

						break;

						case 'active':

							user_active_flip($user_id, $user_row['user_type'], false, $user_row['username']);

							$message = ($user_row['user_type'] == USER_INACTIVE) ? 'USER_ADMIN_ACTIVATED' : 'USER_ADMIN_DEACTIVED';
							$log = ($user_row['user_type'] == USER_INACTIVE) ? 'LOG_USER_ACTIVE' : 'LOG_USER_INACTIVE';

							add_log('user', $user_id, $log . '_USER');

							trigger_error($user->lang[$message] . adm_back_link($u_action));

						break;

						case 'delsig':

							$sql_ary = array(
								'user_sig'					=> '',
								'user_sig_bbcode_uid'		=> '',
								'user_sig_bbcode_bitfield'	=> 0
							);

							$sql = 'UPDATE ' . USERS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
								WHERE user_id = $user_id";
							$db->sql_query($sql);
						
							add_log('admin', 'LOG_USER_DEL_SIG', $user_row['username']);
							add_log('user', $user_id, 'LOG_USER_DEL_SIG_USER');

							trigger_error($user->lang['USER_ADMIN_SIG_REMOVED'] . adm_back_link($u_action));

						break;

						case 'delavatar':
							
							$sql_ary = array(
								'user_avatar'			=> '',
								'user_avatar_type'		=> 0,
								'user_avatar_width'		=> 0,
								'user_avatar_height'	=> 0,
							);

							$sql = 'UPDATE ' . USERS_TABLE . '
								SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
								WHERE user_id = $user_id";
							$db->sql_query($sql);

							// Delete old avatar if present
							if ($user_row['user_avatar'] && $user_row['user_avatar_type'] != AVATAR_GALLERY)
							{
								avatar_delete($user_row['user_avatar']);
							}

							add_log('admin', 'LOG_USER_DEL_AVATAR', $user_row['username']);
							add_log('user', $user_id, 'LOG_USER_DEL_AVATAR_USER');

							trigger_error($user->lang['USER_ADMIN_AVATAR_REMOVED'] . adm_back_link($u_action));
						break;

						case 'delposts':

							if (confirm_box(true))
							{
								$sql = 'SELECT topic_id, COUNT(post_id) AS total_posts
									FROM ' . POSTS_TABLE . "
									WHERE poster_id = $user_id
									GROUP BY topic_id";
								$result = $db->sql_query($sql);

								$topic_id_ary = array();
								while ($row = $db->sql_fetchrow($result))
								{
									$topic_id_ary[$row['topic_id']] = $row['total_posts'];
								}
								$db->sql_freeresult($result);

								if (sizeof($topic_id_ary))
								{
									$sql = 'SELECT topic_id, topic_replies, topic_replies_real
										FROM ' . TOPICS_TABLE . '
										WHERE topic_id IN (' . implode(', ', array_keys($topic_id_ary)) . ')';
									$result = $db->sql_query($sql);

									$del_topic_ary = array();
									while ($row = $db->sql_fetchrow($result))
									{
										if (max($row['topic_replies'], $row['topic_replies_real']) + 1 == $topic_id_ary[$row['topic_id']])
										{
											$del_topic_ary[] = $row['topic_id'];
										}
									}
									$db->sql_freeresult($result);

									if (sizeof($del_topic_ary))
									{
										$sql = 'DELETE FROM ' . TOPICS_TABLE . '
											WHERE topic_id IN (' . implode(', ', $del_topic_ary) . ')';
										$db->sql_query($sql);
									}
								}

								// Delete posts, attachments, etc.
								delete_posts('poster_id', $user_id);

								add_log('admin', 'LOG_USER_DEL_POSTS', $user_row['username']);
								trigger_error($user->lang['USER_POSTS_DELETED'] . adm_back_link($u_action));
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
								trigger_error($user->lang['USER_ATTACHMENTS_REMOVED'] . adm_back_link($u_action));
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
								
							$new_forum_id = request_var('new_f', 0);

							if (!$new_forum_id)
							{
								$this->page_title = 'USER_ADMIN_MOVE_POSTS';

								$template->assign_vars(array(
									'S_SELECT_FORUM'		=> true,
									'U_ACTION'				=> $u_action . "&amp;action=$action&amp;u=$user_id",
									'U_BACK'				=> $u_action . "&amp;u=$user_id",
									'S_FORUM_OPTIONS'		=> make_forum_select(false, false, false, true))
								);

								return;
							}

							// Two stage?
							// Move topics comprising only posts from this user
							$topic_id_ary = $move_topic_ary = $move_post_ary = $new_topic_id_ary = array();
							$forum_id_ary = array($new_forum_id);

							$sql = 'SELECT topic_id, COUNT(post_id) AS total_posts
								FROM ' . POSTS_TABLE . "
								WHERE poster_id = $user_id
									AND forum_id <> $new_forum_id
								GROUP BY topic_id";
							$result = $db->sql_query($sql);

							while ($row = $db->sql_fetchrow($result))
							{
								$topic_id_ary[$row['topic_id']] = $row['total_posts'];
							}
							$db->sql_freeresult($result);

							if (sizeof($topic_id_ary))
							{
								$sql = 'SELECT topic_id, forum_id, topic_title, topic_replies, topic_replies_real
									FROM ' . TOPICS_TABLE . '
									WHERE topic_id IN (' . implode(', ', array_keys($topic_id_ary)) . ')';
								$result = $db->sql_query($sql);

								while ($row = $db->sql_fetchrow($result))
								{
									if (max($row['topic_replies'], $row['topic_replies_real']) + 1 == $topic_id_ary[$row['topic_id']])
									{
										$move_topic_ary[] = $row['topic_id'];
									}
									else
									{
										$move_post_ary[$row['topic_id']]['title'] = $row['topic_title'];
										$move_post_ary[$row['topic_id']]['attach'] = ($row['attach']) ? 1 : 0;
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
										'topic_approved'			=> 1,
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
							$topic_id_ary = array_unique(array_merge($topic_id_ary, $new_topic_id_ary));

							if (sizeof($topic_id_ary))
							{
								sync('reported', 'topic_id', $topic_id_ary);
								sync('topic', 'topic_id', $topic_id_ary);
							}

							if (sizeof($forum_id_ary))
							{
								sync('forum', 'forum_id', $forum_id_ary);
							}

							$sql = 'SELECT forum_name
								FROM ' . FORUMS_TABLE . "
								WHERE forum_id = $new_forum_id";
							$result = $db->sql_query($sql);
							$forum_info = $db->sql_fetchrow($result);
							$db->sql_freeresult($result);

							add_log('admin', 'LOG_USER_MOVE_POSTS', $user_row['username'], $forum_info['forum_name']);
							add_log('user', $user_id, 'LOG_USER_MOVE_POSTS_USER', $forum_info['forum_name']);

							trigger_error($user->lang['USER_POSTS_MOVED'] . adm_back_link($u_action));

						break;
					}

					$data = array();

					// Handle registration info updates
					$var_ary = array(
						'user'				=> (string) $user_row['username'],
						'user_founder'		=> (int) (($user_row['user_type'] == USER_FOUNDER) ? 1 : 0),
						'user_email'		=> (string) $user_row['user_email'],
						'email_confirm'		=> (string) '',
						'user_password'		=> (string) '',
						'password_confirm'	=> (string) '',
						'warnings'			=> (int) $user_row['user_warnings'],
					);

					// Get the data from the form. Use data from the database if no info is provided
					foreach ($var_ary as $var => $default)
					{
						$data[$var] = request_var($var, $default);
					}

					// We use user within the form to circumvent auto filling
					$data['username'] = $data['user'];
					unset($data['user']);

					/**
					* $config['max_warnings'] does not exist yet
					*/
					// Validation data
					$var_ary = array(
						'password_confirm'	=> array('string', true, $config['min_pass_chars'], $config['max_pass_chars']),
						'user_password'		=> array('string', true, $config['min_pass_chars'], $config['max_pass_chars']),
						'warnings'			=> array('num', 0, $config['max_warnings']),
					);

					// Check username if altered
					if ($data['username'] != $user_row['username'])
					{
						$var_ary += array(
							'username'			=> array(
								array('string', false, $config['min_name_chars'], $config['max_name_chars']),
								array('username', $user_row['username'])),
						);
					}

					// Check email if altered
					if ($data['user_email'] != $user_row['user_email'])
					{
						$var_ary += array(
							'user_email'		=> array(
								array('string', false, 6, 60),
								array('email', $user_row['user_email'])
								), 
							'email_confirm'		=> array('string', true, 6, 60)
						);
					}

					$error = validate_data($data, $var_ary);

					if ($data['user_password'] && $data['password_confirm'] != $data['user_password'])
					{
						$error[] = 'NEW_PASSWORD_ERROR';
					}

					if ($data['user_email'] != $user_row['user_email'] && $data['email_confirm'] != $data['user_email'])
					{
						$error[] = 'NEW_EMAIL_ERROR';
					}

					// Which updates do we need to do?
					$update_warning = ($user_row['user_warnings'] != $data['warnings']) ? true : false;
					$update_username = ($user_row['username'] != $data['username']) ? $data['username'] : false;
					$update_password = ($user_row['user_password'] != $data['user_password']) ? true : false;

					if (!sizeof($error))
					{
						$sql_ary = array(
							'username'			=> $data['username'],
							'user_email'		=> $data['user_email'],
							'user_email_hash'	=> crc32(strtolower($data['user_email'])) . strlen($data['user_email'])
						);
						
						if ($user_row['user_type'] != USER_FOUNDER || $user->data['user_type'] == USER_FOUNDER)
						{
							if ($update_warning)
							{
								$sql_ary['user_warnings'] = $data['warnings'];
							}

							if (($user_row['user_type'] == USER_FOUNDER && !$data['user_founder']) || ($user_row['user_type'] != USER_FOUNDER && $data['user_founder']))
							{
								$sql_ary['user_type'] = ($data['user_founder']) ? USER_FOUNDER : USER_NORMAL;
							}
						}

						if ($update_password)
						{
							$sql_ary += array(
								'user_password' => md5($data['user_password']),
								'user_passchg'	=> time(),
							);

							add_log('admin', 'LOG_USER_NEW_PASSWORD', $user_row['username']);
						}

						$sql = 'UPDATE ' . USERS_TABLE . '
							SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
							WHERE user_id = ' . $user_id;
						$db->sql_query($sql);

						/**
						* @todo adjust every data based in the number of user warnings
						*/
						if ($update_warning)
						{
						}

						if ($update_username)
						{
							user_update_name($user_row['username'], $update_username);

							add_log('admin', 'LOG_USER_UPDATE_NAME', $user_row['username'], $update_username);
							add_log('user', $user_id, 'LOG_USER_UPDATE_NAME', $user_row['username'], $update_username);
						}

						add_log('admin', 'LOG_USER_USER_UPDATE', $data['username']);

						trigger_error($user->lang['USER_OVERVIEW_UPDATED'] . adm_back_link($u_action));
					}

					// Replace "error" strings with their real, localised form
					$error = preg_replace('#^([A-Z_]+)$#e', "(!empty(\$user->lang['\\1'])) ? \$user->lang['\\1'] : '\\1'", $error);
				}

				$user_char_ary = array('.*' => 'USERNAME_CHARS_ANY', '[\w]+' => 'USERNAME_ALPHA_ONLY', '[\w_\+\. \-\[\]]+' => 'USERNAME_ALPHA_SPACERS');
				$quick_tool_ary = array('banuser' => 'BAN_USER', 'banemail' => 'BAN_EMAIL', 'banip' => 'BAN_IP', 'active' => (($user_row['user_type'] == USER_INACTIVE) ? 'ACTIVATE' : 'DEACTIVATE'), 'delsig' => 'DEL_SIG', 'delavatar' => 'DEL_AVATAR', 'moveposts' => 'MOVE_POSTS', 'delposts' => 'DEL_POSTS', 'delattach' => 'DEL_ATTACH');
				
				if ($config['email_enable'])
				{
					$quick_tool_ary['reactivate'] = 'FORCE';
				}

				$s_action_options = '<option class="sep" value="">' . $user->lang['SELECT_OPTION'] . '</option>';
				foreach ($quick_tool_ary as $value => $lang)
				{
					$s_action_options .= '<option value="' . $value . '">' . $user->lang['USER_ADMIN_' . $lang]  . '</option>';
				}

				$template->assign_vars(array(
					'L_NAME_CHARS_EXPLAIN'		=> sprintf($user->lang[$user_char_ary[$config['allow_name_chars']] . '_EXPLAIN'], $config['min_name_chars'], $config['max_name_chars']),
					'L_CHANGE_PASSWORD_EXPLAIN'	=> sprintf($user->lang['CHANGE_PASSWORD_EXPLAIN'], $config['min_pass_chars'], $config['max_pass_chars']),
					'S_FOUNDER'					=> ($user->data['user_type'] == USER_FOUNDER) ? true : false,

					'S_OVERVIEW'		=> true,
					'S_USER_IP'			=> ($user_row['user_ip']) ? true : false,
					'S_USER_FOUNDER'	=> ($user_row['user_type'] == USER_FOUNDER) ? true : false,
					'S_ACTION_OPTIONS'	=> $s_action_options,

					'U_SHOW_IP'		=> $u_action . "&amp;u=$user_id&amp;ip=" . (($ip == 'ip') ? 'hostname' : 'ip'),
					'U_WHOIS'		=> $u_action . "&amp;action=whois&amp;user_ip={$user_row['user_ip']}",
					
					'USER'				=> $user_row['username'],
					'USER_REGISTERED'	=> $user->format_date($user_row['user_regdate']),
					'REGISTERED_IP'		=> ($ip == 'hostname') ? gethostbyaddr($user_row['user_ip']) : $user_row['user_ip'],
					'USER_LASTACTIVE'	=> $user->format_date($user_row['user_lastvisit']),
					'USER_EMAIL'		=> $user_row['user_email'],
					'USER_WARNINGS'		=> $user_row['user_warnings'],
					)
				);

			break;

			case 'feedback':

				$user->add_lang('mcp');
				
				// Set up general vars
				$start		= request_var('start', 0);
				$deletemark = (isset($_POST['delmarked'])) ? true : false;
				$deleteall	= (isset($_POST['delall'])) ? true : false;
				$marked		= request_var('mark', array(0));
				$message	= request_var('message', '');

				// Sort keys
				$sort_days	= request_var('st', 0);
				$sort_key	= request_var('sk', 't');
				$sort_dir	= request_var('sd', 'd');

				// Delete entries if requested and able
				if (($deletemark || $deleteall) && $auth->acl_get('a_clearlogs'))
				{
					$where_sql = '';
					if ($deletemark && $marked)
					{
						$sql_in = array();
						foreach ($marked as $mark)
						{
							$sql_in[] = $mark;
						}
						$where_sql = ' AND log_id IN (' . implode(', ', $sql_in) . ')';
						unset($sql_in);
					}

					if ($where_sql || $deleteall)
					{
						$sql = 'DELETE FROM ' . LOG_TABLE . '
							WHERE log_type = ' . LOG_USERS . "
							$where_sql";
						$db->sql_query($sql);

						add_log('admin', 'LOG_CLEAR_USER', $user_row['username']);
					}
				}

				if ($submit && $message)
				{
					add_log('admin', 'LOG_USER_FEEDBACK', $user_row['username']);
					add_log('user', $user_id, 'LOG_USER_GENERAL', $message);

					trigger_error($user->lang['USER_FEEDBACK_ADDED'] . adm_back_link($u_action));
				}
				
				// Sorting
				$limit_days = array(0 => $user->lang['ALL_ENTRIES'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 364 => $user->lang['1_YEAR']);
				$sort_by_text = array('u' => $user->lang['SORT_USERNAME'], 't' => $user->lang['SORT_DATE'], 'i' => $user->lang['SORT_IP'], 'o' => $user->lang['SORT_ACTION']);
				$sort_by_sql = array('u' => 'l.user_id', 't' => 'l.log_time', 'i' => 'l.log_ip', 'o' => 'l.log_operation');

				$s_limit_days = $s_sort_key = $s_sort_dir = $u_sort_param = '';
				gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);

				// Define where and sort sql for use in displaying logs
				$sql_where = ($sort_days) ? (time() - ($sort_days * 86400)) : 0;
				$sql_sort = $sort_by_sql[$sort_key] . ' ' . (($sort_dir == 'd') ? 'DESC' : 'ASC');

				// Grab log data
				$log_data = array();
				$log_count = 0;
				view_log('user', $log_data, $log_count, $config['topics_per_page'], $start, 0, 0, $user_id, $sql_where, $sql_sort);

				$template->assign_vars(array(
					'S_FEEDBACK'	=> true,
					'S_ON_PAGE'		=> on_page($log_count, $config['topics_per_page'], $start),
					'PAGINATION'	=> generate_pagination($u_action . "&amp;u=$user_id&amp;$u_sort_param", $log_count, $config['topics_per_page'], $start, true),

					'S_LIMIT_DAYS'	=> $s_limit_days,
					'S_SORT_KEY'	=> $s_sort_key,
					'S_SORT_DIR'	=> $s_sort_dir,
					'S_CLEARLOGS'	=> $auth->acl_get('a_clearlogs'))
				);

				foreach ($log_data as $row)
				{
					$template->assign_block_vars('log', array(
						'USERNAME'		=> $row['username'],
						'IP'			=> $row['ip'],
						'DATE'			=> $user->format_date($row['time']),
						'ACTION'		=> nl2br($row['action']),
						'ID'			=> $row['id'])
					);
				}

			break;

			case 'profile':

				$cp = new custom_profile();

				$cp_data = $cp_error = array();
				$data = array();

				$sql = 'SELECT lang_id
					FROM ' . LANG_TABLE . "
					WHERE lang_iso = '" . $db->sql_escape($user_row['user_lang']) . "'";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				$user_row['iso_lang_id'] = $row['lang_id'];

				if ($submit)
				{
					$var_ary = array(
						'icq'			=> (string) '',
						'aim'			=> (string) '',
						'msn'			=> (string) '',
						'yim'			=> (string) '',
						'jabber'		=> (string) '',
						'website'		=> (string) '',
						'location'		=> (string) '',
						'occupation'	=> (string) '',
						'interests'		=> (string) '',
						'bday_day'		=> 0,
						'bday_month'	=> 0,
						'bday_year'		=> 0,
					);

					foreach ($var_ary as $var => $default)
					{
						$data[$var] = request_var($var, $default);
					}

					$var_ary = array(
						'icq'			=> array(
							array('string', true, 3, 15),
							array('match', true, '#^[0-9]+$#i')),
						'aim'			=> array('string', true, 5, 255),
						'msn'			=> array('string', true, 5, 255),
						'jabber'		=> array(
							array('string', true, 5, 255),
							array('match', true, '#^[a-z0-9\.\-_\+]+?@(.*?\.)*?[a-z0-9\-_]+?\.[a-z]{2,4}(/.*)?$#i')),
						'yim'			=> array('string', true, 5, 255),
						'website'		=> array(
							array('string', true, 12, 255),
							array('match', true, '#^http[s]?://(.*?\.)*?[a-z0-9\-]+\.[a-z]{2,4}#i')),
						'location'		=> array('string', true, 2, 255),
						'occupation'	=> array('string', true, 2, 500),
						'interests'		=> array('string', true, 2, 500),
						'bday_day'		=> array('num', true, 1, 31),
						'bday_month'	=> array('num', true, 1, 12),
						'bday_year'		=> array('num', true, 1901, gmdate('Y', time())),
					);

					$error = validate_data($data, $var_ary);

					// validate custom profile fields
					$cp->submit_cp_field('profile', $user_row['iso_lang_id'], $cp_data, $cp_error);

					if (sizeof($cp_error))
					{
						$error = array_merge($error, $cp_error);
					}

					if (!sizeof($error))
					{
						$sql_ary = array(
							'user_icq'		=> $data['icq'],
							'user_aim'		=> $data['aim'],
							'user_msnm'		=> $data['msn'],
							'user_yim'		=> $data['yim'],
							'user_jabber'	=> $data['jabber'],
							'user_website'	=> $data['website'],
							'user_from'		=> $data['location'],
							'user_occ'		=> $data['occupation'],
							'user_interests'=> $data['interests'],
							'user_birthday'	=> sprintf('%2d-%2d-%4d', $data['bday_day'], $data['bday_month'], $data['bday_year']),
						);

						$sql = 'UPDATE ' . USERS_TABLE . '
							SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
							WHERE user_id = $user_id";
						$db->sql_query($sql);

						// Update Custom Fields
						if (sizeof($cp_data))
						{
							$sql = 'UPDATE ' . PROFILE_DATA_TABLE . '
								SET ' . $db->sql_build_array('UPDATE', $cp_data) . "
								WHERE user_id = $user_id";
							$db->sql_query($sql);

							if (!$db->sql_affectedrows())
							{
								$cp_data['user_id'] = (int) $user_id;

								$db->return_on_error = true;

								$sql = 'INSERT INTO ' . PROFILE_DATA_TABLE . ' ' . $db->sql_build_array('INSERT', $cp_data);
								$db->sql_query($sql);

								$db->return_on_error = false;
							}
						}

						trigger_error($user->lang['USER_PROFILE_UPDATED'] . adm_back_link($u_action));
					}

					// Replace "error" strings with their real, localised form
					$error = preg_replace('#^([A-Z_]+)$#e', "(!empty(\$user->lang['\\1'])) ? \$user->lang['\\1'] : '\\1'", $error);
				}

				if (!isset($data['bday_day']))
				{
					list($data['bday_day'], $data['bday_month'], $data['bday_year']) = explode('-', $user_row['user_birthday']);
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
				for ($i = $now['year'] - 100; $i < $now['year']; $i++)
				{
					$selected = ($i == $data['bday_year']) ? ' selected="selected"' : '';
					$s_birthday_year_options .= "<option value=\"$i\"$selected>$i</option>";
				}
				unset($now);

				$template->assign_vars(array(
					'ICQ'			=> (isset($data['icq'])) ? $data['icq'] : $user_row['user_icq'],
					'YIM'			=> (isset($data['yim'])) ? $data['yim'] : $user_row['user_yim'],
					'AIM'			=> (isset($data['aim'])) ? $data['aim'] : $user_row['user_aim'],
					'MSN'			=> (isset($data['msn'])) ? $data['msn'] : $user_row['user_msnm'],
					'JABBER'		=> (isset($data['jabber'])) ? $data['jabber'] : $user_row['user_jabber'],
					'WEBSITE'		=> (isset($data['website'])) ? $data['website']: $user_row['user_website'],
					'LOCATION'		=> (isset($data['location'])) ? $data['location'] : $user_row['user_from'],
					'OCCUPATION'	=> (isset($data['occupation'])) ? $data['occupation'] : $user_row['user_occ'],
					'INTERESTS'		=> (isset($data['interests'])) ? $data['interests'] : $user_row['user_interests'],

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

				$data = array();

				if ($submit)
				{
					$var_ary = array(
						'dateformat'		=> (string) $config['default_dateformat'],
						'lang'				=> (string) $config['default_lang'],
						'tz'				=> (float) $config['board_timezone'],
						'style'				=> (int) $config['default_style'],
						'dst'				=> (bool) $config['board_dst'],
						'viewemail'			=> false,
						'massemail'			=> true,
						'hideonline'		=> false,
						'notifymethod'		=> 0,
						'notifypm'			=> true,
						'popuppm'			=> false,
						'allowpm'			=> true,
						'report_pm_notify'	=> false,

						'topic_sk'			=> (string) 't',
						'topic_sd'			=> (string) 'd',
						'topic_st'			=> 0,

						'post_sk'			=> (string) 't',
						'post_sd'			=> (string) 'a',
						'post_st'			=> 0,

						'view_images'		=> true,
						'view_flash'		=> false,
						'view_smilies'		=> true,
						'view_sigs'			=> true,
						'view_avatars'		=> true,
						'view_wordcensor'	=> false,

						'bbcode'	=> true,
						'html'		=> false,
						'smilies'	=> true,
						'sig'		=> true,
						'notify'	=> false,
					);

					foreach ($var_ary as $var => $default)
					{
						$data[$var] = request_var($var, $default);
					}

					$var_ary = array(
						'dateformat'	=> array('string', false, 3, 15),
						'lang'			=> array('match', false, '#^[a-z_]{2,}$#i'),
						'tz'			=> array('num', false, -13, 13),

						'topic_sk'		=> array('string', false, 1, 1),
						'topic_sd'		=> array('string', false, 1, 1),
						'post_sk'		=> array('string', false, 1, 1),
						'post_sd'		=> array('string', false, 1, 1),
					);

					$error = validate_data($data, $var_ary);

					if (!sizeof($error))
					{
						$this->optionset($user_row, 'popuppm', $data['popuppm']);
						$this->optionset($user_row, 'report_pm_notify', $data['report_pm_notify']);
						$this->optionset($user_row, 'viewimg', $data['view_images']);
						$this->optionset($user_row, 'viewflash', $data['view_flash']);
						$this->optionset($user_row, 'viewsmilies', $data['view_smilies']);
						$this->optionset($user_row, 'viewsigs', $data['view_sigs']);
						$this->optionset($user_row, 'viewavatars', $data['view_avatars']);
						$this->optionset($user_row, 'viewcensors', $data['view_wordcensor']);
						$this->optionset($user_row, 'bbcode', $data['bbcode']);
						$this->optionset($user_row, 'html', $data['html']);
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

							'user_dst'				=> $data['dst'],
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

						$sql = 'UPDATE ' . USERS_TABLE . '
							SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
							WHERE user_id = $user_id";
						$db->sql_query($sql);

						trigger_error($user->lang['USER_PREFS_UPDATED'] . adm_back_link($u_action));
					}

					// Replace "error" strings with their real, localised form
					$error = preg_replace('#^([A-Z_]+)$#e', "(!empty(\$user->lang['\\1'])) ? \$user->lang['\\1'] : '\\1'", $error);
				}

				$notify_method = (isset($data['notifymethod'])) ? $data['notifymethod'] : $user_row['user_notify_type'];
				$dateformat = (isset($data['dateformat'])) ? $data['dateformat'] : $user_row['user_dateformat'];
				$lang = (isset($data['lang'])) ? $data['lang'] : $user_row['user_lang'];
				$style = (isset($data['style'])) ? $data['style'] : $user_row['user_style'];
				$tz = (isset($data['tz'])) ? $data['tz'] : $user_row['user_timezone'];

				$dateformat_options = '';

				foreach ($user->lang['dateformats'] as $format => $null)
				{
					$dateformat_options .= '<option value="' . $format . '"' . (($format == $dateformat) ? ' selected="selected"' : '') . '>';
					$dateformat_options .= $user->format_date(time(), $format, true) . ((strpos($format, '|') !== false) ? ' [' . $user->lang['RELATIVE_DAYS'] . ']' : '');
					$dateformat_options .= '</option>';
				}

				$s_custom = false;

				$dateformat_options .= '<option value="custom"';
				if (!in_array($dateformat, array_keys($user->lang['dateformats'])))
				{
					$dateformat_options .= ' selected="selected"';
					$s_custom = true;
				}
				$dateformat_options .= '>' . $user->lang['CUSTOM_DATEFORMAT'] . '</option>';

				$topic_sk = (isset($data['topic_sk'])) ? $data['topic_sk'] : (($user_row['user_topic_sortby_type']) ? $user_row['user_topic_sortby_type'] : 't');
				$post_sk = (isset($data['post_sk'])) ? $data['post_sk'] : (($user_row['user_post_sortby_type']) ? $user_row['user_post_sortby_type'] : 't');

				$topic_sd = (isset($data['topic_sd'])) ? $data['topic_sd'] : (($user_row['user_topic_sortby_dir']) ? $user_row['user_topic_sortby_dir'] : 'd');
				$post_sd = (isset($data['post_sd'])) ? $data['post_sd'] : (($user_row['user_post_sortby_dir']) ? $user_row['user_post_sortby_dir'] : 'd');
				
				$topic_st = (isset($data['topic_st'])) ? $data['topic_st'] : (($user_row['user_topic_show_days']) ? $user_row['user_topic_show_days'] : 0);
				$post_st = (isset($data['post_st'])) ? $data['post_st'] : (($user_row['user_post_show_days']) ? $user_row['user_post_show_days'] : 0);

				$sort_dir_text = array('a' => $user->lang['ASCENDING'], 'd' => $user->lang['DESCENDING']);

				// Topic ordering options
				$limit_topic_days = array(0 => $user->lang['ALL_TOPICS'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 364 => $user->lang['1_YEAR']);
				$sort_by_topic_text = array('a' => $user->lang['AUTHOR'], 't' => $user->lang['POST_TIME'], 'r' => $user->lang['REPLIES'], 's' => $user->lang['SUBJECT'], 'v' => $user->lang['VIEWS']);

				// Post ordering options
				$limit_post_days = array(0 => $user->lang['ALL_POSTS'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 364 => $user->lang['1_YEAR']);
				$sort_by_post_text = array('a' => $user->lang['AUTHOR'], 't' => $user->lang['POST_TIME'], 's' => $user->lang['SUBJECT']);

				$_options = array('topic', 'post');
				foreach ($_options as $sort_option)
				{
					${'s_limit_' . $sort_option . '_days'} = '<select name="' . $sort_option . '_st">';
					foreach (${'limit_' . $sort_option . '_days'} as $day => $text)
					{
						$selected = (${$sort_option . '_st'} == $day) ? ' selected="selected"' : '';
						${'s_limit_' . $sort_option . '_days'} .= '<option value="' . $day . '"' . $selected . '>' . $text . '</option>';
					}
					${'s_limit_' . $sort_option . '_days'} .= '</select>';

					${'s_sort_' . $sort_option . '_key'} = '<select name="' . $sort_option . '_sk">';
					foreach (${'sort_by_' . $sort_option . '_text'} as $key => $text)
					{
						$selected = (${$sort_option . '_sk'} == $key) ? ' selected="selected"' : '';
						${'s_sort_' . $sort_option . '_key'} .= '<option value="' . $key . '"' . $selected . '>' . $text . '</option>';
					}
					${'s_sort_' . $sort_option . '_key'} .= '</select>';

					${'s_sort_' . $sort_option . '_dir'} = '<select name="' . $sort_option . '_sd">';
					foreach ($sort_dir_text as $key => $value)
					{
						$selected = (${$sort_option . '_sd'} == $key) ? ' selected="selected"' : '';
						${'s_sort_' . $sort_option . '_dir'} .= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
					}
					${'s_sort_' . $sort_option . '_dir'} .= '</select>';
				}

				$template->assign_vars(array(
					'S_PREFS'			=> true,
					'S_JABBER_DISABLED'	=> ($config['jab_enable'] && $user->data['user_jabber'] && @extension_loaded('xml')) ? false : true,
					
					'VIEW_EMAIL'		=> (isset($data['viewemail'])) ? $data['viewemail'] : $user_row['user_allow_viewemail'],
					'MASS_EMAIL'		=> (isset($data['massemail'])) ? $data['massemail'] : $user_row['user_allow_massemail'],
					'ALLOW_PM'			=> (isset($data['allowpm'])) ? $data['allowpm'] : $user_row['user_allow_pm'],
					'HIDE_ONLINE'		=> (isset($data['hideonline'])) ? $data['hideonline'] : !$user_row['user_allow_viewonline'],
					'NOTIFY_EMAIL'		=> ($notify_method == NOTIFY_EMAIL) ? true : false,
					'NOTIFY_IM'			=> ($notify_method == NOTIFY_IM) ? true : false,
					'NOTIFY_BOTH'		=> ($notify_method == NOTIFY_BOTH) ? true : false,
					'NOTIFY_PM'			=> (isset($data['notifypm'])) ? $data['notifypm'] : $user_row['user_notify_pm'],
					'POPUP_PM'			=> (isset($data['popuppm'])) ? $data['popuppm'] : $this->optionget($user_row, 'popuppm'),
					'REPORT_PM_NOTIFY'	=> (isset($data['report_pm_notify'])) ? $data['report_pm_notify'] : $this->optionget($user_row, 'report_pm_notify'),
					'DST'				=> (isset($data['dst'])) ? $data['dst'] : $user_row['user_dst'],
					'BBCODE'			=> (isset($data['bbcode'])) ? $data['bbcode'] : $this->optionget($user_row, 'bbcode'),
					'HTML'				=> (isset($data['html'])) ? $data['html'] : $this->optionget($user_row, 'html'),
					'SMILIES'			=> (isset($data['smilies'])) ? $data['smilies'] : $this->optionget($user_row, 'smilies'),
					'ATTACH_SIG'		=> (isset($data['sig'])) ? $data['sig'] : $this->optionget($user_row, 'attachsig'),
					'NOTIFY'			=> (isset($data['notify'])) ? $data['notify'] : $user_row['user_notify'],
					'VIEW_IMAGES'		=> (isset($data['view_images'])) ? $data['view_images'] : $this->optionget($user_row, 'viewimg'),
					'VIEW_FLASH'		=> (isset($data['view_flash'])) ? $data['view_flash'] : $this->optionget($user_row, 'viewflash'),
					'VIEW_SMILIES'		=> (isset($data['view_smilies'])) ? $data['view_smilies'] : $this->optionget($user_row, 'viewsmilies'),
					'VIEW_SIGS'			=> (isset($data['view_sigs'])) ? $data['view_sigs'] : $this->optionget($user_row, 'viewsigs'),
					'VIEW_AVATARS'		=> (isset($data['view_avatars'])) ? $data['view_avatars'] : $this->optionget($user_row, 'viewavatars'),
					'VIEW_WORDCENSOR'	=> (isset($data['view_wordcensor'])) ? $data['view_wordcensor'] : $this->optionget($user_row, 'viewcensors'),
					
					'S_TOPIC_SORT_DAYS'		=> $s_limit_topic_days,
					'S_TOPIC_SORT_KEY'		=> $s_sort_topic_key,
					'S_TOPIC_SORT_DIR'		=> $s_sort_topic_dir,
					'S_POST_SORT_DAYS'		=> $s_limit_post_days,
					'S_POST_SORT_KEY'		=> $s_sort_post_key,
					'S_POST_SORT_DIR'		=> $s_sort_post_dir,

					'DATE_FORMAT'			=> $dateformat,
					'S_DATEFORMAT_OPTIONS'	=> $dateformat_options,
					'S_CUSTOM_DATEFORMAT'	=> $s_custom,
					'DEFAULT_DATEFORMAT'	=> $config['default_dateformat'],

					'S_LANG_OPTIONS'	=> language_select($lang),
					'S_STYLE_OPTIONS'	=> style_select($style),
					'S_TZ_OPTIONS'		=> tz_select($tz),
					)
				);


			break;
		}

		// Assign general variables
		$template->assign_vars(array(
			'S_ERROR'			=> (sizeof($error)) ? true : false,
			'ERROR_MSG'			=> (sizeof($error)) ? implode('<br />', $error) : '')
		);
	}

	function optionset(&$user_row, $key, $value, $data = false)
	{
		global $user;

		$var = ($data) ? $data : $user_row['user_options'];

		if ($value && !($var & 1 << $user->keyoptions[$key]))
		{
			$var += 1 << $user->keyoptions[$key];
		}
		else if (!$value && ($var & 1 << $user->keyoptions[$key]))
		{
			$var -= 1 << $user->keyoptions[$key];
		}
		else
		{
			return ($data) ? $var : false;
		}

		if (!$data)
		{
			$user_row['user_options'] = $var;
			return true;
		}
		else
		{
			return $var;
		}
	}

	function optionget(&$user_row, $key, $data = false)
	{
		global $user;

		$var = ($data) ? $data : $user_row['user_options'];
		return ($var & 1 << $user->keyoptions[$key]) ? true : false;
	}
}

/**
* @package module_install
*/
class acp_users_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_users',
			'title'		=> 'ACP_USER_MANAGEMENT',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'overview'		=> array('title' => 'ACP_MANAGE_USERS', 'auth' => 'acl_a_user'),
				'feedback'		=> array('title' => 'ACP_USER_FEEDBACK', 'auth' => 'acl_a_user', 'display' => false),
				'profile'		=> array('title' => 'ACP_USER_PROFILE', 'auth' => 'acl_a_user', 'display' => false),
				'prefs'			=> array('title' => 'ACP_USER_PREFS', 'auth' => 'acl_a_user', 'display' => false),
				'avatar'		=> array('title' => 'ACP_USER_AVATAR', 'auth' => 'acl_a_user', 'display' => false),
				'sig'			=> array('title' => 'ACP_USER_SIG', 'auth' => 'acl_a_user', 'display' => false),
				'groups'		=> array('title' => 'ACP_USER_GROUPS', 'auth' => 'acl_a_user', 'display' => false),
				'perm'			=> array('title' => 'ACP_USER_PERM', 'auth' => 'acl_a_user', 'display' => false),
				'attach'		=> array('title' => 'ACP_USER_ATTACH', 'auth' => 'acl_a_user', 'display' => false),
			),
		);
	}

	function install()
	{
	}

	function uninstall()
	{
	}
}

?>