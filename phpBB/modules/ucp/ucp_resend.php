<?php
/**
*
* @package ucp
* @version $Id$
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
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
* @package ucp
*/
class ucp_resend
{
	var $u_action;

	function main($id, $mode)
	{
		$username	= request_var('username', '', true);
		$email		= strtolower(request_var('email', ''));
		$submit		= phpbb_request::is_set_post('submit');

		add_form_key('ucp_resend');

		if ($submit)
		{
			if (!check_form_key('ucp_resend'))
			{
				trigger_error('FORM_INVALID');
			}

			$sql = 'SELECT user_id, group_id, username, user_email, user_type, user_lang, user_actkey, user_inactive_reason
				FROM ' . USERS_TABLE . "
				WHERE user_email = '" . phpbb::$db->sql_escape($email) . "'
					AND username_clean = '" . phpbb::$db->sql_escape(utf8_clean_string($username)) . "'";
			$result = phpbb::$db->sql_query($sql);
			$user_row = phpbb::$db->sql_fetchrow($result);
			phpbb::$db->sql_freeresult($result);

			if (!$user_row)
			{
				trigger_error('NO_EMAIL_USER');
			}

			if ($user_row['user_type'] == phpbb::USER_IGNORE)
			{
				trigger_error('NO_USER');
			}

			if (!$user_row['user_actkey'] && $user_row['user_type'] != phpbb::USER_INACTIVE)
			{
				trigger_error('ACCOUNT_ALREADY_ACTIVATED');
			}

			if (!$user_row['user_actkey'] || ($user_row['user_type'] == phpbb::USER_INACTIVE && $user_row['user_inactive_reason'] == INACTIVE_MANUAL))
			{
				trigger_error('ACCOUNT_DEACTIVATED');
			}

			// Determine coppa status on group (REGISTERED(_COPPA))
			$sql = 'SELECT group_name, group_type
				FROM ' . GROUPS_TABLE . '
				WHERE group_id = ' . $user_row['group_id'];
			$result = phpbb::$db->sql_query($sql);
			$row = phpbb::$db->sql_fetchrow($result);
			phpbb::$db->sql_freeresult($result);

			if (!$row)
			{
				trigger_error('NO_GROUP');
			}

			$coppa = ($row['group_name'] == 'REGISTERED_COPPA' && $row['group_type'] == GROUP_SPECIAL) ? true : false;

			include_once(PHPBB_ROOT_PATH . 'includes/functions_messenger.' . PHP_EXT);
			$messenger = new messenger(false);

			if (phpbb::$config['require_activation'] == USER_ACTIVATION_SELF || $coppa)
			{
				$messenger->template(($coppa) ? 'coppa_resend_inactive' : 'user_resend_inactive', $user_row['user_lang']);
				$messenger->to($user_row['user_email'], $user_row['username']);

				$messenger->headers('X-AntiAbuse: Board servername - ' . phpbb::$config['server_name']);
				$messenger->headers('X-AntiAbuse: User_id - ' . phpbb::$user->data['user_id']);
				$messenger->headers('X-AntiAbuse: Username - ' . phpbb::$user->data['username']);
				$messenger->headers('X-AntiAbuse: User IP - ' . phpbb::$user->ip);

				$messenger->assign_vars(array(
					'WELCOME_MSG'	=> htmlspecialchars_decode(sprintf(phpbb::$user->lang['WELCOME_SUBJECT'], phpbb::$config['sitename'])),
					'USERNAME'		=> htmlspecialchars_decode($user_row['username']),
					'U_ACTIVATE'	=> generate_board_url() . '/ucp.' . PHP_EXT . "?mode=activate&u={$user_row['user_id']}&k={$user_row['user_actkey']}")
				);

				if ($coppa)
				{
					$messenger->assign_vars(array(
						'FAX_INFO'		=> phpbb::$config['coppa_fax'],
						'MAIL_INFO'		=> phpbb::$config['coppa_mail'],
						'EMAIL_ADDRESS'	=> $user_row['user_email'],
					));
				}

				$messenger->send(NOTIFY_EMAIL);
			}

			if (phpbb::$config['require_activation'] == USER_ACTIVATION_ADMIN)
			{
				// Grab an array of user_id's with a_user permissions ... these users can activate a user
				$admin_ary = phpbb::$acl->acl_get_list(false, 'a_user', false);

				$sql = 'SELECT user_id, username, user_email, user_lang, user_jabber, user_notify_type
					FROM ' . USERS_TABLE . '
					WHERE ' . phpbb::$db->sql_in_set('user_id', $admin_ary[0]['a_user']);
				$result = phpbb::$db->sql_query($sql);

				while ($row = phpbb::$db->sql_fetchrow($result))
				{
					$messenger->template('admin_activate', $row['user_lang']);
					$messenger->to($row['user_email'], $row['username']);
					$messenger->im($row['user_jabber'], $row['username']);

					$messenger->assign_vars(array(
						'USERNAME'			=> htmlspecialchars_decode($user_row['username']),
						'U_USER_DETAILS'	=> generate_board_url() . '/memberlist.' . PHP_EXT . "?mode=viewprofile&u={$user_row['user_id']}",
						'U_ACTIVATE'		=> generate_board_url() . '/ucp.' . PHP_EXT . "?mode=activate&u={$user_row['user_id']}&k={$user_row['user_actkey']}",
					));

					$messenger->send($row['user_notify_type']);
				}
				phpbb::$db->sql_freeresult($result);
			}

			meta_refresh(3, append_sid('index'));

			$message = (phpbb::$config['require_activation'] == USER_ACTIVATION_ADMIN) ? phpbb::$user->lang['ACIVATION_EMAIL_SENT_ADMIN'] : phpbb::$user->lang['ACTIVATION_EMAIL_SENT'];
			$message .= '<br /><br />' . sprintf(phpbb::$user->lang['RETURN_INDEX'], '<a href="' . append_sid('index') . '">', '</a>');
			trigger_error($message);
		}

		$template->assign_vars(array(
			'USERNAME'			=> $username,
			'EMAIL'				=> $email,
			'S_PROFILE_ACTION'	=> append_sid('ucp', 'mode=resend_act'),
		));

		$this->tpl_name = 'ucp_resend';
		$this->page_title = 'UCP_RESEND';
	}
}

?>