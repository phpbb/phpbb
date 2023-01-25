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

use Doctrine\DBAL\Driver\Statement as DriverStatement;
use Doctrine\DBAL\Statement;
use phpbb\install\database_task;
use phpbb\install\helper\config;
use phpbb\install\helper\container_factory;
use phpbb\install\helper\database;
use phpbb\install\helper\iohandler\iohandler_interface;
use phpbb\install\sequential_task;
use phpbb\language\language_file_helper;

class add_languages extends database_task
{
	use sequential_task;

	/**
	 * @var config
	 */
	protected $config;

	/**
	 * @var iohandler_interface
	 */
	protected $iohandler;

	/**
	 * @var language_file_helper
	 */
	protected $language_helper;

	/**
	 * @var string
	 */
	protected $lang_table;

	/**
	 * @var DriverStatement|Statement
	 */
	protected $stmt;

	/**
	 * Constructor
	 *
	 * @param config					$config				Installer config.
	 * @param database					$db_helper			Database helper.
	 * @param iohandler_interface		$iohandler			Installer's input-output handler
	 * @param container_factory			$container			Installer's DI container
	 * @param language_file_helper		$language_helper	Language file helper service
	 */
	public function __construct(config $config,
								database $db_helper,
								iohandler_interface $iohandler,
								container_factory $container,
								language_file_helper $language_helper)
	{
		$this->config			= $config;
		$this->iohandler		= $iohandler;
		$this->language_helper	= $language_helper;
		$this->lang_table		= $container->get_parameter('tables.lang');

		parent::__construct(self::get_doctrine_connection($db_helper, $config), $this->iohandler,true);
	}

	/**
	 * {@inheritdoc}
	 */
	public function run()
	{
		$languages = $this->language_helper->get_available_languages();
		$sql = 'INSERT INTO ' . $this->lang_table
			. ' (lang_iso, lang_dir, lang_english_name, lang_local_name, lang_author)'
			. ' VALUES (:lang_iso, :lang_dir, :lang_english_name, :lang_local_name, :lang_author)';
		$this->stmt = $this->create_prepared_stmt($sql);
		$this->execute($this->config, $languages);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function execute_step($key, $value) : void
	{
		$this->exec_prepared_stmt($this->stmt, [
			'lang_iso'			=> $value['iso'],
			'lang_dir'			=> $value['iso'],
			'lang_english_name'	=> htmlspecialchars($value['name'], ENT_COMPAT),
			'lang_local_name'	=> htmlspecialchars($value['local_name'], ENT_COMPAT, 'UTF-8'),
			'lang_author'		=> htmlspecialchars($value['author'], ENT_COMPAT, 'UTF-8'),
		]);

		$installed_languages = $this->config->get('installed_languages', []);
		$installed_languages[] = (int) $this->get_last_insert_id();
		$this->config->set('installed_languages', $installed_languages);
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
		return 'TASK_ADD_LANGUAGES';
	}
}
