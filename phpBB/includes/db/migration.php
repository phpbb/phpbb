<?php
/**
*
* @package db
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Abstract base class for database migrations
*
* Each migration consists of a set of schema and data changes to be implemented
* in a subclass. This class provides various utility methods to simplify editing
* a phpBB.
*
* @package db
*/
abstract class phpbb_db_migration
{
	protected $config;
	protected $db;
	protected $db_tools;
	protected $table_prefix;

	protected $phpbb_root_path;
	protected $php_ext;

	protected $errors;

	/**
	* Migration constructor
	*
	* @param \Symfony\Component\DependencyInjection\ContainerInterface	$container	Container supplying dependencies
	*/
	public function __construct(\Symfony\Component\DependencyInjection\ContainerInterface $container)
	{
		$this->config = $this->container->get('config');
		$this->db = $this->container->get('dbal.conn');
		$this->db_tools = $this->container->get('dbal.tools');
		$this->table_prefix = $this->container->getParameters('core.table_prefix');

		$this->phpbb_root_path = $this->container->getParameters('core.root_path');
		$this->php_ext = $this->container->getParameters('core.php_ext');

		$this->errors = array();
	}

	/**
	* Defines other migrationsto be applied first (abstract method)
	*
	* @return array An array of migration class names
	*/
	public function depends_on()
	{
		return array();
	}

	/**
	* Updates the database schema by providing a set of change instructions
	*
	* @return array
	*/
	public function update_schema()
	{
		return array();
	}

	/**
	* Updates data by returning a list of instructions to be executed
	*
	* @return array
	*/
	public function update_data()
	{
	}

	/**
	* Wrapper for running queries to generate user feedback on updates
	*/
	protected function sql_query($sql)
	{
		if (defined('DEBUG'))
		{
			echo "<br />\n{$sql}\n<br />";
		}

		$this->db->sql_return_on_error(true);

		if ($sql === 'begin')
		{
			$result = $this->db->sql_transaction('begin');
		}
		else if ($sql === 'commit')
		{
			$result = $this->db->sql_transaction('commit');
		}
		else
		{
			$result = $this->db->sql_query($sql);
			if ($this->db->sql_error_triggered)
			{
				$this->errors[] = array(
					'sql'	=> $this->db->sql_error_sql,
					'code'	=> $this->db->sql_error_returned,
				);
			}
		}

		$this->db->sql_return_on_error(false);

		return $result;
	}
}
