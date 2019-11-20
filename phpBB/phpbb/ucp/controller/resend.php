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
 * Resending activation emails
 */
class resend
{
	var $u_action;

	public function main($id, $mode)
	{

		$username	= $this->request->variable('username', '', true);
		$email		= strtolower($this->request->variable('email', ''));
		$submit		= ($this->request->is_set_post('submit')) ? true : false;

		add_form_key('ucp_resend');

		if ($submit)
		{
			if (!check_form_key('ucp_resend'))
			{
				trigger_error('FORM_INVALID');
			}

			$sql = 'SELECT user_id, group_id, username, user_email, user_type, user_lang, user_actkey, user_inactive_reason
				FROM ' . $this->tables['users'] . "
				WHERE user_email = '" . $db->sql_escape($email) . "'
					AND username_clean = '" . $this->db->sql_escape(utf8_clean_string($username)) . "'";
			$result = $this->db->sql_query($sql);
			$user_row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

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

			// Determine coppa status on group (REGISTERED(_COPPA))
			$sql = 'SELECT group_name, group_type
				FROM ' . $this->tables['groups'] . '
				WHERE group_id = ' . $user_row['group_id'];
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if (!$row)
			{
				trigger_error('NO_GROUP');
			}

			$coppa = ($row['group_name'] == 'REGISTERED_COPPA' && $row['group_type'] == GROUP_SPECIAL) ? true : false;

			include_once($this->root_path . 'includes/functions_messenger.' . $this->php_ext);
			$messenger = new messenger(false);

			if ($this->config['require_activation'] == USER_ACTIVATION_SELF || $coppa)
			{
				$messenger->template(($coppa) ? 'coppa_resend_inactive' : 'user_resend_inactive', $user_row['user_lang']);
				$messenger->set_addresses($user_row);

				$messenger->anti_abuse_headers($config, $user);

				$messenger->assign_vars(array(
					'WELCOME_MSG'	=> htmlspecialchars_decode(sprintf($this->language->lang('WELCOME_SUBJECT'), $this->config['sitename'])),
					'USERNAME'		=> htmlspecialchars_decode($user_row['username']),
					'U_ACTIVATE'	=> generate_board_url() . "/ucp.$this->php_ext?mode=activate&u={$user_row['user_id']}&k={$user_row['user_actkey']}")
				);

				if ($coppa)
				{
					$messenger->assign_vars(array(
						'FAX_INFO'		=> $this->config['coppa_fax'],
						'MAIL_INFO'		=> $this->config['coppa_mail'],
						'EMAIL_ADDRESS'	=> $user_row['user_email'])
					);
				}

				$messenger->send(NOTIFY_EMAIL);
			}

			if ($this->config['require_activation'] == USER_ACTIVATION_ADMIN)
			{
				// Grab an array of user_id's with a_user permissions ... these users can activate a user
				$admin_ary = $this->auth->acl_get_list(false, 'a_user', false);

				$sql = 'SELECT user_id, username, user_email, user_lang, user_jabber, user_notify_type
					FROM ' . $this->tables['users'] . '
					WHERE ' . $this->db->sql_in_set('user_id', $admin_ary[0]['a_user']);
				$result = $this->db->sql_query($sql);

				while ($row = $this->db->sql_fetchrow($result))
				{
					$messenger->template('admin_activate', $row['user_lang']);
					$messenger->set_addresses($row);

					$messenger->anti_abuse_headers($config, $user);

					$messenger->assign_vars(array(
						'USERNAME'			=> htmlspecialchars_decode($user_row['username']),
						'U_USER_DETAILS'	=> generate_board_url() . "/memberlist.$this->php_ext?mode=viewprofile&u={$user_row['user_id']}",
						'U_ACTIVATE'		=> generate_board_url() . "/ucp.$this->php_ext?mode=activate&u={$user_row['user_id']}&k={$user_row['user_actkey']}")
					);

					$messenger->send($row['user_notify_type']);
				}
				$this->db->sql_freeresult($result);
			}

			meta_refresh(3, append_sid("{$this->root_path}index.$this->php_ext"));

			$message = ($this->config['require_activation'] == USER_ACTIVATION_ADMIN) ? $this->language->lang('ACTIVATION_EMAIL_SENT_ADMIN') : $this->language->lang('ACTIVATION_EMAIL_SENT');
			$message .= '<br /><br />' . sprintf($this->language->lang('RETURN_INDEX'), '<a href="' . append_sid("{$this->root_path}index.$this->php_ext") . '">', '</a>');
			trigger_error($message);
		}

		$this->template->assign_vars(array(
			'USERNAME'			=> $username,
			'EMAIL'				=> $email,
			'S_PROFILE_ACTION'	=> append_sid($this->root_path . 'ucp.' . $this->php_ext, 'mode=resend_act'))
		);

		$this->tpl_name = 'ucp_resend';
		$this->page_title = 'UCP_RESEND';
	}
}
