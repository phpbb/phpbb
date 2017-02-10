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

/**
* Stores variables assigned to template.
*/
class context
{
	/**
	* variable that holds all the data we'll be substituting into
	* the compiled templates. Takes form:
	* --> $this->tpldata[block][iteration#][child][iteration#][child2][iteration#][variablename] == value
	* if it's a root-level variable, it'll be like this:
	* --> $this->tpldata[.][0][varname] == value
	*
	* @var array
	*/
	private $tpldata = array('.' => array(0 => array()));

	/**
	* @var array Reference to template->tpldata['.'][0]
	*/
	private $rootref;

	/**
	* @var bool
	*/
	private $num_rows_is_set;

	public function __construct()
	{
		$this->clear();
	}

	/**
	* Clears template data set.
	*/
	public function clear()
	{
		$this->tpldata = array('.' => array(0 => array()));
		$this->rootref = &$this->tpldata['.'][0];
		$this->num_rows_is_set = false;
	}

	/**
	* Assign a single scalar value to a single key.
	*
	* Value can be a string, an integer or a boolean.
	*
	* @param string $varname Variable name
	* @param string $varval Value to assign to variable
	* @return true
	*/
	public function assign_var($varname, $varval)
	{
		$this->rootref[$varname] = $varval;

		return true;
	}

	/**
	* Append text to the string value stored in a key.
	*
	* Text is appended using the string concatenation operator (.).
	*
	* @param string $varname Variable name
	* @param string $varval Value to append to variable
	* @return true
	*/
	public function append_var($varname, $varval)
	{
		$this->rootref[$varname] = (isset($this->rootref[$varname]) ? $this->rootref[$varname] : '') . $varval;

		return true;
	}

	/**
	* Retreive a single scalar value from a single key.
	*
	* @param string $varname Variable name
	* @return mixed Variable value, or null if not set
	*/
	public function retrieve_var($varname)
	{
		return isset($this->rootref[$varname]) ? $this->rootref[$varname] : null;
	}

	/**
	* Returns a reference to template data array.
	*
	* This function is public so that template renderer may invoke it.
	* Users should alter template variables via functions in \phpbb\template\template.
	*
	* Note: modifying returned array will affect data stored in the context.
	*
	* @return array template data
	*/
	public function &get_data_ref()
	{
		// returning a reference directly is not
		// something php is capable of doing
		$ref = &$this->tpldata;

		if (!$this->num_rows_is_set)
		{
			/*
			* We do not set S_NUM_ROWS while adding a row, to reduce the complexity
			* If we would set it on adding, each subsequent adding would cause
			* n modifications, resulting in a O(n!) complexity, rather then O(n)
			*/
			foreach ($ref as $loop_name => &$loop_data)
			{
				if ($loop_name === '.')
				{
					continue;
				}

				$this->set_num_rows($loop_data);
			}
			$this->num_rows_is_set = true;
		}

		return $ref;
	}

	/**
	* Set S_NUM_ROWS for each row in this template block
	*
	* @param array $loop_data
	*/
	protected function set_num_rows(&$loop_data)
	{
		$s_num_rows = sizeof($loop_data);
		foreach ($loop_data as &$mod_block)
		{
			foreach ($mod_block as $sub_block_name => &$sub_block)
			{
				// If the key name is lowercase and the data is an array,
				// it could be a template loop. So we set the S_NUM_ROWS there
				// aswell.
				if ($sub_block_name === strtolower($sub_block_name) && is_array($sub_block))
				{
					$this->set_num_rows($sub_block);
				}
			}

			// Check whether we are inside a block before setting the variable
			if (isset($mod_block['S_BLOCK_NAME']))
			{
				$mod_block['S_NUM_ROWS'] = $s_num_rows;
			}
		}
	}

	/**
	* Returns a reference to template root scope.
	*
	* This function is public so that template renderer may invoke it.
	* Users should not need to invoke this function.
	*
	* Note: modifying returned array will affect data stored in the context.
	*
	* @return array template data
	*/
	public function &get_root_ref()
	{
		// rootref is already a reference
		return $this->rootref;
	}

	/**
	* Assign key variable pairs from an array to a specified block
	*
	* @param mixed	$block_selector Selector of block to assign $vararray to,
	*								see alter_block_array for full description and syntax
	* @param array	$vararray A hash of variable name => value pairs
	* @return false on error, true on success
	*/
	public function assign_block_vars($block_selector, array $vararray)
	{
		return $this->alter_block_array($block_selector, array($vararray), null, 'multiinsert');
	}

	/**
	* Assign key variable pairs from an array to a whole specified block loop
	*
	* @param mixed	$block_selector Selector of block to assign block vars array to,
	*								see alter_block_array for full description and syntax
	* @param array	$block_vars_array An array of hashes of variable name => value pairs
	* @return true on success, false otherwise
	*/
	public function assign_block_vars_array($block_selector, array $block_vars_array)
	{
		return $this->alter_block_array($block_selector, $block_vars_array, null, 'multiinsert');
	}

	/**
	* Retrieve variable values from an specified block
	*
	* @param mixed	$block_selector Selector of block to retrieve $vararray from,
	*								see alter_block_array for full description and syntax
	* @param array	$vararray An array with variable names
	* @return array of hashes with variable name as key and retrieved value or null as value, false on error
	*/
	public function retrieve_block_vars($block_selector, array $vararray)
	{
		return $this->alter_block_array($block_selector, $vararray, null, 'retrieve');
	}

	/**
	* Reset/empty complete block
	*
	* @param mixed	$block_selector Selector of block to destroy,
	*								see alter_block_array for full description and syntax
	* @return bool	true if successful, false if block is not found
	*/
	public function destroy_block_vars($block_selector)
	{
		return $this->alter_block_array($block_selector, array(), null, 'delete');
	}

	/**
	* Find the index for a specified key in the innermost specified block
	*
	* @param mixed	$block_selector Selector of block to find,
	*								see alter_block_array for full description and syntax
	* @param mixed	$key Key to search for, provided for backward compatibility, only considered if last level block selector value === null
	* @return mixed false if not found, index position otherwise; be sure to test with ===
	*/
	public function find_key_index($block_selector, $key = null)
	{
		return $this->alter_block_array($block_selector, array(), $key, 'find');
	}

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
	* @param	mixed	$key		Provided for backward compatibility, only considered if last level block selector value === null, same semantics
	* @param	string	$mode		Mode to execute (valid modes are 'find', 'retrieve', 'insert', 'multiinsert', 'change' and 'delete')
	*			'find'			the vararray is ignored (but must be an array), and the integer index of the last level block is returned; use find_key_index instead.
	*			'retrieve'		the vararray is a list of variable names to retrieve from the selected block; use retrieve_block_vars instead.
	*			'insert'		the vararray is inserted at the given position (position counting from zero).
	*			'multiinsert'	the vararray is an array of vararrays, inserted at the given position (position counting from zero); use assign_block_vars_array instead.
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
	* @return mixed		bool false on error, true on success, int for mode='find', array of hashes for mode='retrieve'
	*/
	public function alter_block_array($block_selector, array $vararray, $key = false, $mode = 'insert')
	{
		// Convert block selector to array format and validate
		if (is_string($block_selector))
		{
			$block_selector = $this->block_selector_array($block_selector);
		}
		if (!is_array($block_selector))
		{
			return false;
		}

		// If last block selector key is null, then we take into considertion the param, otherwise ignored
		if (!is_null($key) && is_null(end($block_selector)))
		{
			$block_selector[key($block_selector)] = $key;
		}

		$block = &$this->tpldata;

		reset($block_selector);
		while (list($name, $search_key) = each($block_selector))
		{
			// Find the index in the block for the given key
			if (($index = $this->find_block_index(@$block[$name], $search_key)) === false)
			{
				return false;
			}

			// Last iteration, we do not traverse last level, and keep $name and $search_key at its latest values
			if (!key($block_selector))
			{
				break;
			}

			// Traverse this block level
			if (!isset($block[$name]))
			{
				return false;
			}
			$block = &$block[$name];
			$block = &$block[$index];
		}

		// Now we perform the specific action with the selected block; we use call_user_func_array to be able to pass block by ref
		return call_user_func_array(array($this, $mode . '_block_array'), array(&$block, $vararray, $search_key, $name, $index));
	}

	/**
	* Insert an array of template vars into a block
	*
	* @param	array	$block			a reference to the block where we have to insert
	* @param	array	$vararray		the var array to insert
	* @param	mixed	$key			search key used in last block, true or null to insert past the end of the block
	* @param	string	$name			name of the block where we are inserting
	* @param	int		$index			index where we have to insert
	* @return bool false on error, true on success
	*/
	protected function insert_block_array(&$block, array $vararray, $key, $name, $index)
	{
		return $this->multiinsert_block_array($block, array($vararray), $key, $name, $index);
	}

	/**
	* Multi-insert an array of arrays of template vars into a block, single call for performance
	*
	* @param	array	$block			a reference to the block where we have to insert
	* @param	array	$vararrays		the array of var arrays to insert
	* @param	mixed	$key			search key used in last block, true or null to insert past the end of the block
	* @param	string	$name			name of the block where we are inserting
	* @param	int		$index			index where we have to insert
	* @return bool false on error, true on success
	*/
	protected function multiinsert_block_array(&$block, array $vararrays, $key, $name, $index)
	{
		if (($numarrays = count($vararrays)) == 0)
		{
			return false; // Nothing to insert
		}

		$this->num_rows_is_set = false;

		if (!isset($block[$name]))
		{
			$block[$name] = array();
		}
		$block = &$block[$name];

		// If inserting at the end, we need to reposition
		if ($key === null || $key === true)
		{
			$index++;
		}

		// Fix S_FIRST_ROW and S_LAST_ROW
		if ($index == count($block))
		{
			unset($block[($index - 1)]['S_LAST_ROW']);
			$vararrays[($numarrays - 1)]['S_LAST_ROW'] = true;
		}
		if ($index == 0)
		{
			unset($block[0]['S_FIRST_ROW']);
			$vararrays[0]['S_FIRST_ROW'] = true;
		}

		// Re-position template blocks to make room for the new one
		for ($i = count($block) + $numarrays - 1; $i > $index + $numarrays - 1; $i--)
		{
			$block[$i] = $block[$i - $numarrays];
			$block[$i]['S_ROW_COUNT'] = $block[$i]['S_ROW_NUM'] = $i;
		}

		// Insert vararrays at given position
		foreach ($vararrays as $vararray)
		{
			// Assign S_BLOCK_NAME and S_ROW_COUNT and S_ROW_NUM
			$vararray['S_BLOCK_NAME'] = $name;
			$vararray['S_ROW_COUNT'] = $vararray['S_ROW_NUM'] = $index;

			// Insert vararray at given position and move the position
			$block[$index] = $vararray;
			$index++;
		}

		return true;
	}

	/**
	* Change an array of template vars into a block, the block must exist, but the template vars will be merged
	*
	* @param	array	$block			a reference to the block where we have to change
	* @param	array	$vararray		the var array to change
	* @param	mixed	$key			search key used in last block, ignored
	* @param	string	$name			name of the block where we are changing
	* @param	int		$index			index where we have to change
	* @return bool false on error, true on success
	*/
	protected function change_block_array(&$block, array $vararray, $key, $name, $index)
	{
		if (!isset($block[$name]))
		{
			return false;
		}

		$this->num_rows_is_set = false;

		$block[$name][$index] = array_merge($block[$name][$index], $vararray);

		return true;
	}

	/**
	* Delete a block of template vars, the block must exist
	*
	* @param	array	$block			a reference to the block where we have to delete
	* @param	array	$vararray		the var array is ignored, but must be an array
	* @param	mixed	$key			search key used in last block, used to identify full-block deletion
	* @param	string	$name			name of the block where we are deleting
	* @param	mixed	$index			index we have to delete
	* @return bool false on error, true on success
	*/
	protected function delete_block_array(&$block, array $vararray, $key, $name, $index)
	{
		if (!isset($block[$name]))
		{
			return false;
		}

		$this->num_rows_is_set = false;

		// Delete the whole block if so specified, or when deleting the only element in block
		if (is_null($key) || count($block[$name]) === 1)
		{
			unset($block[$name]);
			return true;
		}

		$block = &$block[$name];

		// Re-position template blocks to fill the gap
		for ($i = $index; $i < count($block)-1; $i++)
		{
			$block[$i] = $block[$i+1];
			$block[$i]['S_ROW_COUNT'] = $block[$i]['S_ROW_NUM'] = $i;
		}

		// Remove the last element now duplicate
		unset($block[$i]);

		// Set first and last elements again, in case they were removed
		$block[0]['S_FIRST_ROW'] = true;
		$block[count($block)-1]['S_LAST_ROW'] = true;

		return true;
	}

	/**
	* Retrieve key variable pairs from a block
	*
	* @param	array	$block			a reference to the block where we have to retrieve the key variable pairs
	* @param	array	$vararray		an array of variablle names to be retrieved
	* @param	mixed	$key			search key used in last block, ignored
	* @param	string	$name			name of the block where we are retrieving
	* @param	int		$index			index were we have to retrieve the vars
	* @return bool false on error, an array of hashes with variable name as key and retrieved value or null as value
	*/
	protected function retrieve_block_array(&$block, array $vararray, $key, $name, $index)
	{
		if (!isset($block[$name]))
		{
			return false;
		}

		$result = array();
		foreach ($vararray as $varname)
		{
			$result[$varname] = isset($block[$name][$index][$varname]) ? $block[$name][$index][$varname] : null;
		}

		return $result;
	}

	/**
	* Find the index for a specified key in the given block
	*
	* @param	array	$block			a reference to the block where we have to get the index
	* @param	array	$vararray		ignored, but must be an array
	* @param	mixed	$key			search key used in last block, ignored
	* @param	string	$name			name of the block where we are finding
	* @param	int		$index			index
	* @return 	int 					index position within the block
	*/
	protected function find_block_array(&$block, array $vararray, $key, $name, $index)
	{
		if (!isset($block[$name]))
		{
			return false;
		}

		return $index;
	}

	/**
	* Converts a string block selector to the equivalent array block selector
	*
	* @param	string	$block_selector		The string format of the block selector
	* @return	array						The same block selector, in the equivalent array format
	*/
	protected function block_selector_array($block_selector)
	{
		// For nested block, $blockcount > 0, for top-level block, $blockcount == 0
		$blocks = explode('.', $block_selector);
		$blockcount = count($blocks);
		$block_selector = array();

		for ($i = 0; $i < $blockcount; $i++)
		{
			if (($pos = strpos($blocks[$i], '[')) !== false)
			{
				$name = substr($blocks[$i], 0, $pos);

				if (strpos($blocks[$i], '[]') === $pos)
				{
					$index = true;
				}
				else
				{
					$index = (int) substr($blocks[$i], $pos + 1, -1);
				}
			}
			else
			{
				$name = $blocks[$i];
				$index = null;
			}
			$block_selector[$name] = $index;
		}
		return $block_selector;
	}

	/**
	* Finds a specific key within a block of variables.
	*
	* @param	array	$block		The block of variables where the key is searched for
	* @param	mixed	$key		The search key to find in the block
	*			bool					true for last element, false for first element
	*			int						the actual index number of the element
	*			array					VARNAME => varvalue to search for in the block
	*			null					last element
	* @return	mixed				false if not found or out of bounds, index position otherwise; be sure to test with ===
	*								note that in case the $block is empty (non-existent), the function returns 0
	*									except in case the $key is an array, that the function returns false
	*/
	protected function find_block_index($block, $key)
	{
		$index = false;

		// Change key to zero if false and to last position if true or null
		if ($key === false || $key === true || $key === null)
		{
			$index = ($key === false) ? 0 : count($block) - 1;
		}

		// Get correct position if array given
		if (is_array($key) && is_array($block))
		{
			// Search array to get correct position
			list($search_key, $search_value) = each($key);

			foreach ($block as $i => $val_ary)
			{
				if (isset($val_ary[$search_key]) && ($val_ary[$search_key] === $search_value))
				{
					$index = $i;
					break;
				}
			}
		}

		if (is_int($key) && ($key == (int) min(max($key, 0), count($block) - 1)))
		{
			$index = $key;
		}

		// Now return the index
		return $index;
	}
}
