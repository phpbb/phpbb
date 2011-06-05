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

class phpbb_message_topic_form extends phpbb_message_form
{
	protected $topic_id;

	protected $topic_row;
	protected $recipient_address;
	protected $recipient_name;
	protected $recipient_lang;

	protected function get_topic_row($topic_id)
	{
		$sql = 'SELECT forum_id, topic_title
			FROM ' . TOPICS_TABLE . '
			WHERE topic_id = ' . (int) $topic_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row;
	}

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

		if (!$this->topic_row)
		{
			return 'NO_TOPIC';
		}

		/**
		* @todo remove else case when global topics have forum id
		*/
		if ($this->topic_row['forum_id'])
		{
			if (!$this->auth->acl_get('f_read', $this->topic_row['forum_id']))
			{
				return 'SORRY_AUTH_READ';
			}

			if (!$this->auth->acl_get('f_email', $this->topic_row['forum_id']))
			{
				return 'NO_EMAIL';
			}
		}
		else
		{
			// If global announcement, we need to check if the user is able to at least read and email in one forum...
			if (!$this->auth->acl_getf_global('f_read'))
			{
				return 'SORRY_AUTH_READ';
			}

			if (!$this->auth->acl_getf_global('f_email'))
			{
				return 'NO_EMAIL';
			}
		}

		return false;
	}

	public function bind($request)
	{
		parent::bind($request);

		$this->topic_id = $request->variable('t', 0);
		$this->recipient_address = $request->variable('email', '');
		$this->recipient_name = $request->variable('name', '', true);
		$this->recipient_lang = $request->variable('lang', $this->config['default_lang']);

		$this->topic_row = $this->get_topic_row($this->topic_id);
	}

	public function submit(messenger $messenger)
	{
		if (!$this->recipient_address || !preg_match('/^' . get_preg_expression('email') . '$/i', $this->recipient_address))
		{
			$this->errors[] = $this->user->lang['EMPTY_ADDRESS_EMAIL'];
		}

		if (!$this->recipient_name)
		{
			$this->errors[] = $this->user->lang['EMPTY_NAME_EMAIL'];
		}


		$this->message->set_template('email_notify');
		$this->message->set_template_vars(array(
			'TOPIC_NAME'	=> htmlspecialchars_decode($this->topic_row['topic_title']),
			'U_TOPIC'		=> generate_board_url() . '/viewtopic.' . $this->phpEx . '?f=' . $this->topic_row['forum_id'] . '&t=' . $this->topic_id,
		));

		$this->message->add_recipient(
			$this->recipient_name,
			$this->recipient_address,
			$this->recipient_lang,
			NOTIFY_EMAIL
		);
		$this->message->set_sender_notify_type(NOTIFY_EMAIL);

		parent::submit($messenger);
	}

	protected function get_return_message()
	{
		return sprintf($this->user->lang['RETURN_TOPIC'],  '<a href="' . append_sid($this->phpbb_root_path . 'viewtopic.' . $this->phpEx, 'f=' . $this->topic_row['forum_id'] . '&amp;t=' . $this->topic_id) . '">', '</a>');
	}

	public function render($template)
	{
		parent::render($template);

		$template->assign_vars(array(
			'EMAIL'				=> $this->recipient_address,
			'NAME'				=> $this->recipient_name,
			'S_LANG_OPTIONS'	=> language_select($this->recipient_lang),
			'MESSAGE'			=> $this->body,

			'L_EMAIL_BODY_EXPLAIN'	=> $this->user->lang['EMAIL_TOPIC_EXPLAIN'],
			'S_POST_ACTION'			=> append_sid($this->phpbb_root_path . 'memberlist.' . $this->phpEx, 'mode=email&amp;t=' . $this->topic_id))
		);
	}
}
