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
	protected function get_records(array $config): array
	{
		$sql     = $this->get_records_sql($config);
		$result  = $this->db->sql_query($sql);
		$records = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $records;
	}

	/**
	* Generate the query that retrieves records that match given criteria
	*
	* @param  array  $config Criteria used to select records
	* @return string         SQL query
	*/
	protected function get_records_sql(array $config): string
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

		$sql   = 'SELECT ' . implode(', ', $fields) . ' FROM ' . $this->table;
		$where = $this->get_where_clauses($config, $columns['id'], $columns['text']);
		if (!empty($where))
		{
			$sql .= ' WHERE ' . implode("\nAND ", $where);
		}

		return $sql;
	}

	/**
	* Generate WHERE clauses for given set of criteria
	*
	* @param  array  $config
	* @param  string $column_id   Name for the id column, including its table alias
	* @param  string $column_text Name for the text column, including its table alias
	* @return array               Potentially empty list of SQL clauses
	*/
	protected function get_where_clauses(array $config, string $column_id, string $column_text): array
	{
		$where = [];
		if (isset($config['range-min']))
		{
			$where[] = $column_id . ' >= ' . $config['range-min'];
		}
		if (isset($config['range-max']))
		{
			$where[] = $column_id . ' <= ' . $config['range-max'];
		}
		if (isset($config['filter-text-like']))
		{
			$where[] = $column_text . ' ' . $this->db->sql_like_expression(str_replace('%', $this->db->get_any_char(), $config['filter-text-like']));
		}

		return $where;
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
