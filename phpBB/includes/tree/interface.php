<?php
/**
*
* @package tree
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

interface phpbb_tree_interface
{
	/**
	* Insert an item into the tree (also insert the rows into the table)
	*
	* @param array	$item	The item to be added
	* @return array Array with item data as set in the database
	*/
	public function insert(array $additional_data);

	/**
	* Delete an item from the tree (also deletes the rows form the table)
	*
	* Also deletes all subitems from the tree
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
	* Change parent item
	*
	* Moves the item to the bottom of the new parent's list of children
	*
	* @param int	$item_id			The item to be moved
	* @param int	$new_parent_id		The new parent item
	* @return bool True if the parent was set successfully
	*/
	public function change_parent($item, $new_parent_id);

	/**
	* Get children and parent branch of the item
	*
	* @param int		$item_id		The item id to get the parents/children from
	* @param bool		$order_desc		Order the items descending (most outer parent first)
	* @param bool		$include_item	Should the item (matching the given item id) be included in the list aswell
	* @return array			Array of items (containing all columns from the item table)
	*							ID => Item data
	*/
	public function get_full_branch_data($item_id, $order_desc, $include_item);

	/**
	* Get parent branch of the item
	*
	* @param int		$item_id		The item id to get the parents from
	* @param bool		$order_desc		Order the items descending (most outer parent first)
	* @param bool		$include_item	Should the item (matching the given item id) be included in the list aswell
	* @return array			Array of items (containing all columns from the item table)
	*							ID => Item data
	*/
	public function get_parent_branch_data($item_id, $order_desc, $include_item);

	/**
	* Get children branch of the item
	*
	* @param int		$item_id		The item id to get the children from
	* @param bool		$order_desc		Order the items descending (most outer parent first)
	* @param bool		$include_item	Should the item (matching the given item id) be included in the list aswell
	* @return array			Array of items (containing all columns from the item table)
	*							ID => Item data
	*/
	public function get_children_branch_data($item_id, $order_desc, $include_item);

	/**
	* Get base information of parent items
	*
	* @param array	$item		The item to get the branch from
	* @return array			Array of items (containing basic columns from the item table)
	*							ID => Item data
	*/
	public function get_parent_data(array $item);
}
