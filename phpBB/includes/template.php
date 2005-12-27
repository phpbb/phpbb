<?php
/** 
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2005 phpBB Group, sections (c) 2001 ispi of Lincoln Inc
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* @package phpBB3
*
* Template class.
*
* psoTFX - Completion of file caching, decompilation routines and implementation of
* conditionals/keywords and associated changes
*
* The interface was inspired by PHPLib templates,  and the template file (formats are
* quite similar)
*
* The keyword/conditional implementation is currently based on sections of code from
* the Smarty templating engine (c) 2001 ispi of Lincoln, Inc. which is released
* (on its own and in whole) under the LGPL. Section 3 of the LGPL states that any code
* derived from an LGPL application may be relicenced under the GPL, this applies
* to this source
* 
* DEFINE directive inspired by a request by Cyberalien
*/
class template
{

	// variable that holds all the data we'll be substituting into
	// the compiled templates. Takes form:
	// --> $this->_tpldata[block.][iteration#][child.][iteration#][child2.][iteration#][variablename] == value
	// if it's a root-level variable, it'll be like this:
	// --> $this->_tpldata[.][0][varname] == value
	var $_tpldata = array();

	// Root dir and hash of filenames for each template handle.
	var $root = '';
	var $cachepath = '';
	var $files = array();

	// this will hash handle names to the compiled/uncompiled code for that handle.
	var $compiled_code = array();

	// Various counters and storage arrays
	var $block_names = array();
	var $block_else_level = array();
	var $block_nesting_level = 0;

	var $static_lang;

	/**
	* Set template location
	* @public
	*/
	function set_template($static_lang = false)
	{
		global $phpbb_root_path, $config, $user;

		if (file_exists($phpbb_root_path . 'styles/' . $user->theme['template_path'] . '/template'))
		{
			$this->root = $phpbb_root_path . 'styles/' . $user->theme['template_path']. '/template';
			$this->cachepath = $phpbb_root_path . 'cache/tpl_' . $user->theme['template_path'] . '_';
		}

		$this->static_lang = $static_lang;

		return true;
	}

	/**
	* Set custom template location (able to use directory outside of phpBB)
	* @public
	*/
	function set_custom_template($template_path, $template_name, $static_lang = false)
	{
		global $phpbb_root_path;

		$this->root = $template_path;
		$this->cachepath = $phpbb_root_path . 'cache/ctpl_' . $template_name . '_';
		
		$this->static_lang = $static_lang;

		return true;
	}

	/**
	* Sets the template filenames for handles. $filename_array
	* should be a hash of handle => filename pairs.
	* @public
	*/
	function set_filenames($filename_array)
	{
		if (!is_array($filename_array))
		{
			return false;
		}

		foreach ($filename_array as $handle => $filename)
		{
			if (empty($filename))
			{
				trigger_error("template error - Empty filename specified for $handle", E_USER_ERROR);
			}

			$this->filename[$handle] = $filename;
			$this->files[$handle] = $this->root . '/' . $filename;
		}

		return true;
	}

	/**
	* Destroy template data set
	* @public
	*/
	function destroy()
	{
		$this->_tpldata = array();
	}

	/**
	* Display handle
	* @public
	*/
	function display($handle, $include_once = true)
	{
		global $user;

		if ($filename = $this->_tpl_load($handle))
		{
			($include_once) ? include_once($filename) : include($filename);
		}
		else
		{
			eval(' ?>' . $this->compiled_code[$handle] . '<?php ');
		}

		return true;
	}

	/**
	* Display the handle and assign the output to a template variable
	* @public
	*/
	function assign_display($handle, $template_var, $return_content = false, $include_once = true)
	{
		ob_start();
		$this->display($handle, $include_once);
		$contents = ob_get_clean();

		if ($return_content)
		{
			return $contents;
		}

		$this->assign_var($template_var, $contents);
		
		return true;
	}

	/**
	* Load a compiled template if possible, if not, recompile it
	* @private
	*/
	function _tpl_load(&$handle)
	{
		global $user, $phpEx, $config;

		$filename = $this->cachepath . $this->filename[$handle] . '.' . (($this->static_lang) ? $user->data['user_lang'] . '.' : '') . $phpEx;

		$recompile = (($config['load_tplcompile'] && @filemtime($filename) < filemtime($this->files[$handle])) || !file_exists($filename)) ? true : false;

		// Recompile page if the original template is newer, otherwise load the compiled version
		if (!$recompile)
		{
			return $filename;
		}

		global $db, $phpbb_root_path;

		include_once($phpbb_root_path . 'includes/functions_template.' . $phpEx);
		$compile = new template_compile($this);

		// If the file for this handle is already loaded and compiled, do nothing.
		if (!empty($this->uncompiled_code[$handle]))
		{
			return true;
		}

		// If we don't have a file assigned to this handle, die.
		if (!isset($this->files[$handle]))
		{
			trigger_error("template->_tpl_load(): No file specified for handle $handle", E_USER_ERROR);
		}

		if ($user->theme['template_storedb'])
		{
			$sql = 'SELECT * FROM ' . STYLES_TPLDATA_TABLE . '
				WHERE template_id = ' . $user->theme['template_id'] . "
					AND (template_filename = '" . $db->sql_escape($this->filename[$handle]) . "'
						OR template_included LIKE '%" . $db->sql_escape($this->filename[$handle]) . ":%')";
			$result = $db->sql_query($sql);

			if ($row = $db->sql_fetchrow($result))
			{
				do
				{
					if ($row['template_mtime'] < filemtime($phpbb_root_path . 'styles/' . $user->theme['template_path'] . '/template/' . $row['template_filename']))
					{
						if ($row['template_filename'] == $this->filename[$handle])
						{
							$compile->_tpl_load_file($handle);
						}
						else
						{
							$this->files[$row['template_filename']] = $this->root . '/' . $row['template_filename'];
							$compile->_tpl_load_file($row['template_filename']);
							unset($this->compiled_code[$row['template_filename']]);
							unset($this->files[$row['template_filename']]);
						}
					}

					if ($row['template_filename'] == $this->filename[$handle])
					{
						$this->compiled_code[$handle] = $compile->compile(trim($row['template_data']));
						$compile->compile_write($handle, $this->compiled_code[$handle]);
					}
					else
					{
						// Only bother compiling if it doesn't already exist
						if (!file_exists($this->cachepath . $row['template_filename'] . '.' . (($this->static_lang) ? $user->data['user_lang'] . '.' : '') . $phpEx))
						{
							$this->filename[$row['template_filename']] = $row['template_filename'];
							$compile->compile_write($row['template_filename'], $compile->compile(trim($row['template_data'])));
							unset($this->filename[$row['template_filename']]);
						}
					}
				}
				while ($row = $db->sql_fetchrow($result));
			}
			$db->sql_freeresult($result);
			return false;
		}

		$compile->_tpl_load_file($handle);
		return false;
	}

	/**
	* Assign key variable pairs from an array
	* @public
	*/
	function assign_vars($vararray)
	{
		foreach ($vararray as $key => $val)
		{
			$this->_tpldata['.'][0][$key] = $val;
		}

		return true;
	}

	/**
	* Assign a single variable to a single key
	* @public
	*/
	function assign_var($varname, $varval)
	{
		$this->_tpldata['.'][0][$varname] = $varval;

		return true;
	}

	/**
	* Assign key variable pairs from an array to a specified block
	* @public
	*/
	function assign_block_vars($blockname, $vararray)
	{
		if (strpos($blockname, '.') !== false)
		{
			// Nested block.
			$blocks = explode('.', $blockname);
			$blockcount = sizeof($blocks) - 1;

			$str = &$this->_tpldata;
			for ($i = 0; $i < $blockcount; $i++)
			{
				$str = &$str[$blocks[$i]];
				$str = &$str[sizeof($str) - 1];
			}

			$vararray['S_ROW_COUNT'] = isset($str[$blocks[$blockcount]]) ? sizeof($str[$blocks[$blockcount]]) : 0;
			
			// Assign S_FIRST_ROW
			if (!isset($str[$blocks[$blockcount]]) || sizeof($str[$blocks[$blockcount]]) == 0)
			{
				$vararray['S_FIRST_ROW'] = true;
			}

			// Now the tricky part, we always assign S_LAST_ROW and remove the entry before
			// This is much more clever than going through the complete template data on display (phew)
			$vararray['S_LAST_ROW'] = true;
			if (isset($str[$blocks[$blockcount]]) && sizeof($str[$blocks[$blockcount]]) > 0)
			{
				unset($str[$blocks[$blockcount]][sizeof($str[$blocks[$blockcount]]) - 1]['S_LAST_ROW']);
			}

			// Now we add the block that we're actually assigning to.
			// We're adding a new iteration to this block with the given
			// variable assignments.
			$str[$blocks[$blockcount]][] = &$vararray;
		}
		else
		{
			// Top-level block.
			$vararray['S_ROW_COUNT'] = (isset($this->_tpldata[$blockname])) ? sizeof($this->_tpldata[$blockname]) : 0;

			// Assign S_FIRST_ROW
			if (!isset($this->_tpldata[$blockname]) || sizeof($this->_tpldata[$blockname]) == 0)
			{
				$vararray['S_FIRST_ROW'] = true;
			}

			// We always assign S_LAST_ROW and remove the entry before
			$vararray['S_LAST_ROW'] = true;
			if (isset($this->_tpldata[$blockname]) && sizeof($this->_tpldata[$blockname]) > 0)
			{
				unset($this->_tpldata[$blockname][sizeof($this->_tpldata[$blockname]) - 1]['S_LAST_ROW']);
			}
			
			// Add a new iteration to this block with the variable assignments
			// we were given.
			$this->_tpldata[$blockname][] = &$vararray;
		}

		return true;
	}

	/**
	* Change already assigned key variable pair (one-dimensional - single loop entry)
	*
	* Some Examples:
	* <code>
	*
	* alter_block_array('loop', $varrarray); // Insert vararray at the end
	* alter_block_array('loop', $vararray, 2); // Insert vararray at position 2
	* alter_block_array('loop', $vararray, array('KEY' => 'value')); // Insert vararray at the position where the key 'KEY' has the value of 'value' 
	* alter_block_array('loop', $vararray, false); // Insert vararray at first position
	* alter_block_array('loop', $vararray, true); // Insert vararray at last position (assign_block_vars equivalence)
	*
	* alter_block_array('loop', $vararray, 2, 'change'); // Change/Merge vararray with existing array at position 2
	* alter_block_array('loop', $vararray, array('KEY' => 'value'), 'change'); // Change/Merge vararray with existing array at the position where the key 'KEY' has the value of 'value' 
	* alter_block_array('loop', $vararray, false, 'change'); // Change/Merge vararray with existing array at first position
	* alter_block_array('loop', $vararray, true, 'change'); // Change/Merge vararray with existing array at last position
	*
	* </code>
	*
	* @param string $blockname the blockname, for example 'loop'
	* @param array $vararray the var array to insert/add or merge
	* @param mixed $key Key to search for
	*
	* array: KEY => VALUE [the key/value pair to search for within the loop to determine the correct position]
	*
	* int: Position [the position to change or insert at directly given]
	*
	* If key is false the position is set to 0
	*
	* If key is true the position is set to the last entry
	* 
	* @param insert|change $mode Mode to execute
	*
	*	If insert, the vararray is inserted at the given position (position counting from zero). 
	*
	*	If change, the current block gets merged with the vararray (resulting in new key/value pairs be added and existing keys be replaced by the new value).
	*
	* Since counting begins by zero, inserting at the last position will result in this array: array(vararray, last positioned array)
	* and inserting at position 1 will result in this array: array(first positioned array, vararray, following vars)
	*
	* @public
	*/
	function alter_block_array($blockname, $vararray, $key = false, $mode = 'insert')
	{
		if (strpos($blockname, '.') !== false)
		{
			// Nested blocks are not supported
			return false;
		}
		
		// Change key to zero (change first position) if false and to last position if true
		if ($key === false || $key === true)
		{
			$key = ($key === false) ? 0 : sizeof($this->_tpldata[$blockname]);
		}

		// Get correct position if array given
		if (is_array($key))
		{
			// Search array to get correct position
			list($search_key, $search_value) = @each($key);

			$key = NULL;
			foreach ($this->_tpldata[$blockname] as $i => $val_ary)
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
			if ($key >= sizeof($this->_tpldata[$blockname]))
			{
				$key = sizeof($this->_tpldata[$blockname]);
				unset($this->_tpldata[$blockname][($key - 1)]['S_LAST_ROW']);
				$vararray['S_LAST_ROW'] = true;
			}
			else if ($key === 0)
			{
				unset($this->_tpldata[$blockname][0]['S_FIRST_ROW']);
				$vararray['S_FIRST_ROW'] = true;
			}

			// Re-position template blocks
			for ($i = sizeof($this->_tpldata[$blockname]); $i > $key; $i--)
			{
				$this->_tpldata[$blockname][$i] = $this->_tpldata[$blockname][$i-1];
				$this->_tpldata[$blockname][$i]['S_ROW_COUNT'] = $i;
			}

			// Insert vararray at given position
			$vararray['S_ROW_COUNT'] = $key;
			$this->_tpldata[$blockname][$key] = &$vararray;
		
			return true;
		}
		
		// Which block to change?
		if ($mode == 'change')
		{
			if ($key == sizeof($this->_tpldata[$blockname]))
			{
				$key--;
			}

			$this->_tpldata[$blockname][$key] = array_merge($this->_tpldata[$blockname][$key], &$vararray);
			return true;
		}
	}

	/**
	* Include a seperate template
	* @private
	*/
	function _tpl_include($filename, $include = true)
	{
		$handle = $filename;
		$this->filename[$handle] = $filename;
		$this->files[$handle] = $this->root . '/' . $filename;

 		$filename = $this->_tpl_load($handle);

		if ($include)
		{
			global $user;

			if ($filename)
			{
				include_once($filename);
				return;
			}
			eval(' ?>' . $this->compiled_code[$handle] . '<?php ');
		}
	}
}

?>