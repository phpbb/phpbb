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
* Interface to manage group positions in various places of phpbb
*
* The interface provides simple methods to add, delete and move a group
*/
interface groupposition_interface
{
	/**
	* Returns the value for a given group, if the group exists.
	* @param	int		$group_id	group_id of the group to be selected
	* @return	int			position of the group
	*/
	public function get_group_value($group_id);

	/**
	* Get number of groups displayed
	*
	* @return	int		value of the last item displayed
	*/
	public function get_group_count();

	/**
	* Addes a group by group_id
	*
	* @param	int		$group_id	group_id of the group to be added
	* @return	bool		True if the group was added successfully
	*/
	public function add_group($group_id);

	/**
	* Deletes a group by group_id
	*
	* @param	int		$group_id		group_id of the group to be deleted
	* @param	bool	$skip_group		Skip setting the value for this group, to save the query, when you need to update it anyway.
	* @return	bool		True if the group was deleted successfully
	*/
	public function delete_group($group_id, $skip_group = false);

	/**
	* Moves a group up by group_id
	*
	* @param	int		$group_id	group_id of the group to be moved
	* @return	bool		True if the group was moved successfully
	*/
	public function move_up($group_id);

	/**
	* Moves a group down by group_id
	*
	* @param	int		$group_id	group_id of the group to be moved
	* @return	bool		True if the group was moved successfully
	*/
	public function move_down($group_id);

	/**
	* Moves a group up/down
	*
	* @param	int		$group_id	group_id of the group to be moved
	* @param	int		$delta		number of steps:
	*								- positive = move up
	*								- negative = move down
	* @return	bool		True if the group was moved successfully
	*/
	public function move($group_id, $delta);
}
