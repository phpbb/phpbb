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
	* Inserts an item into the database table and into the tree.
	*
	* @param array	$item	The item to be added
	* @return array Array with item data as set in the database
	*/
	public function insert(array $additional_data);

	/**
	* Delete an item from the tree and from the database table
	*
	* Also deletes the subtree from the tree and from the database table
	*
	* @param int	$item_id	The item to be deleted
	* @return array		Item ids that have been deleted
	*/
	public function delete($item_id);

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
	public function change_parent($item_id, $new_parent_id);

	/**
	* Get all items that are either ancestors or descendants of the item
	*
	* @param int		$item_id		Id of the item to retrieve the ancestors/descendants from
	* @param bool		$order_asc		Order the items ascendingly (most outer ancestor first)
	* @param bool		$include_item	Should the item matching the given item id be included in the list as well
	* @return array			Array of items (containing all columns from the item table)
	*							ID => Item data
	*/
	public function get_path_and_subtree_data($item_id, $order_asc, $include_item);

	/**
	* Get all of the item's ancestors
	*
	* @param int		$item_id		Id of the item to retrieve the ancestors from
	* @param bool		$order_asc		Order the items ascendingly (most outer ancestor first)
	* @param bool		$include_item	Should the item matching the given item id be included in the list as well
	* @return array			Array of items (containing all columns from the item table)
	*							ID => Item data
	*/
	public function get_path_data($item_id, $order_asc, $include_item);

	/**
	* Get all of the item's descendants
	*
	* @param int		$item_id		Id of the item to retrieve the descendants from
	* @param bool		$order_asc		Order the items ascendingly
	* @param bool		$include_item	Should the item matching the given item id be included in the list as well
	* @return array			Array of items (containing all columns from the item table)
	*							ID => Item data
	*/
	public function get_subtree_data($item_id, $order_asc, $include_item);
}
