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

namespace phpbb\message;

/**
* Abstract class form
*/
abstract class form
{
	/** @var \phpbb\auth\auth */
	protected $auth;
	/** @var \phpbb\config\config */
	protected $config;
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;
	/** @var \phpbb\message\message */
	protected $message;
	/** @var \phpbb\user */
	protected $user;

	/** @var string */
	protected $phpbb_root_path;
	/** @var string */
	protected $phpEx;

	/** @var array */
	protected $errors = array();
	/** @var bool */
	protected $cc_sender;
	/** @var string */
	protected $body;

	/**
	* Construct
	*
	* @param \phpbb\auth\auth $auth
	* @param \phpbb\config\config $config
	* @param \phpbb\db\driver\driver_interface $db
	* @param \phpbb\user $user
	* @param string $phpbb_root_path
	* @param string $phpEx
	*/
	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\user $user, $phpbb_root_path, $phpEx)
	{
		$this->phpbb_root_path = $phpbb_root_path;
		$this->phpEx = $phpEx;
		$this->user = $user;
		$this->auth = $auth;
		$this->config = $config;
		$this->db = $db;

		$this->message = new message($config['server_name']);
		$this->message->set_sender_from_user($this->user);
	}

	/**
	* Returns the title for the email form page
	*
	* @return string
	*/
	public function get_page_title()
	{
		return $this->user->lang['SEND_EMAIL'];
	}

	/**
	* Returns the file name of the form template
	*
	* @return string
	*/
	public function get_template_file()
	{
		return 'memberlist_email.html';
	}

	/**
	* Checks whether the user is allowed to use the form
	*
	* @return false|string	Error string if not allowed, false otherwise
	*/
	public function check_allow()
	{
		if (!$this->config['email_enable'])
		{
			return 'EMAIL_DISABLED';
		}

		if (time() - $this->user->data['user_emailtime'] < $this->config['flood_interval'])
		{
			return 'FLOOD_EMAIL_LIMIT';
		}

		return false;
	}

	/**
	* Get the return link after the message has been sent
	*
	* @return string
	*/
	public function get_return_message()
	{
		return sprintf($this->user->lang['RETURN_INDEX'], '<a href="' . append_sid($this->phpbb_root_path . 'index.' . $this->phpEx) . '">', '</a>');
	}

	/**
	* Bind the values of the request to the form
	*
	* @param \phpbb\request\request_interface $request
	* @return null
	*/
	public function bind(\phpbb\request\request_interface $request)
	{
		$this->cc_sender = $request->is_set_post('cc_sender');
		$this->body = $request->variable('message', '', true);
	}

	/**
	* Submit form, generate the email and send it
	*
	* @param \messenger $messenger
	* @return null
	*/
	public function submit(\messenger $messenger)
	{
		if (!check_form_key('memberlist_email'))
		{
			$this->errors[] = $this->user->lang('FORM_INVALID');
		}

		if (!count($this->errors))
		{
			$sql = 'UPDATE ' . USERS_TABLE . '
				SET user_emailtime = ' . time() . '
				WHERE user_id = ' . $this->user->data['user_id'];
			$this->db->sql_query($sql);

			if ($this->cc_sender && $this->user->data['is_registered'])
			{
				$this->message->cc_sender();
			}

			$this->message->send($messenger, phpbb_get_board_contact($this->config, $this->phpEx));

			meta_refresh(3, append_sid($this->phpbb_root_path . 'index.' . $this->phpEx));
			trigger_error($this->user->lang['EMAIL_SENT'] . '<br /><br />' . $this->get_return_message());
		}
	}

	/**
	* Render the template of the form
	*
	* @param \phpbb\template\template $template
	* @return null
	*/
	public function render(\phpbb\template\template $template)
	{
		add_form_key('memberlist_email');

		$template->assign_vars(array(
			'ERROR_MESSAGE'		=> (count($this->errors)) ? implode('<br />', $this->errors) : '',
		));
	}
}
