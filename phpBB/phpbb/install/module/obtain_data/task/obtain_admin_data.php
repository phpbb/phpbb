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

namespace phpbb\install\module\obtain_data\task;

use phpbb\install\exception\user_interaction_required_exception;

/**
 * This class requests and validates admin account data from the user
 */
class obtain_admin_data extends \phpbb\install\task_base implements \phpbb\install\task_interface
{
	/**
	 * @var \phpbb\install\helper\config
	 */
	protected $install_config;

	/**
	 * @var \phpbb\install\helper\iohandler\iohandler_interface
	 */
	protected $io_handler;

	/**
	 * Constructor
	 *
	 * @param \phpbb\install\helper\config							$install_config	Installer's config helper
	 * @param \phpbb\install\helper\iohandler\iohandler_interface	$iohandler		Installer's input-output handler
	 */
	public function __construct(\phpbb\install\helper\config $install_config,
								\phpbb\install\helper\iohandler\iohandler_interface $iohandler)
	{
		$this->install_config	= $install_config;
		$this->io_handler		= $iohandler;

		parent::__construct(true);
	}

	/**
	 * {@inheritdoc}
	 */
	public function run()
	{
		// Check if data is sent
		if ($this->io_handler->get_input('submit_admin', false))
		{
			$this->process_form();
		}
		else
		{
			$this->request_form_data();
		}
	}

	/**
	 * Process form data
	 */
	protected function process_form()
	{
		// Admin data
		$admin_name		= $this->io_handler->get_input('admin_name', '', true);
		$admin_pass1	= $this->io_handler->get_input('admin_pass1', '', true);
		$admin_pass2	= $this->io_handler->get_input('admin_pass2', '', true);
		$board_email	= $this->io_handler->get_input('board_email', '', true);

		$admin_data_valid = $this->check_admin_data($admin_name, $admin_pass1, $admin_pass2, $board_email);

		if ($admin_data_valid)
		{
			$this->install_config->set('admin_name', $admin_name);
			$this->install_config->set('admin_passwd', $admin_pass1);
			$this->install_config->set('board_email', $board_email);
		}
		else
		{
			$this->request_form_data(true);
		}
	}

	/**
	 * Request data from the user
	 *
	 * @param bool $use_request_data Whether to use submited data
	 *
	 * @throws \phpbb\install\exception\user_interaction_required_exception When the user is required to provide data
	 */
	protected function request_form_data($use_request_data = false)
	{
		if ($use_request_data)
		{
			$admin_username	= $this->io_handler->get_input('admin_name', '', true);
			$admin_email	= $this->io_handler->get_input('board_email', '', true);
		}
		else
		{
			$admin_username	= '';
			$admin_email	= '';
		}

		$admin_form = array(
			'admin_name'	=> array(
				'label'			=> 'ADMIN_USERNAME',
				'description'	=> 'ADMIN_USERNAME_EXPLAIN',
				'type'			=> 'text',
				'default'		=> $admin_username,
			),
			'board_email'	=> array(
				'label'		=> 'CONTACT_EMAIL',
				'type'		=> 'email',
				'default'	=> $admin_email,
			),
			'admin_pass1'	=> array(
				'label'			=> 'ADMIN_PASSWORD',
				'description'	=> 'ADMIN_PASSWORD_EXPLAIN',
				'type'			=> 'password',
			),
			'admin_pass2'	=> array(
				'label'	=> 'ADMIN_PASSWORD_CONFIRM',
				'type'	=> 'password',
			),
			'submit_admin'	=> array(
				'label'	=> 'SUBMIT',
				'type'	=> 'submit',
			),
		);

		$this->io_handler->add_user_form_group('ADMIN_CONFIG', $admin_form);

		// Require user interaction
		throw new user_interaction_required_exception();
	}

	/**
	 * Check admin data
	 *
	 * @param string	$username	Admin username
	 * @param string	$pass1		Admin password
	 * @param string	$pass2		Admin password confirmation
	 * @param string	$email		Admin e-mail address
	 *
	 * @return bool	True if data is valid, false otherwise
	 */
	protected function check_admin_data($username, $pass1, $pass2, $email)
	{
		$data_valid = true;

		// Check if none of admin data is empty
		if (in_array('', array($username, $pass1, $pass2, $email), true))
		{
			$this->io_handler->add_error_message('INST_ERR_MISSING_DATA');
			$data_valid = false;
		}

		if (utf8_strlen($username) < 3)
		{
			$this->io_handler->add_error_message('INST_ERR_USER_TOO_SHORT');
			$data_valid = false;
		}

		if (utf8_strlen($username) > 20)
		{
			$this->io_handler->add_error_message('INST_ERR_USER_TOO_LONG');
			$data_valid = false;
		}

		if ($pass1 !== $pass2 && $pass1 !== '')
		{
			$this->io_handler->add_error_message('INST_ERR_PASSWORD_MISMATCH');
			$data_valid = false;
		}

		// Test against the default password rules
		if (utf8_strlen($pass1) < 6)
		{
			$this->io_handler->add_error_message('INST_ERR_PASSWORD_TOO_SHORT');
			$data_valid = false;
		}

		if (utf8_strlen($pass1) > 30)
		{
			$this->io_handler->add_error_message('INST_ERR_PASSWORD_TOO_LONG');
			$data_valid = false;
		}

		if (!preg_match('/^' . get_preg_expression('email') . '$/i', $email))
		{
			$this->io_handler->add_error_message('INST_ERR_EMAIL_INVALID');
			$data_valid = false;
		}

		return $data_valid;
	}

	/**
	 * {@inheritdoc}
	 */
	static public function get_step_count()
	{
		return 0;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_task_lang_name()
	{
		return '';
	}
}
