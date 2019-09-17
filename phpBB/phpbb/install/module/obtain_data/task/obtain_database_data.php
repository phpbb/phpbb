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
 * This class requests and validates database information from the user
 */
class obtain_database_data extends \phpbb\install\task_base implements \phpbb\install\task_interface
{
	/**
	 * @var \phpbb\install\helper\database
	 */
	protected $database_helper;

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
	 * @param \phpbb\install\helper\database						$database_helper	Installer's database helper
	 * @param \phpbb\install\helper\config							$install_config		Installer's config helper
	 * @param \phpbb\install\helper\iohandler\iohandler_interface	$iohandler			Installer's input-output handler
	 */
	public function __construct(\phpbb\install\helper\database $database_helper,
								\phpbb\install\helper\config $install_config,
								\phpbb\install\helper\iohandler\iohandler_interface $iohandler)
	{
		$this->database_helper	= $database_helper;
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
		if ($this->io_handler->get_input('submit_database', false))
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
		// Collect database data
		$dbms			= $this->io_handler->get_input('dbms', '');
		$dbhost			= $this->io_handler->get_input('dbhost', '', true);
		$dbport			= $this->io_handler->get_input('dbport', '');
		$dbuser			= $this->io_handler->get_input('dbuser', '', true);
		$dbpasswd		= $this->io_handler->get_raw_input('dbpasswd', '', true);
		$dbname			= $this->io_handler->get_input('dbname', '', true);
		$table_prefix	= $this->io_handler->get_input('table_prefix', '', true);

		// Check database data
		$user_data_vaild = $this->check_database_data($dbms, $dbhost, $dbport, $dbuser, $dbpasswd, $dbname, $table_prefix);

		// Save database data if it is correct
		if ($user_data_vaild)
		{
			$this->install_config->set('dbms', $dbms);
			$this->install_config->set('dbhost', $dbhost);
			$this->install_config->set('dbport', $dbport);
			$this->install_config->set('dbuser', $dbuser);
			$this->install_config->set('dbpasswd', $dbpasswd);
			$this->install_config->set('dbname', $dbname);
			$this->install_config->set('table_prefix', $table_prefix);
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
			$dbms			= $this->io_handler->get_input('dbms', '');
			$dbhost			= $this->io_handler->get_input('dbhost', '', true);
			$dbport			= $this->io_handler->get_input('dbport', '');
			$dbuser			= $this->io_handler->get_input('dbuser', '');
			$dbname			= $this->io_handler->get_input('dbname', '');
			$table_prefix	= $this->io_handler->get_input('table_prefix', 'phpbb_');
		}
		else
		{
			$dbms			= '';
			$dbhost			= '';
			$dbport			= '';
			$dbuser			= '';
			$dbname			= '';
			$table_prefix	= 'phpbb_';
		}

		$dbms_select = array();
		foreach ($this->database_helper->get_available_dbms() as $dbms_key => $dbms_array)
		{
			$dbms_select[] = array(
				'value'		=> $dbms_key,
				'label'		=> 'DB_OPTION_' . strtoupper($dbms_key),
				'selected'	=> ($dbms_key === $dbms),
			);
		}

		$database_form = array(
			'dbms' => array(
				'label'		=> 'DBMS',
				'type'		=> 'select',
				'options'	=> $dbms_select,
			),
			'dbhost' => array(
				'label'			=> 'DB_HOST',
				'description'	=> 'DB_HOST_EXPLAIN',
				'type'			=> 'text',
				'default'		=> $dbhost,
			),
			'dbport' => array(
				'label'			=> 'DB_PORT',
				'description'	=> 'DB_PORT_EXPLAIN',
				'type'			=> 'text',
				'default'		=> $dbport,
			),
			'dbuser' => array(
				'label'		=> 'DB_USERNAME',
				'type'		=> 'text',
				'default'	=> $dbuser,
			),
			'dbpasswd' => array(
				'label'		=> 'DB_PASSWORD',
				'type'	=> 'password',
			),
			'dbname' => array(
				'label'		=> 'DB_NAME',
				'type'		=> 'text',
				'default'	=> $dbname,
			),
			'table_prefix' => array(
				'label'			=> 'TABLE_PREFIX',
				'description'	=> 'TABLE_PREFIX_EXPLAIN',
				'type'			=> 'text',
				'default'		=> $table_prefix,
			),
			'submit_database' => array(
				'label'	=> 'SUBMIT',
				'type'	=> 'submit',
			),
		);

		$this->io_handler->add_user_form_group('DB_CONFIG', $database_form);

		// Require user interaction
		throw new user_interaction_required_exception();
	}

	/**
	 * Check database data
	 *
	 * @param string	$dbms			Selected database type
	 * @param string	$dbhost			Database host address
	 * @param int		$dbport			Database port number
	 * @param string	$dbuser			Database username
	 * @param string	$dbpass			Database password
	 * @param string	$dbname			Database name
	 * @param string	$table_prefix	Database table prefix
	 *
	 * @return bool	True if database data is correct, false otherwise
	 */
	protected function check_database_data($dbms, $dbhost, $dbport, $dbuser, $dbpass, $dbname, $table_prefix)
	{
		$available_dbms = $this->database_helper->get_available_dbms();
		$data_valid = true;

		// Check if PHP has the database extensions for the specified DBMS
		if (!isset($available_dbms[$dbms]))
		{
			$this->io_handler->add_error_message('INST_ERR_NO_DB');
			$data_valid = false;
		}

		// Validate table prefix
		$prefix_valid = $this->database_helper->validate_table_prefix($dbms, $table_prefix);
		if (is_array($prefix_valid))
		{
			foreach ($prefix_valid as $error)
			{
				$this->io_handler->add_error_message(
					$error['title'],
					(isset($error['description'])) ? $error['description'] : false
				);
			}

			$data_valid = false;
		}

		// Try to connect to database if all provided data is valid
		if ($data_valid)
		{
			$connect_test = $this->database_helper->check_database_connection($dbms, $dbhost, $dbport, $dbuser, $dbpass, $dbname, $table_prefix);
			if (is_array($connect_test))
			{
				foreach ($connect_test as $error)
				{
					$this->io_handler->add_error_message(
						$error['title'],
						(isset($error['description'])) ? $error['description'] : false
					);
				}

				$data_valid = false;
			}
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
