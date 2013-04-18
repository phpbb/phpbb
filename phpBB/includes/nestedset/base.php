<?php
/**
*
* @package Nested Set
* @copyright (c) 2013 phpBB Group
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

abstract class phpbb_nestedset_base implements phpbb_nestedset_interface
{
	/** @var phpbb_db_driver */
	protected $db;

	/** @var phpbb_lock_db */
	protected $lock;

	/** @var string */
	protected $table_name;

	/**
	* Prefix for the language keys returned by exceptions
	* @var string
	*/
	protected $message_prefix = '';

	/**
	* Column names in the table
	* @var string
	*/
	protected $column_item_id = 'item_id';
	protected $column_left_id = 'left_id';
	protected $column_right_id = 'right_id';
	protected $column_parent_id = 'parent_id';
	protected $column_item_parents = 'item_parents';

	/**
	* Additional SQL restrictions
	* Allows to have multiple nested sets in one table
	* @var string
	*/
	protected $sql_where = '';

	/**
	* List of item properties to be cached in $item_parents
	* @var array
	*/
	protected $item_basic_data = array('*');

	/**
	* Returns additional sql where restrictions
	*
	* @param string		$operator		SQL operator that needs to be prepended to sql_where,
	*									if it is not empty.
	* @param string		$column_prefix	Prefix that needs to be prepended to column names
	* @return bool True if the item was deleted
	*/
	public function get_sql_where($operator = 'AND', $column_prefix = '')
	{
		return (!$this->sql_where) ? '' : $operator . ' ' . sprintf($this->sql_where, $column_prefix);
	}

	/**
	* @inheritdoc
	*/
	public function insert(array $additional_data)
	{
		$item_data = $this->reset_nestedset_values($additional_data);

		$sql = 'INSERT INTO ' . $this->table_name . ' ' . $this->db->sql_build_array('INSERT', $item_data);
		$this->db->sql_query($sql);

		$item_data[$this->column_item_id] = (int) $this->db->sql_nextid();

		return array_merge($item_data, $this->add($item_data));
	}

	/**
	* @inheritdoc
	*/
	public function add(array $item)
	{
		$sql = 'SELECT MAX(' . $this->column_right_id . ') AS ' . $this->column_right_id . '
			FROM ' . $this->table_name . '
			' . $this->get_sql_where('WHERE');
		$result = $this->db->sql_query($sql);
		$current_max_right_id = (int) $this->db->sql_fetchfield($this->column_right_id);
		$this->db->sql_freeresult($result);

		$update_item_data = array(
			$this->column_parent_id		=> 0,
			$this->column_left_id		=> $current_max_right_id + 1,
			$this->column_right_id		=> $current_max_right_id + 2,
			$this->column_item_parents	=> '',
		);

		$sql = 'UPDATE ' . $this->table_name . '
			SET ' . $this->db->sql_build_array('UPDATE', $update_item_data) . '
			WHERE ' . $this->column_item_id . ' = ' . (int) $item[$this->column_item_id];
		$this->db->sql_query($sql);

		return $update_item_data;
	}

	/**
	* @inheritdoc
	*/
	public function remove(array $item)
	{
		if ($item[$this->column_right_id] - $item[$this->column_left_id] > 1)
		{
			$items = array_keys($this->get_branch_data($item, 'children'));
		}
		else
		{
			$items = array((int) $item[$this->column_item_id]);
		}

		$this->remove_subset($items, $item);

		return $items;
	}

	/**
	* @inheritdoc
	*/
	public function delete(array $item)
	{
		$removed_items = $this->remove($item);

		$sql = 'DELETE FROM ' . $this->table_name . '
			WHERE ' . $this->db->sql_in_set($this->column_item_id, $removed_items) . '
			' . $this->get_sql_where('AND');
		$this->db->sql_query($sql);

		return $removed_items;
	}

	/**
	* @inheritdoc
	*/
	public function move(array $item, $delta)
	{
		if ($delta == 0)
		{
			return false;
		}

		if (!$this->lock->acquire())
		{
			throw new phpbb_nestedset_exception($this->message_prefix . 'LOCK_FAILED_ACQUIRE');
		}

		$action = ($delta > 0) ? 'move_up' : 'move_down';
		$delta = abs($delta);

		/**
		* Fetch all the siblings between the item's current spot
		* and where we want to move it to. If there are less than $delta
		* siblings between the current spot and the target then the
		* item will move as far as possible
		*/
		$sql = "SELECT {$this->column_item_id}, {$this->column_parent_id}, {$this->column_left_id}, {$this->column_right_id}, {$this->column_item_parents}
			FROM " . $this->table_name . '
			WHERE ' . $this->column_parent_id . ' = ' . (int) $item[$this->column_parent_id] . '
				' . $this->get_sql_where() . '
				AND ';

		if ($action == 'move_up')
		{
			$sql .= $this->column_right_id . ' < ' . (int) $item[$this->column_right_id] . ' ORDER BY ' . $this->column_right_id . ' DESC';
		}
		else
		{
			$sql .= $this->column_left_id . ' > ' . (int) $item[$this->column_left_id] . ' ORDER BY ' . $this->column_left_id . ' ASC';
		}

		$result = $this->db->sql_query_limit($sql, $delta);

		$target = null;
		while ($row = $this->db->sql_fetchrow($result))
		{
			$target = $row;
		}
		$this->db->sql_freeresult($result);

		if (is_null($target))
		{
			$this->lock->release();
			// The item is already on top or bottom
			return false;
		}

		/**
		* $left_id and $right_id define the scope of the items that are affected by the move.
		* $diff_up and $diff_down are the values to substract or add to each item's left_id
		* and right_id in order to move them up or down.
		* $move_up_left and $move_up_right define the scope of the items that are moving
		* up. Other items in the scope of ($left_id, $right_id) are considered to move down.
		*/
		if ($action == 'move_up')
		{
			$left_id = $target[$this->column_left_id];
			$right_id = (int) $item[$this->column_right_id];

			$diff_up = (int) $item[$this->column_left_id] - $target[$this->column_left_id];
			$diff_down = (int) $item[$this->column_right_id] + 1 - (int) $item[$this->column_left_id];

			$move_up_left = (int) $item[$this->column_left_id];
			$move_up_right = (int) $item[$this->column_right_id];
		}
		else
		{
			$left_id = (int) $item[$this->column_left_id];
			$right_id = $target[$this->column_right_id];

			$diff_up = (int) $item[$this->column_right_id] + 1 - (int) $item[$this->column_left_id];
			$diff_down = $target[$this->column_right_id] - (int) $item[$this->column_right_id];

			$move_up_left = (int) $item[$this->column_right_id] + 1;
			$move_up_right = $target[$this->column_right_id];
		}

		// Now do the dirty job
		$sql = 'UPDATE ' . $this->table_name . '
			SET ' . $this->column_left_id . ' = ' . $this->column_left_id . ' + CASE
				WHEN ' . $this->column_left_id . " BETWEEN {$move_up_left} AND {$move_up_right} THEN -{$diff_up}
				ELSE {$diff_down}
			END,
			" . $this->column_right_id . ' = ' . $this->column_right_id . ' + CASE
				WHEN ' . $this->column_right_id . " BETWEEN {$move_up_left} AND {$move_up_right} THEN -{$diff_up}
				ELSE {$diff_down}
			END,
			" . $this->column_item_parents . " = ''
			WHERE
				" . $this->column_left_id . " BETWEEN {$left_id} AND {$right_id}
				AND " . $this->column_right_id . " BETWEEN {$left_id} AND {$right_id}
				" . $this->get_sql_where();
		$this->db->sql_query($sql);

		$this->lock->release();

		return true;
	}

	/**
	* @inheritdoc
	*/
	public function move_down(array $item)
	{
		return $this->move($item, -1);
	}

	/**
	* @inheritdoc
	*/
	public function move_up(array $item)
	{
		return $this->move($item, 1);
	}

	/**
	* @inheritdoc
	*/
	public function move_children(array $current_parent, array $new_parent)
	{
		if (($current_parent[$this->column_right_id] - $current_parent[$this->column_left_id]) <= 1 || !$current_parent[$this->column_item_id] || $current_parent[$this->column_item_id] == $new_parent[$this->column_item_id])
		{
			return false;
		}

		if (!$this->lock->acquire())
		{
			throw new phpbb_nestedset_exception($this->message_prefix . 'LOCK_FAILED_ACQUIRE');
		}

		$move_items = array_keys($this->get_branch_data($current_parent, 'children', true, false));

		if (in_array($new_parent[$this->column_item_id], $move_items))
		{
			$this->lock->release();
			throw new phpbb_nestedset_exception($this->message_prefix . 'INVALID_PARENT');
		}

		$diff = sizeof($move_items) * 2;
		$sql_exclude_moved_items = $this->db->sql_in_set($this->column_item_id, $move_items, true);

		$this->db->sql_transaction('begin');

		$this->remove_subset($move_items, $current_parent, false, true);

		if ($new_parent[$this->column_item_id])
		{
			// Retrieve new-parent again, it may have been changed...
			$sql = 'SELECT *
				FROM ' . $this->table_name . '
				WHERE ' . $this->column_item_id . ' = ' . (int) $new_parent[$this->column_item_id];
			$result = $this->db->sql_query($sql);
			$new_parent = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if (!$new_parent)
			{
				$this->db->sql_transaction('rollback');
				$this->lock->release();
				throw new phpbb_nestedset_exception($this->message_prefix . 'INVALID_PARENT');
			}

			$new_right_id = $this->prepare_adding_subset($move_items, $new_parent, true);

			if ($new_right_id > $current_parent[$this->column_right_id])
			{
				$diff = ' + ' . ($new_right_id - $current_parent[$this->column_right_id]);
			}
			else
			{
				$diff = ' - ' . abs($new_right_id - $current_parent[$this->column_right_id]);
			}
		}
		else
		{
			$sql = 'SELECT MAX(' . $this->column_right_id . ') AS ' . $this->column_right_id . '
				FROM ' . $this->table_name . '
				WHERE ' . $sql_exclude_moved_items . '
					' . $this->get_sql_where('AND');
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			$diff = ' + ' . ($row[$this->column_right_id] - $current_parent[$this->column_left_id]);
		}

		$sql = 'UPDATE ' . $this->table_name . '
			SET ' . $this->column_left_id . ' = ' . $this->column_left_id . $diff . ',
				' . $this->column_right_id . ' = ' . $this->column_right_id . $diff . ',
				' . $this->column_parent_id . ' = ' . $this->db->sql_case($this->column_parent_id . ' = ' . (int) $current_parent[$this->column_item_id], (int) $new_parent[$this->column_item_id], $this->column_parent_id) . ',
				' . $this->column_item_parents . " = ''
			WHERE " . $this->db->sql_in_set($this->column_item_id, $move_items) . '
				' . $this->get_sql_where('AND');
		$this->db->sql_query($sql);

		$this->db->sql_transaction('commit');
		$this->lock->release();

		return true;
	}

	/**
	* @inheritdoc
	*/
	public function set_parent(array $item, array $new_parent)
	{
		if (!$this->lock->acquire())
		{
			throw new phpbb_nestedset_exception($this->message_prefix . 'LOCK_FAILED_ACQUIRE');
		}

		$move_items = array_keys($this->get_branch_data($item, 'children'));

		if (in_array($new_parent[$this->column_item_id], $move_items))
		{
			$this->lock->release();
			throw new phpbb_nestedset_exception($this->message_prefix . 'INVALID_PARENT');
		}

		$diff = sizeof($move_items) * 2;
		$sql_exclude_moved_items = $this->db->sql_in_set($this->column_item_id, $move_items, true);

		$this->db->sql_transaction('begin');

		$this->remove_subset($move_items, $item, false, true);

		if ($new_parent[$this->column_item_id])
		{
			// Retrieve new-parent again, it may have been changed...
			$sql = 'SELECT *
				FROM ' . $this->table_name . '
				WHERE ' . $this->column_item_id . ' = ' . (int) $new_parent[$this->column_item_id];
			$result = $this->db->sql_query($sql);
			$new_parent = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if (!$new_parent)
			{
				$this->db->sql_transaction('rollback');
				$this->lock->release();
				throw new phpbb_nestedset_exception($this->message_prefix . 'INVALID_PARENT');
			}

			$new_right_id = $this->prepare_adding_subset($move_items, $new_parent, true);

			if ($new_right_id > (int) $item[$this->column_right_id])
			{
				$diff = ' + ' . ($new_right_id - (int) $item[$this->column_right_id] - 1);
			}
			else
			{
				$diff = ' - ' . abs($new_right_id - (int) $item[$this->column_right_id] - 1);
			}
		}
		else
		{
			$sql = 'SELECT MAX(' . $this->column_right_id . ') AS ' . $this->column_right_id . '
				FROM ' . $this->table_name . '
				WHERE ' . $sql_exclude_moved_items . '
					' . $this->get_sql_where('AND');
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			$diff = ' + ' . ($row[$this->column_right_id] - (int) $item[$this->column_left_id] + 1);
		}

		$sql = 'UPDATE ' . $this->table_name . '
			SET ' . $this->column_left_id . ' = ' . $this->column_left_id . $diff . ',
				' . $this->column_right_id . ' = ' . $this->column_right_id . $diff . ',
				' . $this->column_parent_id . ' = ' . $this->db->sql_case($this->column_item_id . ' = ' . (int) $item[$this->column_item_id], $new_parent[$this->column_item_id], $this->column_parent_id) . ',
				' . $this->column_item_parents . " = ''
			WHERE " . $this->db->sql_in_set($this->column_item_id, $move_items) . '
				' . $this->get_sql_where('AND');
		$this->db->sql_query($sql);

		$this->db->sql_transaction('commit');
		$this->lock->release();

		return true;
	}

	/**
	* @inheritdoc
	*/
	public function get_branch_data(array $item, $type = 'all', $order_desc = true, $include_item = true)
	{
		switch ($type)
		{
			case 'parents':
				$condition = 'i1.' . $this->column_left_id . ' BETWEEN i2.' . $this->column_left_id . ' AND i2.' . $this->column_right_id . '';
			break;

			case 'children':
				$condition = 'i2.' . $this->column_left_id . ' BETWEEN i1.' . $this->column_left_id . ' AND i1.' . $this->column_right_id . '';
			break;

			default:
				$condition = 'i2.' . $this->column_left_id . ' BETWEEN i1.' . $this->column_left_id . ' AND i1.' . $this->column_right_id . '
					OR i1.' . $this->column_left_id . ' BETWEEN i2.' . $this->column_left_id . ' AND i2.' . $this->column_right_id;
			break;
		}

		$rows = array();

		$sql = 'SELECT i2.*
			FROM ' . $this->table_name . ' i1
			LEFT JOIN ' . $this->table_name . " i2
				ON (($condition) " . $this->get_sql_where('AND', 'i2.') . ')
			WHERE i1.' . $this->column_item_id . ' = ' . (int) $item[$this->column_item_id] . '
				' . $this->get_sql_where('AND', 'i1.') . '
			ORDER BY i2.' . $this->column_left_id . ' ' . ($order_desc ? 'ASC' : 'DESC');
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			if (!$include_item && $item[$this->column_item_id] == $row[$this->column_item_id])
			{
				continue;
			}

			$rows[(int) $row[$this->column_item_id]] = $row;
		}
		$this->db->sql_freeresult($result);

		return $rows;
	}

	/**
	* Get base information of parent items
	*
	* Data is cached in the item_parents column in the item table
	*
	* @inheritdoc
	*/
	public function get_parent_data(array $item)
	{
		$parents = array();
		if ((int) $item[$this->column_parent_id])
		{
			if (!$item[$this->column_item_parents])
			{
				$sql = 'SELECT ' . implode(', ', $this->item_basic_data) . '
					FROM ' . $this->table_name . '
					WHERE ' . $this->column_left_id . ' < ' . (int) $item[$this->column_left_id] . '
						AND ' . $this->column_right_id . ' > ' . (int) $item[$this->column_right_id] . '
						' . $this->get_sql_where('AND') . '
					ORDER BY ' . $this->column_left_id . ' ASC';
				$result = $this->db->sql_query($sql);

				while ($row = $this->db->sql_fetchrow($result))
				{
					$parents[$row[$this->column_item_id]] = $row;
				}
				$this->db->sql_freeresult($result);

				$item_parents = serialize($parents);

				$sql = 'UPDATE ' . $this->table_name . '
					SET ' . $this->column_item_parents . " = '" . $this->db->sql_escape($item_parents) . "'
					WHERE " . $this->column_parent_id . ' = ' . (int) $item[$this->column_parent_id];
				$this->db->sql_query($sql);
			}
			else
			{
				$parents = unserialize($item[$this->column_item_parents]);
			}
		}

		return $parents;
	}

	/**
	* Remove a subset from the nested set
	*
	* @param array	$subset_items		Subset of items to remove
	* @param array	$bounding_item	Item containing the right bound of the subset
	* @param bool	$set_subset_zero	Should the parent, left and right id of the item be set to 0, or kept unchanged?
	* @param bool	$table_already_locked	Is the table already locked, or should we acquire a new lock?
	* @return	null
	*/
	protected function remove_subset(array $subset_items, array $bounding_item, $set_subset_zero = true, $table_already_locked = false)
	{
		if (!$table_already_locked && !$this->lock->acquire())
		{
			throw new phpbb_nestedset_exception($this->message_prefix . 'LOCK_FAILED_ACQUIRE');
		}

		$diff = sizeof($subset_items) * 2;
		$sql_subset_items = $this->db->sql_in_set($this->column_item_id, $subset_items);
		$sql_not_subset_items = $this->db->sql_in_set($this->column_item_id, $subset_items, true);

		$sql_is_parent = $this->column_left_id . ' <= ' . (int) $bounding_item[$this->column_right_id] . '
			AND ' . $this->column_right_id . ' >= ' . (int) $bounding_item[$this->column_right_id];

		$sql_is_right = $this->column_left_id . ' > ' . (int) $bounding_item[$this->column_right_id];

		$set_left_id = $this->db->sql_case($sql_is_right, $this->column_left_id . ' - ' . $diff, $this->column_left_id);
		$set_right_id = $this->db->sql_case($sql_is_parent . ' OR ' . $sql_is_right, $this->column_right_id . ' - ' . $diff, $this->column_right_id);

		if ($set_subset_zero)
		{
			$set_left_id = $this->db->sql_case($sql_subset_items, 0, $set_left_id);
			$set_right_id = $this->db->sql_case($sql_subset_items, 0, $set_right_id);
		}

		$sql = 'UPDATE ' . $this->table_name . '
			SET ' . $this->column_left_id . ' = ' . $set_left_id . ',
				' . $this->column_right_id . ' = ' . $set_right_id . ',
				' . (($set_subset_zero) ? $this->column_parent_id . ' = ' . $this->db->sql_case($sql_subset_items, 0, $this->column_parent_id) . ',' : '') . '
				' . $this->column_item_parents . " = ''
			" . ((!$set_subset_zero) ? ' WHERE ' . $sql_not_subset_items . ' ' . $this->get_sql_where('AND') : $this->get_sql_where('WHERE'));
		$this->db->sql_query($sql);

		if (!$table_already_locked)
		{
			$this->lock->release();
		}
	}

	/**
	* Add a subset to the nested set
	*
	* @param array	$subset_items		Subset of items to add
	* @param array	$new_parent	Item containing the right bound of the new parent
	* @return	int		New right id of the parent item
	*/
	protected function prepare_adding_subset(array $subset_items, array $new_parent)
	{
		$diff = sizeof($subset_items) * 2;
		$sql_not_subset_items = $this->db->sql_in_set($this->column_item_id, $subset_items, true);

		$set_left_id = $this->db->sql_case($this->column_left_id . ' > ' . (int) $new_parent[$this->column_right_id], $this->column_left_id . ' + ' . $diff, $this->column_left_id);
		$set_right_id = $this->db->sql_case($this->column_right_id . ' >= ' . (int) $new_parent[$this->column_right_id], $this->column_right_id . ' + ' . $diff, $this->column_right_id);

		$sql = 'UPDATE ' . $this->table_name . '
			SET ' . $this->column_left_id . ' = ' . $set_left_id . ',
				' . $this->column_right_id . ' = ' . $set_right_id . ',
				' . $this->column_item_parents . " = ''
			WHERE " . $sql_not_subset_items . '
				' . $this->get_sql_where('AND');
		$this->db->sql_query($sql);

		return $new_parent[$this->column_right_id] + $diff;
	}

	/**
	* Resets values required for the nested set system
	*
	* @param array	$item		Original item data
	* @return	array		Original item data + nested set defaults
	*/
	protected function reset_nestedset_values(array $item)
	{
		$item_data = array_merge($item, array(
			$this->column_parent_id		=> 0,
			$this->column_left_id		=> 0,
			$this->column_right_id		=> 0,
			$this->column_item_parents	=> '',
		));

		unset($item_data[$this->column_item_id]);

		return $item_data;
	}

	/**
	* @inheritdoc
	*/
	public function recalculate_nested_set($new_id, $parent_id = 0, $reset_ids = false)
	{
		if ($reset_ids)
		{
			if (!$this->lock->acquire())
			{
				throw new phpbb_nestedset_exception($this->message_prefix . 'LOCK_FAILED_ACQUIRE');
			}
			$this->db->sql_transaction('begin');

			$sql = 'UPDATE ' . $this->table_name . '
				SET ' . $this->db->sql_build_array('UPDATE', array(
					$this->column_left_id		=> 0,
					$this->column_right_id		=> 0,
					$this->column_item_parents	=> '',
				)) . '
				' . $this->get_sql_where('WHERE');
			$this->db->sql_query($sql);
		}

		$sql = 'SELECT *
			FROM ' . $this->table_name . '
			WHERE ' . $this->column_parent_id . ' = ' . (int) $parent_id . '
				' . $this->get_sql_where('AND') . '
			ORDER BY ' . $this->column_left_id . ', ' . $this->column_item_id . ' ASC';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			// First we update the left_id for this module
			if ($row[$this->column_left_id] != $new_id)
			{
				$sql = 'UPDATE ' . $this->table_name . '
					SET ' . $this->db->sql_build_array('UPDATE', array(
						$this->column_left_id		=> $new_id,
						$this->column_item_parents	=> '',
					)) . '
					WHERE ' . $this->column_item_id . ' = ' . (int) $row[$this->column_item_id];
				$this->db->sql_query($sql);
			}
			$new_id++;

			// Then we go through any children and update their left/right id's
			$new_id = $this->recalculate_nested_set($new_id, $row[$this->column_item_id]);

			// Then we come back and update the right_id for this module
			if ($row[$this->column_right_id] != $new_id)
			{
				$sql = 'UPDATE ' . $this->table_name . '
					SET ' . $this->db->sql_build_array('UPDATE', array($this->column_right_id => $new_id)) . '
					WHERE ' . $this->column_item_id . ' = ' . (int) $row[$this->column_item_id];
				$this->db->sql_query($sql);
			}
			$new_id++;
		}
		$this->db->sql_freeresult($result);


		if ($reset_ids)
		{
			$this->db->sql_transaction('commit');
			$this->lock->release();
		}

		return $new_id;
	}
}
