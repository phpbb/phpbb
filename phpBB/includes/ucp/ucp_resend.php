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

use phpbb\auth\auth;
use phpbb\config\config;
use phpbb\controller\helper;
use phpbb\db\driver\driver_interface;
use phpbb\language\language;
use phpbb\messenger\method\email;
use phpbb\request\request_interface;
use phpbb\template\template;
use phpbb\user;

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

	/** @var string */
	public $tpl_name;

	/** @var string */
	public $page_title;

	/** @var auth */
	protected $auth;

	/** @var config */
	protected $config;

	/** @var helper */
	protected $controller_helper;

	/** @var driver_interface */
	protected $db;

	/** @var language */
	protected $language;

	/** @var \phpbb\di\container_builder */
	protected $phpbb_container;

	/** @var request_interface */
	protected $request;

	/** @var template */
	protected $template;

	/** @var user */
	protected $user;

	/** @var string phpBB root path */
	protected $phpbb_root_path;

	/** @var string PHP file extension */
	protected $php_ext;

	public function __construct()
	{
		global $db, $user, $auth, $request;
		global $config, $language, $template, $phpbb_container, $phpbb_root_path, $phpEx;

		$this->auth = $auth;
		$this->config = $config;
		$this->controller_helper = $phpbb_container->get('controller.helper');
		$this->db = $db;
		$this->language = $language;
		$this->phpbb_container = $phpbb_container;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $phpEx;
	}

	function main($id, $mode)
	{
		$username	= $this->request->variable('username', '', true);
		$email		= strtolower($this->request->variable('email', ''));
		$submit		= $this->request->is_set_post('submit');

		add_form_key('ucp_resend');

		if ($submit)
		{
			if (!check_form_key('ucp_resend'))
			{
				trigger_error('FORM_INVALID');
			}

			$sql = 'SELECT user_id, group_id, username, user_email, user_type, user_lang, user_actkey, user_actkey_expiration, user_inactive_reason
				FROM ' . USERS_TABLE . "
				WHERE user_email = '" . $this->db->sql_escape($email) . "'
					AND username_clean = '" . $this->db->sql_escape(utf8_clean_string($username)) . "'";
			$result = $this->db->sql_query($sql);
			$user_row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if (!$user_row)
			{
				// No user found, output the same message as if the email was sent to avoid giving away information about registered users
				$this->output_resend_message();
				return;
			}

			if ($user_row['user_type'] == USER_IGNORE)
			{
				// User is ignored, output the same message as if the email was sent to avoid giving away information about registered users
				$this->output_resend_message();
				return;
			}

			if (!$user_row['user_actkey'] && $user_row['user_type'] != USER_INACTIVE)
			{
				// User is active or has no actkey, output the same message as if the email was sent to avoid giving away information about registered users
				$this->output_resend_message();
				return;
			}

			if (!$user_row['user_actkey'] || ($user_row['user_type'] == USER_INACTIVE && $user_row['user_inactive_reason'] == INACTIVE_MANUAL))
			{
				// User is inactive and has no actkey or is manually deactivated, output the same message as if the email was sent to avoid giving away information about registered users
				$this->output_resend_message();
				return;
			}

			// Do not resend activation email if valid one still exists
			if (!empty($user_row['user_actkey']) && (int) $user_row['user_actkey_expiration'] >= time())
			{
				// User has a valid activation key, output the same message as if the email was sent to avoid giving away information about registered users
				$this->output_resend_message();
				return;
			}

			// Determine coppa status on group (REGISTERED(_COPPA))
			$sql = 'SELECT group_name, group_type
				FROM ' . GROUPS_TABLE . '
				WHERE group_id = ' . $user_row['group_id'];
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if (!$row)
			{
				// User has no group, output the same message as if the email was sent to avoid giving away information about registered users
				$this->output_resend_message();
				return;
			}

			$board_url = generate_board_url();
			$coppa = ($row['group_name'] == 'REGISTERED_COPPA' && $row['group_type'] == GROUP_SPECIAL) ? true : false;

			$email_method = $this->phpbb_container->get('messenger.method.email');
			$email_method->set_use_queue(false);

			if ($this->config['require_activation'] == USER_ACTIVATION_SELF || $coppa)
			{
				$email_method->template(($coppa) ? 'coppa_resend_inactive' : 'user_resend_inactive', $user_row['user_lang']);
				$email_method->set_addresses($user_row);

				$email_method->anti_abuse_headers($this->config, $this->user);

				$email_method->assign_vars([
					'WELCOME_MSG'	=> html_entity_decode($this->language->lang('WELCOME_SUBJECT', $this->config['sitename']), ENT_COMPAT),
					'USERNAME'		=> html_entity_decode($user_row['username'], ENT_COMPAT),
					'U_ACTIVATE'	=> $board_url . "/ucp.$this->php_ext?mode=activate&u={$user_row['user_id']}&k={$user_row['user_actkey']}",
				]);

				if ($coppa)
				{
					$email_method->assign_vars([
						'FAX_INFO'		=> $this->config['coppa_fax'],
						'MAIL_INFO'		=> $this->config['coppa_mail'],
						'EMAIL_ADDRESS'	=> $user_row['user_email'],
					]);
				}

				$email_method->send();
			}

			if ($this->config['require_activation'] == USER_ACTIVATION_ADMIN)
			{
				// Grab an array of user_id's with a_user permissions ... these users can activate a user
				$admin_ary = $this->auth->acl_get_list(false, 'a_user', false);

				$sql = 'SELECT user_id, username, user_email, user_lang
					FROM ' . USERS_TABLE . '
					WHERE ' . $this->db->sql_in_set('user_id', $admin_ary[0]['a_user']);
				$result = $this->db->sql_query($sql);

				/** @var \phpbb\di\service_collection $messenger_collection */
				$messenger_collection = $this->phpbb_container->get('messenger.method_collection');
				/** @var \phpbb\messenger\method\messenger_interface $messenger_method */
				$messenger_method = $messenger_collection->offsetGet('messenger.method.email');

				while ($row = $this->db->sql_fetchrow($result))
				{
					$messenger_method->set_use_queue(false);
					$messenger_method->template('admin_activate', $row['user_lang']);
					$messenger_method->set_addresses($row);
					$messenger_method->anti_abuse_headers($this->config, $this->user);
					$messenger_method->assign_vars([
						'USERNAME'			=> html_entity_decode($user_row['username'], ENT_COMPAT),
						'U_USER_DETAILS'	=> $board_url . "/memberlist.$this->php_ext?mode=viewprofile&u={$user_row['user_id']}",
						'U_ACTIVATE'		=> $board_url . "/ucp.$this->php_ext?mode=activate&u={$user_row['user_id']}&k={$user_row['user_actkey']}",
					]);

					$messenger_method->send();
				}
				$this->db->sql_freeresult($result);
			}

			$this->update_activation_expiration($user_row['user_id']);

			meta_refresh(3, $this->controller_helper->route('phpbb_index_controller'));

			$this->output_resend_message();
			return;
		}

		$this->template->assign_vars(array(
			'USERNAME'			=> $username,
			'EMAIL'				=> $email,
			'S_PROFILE_ACTION'	=> append_sid($this->phpbb_root_path . 'ucp.' . $this->php_ext, 'mode=resend_act'))
		);

		$this->tpl_name = 'ucp_resend';
		$this->page_title = 'UCP_RESEND';
	}

	/**
	 * Update activation expiration to 1 day from now
	 *
	 * @param int $user_id User ID to update the activation expiration for
	 *
	 * @return void
	 */
	protected function update_activation_expiration(int $user_id): void
	{
		$sql_ary = [
			'user_actkey_expiration'	=> $this->user::get_token_expiration(),
		];

		$sql = 'UPDATE ' . USERS_TABLE . '
			SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
			WHERE user_id = ' . (int) $user_id;
		$this->db->sql_query($sql);
	}

	/**
	 * Output resend activation email message
	 *
	 * @return void
	 */
	protected function output_resend_message(): void
	{
		$index_link = append_sid("{$this->phpbb_root_path}index.{$this->php_ext}");
		meta_refresh(3, $index_link);

		$this->template->assign_vars([
			'RETURN_LINK'		=> $index_link,
			'RETURN_LINK_TITLE'	=> 'RETURN_INDEX',
			'MESSAGE_TITLE'		=> 'INFORMATION',
			'MESSAGE_TEXT'		=> ($this->config['require_activation'] == USER_ACTIVATION_ADMIN) ? $this->language->lang('ACTIVATION_EMAIL_SENT_ADMIN') : $this->language->lang('ACTIVATION_EMAIL_SENT')
		]);

		$this->template->set_filenames(['body' => 'message_body.html']);

		$this->tpl_name = 'message_body';
		$this->page_title = 'INFORMATION';
	}
}
