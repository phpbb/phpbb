<?php
/**
*
* @package phpBB3
* @copyright (c) 2012 phpBB Group
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
* Updates rows in given table from a set of values to a new value.
* If this results in rows violating uniqueness constraints, the duplicate
* rows are eliminated.
*
* The only supported tables are bookmarks and topics_watch.
*
* @param dbal $db Database object
* @param string $table Table on which to perform the update
* @param string $column Column whose values to change
* @param array $from_values An array of values that should be changed
* @param int $to_value The new value
* @return null
*/
function phpbb_update_rows_avoiding_duplicates($db, $table, $column, $from_values, $to_value)
{
	$sql = "SELECT $column, user_id
		FROM $table
		WHERE " . $db->sql_in_set($column, $from_values);
	$result = $db->sql_query($sql);

	$old_user_ids = array();
	while ($row = $db->sql_fetchrow($result))
	{
		$old_user_ids[$row[$column]][] = $row['user_id'];
	}
	$db->sql_freeresult($result);

	$sql = "SELECT $column, user_id
		FROM $table
		WHERE $column = '" . (int) $to_value . "'";
	$result = $db->sql_query($sql);

	$new_user_ids = array();
	while ($row = $db->sql_fetchrow($result))
	{
		$new_user_ids[$row[$column]][] = $row['user_id'];
	}
	$db->sql_freeresult($result);

	$queries = array();
	$any_found = false;
	foreach ($from_values as $from_value)
	{
		if (!isset($old_user_ids[$from_value]))
		{
			continue;
		}
		$any_found = true;
		if (empty($new_user_ids))
		{
			$sql = "UPDATE $table
				SET $column = " . (int) $to_value. "
				WHERE $column = '" . $db->sql_escape($from_value) . "'";
			$queries[] = $sql;
		}
		else
		{
			$different_user_ids = array_diff($old_user_ids[$from_value], $new_user_ids[$to_value]);
			if (!empty($different_user_ids))
			{
				$sql = "UPDATE $table
					SET $column = " . (int) $to_value. "
					WHERE $column = '" . $db->sql_escape($from_value) . "'
					AND " . $db->sql_in_set('user_id', $different_user_ids);
				$queries[] = $sql;
			}
		}
	}

	if ($any_found)
	{
		$db->sql_transaction('begin');

		foreach ($queries as $sql)
		{
			$db->sql_query($sql);
		}

		$sql = "DELETE FROM $table
			WHERE " . $db->sql_in_set($column, $from_values);
		$db->sql_query($sql);

		$db->sql_transaction('commit');
	}
}
