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

namespace phpbb\template;

interface template
{

	/**
	* Clear the cache
	*
	* @return \phpbb\template\template
	*/
	public function clear_cache();

	/**
	* Sets the template filenames for handles.
	*
	* @param array $filename_array Should be a hash of handle => filename pairs.
	* @return \phpbb\template\template $this
	*/
	public function set_filenames(array $filename_array);

	/**
	* Get the style tree of the style preferred by the current user
	*
	* @return array Style tree, most specific first
	*/
	public function get_user_style();

	/**
	* Set style location based on (current) user's chosen style.
	*
	* @param array $style_directories The directories to add style paths for
	* 	E.g. array('ext/foo/bar/styles', 'styles')
	* 	Default: array('styles') (phpBB's style directory)
	* @return \phpbb\template\template $this
	*/
	public function set_style($style_directories = array('styles'));

	/**
	* Set custom style location (able to use directory outside of phpBB).
	*
	* Note: Templates are still compiled to phpBB's cache directory.
	*
	* @param string|array $names Array of names or string of name of template(s) in inheritance tree order, used by extensions.
	* @param string|array $paths Array of style paths, relative to current root directory
	* @return \phpbb\template\template $this
	*/
	public function set_custom_style($names, $paths);

	/**
	* Clears all variables and blocks assigned to this template.
	*
	* @return \phpbb\template\template $this
	*/
	public function destroy();

	/**
	* Reset/empty complete block
	*
	* @param string $blockname Name of block to destroy
	* @return \phpbb\template\template $this
	*/
	public function destroy_block_vars($blockname);

	/**
	* Display a template for provided handle.
	*
	* The template will be loaded and compiled, if necessary, first.
	*
	* This function calls hooks.
	*
	* @param string $handle Handle to display
	* @return \phpbb\template\template $this
	*/
	public function display($handle);

	/**
	* Display the handle and assign the output to a template variable
	* or return the compiled result.
	*
	* @param string $handle Handle to operate on
	* @param string $template_var Template variable to assign compiled handle to
	* @param bool $return_content If true return compiled handle, otherwise assign to $template_var
	* @return \phpbb\template\template|string if $return_content is true return string of the compiled handle, otherwise return $this
	*/
	public function assign_display($handle, $template_var = '', $return_content = true);

	/**
	* Assign key variable pairs from an array
	*
	* @param array $vararray A hash of variable name => value pairs
	* @return \phpbb\template\template $this
	*/
	public function assign_vars(array $vararray);

	/**
	* Assign a single scalar value to a single key.
	*
	* Value can be a string, an integer or a boolean.
	*
	* @param string $varname Variable name
	* @param string $varval Value to assign to variable
	* @return \phpbb\template\template $this
	*/
	public function assign_var($varname, $varval);

	/**
	* Append text to the string value stored in a key.
	*
	* Text is appended using the string concatenation operator (.).
	*
	* @param string $varname Variable name
	* @param string $varval Value to append to variable
	* @return \phpbb\template\template $this
	*/
	public function append_var($varname, $varval);

	/**
	* Retrieve multiple template values
	*
	* @param array $vararray An array with variable names
	* @return array A hash of variable name => value pairs (value is null if not set)
	*/
	public function retrieve_vars(array $vararray);

	/**
	* Retrieve a single scalar value from a single key.
	*
	* @param string $varname Variable name
	* @return mixed Variable value, or null if not set
	*/
	public function retrieve_var($varname);

	/**
	* Assign key variable pairs from an array to a specified block
	* @param string $blockname Name of block to assign $vararray to
	* @param array $vararray A hash of variable name => value pairs
	* @return \phpbb\template\template $this
	*/
	public function assign_block_vars($blockname, array $vararray);

	/**
	* Assign key variable pairs from an array to a whole specified block loop
	* @param string $blockname Name of block to assign $block_vars_array to
	* @param array $block_vars_array An array of hashes of variable name => value pairs
	* @return \phpbb\template\template $this
	*/
	public function assign_block_vars_array($blockname, array $block_vars_array);

	/**
	* Retrieve variable values from an specified block
	* @param string $blockname Name of block to retrieve $vararray from
	* @param array $vararray An array with variable names, empty array gets all vars
	* @return array A hash of variable name => value pairs (value is null if not set)
	*/
	public function retrieve_block_vars($blockname, array $vararray);

	/**
	* Change already assigned key variable pair (one-dimensional - single loop entry)
	*
	* An example of how to use this function:
	* {@example alter_block_array.php}
	*
	* @param	string	$blockname	the blockname, for example 'loop'
	* @param	array	$vararray	the var array to insert/add or merge
	* @param	mixed	$key		Key to search for
	*
	* array: KEY => VALUE [the key/value pair to search for within the loop to determine the correct position]
	*
	* int: Position [the position to change or insert at directly given]
	*
	* If key is false the position is set to 0
	* If key is true the position is set to the last entry
	*
	* @param	string	$mode		Mode to execute (valid modes are 'insert', 'change' and 'delete')
	*
	*	If insert, the vararray is inserted at the given position (position counting from zero).
	*	If change, the current block gets merged with the vararray (resulting in new \key/value pairs be added and existing keys be replaced by the new \value).
	*	If delete, the vararray is ignored, and the block at the given position (counting from zero) is removed.
	*
	* Since counting begins by zero, inserting at the last position will result in this array: array(vararray, last positioned array)
	* and inserting at position 1 will result in this array: array(first positioned array, vararray, following vars)
	*
	* @return bool false on error, true on success
	*/
	public function alter_block_array($blockname, array $vararray, $key = false, $mode = 'insert');

	/**
	* Find the index for a specified key in the innermost specified block
	*
	* @param	string	$blockname	the blockname, for example 'loop'
	* @param	mixed	$key		Key to search for
	*
	* array: KEY => VALUE [the key/value pair to search for within the loop to determine the correct position]
	*
	* int: Position [the position to search for]
	*
	* If key is false the position is set to 0
	* If key is true the position is set to the last entry
	*
	* @return mixed false if not found, index position otherwise; be sure to test with ===
	*/
	public function find_key_index($blockname, $key);

	/**
	* Get path to template for handle (required for BBCode parser)
	*
	* @param string $handle Handle to retrieve the source file
	* @return string
	*/
	public function get_source_file_for_handle($handle);
}
