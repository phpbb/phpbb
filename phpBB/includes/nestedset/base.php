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
	/** @var phpbb_db_driver*/
	protected $db;

	/** @var String */
	protected $table_name;

	/** @var String */
	protected $item_class = 'phpbb_nestedset_item_base';

	/**
	* Column names in the table
	* @var String
	*/
	protected $columns_item_id = 'item_id';
	protected $columns_left_id = 'left_id';
	protected $columns_right_id = 'right_id';
	protected $columns_parent_id = 'parent_id';
	protected $columns_item_parents = 'item_parents';

	/**
	* Additional SQL restrictions
	* Allows to have multiple nested sets in one table
	* @var String
	*/
	protected $sql_where = '';

	/**
	* List of item properties to be cached in $item_parents
	* @var array
	*/
	protected $item_basic_data = array('*');

	/**
	* Delete an item from the nested set (also deletes the rows form the table)
	*
	* Also deletes all subitems from the nested set
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
		$item_data = array_merge($additional_data, array(
			$this->column_parent_id		=> 0,
			$this->column_left_id		=> 0,
			$this->column_right_id		=> 0,
			$this->column_item_parents	=> '',
		));

		unset($item_data[$this->column_item_id]);

		$sql = 'INSERT INTO ' . $this->table_name . ' ' . $this->db->sql_build_array('INSERT', $item_data);
		$this->db->sql_query($sql);

		$item_data[$this->column_item_id] = (int) $this->db->sql_nextid();

		$item = new $this->item_class($item_data);

		return array_merge($item_data, $this->add($item));
	}

	/**
	* @inheritdoc
	*/
	public function add(phpbb_nestedset_item_interface $item)
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
			WHERE ' . $this->column_item_id . ' = ' . $item->get_item_id();
		$this->db->sql_query($sql);

		return $update_item_data;
	}

	/**
	* @inheritdoc
	*/
	public function remove(phpbb_nestedset_item_interface $item)
	{
		if ($item->has_children())
		{
			$items = array_keys($this->get_branch_data($item, 'children'));
		}
		else
		{
			$items = array($item->get_item_id());
		}

		$this->remove_subset($items, $item);

		return $items;
	}

	/**
	* @inheritdoc
	*/
	public function delete(phpbb_nestedset_item_interface $item)
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
	public function move(phpbb_nestedset_item_interface $item, $delta)
	{
		if ($delta == 0)
		{
			return false;
		}

		$action = ($delta > 0) ? 'move_up' : 'move_down';
		$delta = abs($delta);

		/**
		* Fetch all the siblings between the item's current spot
		* and where we want to move it to. If there are less than $delta
		* siblings between the current spot and the target then the
		* item will move as far as possible
		*/
		$sql = 'SELECT ' . implode(', ', $this->table_columns) . '
			FROM ' . $this->table_name . '
			WHERE ' . $this->column_parent_id . ' = ' . $item->get_parent_id() . '
				' . $this->get_sql_where() . '
				AND ';

		if ($action == 'move_up')
		{
			$sql .= $this->column_right_id . ' < ' . $item->get_right_id() . ' ORDER BY ' . $this->column_right_id . ' DESC';
		}
		else
		{
			$sql .= $this->column_left_id . ' > ' . $item->get_left_id() . ' ORDER BY ' . $this->column_left_id . ' ASC';
		}

		$result = $this->db->sql_query_limit($sql, $delta);

		$target = null;
		while ($row = $this->db->sql_fetchrow($result))
		{
			$target = new $this->item_class($row);
		}
		$this->db->sql_freeresult($result);

		if (is_null($target))
		{
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
			$left_id = $target->get_left_id();
			$right_id = $item->get_right_id();

			$diff_up = $item->get_left_id() - $target->get_left_id();
			$diff_down = $item->get_right_id() + 1 - $item->get_left_id();

			$move_up_left = $item->get_left_id();
			$move_up_right = $item->get_right_id();
		}
		else
		{
			$left_id = $item->get_left_id();
			$right_id = $target->get_right_id();

			$diff_up = $item->get_right_id() + 1 - $item->get_left_id();
			$diff_down = $target->get_right_id() - $item->get_right_id();

			$move_up_left = $item->get_right_id() + 1;
			$move_up_right = $target->get_right_id();
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

		return true;
	}

	/**
	* @inheritdoc
	*/
	public function move_down(phpbb_nestedset_item_interface $item)
	{
		return $this->move($item, -1);
	}

	/**
	* @inheritdoc
	*/
	public function move_up(phpbb_nestedset_item_interface $item)
	{
		return $this->move($item, 1);
	}

	/**
	* @inheritdoc
	*/
	public function move_children(phpbb_nestedset_item_interface $current_parent, phpbb_nestedset_item_interface $new_parent)
	{
		if (!$current_parent->has_children() || !$current_parent->get_item_id() || $current_parent->get_item_id() == $new_parent->get_item_id())
		{
			return false;
		}

		$move_items = array_keys($this->get_branch_data($current_parent, 'children', true, false));

		if (in_array($new_parent->get_item_id(), $move_items))
		{
			throw new phpbb_nestedset_exception('INVALID_PARENT');
		}

		$diff = sizeof($move_items) * 2;
		$sql_exclude_moved_items = $this->db->sql_in_set($this->column_item_id, $move_items, true);

		$this->db->sql_transaction('begin');

		$this->remove_subset($move_items, $current_parent, false);

		if ($new_parent->get_item_id())
		{
			// Retrieve new-parent again, it may have been changed...
			$sql = 'SELECT *
				FROM ' . $this->table_name . '
				WHERE ' . $this->column_item_id . ' = ' . $new_parent->get_item_id();
			$result = $this->db->sql_query($sql);
			$parent_data = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if (!$parent_data)
			{
				$this->db->sql_transaction('rollback');
				throw new phpbb_nestedset_exception('INVALID_PARENT');
			}

			$new_parent = new $this->item_class($parent_data);

			$new_right_id = $this->prepare_adding_subset($move_items, $new_parent);

			if ($new_right_id > $current_parent->get_right_id())
			{
				$diff = ' + ' . ($new_right_id - $current_parent->get_right_id());
			}
			else
			{
				$diff = ' - ' . abs($new_right_id - $current_parent->get_right_id());
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

			$diff = ' + ' . ($row[$this->column_right_id] - $current_parent->get_left_id());
		}

		$sql = 'UPDATE ' . $this->table_name . '
			SET ' . $this->column_left_id . ' = ' . $this->column_left_id . $diff . ',
				' . $this->column_right_id . ' = ' . $this->column_right_id . $diff . ',
				' . $this->column_parent_id . ' = ' . $this->db->sql_case($this->column_parent_id . ' = ' . $current_parent->get_item_id(), $new_parent->get_item_id(), $this->column_parent_id) . ',
				' . $this->column_item_parents . " = ''
			WHERE " . $this->db->sql_in_set($this->column_item_id, $move_items) . '
				' . $this->get_sql_where('AND');
		$this->db->sql_query($sql);

		$this->db->sql_transaction('commit');

		return true;
	}

	/**
	* @inheritdoc
	*/
	public function set_parent(phpbb_nestedset_item_interface $item, phpbb_nestedset_item_interface $new_parent)
	{
		$move_items = array_keys($this->get_branch_data($item, 'children'));

		if (in_array($new_parent->get_item_id(), $move_items))
		{
			throw new phpbb_nestedset_exception('INVALID_PARENT');
		}

		$diff = sizeof($move_items) * 2;
		$sql_exclude_moved_items = $this->db->sql_in_set($this->column_item_id, $move_items, true);

		$this->db->sql_transaction('begin');

		$this->remove_subset($move_items, $item, false);

		if ($new_parent->get_item_id())
		{
			// Retrieve new-parent again, it may have been changed...
			$sql = 'SELECT *
				FROM ' . $this->table_name . '
				WHERE ' . $this->column_item_id . ' = ' . $new_parent->get_item_id();
			$result = $this->db->sql_query($sql);
			$parent_data = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if (!$parent_data)
			{
				$this->db->sql_transaction('rollback');
				throw new phpbb_nestedset_exception('INVALID_PARENT');
			}

			$new_parent = new $this->item_class($parent_data);

			$new_right_id = $this->prepare_adding_subset($move_items, $new_parent);

			if ($new_right_id > $item->get_right_id())
			{
				$diff = ' + ' . ($new_right_id - $item->get_right_id() - 1);
			}
			else
			{
				$diff = ' - ' . abs($new_right_id - $item->get_right_id() - 1);
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

			$diff = ' + ' . ($row[$this->column_right_id] - $item->get_left_id() + 1);
		}

		$sql = 'UPDATE ' . $this->table_name . '
			SET ' . $this->column_left_id . ' = ' . $this->column_left_id . $diff . ',
				' . $this->column_right_id . ' = ' . $this->column_right_id . $diff . ',
				' . $this->column_parent_id . ' = ' . $this->db->sql_case($this->column_item_id . ' = ' . $item->get_item_id(), $new_parent->get_item_id(), $this->column_parent_id) . ',
				' . $this->column_item_parents . " = ''
			WHERE " . $this->db->sql_in_set($this->column_item_id, $move_items) . '
				' . $this->get_sql_where('AND');
		$this->db->sql_query($sql);

		$this->db->sql_transaction('commit');

		return true;
	}

	/**
	* @inheritdoc
	*/
	public function get_branch_data(phpbb_nestedset_item_interface $item, $type = 'all', $order_desc = true, $include_item = true)
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
			WHERE i1.' . $this->column_item_id . ' = ' . $item->get_item_id() . '
				' . $this->get_sql_where('AND', 'i1.') . '
			ORDER BY i2.' . $this->column_left_id . ' ' . ($order_desc ? 'ASC' : 'DESC');
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			if (!$include_item && $item->get_item_id() === (int) $row[$this->column_item_id])
			{
				continue;
			}

			$rows[$row[$this->column_item_id]] = $row;
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
	public function get_parent_data(phpbb_nestedset_item_interface $item)
	{
		$parents = array();
		if ($item->get_parent_id())
		{
			if (!$item->get_item_parents_data())
			{
				$sql = 'SELECT ' . implode(', ', $this->item_basic_data) . '
					FROM ' . $this->table_name . '
					WHERE ' . $this->column_left_id . ' < ' . $item->get_left_id() . '
						AND ' . $this->column_right_id . ' > ' . $item->get_right_id() . '
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
					WHERE " . $this->column_parent_id . ' = ' . $item->get_parent_id();
				$this->db->sql_query($sql);
			}
			else
			{
				$parents = unserialize($item->get_item_parents_data());
			}
		}

		return $parents;
	}

	/**
	* Remove a subset from the nested set
	*
	* @param array	$subset_items		Subset of items to remove
	* @param phpbb_nestedset_item_interface	$bounding_item	Item containing the right bound of the subset
	* @param bool	$set_subset_zero	Should the parent, left and right id of the item be set to 0, or kept unchanged?
	* @return	null
	*/
	protected function remove_subset(array $subset_items, phpbb_nestedset_item_interface $bounding_item, $set_subset_zero = true)
	{
		$diff = sizeof($subset_items) * 2;
		$sql_subset_items = $this->db->sql_in_set($this->column_item_id, $subset_items);
		$sql_not_subset_items = $this->db->sql_in_set($this->column_item_id, $subset_items, true);

		$sql_is_parent = $this->column_left_id . ' <= ' . $bounding_item->get_right_id() . '
			AND ' . $this->column_right_id . ' >= ' . $bounding_item->get_right_id();

		$sql_is_right = $this->column_left_id . ' > ' . $bounding_item->get_right_id();

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
	}

	/**
	* Add a subset to the nested set
	*
	* @param array	$subset_items		Subset of items to add
	* @param phpbb_nestedset_item_interface	$new_parent	Item containing the right bound of the new parent
	* @return	int		New right id of the parent item
	*/
	protected function prepare_adding_subset(array $subset_items, phpbb_nestedset_item_interface $new_parent)
	{
		$diff = sizeof($subset_items) * 2;
		$sql_not_subset_items = $this->db->sql_in_set($this->column_item_id, $subset_items, true);

		$set_left_id = $this->db->sql_case($this->column_left_id . ' > ' . $new_parent->get_right_id(), $this->column_left_id . ' + ' . $diff, $this->column_left_id);
		$set_right_id = $this->db->sql_case($this->column_right_id . ' >= ' . $new_parent->get_right_id(), $this->column_right_id . ' + ' . $diff, $this->column_right_id);

		$sql = 'UPDATE ' . $this->table_name . '
			SET ' . $this->column_left_id . ' = ' . $set_left_id . ',
				' . $this->column_right_id . ' = ' . $set_right_id . ',
				' . $this->column_item_parents . " = ''
			WHERE " . $sql_not_subset_items . '
				' . $this->get_sql_where('AND');
		$this->db->sql_query($sql);

		return $new_parent->get_right_id() + $diff;
	}

	/**
	* @inheritdoc
	*/
	public function recalculate_nested_set($new_id, $parent_id = 0, $reset_ids = false)
	{
		if ($reset_ids)
		{
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
					WHERE ' . $this->column_item_id . ' = ' . $row[$this->column_item_id];
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
					WHERE ' . $this->column_item_id . ' = ' . $row[$this->column_item_id];
				$this->db->sql_query($sql);
			}
			$new_id++;
		}
		$this->db->sql_freeresult($result);

		return $new_id;
	}
}
