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
namespace phpbb\db\doctrine;

use InvalidArgumentException;

class table_helper
{
	/**
	 * Converts phpBB's column representation to Doctrine's representation.
	 *
	 * @param array $column_data Column data.
	 *
	 * @return array<string, array> A pair of type and array of column options.
	 * @psalm-return array{string, array}
	 */
	public static function convert_column_data(array $column_data, string $dbms_layer): array
	{
		$options = self::resolve_dbms_specific_options($column_data, $dbms_layer);
		list($type, $opts) = type_converter::convert($column_data[0], $dbms_layer);
		$options = array_merge($options, $opts);
		return [$type, $options];
	}

	/**
	 * Resolve DBMS specific options in column data.
	 *
	 * @param array  $column_data Original column data.
	 * @param string $dbms_layer  DBMS layer name.
	 *
	 * @return array Doctrine column options.
	 */
	private static function resolve_dbms_specific_options(array $column_data, string $dbms_layer): array
	{
		$doctrine_options = [];

		if (is_array($column_data[1]))
		{
			$column_data[1] = self::get_default_column_option($column_data[1], $dbms_layer);
		}

		if (!is_null($column_data[1]))
		{
			$doctrine_options['default'] = $column_data[1];
			$doctrine_options['notnull'] = true;
		}
		else
		{
			$doctrine_options['notnull'] = false;
		}

		$non_string_pattern = '/^[a-z]*(?:int|decimal|bool|timestamp)(?::[0-9]+)?$/';
		if ($dbms_layer === 'oracle'
			&& !preg_match($non_string_pattern, strtolower($column_data[0]))
			&& array_key_exists('default', $doctrine_options)
			&& $doctrine_options['default'] === '')
		{
			// Not null is true by default and Oracle does not allow empty strings in not null columns
			$doctrine_options['notnull'] = false;
		}

		if (isset($column_data[2]))
		{
			if ($column_data[2] === 'auto_increment')
			{
				$doctrine_options['autoincrement'] = true;
			}
			else if ($dbms_layer === 'mysql' && $column_data[2] === 'true_sort')
			{
				$doctrine_options['platformoptions']['collation'] = 'utf8_unicode_ci';
			}
		}

		return $doctrine_options;
	}

	/**
	 * Returns the DBMS specific default value for a column definition.
	 *
	 * @param array  $default_options Database specific default value options.
	 * @param string $dbms_layer      Name of the DBMS layer.
	 *
	 * @return mixed Default value for the current DBMS.
	 *
	 * @throws InvalidArgumentException When `$schema_name` contains an invalid legacy DBMS name.
	 */
	private static function get_default_column_option(array $default_options, string $dbms_layer)
	{
		switch ($dbms_layer)
		{
			case 'mysql':
				return array_key_exists('mysql_41', $default_options)
					? $default_options['mysql_41']
					: $default_options['default'];
			case 'oracle':
				return array_key_exists('oracle', $default_options)
					? $default_options['oracle']
					: $default_options['default'];
			case 'postgresql':
				return array_key_exists('postgres', $default_options)
					? $default_options['postgres']
					: $default_options['default'];
			case 'mssql':
				return array_key_exists('mssqlnative', $default_options)
					? $default_options['mssqlnative']
					: (array_key_exists('mssql', $default_options) ? $default_options['mssql'] : $default_options['default']);
			case 'sqlite':
				return array_key_exists('sqlite3', $default_options)
					? $default_options['sqlite3']
					: $default_options['default'];
			default:
				throw new InvalidArgumentException('Invalid schema name.');
		}
	}

	/**
	 * Private constructor. Call methods of table_helper statically.
	 */
	private function __construct()
	{
	}
}
