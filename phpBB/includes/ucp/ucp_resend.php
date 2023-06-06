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
* ucp_resend
* Resending activation emails
*/
class ucp_resend
{
	var $u_action;

	function main($id, $mode)
	{
		global $config, $phpbb_root_path, $phpEx;
		global $db, $user, $auth, $template, $request, $phpbb_container;

		$username	= $request->variable('username', '', true);
		$email		= strtolower($request->variable('email', ''));
		$submit		= (isset($_POST['submit'])) ? true : false;

		add_form_key('ucp_resend');

		if ($submit)
		{
			if (!check_form_key('ucp_resend'))
			{
				trigger_error('FORM_INVALID');
			}

			$sql = 'SELECT user_id, group_id, username, user_email, user_type, user_lang, user_actkey, user_actkey_expiration, user_inactive_reason
				FROM ' . USERS_TABLE . "
				WHERE user_email = '" . $db->sql_escape($email) . "'
					AND username_clean = '" . $db->sql_escape(utf8_clean_string($username)) . "'";
			$result = $db->sql_query($sql);
			$user_row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			if (!$user_row)
			{
				trigger_error('NO_EMAIL_USER');
			}

			if ($user_row['user_type'] == USER_IGNORE)
			{
				trigger_error('NO_USER');
			}

			if (!$user_row['user_actkey'] && $user_row['user_type'] != USER_INACTIVE)
			{
				trigger_error('ACCOUNT_ALREADY_ACTIVATED');
			}

			if (!$user_row['user_actkey'] || ($user_row['user_type'] == USER_INACTIVE && $user_row['user_inactive_reason'] == INACTIVE_MANUAL))
			{
				trigger_error('ACCOUNT_DEACTIVATED');
			}

			// Do not resend activation email if valid one still exists
			if (!empty($user_row['user_actkey']) && (int) $user_row['user_actkey_expiration'] >= time())
			{
				trigger_error('ACTIVATION_ALREADY_SENT');
			}

			// Determine coppa status on group (REGISTERED(_COPPA))
			$sql = 'SELECT group_name, group_type
				FROM ' . GROUPS_TABLE . '
				WHERE group_id = ' . $user_row['group_id'];
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			if (!$row)
			{
				trigger_error('NO_GROUP');
			}

			$coppa = ($row['group_name'] == 'REGISTERED_COPPA' && $row['group_type'] == GROUP_SPECIAL) ? true : false;

			$messenger = $phpbb_container->get('messenger.method_collection');
			$email = $messenger->offsetGet('messenger.method.email');
			$email->set_use_queue(false);

			if ($config['require_activation'] == USER_ACTIVATION_SELF || $coppa)
			{
				$email->template(($coppa) ? 'coppa_resend_inactive' : 'user_resend_inactive', $user_row['user_lang']);
				$email->set_addresses($user_row);

				$email->anti_abuse_headers($config, $user);

				$email->assign_vars([
					'WELCOME_MSG'	=> html_entity_decode(sprintf($user->lang['WELCOME_SUBJECT'], $config['sitename']), ENT_COMPAT),
					'USERNAME'		=> html_entity_decode($user_row['username'], ENT_COMPAT),
					'U_ACTIVATE'	=> generate_board_url() . "/ucp.$phpEx?mode=activate&u={$user_row['user_id']}&k={$user_row['user_actkey']}",
				]);

				if ($coppa)
				{
					$email->assign_vars([
						'FAX_INFO'		=> $config['coppa_fax'],
						'MAIL_INFO'		=> $config['coppa_mail'],
						'EMAIL_ADDRESS'	=> $user_row['user_email'],
					]);
				}

				$email->send();
			}

			if ($config['require_activation'] == USER_ACTIVATION_ADMIN)
			{
				// Grab an array of user_id's with a_user permissions ... these users can activate a user
				$admin_ary = $auth->acl_get_list(false, 'a_user', false);

				$sql = 'SELECT user_id, username, user_email, user_lang, user_jabber, user_notify_type
					FROM ' . USERS_TABLE . '
					WHERE ' . $db->sql_in_set('user_id', $admin_ary[0]['a_user']);
				$result = $db->sql_query($sql);

				$messenger_collection_iterator = $messenger->getIterator();
				while ($row = $db->sql_fetchrow($result))
				{
					while ($messenger_collection_iterator->valid())
					{
						$messenger_method = $messenger_collection_iterator->current();
						$messenger_method->set_use_queue(false);
						if ($messenger_method->get_id() == $row['user_notify_type'] || $row['user_notify_type'] == NOTIFY_BOTH)
						{
							$messenger_method->template('admin_activate', $row['user_lang']);
							$messenger_method->set_addresses($row);
							$messenger_method->anti_abuse_headers($config, $user);
							$messenger_method->assign_vars([
								'USERNAME'			=> html_entity_decode($user_row['username'], ENT_COMPAT),
								'U_USER_DETAILS'	=> generate_board_url() . "/memberlist.$phpEx?mode=viewprofile&u={$user_row['user_id']}",
								'U_ACTIVATE'		=> generate_board_url() . "/ucp.$phpEx?mode=activate&u={$user_row['user_id']}&k={$user_row['user_actkey']}",
							]);

							$messenger_method->send();

							// Save the queue in the messenger method class (has to be called or these messages could be lost)
							$messenger_method->save_queue();
						}
						$messenger_collection_iterator->next();
					}
				}
				$db->sql_freeresult($result);
			}

			$this->update_activation_expiration();

			meta_refresh(3, append_sid("{$phpbb_root_path}index.$phpEx"));

			$message = ($config['require_activation'] == USER_ACTIVATION_ADMIN) ? $user->lang['ACTIVATION_EMAIL_SENT_ADMIN'] : $user->lang['ACTIVATION_EMAIL_SENT'];
			$message .= '<br /><br />' . sprintf($user->lang['RETURN_INDEX'], '<a href="' . append_sid("{$phpbb_root_path}index.$phpEx") . '">', '</a>');
			trigger_error($message);
		}

		$template->assign_vars(array(
			'USERNAME'			=> $username,
			'EMAIL'				=> $email,
			'S_PROFILE_ACTION'	=> append_sid($phpbb_root_path . 'ucp.' . $phpEx, 'mode=resend_act'))
		);

		$this->tpl_name = 'ucp_resend';
		$this->page_title = 'UCP_RESEND';
	}

	/**
	 * Update activation expiration to 1 day from now
	 *
	 * @return void
	 */
	protected function update_activation_expiration(): void
	{
		global $db, $user;

		$sql_ary = [
			'user_actkey_expiration'	=> $user::get_token_expiration(),
		];

		$sql = 'UPDATE ' . USERS_TABLE . '
			SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
			WHERE user_id = ' . (int) $user->id();
		$db->sql_query($sql);
	}
}
