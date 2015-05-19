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

namespace phpbb\textreparser;

interface reparser_interface
{
	/**
	* Return the highest ID for all existing records
	*
	* @return integer
	*/
	public function get_max_id();

	/**
	* Reparse all records in given range
	*
	* @param integer $min_id Lower bound
	* @param integer $max_id Upper bound
	*/
	public function reparse_range($min_id, $max_id);
}
