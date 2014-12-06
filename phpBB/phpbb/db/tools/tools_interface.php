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

namespace phpbb\db\tools;

/**
 * Interface for a Database Tools for handling cross-db actions such as altering columns, etc.
 */
interface tools_interface
{
	/**
	 * Handle passed database update array.
	 * Expected structure...
	 * Key being one of the following
	 *	drop_tables: Drop tables
	 *	add_tables: Add tables
	 *	change_columns: Column changes (only type, not name)
	 *	add_columns: Add columns to a table
	 *	drop_keys: Dropping keys
	 *	drop_columns: Removing/Dropping columns
	 *	add_primary_keys: adding primary keys
	 *	add_unique_index: adding an unique index
	 *	add_index: adding an index (can be column:index_size if you need to provide size)
	 *
	 * The values are in this format:
	 *		{TABLE NAME}		=> array(
	 *			{COLUMN NAME}		=> array({COLUMN TYPE}, {DEFAULT VALUE}, {OPTIONAL VARIABLES}),
	 *			{KEY/INDEX NAME}	=> array({COLUMN NAMES}),
	 *		)
	 *
	 *
	 * @param array $schema_changes
	 * @return null
	 */
	public function perform_schema_changes($schema_changes);

	/**
	 * Gets a list of tables in the database.
	 *
	 * @return array		Array of table names  (all lower case)
	 */
	public function sql_list_tables();

	/**
	 * Check if table exists
	 *
	 * @param string	$table_name	The table name to check for
	 * @return bool true if table exists, else false
	 */
	public function sql_table_exists($table_name);

	/**
	 * Create SQL Table
	 *
	 * @param string	$table_name		The table name to create
	 * @param array		$table_data		Array containing table data.
	 * @return array|true	Statements to run, or true if the statements have been executed
	 */
	public function sql_create_table($table_name, $table_data);

	/**
	 * Drop Table
	 *
	 * @param string	$table_name		The table name to drop
	 * @return array|true	Statements to run, or true if the statements have been executed
	 */
	public function sql_table_drop($table_name);

	/**
	 * Gets a list of columns of a table.
	 *
	 * @param string $table_name	Table name
	 * @return array		Array of column names (all lower case)
	 */
	public function sql_list_columns($table_name);

	/**
	 * Check whether a specified column exist in a table
	 *
	 * @param string	$table_name		Table to check
	 * @param string	$column_name	Column to check
	 * @return bool		True if column exists, false otherwise
	 */
	public function sql_column_exists($table_name, $column_name);

	/**
	 * Add new column
	 *
	 * @param string	$table_name		Table to modify
	 * @param string	$column_name	Name of the column to add
	 * @param array		$column_data	Column data
	 * @param bool		$inline			Whether the query should actually be run,
	 *						or return a string for adding the column
	 * @return array|true	Statements to run, or true if the statements have been executed
	 */
	public function sql_column_add($table_name, $column_name, $column_data, $inline = false);

	/**
	 * Change column type (not name!)
	 *
	 * @param string	$table_name		Table to modify
	 * @param string	$column_name	Name of the column to modify
	 * @param array		$column_data	Column data
	 * @param bool		$inline			Whether the query should actually be run,
	 *						or return a string for modifying the column
	 * @return array|true	Statements to run, or true if the statements have been executed
	 */
	public function sql_column_change($table_name, $column_name, $column_data, $inline = false);

	/**
	 * Drop column
	 *
	 * @param string	$table_name		Table to modify
	 * @param string	$column_name	Name of the column to drop
	 * @param bool		$inline			Whether the query should actually be run,
	 *						or return a string for deleting the column
	 * @return array|true	Statements to run, or true if the statements have been executed
	 */
	public function sql_column_remove($table_name, $column_name, $inline = false);

	/**
	 * List all of the indices that belong to a table
	 *
	 * NOTE: does not list
	 * - UNIQUE indices
	 * - PRIMARY keys
	 *
	 * @param string	$table_name		Table to check
	 * @return array		Array with index names
	 */
	public function sql_list_index($table_name);

	/**
	 * Check if a specified index exists in table. Does not return PRIMARY KEY and UNIQUE indexes.
	 *
	 * @param string	$table_name		Table to check the index at
	 * @param string	$index_name		The index name to check
	 * @return bool			True if index exists, else false
	 */
	public function sql_index_exists($table_name, $index_name);

	/**
	 * Add index
	 *
	 * @param string	$table_name		Table to modify
	 * @param string	$index_name		Name of the index to create
	 * @param string|array	$column		Either a string with a column name, or an array with columns
	 * @return array|true	Statements to run, or true if the statements have been executed
	 */
	public function sql_create_index($table_name, $index_name, $column);

	/**
	 * Drop Index
	 *
	 * @param string	$table_name		Table to modify
	 * @param string	$index_name		Name of the index to delete
	 * @return array|true	Statements to run, or true if the statements have been executed
	 */
	public function sql_index_drop($table_name, $index_name);

	/**
	 * Check if a specified index exists in table.
	 *
	 * NOTE: Does not return normal and PRIMARY KEY indexes
	 *
	 * @param string	$table_name		Table to check the index at
	 * @param string	$index_name		The index name to check
	 * @return bool True if index exists, else false
	 */
	public function sql_unique_index_exists($table_name, $index_name);

	/**
	 * Add unique index
	 *
	 * @param string	$table_name		Table to modify
	 * @param string	$index_name		Name of the unique index to create
	 * @param string|array	$column		Either a string with a column name, or an array with columns
	 * @return array|true	Statements to run, or true if the statements have been executed
	 */
	public function sql_create_unique_index($table_name, $index_name, $column);

	/**
	 * Add primary key
	 *
	 * @param string	$table_name		Table to modify
	 * @param string|array	$column		Either a string with a column name, or an array with columns
	 * @param bool		$inline			Whether the query should actually be run,
	 *						or return a string for creating the key
	 * @return array|true	Statements to run, or true if the statements have been executed
	 */
	public function sql_create_primary_key($table_name, $column, $inline = false);
}
