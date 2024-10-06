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

namespace phpbb\install\module\install_database\task;

use Doctrine\DBAL\Driver\Statement as DriverStatement;
use Doctrine\DBAL\Statement;
use phpbb\filesystem\filesystem_interface;
use phpbb\install\database_task;
use phpbb\install\helper\config;
use phpbb\install\helper\container_factory;
use phpbb\install\helper\database;
use phpbb\install\helper\iohandler\iohandler_interface;
use phpbb\install\sequential_task;
use phpbb\language\language;

/**
 * Create database schema
 */
class add_config_settings extends database_task
{
	use sequential_task;

	/**
	 * @var \phpbb\db\driver\driver_interface
	 */
	protected $db;

	/**
	 * @var filesystem_interface
	 */
	protected $filesystem;

	/**
	 * @var config
	 */
	protected $install_config;

	/**
	 * @var iohandler_interface
	 */
	protected $iohandler;

	/**
	 * @var language
	 */
	protected $language;

	/**
	 * @var string
	 */
	protected $phpbb_root_path;

	/**
	 * @var string
	 */
	protected $config_table;

	/**
	 * @var Statement|DriverStatement
	 */
	protected $stmt;

	/**
	 * Constructor
	 *
	 * @param database				$db_helper			Database helper
	 * @param filesystem_interface	$filesystem			Filesystem service
	 * @param config				$install_config		Installer's config helper
	 * @param iohandler_interface	$iohandler			Installer's input-output handler
	 * @param container_factory		$container			Installer's DI container
	 * @param language				$language			Language service
	 * @param string				$phpbb_root_path	Path to phpBB's root
	 */
	public function __construct(database $db_helper,
								filesystem_interface $filesystem,
								config $install_config,
								iohandler_interface $iohandler,
								container_factory $container,
								language $language,
								string $phpbb_root_path)
	{
		$this->db				= $container->get('dbal.conn');
		$this->filesystem		= $filesystem;
		$this->install_config	= $install_config;
		$this->iohandler		= $iohandler;
		$this->language			= $language;
		$this->phpbb_root_path	= $phpbb_root_path;

		// Table names
		$this->config_table = $container->get_parameter('tables.config');

		parent::__construct(
			self::get_doctrine_connection($db_helper, $install_config),
			$this->iohandler,
			true
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function run()
	{
		$current_time = $this->install_config->get('install_board_time');
		if ($current_time === false)
		{
			$current_time = time();
			$this->install_config->set('install_board_time', $current_time);
		}

		$server_name	= $this->install_config->get('server_name');
		$referer		= $this->iohandler->get_server_variable('REFERER');

		// Calculate cookie domain
		$cookie_domain = $server_name;

		if (strpos($cookie_domain, 'www.') === 0)
		{
			$cookie_domain = substr($cookie_domain, 3);
		}

		$updates = [
			'board_startdate' => (string) $current_time,
			'board_timezone' => $this->install_config->get('admin_timezone'),
			'default_lang' => $this->install_config->get('default_lang'),

			'server_name' => $this->install_config->get('server_name'),
			'server_port' => $this->install_config->get('server_port'),

			'board_email' => $this->install_config->get('board_email'),
			'board_contact' => $this->install_config->get('board_email'),

			'cookie_domain' => $cookie_domain,
			'cookie_secure' => $this->install_config->get('cookie_secure'),

			'default_dateformat' => $this->language->lang('default_dateformat'),

			'email_enable'		=> $this->install_config->get('email_enable'),
			'smtp_delivery'		=> $this->install_config->get('smtp_delivery'),
			'smtp_host'			=> $this->install_config->get('smtp_host'),
			'smtp_port'			=> $this->install_config->get('smtp_port'),
			'smtp_auth_method'	=> $this->install_config->get('smtp_auth'),
			'smtp_username'		=> $this->install_config->get('smtp_user'),
			'smtp_password'		=> $this->install_config->get('smtp_pass'),

			'force_server_vars'	=> $this->install_config->get('force_server_vars'),
			'script_path'		=> $this->install_config->get('script_path'),
			'server_protocol'	=> $this->install_config->get('server_protocol'),

			'newest_username' => $this->install_config->get('admin_name'),

			'avatar_salt'	=> md5(mt_rand()),
			'plupload_salt'	=> md5(mt_rand()),

			'sitename'	=> $this->install_config->get('board_name'),
			'site_desc'	=> $this->install_config->get('board_description'),
		];

		$ref = substr($referer, strpos($referer, '://') + 3);
		if (!(stripos($ref, $server_name) === 0))
		{
			$updates['referer_validation'] = '0';
		}

		// We set a (semi-)unique cookie name to bypass login issues related to the cookie name.
		$cookie_name = 'phpbb3_';
		$rand_str = md5(mt_rand());
		$rand_str = str_replace('0', 'z', base_convert($rand_str, 16, 35));
		$rand_str = substr($rand_str, 0, 5);
		$cookie_name .= strtolower($rand_str);

		$updates['cookie_name'] = $cookie_name;

		// Disable avatars if upload directory is not writable
		if (!$this->filesystem->is_writable($this->phpbb_root_path . 'images/avatars/upload/'))
		{
			$updates['allow_avatar'] = '0';
			$updates['allow_avatar_upload'] = '0';
		}

		$this->stmt = $this->create_prepared_stmt(
			'UPDATE ' . $this->config_table . ' SET config_value = :value WHERE config_name = :name'
		);

		if ($this->stmt !== null)
		{
			$this->execute($this->install_config, $updates);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	protected function execute_step($key, $value) : void
	{
		$this->exec_prepared_stmt($this->stmt, [
			'name' => $key,
			'value' => $value,
		]);
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_step_count() : int
	{
		return 1;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_task_lang_name() : string
	{
		return 'TASK_ADD_CONFIG_SETTINGS';
	}
}
