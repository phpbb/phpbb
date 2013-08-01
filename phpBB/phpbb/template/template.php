<?php
/**
*
* @package phpBB3
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

interface phpbb_template
{

	/**
	* Clear the cache
	*
	* @return phpbb_template
	*/
	public function clear_cache();

	/**
	* Sets the template filenames for handles.
	*
	* @param array $filename_array Should be a hash of handle => filename pairs.
	* @return phpbb_template $this
	*/
	public function set_filenames(array $filename_array);

	/**
	* Sets the style names/paths corresponding to style hierarchy being compiled
	* and/or rendered.
	*
	* @param array $style_names List of style names in inheritance tree order
	* @param array $style_paths List of style paths in inheritance tree order
	* @return phpbb_template $this
	*/
	public function set_style_names(array $style_names, array $style_paths);

	/**
	* Clears all variables and blocks assigned to this template.
	*
	* @return phpbb_template $this
	*/
	public function destroy();

	/**
	* Reset/empty complete block
	*
	* @param string $blockname Name of block to destroy
	* @return phpbb_template $this
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
	* @return phpbb_template $this
	*/
	public function display($handle);

	/**
	* Display the handle and assign the output to a template variable
	* or return the compiled result.
	*
	* @param string $handle Handle to operate on
	* @param string $template_var Template variable to assign compiled handle to
	* @param bool $return_content If true return compiled handle, otherwise assign to $template_var
	* @return phpbb_template|string if $return_content is true return string of the compiled handle, otherwise return $this
	*/
	public function assign_display($handle, $template_var = '', $return_content = true);

	/**
	* Assign key variable pairs from an array
	*
	* @param array $vararray A hash of variable name => value pairs
	* @return phpbb_template $this
	*/
	public function assign_vars(array $vararray);

	/**
	* Assign a single scalar value to a single key.
	*
	* Value can be a string, an integer or a boolean.
	*
	* @param string $varname Variable name
	* @param string $varval Value to assign to variable
	* @return phpbb_template $this
	*/
	public function assign_var($varname, $varval);

	/**
	* Append text to the string value stored in a key.
	*
	* Text is appended using the string concatenation operator (.).
	*
	* @param string $varname Variable name
	* @param string $varval Value to append to variable
	* @return phpbb_template $this
	*/
	public function append_var($varname, $varval);

	/**
	* Assign key variable pairs from an array to a specified block
	* @param string $blockname Name of block to assign $vararray to
	* @param array $vararray A hash of variable name => value pairs
	* @return phpbb_template $this
	*/
	public function assign_block_vars($blockname, array $vararray);

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
	* @param	string	$mode		Mode to execute (valid modes are 'insert' and 'change')
	*
	*	If insert, the vararray is inserted at the given position (position counting from zero).
	*	If change, the current block gets merged with the vararray (resulting in new key/value pairs be added and existing keys be replaced by the new value).
	*
	* Since counting begins by zero, inserting at the last position will result in this array: array(vararray, last positioned array)
	* and inserting at position 1 will result in this array: array(first positioned array, vararray, following vars)
	*
	* @return bool false on error, true on success
	*/
	public function alter_block_array($blockname, array $vararray, $key = false, $mode = 'insert');

	/**
	* Get path to template for handle (required for BBCode parser)
	*
	* @return string
	*/
	public function get_source_file_for_handle($handle);
}
