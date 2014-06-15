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

namespace phpbb\tree;

abstract class nestedset implements \phpbb\tree\tree_interface
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\lock\db */
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
	* List of item properties to be cached in the item_parents column
	* @var array
	*/
	protected $item_basic_data = array('*');

	/**
	* Construct
	*
	* @param \phpbb\db\driver\driver_interface	$db		Database connection
	* @param \phpbb\lock\db		$lock	Lock class used to lock the table when moving forums around
	* @param string			$table_name			Table name
	* @param string			$message_prefix		Prefix for the messages thrown by exceptions
	* @param string			$sql_where			Additional SQL restrictions for the queries
	* @param array			$item_basic_data	Array with basic item data that is stored in item_parents
	* @param array			$columns			Array with column names to overwrite
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\lock\db $lock, $table_name, $message_prefix = '', $sql_where = '', $item_basic_data = array(), $columns = array())
	{
		$this->db = $db;
		$this->lock = $lock;

		$this->table_name = $table_name;
		$this->message_prefix = $message_prefix;
		$this->sql_where = $sql_where;
		$this->item_basic_data = (!empty($item_basic_data)) ? $item_basic_data : array('*');

		if (!empty($columns))
		{
			foreach ($columns as $column => $name)
			{
				$column_name = 'column_' . $column;
				$this->$column_name = $name;
			}
		}
	}

	/**
	* Returns additional sql where restrictions
	*
	* @param string		$operator		SQL operator that needs to be prepended to sql_where,
	*									if it is not empty.
	* @param string		$column_prefix	Prefix that needs to be prepended to column names
	* @return string		Returns additional where statements to narrow down the tree,
	*						prefixed with operator and prepended column_prefix to column names
	*/
	public function get_sql_where($operator = 'AND', $column_prefix = '')
	{
		return (!$this->sql_where) ? '' : $operator . ' ' . sprintf($this->sql_where, $column_prefix);
	}

	/**
	* Acquires a lock on the item table
	*
	* @return bool	True if the lock was acquired, false if it has been acquired previously
	*
	* @throws \RuntimeException If the lock could not be acquired
	*/
	protected function acquire_lock()
	{
		if ($this->lock->owns_lock())
		{
			return false;
		}

		if (!$this->lock->acquire())
		{
			throw new \RuntimeException($this->message_prefix . 'LOCK_FAILED_ACQUIRE');
		}

		return true;
	}

	/**
	* {@inheritdoc}
	*/
	public function insert(array $additional_data)
	{
		$item_data = $this->reset_nestedset_values($additional_data);

		$sql = 'INSERT INTO ' . $this->table_name . ' ' . $this->db->sql_build_array('INSERT', $item_data);
		$this->db->sql_query($sql);

		$item_data[$this->column_item_id] = (int) $this->db->sql_nextid();

		return array_merge($item_data, $this->add_item_to_nestedset($item_data[$this->column_item_id]));
	}

	/**
	* Add an item which already has a database row at the end of the tree
	*
	* @param int	$item_id	The item to be added
	* @return array		Array with updated data, if the item was added successfully
	*					Empty array otherwise
	*/
	protected function add_item_to_nestedset($item_id)
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
			WHERE ' . $this->column_item_id . ' = ' . (int) $item_id . '
				AND ' . $this->column_parent_id . ' = 0
				AND ' . $this->column_left_id . ' = 0
				AND ' . $this->column_right_id . ' = 0';
		$this->db->sql_query($sql);

		return ($this->db->sql_affectedrows() == 1) ? $update_item_data : array();
	}

	/**
	* Remove an item from the tree without deleting it from the database
	*
	* Also removes all subitems from the tree without deleting them from the database either
	*
	* @param int	$item_id	The item to be deleted
	* @return array		Item ids that have been removed
	* @throws \OutOfBoundsException
	*/
	protected function remove_item_from_nestedset($item_id)
	{
		$item_id = (int) $item_id;
		if (!$item_id)
		{
			throw new \OutOfBoundsException($this->message_prefix . 'INVALID_ITEM');
		}

		$items = $this->get_subtree_data($item_id);
		$item_ids = array_keys($items);

		if (empty($items) || !isset($items[$item_id]))
		{
			throw new \OutOfBoundsException($this->message_prefix . 'INVALID_ITEM');
		}

		$this->remove_subset($item_ids, $items[$item_id]);

		return $item_ids;
	}

	/**
	* {@inheritdoc}
	*/
	public function delete($item_id)
	{
		$removed_items = $this->remove_item_from_nestedset($item_id);

		$sql = 'DELETE FROM ' . $this->table_name . '
			WHERE ' . $this->db->sql_in_set($this->column_item_id, $removed_items) . '
			' . $this->get_sql_where('AND');
		$this->db->sql_query($sql);

		return $removed_items;
	}

	/**
	* {@inheritdoc}
	*/
	public function move($item_id, $delta)
	{
		if ($delta == 0)
		{
			return false;
		}

		$this->acquire_lock();

		$action = ($delta > 0) ? 'move_up' : 'move_down';
		$delta = abs($delta);

		// Keep $this->get_sql_where() here, to ensure we are in the right tree.
		$sql = 'SELECT *
			FROM ' . $this->table_name . '
			WHERE ' . $this->column_item_id . ' = ' . (int) $item_id . '
				' . $this->get_sql_where();
		$result = $this->db->sql_query_limit($sql, $delta);
		$item = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$item)
		{
			$this->lock->release();
			throw new \OutOfBoundsException($this->message_prefix . 'INVALID_ITEM');
		}

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

		$target = false;
		while ($row = $this->db->sql_fetchrow($result))
		{
			$target = $row;
		}
		$this->db->sql_freeresult($result);

		if (!$target)
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
			$left_id = (int) $target[$this->column_left_id];
			$right_id = (int) $item[$this->column_right_id];

			$diff_up = (int) $item[$this->column_left_id] - (int) $target[$this->column_left_id];
			$diff_down = (int) $item[$this->column_right_id] + 1 - (int) $item[$this->column_left_id];

			$move_up_left = (int) $item[$this->column_left_id];
			$move_up_right = (int) $item[$this->column_right_id];
		}
		else
		{
			$left_id = (int) $item[$this->column_left_id];
			$right_id = (int) $target[$this->column_right_id];

			$diff_up = (int) $item[$this->column_right_id] + 1 - (int) $item[$this->column_left_id];
			$diff_down = (int) $target[$this->column_right_id] - (int) $item[$this->column_right_id];

			$move_up_left = (int) $item[$this->column_right_id] + 1;
			$move_up_right = (int) $target[$this->column_right_id];
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
			END
			WHERE
				" . $this->column_left_id . " BETWEEN {$left_id} AND {$right_id}
				AND " . $this->column_right_id . " BETWEEN {$left_id} AND {$right_id}
				" . $this->get_sql_where();
		$this->db->sql_query($sql);

		$this->lock->release();

		return true;
	}

	/**
	* {@inheritdoc}
	*/
	public function move_down($item_id)
	{
		return $this->move($item_id, -1);
	}

	/**
	* {@inheritdoc}
	*/
	public function move_up($item_id)
	{
		return $this->move($item_id, 1);
	}

	/**
	* {@inheritdoc}
	*/
	public function move_children($current_parent_id, $new_parent_id)
	{
		$current_parent_id = (int) $current_parent_id;
		$new_parent_id = (int) $new_parent_id;

		if ($current_parent_id == $new_parent_id)
		{
			return false;
		}

		if (!$current_parent_id)
		{
			throw new \OutOfBoundsException($this->message_prefix . 'INVALID_ITEM');
		}

		$this->acquire_lock();

		$item_data = $this->get_subtree_data($current_parent_id);
		if (!isset($item_data[$current_parent_id]))
		{
			$this->lock->release();
			throw new \OutOfBoundsException($this->message_prefix . 'INVALID_ITEM');
		}

		$current_parent = $item_data[$current_parent_id];
		unset($item_data[$current_parent_id]);
		$move_items = array_keys($item_data);

		if (($current_parent[$this->column_right_id] - $current_parent[$this->column_left_id]) <= 1)
		{
			$this->lock->release();
			return false;
		}

		if (in_array($new_parent_id, $move_items))
		{
			$this->lock->release();
			throw new \OutOfBoundsException($this->message_prefix . 'INVALID_PARENT');
		}

		$diff = sizeof($move_items) * 2;
		$sql_exclude_moved_items = $this->db->sql_in_set($this->column_item_id, $move_items, true);

		$this->db->sql_transaction('begin');

		$this->remove_subset($move_items, $current_parent, false, true);

		if ($new_parent_id)
		{
			// Retrieve new-parent again, it may have been changed...
			$sql = 'SELECT *
				FROM ' . $this->table_name . '
				WHERE ' . $this->column_item_id . ' = ' . $new_parent_id;
			$result = $this->db->sql_query($sql);
			$new_parent = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if (!$new_parent)
			{
				$this->db->sql_transaction('rollback');
				$this->lock->release();
				throw new \OutOfBoundsException($this->message_prefix . 'INVALID_PARENT');
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
				' . $this->column_parent_id . ' = ' . $this->db->sql_case($this->column_parent_id . ' = ' . $current_parent_id, $new_parent_id, $this->column_parent_id) . ',
				' . $this->column_item_parents . " = ''
			WHERE " . $this->db->sql_in_set($this->column_item_id, $move_items) . '
				' . $this->get_sql_where('AND');
		$this->db->sql_query($sql);

		$this->db->sql_transaction('commit');
		$this->lock->release();

		return true;
	}

	/**
	* {@inheritdoc}
	*/
	public function change_parent($item_id, $new_parent_id)
	{
		$item_id = (int) $item_id;
		$new_parent_id = (int) $new_parent_id;

		if ($item_id == $new_parent_id)
		{
			return false;
		}

		if (!$item_id)
		{
			throw new \OutOfBoundsException($this->message_prefix . 'INVALID_ITEM');
		}

		$this->acquire_lock();

		$item_data = $this->get_subtree_data($item_id);
		if (!isset($item_data[$item_id]))
		{
			$this->lock->release();
			throw new \OutOfBoundsException($this->message_prefix . 'INVALID_ITEM');
		}

		$item = $item_data[$item_id];
		$move_items = array_keys($item_data);

		if (in_array($new_parent_id, $move_items))
		{
			$this->lock->release();
			throw new \OutOfBoundsException($this->message_prefix . 'INVALID_PARENT');
		}

		$diff = sizeof($move_items) * 2;
		$sql_exclude_moved_items = $this->db->sql_in_set($this->column_item_id, $move_items, true);

		$this->db->sql_transaction('begin');

		$this->remove_subset($move_items, $item, false, true);

		if ($new_parent_id)
		{
			// Retrieve new-parent again, it may have been changed...
			$sql = 'SELECT *
				FROM ' . $this->table_name . '
				WHERE ' . $this->column_item_id . ' = ' . $new_parent_id;
			$result = $this->db->sql_query($sql);
			$new_parent = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if (!$new_parent)
			{
				$this->db->sql_transaction('rollback');
				$this->lock->release();
				throw new \OutOfBoundsException($this->message_prefix . 'INVALID_PARENT');
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
				' . $this->column_parent_id . ' = ' . $this->db->sql_case($this->column_item_id . ' = ' . $item_id, $new_parent_id, $this->column_parent_id) . ',
				' . $this->column_item_parents . " = ''
			WHERE " . $this->db->sql_in_set($this->column_item_id, $move_items) . '
				' . $this->get_sql_where('AND');
		$this->db->sql_query($sql);

		$this->db->sql_transaction('commit');
		$this->lock->release();

		return true;
	}

	/**
	* {@inheritdoc}
	*/
	public function get_path_and_subtree_data($item_id, $order_asc = true, $include_item = true)
	{
		$condition = 'i2.' . $this->column_left_id . ' BETWEEN i1.' . $this->column_left_id . ' AND i1.' . $this->column_right_id . '
			OR i1.' . $this->column_left_id . ' BETWEEN i2.' . $this->column_left_id . ' AND i2.' . $this->column_right_id;

		return $this->get_set_of_nodes_data($item_id, $condition, $order_asc, $include_item);
	}

	/**
	* {@inheritdoc}
	*/
	public function get_path_data($item_id, $order_asc = true, $include_item = true)
	{
		$condition = 'i1.' . $this->column_left_id . ' BETWEEN i2.' . $this->column_left_id . ' AND i2.' . $this->column_right_id . '';

		return $this->get_set_of_nodes_data($item_id, $condition, $order_asc, $include_item);
	}

	/**
	* {@inheritdoc}
	*/
	public function get_subtree_data($item_id, $order_asc = true, $include_item = true)
	{
		$condition = 'i2.' . $this->column_left_id . ' BETWEEN i1.' . $this->column_left_id . ' AND i1.' . $this->column_right_id . '';

		return $this->get_set_of_nodes_data($item_id, $condition, $order_asc, $include_item);
	}

	/**
	* Get items that are related to the given item by the condition
	*
	* @param int		$item_id		Id of the item to retrieve the node set from
	* @param string		$condition		Query string restricting the item list
	* @param bool		$order_asc		Order the items ascending by their left_id
	* @param bool		$include_item	Should the item matching the given item id be included in the list as well
	* @return array			Array of items (containing all columns from the item table)
	*							ID => Item data
	*/
	protected function get_set_of_nodes_data($item_id, $condition, $order_asc = true, $include_item = true)
	{
		$rows = array();

		$sql = 'SELECT i2.*
			FROM ' . $this->table_name . ' i1
			LEFT JOIN ' . $this->table_name . " i2
				ON (($condition) " . $this->get_sql_where('AND', 'i2.') . ')
			WHERE i1.' . $this->column_item_id . ' = ' . (int) $item_id . '
				' . $this->get_sql_where('AND', 'i1.') . '
			ORDER BY i2.' . $this->column_left_id . ' ' . ($order_asc ? 'ASC' : 'DESC');
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			if (!$include_item && $item_id == $row[$this->column_item_id])
			{
				continue;
			}

			$rows[(int) $row[$this->column_item_id]] = $row;
		}
		$this->db->sql_freeresult($result);

		return $rows;
	}

	/**
	* Get basic data of all parent items
	*
	* Basic data is defined in the $item_basic_data property.
	* Data is cached in the item_parents column in the item table
	*
	* @param array	$item		The item to get the path from
	* @return array			Array of items (containing basic columns from the item table)
	*							ID => Item data
	*/
	public function get_path_basic_data(array $item)
	{
		$parents = array();
		if ($item[$this->column_parent_id])
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
	* Get all items from the tree
	*
	* @param bool		$order_asc		Order the items ascending by their left_id
	* @return array			Array of items (containing all columns from the item table)
	*							ID => Item data
	*/
	public function get_all_tree_data($order_asc = true)
	{
		$rows = array();

		$sql = 'SELECT *
			FROM ' . $this->table_name . ' ' .
			$this->get_sql_where('WHERE') . '
			ORDER BY ' . $this->column_left_id . ' ' . ($order_asc ? 'ASC' : 'DESC');
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$rows[(int) $row[$this->column_item_id]] = $row;
		}
		$this->db->sql_freeresult($result);

		return $rows;
	}

	/**
	* Remove a subset from the nested set
	*
	* @param array	$subset_items		Subset of items to remove
	* @param array	$bounding_item		Item containing the right bound of the subset
	* @param bool	$set_subset_zero	Should the parent, left and right id of the items be set to 0, or kept unchanged?
	*									In case of removing an item from the tree, we should the values to 0
	*									In case of moving an item, we shouldkeep the original values, in order to allow "+ diff" later
	* @return	null
	*/
	protected function remove_subset(array $subset_items, array $bounding_item, $set_subset_zero = true)
	{
		$acquired_new_lock = $this->acquire_lock();

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
			SET ' . (($set_subset_zero) ? $this->column_parent_id . ' = ' . $this->db->sql_case($sql_subset_items, 0, $this->column_parent_id) . ',' : '') . '
				' . $this->column_left_id . ' = ' . $set_left_id . ',
				' . $this->column_right_id . ' = ' . $set_right_id . '
			' . ((!$set_subset_zero) ? ' WHERE ' . $sql_not_subset_items . ' ' . $this->get_sql_where('AND') : $this->get_sql_where('WHERE'));
		$this->db->sql_query($sql);

		if ($acquired_new_lock)
		{
			$this->lock->release();
		}
	}

	/**
	* Prepare adding a subset to the nested set
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
				' . $this->column_right_id . ' = ' . $set_right_id . '
			WHERE ' . $sql_not_subset_items . '
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
	* Regenerate left/right ids from parent/child relationship
	*
	* This method regenerates the left/right ids for the tree based on
	* the parent/child relations. This function executes three queries per
	* item, so it should only be called, when the set has one of the following
	* problems:
	*	- The set has a duplicated value inside the left/right id chain
	*	- The set has a missing value inside the left/right id chain
	*	- The set has items that do not have a left/right id set
	*
	* When regenerating the items, the items are sorted by parent id and their
	* current left id, so the current child/parent relationships are kept
	* and running the function on a working set will not change the order.
	*
	* @param int	$new_id		First left_id to be used (should start with 1)
	* @param int	$parent_id	parent_id of the current set (default = 0)
	* @param bool	$reset_ids	Should we reset all left_id/right_id on the first call?
	* @return	int		$new_id		The next left_id/right_id that should be used
	*/
	public function regenerate_left_right_ids($new_id, $parent_id = 0, $reset_ids = false)
	{
		if ($acquired_new_lock = $this->acquire_lock())
		{
			$this->db->sql_transaction('begin');

			if (!$reset_ids)
			{
				$sql = 'UPDATE ' . $this->table_name . '
					SET ' . $this->column_item_parents . " = ''
					" . $this->get_sql_where('WHERE');
				$this->db->sql_query($sql);
			}
		}

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
					SET ' . $this->db->sql_build_array('UPDATE', array($this->column_left_id => $new_id)) . '
					WHERE ' . $this->column_item_id . ' = ' . (int) $row[$this->column_item_id];
				$this->db->sql_query($sql);
			}
			$new_id++;

			// Then we go through any children and update their left/right id's
			$new_id = $this->regenerate_left_right_ids($new_id, $row[$this->column_item_id]);

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

		if ($acquired_new_lock)
		{
			$this->db->sql_transaction('commit');
			$this->lock->release();
		}

		return $new_id;
	}
}
