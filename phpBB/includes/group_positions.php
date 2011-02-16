<?php
/**
*
* @package phpBB3
* @copyright (c) 2011 phpBB Group
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
	* phpbb-database object
	*/
	public $db = null;

	/**
	* Name of the field we want to handle: either 'teampage' or 'legend'
	*/
	private $field = '';

	/**
	* URI for the adm_back_link when there was an error.
	*/
	private $adm_back_link = '';

	/**
	* Constructor
	*/
	public function __construct ($db, $field, $adm_back_link = '')
	{
		$this->adm_back_link = $adm_back_link;

		if (!in_array($field, array('teampage', 'legend')))
		{
			$this->error('NO_MODE');
		}

		$this->db = $db;
		$this->field = $field;
	}

	/**
	* Returns the group_{$this->field} for a given group, if the group exists.
	* @param	int		$group_id	group_id of the group to be selected
	* @return	int					position of the group
	*/
	public function get_group_value($group_id)
	{
		$sql = 'SELECT group_' . $this->field . '
			FROM ' . GROUPS_TABLE . '
			WHERE group_id = ' . (int) $group_id;
		$result = $this->db->sql_query($sql);
		$current_value = $this->db->sql_fetchfield('group_' . $this->field);
		$this->db->sql_freeresult($result);

		if ($current_value === false)
		{
			// Group not found.
			$this->error('NO_GROUP');
		}

		return (int) $current_value;
	}

	/**
	* Get number of groups, displayed on the teampage/legend
	*
	* @return	int		value of the last group displayed
	*/
	public function get_group_count()
	{
		$sql = 'SELECT group_' . $this->field . '
			FROM ' . GROUPS_TABLE . '
			ORDER BY group_' . $this->field . ' DESC';
		$result = $this->db->sql_query_limit($sql, 1);
		$group_count = (int) $this->db->sql_fetchfield('group_' . $this->field);
		$this->db->sql_freeresult($result);

		return $group_count;
	}

	/**
	* Addes a group by group_id
	*
	* @param	int		$group_id	group_id of the group to be added
	* @return	void
	*/
	public function add_group($group_id)
	{
		$current_value = $this->get_group_value($group_id);

		if ($current_value == self::GROUP_DISABLED)
		{
			// Group is currently not displayed, add it at the end.
			$next_value = 1 + $this->get_group_count();

			$sql = 'UPDATE ' . GROUPS_TABLE . '
				SET group_' . $this->field . ' = ' . $next_value . '
				WHERE group_' . $this->field . ' = ' . self::GROUP_DISABLED . '
					AND group_id = ' . (int) $group_id;
			$this->db->sql_query($sql);
		}
	}

	/**
	* Deletes a group by setting the field to self::GROUP_DISABLED and closing the gap in the list.
	*
	* @param	int		$group_id		group_id of the group to be deleted
	* @param	bool	$skip_group		Skip setting the group to GROUP_DISABLED, to save the query, when you need to update it anyway.
	* @return	void
	*/
	public function delete_group($group_id, $skip_group = false)
	{
		$current_value = $this->get_group_value($group_id);

		if ($current_value != self::GROUP_DISABLED)
		{
			$this->db->sql_transaction('begin');

			$sql = 'UPDATE ' . GROUPS_TABLE . '
				SET group_' . $this->field . ' = group_' . $this->field . ' - 1
				WHERE group_' . $this->field . ' > ' . $current_value;
			$this->db->sql_query($sql);

			if (!$skip_group)
			{
				$sql = 'UPDATE ' . GROUPS_TABLE . '
					SET group_' . $this->field . ' = ' . self::GROUP_DISABLED . '
					WHERE group_id = ' . (int) $group_id;
				$this->db->sql_query($sql);
			}

			$this->db->sql_transaction('commit');
		}
	}

	/**
	* Moves a group up by group_id
	*
	* @param	int		$group_id	group_id of the group to be moved
	* @return	void
	*/
	public function move_up($group_id)
	{
		$current_value = $this->get_group_value($group_id);

		// Only move the group, if it is in the list and not already on top.
		if ($current_value > 1)
		{
			$this->db->sql_transaction('begin');

			$sql = 'UPDATE ' . GROUPS_TABLE . '
				SET group_' . $this->field . ' = group_' . $this->field . ' + 1
				WHERE group_' . $this->field . ' = ' . ($current_value - 1);
			$this->db->sql_query($sql);

			$sql = 'UPDATE ' . GROUPS_TABLE . '
				SET group_' . $this->field . ' = group_' . $this->field . ' - 1
				WHERE group_id = ' . (int) $group_id;
			$this->db->sql_query($sql);

			$this->db->sql_transaction('commit');
		}
	}

	/**
	* Moves a group down by group_id
	*
	* @param	int		$group_id	group_id of the group to be moved
	* @return	void
	*/
	public function move_down($group_id)
	{
		$current_value = $this->get_group_value($group_id);

		if ($current_value != self::GROUP_DISABLED)
		{
			$this->db->sql_transaction('begin');

			$sql = 'UPDATE ' . GROUPS_TABLE . '
				SET group_' . $this->field . ' = group_' . $this->field . ' - 1
				WHERE group_' . $this->field . ' = ' . ($current_value + 1);
			$this->db->sql_query($sql);

			if ($this->db->sql_affectedrows() == 1)
			{
				// Only update when we move another one up, otherwise it was the last.
				$sql = 'UPDATE ' . GROUPS_TABLE . '
					SET group_' . $this->field . ' = group_' . $this->field . ' + 1
					WHERE group_id = ' . (int) $group_id;
				$this->db->sql_query($sql);
			}

			$this->db->sql_transaction('commit');
		}
	}

	/**
	* Get group type language var
	*
	* @param	int		$group_type	group_type from the groups-table
	* @return	string				name of the language variable for the given group-type.
	*/
	static public function group_type_language($group_type)
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

	/**
	* Error
	*/
	public function error($message)
	{
		global $user;
		trigger_error($user->lang[$message] . (($this->adm_back_link) ? adm_back_link($this->adm_back_link) : ''), E_USER_WARNING);
	}
}
