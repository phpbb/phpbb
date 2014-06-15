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

namespace phpbb\groupposition;

/**
* Teampage group position class
*
* Teampage position is an ascending list 1, 2, ..., n for items which are displayed. 1 is the first item, n the last.
*/
class teampage implements \phpbb\groupposition\groupposition_interface
{
	/**
	* Group is not displayed
	*/
	const GROUP_DISABLED = 0;

	/**
	* No parent item
	*/
	const NO_PARENT = 0;

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
	* Cache object
	* @var \phpbb\cache\driver\driver_interface
	*/
	protected $cache;

	/**
	* Constructor
	*
	* @param \phpbb\db\driver\driver_interface				$db		Database object
	* @param \phpbb\user						$user	User object
	* @param \phpbb\cache\driver\driver_interface	$cache	Cache object
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\user $user, \phpbb\cache\driver\driver_interface $cache)
	{
		$this->db = $db;
		$this->user = $user;
		$this->cache = $cache;
	}

	/**
	* Returns the teampage position for a given group, if the group exists.
	*
	* @param	int		$group_id	group_id of the group to be selected
	* @return	int			position of the group
	* @throws \phpbb\groupposition\exception
	*/
	public function get_group_value($group_id)
	{
		// The join is required to ensure that the group itself exists
		$sql = 'SELECT g.group_id, t.teampage_position
			FROM ' . GROUPS_TABLE . ' g
			LEFT JOIN ' . TEAMPAGE_TABLE . ' t
				ON (t.group_id = g.group_id)
			WHERE g.group_id = ' . (int) $group_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if ($row === false)
		{
			// Group not found.
			throw new \phpbb\groupposition\exception('NO_GROUP');
		}

		return (int) $row['teampage_position'];
	}

	/**
	* Returns the row for a given group, if the group exists.
	*
	* @param	int		$group_id	group_id of the group to be selected
	* @return	array			Data row of the group
	* @throws \phpbb\groupposition\exception
	*/
	public function get_group_values($group_id)
	{
		// The join is required to ensure that the group itself exists
		$sql = 'SELECT *
			FROM ' . GROUPS_TABLE . ' g
			LEFT JOIN ' . TEAMPAGE_TABLE . ' t
				ON (t.group_id = g.group_id)
			WHERE g.group_id = ' . (int) $group_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if ($row === false)
		{
			// Group not found.
			throw new \phpbb\groupposition\exception('NO_GROUP');
		}

		return $row;
	}

	/**
	* Returns the teampage position for a given teampage item, if the item exists.
	*
	* @param	int		$teampage_id	Teampage_id of the selected item
	* @return	int			Teampage position of the item
	* @throws \phpbb\groupposition\exception
	*/
	public function get_teampage_value($teampage_id)
	{
		$sql = 'SELECT teampage_position
			FROM ' . TEAMPAGE_TABLE . '
			WHERE teampage_id = ' . (int) $teampage_id;
		$result = $this->db->sql_query($sql);
		$current_value = $this->db->sql_fetchfield('teampage_position');
		$this->db->sql_freeresult($result);

		if ($current_value === false)
		{
			// Group not found.
			throw new \phpbb\groupposition\exception('NO_GROUP');
		}

		return (int) $current_value;
	}

	/**
	* Returns the teampage row for a given teampage item, if the item exists.
	*
	* @param	int		$teampage_id	Teampage_id of the selected item
	* @return	array			Teampage row of the item
	* @throws \phpbb\groupposition\exception
	*/
	public function get_teampage_values($teampage_id)
	{
		$sql = 'SELECT teampage_position, teampage_parent
			FROM ' . TEAMPAGE_TABLE . '
			WHERE teampage_id = ' . (int) $teampage_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if ($row === false)
		{
			// Group not found.
			throw new \phpbb\groupposition\exception('NO_GROUP');
		}

		return $row;
	}


	/**
	* {@inheritDoc}
	*/
	public function get_group_count()
	{
		$sql = 'SELECT teampage_position
			FROM ' . TEAMPAGE_TABLE . '
			ORDER BY teampage_position DESC';
		$result = $this->db->sql_query_limit($sql, 1);
		$group_count = (int) $this->db->sql_fetchfield('teampage_position');
		$this->db->sql_freeresult($result);

		return $group_count;
	}

	/**
	* {@inheritDoc}
	*/
	public function add_group($group_id)
	{
		return $this->add_group_teampage($group_id, self::NO_PARENT);
	}

	/**
	* Adds a group by group_id
	*
	* @param	int		$group_id	group_id of the group to be added
	* @param	int		$parent_id	Teampage ID of the parent item
	* @return	bool		True if the group was added successfully
	*/
	public function add_group_teampage($group_id, $parent_id)
	{
		$current_value = $this->get_group_value($group_id);

		if ($current_value == self::GROUP_DISABLED)
		{
			if ($parent_id != self::NO_PARENT)
			{
				// Check, whether the given parent is a category
				$sql = 'SELECT teampage_id
					FROM ' . TEAMPAGE_TABLE . '
					WHERE group_id = 0
						AND teampage_id = ' . (int) $parent_id;
				$result = $this->db->sql_query_limit($sql, 1);
				$parent_is_category = (bool) $this->db->sql_fetchfield('teampage_id');
				$this->db->sql_freeresult($result);

				if ($parent_is_category)
				{
					// Get value of last child from this parent and add group there
					$sql = 'SELECT teampage_position
						FROM ' . TEAMPAGE_TABLE . '
						WHERE teampage_parent = ' . (int) $parent_id . '
							OR teampage_id = ' . (int) $parent_id . '
						ORDER BY teampage_position DESC';
					$result = $this->db->sql_query_limit($sql, 1);
					$new_position = (int) $this->db->sql_fetchfield('teampage_position');
					$this->db->sql_freeresult($result);

					$sql = 'UPDATE ' . TEAMPAGE_TABLE . '
						SET teampage_position = teampage_position + 1
						WHERE teampage_position > ' . $new_position;
					$this->db->sql_query($sql);
				}
			}
			else
			{
				// Add group at the end
				$new_position = $this->get_group_count();
			}

			$sql_ary = array(
				'group_id'			=> $group_id,
				'teampage_position'	=> $new_position + 1,
				'teampage_parent'	=> $parent_id,
			);

			$sql = 'INSERT INTO ' . TEAMPAGE_TABLE . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
			$this->db->sql_query($sql);

			$this->cache->destroy('sql', TEAMPAGE_TABLE);
			return true;
		}

		$this->cache->destroy('sql', TEAMPAGE_TABLE);
		return false;
	}

	/**
	* Adds a new category
	*
	* @param	string	$category_name	Name of the category to be added
	* @return	bool		True if the category was added successfully
	*/
	public function add_category_teampage($category_name)
	{
		if ($category_name === '')
		{
			return false;
		}

		$num_entries = $this->get_group_count();

		$sql_ary = array(
			'group_id'			=> 0,
			'teampage_position'	=> $num_entries + 1,
			'teampage_parent'	=> 0,
			'teampage_name'		=> truncate_string($category_name, 255, 255),
		);

		$sql = 'INSERT INTO ' . TEAMPAGE_TABLE . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
		$this->db->sql_query($sql);

		$this->cache->destroy('sql', TEAMPAGE_TABLE);
		return true;
	}

	/**
	* Deletes a group from the list and closes the gap in the position list.
	*
	* @param	int		$group_id		group_id of the group to be deleted
	* @param	bool	$skip_group		Skip setting the value for this group, to save the query, when you need to update it anyway.
	* @return	bool		True if the group was deleted successfully
	*/
	public function delete_group($group_id, $skip_group = false)
	{
		$current_value = $this->get_group_value($group_id);

		if ($current_value != self::GROUP_DISABLED)
		{
			$sql = 'UPDATE ' . TEAMPAGE_TABLE . '
				SET teampage_position = teampage_position - 1
				WHERE teampage_position > ' . $current_value;
			$this->db->sql_query($sql);

			$sql = 'DELETE FROM ' . TEAMPAGE_TABLE . '
				WHERE group_id = ' . $group_id;
			$this->db->sql_query($sql);

			$this->cache->destroy('sql', TEAMPAGE_TABLE);
			return true;
		}

		$this->cache->destroy('sql', TEAMPAGE_TABLE);
		return false;
	}

	/**
	* Deletes an item from the list and closes the gap in the position list.
	*
	* @param	int		$teampage_id	teampage_id of the item to be deleted
	* @param	bool	$skip_group		Skip setting the group to GROUP_DISABLED, to save the query, when you need to update it anyway.
	* @return	bool		True if the item was deleted successfully
	*/
	public function delete_teampage($teampage_id, $skip_group = false)
	{
		$current_value = $this->get_teampage_value($teampage_id);

		if ($current_value != self::GROUP_DISABLED)
		{
			$sql = 'DELETE FROM ' . TEAMPAGE_TABLE . '
				WHERE teampage_id = ' . $teampage_id . '
					OR teampage_parent = ' . $teampage_id;
			$this->db->sql_query($sql);

			$delta = (int) $this->db->sql_affectedrows();

			$sql = 'UPDATE ' . TEAMPAGE_TABLE . '
				SET teampage_position = teampage_position - ' . $delta . '
				WHERE teampage_position > ' . $current_value;
			$this->db->sql_query($sql);

			$this->cache->destroy('sql', TEAMPAGE_TABLE);
			return true;
		}

		$this->cache->destroy('sql', TEAMPAGE_TABLE);
		return false;
	}

	/**
	* {@inheritDoc}
	*/
	public function move_up($group_id)
	{
		return $this->move($group_id, 1);
	}

	/**
	* Moves an item up by teampage_id
	*
	* @param	int		$teampage_id	teampage_id of the item to be move
	* @return	bool		True if the group was moved successfully
	*/
	public function move_up_teampage($teampage_id)
	{
		return $this->move_teampage($teampage_id, 1);
	}

	/**
	* {@inheritDoc}
	*/
	public function move_down($group_id)
	{
		return $this->move($group_id, -1);
	}

	/**
	* Moves an item down by teampage_id
	*
	* @param	int		$teampage_id	teampage_id of the item to be moved
	* @return	bool		True if the group was moved successfully
	*/
	public function move_down_teampage($teampage_id)
	{
		return $this->move_teampage($teampage_id, -1);
	}

	/**
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
		$data = $this->get_group_values($group_id);

		$current_value = (int) $data['teampage_position'];
		if ($current_value != self::GROUP_DISABLED)
		{
			$this->db->sql_transaction('begin');

			if (!$move_up && $data['teampage_parent'] == self::NO_PARENT)
			{
				// If we move items down, we need to grab the one sibling more,
				// so we do not ignore the children of the previous sibling.
				// We will remove the additional sibling later on.
				$delta = abs($delta) + 1;
			}

			$sql = 'SELECT teampage_position
				FROM ' . TEAMPAGE_TABLE . '
				WHERE teampage_parent = ' . (int) $data['teampage_parent'] . '
					AND teampage_position' . (($move_up) ? ' < ' : ' > ') . $current_value . '
				ORDER BY teampage_position' . (($move_up) ? ' DESC' : ' ASC');
			$result = $this->db->sql_query_limit($sql, $delta);

			$sibling_count = 0;
			$sibling_limit = $delta;

			// Reset the delta, as we recalculate the new real delta
			$delta = 0;
			while ($row = $this->db->sql_fetchrow($result))
			{
				$sibling_count++;
				$delta = $current_value - $row['teampage_position'];

				if (!$move_up && $data['teampage_parent'] == self::NO_PARENT && $sibling_count == $sibling_limit)
				{
					// Remove the additional sibling we added previously
					$delta++;
				}
			}
			$this->db->sql_freeresult($result);

			if ($delta)
			{
				// First we move all items between our current value and the target value up/down 1,
				// so we have a gap for our item to move.
				$sql = 'UPDATE ' . TEAMPAGE_TABLE . '
					SET teampage_position = teampage_position' . (($move_up) ? ' + 1' : ' - 1') . '
					WHERE teampage_position' . (($move_up) ? ' >= ' : ' <= ') . ($current_value - $delta) . '
						AND teampage_position' . (($move_up) ? ' < ' : ' > ') . $current_value;
				$this->db->sql_query($sql);

				// And now finally, when we moved some other items and built a gap,
				// we can move the desired item to it.
				$sql = 'UPDATE ' . TEAMPAGE_TABLE . '
					SET teampage_position = teampage_position ' . (($move_up) ? ' - ' : ' + ') . abs($delta) . '
					WHERE group_id = ' . (int) $group_id;
				$this->db->sql_query($sql);

				$this->db->sql_transaction('commit');
				$this->cache->destroy('sql', TEAMPAGE_TABLE);

				return true;
			}

			$this->db->sql_transaction('commit');
		}

		$this->cache->destroy('sql', TEAMPAGE_TABLE);
		return false;
	}

	/**
	* Moves an item up/down
	*
	* @param	int		$teampage_id	teampage_id of the item to be moved
	* @param	int		$delta		number of steps:
	*								- positive = move up
	*								- negative = move down
	* @return	bool		True if the group was moved successfully
	*/
	public function move_teampage($teampage_id, $delta)
	{
		$delta = (int) $delta;
		if (!$delta)
		{
			return false;
		}

		$move_up = ($delta > 0) ? true : false;
		$data = $this->get_teampage_values($teampage_id);

		$current_value = (int) $data['teampage_position'];
		if ($current_value != self::GROUP_DISABLED)
		{
			$this->db->sql_transaction('begin');

			if (!$move_up && $data['teampage_parent'] == self::NO_PARENT)
			{
				// If we move items down, we need to grab the one sibling more,
				// so we do not ignore the children of the previous sibling.
				// We will remove the additional sibling later on.
				$delta = abs($delta) + 1;
			}

			$sql = 'SELECT teampage_id, teampage_position
				FROM ' . TEAMPAGE_TABLE . '
				WHERE teampage_parent = ' . (int) $data['teampage_parent'] . '
					AND teampage_position' . (($move_up) ? ' < ' : ' > ') . $current_value . '
				ORDER BY teampage_position' . (($move_up) ? ' DESC' : ' ASC');
			$result = $this->db->sql_query_limit($sql, $delta);

			$sibling_count = 0;
			$sibling_limit = $delta;

			// Reset the delta, as we recalculate the new real delta
			$delta = 0;
			while ($row = $this->db->sql_fetchrow($result))
			{
				$sibling_count++;
				$delta = $current_value - $row['teampage_position'];

				// Remove the additional sibling we added previously
				// But only, if we included it, this is not be the case
				// when we reached the end of our list
				if (!$move_up && $data['teampage_parent'] == self::NO_PARENT && $sibling_count == $sibling_limit)
				{
					$delta++;
				}
			}
			$this->db->sql_freeresult($result);

			if ($delta)
			{
				$sql = 'SELECT COUNT(teampage_id) as num_items
					FROM ' . TEAMPAGE_TABLE . '
					WHERE teampage_id = ' . (int) $teampage_id . '
						OR teampage_parent = ' . (int) $teampage_id;
				$result = $this->db->sql_query($sql);
				$num_items = (int) $this->db->sql_fetchfield('num_items');
				$this->db->sql_freeresult($result);

				// First we move all items between our current value and the target value up/down 1,
				// so we have a gap for our item to move.
				$sql = 'UPDATE ' . TEAMPAGE_TABLE . '
					SET teampage_position = teampage_position' . (($move_up) ? ' + ' : ' - ') . $num_items . '
					WHERE teampage_position' . (($move_up) ? ' >= ' : ' <= ') . ($current_value - $delta) . '
						AND teampage_position' . (($move_up) ? ' < ' : ' > ') . $current_value . '
						AND NOT (teampage_id = ' . (int) $teampage_id . '
							OR teampage_parent = ' . (int) $teampage_id . ')';
				$this->db->sql_query($sql);

				$delta = (!$move_up && $data['teampage_parent'] == self::NO_PARENT) ? (abs($delta) - ($num_items - 1)) : abs($delta);

				// And now finally, when we moved some other items and built a gap,
				// we can move the desired item to it.
				$sql = 'UPDATE ' . TEAMPAGE_TABLE . '
					SET teampage_position = teampage_position ' . (($move_up) ? ' - ' : ' + ') . $delta . '
					WHERE teampage_id = ' . (int) $teampage_id . '
						OR teampage_parent = ' . (int) $teampage_id;
				$this->db->sql_query($sql);

				$this->db->sql_transaction('commit');
				$this->cache->destroy('sql', TEAMPAGE_TABLE);

				return true;
			}

			$this->db->sql_transaction('commit');
		}

		$this->cache->destroy('sql', TEAMPAGE_TABLE);
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
