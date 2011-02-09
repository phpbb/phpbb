<?php
/**
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
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
* Group Position class, containing all functions to manage the groups in the teampage and legend.
*
* group_teampage/group_legend is an ascending list 1, 2, ..., n for groups which are displayed. 1 is the first group, n the last.
* If the value is 0 (self::GROUP_DISABLED) the group is not displayed.
* @package phpBB3
*/
class phpbb_group_positions
{
	/**
	* Group is not displayed
	*/
	const GROUP_DISABLED = 0;

	/**
	* Returns the group_{$field} for a given group, if the group exists.
	* @param string $field name of the field to be selected
	* @param int $group_id group_id of the group to be selected
	* @return int position of the group
	*/
	static function get_group_value($field, $group_id)
	{
		global $db;

		$sql = 'SELECT group_' . $field . '
			FROM ' . GROUPS_TABLE . '
			WHERE group_id = ' . (int) $group_id;
		$result = $db->sql_query($sql);
		$current_value = $db->sql_fetchfield('group_' . $field);
		$db->sql_freeresult($result);

		if ($current_value === false)
		{
			// Group not found.
			global $user;
			trigger_error($user->lang['NO_GROUP'] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		return (int) $current_value;
	}

	/**
	* Get number of groups, displayed on the teampage/legend
	* @param string $field name of the field to be counted
	* @return int value of the last group displayed
	*/
	static function get_group_count($field)
	{
		global $db;

		$sql = 'SELECT group_' . $field . '
			FROM ' . GROUPS_TABLE . '
			ORDER BY group_' . $field . ' DESC';
		$result = $db->sql_query_limit($sql, 1);
		$group_count = (int) $db->sql_fetchfield('group_' . $field);
		$db->sql_freeresult($result);

		return $group_count;
	}

	/**
	* Addes a group by group_id
	* @param string $field name of the field the group is added to
	* @param int $group_id group_id of the group to be added
	* @return void
	*/
	static function add_group($field, $group_id)
	{
		$current_value = self::get_group_value($field, $group_id);

		if ($current_value == self::GROUP_DISABLED)
		{
			global $db;

			// Group is currently not displayed, add it at the end.
			$next_value = 1 + self::get_group_count($field, $field);

			$sql = 'UPDATE ' . GROUPS_TABLE . '
				SET group_' . $field . ' = ' . $next_value . '
				WHERE group_' . $field . ' = ' . self::GROUP_DISABLED . '
					AND group_id = ' . (int) $group_id;
			$db->sql_query($sql);
		}
	}

	/**
	* Deletes a group by group_id
	* @param string $field name of the field the group is deleted from
	* @param int $group_id group_id of the group to be deleted
	* @return void
	*/
	static function delete_group($field, $group_id)
	{
		$current_value = self::get_group_value($field, $group_id);

		if ($current_value != self::GROUP_DISABLED)
		{
			global $db;

			$db->sql_transaction('begin');

			$sql = 'UPDATE ' . GROUPS_TABLE . '
				SET group_' . $field . ' = group_' . $field . ' - 1
				WHERE group_' . $field . ' > ' . $current_value;
			$db->sql_query($sql);

			$sql = 'UPDATE ' . GROUPS_TABLE . '
				SET group_' . $field . ' = ' . self::GROUP_DISABLED . '
				WHERE group_id = ' . (int) $group_id;
			$db->sql_query($sql);

			$db->sql_transaction('commit');
		}
	}

	/**
	* Moves a group up by group_id
	* @param string $field name of the field the group is moved by
	* @param int $group_id group_id of the group to be moved
	* @return void
	*/
	static function move_up($field, $group_id)
	{
		$current_value = self::get_group_value($field, $group_id);

		// Only move the group, if it is in the list and not already on top.
		if ($current_value > 1)
		{
			global $db;

			$db->sql_transaction('begin');

			$sql = 'UPDATE ' . GROUPS_TABLE . '
				SET group_' . $field . ' = group_' . $field . ' + 1
				WHERE group_' . $field . ' = ' . ($current_value - 1);
			$db->sql_query($sql);

			$sql = 'UPDATE ' . GROUPS_TABLE . '
				SET group_' . $field . ' = ' . ($current_value - 1) . '
				WHERE group_id = ' . (int) $group_id;
			$db->sql_query($sql);

			$db->sql_transaction('commit');
		}
	}

	/**
	* Moves a group down by group_id
	* @param string $field name of the field the group is moved by
	* @param int $group_id group_id of the group to be moved
	* @return void
	*/
	static function move_down($field, $group_id)
	{
		$current_value = self::get_group_value($field, $group_id);

		if ($current_value != self::GROUP_DISABLED)
		{
			global $db;

			$db->sql_transaction('begin');

			$sql = 'UPDATE ' . GROUPS_TABLE . '
				SET group_' . $field . ' = group_' . $field . ' - 1
				WHERE group_' . $field . ' = ' . ($current_value + 1);
			$db->sql_query($sql);

			if ($db->sql_affectedrows() == 1)
			{
				// Only update when we move another one up, otherwise it was the last.
				$sql = 'UPDATE ' . GROUPS_TABLE . '
					SET group_' . $field . ' = ' . ($current_value + 1) . '
					WHERE group_id = ' . (int) $group_id;
				$db->sql_query($sql);
			}

			$db->sql_transaction('commit');
		}
	}

	/**
	* Get group type language var
	* @param int $group_type group_type from the groups-table
	* @return string name of the language variable for the given group-type.
	*/
	static function group_type_language($group_type)
	{
		switch ($group_type)
		{
			case GROUP_OPEN:
				return 'GROUP_REQUEST';
			case GROUP_CLOSED:
				return 'GROUP_CLOSED';
			case GROUP_HIDDEN:
				return 'GROUP_HIDDEN';
			case GROUP_SPECIAL:
				return 'GROUP_SPECIAL';
			case GROUP_FREE:
				return 'GROUP_OPEN';
		}
	}
}
