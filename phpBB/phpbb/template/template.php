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
	* @param string|array or string $paths Array of style paths, relative to current root directory
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
	* @param array	$vararray An array with variable names
	* @return array A hash of variable name => value pairs (value is null if not set)
	*/
	public function retrieve_vars(array $vararray);

	/**
	* Retreive a single scalar value from a single key.
	*
	* @param string	$varname Variable name
	* @return mixed	Variable value, or null if not set
	*/
	public function retrieve_var($varname);

	/**
	* Assign key variable pairs from an array to a specified block
	*
	* @param mixed	$block_selector Selector of block to assign $vararray to,
	*								see alter_block_array for full description and syntax
	* @param array $vararray A hash of variable name => value pairs
	* @return \phpbb\template\template $this
	*/
	public function assign_block_vars($block_selector, array $vararray);

	/**
	* Assign key variable pairs from an array to a whole specified block loop
	*
	* @param mixed	$block_selector Selector of block to assign block vars array to,
	*								see alter_block_array for full description and syntax
	* @param array $block_vars_array An array of hashes of variable name => value pairs
	* @return \phpbb\template\template $this
	*/
	public function assign_block_vars_array($block_selector, array $block_vars_array);

	/**
	* Retrieve variable values from an specified block
	*
	* @param mixed	$block_selector Selector of block to retrieve $vararray from,
	*								see alter_block_array for full description and syntax
	* @param array	$vararray An array with variable names, empty array gets all vars
	* @return array of hashes with variable name as key and retrieved value or null as value, false on error
	*/
	public function retrieve_block_vars($block_selector, array $vararray);

	/**
	* Reset/empty complete block
	*
	* @param mixed	$block_selector Selector of block to destroy,
	*								see alter_block_array for full description and syntax
	* @return \phpbb\template\template $this
	*/
	public function destroy_block_vars($block_selector);

	/**
	* Find the index for a specified key in the innermost specified block
	*
	* @param mixed	$block_selector Selector of block to find,
	*								see alter_block_array for full description and syntax
	* @param mixed	$key	Key to search for, provided for backward compatibility, only considered if last level block selector value === null
	* @return mixed false if not found, index position otherwise; be sure to test with ===
	*/
	public function find_key_index($block_selector, $key);

	/**
	* Core function to select a block in which we are going to act
	*
	* @param	mixed	$block_selector Selector of block to retrieve $vararray from, with two possible formats
	*			string		blockname of the block to act on; can be
	*							* simple ('loop')
	*							* multilevel ('loop.inner')
	*							* indexed ('loop[1].inner', or 'loop.inner[0]', or even 'loop[1].inner[2]')
	*							*		also allows 'loop[]' to refer to the last element of a loop
	*							*		if index is ommited, last element is also taken
	*			array		complete block selector, with one hash per (ordered) nesting block level
	*							* array key is the (string) name of the block
	*							* array value is the index within that block, as follows
	*								- false refers to the first element of the block (index 0)
	*								- true refers to the end of the block
	*									last element, index == count(block)-1 for most operations
	*									or after it for last level of insertion, index == count(block)
	*								- int refers to the exact position of index to take; valid values 0..count(block)-1
	*								- array('KEY' => value) search block for index where block[index]['KEY'] === value
	*								- null is equivalent to true except for last level deletion, where it is used to delete whole block (all indexes)
	*				EXAMPLES of block_selector:
	*						'loop' == array('loop' => null)
	*						'loop.inner' == array('loop' => null, 'inner' => null)
	*						'loop[1].inner[]' == array('loop' => 1, 'inner' => true)
	*						not possible as string == array(array('loop' => array('VARNAME' => varvalue), 'inner' => true)
	*
	* @param	array	$vararray	the var array to operate with
	* @param	mixed	$key		Provided for backward compatibility, only considered if last level block selector value === null
	* @param	string	$mode		Mode to execute (valid modes are 'find', 'retrieve', 'insert', 'multiinsert', 'change' and 'delete')
	*			'find'			the vararray is ignored (but must be an array), and the integer index of the last block is returned; use find_key_index instead.
	*			'retrieve'		the vararray is a list of variable names to retrieve from the selected block; use retrieve_block_vars instead.
	*			'insert'		the vararray is inserted at the given position (position counting from zero).
	*			'multiinsert'	the vararray is an array of arrays, inserted at the given position (position counting from zero); use assign_block_vars_array instead.
	*			'change'		the current block gets merged with the vararray (resulting in new \key/value pairs be added and existing keys be replaced by the new \value).
	*			'delete'		the vararray is ignored (but must be an array), and the block at the given position is removed; use destroy_block_vars instead.
	*
	*		EXAMPLES of alter_block_array
	*			alter_block_array('loop', array('NAME'=>'first')) // Insert the vararray in the loop block, at the beginning (if it exists, if not, creates the 'loop' block)
	*			alter_block_array('loop.inner', array('INSIDE'=>11), true, 'insert')
	*			alter_block_array('loop', array('NAME'=>'zero')) // Inserted BEFORE the existing block in loop
	*			alter_block_array('loop', array('NAME'=>'second'), true) // Insert at the end
	*			alter_block_array('loop.inner', array('INSIDE'=>21)) // Create new block inside last one
	*			alter_block_array(array('loop' => array('NAME'=>'first'), 'inner' => null), array('S_INSIDE'=>12), null, 'insert')
	*			alter_block_array(array('loop' => array('NAME'=>'zero'), 'inner' => 0), array('S_INSIDE'=>01), null, 'insert')
	*			alter_block_array(array('loop' => array('NAME'=>'first'), 'inner' => null), array(), array('INSIDE'=>11), 'delete') // Deletes single block entry
	*			alter_block_array(array('loop' => array('NAME'=>'first'), 'inner' => null), array(), null, 'delete') // Deletes the whole block
	*			alter_block_array('loop[1]', array('NAME'=>'newfirst', 'WAS'=>'second'), null, 'change') // Changes the block, changing one var and adding another
	*
	* @return mixed	bool false on error, true on success, int for mode='find', array of hashes for mode='retrieve'
	*/
	public function alter_block_array($block_selector, array $vararray, $key = false, $mode = 'insert');

	/**
	* Get path to template for handle (required for BBCode parser)
	*
	* @param string $handle Handle to retrieve the source file
	* @return string
	*/
	public function get_source_file_for_handle($handle);
}
