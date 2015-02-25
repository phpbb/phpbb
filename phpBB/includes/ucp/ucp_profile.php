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
* ucp_profile
* Changing profile settings
*
* @todo what about pertaining user_sig_options?
*/
class ucp_profile
{
	var $u_action;

	function main($id, $mode)
	{
		global $cache, $config, $db, $user, $auth, $template, $phpbb_root_path, $phpEx;
		global $request, $phpbb_container, $phpbb_dispatcher;

		$user->add_lang('posting');

		$preview	= $request->variable('preview', false, false, \phpbb\request\request_interface::POST);
		$submit		= $request->variable('submit', false, false, \phpbb\request\request_interface::POST);
		$delete		= $request->variable('delete', false, false, \phpbb\request\request_interface::POST);
		$error = $data = array();
		$s_hidden_fields = '';

		switch ($mode)
		{
			case 'reg_details':

				$data = array(
					'username'			=> utf8_normalize_nfc(request_var('username', $user->data['username'], true)),
					'email'				=> strtolower(request_var('email', $user->data['user_email'])),
					'new_password'		=> $request->variable('new_password', '', true),
					'cur_password'		=> $request->variable('cur_password', '', true),
					'password_confirm'	=> $request->variable('password_confirm', '', true),
				);

				/**
				* Modify user registration data on editing account settings in UCP
				*
				* @event core.ucp_profile_reg_details_data
				* @var	array	data		Array with current or updated user registration data
				* @var	bool	submit		Flag indicating if submit button has been pressed
				* @since 3.1.4-RC1
				*/
				$vars = array('data', 'submit');
				extract($phpbb_dispatcher->trigger_event('core.ucp_profile_reg_details_data', compact($vars)));

				add_form_key('ucp_reg_details');

				if ($submit)
				{
					// Do not check cur_password, it is the old one.
					$check_ary = array(
						'new_password'		=> array(
							array('string', true, $config['min_pass_chars'], $config['max_pass_chars']),
							array('password')),
						'password_confirm'	=> array('string', true, $config['min_pass_chars'], $config['max_pass_chars']),
						'email'				=> array(
							array('string', false, 6, 60),
							array('user_email')),
					);

					if ($auth->acl_get('u_chgname') && $config['allow_namechange'])
					{
						$check_ary['username'] = array(
							array('string', false, $config['min_name_chars'], $config['max_name_chars']),
							array('username'),
						);
					}

					$error = validate_data($data, $check_ary);

					if ($auth->acl_get('u_chgpasswd') && $data['new_password'] && $data['password_confirm'] != $data['new_password'])
					{
						$error[] = ($data['password_confirm']) ? 'NEW_PASSWORD_ERROR' : 'NEW_PASSWORD_CONFIRM_EMPTY';
					}

					// Instantiate passwords manager
					$passwords_manager = $phpbb_container->get('passwords.manager');

					// Only check the new password against the previous password if there have been no errors
					if (!sizeof($error) && $auth->acl_get('u_chgpasswd') && $data['new_password'] && $passwords_manager->check($data['new_password'], $user->data['user_password']))
					{
						$error[] = 'SAME_PASSWORD_ERROR';
					}

					if (!$passwords_manager->check($data['cur_password'], $user->data['user_password']))
					{
						$error[] = ($data['cur_password']) ? 'CUR_PASSWORD_ERROR' : 'CUR_PASSWORD_EMPTY';
					}

					if (!check_form_key('ucp_reg_details'))
					{
						$error[] = 'FORM_INVALID';
					}

					/**
					* Validate user data on editing registration data in UCP
					*
					* @event core.ucp_profile_reg_details_validate
					* @var	array	data			Array with user profile data
					* @var	bool	submit			Flag indicating if submit button has been pressed
					* @var array	error			Array of any generated errors
					* @since 3.1.4-RC1
					*/
					$vars = array('data', 'submit', 'error');
					extract($phpbb_dispatcher->trigger_event('core.ucp_profile_reg_details_validate', compact($vars)));

					if (!sizeof($error))
					{
						$sql_ary = array(
							'username'			=> ($auth->acl_get('u_chgname') && $config['allow_namechange']) ? $data['username'] : $user->data['username'],
							'username_clean'	=> ($auth->acl_get('u_chgname') && $config['allow_namechange']) ? utf8_clean_string($data['username']) : $user->data['username_clean'],
							'user_email'		=> ($auth->acl_get('u_chgemail')) ? $data['email'] : $user->data['user_email'],
							'user_email_hash'	=> ($auth->acl_get('u_chgemail')) ? phpbb_email_hash($data['email']) : $user->data['user_email_hash'],
							'user_password'		=> ($auth->acl_get('u_chgpasswd') && $data['new_password']) ? $passwords_manager->hash($data['new_password']) : $user->data['user_password'],
							'user_passchg'		=> ($auth->acl_get('u_chgpasswd') && $data['new_password']) ? time() : 0,
						);

						if ($auth->acl_get('u_chgname') && $config['allow_namechange'] && $data['username'] != $user->data['username'])
						{
							add_log('user', $user->data['user_id'], 'LOG_USER_UPDATE_NAME', $user->data['username'], $data['username']);
						}

						if ($auth->acl_get('u_chgpasswd') && $data['new_password'] && !$passwords_manager->check($data['new_password'], $user->data['user_password']))
						{
							$user->reset_login_keys();
							add_log('user', $user->data['user_id'], 'LOG_USER_NEW_PASSWORD', $data['username']);
						}

						if ($auth->acl_get('u_chgemail') && $data['email'] != $user->data['user_email'])
						{
							add_log('user', $user->data['user_id'], 'LOG_USER_UPDATE_EMAIL', $data['username'], $user->data['user_email'], $data['email']);
						}

						$message = 'PROFILE_UPDATED';

						if ($auth->acl_get('u_chgemail') && $config['email_enable'] && $data['email'] != $user->data['user_email'] && $user->data['user_type'] != USER_FOUNDER && ($config['require_activation'] == USER_ACTIVATION_SELF || $config['require_activation'] == USER_ACTIVATION_ADMIN))
						{
							$message = ($config['require_activation'] == USER_ACTIVATION_SELF) ? 'ACCOUNT_EMAIL_CHANGED' : 'ACCOUNT_EMAIL_CHANGED_ADMIN';

							include_once($phpbb_root_path . 'includes/functions_messenger.' . $phpEx);

							$server_url = generate_board_url();

							$user_actkey = gen_rand_string(mt_rand(6, 10));

							$messenger = new messenger(false);

							$template_file = ($config['require_activation'] == USER_ACTIVATION_ADMIN) ? 'user_activate_inactive' : 'user_activate';
							$messenger->template($template_file, $user->data['user_lang']);

							$messenger->to($data['email'], $data['username']);

							$messenger->anti_abuse_headers($config, $user);

							$messenger->assign_vars(array(
								'USERNAME'		=> htmlspecialchars_decode($data['username']),
								'U_ACTIVATE'	=> "$server_url/ucp.$phpEx?mode=activate&u={$user->data['user_id']}&k=$user_actkey")
							);

							$messenger->send(NOTIFY_EMAIL);

							if ($config['require_activation'] == USER_ACTIVATION_ADMIN)
							{
								// Grab an array of user_id's with a_user permissions ... these users can activate a user
								$admin_ary = $auth->acl_get_list(false, 'a_user', false);
								$admin_ary = (!empty($admin_ary[0]['a_user'])) ? $admin_ary[0]['a_user'] : array();

								// Also include founders
								$where_sql = ' WHERE user_type = ' . USER_FOUNDER;

								if (sizeof($admin_ary))
								{
									$where_sql .= ' OR ' . $db->sql_in_set('user_id', $admin_ary);
								}

								$sql = 'SELECT user_id, username, user_email, user_lang, user_jabber, user_notify_type
									FROM ' . USERS_TABLE . ' ' .
									$where_sql;
								$result = $db->sql_query($sql);

								while ($row = $db->sql_fetchrow($result))
								{
									$messenger->template('admin_activate', $row['user_lang']);
									$messenger->set_addresses($row);

									$messenger->assign_vars(array(
										'USERNAME'			=> htmlspecialchars_decode($data['username']),
										'U_USER_DETAILS'	=> "$server_url/memberlist.$phpEx?mode=viewprofile&u={$user->data['user_id']}",
										'U_ACTIVATE'		=> "$server_url/ucp.$phpEx?mode=activate&u={$user->data['user_id']}&k=$user_actkey")
									);

									$messenger->send($row['user_notify_type']);
								}
								$db->sql_freeresult($result);
							}

							user_active_flip('deactivate', $user->data['user_id'], INACTIVE_PROFILE);

							// Because we want the profile to be reactivated we set user_newpasswd to empty (else the reactivation will fail)
							$sql_ary['user_actkey'] = $user_actkey;
							$sql_ary['user_newpasswd'] = '';
						}

						/**
						* Modify user registration data before submitting it to the database
						*
						* @event core.ucp_profile_reg_details_sql_ary
						* @var	array	data		Array with current or updated user registration data
						* @var	array	sql_ary		Array with user registration data to submit to the database
						* @since 3.1.4-RC1
						*/
						$vars = array('data', 'sql_ary');
						extract($phpbb_dispatcher->trigger_event('core.ucp_profile_reg_details_sql_ary', compact($vars)));

						if (sizeof($sql_ary))
						{
							$sql = 'UPDATE ' . USERS_TABLE . '
								SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
								WHERE user_id = ' . $user->data['user_id'];
							$db->sql_query($sql);
						}

						// Need to update config, forum, topic, posting, messages, etc.
						if ($data['username'] != $user->data['username'] && $auth->acl_get('u_chgname') && $config['allow_namechange'])
						{
							user_update_name($user->data['username'], $data['username']);
						}

						// Now, we can remove the user completely (kill the session) - NOT BEFORE!!!
						if (!empty($sql_ary['user_actkey']))
						{
							meta_refresh(5, append_sid($phpbb_root_path . 'index.' . $phpEx));
							$message = $user->lang[$message] . '<br /><br />' . sprintf($user->lang['RETURN_INDEX'], '<a href="' . append_sid($phpbb_root_path . 'index.' . $phpEx) . '">', '</a>');

							// Because the user gets deactivated we log him out too, killing his session
							$user->session_kill();
						}
						else
						{
							meta_refresh(3, $this->u_action);
							$message = $user->lang[$message] . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], '<a href="' . $this->u_action . '">', '</a>');
						}

						trigger_error($message);
					}

					// Replace "error" strings with their real, localised form
					$error = array_map(array($user, 'lang'), $error);
				}

				$template->assign_vars(array(
					'ERROR'				=> (sizeof($error)) ? implode('<br />', $error) : '',

					'USERNAME'			=> $data['username'],
					'EMAIL'				=> $data['email'],
					'PASSWORD_CONFIRM'	=> $data['password_confirm'],
					'NEW_PASSWORD'		=> $data['new_password'],
					'CUR_PASSWORD'		=> '',

					'L_USERNAME_EXPLAIN'		=> $user->lang($config['allow_name_chars'] . '_EXPLAIN', $user->lang('CHARACTERS', (int) $config['min_name_chars']), $user->lang('CHARACTERS', (int) $config['max_name_chars'])),
					'L_CHANGE_PASSWORD_EXPLAIN'	=> $user->lang($config['pass_complex'] . '_EXPLAIN', $user->lang('CHARACTERS', (int) $config['min_pass_chars']), $user->lang('CHARACTERS', (int) $config['max_pass_chars'])),

					'S_FORCE_PASSWORD'	=> ($auth->acl_get('u_chgpasswd') && $config['chg_passforce'] && $user->data['user_passchg'] < time() - ($config['chg_passforce'] * 86400)) ? true : false,
					'S_CHANGE_USERNAME' => ($config['allow_namechange'] && $auth->acl_get('u_chgname')) ? true : false,
					'S_CHANGE_EMAIL'	=> ($auth->acl_get('u_chgemail')) ? true : false,
					'S_CHANGE_PASSWORD'	=> ($auth->acl_get('u_chgpasswd')) ? true : false)
				);
			break;

			case 'profile_info':
				// Do not display profile information panel if not authed to do so
				if (!$auth->acl_get('u_chgprofileinfo'))
				{
					trigger_error('NO_AUTH_PROFILEINFO');
				}

				$cp = $phpbb_container->get('profilefields.manager');

				$cp_data = $cp_error = array();

				$data = array(
					'jabber'		=> utf8_normalize_nfc(request_var('jabber', $user->data['user_jabber'], true)),
				);

				if ($config['allow_birthdays'])
				{
					$data['bday_day'] = $data['bday_month'] = $data['bday_year'] = 0;

					if ($user->data['user_birthday'])
					{
						list($data['bday_day'], $data['bday_month'], $data['bday_year']) = explode('-', $user->data['user_birthday']);
					}

					$data['bday_day'] = request_var('bday_day', $data['bday_day']);
					$data['bday_month'] = request_var('bday_month', $data['bday_month']);
					$data['bday_year'] = request_var('bday_year', $data['bday_year']);
					$data['user_birthday'] = sprintf('%2d-%2d-%4d', $data['bday_day'], $data['bday_month'], $data['bday_year']);
				}

				/**
				* Modify user data on editing profile in UCP
				*
				* @event core.ucp_profile_modify_profile_info
				* @var	array	data		Array with user profile data
				* @var	bool	submit		Flag indicating if submit button has been pressed
				* @since 3.1.4-RC1
				*/
				$vars = array('data', 'submit');
				extract($phpbb_dispatcher->trigger_event('core.ucp_profile_modify_profile_info', compact($vars)));

				add_form_key('ucp_profile_info');

				if ($submit)
				{
					$validate_array = array(
						'jabber'		=> array(
							array('string', true, 5, 255),
							array('jabber')),
					);

					if ($config['allow_birthdays'])
					{
						$validate_array = array_merge($validate_array, array(
							'bday_day'		=> array('num', true, 1, 31),
							'bday_month'	=> array('num', true, 1, 12),
							'bday_year'		=> array('num', true, 1901, gmdate('Y', time()) + 50),
							'user_birthday' => array('date', true),
						));
					}

					$error = validate_data($data, $validate_array);

					// validate custom profile fields
					$cp->submit_cp_field('profile', $user->get_iso_lang_id(), $cp_data, $cp_error);

					if (sizeof($cp_error))
					{
						$error = array_merge($error, $cp_error);
					}

					if (!check_form_key('ucp_profile_info'))
					{
						$error[] = 'FORM_INVALID';
					}

					/**
					* Validate user data on editing profile in UCP
					*
					* @event core.ucp_profile_validate_profile_info
					* @var	array	data			Array with user profile data
					* @var	bool	submit			Flag indicating if submit button has been pressed
					* @var array	error			Array of any generated errors
					* @since 3.1.4-RC1
					*/
					$vars = array('data', 'submit', 'error');
					extract($phpbb_dispatcher->trigger_event('core.ucp_profile_validate_profile_info', compact($vars)));

					if (!sizeof($error))
					{
						$data['notify'] = $user->data['user_notify_type'];

						if ($data['notify'] == NOTIFY_IM && (!$config['jab_enable'] || !$data['jabber'] || !@extension_loaded('xml')))
						{
							// User has not filled in a jabber address (Or one of the modules is disabled or jabber is disabled)
							// Disable notify by Jabber now for this user.
							$data['notify'] = NOTIFY_EMAIL;
						}

						$sql_ary = array(
							'user_jabber'	=> $data['jabber'],
							'user_notify_type'	=> $data['notify'],
						);

						if ($config['allow_birthdays'])
						{
							$sql_ary['user_birthday'] = $data['user_birthday'];
						}

						/**
						* Modify profile data in UCP before submitting to the database
						*
						* @event core.ucp_profile_info_modify_sql_ary
						* @var	array	cp_data		Array with the user custom profile fields data
						* @var	array	data		Array with user profile data
						* @var  array	sql_ary		user options data we update
						* @since 3.1.4-RC1
						*/
						$vars = array('cp_data', 'data', 'sql_ary');
						extract($phpbb_dispatcher->trigger_event('core.ucp_profile_info_modify_sql_ary', compact($vars)));

						$sql = 'UPDATE ' . USERS_TABLE . '
							SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
							WHERE user_id = ' . $user->data['user_id'];
						$db->sql_query($sql);

						// Update Custom Fields
						$cp->update_profile_field_data($user->data['user_id'], $cp_data);

						meta_refresh(3, $this->u_action);
						$message = $user->lang['PROFILE_UPDATED'] . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], '<a href="' . $this->u_action . '">', '</a>');
						trigger_error($message);
					}

					// Replace "error" strings with their real, localised form
					$error = array_map(array($user, 'lang'), $error);
				}

				if ($config['allow_birthdays'])
				{
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
						'S_BIRTHDAY_DAY_OPTIONS'	=> $s_birthday_day_options,
						'S_BIRTHDAY_MONTH_OPTIONS'	=> $s_birthday_month_options,
						'S_BIRTHDAY_YEAR_OPTIONS'	=> $s_birthday_year_options,
						'S_BIRTHDAYS_ENABLED'		=> true,
					));
				}

				$template->assign_vars(array(
					'ERROR'				=> (sizeof($error)) ? implode('<br />', $error) : '',
					'S_JABBER_ENABLED'	=> $config['jab_enable'],
					'JABBER'			=> $data['jabber'],
				));

				// Get additional profile fields and assign them to the template block var 'profile_fields'
				$user->get_profile_fields($user->data['user_id']);

				$cp->generate_profile_fields('profile', $user->get_iso_lang_id());

			break;

			case 'signature':

				if (!$auth->acl_get('u_sig'))
				{
					trigger_error('NO_AUTH_SIGNATURE');
				}

				include($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
				include($phpbb_root_path . 'includes/functions_display.' . $phpEx);

				$enable_bbcode	= ($config['allow_sig_bbcode']) ? (bool) $user->optionget('sig_bbcode') : false;
				$enable_smilies	= ($config['allow_sig_smilies']) ? (bool) $user->optionget('sig_smilies') : false;
				$enable_urls	= ($config['allow_sig_links']) ? (bool) $user->optionget('sig_links') : false;

				$signature		= utf8_normalize_nfc(request_var('signature', (string) $user->data['user_sig'], true));

				add_form_key('ucp_sig');

				if ($submit || $preview)
				{
					include($phpbb_root_path . 'includes/message_parser.' . $phpEx);

					$enable_bbcode	= ($config['allow_sig_bbcode']) ? ((request_var('disable_bbcode', false)) ? false : true) : false;
					$enable_smilies	= ($config['allow_sig_smilies']) ? ((request_var('disable_smilies', false)) ? false : true) : false;
					$enable_urls	= ($config['allow_sig_links']) ? ((request_var('disable_magic_url', false)) ? false : true) : false;

					if (!sizeof($error))
					{
						$message_parser = new parse_message($signature);

						// Allowing Quote BBCode
						$message_parser->parse($enable_bbcode, $enable_urls, $enable_smilies, $config['allow_sig_img'], $config['allow_sig_flash'], true, $config['allow_sig_links'], true, 'sig');

						if (sizeof($message_parser->warn_msg))
						{
							$error[] = implode('<br />', $message_parser->warn_msg);
						}

						if (!check_form_key('ucp_sig'))
						{
							$error[] = 'FORM_INVALID';
						}

						if (!sizeof($error) && $submit)
						{
							$user->optionset('sig_bbcode', $enable_bbcode);
							$user->optionset('sig_smilies', $enable_smilies);
							$user->optionset('sig_links', $enable_urls);

							$sql_ary = array(
								'user_sig'					=> (string) $message_parser->message,
								'user_options'				=> $user->data['user_options'],
								'user_sig_bbcode_uid'		=> (string) $message_parser->bbcode_uid,
								'user_sig_bbcode_bitfield'	=> $message_parser->bbcode_bitfield
							);

							$sql = 'UPDATE ' . USERS_TABLE . '
								SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
								WHERE user_id = ' . $user->data['user_id'];
							$db->sql_query($sql);

							$message = $user->lang['PROFILE_UPDATED'] . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], '<a href="' . $this->u_action . '">', '</a>');
							trigger_error($message);
						}
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

				decode_message($signature, $user->data['user_sig_bbcode_uid']);

				$template->assign_vars(array(
					'ERROR'				=> (sizeof($error)) ? implode('<br />', $error) : '',
					'SIGNATURE'			=> $signature,
					'SIGNATURE_PREVIEW'	=> $signature_preview,

					'S_BBCODE_CHECKED' 		=> (!$enable_bbcode) ? ' checked="checked"' : '',
					'S_SMILIES_CHECKED' 	=> (!$enable_smilies) ? ' checked="checked"' : '',
					'S_MAGIC_URL_CHECKED' 	=> (!$enable_urls) ? ' checked="checked"' : '',

					'BBCODE_STATUS'			=> ($config['allow_sig_bbcode']) ? sprintf($user->lang['BBCODE_IS_ON'], '<a href="' . append_sid("{$phpbb_root_path}faq.$phpEx", 'mode=bbcode') . '">', '</a>') : sprintf($user->lang['BBCODE_IS_OFF'], '<a href="' . append_sid("{$phpbb_root_path}faq.$phpEx", 'mode=bbcode') . '">', '</a>'),
					'SMILIES_STATUS'		=> ($config['allow_sig_smilies']) ? $user->lang['SMILIES_ARE_ON'] : $user->lang['SMILIES_ARE_OFF'],
					'IMG_STATUS'			=> ($config['allow_sig_img']) ? $user->lang['IMAGES_ARE_ON'] : $user->lang['IMAGES_ARE_OFF'],
					'FLASH_STATUS'			=> ($config['allow_sig_flash']) ? $user->lang['FLASH_IS_ON'] : $user->lang['FLASH_IS_OFF'],
					'URL_STATUS'			=> ($config['allow_sig_links']) ? $user->lang['URL_IS_ON'] : $user->lang['URL_IS_OFF'],
					'MAX_FONT_SIZE'			=> (int) $config['max_sig_font_size'],

					'L_SIGNATURE_EXPLAIN'	=> $user->lang('SIGNATURE_EXPLAIN', (int) $config['max_sig_chars']),

					'S_BBCODE_ALLOWED'		=> $config['allow_sig_bbcode'],
					'S_SMILIES_ALLOWED'		=> $config['allow_sig_smilies'],
					'S_BBCODE_IMG'			=> ($config['allow_sig_img']) ? true : false,
					'S_BBCODE_FLASH'		=> ($config['allow_sig_flash']) ? true : false,
					'S_LINKS_ALLOWED'		=> ($config['allow_sig_links']) ? true : false)
				);

				// Build custom bbcodes array
				display_custom_bbcodes();

				// Generate smiley listing
				generate_smilies('inline', 0);

			break;

			case 'avatar':

				add_form_key('ucp_avatar');

				$avatars_enabled = false;

				if ($config['allow_avatar'] && $auth->acl_get('u_chgavatar'))
				{
					$phpbb_avatar_manager = $phpbb_container->get('avatar.manager');
					$avatar_drivers = $phpbb_avatar_manager->get_enabled_drivers();

					// This is normalised data, without the user_ prefix
					$avatar_data = \phpbb\avatar\manager::clean_row($user->data, 'user');

					if ($submit)
					{
						if (check_form_key('ucp_avatar'))
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
										WHERE user_id = ' . (int) $user->data['user_id'];

									$db->sql_query($sql);

									meta_refresh(3, $this->u_action);
									$message = $user->lang['PROFILE_UPDATED'] . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], '<a href="' . $this->u_action . '">', '</a>');
									trigger_error($message);
								}
							}
						}
						else
						{
							$error[] = 'FORM_INVALID';
						}
					}

					// Handle deletion of avatars
					if ($request->is_set_post('avatar_delete'))
					{
						if (!confirm_box(true))
						{
							confirm_box(false, $user->lang('CONFIRM_AVATAR_DELETE'), build_hidden_fields(array(
									'avatar_delete'     => true,
									'i'                 => $id,
									'mode'              => $mode))
							);
						}
						else
						{
							$phpbb_avatar_manager->handle_avatar_delete($db, $user, $avatar_data, USERS_TABLE, 'user_');

							meta_refresh(3, $this->u_action);
							$message = $user->lang['PROFILE_UPDATED'] . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], '<a href="' . $this->u_action . '">', '</a>');
							trigger_error($message);
						}
					}

					$selected_driver = $phpbb_avatar_manager->clean_driver_name($request->variable('avatar_driver', $user->data['user_avatar_type']));

					foreach ($avatar_drivers as $current_driver)
					{
						$driver = $phpbb_avatar_manager->get_driver($current_driver);

						$avatars_enabled = true;
						$template->set_filenames(array(
							'avatar' => $driver->get_template_name(),
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

					// Replace "error" strings with their real, localised form
					$error = $phpbb_avatar_manager->localize_errors($user, $error);
				}

				$avatar = phpbb_get_user_avatar($user->data, 'USER_AVATAR', true);

				$template->assign_vars(array(
					'ERROR'			=> (sizeof($error)) ? implode('<br />', $error) : '',
					'AVATAR'		=> $avatar,

					'S_FORM_ENCTYPE'	=> ' enctype="multipart/form-data"',

					'L_AVATAR_EXPLAIN'	=> phpbb_avatar_explanation_string(),

					'S_AVATARS_ENABLED'		=> ($config['allow_avatar'] && $avatars_enabled),
				));

			break;

			case 'autologin_keys':

				add_form_key('ucp_autologin_keys');

				if ($submit)
				{
					$keys = request_var('keys', array(''));

					if (!check_form_key('ucp_autologin_keys'))
					{
						$error[] = 'FORM_INVALID';
					}

					if (!sizeof($error))
					{
						if (!empty($keys))
						{
							foreach ($keys as $key => $id)
							{
								$keys[$key] = $db->sql_like_expression($id . $db->get_any_char());
							}
							$sql_where = '(key_id ' . implode(' OR key_id ', $keys) . ')';
							$sql = 'DELETE FROM ' . SESSIONS_KEYS_TABLE . '
								WHERE user_id = ' . (int) $user->data['user_id'] . '
								AND ' . $sql_where ;

							$db->sql_query($sql);

							meta_refresh(3, $this->u_action);
							$message = $user->lang['AUTOLOGIN_SESSION_KEYS_DELETED'] . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], '<a href="' . $this->u_action . '">', '</a>');
							trigger_error($message);
						}
					}

					// Replace "error" strings with their real, localised form
					$error = array_map(array($user, 'lang'), $error);
				}

				$sql = 'SELECT key_id, last_ip, last_login
					FROM ' . SESSIONS_KEYS_TABLE . '
					WHERE user_id = ' . (int) $user->data['user_id'] . '
					ORDER BY last_login ASC';

				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					$template->assign_block_vars('sessions', array(
						'KEY' => substr($row['key_id'], 0, 8),
						'IP' => $row['last_ip'],
						'LOGIN_TIME' => $user->format_date($row['last_login']),
					));
				}

				$db->sql_freeresult($result);

			break;
		}

		$template->assign_vars(array(
			'ERROR'		=> (sizeof($error)) ? implode('<br />', $error) : '',

			'L_TITLE'	=> $user->lang['UCP_PROFILE_' . strtoupper($mode)],

			'S_HIDDEN_FIELDS'	=> $s_hidden_fields,
			'S_UCP_ACTION'		=> $this->u_action)
		);

		// Set desired template
		$this->tpl_name = 'ucp_profile_' . $mode;
		$this->page_title = 'UCP_PROFILE_' . strtoupper($mode);
	}
}
