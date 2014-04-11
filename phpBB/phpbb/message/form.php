<?php
/**
*
* @package message
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\message;

abstract class form
{
	/** @var \phpbb\auth\auth */
	protected $auth;
	/** @var \phpbb\config\config */
	protected $config;
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;
	/** @var \phpbb\user */
	protected $user;

	/** @var string */
	protected $phpbb_root_path;
	/** @var string */
	protected $phpEx;

	protected $errors;
	protected $message;
	protected $cc_sender;
	protected $body;

	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\user $user, $phpbb_root_path, $phpEx)
	{
		$this->phpbb_root_path = $phpbb_root_path;
		$this->phpEx = $phpEx;
		$this->user = $user;
		$this->auth = $auth;
		$this->config = $config;
		$this->db = $db;

		$this->errors = array();

		$this->message = new \phpbb\message\message($config['server_name']);
		$this->message->set_sender_from_user($this->user);
	}

	/**
	* Returns the title for the email form page
	*/
	public function get_page_title()
	{
		$this->user->lang['SEND_EMAIL'];
	}

	public function get_template_file()
	{
		return 'memberlist_email.html';
	}

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

	public function get_return_message()
	{
		return sprintf($this->user->lang['RETURN_INDEX'], '<a href="' . append_sid($this->phpbb_root_path . 'index.' . $this->phpEx) . '">', '</a>');
	}

	public function bind(\phpbb\request\request_interface $request)
	{
		$this->cc_sender = $request->is_set_post('cc_sender');
		$this->body = $request->variable('message', '', true);
	}

	public function submit(\messenger $messenger)
	{
		if (!check_form_key('memberlist_email'))
		{
			$this->errors[] = 'FORM_INVALID';
		}

		if (!sizeof($this->errors))
		{
			$sql = 'UPDATE ' . USERS_TABLE . '
				SET user_emailtime = ' . time() . '
				WHERE user_id = ' . $this->user->data['user_id'];
			$this->db->sql_query($sql);

			if ($this->cc_sender)
			{
				$this->message->cc_sender();
			}

			$this->message->send($messenger, $this->phpEx);

			meta_refresh(3, append_sid($this->phpbb_root_path . 'index.' . $this->phpEx));
			trigger_error($this->user->lang['EMAIL_SENT'] . '<br /><br />' . $this->get_return_message());
		}
	}

	public function render(\phpbb\template\template $template)
	{
		add_form_key('memberlist_email');

		$template->assign_vars(array(
			'ERROR_MESSAGE'		=> (sizeof($this->errors)) ? implode('<br />', $this->errors) : '',
		));
	}
}
