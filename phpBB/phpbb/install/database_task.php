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

namespace phpbb\install;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Exception as DriverException;
use Doctrine\DBAL\Driver\Statement as DriverStmt;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Statement;
use phpbb\db\doctrine\connection_factory;
use phpbb\install\helper\config;
use phpbb\install\helper\database;
use phpbb\install\helper\iohandler\iohandler_interface;

/**
 * Abstract base class for common database manipulation tasks.
 */
abstract class database_task extends task_base
{
	/** @var Connection */
	private $conn;

	/** @var iohandler_interface */
	private $io;

	/**
	 * Constructor.
	 *
	 * @param Connection			$connection	Doctrine DBAL connection.
	 * @param iohandler_interface	$io			IO handler to use.
	 * @param bool					$essential	Whether the task is essential.
	 */
	public function __construct(Connection $connection, iohandler_interface $io, bool $essential = true)
	{
		$this->conn = $connection;
		$this->io = $io;

		parent::__construct($essential);
	}

	/**
	 * Execute a SQL query.
	 *
	 * @param string $sql The SQL to execute.
	 */
	protected function exec_sql(string $sql)
	{
		try
		{
			$this->conn->executeStatement($sql);
		}
		catch (Exception $e)
		{
			$this->report_error($e->getMessage());
		}
	}

	/**
	 * Run a query and return the result object.
	 *
	 * @param string $sql SQL query.
	 *
	 * @return Result|null Result of the query.
	 */
	protected function query(string $sql) : ?Result
	{
		try
		{
			return $this->conn->executeQuery($sql);
		}
		catch (Exception $e)
		{
			$this->report_error($e->getMessage());
		}

		return null;
	}

	/**
	 * Creates a prepared statement.
	 *
	 * @param string $sql The SQL.
	 *
	 * @return Statement|null The prepared statement object or null if preparing failed
	 */
	protected function create_prepared_stmt(string $sql): ?Statement
	{
		try
		{
			return $this->conn->prepare($sql);
		}
		catch (Exception $e)
		{
			$this->report_error($e->getMessage());
		}

		return null;
	}

	/**
	 * Create and execute a prepared statement.
	 *
	 * @param string	$sql	The SQL to create the statement from.
	 * @param array		$params	The parameters to bind to it.
	 */
	protected function create_and_execute_prepared_stmt(string $sql, array $params)
	{
		try
		{
			$stmt = $this->conn->prepare($sql);
			$this->exec_prepared_stmt($stmt, $params);
		}
		catch (Exception $e)
		{
			$this->report_error($e->getMessage());
		}
	}

	/**
	 * Bind values and execute a prepared statement.
	 *
	 * @param Statement|DriverStmt	$stmt	Prepared statement.
	 * @param array					$params	Parameters.
	 */
	protected function exec_prepared_stmt($stmt, array $params)
	{
		try
		{
			foreach ($params as $name => $val)
			{
				$stmt->bindValue($name, $val);
			}
			$stmt->execute();
		}
		catch (DriverException $e)
		{
			$this->report_error($e->getMessage());
		}
	}

	/**
	 * Returns the last insert ID.
	 *
	 * @return int|null The last insert ID.
	 */
	protected function get_last_insert_id() : ?int
	{
		try
		{
			return (int) $this->conn->lastInsertId();
		}
		catch (Exception $e)
		{
			$this->report_error($e->getMessage());
		}

		return null;
	}

	/**
	 * Report a database error.
	 *
	 * @param string $message The error message.
	 */
	private function report_error(string $message)
	{
		$this->io->add_error_message('INST_ERR_DB', $message);
	}

	/**
	 * Create a Doctrine connection in the installer context.
	 *
	 * @param database	$db_helper	Database helper.
	 * @param config	$config		Config options.
	 *
	 * @return Connection Doctrine DBAL connection object.
	 */
	protected static function get_doctrine_connection(database $db_helper, config $config) : Connection
	{
		$dbms = $db_helper->get_available_dbms($config->get('dbms'));
		$dbms = $dbms[$config->get('dbms')]['DRIVER'];

		return connection_factory::get_connection_from_params(
			$dbms,
			$config->get('dbhost'),
			$config->get('dbuser'),
			$config->get('dbpasswd'),
			$config->get('dbname'),
			$config->get('dbport')
		);
	}
}
