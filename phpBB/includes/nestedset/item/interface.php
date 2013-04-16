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

interface phpbb_nestedset_item_interface
{
	/**
	* Returns the ID of the item
	*
	* @return int
	*/
	public function get_item_id();

	/**
	* Returns the ID of the parent item
	*
	* @return int
	*/
	public function get_parent_id();

	/**
	* Returns a serialized or empty string with the data of the item's parents
	*
	* @return string
	*/
	public function get_item_parents_data();

	/**
	* Returns the left_id of the item
	*
	* @return int
	*/
	public function get_left_id();

	/**
	* Returns the right_id of the item
	*
	* @return int
	*/
	public function get_right_id();

	/**
	* Does the item have sub-items?
	*
	* @return bool
	*/
	public function has_children();
}
