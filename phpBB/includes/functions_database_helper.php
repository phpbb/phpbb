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
* The only supported table is bookmarks.
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
		$old_user_ids[$row[$column]][] = (int) $row['user_id'];
	}
	$db->sql_freeresult($result);

	$sql = "SELECT $column, user_id
		FROM $table
		WHERE $column = " . (int) $to_value;
	$result = $db->sql_query($sql);

	$new_user_ids = array();
	while ($row = $db->sql_fetchrow($result))
	{
		$new_user_ids[$row[$column]][] = (int) $row['user_id'];
	}
	$db->sql_freeresult($result);

	$queries = array();
	foreach ($from_values as $from_value)
	{
		if (!isset($old_user_ids[$from_value]))
		{
			continue;
		}
		if (empty($new_user_ids))
		{
			$sql = "UPDATE $table
				SET $column = " . (int) $to_value . "
				WHERE $column = '" . $db->sql_escape($from_value) . "'";
			$queries[] = $sql;
		}
		else
		{
			$different_user_ids = array_diff($old_user_ids[$from_value], $new_user_ids[$to_value]);
			if (!empty($different_user_ids))
			{
				$sql = "UPDATE $table
					SET $column = " . (int) $to_value . "
					WHERE $column = '" . $db->sql_escape($from_value) . "'
					AND " . $db->sql_in_set('user_id', $different_user_ids);
				$queries[] = $sql;
			}
		}
	}

	if (!empty($queries))
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

/**
* Updates rows in given table from a set of values to a new value.
* If this results in rows violating uniqueness constraints, the duplicate
* rows are merged respecting notify_status (0 takes precedence over 1).
*
* The only supported table is topics_watch.
*
* @param dbal $db Database object
* @param string $table Table on which to perform the update
* @param string $column Column whose values to change
* @param array $from_values An array of values that should be changed
* @param int $to_value The new value
* @return null
*/
function phpbb_update_rows_avoiding_duplicates_notify_status($db, $table, $column, $from_values, $to_value)
{
	$sql = "SELECT $column, user_id, notify_status
		FROM $table
		WHERE " . $db->sql_in_set($column, $from_values);
	$result = $db->sql_query($sql);

	$old_user_ids = array();
	while ($row = $db->sql_fetchrow($result))
	{
		$old_user_ids[(int) $row['notify_status']][$row[$column]][] = (int) $row['user_id'];
	}
	$db->sql_freeresult($result);

	$sql = "SELECT $column, user_id
		FROM $table
		WHERE $column = " . (int) $to_value;
	$result = $db->sql_query($sql);

	$new_user_ids = array();
	while ($row = $db->sql_fetchrow($result))
	{
		$new_user_ids[$row[$column]][] = (int) $row['user_id'];
	}
	$db->sql_freeresult($result);

	$queries = array();
	$extra_updates = array(
		0 => 'notify_status = 0',
		1 => '',
	);
	foreach ($from_values as $from_value)
	{
		foreach ($extra_updates as $notify_status => $extra_update)
		{
			if (!isset($old_user_ids[$notify_status][$from_value]))
			{
				continue;
			}
			if (empty($new_user_ids))
			{
				$sql = "UPDATE $table
					SET $column = " . (int) $to_value . "
					WHERE $column = '" . $db->sql_escape($from_value) . "'";
				$queries[] = $sql;
			}
			else
			{
				$different_user_ids = array_diff($old_user_ids[$notify_status][$from_value], $new_user_ids[$to_value]);
				if (!empty($different_user_ids))
				{
					$sql = "UPDATE $table
						SET $column = " . (int) $to_value . "
						WHERE $column = '" . $db->sql_escape($from_value) . "'
						AND " . $db->sql_in_set('user_id', $different_user_ids);
					$queries[] = $sql;
				}

				if ($extra_update)
				{
					$same_user_ids = array_diff($old_user_ids[$notify_status][$from_value], $different_user_ids);
					if (!empty($same_user_ids))
					{
						$sql = "UPDATE $table
							SET $extra_update
							WHERE $column = '" . (int) $to_value . "'
							AND " . $db->sql_in_set('user_id', $same_user_ids);
						$queries[] = $sql;
					}
				}
			}
		}
	}

	if (!empty($queries))
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
