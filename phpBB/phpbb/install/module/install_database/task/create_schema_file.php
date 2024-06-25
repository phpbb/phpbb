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

use phpbb\db\doctrine\connection_factory;
use phpbb\install\exception\resource_limit_reached_exception;

/**
 * Create database schema
 */
class create_schema_file extends \phpbb\install\task_base
{
	/**
	 * @var \phpbb\install\helper\config
	 */
	protected $config;

	/**
	 * @var \phpbb\db\driver\driver_interface
	 */
	protected $db;

	/**
	 * @var \Doctrine\DBAL\Connection
	 */
	protected $db_doctrine;

	/**
	 * @var \phpbb\filesystem\filesystem_interface
	 */
	protected $filesystem;

	/**
	 * @var string
	 */
	protected $phpbb_root_path;

	/**
	 * @var string
	 */
	protected $php_ext;

	/**
	 * @var bool
	 */
	protected $finder_cache;

	/**
	 * Constructor
	 *
	 * @param \phpbb\install\helper\config							$config				Installer's config provider
	 * @param \phpbb\install\helper\database						$db_helper			Installer's database helper
	 * @param \phpbb\filesystem\filesystem_interface				$filesystem			Filesystem service
	 * @param string												$phpbb_root_path	Path phpBB's root
	 * @param string												$php_ext			Extension of PHP files
	 * @param bool													$finder_cache		Flag whether to cache finder
	 */
	public function __construct(\phpbb\install\helper\config $config,
								\phpbb\install\helper\database $db_helper,
								\phpbb\filesystem\filesystem_interface $filesystem,
								$phpbb_root_path,
								$php_ext,
								$finder_cache)
	{
		$dbms = $db_helper->get_available_dbms($config->get('dbms'));
		$dbms = $dbms[$config->get('dbms')]['DRIVER'];

		$this->db				= new $dbms();
		$this->db->sql_connect(
			$config->get('dbhost'),
			$config->get('dbuser'),
			$config->get('dbpasswd'),
			$config->get('dbname'),
			$config->get('dbport'),
			false,
			false
		);

		$this->db_doctrine = connection_factory::get_connection_from_params(
			$config->get('dbms'),
			$config->get('dbhost'),
			$config->get('dbuser'),
			$config->get('dbpasswd'),
			$config->get('dbname'),
			$config->get('dbport')
		);

		$this->config			= $config;
		$this->filesystem		= $filesystem;
		$this->phpbb_root_path	= $phpbb_root_path;
		$this->php_ext			= $php_ext;
		$this->finder_cache		= $finder_cache;

		parent::__construct(true);
	}

	/**
	 * {@inheritdoc}
	 */
	public function run()
	{
		// Generate database schema
		if ($this->filesystem->exists($this->phpbb_root_path . 'install/schemas/schema.json'))
		{
			$db_table_schema = @file_get_contents($this->phpbb_root_path . 'install/schemas/schema.json');
			$this->config->set('change_table_prefix', true);
		}
		else
		{
			global $table_prefix;

			// As this task may take a large amount of time to complete refreshing the page might be necessary for some
			// server configurations with limited resources
			if (!$this->config->get('pre_schema_forced_refresh', false))
			{
				if ($this->config->get_time_remaining() < 5)
				{
					$this->config->set('pre_schema_forced_refresh', true);
					throw new resource_limit_reached_exception();
				}
			}

			$table_prefix = $this->config->get('table_prefix');

			if (!defined('CONFIG_TABLE'))
			{
				// We need to include the constants file for the table constants
				// when we generate the schema from the migration files.
				include ($this->phpbb_root_path . 'includes/constants.' . $this->php_ext);
			}

			$finder_factory = new \phpbb\finder\factory(null, $this->finder_cache, $this->phpbb_root_path, $this->php_ext);
			$finder = $finder_factory->get();
			$migrator_classes = $finder->core_path('phpbb/db/migration/data/')->get_classes();
			$factory = new \phpbb\db\tools\factory();
			$db_tools = $factory->get($this->db_doctrine, true);
			$tables_data = \Symfony\Component\Yaml\Yaml::parseFile($this->phpbb_root_path . '/config/default/container/tables.yml');
			$tables = [];
			foreach ($tables_data['parameters'] as $parameter => $table)
			{
				$tables[str_replace('tables.', '', (string) $parameter)] = str_replace('%core.table_prefix%', $table_prefix, $table);
			}

			$schema_generator = new \phpbb\db\migration\schema_generator(
				$migrator_classes,
				new \phpbb\config\config(array()),
				$this->db,
				$db_tools,
				$this->phpbb_root_path,
				$this->php_ext,
				$table_prefix,
				$tables
			);
			$db_table_schema = $schema_generator->get_schema();
			$db_table_schema = json_encode($db_table_schema, JSON_PRETTY_PRINT);

			$this->config->set('change_table_prefix', false);
		}

		$fp = @fopen($this->phpbb_root_path . 'store/schema.json', 'wb');
		if (!$fp)
		{
			throw new \Exception('INST_SCHEMA_FILE_NOT_WRITABLE');
		}

		fwrite($fp, $db_table_schema);
		fclose($fp);
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_step_count()
	{
		return 1;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_task_lang_name()
	{
		return 'TASK_CREATE_DATABASE_SCHEMA_FILE';
	}
}
