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

namespace phpbb\db;

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
*	$buffer = new \phpbb\db\sql_insert_buffer($db, 'test_table', 1234);
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
*/
class sql_insert_buffer
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var string */
	protected $table_name;

	/** @var int */
	protected $max_buffered_rows;

	/** @var array */
	protected $buffer = array();

	/**
	* @param \phpbb\db\driver\driver_interface $db
	* @param string          $table_name
	* @param int             $max_buffered_rows
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, $table_name, $max_buffered_rows = 500)
	{
		$this->db = $db;
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
		$this->buffer[] = $row;

		// Flush buffer if it is full or when DB does not support multi inserts.
		// In the later case, the buffer will always only contain one row.
		if (!$this->db->get_multi_insert() || sizeof($this->buffer) >= $this->max_buffered_rows)
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
		// Using bitwise |= because PHP does not have logical ||=
		$result = 0;

		foreach ($rows as $row)
		{
			$result |= (int) $this->insert($row);
		}

		return (bool) $result;
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
