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
	* Retrieve a single scalar value from a single key.
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
		$s_num_rows = count($loop_data);
		foreach ($loop_data as &$mod_block)
		{
			foreach ($mod_block as $sub_block_name => &$sub_block)
			{
				// If the key name is lowercase and the data is an array,
				// it could be a template loop. So we set the S_NUM_ROWS there
				// as well.
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
	* @param string $blockname Name of block to assign $vararray to
	* @param array $vararray A hash of variable name => value pairs
	* @return true
	*/
	public function assign_block_vars($blockname, array $vararray)
	{
		$this->num_rows_is_set = false;

		// For nested block, $blockcount > 0, for top-level block, $blockcount == 0
		$blocks = explode('.', $blockname);
		$blockcount = count($blocks) - 1;

		$block = &$this->tpldata;
		for ($i = 0; $i < $blockcount; $i++)
		{
			$pos = strpos($blocks[$i], '[');
			$name = ($pos !== false) ? substr($blocks[$i], 0, $pos) : $blocks[$i];
			$block = &$block[$name];
			$block_count = empty($block) ? 0 : count($block) - 1;
			$index = (!$pos || strpos($blocks[$i], '[]') === $pos) ? $block_count : (min((int) substr($blocks[$i], $pos + 1, -1), $block_count));
			$block = &$block[$index];
		}

		// $block = &$block[$blocks[$i]]; // Do not traverse the last block as it might be empty
		$name = $blocks[$i];

		// Assign S_ROW_COUNT and S_ROW_NUM
		$s_row_count = isset($block[$name]) ? count($block[$name]) : 0;
		$vararray['S_ROW_COUNT'] = $vararray['S_ROW_NUM'] = $s_row_count;

		// Assign S_FIRST_ROW
		if (!$s_row_count)
		{
			$vararray['S_FIRST_ROW'] = true;
		}

		// Assign S_BLOCK_NAME
		$vararray['S_BLOCK_NAME'] = $name;

		// Now the tricky part, we always assign S_LAST_ROW and remove the entry before
		// This is much more clever than going through the complete template data on display (phew)
		$vararray['S_LAST_ROW'] = true;
		if ($s_row_count > 0)
		{
			unset($block[$name][($s_row_count - 1)]['S_LAST_ROW']);
		}

		// Now we add the block that we're actually assigning to.
		// We're adding a new iteration to this block with the given
		// variable assignments.
		$block[$name][] = $vararray;

		return true;
	}

	/**
	* Assign key variable pairs from an array to a whole specified block loop
	*
	* @param string $blockname Name of block to assign $block_vars_array to
	* @param array $block_vars_array An array of hashes of variable name => value pairs
	* @return true
	*/
	public function assign_block_vars_array($blockname, array $block_vars_array)
	{
		foreach ($block_vars_array as $vararray)
		{
			$this->assign_block_vars($blockname, $vararray);
		}

		return true;
	}

	/**
	* Retrieve key variable pairs from the specified block
	*
	* @param string $blockname Name of block to retrieve $vararray from
	* @param array $vararray An array of variable names, empty array retrieves all vars
	* @return array of hashes with variable name as key and retrieved value or null as value
	*/
	public function retrieve_block_vars($blockname, array $vararray)
	{
		// For nested block, $blockcount > 0, for top-level block, $blockcount == 0
		$blocks = explode('.', $blockname);
		$blockcount = count($blocks) - 1;

		$block = $this->tpldata;
		for ($i = 0; $i <= $blockcount; $i++)
		{
			if (($pos = strpos($blocks[$i], '[')) !== false)
			{
				$name = substr($blocks[$i], 0, $pos);

				if (empty($block[$name]))
				{
					return array();
				}

				if (strpos($blocks[$i], '[]') === $pos)
				{
					$index = count($block[$name]) - 1;
				}
				else
				{
					$index = min((int) substr($blocks[$i], $pos + 1, -1), count($block[$name]) - 1);
				}
			}
			else
			{
				$name = $blocks[$i];
				if (empty($block[$name]))
				{
					return array();
				}

				$index = count($block[$name]) - 1;
			}
			$block = $block[$name];
			$block = $block[$index];
		}

		$result = array();
		if ($vararray === array())
		{
			// The calculated vars that depend on the block position are excluded from the complete block returned results
			$excluded_vars = array('S_FIRST_ROW', 'S_LAST_ROW', 'S_BLOCK_NAME', 'S_NUM_ROWS', 'S_ROW_COUNT', 'S_ROW_NUM');

			foreach ($block as $varname => $varvalue)
			{
				if ($varname === strtoupper($varname) && !is_array($varvalue) && !in_array($varname, $excluded_vars))
				{
					$result[$varname] = $varvalue;
				}
			}
		}
		else
		{
			foreach ($vararray as $varname)
			{
				$result[$varname] = isset($block[$varname]) ? $block[$varname] : null;
			}
		}
		return $result;
	}

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
	public function find_key_index($blockname, $key)
	{
		// For nested block, $blockcount > 0, for top-level block, $blockcount == 0
		$blocks = explode('.', $blockname);
		$blockcount = count($blocks) - 1;

		$block = $this->tpldata;
		for ($i = 0; $i < $blockcount; $i++)
		{
			$pos = strpos($blocks[$i], '[');
			$name = ($pos !== false) ? substr($blocks[$i], 0, $pos) : $blocks[$i];

			if (!isset($block[$name]))
			{
				return false;
			}

			$index = (!$pos || strpos($blocks[$i], '[]') === $pos) ? (count($block[$name]) - 1) : (min((int) substr($blocks[$i], $pos + 1, -1), count($block[$name]) - 1));

			if (!isset($block[$name][$index]))
			{
				return false;
			}
			$block = $block[$name][$index];
		}

		if (!isset($block[$blocks[$i]]))
		{
			return false;
		}
		$block = $block[$blocks[$i]]; // Traverse the last block

		// Change key to zero (change first position) if false and to last position if true
		if (is_bool($key))
		{
			return (!$key) ? 0 : count($block) - 1;
		}

		// Get correct position if array given
		if (is_array($key))
		{
			// Search array to get correct position
			$search_key = key($key);
			$search_value = current($key);

			foreach ($block as $i => $val_ary)
			{
				if ($val_ary[$search_key] === $search_value)
				{
					return $i;
				}
			}
		}

		return (is_int($key) && ((0 <= $key) && ($key < count($block)))) ? $key : false;
	}

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
	*	If change, the current block gets merged with the vararray (resulting in new key/value pairs be added and existing keys be replaced by the new \value).
	*	If delete, the vararray is ignored, and the block at the given position (counting from zero) is removed.
	*
	* Since counting begins by zero, inserting at the last position will result in this array: array(vararray, last positioned array)
	* and inserting at position 1 will result in this array: array(first positioned array, vararray, following vars)
	*
	* @return bool false on error, true on success
	*/
	public function alter_block_array($blockname, array $vararray, $key = false, $mode = 'insert')
	{
		$this->num_rows_is_set = false;

		// For nested block, $blockcount > 0, for top-level block, $blockcount == 0
		$blocks = explode('.', $blockname);
		$blockcount = count($blocks) - 1;

		$block = &$this->tpldata;
		for ($i = 0; $i < $blockcount; $i++)
		{
			if (($pos = strpos($blocks[$i], '[')) !== false)
			{
				$name = substr($blocks[$i], 0, $pos);

				if (strpos($blocks[$i], '[]') === $pos)
				{
					$index = count($block[$name]) - 1;
				}
				else
				{
					$index = min((int) substr($blocks[$i], $pos + 1, -1), count($block[$name]) - 1);
				}
			}
			else
			{
				$name = $blocks[$i];
				$index = count($block[$name]) - 1;
			}
			$block = &$block[$name];
			$block = &$block[$index];
		}
		$name = $blocks[$i];

		// If last block does not exist and we are inserting, and not searching for key, we create it empty; otherwise, nothing to do
		if (!isset($block[$name]))
		{
			if ($mode != 'insert' || is_array($key))
			{
				return false;
			}
			$block[$name] = array();
		}

		$block = &$block[$name]; // Now we can traverse the last block

		// Change key to zero (change first position) if false and to last position if true
		if ($key === false || $key === true)
		{
			$key = ($key === false) ? 0 : count($block);
		}

		// Get correct position if array given
		if (is_array($key))
		{
			// Search array to get correct position
			$search_key = key($key);
			$search_value = current($key);

			$key = null;
			foreach ($block as $i => $val_ary)
			{
				if ($val_ary[$search_key] === $search_value)
				{
					$key = $i;
					break;
				}
			}

			// key/value pair not found
			if ($key === null)
			{
				return false;
			}
		}

		// Insert Block
		if ($mode == 'insert')
		{
			// Make sure we are not exceeding the last iteration
			if ($key >= count($block))
			{
				$key = count($block);
				unset($block[($key - 1)]['S_LAST_ROW']);
				$vararray['S_LAST_ROW'] = true;
			}
			if ($key <= 0)
			{
				$key = 0;
				unset($block[0]['S_FIRST_ROW']);
				$vararray['S_FIRST_ROW'] = true;
			}

			// Assign S_BLOCK_NAME
			$vararray['S_BLOCK_NAME'] = $name;

			// Re-position template blocks
			for ($i = count($block); $i > $key; $i--)
			{
				$block[$i] = $block[$i-1];

				$block[$i]['S_ROW_COUNT'] = $block[$i]['S_ROW_NUM'] = $i;
			}

			// Insert vararray at given position
			$block[$key] = $vararray;
			$block[$key]['S_ROW_COUNT'] = $block[$key]['S_ROW_NUM'] = $key;

			return true;
		}

		// Which block to change?
		if ($mode == 'change')
		{
			// If key is out of bounds, do not change anything
			if ($key > count($block) || $key < 0)
			{
				return false;
			}

			if ($key == count($block))
			{
				$key--;
			}

			$block[$key] = array_merge($block[$key], $vararray);

			return true;
		}

		// Delete Block
		if ($mode == 'delete')
		{
			// If we are exceeding last iteration, do not delete anything
			if ($key > count($block) || $key < 0)
			{
				return false;
			}

			// If we are positioned at the end, we remove the last element
			if ($key == count($block))
			{
				$key--;
			}

			// We are deleting the last element in the block, so remove the block
			if (count($block) === 1)
			{
				$block = null; // unset($block); does not work on references
				return true;
			}

			// Re-position template blocks
			for ($i = $key; $i < count($block)-1; $i++)
			{
				$block[$i] = $block[$i+1];
				$block[$i]['S_ROW_COUNT'] = $block[$i]['S_ROW_NUM'] = $i;
			}

			// Remove the last element
			unset($block[$i]);

			// Set first and last elements again, in case they were removed
			$block[0]['S_FIRST_ROW'] = true;
			$block[count($block)-1]['S_LAST_ROW'] = true;

			return true;
		}

		return false;
	}

	/**
	* Reset/empty complete block
	*
	* @param string $blockname Name of block to destroy
	* @return true
	*/
	public function destroy_block_vars($blockname)
	{
		$this->num_rows_is_set = false;
		if (strpos($blockname, '.') !== false)
		{
			// Nested block.
			$blocks = explode('.', $blockname);
			$blockcount = count($blocks) - 1;

			$str = &$this->tpldata;
			for ($i = 0; $i < $blockcount; $i++)
			{
				$str = &$str[$blocks[$i]];
				$str = &$str[count($str) - 1];
			}

			unset($str[$blocks[$blockcount]]);
		}
		else
		{
			// Top-level block.
			unset($this->tpldata[$blockname]);
		}

		return true;
	}
}
