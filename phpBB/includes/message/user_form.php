<?php
/**
*
* @package message
* @copyright (c) 2011 phpBB Group
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

class phpbb_message_user_form extends phpbb_message_form
{
	protected $recipient_id;
	protected $subject;

	public function check_allow()
	{
		$error = parent::check_allow();
		if ($error)
		{
			return $error;
		}

		if (!$this->auth->acl_get('u_sendemail'))
		{
			return 'NO_EMAIL';
		}

		if ($this->recipient_id == ANONYMOUS || !$this->config['board_email_form'])
		{
			return 'NO_EMAIL';
		}

		if (!$this->recipient_row)
		{
			return 'NO_USER';
		}

		// Can we send email to this user?
		if (!$this->recipient_row['user_allow_viewemail'] && !$this->auth->acl_get('a_user'))
		{
			return 'NO_EMAIL';
		}

		return false;
	}

	protected function get_user_row($user_id)
	{
		$sql = 'SELECT username, user_email, user_allow_viewemail, user_lang, user_jabber, user_notify_type
			FROM ' . USERS_TABLE . '
			WHERE user_id = ' . ((int) $user_id) . '
				AND user_type IN (' . USER_NORMAL . ', ' . USER_FOUNDER . ')';
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row;
	}

	public function bind($request)
	{
		parent::bind($request);

		$this->recipient_id = $request->variable('u', 0);
		$this->subject = $request->variable('subject', '', true);

		$this->recipient_row = $this->get_user_row($this->recipient_id);
	}

	public function submit(messenger $messenger)
	{
		if (!$this->subject)
		{
			$this->errors[] = $this->user->lang['EMPTY_SUBJECT_EMAIL'];
		}

		if (!$this->body)
		{
			$this->errors[] = $this->user->lang['EMPTY_MESSAGE_EMAIL'];
		}

		$this->message->set_template('profile_send_email');
		$this->message->set_subject($this->subject);
		$this->message->set_body($this->body);
		$this->message->add_recipient_from_user_row($this->recipient_row);

		parent::submit($messenger);
	}

	public function render($template)
	{
		parent::render($template);

		$template->assign_vars(array(
			'S_SEND_USER'			=> true,
			'S_POST_ACTION'			=> append_sid($this->phpbb_root_path . 'memberlist.' . $this->phpEx, 'mode=email&amp;u=' . $this->recipient_id),

			'USERNAME'				=> $this->recipient_row['username'],
			'SUBJECT'				=> $this->subject,
			'MESSAGE'				=> $this->body,
		));
	}
}
