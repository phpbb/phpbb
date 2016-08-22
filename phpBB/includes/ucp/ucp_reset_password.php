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
* ucp_reset_password
* Sending password reset links
*/
class ucp_reset_password
{
	var $u_action;

	function main($id, $mode)
	{
		global $config, $phpbb_root_path, $phpEx, $request;
		global $db, $user, $template, $phpbb_container, $phpbb_log;

		if (!$config['allow_password_reset'])
		{
			trigger_error($user->lang('UCP_PASSWORD_RESET_DISABLED', '<a href="mailto:' . htmlspecialchars($config['board_contact']) . '">', '</a>'));
		}

		$submit = $request->is_set_post('submit');
		// Variables for sendpassword
		$username = $request->variable('username', '', true);
		$email = strtolower($request->variable('email', ''));
		// Variables for setpassword
		$key = $request->variable('k', '');
		$user_id = $request->variable('u', 0);

		$error = [];

		add_form_key('ucp_reset_password');

		if ($mode === 'setpassword' || ($mode === 'sendpassword' && $submit))
		{
			$where = 'WHERE ' . (($mode === 'setpassword') ? 'user_id = ' . (int) $user_id :
				"user_email_hash = '" . $db->sql_escape(phpbb_email_hash($email)) . "'
					AND username_clean = '" . $db->sql_escape(utf8_clean_string($username)) . "'");

			$sql = 'SELECT user_id, username, user_permissions, user_actkey, user_email, user_jabber, user_notify_type, user_type, user_lang, user_inactive_reason
						FROM ' . USERS_TABLE . '
						' . $where;
			$result = $db->sql_query($sql);
			$user_row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			if (empty($user_row))
			{
				$message = ($mode === 'setpassword') ? 'NO_USER' : 'NO_EMAIL_USER';
				trigger_error($message);
			}
			else if ($user_row['user_type'] === USER_FOUNDER)
			{
				trigger_error('NO_USER');
			}
			else if ($user_row['user_type'] == USER_INACTIVE)
			{
				if ($user_row['user_inactive_reason'] == INACTIVE_MANUAL)
				{
					trigger_error('ACCOUNT_DEACTIVATED');
				}
				else
				{
					trigger_error('ACCOUNT_NOT_ACTIVATED');
				}
			}
			else if (empty($key) && $mode === 'setpassword')
			{
				trigger_error('NO_ACTIVATION_KEY');
			}

			// Check users permissions
			$auth2 = new \phpbb\auth\auth();
			$auth2->acl($user_row);

			if (!$auth2->acl_get('u_chgpasswd'))
			{
				trigger_error('NO_AUTH_PASSWORD_REMINDER');
			}

			if ($submit && !check_form_key('ucp_reset_password'))
			{
				$error[] = 'INVALID_FORM';
			}

			if ($mode === 'sendpassword' && empty($error))
			{
				$server_url = generate_board_url();

				// For the activation key a random length between 6 and 10 will do.
				$user_actkey = gen_rand_string(mt_rand(6, 10));

				$sql = 'UPDATE ' . USERS_TABLE . "
						SET user_actkey = '" . $db->sql_escape($user_actkey) . "'
						WHERE user_id = " . $user_row['user_id'];
				$db->sql_query($sql);

				include_once($phpbb_root_path . 'includes/functions_messenger.' . $phpEx);

				$messenger = new messenger(false);

				$messenger->template('user_reset_password', $user_row['user_lang']);

				$messenger->set_addresses($user_row);

				$messenger->anti_abuse_headers($config, $user);

				$messenger->assign_vars([
					'USERNAME'			=> htmlspecialchars_decode($user_row['username']),
					'U_PASSWORD_RESET'	=> "$server_url/ucp.$phpEx?mode=setpassword&u={$user_row['user_id']}&k=$user_actkey",
				]);

				$messenger->send($user_row['user_notify_type']);

				meta_refresh(3, append_sid("{$phpbb_root_path}index.$phpEx"));

				$message = $user->lang['PASSWORD_RESET_LINK_SENT'] . '<br /><br />' . sprintf($user->lang['RETURN_INDEX'], '<a href="' . append_sid("{$phpbb_root_path}index.$phpEx") . '">', '</a>');
				trigger_error($message);
			}
			else if ($mode === 'setpassword' && $submit)
			{
				if (strcmp($key, $user_row['user_actkey']) !== 0)
				{
					trigger_error('WRONG_ACTIVATION');
				}

				$data = [
					'new_password'		=> $request->untrimmed_variable('new_password', '', true),
					'password_confirm'	=> $request->untrimmed_variable('new_password_confirm', '', true),
				];

				$check_data = [
					'new_password'		=> [
						['string', false, $config['min_pass_chars'], $config['max_pass_chars']],
						['password'],
					],
					'password_confirm'	=> ['string', true, $config['min_pass_chars'], $config['max_pass_chars']],
				];

				$error = array_merge($error, validate_data($data, $check_data));

				if (strcmp($data['new_password'], $data['password_confirm']) !== 0)
				{
					$error[] = ($data['password_confirm']) ? 'NEW_PASSWORD_ERROR' : 'NEW_PASSWORD_CONFIRM_EMPTY';
				}

				if (empty($error))
				{
					// Instantiate passwords manager
					/* @var $passwords_manager \phpbb\passwords\manager */
					$passwords_manager = $phpbb_container->get('passwords.manager');

					$sql_ary = [
						'user_password'			=> $passwords_manager->hash($data['new_password']),
						'user_actkey'			=> '',
						'user_login_attempts'	=> 0,
					];

					$sql = 'UPDATE ' . USERS_TABLE . '
							SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
							WHERE user_id = ' . (int) $user_row['user_id'];
					$db->sql_query($sql);

					$phpbb_log->add('user', $user->data['user_id'], $user->ip, 'LOG_USER_NEW_PASSWORD', false, [
						'reportee_id' => $user_row['user_id'],
						$user_row['username']
					]);

					meta_refresh(3, append_sid("{$phpbb_root_path}index.$phpEx"));
					trigger_error($user->lang('PASSWORD_RESET'));
				}
			}

		}

		$s_hidden_fields = build_hidden_fields([
			'u'	=> $user_id,
			'k'	=> $key,
		]);

		$template->assign_vars([
			'ERROR'		=> !empty($error) ? implode('<br />', array_map([$user, 'lang'], $error)) : '',
			'MODE'		=> strtolower($mode),

			'USERNAME'	=> $username,
			'EMAIL'		=> $email,

			'S_HIDDEN_FIELDS'	=> $s_hidden_fields,
			'S_PROFILE_ACTION'	=> append_sid($phpbb_root_path . 'ucp.' . $phpEx, 'mode=' . $mode),
		]);

		$this->tpl_name = 'ucp_reset_password';
		$this->page_title = 'UCP_RESET_PASSWORD';
	}
}
