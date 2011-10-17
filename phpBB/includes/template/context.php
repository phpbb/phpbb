<?php
/**
*
* @package phpBB3
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Stores variables assigned to template.
*
* @package phpBB3
*/
class phpbb_template_context
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
	}

	/**
	* Assign a single variable to a single key
	*
	* @param string $varname Variable name
	* @param string $varval Value to assign to variable
	*/
	public function assign_var($varname, $varval)
	{
		$this->rootref[$varname] = $varval;

		return true;
	}

	/**
	* Returns a reference to template data array.
	*
	* This function is public so that template renderer may invoke it.
	* Users should alter template variables via functions in phpbb_template.
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
		return $ref;
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
	*/
	public function assign_block_vars($blockname, array $vararray)
	{
		if (strpos($blockname, '.') !== false)
		{
			// Nested block.
			$blocks = explode('.', $blockname);
			$blockcount = sizeof($blocks) - 1;

			$str = &$this->tpldata;
			for ($i = 0; $i < $blockcount; $i++)
			{
				$str = &$str[$blocks[$i]];
				$str = &$str[sizeof($str) - 1];
			}

			$s_row_count = isset($str[$blocks[$blockcount]]) ? sizeof($str[$blocks[$blockcount]]) : 0;
			$vararray['S_ROW_COUNT'] = $s_row_count;

			// Assign S_FIRST_ROW
			if (!$s_row_count)
			{
				$vararray['S_FIRST_ROW'] = true;
			}

			// Now the tricky part, we always assign S_LAST_ROW and remove the entry before
			// This is much more clever than going through the complete template data on display (phew)
			$vararray['S_LAST_ROW'] = true;
			if ($s_row_count > 0)
			{
				unset($str[$blocks[$blockcount]][($s_row_count - 1)]['S_LAST_ROW']);
			}

			// Now we add the block that we're actually assigning to.
			// We're adding a new iteration to this block with the given
			// variable assignments.
			$str[$blocks[$blockcount]][] = $vararray;
		}
		else
		{
			// Top-level block.
			$s_row_count = (isset($this->tpldata[$blockname])) ? sizeof($this->tpldata[$blockname]) : 0;
			$vararray['S_ROW_COUNT'] = $s_row_count;

			// Assign S_FIRST_ROW
			if (!$s_row_count)
			{
				$vararray['S_FIRST_ROW'] = true;
			}

			// We always assign S_LAST_ROW and remove the entry before
			$vararray['S_LAST_ROW'] = true;
			if ($s_row_count > 0)
			{
				unset($this->tpldata[$blockname][($s_row_count - 1)]['S_LAST_ROW']);
			}

			// Add a new iteration to this block with the variable assignments we were given.
			$this->tpldata[$blockname][] = $vararray;
		}

		return true;
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
	public function alter_block_array($blockname, array $vararray, $key = false, $mode = 'insert')
	{
		if (strpos($blockname, '.') !== false)
		{
			// Nested block.
			$blocks = explode('.', $blockname);
			$blockcount = sizeof($blocks) - 1;

			$block = &$this->tpldata;
			for ($i = 0; $i < $blockcount; $i++)
			{
				if (($pos = strpos($blocks[$i], '[')) !== false)
				{
					$name = substr($blocks[$i], 0, $pos);

					if (strpos($blocks[$i], '[]') === $pos)
					{
						$index = sizeof($block[$name]) - 1;
					}
					else
					{
						$index = min((int) substr($blocks[$i], $pos + 1, -1), sizeof($block[$name]) - 1);
					}
				}
				else
				{
					$name = $blocks[$i];
					$index = sizeof($block[$name]) - 1;
				}
				$block = &$block[$name];
				$block = &$block[$index];
			}

			$block = &$block[$blocks[$i]]; // Traverse the last block
		}
		else
		{
			// Top-level block.
			$block = &$this->tpldata[$blockname];
		}

		// Change key to zero (change first position) if false and to last position if true
		if ($key === false || $key === true)
		{
			$key = ($key === false) ? 0 : sizeof($block);
		}

		// Get correct position if array given
		if (is_array($key))
		{
			// Search array to get correct position
			list($search_key, $search_value) = @each($key);

			$key = NULL;
			foreach ($block as $i => $val_ary)
			{
				if ($val_ary[$search_key] === $search_value)
				{
					$key = $i;
					break;
				}
			}

			// key/value pair not found
			if ($key === NULL)
			{
				return false;
			}
		}

		// Insert Block
		if ($mode == 'insert')
		{
			// Make sure we are not exceeding the last iteration
			if ($key >= sizeof($this->tpldata[$blockname]))
			{
				$key = sizeof($this->tpldata[$blockname]);
				unset($this->tpldata[$blockname][($key - 1)]['S_LAST_ROW']);
				$vararray['S_LAST_ROW'] = true;
			}
			else if ($key === 0)
			{
				unset($this->tpldata[$blockname][0]['S_FIRST_ROW']);
				$vararray['S_FIRST_ROW'] = true;
			}

			// Re-position template blocks
			for ($i = sizeof($block); $i > $key; $i--)
			{
				$block[$i] = $block[$i-1];
			}

			// Insert vararray at given position
			$block[$key] = $vararray;

			return true;
		}

		// Which block to change?
		if ($mode == 'change')
		{
			if ($key == sizeof($block))
			{
				$key--;
			}

			$block[$key] = array_merge($block[$key], $vararray);

			return true;
		}

		return false;
	}

	/**
	* Reset/empty complete block
	*
	* @param string $blockname Name of block to destroy
	*/
	public function destroy_block_vars($blockname)
	{
		if (strpos($blockname, '.') !== false)
		{
			// Nested block.
			$blocks = explode('.', $blockname);
			$blockcount = sizeof($blocks) - 1;

			$str = &$this->tpldata;
			for ($i = 0; $i < $blockcount; $i++)
			{
				$str = &$str[$blocks[$i]];
				$str = &$str[sizeof($str) - 1];
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
