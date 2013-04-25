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

interface phpbb_nestedset_interface
{
	/**
	* Insert an item into the nested set (also insert the rows into the table)
	*
	* @param array	$item	The item to be added
	* @return array Array with item data as set in the database
	*/
	public function insert(array $additional_data);

	/**
	* Add an item at the end of the nested set
	*
	* @param array	$item	The item to be added
	* @return bool True if the item was added
	*/
	public function add(array $item);

	/**
	* Remove an item from the nested set
	*
	* Also removes all subitems from the nested set
	*
	* @param int	$item_id	The item to be deleted
	* @return array		Item ids that have been removed
	*/
	public function remove($item);

	/**
	* Delete an item from the nested set (also deletes the rows form the table)
	*
	* Also deletes all subitems from the nested set
	*
	* @param int	$item_id	The item to be deleted
	* @return array		Item ids that have been deleted
	*/
	public function delete($item);

	/**
	* Move an item by a given delta
	*
	* An item is only moved up/down within the same parent. If the delta is
	* larger then the number of children, the item is moved to the top/bottom
	* of the list of children within this parent.
	*
	* @param int	$item_id	The item to be moved
	* @param int	$delta		Number of steps to move this item, < 0 => down, > 0 => up
	* @return bool True if the item was moved
	*/
	public function move($item_id, $delta);

	/**
	* Move an item down by 1
	*
	* @param int	$item_id	The item to be moved
	* @return bool True if the item was moved
	*/
	public function move_down($item_id);

	/**
	* Move an item up by 1
	*
	* @param int	$item_id	The item to be moved
	* @return bool True if the item was moved
	*/
	public function move_up($item_id);

	/**
	* Moves all children of one item to another item
	*
	* If the new parent already has children, the new children are appended
	* to the list.
	*
	* @param int	$current_parent_id	The current parent item
	* @param int	$new_parent_id		The new parent item
	* @return bool True if any items where moved
	*/
	public function move_children($current_parent_id, $new_parent_id);

	/**
	* Set the parent item
	*
	* @param int	$item_id			The item to be moved
	* @param int	$new_parent_id		The new parent item
	* @return bool True if the parent was set successfully
	*/
	public function set_parent($item, $new_parent_id);

	/**
	* Get branch of the item
	*
	* This method can return all parents, children or both of the given item
	*
	* @param int		$item_id		The item id to get the parents from
	* @param string		$type			One of all|parent|children
	* @param bool		$order_desc		Order the items descending (most outer parent first)
	* @param bool		$include_item	Should the given item be included in the list aswell
	* @return array			Array of items (containing all columns from the item table)
	*							ID => Item data
	*/
	public function get_branch_data($item_id, $type, $order_desc, $include_item);

	/**
	* Get base information of parent items
	*
	* @param array	$item		The item to get the branch from
	* @return array			Array of items (containing basic columns from the item table)
	*							ID => Item data
	*/
	public function get_parent_data(array $item);

	/**
	* Regenerate left/right ids from parent/child relationship
	*
	* This method regenerates the left/right ids for the nested set based on
	* the parent/child relations. This function executes three queries per
	* item, so it should only be called, when the set has one of the following
	* problems:
	*	- The set has a duplicated value inside the left/right id chain
	*	- The set has a missing value inside the left/right id chain
	*	- The set has items that do not have a left/right is set
	*
	* When regenerating the items, the items are sorted by parent id and their
	* current left id, so the current child/parent relationships are kept
	* and running the function on a working set will not change any orders.
	*
	* @param int	$new_id		First left_id to be used (should start with 1)
	* @param int	$parent_id	parent_id of the current set (default = 0)
	* @param bool	$reset_ids	Should we reset all left_id/right_id on the first call?
	* @return	int		$new_id		The next left_id/right_id that should be used
	*/
	public function regenerate_left_right_ids($new_id, $parent_id = 0, $reset_ids = false);
}
