<?php
/**
*
* @package message
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\message;

/**
* Class admin_form
* Displays a message to the user and allows him to send an email
*
* @package phpbb\message
*/
class admin_form extends form
{
	/** @var string */
	protected $subject;
	/** @var string */
	protected $sender_name;
	/** @var string */
	protected $sender_address;

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

		if (!$this->config['contact_admin_form_enable']) /** TODO:  && !$this->config['contact_admin_info']) */
		{
			return 'NO_CONTACT_PAGE';
		}

		return false;
	}

	/**
	* {inheritDoc}
	*/
	public function bind(\phpbb\request\request_interface $request)
	{
		parent::bind($request);

		$this->subject = $request->variable('subject', '', true);
		$this->sender_address = $request->variable('email', '');
		$this->sender_name = $request->variable('name', '', true);
	}

	/**
	* {inheritDoc}
	*/
	public function submit(\messenger $messenger)
	{
		if (!$this->subject)
		{
			$this->errors[] = $this->user->lang['EMPTY_SUBJECT_EMAIL'];
		}
		if (!$this->body)
		{
			$this->errors[] = $this->user->lang['EMPTY_MESSAGE_EMAIL'];
		}

		if ($this->user->data['is_registered'])
		{
			$this->message->set_sender_from_user($this->user);
			$this->sender_name = $this->user->data['username'];
			$this->sender_address = $this->user->data['user_email'];
		}
		else
		{
			if (!$this->sender_name)
			{
				$this->errors[] = $this->user->lang['EMPTY_SENDER_NAME'];
			}
			if (!$this->sender_address || !preg_match('/^' . get_preg_expression('email') . '$/i', $this->sender_address))
			{
				$this->errors[] = $this->user->lang['EMPTY_SENDER_EMAIL'];
			}

			$this->message->set_sender($this->user->ip, $this->sender_name, $this->sender_address, $this->user->lang_name);
			$this->message->set_sender_notify_type(NOTIFY_EMAIL);
		}

		$this->message->set_template('contact_admin');
		$this->message->set_subject($this->subject);
		$this->message->set_body($this->body);
		$this->message->add_recipient(
			$this->user->lang['ADMINISTRATOR'],
			$this->config['board_contact'],
			$this->config['default_lang'],
			NOTIFY_EMAIL
		);

		$this->message->set_template_vars(array(
			'FROM_EMAIL_ADDRESS'	=> $this->sender_address,
			'FROM_IP_ADDRESS'		=> $this->user->ip,
			'S_IS_REGISTERED'		=> $this->user->data['is_registered'],

			'U_FROM_PROFILE'		=> generate_board_url() . '/memberlist.' . $this->phpEx . '?mode=viewprofile&u=' . $this->user->data['user_id'],
		));

		parent::submit($messenger);
	}

	/**
	* {inheritDoc}
	*/
	public function render(\phpbb\template\template $template)
	{
		$template->assign_vars(array(
			'S_CONTACT_ADMIN'	=> true,
			'S_CONTACT_FORM'	=> $this->config['contact_admin_form_enable'],
			'S_IS_REGISTERED'	=> $this->user->data['is_registered'],

			'CONTACT_INFO'		=> '', /** TODO: $this->config['contact_admin_info'] */
			'MESSAGE'			=> $this->body,
			'SUBJECT'			=> $this->subject,
			'NAME'				=> $this->sender_name,
			'EMAIL'				=> $this->sender_address,
		));

		parent::render($template);
	}
}
