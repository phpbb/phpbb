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

namespace phpbb\request;

/**
* An interface for type cast operations.
*/
interface type_cast_helper_interface
{
	/**
	* Recursively applies addslashes to a variable.
	*
	* @param	mixed	&$var	Variable passed by reference to which slashes will be added.
	*/
	public function addslashes_recursively(&$var);

	/**
	* Recursively applies addslashes to a variable if magic quotes are turned on.
	*
	* @param	mixed	&$var	Variable passed by reference to which slashes will be added.
	*/
	public function add_magic_quotes(&$var);

	/**
	* Set variable $result to a particular type.
	*
	* @param mixed	&$result	The variable to fill
	* @param mixed	$var		The contents to fill with
	* @param mixed	$type		The variable type. Will be used with {@link settype()}
	* @param bool	$multibyte	Indicates whether string values may contain UTF-8 characters.
	* 							Default is false, causing all bytes outside the ASCII range (0-127) to be replaced with question marks.
	*/
	public function set_var(&$result, $var, $type, $multibyte = false);

	/**
	* Recursively sets a variable to a given type using {@link set_var set_var}.
	*
	* @param	string	$var		The value which shall be sanitised (passed by reference).
	* @param	mixed	$default	Specifies the type $var shall have.
	* 								If it is an array and $var is not one, then an empty array is returned.
	* 								Otherwise var is cast to the same type, and if $default is an array all
	* 								keys and values are cast recursively using this function too.
	* @param	bool	$multibyte	Indicates whether string keys and values may contain UTF-8 characters.
	* 								Default is false, causing all bytes outside the ASCII range (0-127) to
	* 								be replaced with question marks.
	*/
	public function recursive_set_var(&$var, $default, $multibyte);
}
