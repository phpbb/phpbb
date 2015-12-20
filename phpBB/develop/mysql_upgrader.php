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

/**
* This file creates SQL statements to upgrade phpBB on MySQL 3.x/4.0.x to 4.1.x/5.x
*/

//
// Security message:
//
// This script is potentially dangerous.
// Remove or comment the next line (die(".... ) to enable this script.
// Do NOT FORGET to either remove this script or disable it after you have used it.
//
die("Please read the first lines of this script for instructions on how to enable it");

define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

$prefix = $table_prefix;

$newline = "\n";

if (PHP_SAPI !== 'cli')
{
	$newline = '<br>';
}

$sql = 'DESCRIBE ' . POSTS_TABLE . ' post_text';
$result = $db->sql_query($sql);

$row = $db->sql_fetchrow($result);

$db->sql_freeresult($result);

$mysql_indexer = $drop_index = false;

if (strtolower($row['Type']) === 'mediumtext')
{
	$mysql_indexer = true;
}

if (strtolower($row['Key']) === 'mul')
{
	$drop_index = true;
}

echo "USE $dbname;$newline$newline";


@set_time_limit(0);

$finder = new \phpbb\finder(new \phpbb\filesystem\filesystem(), $phpbb_root_path);
$classes = $finder->core_path('phpbb/')
	->directory('/db/migration/data')
	->get_classes();

$factory = new \phpbb\db\tools\factory();
$db_tools = $factory->get($db, true);

$schema_generator = new \phpbb\db\migration\schema_generator($classes, $config, $db, $db_tools, $phpbb_root_path, $phpEx, $table_prefix);
$schema_data = $schema_generator->get_schema();
$dbms_type_map = \phpbb\db\tools\tools::get_dbms_type_map();

foreach ($schema_data as $table_name => $table_data)
{
	$table_name = str_replace('phpbb_', $prefix, $table_name);
	// Write comment about table
	echo "# Table: '{$table_name}'$newline";

	// Create Table statement
	$generator = $textimage = false;

	// Do we need to DROP a fulltext index before we alter the table?
	if ($table_name == ($prefix . 'posts') && $drop_index)
	{
		echo "ALTER TABLE {$table_name}{$newline}";
		echo "DROP INDEX post_text,{$newline}DROP INDEX post_subject,{$newline}DROP INDEX post_content;{$newline}{$newline}";
	}

	$line = "ALTER TABLE {$table_name} $newline";

	// Table specific so we don't get overlap
	$modded_array = array();

	// Write columns one by one...
	foreach ($table_data['COLUMNS'] as $column_name => $column_data)
	{
		// Get type
		if (strpos($column_data[0], ':') !== false)
		{
			list($orig_column_type, $column_length) = explode(':', $column_data[0]);
			$column_type = sprintf($dbms_type_map['mysql_41'][$orig_column_type . ':'], $column_length);

			if (isset($dbms_type_map['mysql_40'][$orig_column_type . ':']['limit']) &&
				isset($dbms_type_map['mysql_40'][$orig_column_type . ':']['limit'][0]))
			{
				switch ($dbms_type_map['mysql_40'][$orig_column_type . ':']['limit'][0])
				{
					case 'mult':
						if (($column_length * $dbms_type_map['mysql_40'][$orig_column_type . ':']['limit'][1]) > $dbms_type_map['mysql_40'][$orig_column_type . ':']['limit'][2])
						{
							$modded_array[$column_name] = $column_type;
						}
					break;
				}
			}

			$orig_column_type .= ':';
		}
		else
		{
			$orig_column_type = $column_data[0];
			$other_column_type = $dbms_type_map['mysql_40'][$column_data[0]];
			if ($other_column_type == 'text' || $other_column_type == 'blob')
			{
				$modded_array[$column_name] = $column_type;
			}
			$column_type = $dbms_type_map['mysql_41'][$column_data[0]];
		}

		// Adjust default value if db-dependent specified
		if (is_array($column_data[1]))
		{
			$column_data[1] = (isset($column_data[1][$dbms])) ? $column_data[1][$dbms] : $column_data[1]['default'];
		}

		$line .= "\tMODIFY {$column_name} {$column_type} ";

		// For hexadecimal values do not use single quotes
		if (!is_null($column_data[1]) && substr($column_type, -4) !== 'text' && substr($column_type, -4) !== 'blob')
		{
			$line .= (strpos($column_data[1], '0x') === 0) ? "DEFAULT {$column_data[1]} " : "DEFAULT '{$column_data[1]}' ";
		}
		$line .= 'NOT NULL';

		if (isset($column_data[2]))
		{
			if ($column_data[2] == 'auto_increment')
			{
				$line .= ' auto_increment';
			}
			else if ($column_data[2] == 'true_sort')
			{
				$line .= ' COLLATE utf8_unicode_ci';
			}
			else if ($column_data[2] == 'no_sort')
			{
				$line .= ' COLLATE utf8_bin';
			}
		}
		else if (preg_match('/(?:var)?char|(?:medium)?text/i', $column_type))
		{
			$line .= ' COLLATE utf8_bin';
		}

		$line .= ",$newline";
	}

	// Write Keys
	if (isset($table_data['KEYS']))
	{
		foreach ($table_data['KEYS'] as $key_name => $key_data)
		{
			$temp = '';
			if (!is_array($key_data[1]))
			{
				$key_data[1] = array($key_data[1]);
			}

			$temp .= ($key_data[0] == 'INDEX') ? "\tADD KEY" : '';
			$temp .= ($key_data[0] == 'UNIQUE') ? "\tADD UNIQUE" : '';
			$repair = false;
			foreach ($key_data[1] as $key => $col_name)
			{
				if (isset($modded_array[$col_name]))
				{
					$repair = true;
				}
			}
			if ($repair)
			{
				$line .= "\tDROP INDEX " . $key_name . ",$newline";
				$line .= $temp;
				$line .= ' ' . $key_name . ' (' . implode(', ', $key_data[1]) . "),$newline";
			}
		}
	}

	//$line .= "\tCONVERT TO CHARACTER SET `utf8`$newline";
	$line .= "\tDEFAULT CHARSET=utf8 COLLATE=utf8_bin;$newline$newline";

	echo $line . "$newline";

	// Do we now need to re-add the fulltext index? ;)
	if ($table_name == ($prefix . 'posts') && $drop_index)
	{
		echo "ALTER TABLE $table_name ADD FULLTEXT (post_subject), ADD FULLTEXT (post_text), ADD FULLTEXT post_content (post_subject, post_text);{$newline}";
	}
}
