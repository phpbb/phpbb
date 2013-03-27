<?php
/**
*
* @package dbal
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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
* Collects rows for insert into a database until the buffer size is reached.
* Then flushes the buffer to the database and starts over again.
*
* Benefits over collecting a (possibly huge) insert array and then using
* $db->sql_multi_insert() include:
*
*  - Going over max packet size of the database connection is usually prevented
*    because the data is submitted in batches.
*
*  - Reaching database connection timeout is usually prevented because
*    submission of batches talks to the database every now and then.
*
*  - Usage of less PHP memory because data no longer needed is discarded on
*    buffer flush.
*
* Attention:
* Please note that users of this class have to call flush() to flush the
* remaining rows to the database after their batch insert operation is
* finished.
*
* Usage:
* <code>
*	$buffer = new phpbb_db_sql_insert_buffer($db, 'test_table', 1234);
*
*	while (do_stuff())
*	{
*		$buffer->insert(array(
*			'column1' => 'value1',
*			'column2' => 'value2',
*		));
*	}
*
*	$buffer->flush();
* </code>
*
* @package dbal
*/
class phpbb_db_sql_insert_buffer
{
	/** @var phpbb_db_driver */
	protected $db;

	/** @var bool */
	protected $db_supports_multi_insert;

	/** @var string */
	protected $table_name;

	/** @var int */
	protected $max_buffered_rows;

	/** @var array */
	protected $buffer = array();

	/**
	* @param phpbb_db_driver $db
	* @param string          $table_name
	* @param int             $max_buffered_rows
	*/
	public function __construct(phpbb_db_driver $db, $table_name, $max_buffered_rows = 500)
	{
		$this->db = $db;
		$this->db_supports_multi_insert = $db->multi_insert;
		$this->table_name = $table_name;
		$this->max_buffered_rows = $max_buffered_rows;
	}

	/**
	* Inserts a single row into the buffer if multi insert is supported by the
	* database (otherwise an insert query is sent immediately). Then flushes
	* the buffer if the number of rows in the buffer is now greater than or
	* equal to $max_buffered_rows.
	*
	* @param array $row
	*
	* @return bool		True when some data was flushed to the database.
	*					False otherwise.
	*/
	public function insert(array $row)
	{
		if (!$this->db_supports_multi_insert)
		{
			// The database does not support multi inserts.
			// Pass data on to sql_multi_insert right away which will
			// immediately send an INSERT INTO query to the database.
			$this->db->sql_multi_insert($this->table_name, array($row));

			return true;
		}

		$this->buffer[] = $row;

		if (sizeof($this->buffer) >= $this->max_buffered_rows)
		{
			return $this->flush();
		}

		return false;
	}

	/**
	* Inserts a row set, i.e. an array of rows, by calling insert().
	*
	* Please note that it is in most cases better to use insert() instead of
	* first building a huge rowset. Or at least sizeof($rows) should be kept
	* small.
	*
	* @param array $rows 
	*
	* @return bool		True when some data was flushed to the database.
	*					False otherwise.
	*/
	public function insert_all(array $rows)
	{
		$result = false;

		foreach ($rows as $row)
		{
			$result |= $this->insert($row);
		}

		return $result;
	}

	/**
	* Flushes the buffer content to the DB and clears the buffer.
	*
	* @return bool		True when some data was flushed to the database.
	*					False otherwise.
	*/
	public function flush()
	{
		if (!empty($this->buffer))
		{
			$this->db->sql_multi_insert($this->table_name, $this->buffer);
			$this->buffer = array();

			return true;
		}

		return false;
	}
}
