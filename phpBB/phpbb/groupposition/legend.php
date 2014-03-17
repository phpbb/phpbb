<?php
/**
*
* @package phpBB3
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\groupposition;

/**
* Legend group position class
*
* group_legend is an ascending list 1, 2, ..., n for groups which are displayed. 1 is the first group, n the last.
* If the value is 0 (self::GROUP_DISABLED) the group is not displayed.
*
* @package phpBB3
*/
class legend implements \phpbb\groupposition\groupposition_interface
{
	/**
	* Group is not displayed
	*/
	const GROUP_DISABLED = 0;

	/**
	* Database object
	* @var \phpbb\db\driver\driver_interface
	*/
	protected $db;

	/**
	* User object
	* @var \phpbb\user
	*/
	protected $user;

	/**
	* Constructor
	*
	* @param \phpbb\db\driver\driver_interface	$db		Database object
	* @param \phpbb\user			$user	User object
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\user $user)
	{
		$this->db = $db;
		$this->user = $user;
	}

	/**
	* Returns the group_legend for a given group, if the group exists.
	*
	* {@inheritDoc}
	*/
	public function get_group_value($group_id)
	{
		$sql = 'SELECT group_legend
			FROM ' . GROUPS_TABLE . '
			WHERE group_id = ' . (int) $group_id;
		$result = $this->db->sql_query($sql);
		$current_value = $this->db->sql_fetchfield('group_legend');
		$this->db->sql_freeresult($result);

		if ($current_value === false)
		{
			// Group not found.
			throw new \phpbb\groupposition\exception('NO_GROUP');
		}

		return (int) $current_value;
	}

	/**
	* Get number of groups, displayed on the legend
	*
	* {@inheritDoc}
	*/
	public function get_group_count()
	{
		$sql = 'SELECT group_legend
			FROM ' . GROUPS_TABLE . '
			ORDER BY group_legend DESC';
		$result = $this->db->sql_query_limit($sql, 1);
		$group_count = (int) $this->db->sql_fetchfield('group_legend');
		$this->db->sql_freeresult($result);

		return $group_count;
	}

	/**
	* Adds a group by group_id
	*
	* {@inheritDoc}
	*/
	public function add_group($group_id)
	{
		$current_value = $this->get_group_value($group_id);

		if ($current_value == self::GROUP_DISABLED)
		{
			// Group is currently not displayed, add it at the end.
			$next_value = 1 + $this->get_group_count();

			$sql = 'UPDATE ' . GROUPS_TABLE . '
				SET group_legend = ' . $next_value . '
				WHERE group_legend = ' . self::GROUP_DISABLED . '
					AND group_id = ' . (int) $group_id;
			$this->db->sql_query($sql);
			return true;
		}

		return false;
	}

	/**
	* Deletes a group by setting the field to self::GROUP_DISABLED and closing the gap in the list.
	*
	* {@inheritDoc}
	*/
	public function delete_group($group_id, $skip_group = false)
	{
		$current_value = $this->get_group_value($group_id);

		if ($current_value != self::GROUP_DISABLED)
		{
			$this->db->sql_transaction('begin');

			$sql = 'UPDATE ' . GROUPS_TABLE . '
				SET group_legend = group_legend - 1
				WHERE group_legend > ' . $current_value;
			$this->db->sql_query($sql);

			if (!$skip_group)
			{
				$sql = 'UPDATE ' . GROUPS_TABLE . '
					SET group_legend = ' . self::GROUP_DISABLED . '
					WHERE group_id = ' . (int) $group_id;
				$this->db->sql_query($sql);
			}

			$this->db->sql_transaction('commit');

			return true;
		}

		return false;
	}

	/**
	* Moves a group up by group_id
	*
	* {@inheritDoc}
	*/
	public function move_up($group_id)
	{
		return $this->move($group_id, 1);
	}

	/**
	* Moves a group down by group_id
	*
	* {@inheritDoc}
	*/
	public function move_down($group_id)
	{
		return $this->move($group_id, -1);
	}

	/**
	* Moves a group up/down
	*
	* {@inheritDoc}
	*/
	public function move($group_id, $delta)
	{
		$delta = (int) $delta;
		if (!$delta)
		{
			return false;
		}

		$move_up = ($delta > 0) ? true : false;
		$current_value = $this->get_group_value($group_id);

		if ($current_value != self::GROUP_DISABLED)
		{
			$this->db->sql_transaction('begin');

			// First we move all groups between our current value and the target value up/down 1,
			// so we have a gap for our group to move.
			$sql = 'UPDATE ' . GROUPS_TABLE . '
				SET group_legend = group_legend' . (($move_up) ? ' + 1' : ' - 1') . '
				WHERE group_legend > ' . self::GROUP_DISABLED . '
					AND group_legend' . (($move_up) ? ' >= ' : ' <= ') . ($current_value - $delta) . '
					AND group_legend' . (($move_up) ? ' < ' : ' > ') . $current_value;
			$this->db->sql_query($sql);

			// Because there might be fewer groups above/below the group than we wanted to move,
			// we use the number of changed groups, to update the group.
			$delta = (int) $this->db->sql_affectedrows();

			if ($delta)
			{
				// And now finally, when we moved some other groups and built a gap,
				// we can move the desired group to it.
				$sql = 'UPDATE ' . GROUPS_TABLE . '
					SET group_legend = group_legend ' . (($move_up) ? ' - ' : ' + ') . $delta . '
					WHERE group_id = ' . (int) $group_id;
				$this->db->sql_query($sql);

				$this->db->sql_transaction('commit');

				return true;
			}

			$this->db->sql_transaction('commit');
		}

		return false;
	}

	/**
	* Get group type language var
	*
	* @param	int		$group_type	group_type from the groups-table
	* @return	string		name of the language variable for the given group-type.
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
}
