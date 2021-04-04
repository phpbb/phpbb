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

namespace phpbb\install\module\install_data\task;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement as DriverStatement;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Statement;
use phpbb\install\database_task;
use phpbb\install\helper\config;
use phpbb\install\helper\container_factory;
use phpbb\install\helper\database;
use phpbb\install\helper\iohandler\iohandler_interface;
use phpbb\install\sequential_task;

class setup_languages extends database_task
{
	use sequential_task;

	/**
	 * @var config
	 */
	protected $config;

	/**
	 * @var Connection
	 */
	protected $db;

	/**
	 * @var iohandler_interface
	 */
	protected $iohandler;

	/**
	 * @var DriverStatement|Statement
	 */
	protected $stmt;

	/**
	 * @var array
	 */
	protected $installed_languages;

	/**
	 * @var string
	 */
	protected $profile_fields_table;

	/**
	 * @var string
	 */
	protected $profile_lang_table;

	/**
	 * Constructor.
	 *
	 * @param config				$config
	 * @param database				$db_helper
	 * @param iohandler_interface	$io
	 * @param container_factory		$container
	 */
	public function __construct(config $config,
								database $db_helper,
								iohandler_interface $io,
								container_factory $container)
	{
		$this->config		= $config;
		$this->db			= self::get_doctrine_connection($db_helper, $config);
		$this->iohandler	= $io;

		$this->profile_fields_table	= $container->get_parameter('tables.profile_fields');
		$this->profile_lang_table	= $container->get_parameter('tables.profile_fields_language');

		parent::__construct($this->db, $io, true);
	}

	/**
	 * {@inheritdoc}
	 */
	public function run()
	{
		$this->installed_languages = $this->config->get('installed_languages', []);
		$profile_fields = $this->config->get('profile_field_rows', false);
		if ($profile_fields === false)
		{
			try
			{
				$rows = $this->db->fetchAllAssociative('SELECT * FROM ' . $this->profile_fields_table);
			}
			catch (Exception $e)
			{
				$this->iohandler->add_error_message('INST_ERR_DB', $e->getMessage());
				$rows = [];
			}

			$this->config->set('profile_field_rows', $rows);
			$profile_fields = $rows;
		}

		$sql = 'INSERT INTO ' . $this->profile_lang_table
			. ' (field_id, lang_id, lang_name, lang_explain, lang_default_value)'
			. " VALUES (:field_id, :lang_id, :lang_name, '', '')";
		$this->stmt = $this->create_prepared_stmt($sql);
		$this->execute($this->config, $profile_fields);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function execute_step($key, $value) : void
	{
		foreach ($this->installed_languages as $lang_id)
		{
			$this->exec_prepared_stmt($this->stmt, [
				'field_id'	=> $value['field_id'],
				'lang_id'	=> $lang_id,

				// Remove phpbb_ from field name
				'lang_name' => strtoupper(substr($value['field_name'], 6)),
			]);
		}
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
		return 'TASK_SET_LANGUAGES';
	}
}
