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
	 *    drop_tables: Drop tables
	 *    add_tables: Add tables
	 *    change_columns: Column changes (only type, not name)
	 *    add_columns: Add columns to a table
	 *    drop_keys: Dropping keys
	 *    drop_columns: Removing/Dropping columns
	 *    add_primary_keys: adding primary keys
	 *    add_unique_index: adding an unique index
	 *    add_index: adding an index (can be column:index_size if you need to provide size)
	 *
	 * The values are in this format:
	 *        {TABLE NAME}        => array(
	 *            {COLUMN NAME}        => array({COLUMN TYPE}, {DEFAULT VALUE}, {OPTIONAL VARIABLES}),
	 *            {KEY/INDEX NAME}    => array({COLUMN NAMES}),
	 *        )
	 *
	 *
	 * @param array $schema_changes
	 *
	 * @return bool|string[]
	 */
	public function perform_schema_changes(array $schema_changes);

	/**
	 * Gets a list of tables in the database.
	 *
	 * @return array        Array of table names  (all lower case)
	 */
	public function sql_list_tables(): array;

	/**
	 * Check if table exists
	 *
	 * @param string $table_name The table name to check for
	 *
	 * @return bool True if table exists, else false
	 */
	public function sql_table_exists(string $table_name): bool;

	/**
	 * Create SQL Table
	 *
	 * @param string $table_name The table name to create
	 * @param array  $table_data Array containing table data.
	 *
	 * @return bool|string[] True if the statements have been executed
	 */
	public function sql_create_table(string $table_name, array $table_data);

	/**
	 * Drop Table
	 *
	 * @param string $table_name The table name to drop
	 *
	 * @return bool|string[]    True if the statements have been executed
	 */
	public function sql_table_drop(string $table_name);

	/**
	 * Gets a list of columns of a table.
	 *
	 * @param string $table_name Table name
	 *
	 * @return array        Array of column names (all lower case)
	 */
	public function sql_list_columns(string $table_name): array;

	/**
	 * Check whether a specified column exist in a table
	 *
	 * @param string $table_name  Table to check
	 * @param string $column_name Column to check
	 *
	 * @return bool        True if column exists, false otherwise
	 */
	public function sql_column_exists(string $table_name, string $column_name): bool;

	/**
	 * Add new column
	 *
	 * @param string $table_name  Table to modify
	 * @param string $column_name Name of the column to add
	 * @param array  $column_data Column data
	 *
	 * @return bool|string[]    True if the statements have been executed
	 */
	public function sql_column_add(string $table_name, string $column_name, array $column_data);

	/**
	 * Change column type (not name!)
	 *
	 * @param string $table_name  Table to modify
	 * @param string $column_name Name of the column to modify
	 * @param array  $column_data Column data
	 *
	 * @return bool|string[]    True if the statements have been executed
	 */
	public function sql_column_change(string $table_name, string $column_name, array $column_data);

	/**
	 * Drop column
	 *
	 * @param string $table_name  Table to modify
	 * @param string $column_name Name of the column to drop
	 *
	 * @return bool|string[]    True if the statements have been executed
	 */
	public function sql_column_remove(string $table_name, string $column_name);

	/**
	 * List all of the indices that belong to a table
	 *
	 * NOTE: does not list
	 * - UNIQUE indices
	 * - PRIMARY keys
	 *
	 * @param string $table_name Table to check
	 *
	 * @return array        Array with index names
	 */
	public function sql_list_index(string $table_name): array;

	/**
	 * Check if a specified index exists in table. Does not return PRIMARY KEY and UNIQUE indexes.
	 *
	 * @param string $table_name Table to check the index at
	 * @param string $index_name The index name to check
	 *
	 * @return bool            True if index exists, else false
	 */
	public function sql_index_exists(string $table_name, string $index_name): bool;

	/**
	 * Add index
	 *
	 * @param string       $table_name Table to modify
	 * @param string       $index_name Name of the index to create
	 * @param string|array $column     Either a string with a column name, or an array with columns
	 *
	 * @return bool|string[]    True if the statements have been executed
	 */
	public function sql_create_index(string $table_name, string $index_name, $column);

	/**
	 * Rename index
	 *
	 * @param string $table_name     Table to modify
	 * @param string $index_name_old Name of the index to rename
	 * @param string $index_name_new New name of the index being renamed
	 *
	 * @return bool|string[]    True if the statements have been executed
	 */
	public function sql_rename_index(string $table_name, string $index_name_old, string $index_name_new);

	/**
	 * Drop Index
	 *
	 * @param string $table_name Table to modify
	 * @param string $index_name Name of the index to delete
	 *
	 * @return bool|string[]    True if the statements have been executed
	 */
	public function sql_index_drop(string $table_name, string $index_name);

	/**
	 * Check if a specified index exists in table.
	 *
	 * NOTE: Does not return normal and PRIMARY KEY indexes
	 *
	 * @param string $table_name Table to check the index at
	 * @param string $index_name The index name to check
	 *
	 * @return bool|string[] True if index exists, else false
	 */
	public function sql_unique_index_exists(string $table_name, string $index_name);

	/**
	 * Add unique index
	 *
	 * @param string       $table_name Table to modify
	 * @param string       $index_name Name of the unique index to create
	 * @param string|array $column     Either a string with a column name, or an array with columns
	 *
	 * @return bool|string[]    True if the statements have been executed
	 */
	public function sql_create_unique_index(string $table_name, string $index_name, $column);

	/**
	 * Add primary key
	 *
	 * @param string       $table_name Table to modify
	 * @param string|array $column     Either a string with a column name, or an array with columns
	 *
	 * @return bool|string[]    True if the statements have been executed
	 */
	public function sql_create_primary_key(string $table_name, $column);

	/**
	 * Drop primary key
	 *
	 * @param string       $table_name Table to modify
	 *
	 * @return bool|string[]    True if the statements have been executed
	 */
	public function sql_drop_primary_key(string $table_name);

	/**
	 * Truncate the table
	 *
	 * @param string $table_name
	 * @return void
	 */
	public function sql_truncate_table(string $table_name): void;

	/**
	 * Gets current Doctrine DBAL connection
	 *
	 * @return \Doctrine\DBAL\Connection
	 */
	public function get_connection(): \Doctrine\DBAL\Connection;

	/**
	 * Adds prefix to a string if needed
	 *
	 * @param string	$name		String to add the prefix to
	 * @param string	$prefix		Prefix to add
	 *
	 * @return string	Prefixed name
	 */
	public static function add_prefix(string $name, string $prefix): string;

	/**
	 * Removes prefix from string if exists. If prefix is empty,
	 * the first part of the string ending with underscore will be removed.
	 *
	 * @param string	$name		String to remove the prefix from
	 * @param string	$prefix		Prefix to remove if any
	 *
	 * @return string	Prefixless string
	 */
	public static function remove_prefix(string $name, string $prefix = ''): string;

	/**
	 * Sets table prefix
	 *
	 * @param string	$table_prefix	String to test vs prefix
	 *
	 * @return void
	 */
	public function set_table_prefix(string $table_prefix): void;

	/**
	 * Adds short table name prefix to an index name if needed
	 *
	 * @param string	$table_name	Table name with or without tables prefix
	 * @param string	$index_name	Index name
	 *
	 * @return string	Index name prefixed with the short table name
	 */
	public static function normalize_index_name(string $table_name, string $index_name): string;
}
