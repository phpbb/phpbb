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
use phpbb\auth\auth;
use phpbb\config\config;
use phpbb\contact\message;
use phpbb\db\driver\driver_interface;
use phpbb\request\request_interface;
use phpbb\user;

/**
 * Abstract class form
 */
abstract class form
{
	/** @var auth */
	protected $auth;
	/** @var config */
	protected $config;
	/** @var driver_interface */
	protected $db;
	/** @var message */
	protected $message;
	/** @var user */
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

	protected $template;

	protected $request;

	protected $phpbb_container;

	protected $language;

	/**
	 * Construct
	 *
	 * @param auth $auth
	 * @param config $config
	 * @param driver_interface $db
	 * @param user $user
	 * @param string $phpbb_root_path
	 * @param string $phpEx
	 * @param $template
	 * @param $request
	 * @param $phpbb_container
	 */
	public function __construct(auth $auth, config $config, driver_interface $db, user $user, string $phpbb_root_path, string $phpEx, $template, $request, $phpbb_container, $language)
	{
		$this->phpbb_root_path = $phpbb_root_path;
		$this->phpEx = $phpEx;
		$this->user = $user;
		$this->auth = $auth;
		$this->config = $config;
		$this->db = $db;
		$this->template = $template;
		$this->request = $request;
		$this->phpbb_container = $phpbb_container;
		$this->language = $language;

		$this->message = new message($config['server_name']);
		$this->message->set_sender_from_user($this->user);
	}

	/**
	 * Returns the title for the email form page
	 *
	 * @return string
	 */
	public function get_page_title(): string
	{
		return $this->language->lang('SEND_EMAIL');
	}

	/**
	 * Returns the file name of the form template
	 *
	 * @return string
	 */
	public function get_template_file(): string
	{
		return 'contact.html';
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
	public function get_return_message(): string
	{
		return sprintf($this->language->lang('RETURN_INDEX'), '<a href="' . append_sid($this->phpbb_root_path . 'index.' . $this->phpEx) . '">', '</a>');
	}

	/**
	 * Bind the values of the request to the form
	 *
	 * @return void
	 */
	public function bind(): void
	{
		$this->cc_sender = $this->request->is_set_post('cc_sender');
		$this->body = $this->request->variable('message', '', true);
	}

	/**
	 * Submit form, generate the email and send it
	 *
	 * @param messenger $messenger
	 * @return void
	 */
	public function submit(messenger $messenger): void
	{
		if (!check_form_key('message_email'))
		{
			$this->errors[] = $this->language->lang('FORM_INVALID');
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
			trigger_error($this->language->lang('EMAIL_SENT') . '<br /><br />' . $this->get_return_message());
		}
	}

	/**
	 * Render the template of the form
	 *
	 * @return void
	 */
	public function render(): void
	{
		add_form_key('message_email');

		$this->template->assign_vars(array(
			'ERROR_MESSAGE'		=> (count($this->errors)) ? implode('<br />', $this->errors) : '',
		));
	}
}
