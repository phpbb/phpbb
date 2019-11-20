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
 * User activation
 */
class activate
{
	var $u_action;

	public function main($id, $mode)
	{

		$user_id = $this->request->variable('u', 0);
		$key = $this->request->variable('k', '');

		$sql = 'SELECT user_id, username, user_type, user_email, user_newpasswd, user_lang, user_notify_type, user_actkey, user_inactive_reason
			FROM ' . $this->tables['users'] . "
			WHERE user_id = $user_id";
		$result = $this->db->sql_query($sql);
		$user_row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$user_row)
		{
			trigger_error('NO_USER');
		}

		if ($user_row['user_type'] <> USER_INACTIVE && !$user_row['user_newpasswd'])
		{
			meta_refresh(3, append_sid("{$this->root_path}index.$this->php_ext"));
			trigger_error('ALREADY_ACTIVATED');
		}

		if ($user_row['user_inactive_reason'] == INACTIVE_MANUAL || $user_row['user_actkey'] !== $key)
		{
			trigger_error('WRONG_ACTIVATION');
		}

		// Do not allow activating by non administrators when admin activation is on
		// Only activation type the user should be able to do is INACTIVE_REMIND
		// or activate a new password which is not an activation state :@
		if (!$user_row['user_newpasswd'] && $user_row['user_inactive_reason'] != INACTIVE_REMIND && $this->config['require_activation'] == USER_ACTIVATION_ADMIN && !$this->auth->acl_get('a_user'))
		{
			if (!$this->user->data['is_registered'])
			{
				login_box('', $this->language->lang('NO_AUTH_OPERATION'));
			}
			send_status_line(403, 'Forbidden');
			trigger_error('NO_AUTH_OPERATION');
		}

		$update_password = ($user_row['user_newpasswd']) ? true : false;

		if ($update_password)
		{
			$sql_ary = array(
				'user_actkey'		=> '',
				'user_password'		=> $user_row['user_newpasswd'],
				'user_newpasswd'	=> '',
				'user_login_attempts'	=> 0,
			);

			$sql = 'UPDATE ' . $this->tables['users'] . '
				SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
				WHERE user_id = ' . $user_row['user_id'];
			$this->db->sql_query($sql);

			$this->user->reset_login_keys($user_row['user_id']);

			$this->log->add('user', $this->user->data['user_id'], $this->user->ip, 'LOG_USER_NEW_PASSWORD', false, array(
				'reportee_id' => $user_row['user_id'],
				$user_row['username']
			));
		}

		if (!$update_password)
		{
			include_once($this->root_path . 'includes/functions_user.' . $this->php_ext);

			user_active_flip('activate', $user_row['user_id']);

			$sql = 'UPDATE ' . $this->tables['users'] . "
				SET user_actkey = ''
				WHERE user_id = {$user_row['user_id']}";
			$this->db->sql_query($sql);

			// Create the correct logs
			$this->log->add('user', $this->user->data['user_id'], $this->user->ip, 'LOG_USER_ACTIVE_USER', false, array(
				'reportee_id' => $user_row['user_id']
			));

			if ($this->auth->acl_get('a_user'))
			{
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_USER_ACTIVE', false, array($user_row['username']));
			}
		}

		if ($this->config['require_activation'] == USER_ACTIVATION_ADMIN && !$update_password)
		{
			/* @var $phpbb_notifications \phpbb\notification\manager */
			$phpbb_notifications = $phpbb_container->get('notification_manager');
			$this->notifications_manager->delete_notifications('notification.type.admin_activate_user', $user_row['user_id']);

			include_once($this->root_path . 'includes/functions_messenger.' . $this->php_ext);

			$messenger = new messenger(false);

			$messenger->template('admin_welcome_activated', $user_row['user_lang']);

			$messenger->set_addresses($user_row);

			$messenger->anti_abuse_headers($config, $user);

			$messenger->assign_vars(array(
				'USERNAME'	=> htmlspecialchars_decode($user_row['username']))
			);

			$messenger->send($user_row['user_notify_type']);

			$message = 'ACCOUNT_ACTIVE_ADMIN';
		}
		else
		{
			if (!$update_password)
			{
				$message = ($user_row['user_inactive_reason'] == INACTIVE_PROFILE) ? 'ACCOUNT_ACTIVE_PROFILE' : 'ACCOUNT_ACTIVE';
			}
			else
			{
				$message = 'PASSWORD_ACTIVATED';
			}
		}

		/**
		 * This event can be used to modify data after user account's activation
		 *
		 * @event core.ucp_activate_after
		 * @var array	user_row	Array with some user data
		 * @var string	message		Language string of the message that will be displayed to the user
		 * @since 3.1.6-RC1
		 */
		$vars = array('user_row', 'message');
		extract($this->dispatcher->trigger_event('core.ucp_activate_after', compact($vars)));

		meta_refresh(3, append_sid("{$this->root_path}index.$this->php_ext"));
		trigger_error($this->language->lang($message));
	}
}
