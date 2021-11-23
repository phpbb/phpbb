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

namespace phpbb\contact\form;

use messenger;

/**
 * Class user_form
 * Allows users to send emails to other users
 */
class user_form extends form
{
	/** @var int */
	protected $recipient_id;
	/** @var array */
	protected $recipient_row;
	/** @var string */
	protected $subject;

	/**
	 * Get the data of the recipient
	 *
	 * @param int $user_id
	 * @return	false|array		false if the user does not exist, array otherwise
	 */
	protected function get_user_row(int $user_id)
	{
		$sql = 'SELECT user_id, username, user_colour, user_email, user_allow_viewemail, user_lang, user_jabber, user_notify_type
			FROM ' . USERS_TABLE . '
			WHERE user_id = ' . (int) $user_id . '
				AND user_type IN (' . USER_NORMAL . ', ' . USER_FOUNDER . ')';
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row;
	}

	/**
	 * {inheritDoc}
	 */
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

	/**
	 * {inheritDoc}
	 */
	public function bind(): void
	{
		parent::bind();

		$this->recipient_id = $this->request->variable('user_id', 0);
		$this->subject = $this->request->variable('subject', '', true);

		$this->recipient_row = $this->get_user_row($this->recipient_id);
	}

	/**
	 * {inheritDoc}
	 */
	public function submit(messenger $messenger): void
	{
		if (!$this->subject)
		{
			$this->errors[] = $this->language->lang('EMPTY_SUBJECT_EMAIL');
		}

		if (!$this->body)
		{
			$this->errors[] = $this->language->lang('EMPTY_MESSAGE_EMAIL');
		}

		$this->message->set_template('profile_send_email');
		$this->message->set_subject($this->subject);
		$this->message->set_body($this->body);
		$this->message->add_recipient_from_user_row($this->recipient_row);

		parent::submit($messenger);
	}

	/**
	 * {inheritDoc}
	 */
	public function render(): void
	{
		$controller_helper = $this->phpbb_container->get('controller.helper');

		parent::render();

		$this->template->assign_vars(array(
			'S_SEND_USER'			=> true,
			'S_POST_ACTION'			=> $controller_helper->route('phpbb_contact_user', ['user_id' => $this->recipient_id]),

			'L_SEND_EMAIL_USER'		=> $this->language->lang('SEND_EMAIL_USER', $this->recipient_row['username']),
			'USERNAME_FULL'			=> get_username_string('full', $this->recipient_row['user_id'], $this->recipient_row['username'], $this->recipient_row['user_colour']),
			'SUBJECT'				=> $this->subject,
			'MESSAGE'				=> $this->body,
		));
	}
}
