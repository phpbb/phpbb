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

/**
 * Changing profile settings
 *
 * @todo what about pertaining user_sig_options?
 */
class profile
{
	var $u_action;

	public function main($id, $mode)
	{

		$this->language->add_lang('posting');

		$submit		= $this->request->variable('submit', false, false, \phpbb\request\request_interface::POST);
		$error = $data = [];
		$s_hidden_fields = '';

		switch ($mode)
		{
			case 'reg_details':

				$data = [
					'username'			=> $this->request->variable('username', $this->user->data['username'], true),
					'email'				=> strtolower($this->request->variable('email', $this->user->data['user_email'])),
					'new_password'		=> $this->request->variable('new_password', '', true),
					'cur_password'		=> $this->request->variable('cur_password', '', true),
					'password_confirm'	=> $this->request->variable('password_confirm', '', true),
				];

				/**
				 * Modify user registration data on editing account settings in UCP
				 *
				 * @event core.ucp_profile_reg_details_data
				 * @var array	data		Array with current or updated user registration data
				 * @var bool	submit		Flag indicating if submit button has been pressed
				 * @since 3.1.4-RC1
				 */
				$vars = ['data', 'submit'];
				extract($this->dispatcher->trigger_event('core.ucp_profile_reg_details_data', compact($vars)));

				add_form_key('ucp_reg_details');

				if ($submit)
				{
					// Do not check cur_password, it is the old one.
					$check_ary = [
						'new_password'		=> [
							['string', true, $this->config['min_pass_chars'], 0],
							['password']],
						'password_confirm'	=> ['string', true, $this->config['min_pass_chars'], 0],
						'email'				=> [
							['string', false, 6, 60],
							['user_email']],
					];

					if ($this->auth->acl_get('u_chgname') && $this->config['allow_namechange'])
					{
						$check_ary['username'] = [
							['string', false, $this->config['min_name_chars'], $this->config['max_name_chars']],
							['username'],
						];
					}

					$error = validate_data($data, $check_ary);

					if ($this->auth->acl_get('u_chgpasswd') && $data['new_password'] && $data['password_confirm'] != $data['new_password'])
					{
						$error[] = ($data['password_confirm']) ? 'NEW_PASSWORD_ERROR' : 'NEW_PASSWORD_CONFIRM_EMPTY';
					}

					// Instantiate passwords manager
					/* @var $passwords_manager \phpbb\passwords\manager */
					$passwords_manager = $phpbb_container->get('passwords.manager');

					// Only check the new password against the previous password if there have been no errors
					if (!count($error) && $this->auth->acl_get('u_chgpasswd') && $data['new_password'] && $this->passwords_manager->check($data['new_password'], $this->user->data['user_password']))
					{
						$error[] = 'SAME_PASSWORD_ERROR';
					}

					if (!$this->passwords_manager->check($data['cur_password'], $this->user->data['user_password']))
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
					 * @var array	data			Array with user profile data
					 * @var bool	submit			Flag indicating if submit button has been pressed
					 * @var array	error			Array of any generated errors
					 * @since 3.1.4-RC1
					 */
					$vars = ['data', 'submit', 'error'];
					extract($this->dispatcher->trigger_event('core.ucp_profile_reg_details_validate', compact($vars)));

					if (!count($error))
					{
						$sql_ary = [
							'username'			=> ($this->auth->acl_get('u_chgname') && $this->config['allow_namechange']) ? $data['username'] : $this->user->data['username'],
							'username_clean'	=> ($this->auth->acl_get('u_chgname') && $this->config['allow_namechange']) ? utf8_clean_string($data['username']) : $this->user->data['username_clean'],
							'user_email'		=> ($this->auth->acl_get('u_chgemail')) ? $data['email'] : $this->user->data['user_email'],
							'user_password'		=> ($this->auth->acl_get('u_chgpasswd') && $data['new_password']) ? $this->passwords_manager->hash($data['new_password']) : $this->user->data['user_password'],
						];

						if ($this->auth->acl_get('u_chgname') && $this->config['allow_namechange'] && $data['username'] != $this->user->data['username'])
						{
							$this->log->add('user', $this->user->data['user_id'], $this->user->ip, 'LOG_USER_UPDATE_NAME', false, [
								'reportee_id' => $this->user->data['user_id'],
								$this->user->data['username'],
								$data['username']
							]);
						}

						if ($this->auth->acl_get('u_chgpasswd') && $data['new_password'] && !$this->passwords_manager->check($data['new_password'], $this->user->data['user_password']))
						{
							$sql_ary['user_passchg'] = time();

							$this->user->reset_login_keys();
							$this->log->add('user', $this->user->data['user_id'], $this->user->ip, 'LOG_USER_NEW_PASSWORD', false, [
								'reportee_id' => $this->user->data['user_id'],
								$this->user->data['username']
							]);
						}

						if ($this->auth->acl_get('u_chgemail') && $data['email'] != $this->user->data['user_email'])
						{
							$this->log->add('user', $this->user->data['user_id'], $this->user->ip, 'LOG_USER_UPDATE_EMAIL', false, [
								'reportee_id' => $this->user->data['user_id'],
								$this->user->data['username'],
								$this->user->data['user_email'],
								$data['email']
							]);
						}

						$message = 'PROFILE_UPDATED';

						if ($this->auth->acl_get('u_chgemail') && $this->config['email_enable'] && $data['email'] != $this->user->data['user_email'] && $this->user->data['user_type'] != USER_FOUNDER && ($this->config['require_activation'] == USER_ACTIVATION_SELF || $this->config['require_activation'] == USER_ACTIVATION_ADMIN))
						{
							$message = ($this->config['require_activation'] == USER_ACTIVATION_SELF) ? 'ACCOUNT_EMAIL_CHANGED' : 'ACCOUNT_EMAIL_CHANGED_ADMIN';

							include_once($this->root_path . 'includes/functions_messenger.' . $this->php_ext);

							$server_url = generate_board_url();

							$user_actkey = gen_rand_string(mt_rand(6, 10));

							$messenger = new messenger(false);

							$template_file = ($this->config['require_activation'] == USER_ACTIVATION_ADMIN) ? 'user_activate_inactive' : 'user_activate';
							$messenger->template($template_file, $this->user->data['user_lang']);

							$messenger->to($data['email'], $data['username']);

							$messenger->anti_abuse_headers($config, $user);

							$messenger->assign_vars([
								'USERNAME'		=> htmlspecialchars_decode($data['username']),
								'U_ACTIVATE'	=> "$server_url/ucp.$this->php_ext?mode=activate&u={$this->user->data['user_id']}&k=$user_actkey"]
							);

							$messenger->send(NOTIFY_EMAIL);

							if ($this->config['require_activation'] == USER_ACTIVATION_ADMIN)
							{
								$notification_manager = $phpbb_container->get('notification_manager');
								$notification_manager->add_notifications('notification.type.admin_activate_user', [
									'user_id'		=> $this->user->data['user_id'],
									'user_actkey'	=> $user_actkey,
									'user_regdate'	=> time(), // Notification time
								]);
							}

							user_active_flip('deactivate', $this->user->data['user_id'], INACTIVE_PROFILE);

							// Because we want the profile to be reactivated we set user_newpasswd to empty (else the reactivation will fail)
							$sql_ary['user_actkey'] = $user_actkey;
							$sql_ary['user_newpasswd'] = '';
						}

						/**
						 * Modify user registration data before submitting it to the database
						 *
						 * @event core.ucp_profile_reg_details_sql_ary
						 * @var array	data		Array with current or updated user registration data
						 * @var array	sql_ary		Array with user registration data to submit to the database
						 * @since 3.1.4-RC1
						 */
						$vars = ['data', 'sql_ary'];
						extract($this->dispatcher->trigger_event('core.ucp_profile_reg_details_sql_ary', compact($vars)));

						if (count($sql_ary))
						{
							$sql = 'UPDATE ' . $this->tables['users'] . '
								SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
								WHERE user_id = ' . $this->user->data['user_id'];
							$this->db->sql_query($sql);
						}

						// Need to update config, forum, topic, posting, messages, etc.
						if ($data['username'] != $this->user->data['username'] && $this->auth->acl_get('u_chgname') && $this->config['allow_namechange'])
						{
							user_update_name($this->user->data['username'], $data['username']);
						}

						// Now, we can remove the user completely (kill the session) - NOT BEFORE!!!
						if (!empty($sql_ary['user_actkey']))
						{
							meta_refresh(5, append_sid($this->root_path . 'index.' . $this->php_ext));
							$message = $this->language->lang($message) . '<br /><br />' . sprintf($this->language->lang('RETURN_INDEX'), '<a href="' . append_sid($this->root_path . 'index.' . $this->php_ext) . '">', '</a>');

							// Because the user gets deactivated we log him out too, killing his session
							$this->user->session_kill();
						}
						else
						{
							meta_refresh(3, $this->u_action);
							$message = $this->language->lang($message) . '<br /><br />' . sprintf($this->language->lang('RETURN_UCP'), '<a href="' . $this->u_action . '">', '</a>');
						}

						trigger_error($message);
					}

					// Replace "error" strings with their real, localised form
					$error = array_map([$user, 'lang'], $error);
				}

				$this->template->assign_vars([
					'ERROR'				=> (count($error)) ? implode('<br />', $error) : '',

					'USERNAME'			=> $data['username'],
					'EMAIL'				=> $data['email'],
					'PASSWORD_CONFIRM'	=> $data['password_confirm'],
					'NEW_PASSWORD'		=> $data['new_password'],
					'CUR_PASSWORD'		=> '',

					'L_USERNAME_EXPLAIN'		=> $this->language->lang($this->config['allow_name_chars'] . '_EXPLAIN', $this->language->lang('CHARACTERS', (int) $this->config['min_name_chars']), $this->language->lang('CHARACTERS', (int) $this->config['max_name_chars'])),
					'L_CHANGE_PASSWORD_EXPLAIN'	=> $this->language->lang($this->config['pass_complex'] . '_EXPLAIN', $this->language->lang('CHARACTERS', (int) $this->config['min_pass_chars'])),

					'S_FORCE_PASSWORD'	=> ($this->auth->acl_get('u_chgpasswd') && $this->config['chg_passforce'] && $this->user->data['user_passchg'] < time() - ($this->config['chg_passforce'] * 86400)) ? true : false,
					'S_CHANGE_USERNAME' => ($this->config['allow_namechange'] && $this->auth->acl_get('u_chgname')) ? true : false,
					'S_CHANGE_EMAIL'	=> ($this->auth->acl_get('u_chgemail')) ? true : false,
					'S_CHANGE_PASSWORD'	=> ($this->auth->acl_get('u_chgpasswd')) ? true : false]
				);
			break;

			case 'profile_info':
				// Do not display profile information panel if not authed to do so
				if (!$this->auth->acl_get('u_chgprofileinfo'))
				{
					send_status_line(403, 'Forbidden');
					trigger_error('NO_AUTH_PROFILEINFO');
				}

				/* @var $cp \phpbb\profilefields\manager */
				$cp = $phpbb_container->get('profilefields.manager');

				$cp_data = $cp_error = [];

				$data = [
					'jabber'		=> $this->request->variable('jabber', $this->user->data['user_jabber'], true),
				];

				if ($this->config['allow_birthdays'])
				{
					$data['bday_day'] = $data['bday_month'] = $data['bday_year'] = 0;

					if ($this->user->data['user_birthday'])
					{
						list($data['bday_day'], $data['bday_month'], $data['bday_year']) = explode('-', $this->user->data['user_birthday']);
					}

					$data['bday_day'] = $this->request->variable('bday_day', $data['bday_day']);
					$data['bday_month'] = $this->request->variable('bday_month', $data['bday_month']);
					$data['bday_year'] = $this->request->variable('bday_year', $data['bday_year']);
					$data['user_birthday'] = sprintf('%2d-%2d-%4d', $data['bday_day'], $data['bday_month'], $data['bday_year']);
				}

				/**
				 * Modify user data on editing profile in UCP
				 *
				 * @event core.ucp_profile_modify_profile_info
				 * @var array	data		Array with user profile data
				 * @var bool	submit		Flag indicating if submit button has been pressed
				 * @since 3.1.4-RC1
				 */
				$vars = ['data', 'submit'];
				extract($this->dispatcher->trigger_event('core.ucp_profile_modify_profile_info', compact($vars)));

				add_form_key('ucp_profile_info');

				if ($submit)
				{
					$validate_array = [
						'jabber'		=> [
							['string', true, 5, 255],
							['jabber']],
					];

					if ($this->config['allow_birthdays'])
					{
						$validate_array = array_merge($validate_array, [
							'bday_day'		=> ['num', true, 1, 31],
							'bday_month'	=> ['num', true, 1, 12],
							'bday_year'		=> ['num', true, 1901, gmdate('Y', time()) + 50],
							'user_birthday' => ['date', true],
						]);
					}

					$error = validate_data($data, $validate_array);

					// validate custom profile fields
					$cp->submit_cp_field('profile', $this->user->get_iso_lang_id(), $cp_data, $cp_error);

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
					 * @var array	data			Array with user profile data
					 * @var bool	submit			Flag indicating if submit button has been pressed
					 * @var array	error			Array of any generated errors
					 * @since 3.1.4-RC1
					 */
					$vars = ['data', 'submit', 'error'];
					extract($this->dispatcher->trigger_event('core.ucp_profile_validate_profile_info', compact($vars)));

					if (!count($error))
					{
						$data['notify'] = $this->user->data['user_notify_type'];

						if ($data['notify'] == NOTIFY_IM && (!$this->config['jab_enable'] || !$data['jabber'] || !@extension_loaded('xml')))
						{
							// User has not filled in a jabber address (Or one of the modules is disabled or jabber is disabled)
							// Disable notify by Jabber now for this user.
							$data['notify'] = NOTIFY_EMAIL;
						}

						$sql_ary = [
							'user_jabber'	=> $data['jabber'],
							'user_notify_type'	=> $data['notify'],
						];

						if ($this->config['allow_birthdays'])
						{
							$sql_ary['user_birthday'] = $data['user_birthday'];
						}

						/**
						 * Modify profile data in UCP before submitting to the database
						 *
						 * @event core.ucp_profile_info_modify_sql_ary
						 * @var array	cp_data		Array with the user custom profile fields data
						 * @var array	data		Array with user profile data
						 * @var  array	sql_ary		user options data we update
						 * @since 3.1.4-RC1
						 */
						$vars = ['cp_data', 'data', 'sql_ary'];
						extract($this->dispatcher->trigger_event('core.ucp_profile_info_modify_sql_ary', compact($vars)));

						$sql = 'UPDATE ' . $this->tables['users'] . '
							SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
							WHERE user_id = ' . $this->user->data['user_id'];
						$this->db->sql_query($sql);

						// Update Custom Fields
						$cp->update_profile_field_data($this->user->data['user_id'], $cp_data);

						meta_refresh(3, $this->u_action);
						$message = $this->language->lang('PROFILE_UPDATED') . '<br /><br />' . sprintf($this->language->lang('RETURN_UCP'), '<a href="' . $this->u_action . '">', '</a>');
						trigger_error($message);
					}

					// Replace "error" strings with their real, localised form
					$error = array_map([$user, 'lang'], $error);
				}

				if ($this->config['allow_birthdays'])
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

					$this->template->assign_vars([
						'S_BIRTHDAY_DAY_OPTIONS'	=> $s_birthday_day_options,
						'S_BIRTHDAY_MONTH_OPTIONS'	=> $s_birthday_month_options,
						'S_BIRTHDAY_YEAR_OPTIONS'	=> $s_birthday_year_options,
						'S_BIRTHDAYS_ENABLED'		=> true,
					]);
				}

				$this->template->assign_vars([
					'ERROR'				=> (count($error)) ? implode('<br />', $error) : '',
					'S_JABBER_ENABLED'	=> $this->config['jab_enable'],
					'JABBER'			=> $data['jabber'],
				]);

				// Get additional profile fields and assign them to the template block var 'profile_fields'
				$this->user->get_profile_fields($this->user->data['user_id']);

				$cp->generate_profile_fields('profile', $this->user->get_iso_lang_id());

			break;

			case 'signature':

				if (!$this->auth->acl_get('u_sig'))
				{
					send_status_line(403, 'Forbidden');
					trigger_error('NO_AUTH_SIGNATURE');
				}

				if (!function_exists('generate_smilies'))
				{
					include($this->root_path . 'includes/functions_posting.' . $this->php_ext);
				}

				if (!function_exists('display_custom_bbcodes'))
				{
					include($this->root_path . 'includes/functions_display.' . $this->php_ext);
				}

				$preview	= $this->request->is_set_post('preview');

				$enable_bbcode	= ($this->config['allow_sig_bbcode']) ? $this->user->optionget('sig_bbcode') : false;
				$enable_smilies	= ($this->config['allow_sig_smilies']) ? $this->user->optionget('sig_smilies') : false;
				$enable_urls	= ($this->config['allow_sig_links']) ? $this->user->optionget('sig_links') : false;

				$bbcode_flags = ($enable_bbcode ? OPTION_FLAG_BBCODE : 0) + ($enable_smilies ? OPTION_FLAG_SMILIES : 0) + ($enable_urls ? OPTION_FLAG_LINKS : 0);

				$decoded_message	= generate_text_for_edit($this->user->data['user_sig'], $this->user->data['user_sig_bbcode_uid'], $bbcode_flags);
				$signature			= $this->request->variable('signature', $decoded_message['text'], true);
				$signature_preview	= '';

				if ($submit || $preview)
				{
					$enable_bbcode	= ($this->config['allow_sig_bbcode']) ? !$this->request->variable('disable_bbcode', false) : false;
					$enable_smilies	= ($this->config['allow_sig_smilies']) ? !$this->request->variable('disable_smilies', false) : false;
					$enable_urls	= ($this->config['allow_sig_links']) ? !$this->request->variable('disable_magic_url', false) : false;

					if (!check_form_key('ucp_sig'))
					{
						$error[] = 'FORM_INVALID';
					}
				}

				/**
				 * Modify user signature on editing profile in UCP
				 *
				 * @event core.ucp_profile_modify_signature
				 * @var bool	enable_bbcode		Whether or not bbcode is enabled
				 * @var bool	enable_smilies		Whether or not smilies are enabled
				 * @var bool	enable_urls			Whether or not urls are enabled
				 * @var string	signature			Users signature text
				 * @var array	error				Any error strings
				 * @var bool	submit				Whether or not the form has been sumitted
				 * @var bool	preview				Whether or not the signature is being previewed
				 * @since 3.1.10-RC1
				 * @changed 3.2.0-RC2 Removed message parser
				 */
				$vars = [
					'enable_bbcode',
					'enable_smilies',
					'enable_urls',
					'signature',
					'error',
					'submit',
					'preview',
				];
				extract($this->dispatcher->trigger_event('core.ucp_profile_modify_signature', compact($vars)));

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
						$this->user->optionset('sig_bbcode', $enable_bbcode);
						$this->user->optionset('sig_smilies', $enable_smilies);
						$this->user->optionset('sig_links', $enable_urls);

						$sql_ary = [
							'user_sig'					=> $signature,
							'user_options'				=> $this->user->data['user_options'],
							'user_sig_bbcode_uid'		=> $bbcode_uid,
							'user_sig_bbcode_bitfield'	=> $bbcode_bitfield
						];

						/**
						 * Modify user registration data before submitting it to the database
						 *
						 * @event core.ucp_profile_modify_signature_sql_ary
						 * @var array	sql_ary		Array with user signature data to submit to the database
						 * @since 3.1.10-RC1
						 */
						$vars = ['sql_ary'];
						extract($this->dispatcher->trigger_event('core.ucp_profile_modify_signature_sql_ary', compact($vars)));

						$sql = 'UPDATE ' . $this->tables['users'] . '
							SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
							WHERE user_id = ' . $this->user->data['user_id'];
						$this->db->sql_query($sql);

						$message = $this->language->lang('PROFILE_UPDATED') . '<br /><br />' . sprintf($this->language->lang('RETURN_UCP'), '<a href="' . $this->u_action . '">', '</a>');
						trigger_error($message);
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
					'ERROR'				=> (count($error)) ? implode('<br />', $error) : '',
					'SIGNATURE'			=> $decoded_message['text'],
					'SIGNATURE_PREVIEW'	=> $signature_preview,

					'S_BBCODE_CHECKED' 		=> (!$enable_bbcode) ? ' checked="checked"' : '',
					'S_SMILIES_CHECKED' 	=> (!$enable_smilies) ? ' checked="checked"' : '',
					'S_MAGIC_URL_CHECKED' 	=> (!$enable_urls) ? ' checked="checked"' : '',

					'BBCODE_STATUS'			=> $this->language->lang(($this->config['allow_sig_bbcode'] ? 'BBCODE_IS_ON' : 'BBCODE_IS_OFF'), '<a href="' . $this->controller_helper->route('phpbb_help_bbcode_controller') . '">', '</a>'),
					'SMILIES_STATUS'		=> ($this->config['allow_sig_smilies']) ? $this->language->lang('SMILIES_ARE_ON') : $this->language->lang('SMILIES_ARE_OFF'),
					'IMG_STATUS'			=> ($this->config['allow_sig_img']) ? $this->language->lang('IMAGES_ARE_ON') : $this->language->lang('IMAGES_ARE_OFF'),
					'FLASH_STATUS'			=> ($this->config['allow_sig_flash']) ? $this->language->lang('FLASH_IS_ON') : $this->language->lang('FLASH_IS_OFF'),
					'URL_STATUS'			=> ($this->config['allow_sig_links']) ? $this->language->lang('URL_IS_ON') : $this->language->lang('URL_IS_OFF'),
					'MAX_FONT_SIZE'			=> (int) $this->config['max_sig_font_size'],

					'L_SIGNATURE_EXPLAIN'	=> $this->language->lang('SIGNATURE_EXPLAIN', (int) $this->config['max_sig_chars']),

					'S_BBCODE_ALLOWED'		=> $this->config['allow_sig_bbcode'],
					'S_SMILIES_ALLOWED'		=> $this->config['allow_sig_smilies'],
					'S_BBCODE_IMG'			=> ($this->config['allow_sig_img']) ? true : false,
					'S_BBCODE_FLASH'		=> ($this->config['allow_sig_flash']) ? true : false,
					'S_LINKS_ALLOWED'		=> ($this->config['allow_sig_links']) ? true : false]
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

				if ($this->config['allow_avatar'] && $this->auth->acl_get('u_chgavatar'))
				{
					/* @var $phpbb_avatar_manager \phpbb\avatar\manager */
					$phpbb_avatar_manager = $phpbb_container->get('avatar.manager');
					$avatar_drivers = $this->avatar_manager->get_enabled_drivers();

					// This is normalised data, without the user_ prefix
					$avatar_data = \phpbb\avatar\manager::clean_row($this->user->data, 'user');

					if ($submit)
					{
						if (check_form_key('ucp_avatar'))
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
									 * Trigger events on successfull avatar change
									 *
									 * @event core.ucp_profile_avatar_sql
									 * @var array	result	Array with data to be stored in DB
									 * @since 3.1.11-RC1
									 */
									$vars = ['result'];
									extract($this->dispatcher->trigger_event('core.ucp_profile_avatar_sql', compact($vars)));

									$sql = 'UPDATE ' . $this->tables['users'] . '
										SET ' . $this->db->sql_build_array('UPDATE', $result) . '
										WHERE user_id = ' . (int) $this->user->data['user_id'];
									$this->db->sql_query($sql);

									meta_refresh(3, $this->u_action);
									$message = $this->language->lang('PROFILE_UPDATED') . '<br /><br />' . sprintf($this->language->lang('RETURN_UCP'), '<a href="' . $this->u_action . '">', '</a>');
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
					if ($this->request->is_set_post('avatar_delete'))
					{
						if (!confirm_box(true))
						{
							confirm_box(false, $this->language->lang('CONFIRM_AVATAR_DELETE'), build_hidden_fields([
									'avatar_delete'     => true,
									'i'                 => $id,
									'mode'              => $mode])
							);
						}
						else
						{
							$this->avatar_manager->handle_avatar_delete($db, $user, $avatar_data, $this->tables['users'], 'user_');

							meta_refresh(3, $this->u_action);
							$message = $this->language->lang('PROFILE_UPDATED') . '<br /><br />' . sprintf($this->language->lang('RETURN_UCP'), '<a href="' . $this->u_action . '">', '</a>');
							trigger_error($message);
						}
					}

					$selected_driver = $this->avatar_manager->clean_driver_name($this->request->variable('avatar_driver', $this->user->data['user_avatar_type']));

					$this->template->assign_vars([
						'AVATAR_MIN_WIDTH'	=> $this->config['avatar_min_width'],
						'AVATAR_MAX_WIDTH'	=> $this->config['avatar_max_width'],
						'AVATAR_MIN_HEIGHT'	=> $this->config['avatar_min_height'],
						'AVATAR_MAX_HEIGHT'	=> $this->config['avatar_max_height'],
					]);

					foreach ($avatar_drivers as $current_driver)
					{
						$driver = $this->avatar_manager->get_driver($current_driver);

						$avatars_enabled = true;
						$this->template->set_filenames([
							'avatar' => $driver->get_template_name(),
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

					// Replace "error" strings with their real, localised form
					$error = $this->avatar_manager->localize_errors($user, $error);
				}

				$avatar = phpbb_get_user_avatar($this->user->data, 'USER_AVATAR', true);

				$this->template->assign_vars([
					'ERROR'			=> (count($error)) ? implode('<br />', $error) : '',
					'AVATAR'		=> $avatar,

					'S_FORM_ENCTYPE'	=> ' enctype="multipart/form-data"',

					'L_AVATAR_EXPLAIN'	=> phpbb_avatar_explanation_string(),

					'S_AVATARS_ENABLED'		=> ($this->config['allow_avatar'] && $avatars_enabled),
				]);

			break;
		}

		$this->template->assign_vars([
			'ERROR'		=> (count($error)) ? implode('<br />', $error) : '',

			'L_TITLE'	=> $this->language->lang('UCP_PROFILE_' . strtoupper($mode)),

			'S_HIDDEN_FIELDS'	=> $s_hidden_fields,
			'S_UCP_ACTION'		=> $this->u_action]
		);

		// Set desired template
		$this->tpl_name = 'ucp_profile_' . $mode;
		$this->page_title = 'UCP_PROFILE_' . strtoupper($mode);
	}
}
