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

namespace phpbb\db\migration;

/**
* Abstract base class for database migrations
*
* Each migration consists of a set of schema and data changes to be implemented
* in a subclass. This class provides various utility methods to simplify editing
* a phpBB.
*/
abstract class migration implements migration_interface
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\db\tools\tools_interface */
	protected $db_tools;

	/** @var string */
	protected $table_prefix;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $php_ext;

	/** @var array Errors, if any occurred */
	protected $errors;

	/** @var array List of queries executed through $this->sql_query() */
	protected $queries = array();

	/**
	* Constructor
	*
	* @param \phpbb\config\config $config
	* @param \phpbb\db\driver\driver_interface $db
	* @param \phpbb\db\tools\tools_interface $db_tools
	* @param string $phpbb_root_path
	* @param string $php_ext
	* @param string $table_prefix
	*/
	public function __construct(\phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\db\tools\tools_interface $db_tools, $phpbb_root_path, $php_ext, $table_prefix)
	{
		$this->config = $config;
		$this->db = $db;
		$this->db_tools = $db_tools;
		$this->table_prefix = $table_prefix;

		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;

		$this->errors = array();
	}

	/**
	* {@inheritdoc}
	*/
	static public function depends_on()
	{
		return array();
	}

	/**
	* {@inheritdoc}
	*/
	public function effectively_installed()
	{
		return false;
	}

	/**
	* {@inheritdoc}
	*/
	public function update_schema()
	{
		return array();
	}

	/**
	* {@inheritdoc}
	*/
	public function revert_schema()
	{
		return array();
	}

	/**
	* {@inheritdoc}
	*/
	public function update_data()
	{
		return array();
	}

	/**
	* {@inheritdoc}
	*/
	public function revert_data()
	{
		return array();
	}

	/**
	* Wrapper for running queries to generate user feedback on updates
	*
	* @param string $sql SQL query to run on the database
	* @return mixed Query result from db->sql_query()
	*/
	protected function sql_query($sql)
	{
		$this->queries[] = $sql;

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
			if ($this->db->get_sql_error_triggered())
			{
				$this->errors[] = array(
					'sql'	=> $this->db->get_sql_error_sql(),
					'code'	=> $this->db->get_sql_error_returned(),
				);
			}
		}

		$this->db->sql_return_on_error(false);

		return $result;
	}

	/**
	* Get the list of queries run
	*
	* @return array
	*/
	public function get_queries()
	{
		return $this->queries;
	}
}
