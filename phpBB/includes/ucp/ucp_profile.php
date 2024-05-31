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
		global $config, $db, $user, $auth, $template, $phpbb_root_path, $phpEx;
		global $request, $phpbb_container, $phpbb_log, $phpbb_dispatcher;

		$user->add_lang('posting');

		$submit		= $request->variable('submit', false, false, \phpbb\request\request_interface::POST);
		$error = $data = array();
		$s_hidden_fields = '';

		switch ($mode)
		{
			case 'reg_details':

				$data = array(
					'username'			=> $request->variable('username', $user->data['username'], true),
					'email'				=> strtolower($request->variable('email', $user->data['user_email'])),
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
							array('string', true, $config['min_pass_chars'], 0),
							array('password')),
						'password_confirm'	=> array('string', true, $config['min_pass_chars'], 0),
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
					/* @var $passwords_manager \phpbb\passwords\manager */
					$passwords_manager = $phpbb_container->get('passwords.manager');

					// Only check the new password against the previous password if there have been no errors
					if (!count($error) && $auth->acl_get('u_chgpasswd') && $data['new_password'] && $passwords_manager->check($data['new_password'], $user->data['user_password']))
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

					if (!count($error))
					{
						$sql_ary = array(
							'username'			=> ($auth->acl_get('u_chgname') && $config['allow_namechange']) ? $data['username'] : $user->data['username'],
							'username_clean'	=> ($auth->acl_get('u_chgname') && $config['allow_namechange']) ? utf8_clean_string($data['username']) : $user->data['username_clean'],
							'user_email'		=> ($auth->acl_get('u_chgemail')) ? $data['email'] : $user->data['user_email'],
							'user_password'		=> ($auth->acl_get('u_chgpasswd') && $data['new_password']) ? $passwords_manager->hash($data['new_password']) : $user->data['user_password'],
						);

						if ($auth->acl_get('u_chgname') && $config['allow_namechange'] && $data['username'] != $user->data['username'])
						{
							$phpbb_log->add('user', $user->data['user_id'], $user->ip, 'LOG_USER_UPDATE_NAME', false, array(
								'reportee_id' => $user->data['user_id'],
								$user->data['username'],
								$data['username']
							));
						}

						if ($auth->acl_get('u_chgpasswd') && $data['new_password'])
						{
							$sql_ary['user_passchg'] = time();

							$user->reset_login_keys();
							$phpbb_log->add('user', $user->data['user_id'], $user->ip, 'LOG_USER_NEW_PASSWORD', false, array(
								'reportee_id' => $user->data['user_id'],
								$user->data['username']
							));
						}

						if ($auth->acl_get('u_chgemail') && $data['email'] != $user->data['user_email'])
						{
							$phpbb_log->add('user', $user->data['user_id'], $user->ip, 'LOG_USER_UPDATE_EMAIL', false, array(
								'reportee_id' => $user->data['user_id'],
								$user->data['username'],
								$user->data['user_email'],
								$data['email']
							));
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
								'USERNAME'		=> html_entity_decode($data['username'], ENT_COMPAT),
								'U_ACTIVATE'	=> "$server_url/ucp.$phpEx?mode=activate&u={$user->data['user_id']}&k=$user_actkey")
							);

							$messenger->send(NOTIFY_EMAIL);

							if ($config['require_activation'] == USER_ACTIVATION_ADMIN)
							{
								$notifications_manager = $phpbb_container->get('notification_manager');
								$notifications_manager->add_notifications('notification.type.admin_activate_user', array(
									'user_id'					=> $user->data['user_id'],
									'user_actkey'				=> $user_actkey,
									'user_actkey_expiration'	=> $user::get_token_expiration(),
									'user_regdate'				=> time(), // Notification time
								));
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

						if (count($sql_ary))
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
					'ERROR'				=> (count($error)) ? implode('<br />', $error) : '',

					'USERNAME'			=> $data['username'],
					'EMAIL'				=> $data['email'],
					'PASSWORD_CONFIRM'	=> $data['password_confirm'],
					'NEW_PASSWORD'		=> $data['new_password'],
					'CUR_PASSWORD'		=> '',

					'L_USERNAME_EXPLAIN'		=> $user->lang($config['allow_name_chars'] . '_EXPLAIN', $user->lang('CHARACTERS_XY', (int) $config['min_name_chars']), $user->lang('CHARACTERS_XY', (int) $config['max_name_chars'])),
					'L_CHANGE_PASSWORD_EXPLAIN'	=> $user->lang($config['pass_complex'] . '_EXPLAIN', $user->lang('CHARACTERS', (int) $config['min_pass_chars'])),

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
					send_status_line(403, 'Forbidden');
					trigger_error('NO_AUTH_PROFILEINFO');
				}

				/* @var $cp \phpbb\profilefields\manager */
				$cp = $phpbb_container->get('profilefields.manager');

				$cp_data = $cp_error = array();

				$data = array(
					'jabber'		=> $request->variable('jabber', $user->data['user_jabber'], true),
				);

				if ($config['allow_birthdays'])
				{
					$data['bday_day'] = $data['bday_month'] = $data['bday_year'] = 0;

					if ($user->data['user_birthday'])
					{
						list($data['bday_day'], $data['bday_month'], $data['bday_year']) = explode('-', $user->data['user_birthday']);
					}

					$data['bday_day'] = $request->variable('bday_day', $data['bday_day']);
					$data['bday_month'] = $request->variable('bday_month', $data['bday_month']);
					$data['bday_year'] = $request->variable('bday_year', $data['bday_year']);
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

					if (count($cp_error))
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

					if (!count($error))
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
					'ERROR'				=> (count($error)) ? implode('<br />', $error) : '',
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
					send_status_line(403, 'Forbidden');
					trigger_error('NO_AUTH_SIGNATURE');
				}

				if (!function_exists('generate_smilies'))
				{
					include($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
				}

				if (!function_exists('display_custom_bbcodes'))
				{
					include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
				}

				$preview	= $request->is_set_post('preview');

				$enable_bbcode	= ($config['allow_sig_bbcode']) ? $user->optionget('sig_bbcode') : false;
				$enable_smilies	= ($config['allow_sig_smilies']) ? $user->optionget('sig_smilies') : false;
				$enable_urls	= ($config['allow_sig_links']) ? $user->optionget('sig_links') : false;

				$bbcode_flags = ($enable_bbcode ? OPTION_FLAG_BBCODE : 0) + ($enable_smilies ? OPTION_FLAG_SMILIES : 0) + ($enable_urls ? OPTION_FLAG_LINKS : 0);

				$decoded_message	= generate_text_for_edit($user->data['user_sig'], $user->data['user_sig_bbcode_uid'], $bbcode_flags);
				$signature			= $request->variable('signature', $decoded_message['text'], true);
				$signature_preview	= '';

				if ($submit || $preview)
				{
					$enable_bbcode	= ($config['allow_sig_bbcode']) ? !$request->variable('disable_bbcode', false) : false;
					$enable_smilies	= ($config['allow_sig_smilies']) ? !$request->variable('disable_smilies', false) : false;
					$enable_urls	= ($config['allow_sig_links']) ? !$request->variable('disable_magic_url', false) : false;

					if (!check_form_key('ucp_sig'))
					{
						$error[] = 'FORM_INVALID';
					}
				}

				/**
				* Modify user signature on editing profile in UCP
				*
				* @event core.ucp_profile_modify_signature
				* @var	bool	enable_bbcode		Whether or not bbcode is enabled
				* @var	bool	enable_smilies		Whether or not smilies are enabled
				* @var	bool	enable_urls			Whether or not urls are enabled
				* @var	string	signature			Users signature text
				* @var	array	error				Any error strings
				* @var	bool	submit				Whether or not the form has been sumitted
				* @var	bool	preview				Whether or not the signature is being previewed
				* @since 3.1.10-RC1
				* @changed 3.2.0-RC2 Removed message parser
				*/
				$vars = array(
					'enable_bbcode',
					'enable_smilies',
					'enable_urls',
					'signature',
					'error',
					'submit',
					'preview',
				);
				extract($phpbb_dispatcher->trigger_event('core.ucp_profile_modify_signature', compact($vars)));

				$bbcode_uid = $bbcode_bitfield = $bbcode_flags = '';
				$warn_msg = generate_text_for_storage(
					$signature,
					$bbcode_uid,
					$bbcode_bitfield,
					$bbcode_flags,
					$enable_bbcode,
					$enable_urls,
					$enable_smilies,
					$config['allow_sig_img'],
					$config['allow_sig_flash'],
					true,
					$config['allow_sig_links'],
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
						$user->optionset('sig_bbcode', $enable_bbcode);
						$user->optionset('sig_smilies', $enable_smilies);
						$user->optionset('sig_links', $enable_urls);

						$sql_ary = array(
							'user_sig'					=> $signature,
							'user_options'				=> $user->data['user_options'],
							'user_sig_bbcode_uid'		=> $bbcode_uid,
							'user_sig_bbcode_bitfield'	=> $bbcode_bitfield
						);

						/**
						* Modify user registration data before submitting it to the database
						*
						* @event core.ucp_profile_modify_signature_sql_ary
						* @var	array	sql_ary		Array with user signature data to submit to the database
						* @since 3.1.10-RC1
						*/
						$vars = array('sql_ary');
						extract($phpbb_dispatcher->trigger_event('core.ucp_profile_modify_signature_sql_ary', compact($vars)));

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

				if ($request->is_set_post('preview'))
				{
					$decoded_message = generate_text_for_edit($signature, $bbcode_uid, $bbcode_flags);
				}

				/** @var \phpbb\controller\helper $controller_helper */
				$controller_helper = $phpbb_container->get('controller.helper');

				$template->assign_vars(array(
					'ERROR'				=> (count($error)) ? implode('<br />', $error) : '',
					'SIGNATURE'			=> $decoded_message['text'],
					'SIGNATURE_PREVIEW'	=> $signature_preview,

					'S_BBCODE_CHECKED' 		=> (!$enable_bbcode) ? ' checked="checked"' : '',
					'S_SMILIES_CHECKED' 	=> (!$enable_smilies) ? ' checked="checked"' : '',
					'S_MAGIC_URL_CHECKED' 	=> (!$enable_urls) ? ' checked="checked"' : '',

					'BBCODE_STATUS'			=> $user->lang(($config['allow_sig_bbcode'] ? 'BBCODE_IS_ON' : 'BBCODE_IS_OFF'), '<a href="' . $controller_helper->route('phpbb_help_bbcode_controller') . '">', '</a>'),
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

				add_form_key('ucp_sig');

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
					/* @var $phpbb_avatar_manager \phpbb\avatar\manager */
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

									/**
									* Trigger events on successfull avatar change
									*
									* @event core.ucp_profile_avatar_sql
									* @var	array	result	Array with data to be stored in DB
									* @since 3.1.11-RC1
									*/
									$vars = array('result');
									extract($phpbb_dispatcher->trigger_event('core.ucp_profile_avatar_sql', compact($vars)));

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

					$template->assign_vars(array(
						'AVATAR_MIN_WIDTH'	=> $config['avatar_min_width'],
						'AVATAR_MAX_WIDTH'	=> $config['avatar_max_width'],
						'AVATAR_MIN_HEIGHT'	=> $config['avatar_min_height'],
						'AVATAR_MAX_HEIGHT'	=> $config['avatar_max_height'],
					));

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
					'ERROR'			=> (count($error)) ? implode('<br />', $error) : '',
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
					$keys = $request->variable('keys', array(''));

					if (!check_form_key('ucp_autologin_keys'))
					{
						$error[] = 'FORM_INVALID';
					}

					if (!count($error))
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

				$sql_ary = [
					'SELECT'	=> 'sk.key_id, sk.last_ip, sk.last_login',
					'FROM'		=> [SESSIONS_KEYS_TABLE	=> 'sk'],
					'WHERE'		=> 'sk.user_id = ' . (int) $user->data['user_id'],
					'ORDER_BY'	=> 'sk.last_login ASC',
				];

				/**
				 * Event allows changing SQL query for autologin keys
				 *
				 * @event core.ucp_profile_autologin_keys_sql
				 * @var	array	sql_ary	Array with autologin keys SQL query
				 * @since 3.3.2-RC1
				 */
				$vars = ['sql_ary'];
				extract($phpbb_dispatcher->trigger_event('core.ucp_profile_autologin_keys_sql', compact($vars)));

				$result = $db->sql_query($db->sql_build_query('SELECT', $sql_ary));
				$sessions = (array) $db->sql_fetchrowset($result);
				$db->sql_freeresult($result);

				$template_vars = [];
				foreach ($sessions as $row)
				{
					$key = substr($row['key_id'], 0, 8);
					$template_vars[$key] = [
						'KEY' => $key,
						'IP' => $row['last_ip'],
						'LOGIN_TIME' => $user->format_date($row['last_login']),
					];
				}

				/**
				 * Event allows changing template variables
				 *
				 * @event core.ucp_profile_autologin_keys_template_vars
				 * @var	array	sessions		Array with session keys data
				 * @var	array	template_vars	Array with template variables
				 * @since 3.3.2-RC1
				 */
				$vars = ['sessions', 'template_vars'];
				extract($phpbb_dispatcher->trigger_event('core.ucp_profile_autologin_keys_template_vars', compact($vars)));

				$template->assign_block_vars_array('sessions', $template_vars);

			break;
		}

		$template->assign_vars(array(
			'ERROR'		=> (count($error)) ? implode('<br />', $error) : '',

			'L_TITLE'	=> $user->lang['UCP_PROFILE_' . strtoupper($mode)],

			'S_HIDDEN_FIELDS'	=> $s_hidden_fields,
			'S_UCP_ACTION'		=> $this->u_action)
		);

		// Set desired template
		$this->tpl_name = 'ucp_profile_' . $mode;
		$this->page_title = 'UCP_PROFILE_' . strtoupper($mode);
	}
}
