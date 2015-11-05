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

namespace phpbb\textreparser;

abstract class row_based_plugin extends base
{
	/**
	* @var \phpbb\db\driver\driver_interface
	*/
	protected $db;

	/**
	 * @var string
	 */
	protected $table;

	/**
	* Constructor
	*
	* @param \phpbb\db\driver\driver_interface $db Database connection
	* @param string $table
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, $table)
	{
		$this->db = $db;
		$this->table = $table;
	}

	/**
	* Return the name of the column that correspond to each field
	*
	* @return array
	*/
	abstract public function get_columns();

	/**
	* {@inheritdoc}
	*/
	public function get_max_id()
	{
		$columns = $this->get_columns();

		$sql = 'SELECT MAX(' . $columns['id'] . ') AS max_id FROM ' . $this->table;
		$result = $this->db->sql_query($sql);
		$max_id = (int) $this->db->sql_fetchfield('max_id');
		$this->db->sql_freeresult($result);

		return $max_id;
	}

	/**
	* {@inheritdoc}
	*/
	protected function get_records_by_range($min_id, $max_id)
	{
		$sql = $this->get_records_by_range_query($min_id, $max_id);
		$result = $this->db->sql_query($sql);
		$records = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $records;
	}

	/**
	* Generate the query that retrieves all records for given range
	*
	* @param  integer $min_id Lower bound
	* @param  integer $max_id Upper bound
	* @return string          SQL query
	*/
	protected function get_records_by_range_query($min_id, $max_id)
	{
		$columns = $this->get_columns();
		$fields  = array();
		foreach ($columns as $field_name => $column_name)
		{
			if ($column_name === $field_name)
			{
				$fields[] = $column_name;
			}
			else
			{
				$fields[] = $column_name . ' AS ' . $field_name;
			}
		}

		$sql = 'SELECT ' . implode(', ', $fields) . '
			FROM ' . $this->table . '
			WHERE ' . $columns['id'] . ' BETWEEN ' . $min_id . ' AND ' . $max_id;

		return $sql;
	}

	/**
	* {@inheritdoc}
	*/
	protected function save_record(array $record)
	{
		$columns = $this->get_columns();

		$sql = 'UPDATE ' . $this->table . '
			SET ' . $columns['text'] . " = '" . $this->db->sql_escape($record['text']) . "'
			WHERE " . $columns['id'] . ' = ' . $record['id'];
		$this->db->sql_query($sql);
	}
}
