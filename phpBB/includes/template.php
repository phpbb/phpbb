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
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Base Template class.
* @package phpBB3
*/
class template
{
	/** variable that holds all the data we'll be substituting into
	* the compiled templates. Takes form:
	* --> $this->_tpldata[block][iteration#][child][iteration#][child2][iteration#][variablename] == value
	* if it's a root-level variable, it'll be like this:
	* --> $this->_tpldata[.][0][varname] == value
	*/
	private $_tpldata = array('.' => array(0 => array()));
	private $_rootref;

	// Root dir and hash of filenames for each template handle.
	private $root = '';
	public $cachepath = '';
	public $files = array();
	public $filename = array();

	/**
	* Set template location
	* @access public
	*/
	public function set_template()
	{
		global $user;

		if (file_exists(PHPBB_ROOT_PATH . 'styles/' . $user->theme['template_path'] . '/template'))
		{
			$this->root = PHPBB_ROOT_PATH . 'styles/' . $user->theme['template_path'] . '/template';
			$this->cachepath = PHPBB_ROOT_PATH . 'cache/tpl_' . $user->theme['template_path'] . '_';
		}
		else
		{
			trigger_error('Template path could not be found: styles/' . $user->theme['template_path'] . '/template', E_USER_ERROR);
		}

		$this->_rootref = &$this->_tpldata['.'][0];

		return true;
	}

	/**
	* Set custom template location (able to use directory outside of phpBB)
	* @access public
	*/
	public function set_custom_template($template_path, $template_name)
	{
		$this->root = $template_path;
		$this->cachepath = PHPBB_ROOT_PATH . 'cache/ctpl_' . str_replace('_', '-', $template_name) . '_';

		return true;
	}

	/**
	* Sets the template filenames for handles. $filename_array
	* should be a hash of handle => filename pairs.
	* @access public
	*/
	public function set_filenames(array $filename_array)
	{
		foreach ($filename_array as $handle => $filename)
		{
			if (empty($filename))
			{
				trigger_error("template->set_filenames: Empty filename specified for $handle", E_USER_ERROR);
			}

			$this->filename[$handle] = $filename;
			$this->files[$handle] = $this->root . '/' . $filename;
		}

		return true;
	}

	/**
	* Destroy template data set
	* @access public
	*/
	function __destruct()
	{
		$this->_tpldata = array('.' => array(0 => array()));
	}

	/**
	* Reset/empty complete block
	* @access public
	*/
	public function destroy_block_vars($blockname)
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

			unset($str[$blocks[$blockcount]]);
		}
		else
		{
			// Top-level block.
			unset($this->_tpldata[$blockname]);
		}

		return true;
	}

	/**
	* Display handle
	* @access public
	*/
	public function display($handle, $include_once = true)
	{
		global $user, $phpbb_hook;

		if (!empty($phpbb_hook) && $phpbb_hook->call_hook(array(__CLASS__, __FUNCTION__), $handle, $include_once))
		{
			if ($phpbb_hook->hook_return(array(__CLASS__, __FUNCTION__)))
			{
				return $phpbb_hook->hook_return_result(array(__CLASS__, __FUNCTION__));
			}
		}

/*		if (defined('IN_ERROR_HANDLER'))
		{
			if ((E_NOTICE & error_reporting()) == E_NOTICE)
			{
				//error_reporting(error_reporting() ^ E_NOTICE);
			}
		}*/

		$_tpldata	= &$this->_tpldata;
		$_rootref	= &$this->_rootref;
		$_lang		= &$user->lang;

		// These _are_ used the included files.
		$_tpldata; $_rootref; $_lang;

		if (($filename = $this->_tpl_load($handle)) !== false)
		{
			($include_once) ? include_once($filename) : include($filename);
		}
		else if (($code = $this->_tpl_eval($handle)) !== false)
		{
			$code = ' ?> ' . $code . ' <?php ';
			eval($code);
		}
		else
		{
			// if we could not eval AND the file exists, something horrific has occured
			return false;
		}

		return true;
	}

	/**
	* Display the handle and assign the output to a template variable or return the compiled result.
	* @access public
	*/
	public function assign_display($handle, $template_var = '', $return_content = true, $include_once = false)
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
	* @access private
	*/
	private function _tpl_load(&$handle)
	{
		global $config;

		$filename = $this->cachepath . str_replace('/', '.', $this->filename[$handle]) . '.' . PHP_EXT;

		$recompile = (($config['load_tplcompile'] && @filemtime($filename) < filemtime($this->files[$handle])) || !file_exists($filename) || @filesize($filename) === 0) ? true : false;

		// Recompile page if the original template is newer, otherwise load the compiled version
		if ($recompile)
		{
			if (!class_exists('template_compile'))
			{
				include(PHPBB_ROOT_PATH . 'includes/functions_template.' . PHP_EXT);
			}

			$compile = new template_compile($this);

			// If we don't have a file assigned to this handle, die.
			if (!isset($this->files[$handle]))
			{
				trigger_error("template->_tpl_load(): No file specified for handle $handle", E_USER_ERROR);
			}

			if ($compile->_tpl_load_file($handle) === false)
			{
				return false;
			}
		}

		return $filename;
	}

	/**
	* This code should only run when some high level error prevents us from writing to the cache.
	* @access private
	*/
	private function _tpl_eval(&$handle)
	{
//		global $user, $config;

		if (!class_exists('template_compile'))
		{
			include(PHPBB_ROOT_PATH . 'includes/functions_template.' . PHP_EXT);
		}

		$compile = new template_compile($this);

		// If we don't have a file assigned to this handle, die.
		if (!isset($this->files[$handle]))
		{
			trigger_error("template->_tpl_eval(): No file specified for handle $handle", E_USER_ERROR);
		}

		if (($code = $compile->_tpl_gen_src($handle)) === false)
		{
			return false;
		}

		return $code;
	}

	/**
	* Assign key variable pairs from an array
	* @access public
	*/
	public function assign_vars(array $vararray)
	{
		foreach ($vararray as $key => $val)
		{
			$this->_rootref[$key] = $val;
		}

		return true;
	}

	/**
	* Assign a single variable to a single key
	* @access public
	*/
	public function assign_var($varname, $varval)
	{
		$this->_rootref[$varname] = $varval;

		return true;
	}

	/**
	* Assign key variable pairs from an array to a specified block
	* @access public
	*/
	public function assign_block_vars($blockname, array $vararray)
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
			$s_row_count = (isset($this->_tpldata[$blockname])) ? sizeof($this->_tpldata[$blockname]) : 0;
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
				unset($this->_tpldata[$blockname][($s_row_count - 1)]['S_LAST_ROW']);
			}

			// Add a new iteration to this block with the variable assignments we were given.
			$this->_tpldata[$blockname][] = $vararray;
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
	* @access public
	*/
	public function alter_block_array($blockname, array $vararray, $key = false, $mode = 'insert')
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
			$this->_tpldata[$blockname][$key] = $vararray;

			return true;
		}

		// Which block to change?
		if ($mode == 'change')
		{
			if ($key == sizeof($this->_tpldata[$blockname]))
			{
				$key--;
			}

			$this->_tpldata[$blockname][$key] = array_merge($this->_tpldata[$blockname][$key], $vararray);
			return true;
		}

		return false;
	}

	/**
	* Include a separate template
	* @access private
	*/
	public function _tpl_include($filename, $include = true)
	{
		$handle = $filename;
		$this->filename[$handle] = $filename;
		$this->files[$handle] = $this->root . '/' . $filename;

 		$filename = $this->_tpl_load($handle);

		if ($include)
		{
			global $user;

			$_tpldata	= &$this->_tpldata;
			$_rootref	= &$this->_rootref;
			$_lang		= &$user->lang;

			// These _are_ used the included files.
			$_tpldata; $_rootref; $_lang;

			if ($filename)
			{
				include($filename);
				return;
			}
			else
			{
				if (!class_exists('template_compile'))
				{
					include(PHPBB_ROOT_PATH . 'includes/functions_template.' . PHP_EXT);
				}

				$compile = new template_compile($this);

				if (($code = $compile->_tpl_gen_src($handle)) !== false)
				{
					$code = ' ?> ' . $code . ' <?php ';
					eval($code);
				}
			}
		}
	}
}

?>