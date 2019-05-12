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

namespace phpbb\install\module\install_filesystem\task;

use phpbb\install\exception\user_interaction_required_exception;

/**
 * Dumps config file
 */
class create_config_file extends \phpbb\install\task_base
{
	/**
	 * @var \phpbb\filesystem\filesystem_interface
	 */
	protected $filesystem;

	/**
	 * @var \phpbb\install\helper\database
	 */
	protected $db_helper;

	/**
	 * @var \phpbb\install\helper\config
	 */
	protected $install_config;

	/**
	 * @var \phpbb\install\helper\iohandler\iohandler_interface
	 */
	protected $iohandler;

	/**
	 * @var string
	 */
	protected $phpbb_root_path;

	/**
	 * @var string
	 */
	protected $php_ext;

	/**
	 * @var array
	 */
	protected $options;

	/**
	 * Constructor
	 *
	 * @param \phpbb\filesystem\filesystem_interface				$filesystem
	 * @param \phpbb\install\helper\config							$install_config
	 * @param \phpbb\install\helper\database						$db_helper
	 * @param \phpbb\install\helper\iohandler\iohandler_interface	$iohandler
	 * @param string												$phpbb_root_path
	 * @param string												$php_ext
	 * @param array													$options
	 */
	public function __construct(\phpbb\filesystem\filesystem_interface $filesystem,
								\phpbb\install\helper\config $install_config,
								\phpbb\install\helper\database $db_helper,
								\phpbb\install\helper\iohandler\iohandler_interface $iohandler,
								$phpbb_root_path,
								$php_ext,
								$options = array())
	{
		$this->install_config	= $install_config;
		$this->db_helper		= $db_helper;
		$this->filesystem		= $filesystem;
		$this->iohandler		= $iohandler;
		$this->phpbb_root_path	= $phpbb_root_path;
		$this->php_ext			= $php_ext;
		$this->options			= array_merge(array(
			'debug' => false,
			'debug_container' => false,
			'environment' => null,
		), $options);

		parent::__construct(true);
	}

	/**
	 * {@inheritdoc}
	 */
	public function run()
	{
		$config_written = true;

		// Create config.php
		$path_to_config = $this->phpbb_root_path . 'config.' . $this->php_ext;

		$fp = @fopen($path_to_config, 'w');
		if (!$fp)
		{
			$config_written = false;
		}

		$config_content = $this->get_config_data($this->options['debug'], $this->options['debug_container'], $this->options['environment']);

		if (!@fwrite($fp, $config_content))
		{
			$config_written = false;
		}

		@fclose($fp);

		// chmod config.php to be only readable
		if ($config_written)
		{
			try
			{
				$this->filesystem->phpbb_chmod($path_to_config, \phpbb\filesystem\filesystem_interface::CHMOD_READ);
			}
			catch (\phpbb\filesystem\exception\filesystem_exception $e)
			{
				// Do nothing, the user will get a notice later
			}
		}
		else
		{
			$this->iohandler->add_error_message('UNABLE_TO_WRITE_CONFIG_FILE');
			throw new user_interaction_required_exception();
		}

		// Create a lock file to indicate that there is an install in progress
		$fp = @fopen($this->phpbb_root_path . 'cache/install_lock', 'wb');
		if ($fp === false)
		{
			// We were unable to create the lock file - abort
			$this->iohandler->add_error_message('UNABLE_TO_WRITE_LOCK');
			throw new user_interaction_required_exception();
		}
		@fclose($fp);

		try
		{
			$this->filesystem->phpbb_chmod($this->phpbb_root_path . 'cache/install_lock', 0777);
		}
		catch (\phpbb\filesystem\exception\filesystem_exception $e)
		{
			// Do nothing, the user will get a notice later
		}
	}

	/**
	 * Returns the content which should be dumped to config.php
	 *
	 * @param bool		$debug 				If the debug constants should be enabled by default or not
	 * @param bool		$debug_container 	If the container should be compiled on
	 *										every page load or not
	 * @param string	$environment		The environment to use
	 *
	 * @return string	content to be written to the config file
	 */
	protected function get_config_data($debug = false, $debug_container = false, $environment = null)
	{
		$config_content = "<?php\n";
		$config_content .= "// phpBB 3.3.x auto-generated configuration file\n// Do not change anything in this file!\n";

		$dbms = $this->install_config->get('dbms');
		$db_driver = $this->db_helper->get_available_dbms($dbms);
		$db_driver = $db_driver[$dbms]['DRIVER'];

		$config_data_array = array(
			'dbms'			=> $db_driver,
			'dbhost'		=> $this->install_config->get('dbhost'),
			'dbport'		=> $this->install_config->get('dbport'),
			'dbname'		=> $this->install_config->get('dbname'),
			'dbuser'		=> $this->install_config->get('dbuser'),
			'dbpasswd'		=> $this->install_config->get('dbpasswd'),
			'table_prefix'	=> $this->install_config->get('table_prefix'),

			'phpbb_adm_relative_path'	=> 'adm/',

			'acm_type'		=> 'phpbb\cache\driver\file',
		);

		foreach ($config_data_array as $key => $value)
		{
			$config_content .= "\${$key} = '" . str_replace("'", "\\'", str_replace('\\', '\\\\', $value)) . "';\n";
		}

		$config_content .= "\n@define('PHPBB_INSTALLED', true);\n";

		if ($environment)
		{
			$config_content .= "@define('PHPBB_ENVIRONMENT', 'test');\n";
		}
		else if ($debug)
		{
			$config_content .= "@define('PHPBB_ENVIRONMENT', 'development');\n";
		}
		else
		{
			$config_content .= "@define('PHPBB_ENVIRONMENT', 'production');\n";
		}

		if ($debug_container)
		{
			$config_content .= "@define('DEBUG_CONTAINER', true);\n";
		}
		else
		{
			$config_content .= "// @define('DEBUG_CONTAINER', true);\n";
		}

		if ($environment === 'test')
		{
			$config_content .= "@define('DEBUG_TEST', true);\n";

			// Mandatory for the functional tests, will be removed by PHPBB3-12623
			$config_content .= "@define('DEBUG', true);\n";
		}

		return $config_content;
	}

	/**
	 * {@inheritdoc}
	 */
	static public function get_step_count()
	{
		return 1;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_task_lang_name()
	{
		return 'TASK_CREATE_CONFIG_FILE';
	}
}
