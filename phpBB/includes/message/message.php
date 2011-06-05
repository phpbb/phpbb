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

class phpbb_message
{
	protected $board_contact;
	protected $server_name;

	protected $subject = '';
	protected $body = '';
	protected $template = '';
	protected $template_vars = array();

	protected $sender_ip = '';
	protected $sender_name = '';
	protected $sender_address = '';
	protected $sender_lang = '';
	protected $sender_id = '';
	protected $sender_username = '';
	protected $sender_jabber = '';
	protected $sender_notify_type = NOTIFY_EMAIL;

	protected $recipients;

	public function __construct($board_contact, $server_name)
	{
		$this->board_contact = $board_contact;
		$this->server_name = $server_name;
	}

	public function set_subject($subject)
	{
		$this->subject = $subject;
	}

	public function set_body($body)
	{
		$this->body = $body;
	}

	public function set_template($template)
	{
		$this->template = $template;
	}

	public function set_template_vars($template_vars)
	{
		$this->template_vars = $template_vars;
	}

	public function add_recipient_from_user_row(array $user)
	{
		$this->add_recipient(
			$user['username'],
			$user['user_email'],
			$user['user_lang'],
			$user['username'],
			$user['user_jabber'],
			$user['user_notify_type']
		);
	}

	public function add_recipient($recipient_name, $recipient_address, $recipient_lang, $recipient_notify_type = NOTIFY_EMAIL, $recipient_username = '', $recipient_jabber = '')
	{
		$this->recipients[] = array(
			'name'			=> $recipient_name,
			'address'		=> $recipient_address,
			'lang'			=> $recipient_lang,
			'username'		=> $recipient_username,
			'jabber'		=> $recipient_jabber,
			'notify_type'	=> $recipient_notify_type,
			'to_name'		=> $recipient_name,
		);
	}

	public function set_sender_from_user($user)
	{
		$this->set_sender(
			$user->ip,
			$user->data['username'],
			$user->data['user_email'],
			$user->lang_name,
			$user->data['user_id'],
			$user->data['username'],
			$user->data['user_jabber']
		);

		$this->set_sender_notify_type($user->data['user_notify_type']);
	}

	public function set_sender($sender_ip, $sender_name, $sender_address, $sender_lang = '', $sender_id = 0, $sender_username = '', $sender_jabber = '')
	{
		$this->sender_ip = $sender_ip;
		$this->sender_name = $sender_name;
		$this->sender_address = $sender_address;
		$this->sender_lang = $sender_lang;
		$this->sender_id = $sender_id;
		$this->sender_username = $sender_username;
		$this->sender_jabber = $sender_jabber;
	}

	public function set_sender_notify_type($sender_notify_type)
	{
		$this->sender_notify_type = $sender_notify_type;
	}


// Ok, now the same email if CC specified, but without exposing the users email address
	public function cc_sender()
	{
		if (!sizeof($this->recipients))
		{
			trigger_error('No email recipients specified');
		}
		if (!$this->sender_address)
		{
			trigger_error('No email sender specified');
		}

		$this->recipients[] = array(
			'lang'			=> $this->sender_lang,
			'address'		=> $this->sender_address,
			'name'			=> $this->sender_name,
			'username'		=> $this->sender_username,
			'jabber'		=> $this->sender_jabber,
			'notify_type'	=> $this->sender_notify_type,
			'to_name'		=> $this->recipients[0]['to_name'],
		);
	}

	public function send(messenger $messenger)
	{
		if (!sizeof($this->recipients))
		{
			return;
		}

		foreach ($this->recipients as $recipient)
		{
			$messenger->template($this->template, $recipient['lang']);
			$messenger->replyto($this->sender_address);
			$messenger->to($recipient['address'], $recipient['name']);
			$messenger->im($recipient['jabber'], $recipient['username']);

			$messenger->headers('X-AntiAbuse: Board servername - ' . $this->server_name);
			$messenger->headers('X-AntiAbuse: User IP - ' . $this->sender_ip);

			if ($this->sender_id)
			{
				$messenger->headers('X-AntiAbuse: User_id - ' . $this->sender_id);
			}
			if ($this->sender_username)
			{
				$messenger->headers('X-AntiAbuse: Username - ' . $this->sender_username);
			}

			$messenger->subject(htmlspecialchars_decode($this->subject));

			$messenger->assign_vars(array(
				'BOARD_CONTACT'	=> $this->board_contact,
				'TO_USERNAME'	=> htmlspecialchars_decode($recipient['to_name']),
				'FROM_USERNAME'	=> htmlspecialchars_decode($this->sender_name),
				'MESSAGE'		=> htmlspecialchars_decode($this->body))
			);

			if (sizeof($this->template_vars))
			{
				$messenger->assign_vars($this->template_vars);
			}

			$messenger->send($recipient['notify_type']);
		}
	}
}
