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
* @todo
* IMG_ for image substitution?
* {IMG_[key]:[alt]:[type]}
* {IMG_ICON_CONTACT:CONTACT:full} -> $user->img('icon_contact', 'CONTACT', 'full');
*
* More in-depth...
* yadayada
*/

/**
* Base Template class.
* @package phpBB3
*/
class phpbb_template
{
	public $phpbb_required = array('user', 'config');
	public $phpbb_optional = array();

	/**
	* variable that holds all the data we'll be substituting into
	* the compiled templates. Takes form:
	* --> $this->_tpldata[block][iteration#][child][iteration#][child2][iteration#][variablename] == value
	* if it's a root-level variable, it'll be like this:
	* --> $this->_tpldata[.][0][varname] == value
	* @var array
	*/
	private $_tpldata = array('.' => array(0 => array()));

	/**
	* @var array Reference to template->_tpldata['.'][0]
	*/
	private $_rootref;

	/**
	* @var string Root dir for template.
	*/
	private $root = '';

	/**
	* @var string Path of the cache directory for the template
	*/
	public $cachepath = '';

	/**
	* @var array Hash of handle => file path pairs
	*/
	public $files = array();

	/**
	* @var array Hash of handle => filename pairs
	*/
	public $filename = array();

	public $files_inherit = array();
	public $files_template = array();
	public $inherit_root = '';

	public $orig_tpl_inherits_id;

	// this will hash handle names to the compiled/uncompiled code for that handle.
	public $compiled_code = array();

	/**
	* Set template location
	* @access public
	*/
	public function set_template()
	{
		global $phpbb_root_path, $user;

		if (file_exists($phpbb_root_path . 'styles/' . $user->theme['template_path'] . '/template'))
		{
			$this->root = $phpbb_root_path . 'styles/' . $user->theme['template_path'] . '/template';
			$this->cachepath = $phpbb_root_path . 'cache/tpl_' . str_replace('_', '-', $user->theme['template_path']) . '_';

			if ($this->orig_tpl_inherits_id === null)
			{
				$this->orig_tpl_inherits_id = $user->theme['template_inherits_id'];
			}

			$user->theme['template_inherits_id'] = $this->orig_tpl_inherits_id;

			if ($user->theme['template_inherits_id'])
			{
				$this->inherit_root = $phpbb_root_path . 'styles/' . $user->theme['template_inherit_path'] . '/template';
			}
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
	* @param string $template_path Path to template directory
	* @param string $template_name Name of template
	* @param string $fallback_template_path Path to fallback template
	*/
	public function set_custom_template($template_path, $template_name, $fallback_template_path = false)
	{
		global $phpbb_root_path, $user;

		// Make sure $template_path has no ending slash
		if (substr($template_path, -1) == '/')
		{
			$template_path = substr($template_path, 0, -1);
		}

		$this->root = $template_path;
		$this->cachepath = $phpbb_root_path . 'cache/ctpl_' . str_replace('_', '-', $template_name) . '_';

		if ($fallback_template_path !== false)
		{
			if (substr($fallback_template_path, -1) == '/')
			{
				$fallback_template_path = substr($fallback_template_path, 0, -1);
			}

			$this->inherit_root = $fallback_template_path;
			$this->orig_tpl_inherits_id = true;
		}
		else
		{
			$this->orig_tpl_inherits_id = false;
		}

		$this->_rootref = &$this->_tpldata['.'][0];

		return true;
	}

	/**
	* Sets the template filenames for handles. $filename_array
	* should be a hash of handle => filename pairs.
	* @access public
	* @param array $filname_array Should be a hash of handle => filename pairs.
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

			if ($this->inherit_root)
			{
				$this->files_inherit[$handle] = $this->inherit_root . '/' . $filename;
			}
		}

		return true;
	}

	/**
	* Destroy template data set
	* @access public
	*/
	public function destroy()
	{
		$this->_tpldata = array('.' => array(0 => array()));
		$this->_rootref = &$this->_tpldata['.'][0];
	}

	/**
	 * destroy method kept for compatibility.
	 */
	public function __destruct()
	{
		$this->destroy();
	}

	/**
	* Reset/empty complete block
	* @access public
	* @param string $blockname Name of block to destroy
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
	* @param string $handle Handle to display
	* @param bool $include_once Allow multiple inclusions
	* @return bool True on success, false on failure
	*/
	public function display($handle, $include_once = true)
	{
		global $user, $phpbb_hook;

		if (!empty($phpbb_hook) && $phpbb_hook->call_hook(array(__CLASS__, __FUNCTION__), $handle, $include_once, $this))
		{
			if ($phpbb_hook->hook_return(array(__CLASS__, __FUNCTION__)))
			{
				return $phpbb_hook->hook_return_result(array(__CLASS__, __FUNCTION__));
			}
		}

		/*
		if (defined('IN_ERROR_HANDLER'))
		{
			if ((E_NOTICE & error_reporting()) == E_NOTICE)
			{
				error_reporting(error_reporting() ^ E_NOTICE);
			}
		}
		*/

		$_tpldata	= &$this->_tpldata;
		$_rootref	= &$this->_rootref;
		$_lang		= &$user->lang;

		$executor = $this->_tpl_load($handle);

		if ($executor)
		{
			$executor->execute();
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	* Display the handle and assign the output to a template variable or return the compiled result.
	* @access public
	* @param string $handle Handle to operate on
	* @param string $template_var Template variable to assign compiled handle to
	* @param bool $return_content If true return compiled handle, otherwise assign to $template_var
	* @param bool $include_once Allow multiple inclusions of the file
	* @return bool|string false on failure, otherwise if $return_content is true return string of the compiled handle, otherwise return true
	*/
	public function assign_display($handle, $template_var = '', $return_content = true, $include_once = false)
	{
		ob_start();
		$result = $this->display($handle, $include_once);
		$contents = ob_get_clean();
		if ($result === false)
		{
			return false;
		}

		if ($return_content)
		{
			return $contents;
		}

		$this->assign_var($template_var, $contents);

		return true;
	}

	/**
	* Obtains a template executor for a template identified by specified
	* handle. THe template executor can execute the template later.
	*
	* Template source will first be compiled into php code.
	* If template cache is writable the compiled php code will be stored
	* on filesystem and template will not be subsequently recompiled.
	* If template cache is not writable template source will be recompiled
	* every time it is needed. DEBUG_EXTRA define and load_tplcompile
	* configuration setting may be used to force templates to be always
	* recompiled.
	*
	* Returns an object implementing phpbb_template_executor, or null
	* if template loading or compilation failed. Call execute() on the
	* executor to execute the template. This will result in template
	* contents sent to the output stream (unless, of course, output
	* buffering is in effect).
	*
	* @access private
	* @param string $handle Handle of the template to load
	* @return phpbb_template_executor Template executor object, or null on failure
	* @uses template_compile is used to compile template source
	*/
	private function _tpl_load($handle)
	{
		global $user, $phpEx, $config;

		if (!isset($this->filename[$handle]))
		{
			trigger_error("template->_tpl_load(): No file specified for handle $handle", E_USER_ERROR);
		}

		// reload this setting to have the values they had when this object was initialised
		// using set_template or set_custom_template, they might otherwise have been overwritten
		// by other template class instances in between.
		$user->theme['template_inherits_id'] = $this->orig_tpl_inherits_id;

		$filename = $this->cachepath . str_replace('/', '.', $this->filename[$handle]) . '.' . $phpEx;
		$this->files_template[$handle] = (isset($user->theme['template_id'])) ? $user->theme['template_id'] : 0;

		$recompile = false;
		$recompile = (!file_exists($filename) || @filesize($filename) === 0 || ($config['load_tplcompile'] && @filemtime($filename) < @filemtime($this->files[$handle]))) ? true : false;

		if (!$recompile)
		{
			if (defined('DEBUG_EXTRA'))
			{
				$recompile = true;
			}
			else if ($config['load_tplcompile'])
			{
				// No way around it: we need to check inheritance here
				if ($user->theme['template_inherits_id'] && !file_exists($this->files[$handle]))
				{
					$this->files[$handle] = $this->files_inherit[$handle];
					$this->files_template[$handle] = $user->theme['template_inherits_id'];
				}
				$recompile = (@filemtime($filename) < @filemtime($this->files[$handle])) ? true : false;
			}
		}

		// Recompile page if the original template is newer, otherwise load the compiled version
		if (!$recompile)
		{
			return new phpbb_template_executor_include($filename);
		}

		// Inheritance - we point to another template file for this one.
		if (isset($user->theme['template_inherits_id']) && $user->theme['template_inherits_id'] && !file_exists($this->files[$handle]))
		{
			$this->files[$handle] = $this->files_inherit[$handle];
			$this->files_template[$handle] = $user->theme['template_inherits_id'];
		}

		$source_file = $this->_source_file_for_handle($handle);

		$compile = new phpbb_template_compile();

		$output_file = $this->_compiled_file_for_handle($handle);
		if ($compile->compile_file_to_file($source_file, $output_file) !== false)
		{
			$executor = new phpbb_template_executor_include($output_file);
		}
		else if (($code = $compile->compile_file($source_file)) !== false)
		{
			$executor = new phpbb_template_executor_eval($code);
		}
		else
		{
			$executor = null;
		}

		return $executor;
	}

	/**
	* Resolves template handle $handle to source file path.
	* @access private
	* @param string $handle Template handle (i.e. "friendly" template name)
	* @return string Source file path
	*/
	private function _source_file_for_handle($handle)
	{
		// If we don't have a file assigned to this handle, die.
		if (!isset($this->files[$handle]))
		{
			trigger_error("_source_file_for_handle(): No file specified for handle $handle", E_USER_ERROR);
		}

		$source_file = $this->files[$handle];

		// Try and open template for reading
		if (!file_exists($source_file))
		{
			trigger_error("_source_file_for_handle(): File $source_file does not exist", E_USER_ERROR);
		}
		return $source_file;
	}

	/**
	* Determines compiled file path for handle $handle.
	* @access private
	* @param string $handle Template handle (i.e. "friendly" template name)
	* @return string Compiled file path
	*/
	private function _compiled_file_for_handle($handle)
	{
		global $phpEx;

		$compiled_file = $this->cachepath . str_replace('/', '.', $this->filename[$handle]) . '.' . $phpEx;
		return $compiled_file;
	}

	/**
	* Assign key variable pairs from an array
	* @access public
	* @param array $vararray A hash of variable name => value pairs
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
	* @param string $varname Variable name
	* @param string $varval Value to assign to variable
	*/
	public function assign_var($varname, $varval)
	{
		$this->_rootref[$varname] = $varval;

		return true;
	}

	/**
	* Assign key variable pairs from an array to a specified block
	* @access public
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
			// Nested block.
			$blocks = explode('.', $blockname);
			$blockcount = sizeof($blocks) - 1;

			$block = &$this->_tpldata;
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
			$block = &$this->_tpldata[$blockname];
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
	* Include a separate template
	* @access private
	* @param string $filename Template filename to include
	* @param bool $include True to include the file, false to just load it
	* @uses template_compile is used to compile uncached templates
	*/
	private function _tpl_include($filename, $include = true)
	{
		$handle = $filename;
		$this->filename[$handle] = $filename;
		$this->files[$handle] = $this->root . '/' . $filename;
		if ($this->inherit_root)
		{
			$this->files_inherit[$handle] = $this->inherit_root . '/' . $filename;
		}

		$executor = $this->_tpl_load($handle);

		if ($executor)
		{
			$executor->execute();
		}
		else
		{
			// What should we do here?
		}
	}

	/**
	* Include a php-file
	* @access private
	*/
	private function _php_include($filename)
	{
		global $phpbb_root_path;

		$file = $phpbb_root_path . $filename;

		if (!file_exists($file))
		{
			// trigger_error cannot be used here, as the output already started
			echo 'template->_php_include(): File ' . htmlspecialchars($file) . ' does not exist or is empty';
			return;
		}
		include($file);
	}
}
