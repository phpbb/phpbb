<?php
/**
 *
 * This file is part of the phpBB Forum Software package.
 *
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 * For full copyright and license information, please see
 * the docs/CREDITS.txt file.
 *
 */

namespace phpbb\install\converter\module\converter_obtain_data\task;

use phpbb\install\exception\user_interaction_required_exception;
use phpbb\config_php_file;

/**
 * This class requests and validates database information from the user
 */
class obtain_data extends \phpbb\install\task_base implements \phpbb\install\task_interface
{
	/**
	 * @var \phpbb\install\helper\database
	 */
	protected $helper;

	/**
	 * @var \phpbb\install\helper\config
	 */
	protected $install_config;

	/**
	 * @var \phpbb\install\helper\iohandler\iohandler_interface
	 */
	protected $io_handler;

	protected $converter;

	protected $phpbb_root_path;

	protected $container_factory;

	protected $yaml_queue;


	/**
	 * Constructor
	 *
	 * @param \phpbb\install\helper\database                      $database_helper Installer's database helper
	 * @param \phpbb\install\helper\config                        $install_config  Installer's config helper
	 * @param \phpbb\install\helper\iohandler\iohandler_interface $iohandler       Installer's input-output handler
	 */
	public function __construct($converter, $helper,
		\phpbb\install\helper\config $install_config,
		\phpbb\install\helper\iohandler\iohandler_interface $iohandler,
		$container_factory, $phpbb_root_path, $php_ext)
	{
		$this->helper = $helper;
		$this->install_config = $install_config;
		$this->io_handler = $iohandler;
		$this->converter = $converter;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->container_factory = $container_factory;
		$this->config_php_file = new config_php_file($phpbb_root_path,$php_ext);

		parent::__construct(true);
	}

	/**
	 * {@inheritdoc}
	 */
	public function run()
	{
		//do not use var_dump as it adds an extra < which causes issues with the js.
		$this->set_menus();
		$this->set_source_database(); // @todo Validation code to be added.
		$this->set_destination_database();
		$this->init_converter();
		$url = append_sid($this->helper->route('phpbb_converter_start'));
		$this->io_handler->add_success_message('Database Configuration Completed',array(
			'CONVERTER_START',
			$url,
		));
		$this->io_handler->send_response(true);

	}

	public function set_menus()
	{
		$this->install_config->set_finished_navigation_stage(array('converter', 0, 'home'));
		$this->io_handler->set_finished_stage_menu(array('converter', 0, 'home'));
		$this->install_config->set_active_navigation_stage(array('converter', 0, 'list'));
		$this->io_handler->set_active_stage_menu(array('converter', 0, 'list'));
	}

	public function set_source_database()
	{
		// todo get_input only for ajax. For CLI we shall use a config file directly.
		$db_name = $this->io_handler->get_input('db_name','NULL');
		$db_user = $this->io_handler->get_input('db_user','NULL');
		$db_pass = $this->io_handler->get_input('db_pass','NULL');
		$db_host = $this->io_handler->get_input('db_host','NULL');
		$credentials_source = array(
			'dbname'   => $db_name,
			'user'     => $db_user,
			'password' => $db_pass,
			'host'     => $db_host,
			'driver'   => 'pdo_mysql',// todo: Convert from phpbb driver to Doctrine driver names.
		);

		$this->helper->set_source_db($credentials_source);


	}

	public function set_destination_database()
	{
		$credentials_destination =array(
			'dbname'   => 'phpBBgsoc_dest',//todo Not changed since during testing we have another DB not the phpBB DB
			'user'     => $this->config_php_file->get('dbuser'),
			'password' => $this->config_php_file->get('dbpasswd'),
			'host'     => $this->config_php_file->get('dbhost'),
			'driver'   => 'pdo_mysql', //driver value from phpBB and DBAL different. @todo an array of key->value pairs will be provided.
		);
		$this->helper->set_destination_db($credentials_destination);
	}

	public function init_converter()
	{
		$this->yaml_queue = $this->converter->get_yaml_queue();
		$this->helper->set_yaml_queue($this->yaml_queue);

		$this->helper->set_conversion_status(true);
		$this->helper->set_file_index(0);
		//$this->helper->set_total_files(count($this->yaml_queue));
		$this->helper->set_chunk_status(false);
		$this->helper->set_current_chunk(0);
		$this->io_handler->add_log_message('Config Files to be converted',$this->yaml_queue);
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
