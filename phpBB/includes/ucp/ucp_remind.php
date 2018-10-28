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
* ucp_remind
* Sending password reminders
*/
class ucp_remind
{
	var $u_action;

	function main($id, $mode)
	{
		global $config, $phpbb_root_path, $phpEx, $request;
		global $db, $user, $template, $phpbb_container, $phpbb_dispatcher;

		if (!$config['allow_password_reset'])
		{
			trigger_error($user->lang('UCP_PASSWORD_RESET_DISABLED', '<a href="mailto:' . htmlspecialchars($config['board_contact']) . '">', '</a>'));
		}

		$username	= $request->variable('username', '', true);
		$email		= strtolower($request->variable('email', ''));
		$submit		= (isset($_POST['submit'])) ? true : false;

		add_form_key('ucp_remind');

		if ($submit)
		{
			if (!check_form_key('ucp_remind'))
			{
				trigger_error('FORM_INVALID');
			}

			if (empty($email))
			{
				trigger_error('NO_EMAIL_USER');
			}

			$sql_array = array(
				'SELECT'	=> 'user_id, username, user_permissions, user_email, user_jabber, user_notify_type, user_type, user_lang, user_inactive_reason',
				'FROM'		=> array(USERS_TABLE => 'u'),
				'WHERE'		=> "user_email_hash = '" . $db->sql_escape(phpbb_email_hash($email)) . "'" .
					(!empty($username) ? " AND username_clean = '" . $db->sql_escape(utf8_clean_string($username)) . "'" : ''),
			);

			/**
			* Change SQL query for fetching user data
			*
			* @event core.ucp_remind_modify_select_sql
			* @var	string	email		User's email from the form
			* @var	string	username	User's username from the form
			* @var	array	sql_array	Fully assembled SQL query with keys SELECT, FROM, WHERE
			* @since 3.1.11-RC1
			*/
			$vars = array(
				'email',
				'username',
				'sql_array',
			);
			extract($phpbb_dispatcher->trigger_event('core.ucp_remind_modify_select_sql', compact($vars)));

			$sql = $db->sql_build_query('SELECT', $sql_array);
			$result = $db->sql_query_limit($sql, 2); // don't waste resources on more rows than we need
			$rowset = $db->sql_fetchrowset($result);

			if (count($rowset) > 1)
			{
				$db->sql_freeresult($result);

				$template->assign_vars(array(
					'USERNAME_REQUIRED'	=> true,
					'EMAIL'				=> $email,
				));
			}
			else
			{
				$message = $user->lang['PASSWORD_UPDATED_IF_EXISTED'] . '<br /><br />' . sprintf($user->lang['RETURN_INDEX'], '<a href="' . append_sid("{$phpbb_root_path}index.$phpEx") . '">', '</a>');

				if (empty($rowset))
				{
					trigger_error($message);
				}

				$user_row = $rowset[0];
				$db->sql_freeresult($result);

				if (!$user_row)
				{
					trigger_error($message);
				}

				if ($user_row['user_type'] == USER_IGNORE || $user_row['user_type'] == USER_INACTIVE)
				{
					trigger_error($message);
				}

				// Check users permissions
				$auth2 = new \phpbb\auth\auth();
				$auth2->acl($user_row);

				if (!$auth2->acl_get('u_chgpasswd'))
				{
					trigger_error($message);
				}

				$server_url = generate_board_url();

				// Make password at least 8 characters long, make it longer if admin wants to.
				// gen_rand_string() however has a limit of 12 or 13.
				$user_password = gen_rand_string_friendly(max(8, mt_rand((int) $config['min_pass_chars'], (int) $config['max_pass_chars'])));

				// For the activation key a random length between 6 and 10 will do.
				$user_actkey = gen_rand_string(mt_rand(6, 10));

				// Instantiate passwords manager
				/* @var $manager \phpbb\passwords\manager */
				$passwords_manager = $phpbb_container->get('passwords.manager');

				$sql = 'UPDATE ' . USERS_TABLE . "
					SET user_newpasswd = '" . $db->sql_escape($passwords_manager->hash($user_password)) . "', user_actkey = '" . $db->sql_escape($user_actkey) . "'
					WHERE user_id = " . $user_row['user_id'];
				$db->sql_query($sql);

				include_once($phpbb_root_path . 'includes/functions_messenger.' . $phpEx);

				$messenger = new messenger(false);

				$messenger->template('user_activate_passwd', $user_row['user_lang']);

				$messenger->set_addresses($user_row);

				$messenger->anti_abuse_headers($config, $user);

				$messenger->assign_vars(array(
					'USERNAME'		=> htmlspecialchars_decode($user_row['username']),
					'PASSWORD'		=> htmlspecialchars_decode($user_password),
					'U_ACTIVATE'	=> "$server_url/ucp.$phpEx?mode=activate&u={$user_row['user_id']}&k=$user_actkey")
				);

				$messenger->send($user_row['user_notify_type']);

				trigger_error($message);
			}
		}

		$template->assign_vars(array(
			'USERNAME'			=> $username,
			'EMAIL'				=> $email,
			'S_PROFILE_ACTION'	=> append_sid($phpbb_root_path . 'ucp.' . $phpEx, 'mode=sendpassword'))
		);

		$this->tpl_name = 'ucp_remind';
		$this->page_title = 'UCP_REMIND';
	}
}
