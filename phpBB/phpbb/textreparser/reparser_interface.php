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
	 * Returns the name of the reparser
	 *
	 * @return string Name of reparser
	 */
	public function get_name();

	/**
	 * Sets the name of the reparser
	 *
	 * @param string $name The reparser name
	 */
	public function set_name($name);

	/**
	* Reparse all records that match given criteria
	*
	* Available criteria passed as $config:
	*  - filter-callback:    a callback that accepts a record as argument and returns a boolean
	*  - filter-text-like:   a SQL LIKE predicate applied on the text, if applicable, e.g. '<r%'
	*  - filter-text-regexp: a PCRE regexp that matches against the text
	*  - range-min:          lowest record ID
	*  - range-max:          highest record ID
	*
	* If a record does not match all criteria, it will generally be skipped. However, not all
	* reparsers may support all kinds of filters and some non-matching records may be reparsed.
	*
	* @param array $config
	*/
	public function reparse(array $config = []): void;

	/**
	* Reparse all records in given range
	*
	* @deprecated 4.0.0
	*
	* @param integer $min_id Lower bound
	* @param integer $max_id Upper bound
	*/
	public function reparse_range($min_id, $max_id);
}
