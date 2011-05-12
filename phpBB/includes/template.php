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
	/**
	* @var phpbb_template_context Template context.
	* Stores template data used during template rendering.
	* @access private
	*/
	private $context;

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

	/**
	* @var string phpBB root path
	*/
	private $phpbb_root_path;

	/**
	* @var phpEx PHP file extension
	*/
	private $phpEx;

	/**
	* @var phpbb_config phpBB config instance
	*/
	private $config;

	/**
	* @var user current user
	*/
	private $user;

	/**
	* Constructor.
	*
	* @param string $phpbb_root_path phpBB root path
	* @param user $user current user
	*/
	public function __construct($phpbb_root_path, $phpEx, $config, $user)
	{
		$this->phpbb_root_path = $phpbb_root_path;
		$this->phpEx = $phpEx;
		$this->config = $config;
		$this->user = $user;
	}

	/**
	* Set template location
	* @access public
	*/
	public function set_template()
	{
		$template_path = $this->user->theme['template_path'];
		if (file_exists($this->phpbb_root_path . 'styles/' . $template_path . '/template'))
		{
			$this->root = $this->phpbb_root_path . 'styles/' . $template_path . '/template';
			$this->cachepath = $this->phpbb_root_path . 'cache/tpl_' . str_replace('_', '-', $template_path) . '_';

			if ($this->orig_tpl_inherits_id === null)
			{
				$this->orig_tpl_inherits_id = $this->user->theme['template_inherits_id'];
			}

			$this->user->theme['template_inherits_id'] = $this->orig_tpl_inherits_id;

			if ($this->user->theme['template_inherits_id'])
			{
				$this->inherit_root = $this->phpbb_root_path . 'styles/' . $this->user->theme['template_inherit_path'] . '/template';
			}
		}
		else
		{
			trigger_error('Template path could not be found: styles/' . $template_path . '/template', E_USER_ERROR);
		}

		$this->context = new phpbb_template_context();

		return true;
	}

	/**
	* Set custom template location (able to use directory outside of phpBB).
	*
	* Note: Templates are still compiled to phpBB's cache directory.
	*
	* @access public
	* @param string $template_path Path to template directory
	* @param string $template_name Name of template
	* @param string $fallback_template_path Path to fallback template
	*/
	public function set_custom_template($template_path, $template_name, $fallback_template_path = false)
	{
		// Make sure $template_path has no ending slash
		if (substr($template_path, -1) == '/')
		{
			$template_path = substr($template_path, 0, -1);
		}

		$this->root = $template_path;
		$this->cachepath = $this->phpbb_root_path . 'cache/ctpl_' . str_replace('_', '-', $template_name) . '_';

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

		$this->context = new phpbb_template_context();

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
	* Clears all variables and blocks assigned to this template.
	* @access public
	*/
	public function destroy()
	{
		$this->context->clear();
	}

	/**
	* Reset/empty complete block
	* @access public
	* @param string $blockname Name of block to destroy
	*/
	public function destroy_block_vars($blockname)
	{
		$this->context->destroy_block_vars($blockname);
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
		$result = $this->call_hook($handle, $include_once);
		if ($result !== false)
		{
			return $result[0];
		}

		$renderer = $this->_tpl_load($handle);

		if ($renderer)
		{
			$renderer->render($this->context, $this->get_lang());
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	* Calls hook if any is defined.
	* @param string $handle Template handle being displayed.
	* @param bool $include_once Allow multiple inclusions
	*/
	private function call_hook($handle, $include_once)
	{
		global $phpbb_hook;

		if (!empty($phpbb_hook) && $phpbb_hook->call_hook(array(__CLASS__, __FUNCTION__), $handle, $include_once, $this))
		{
			if ($phpbb_hook->hook_return(array(__CLASS__, __FUNCTION__)))
			{
				$result = $phpbb_hook->hook_return_result(array(__CLASS__, __FUNCTION__));
				return array($result);
			}
		}

		return false;
	}

	/**
	* Obtains language array.
	* This is either lang property of $user property, or if
	* it is not set an empty array.
	* @return array language entries
	*/
	public function get_lang()
	{
		if (isset($this->user->lang))
		{
			$lang = $this->user->lang;
		}
		else
		{
			$lang = array();
		}
		return $lang;
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
	* Obtains a template renderer for a template identified by specified
	* handle. The template renderer can display the template later.
	*
	* Template source will first be compiled into php code.
	* If template cache is writable the compiled php code will be stored
	* on filesystem and template will not be subsequently recompiled.
	* If template cache is not writable template source will be recompiled
	* every time it is needed. DEBUG_EXTRA define and load_tplcompile
	* configuration setting may be used to force templates to be always
	* recompiled.
	*
	* Returns an object implementing phpbb_template_renderer, or null
	* if template loading or compilation failed. Call render() on the
	* renderer to display the template. This will result in template
	* contents sent to the output stream (unless, of course, output
	* buffering is in effect).
	*
	* @access private
	* @param string $handle Handle of the template to load
	* @return phpbb_template_renderer Template renderer object, or null on failure
	* @uses template_compile is used to compile template source
	*/
	private function _tpl_load($handle)
	{
		if (!isset($this->filename[$handle]))
		{
			trigger_error("template->_tpl_load(): No file specified for handle $handle", E_USER_ERROR);
		}

		// reload this setting to have the values they had when this object was initialised
		// using set_template or set_custom_template, they might otherwise have been overwritten
		// by other template class instances in between.
		$this->user->theme['template_inherits_id'] = $this->orig_tpl_inherits_id;

		$compiled_path = $this->cachepath . str_replace('/', '.', $this->filename[$handle]) . '.' . $this->phpEx;
		$this->files_template[$handle] = (isset($this->user->theme['template_id'])) ? $this->user->theme['template_id'] : 0;

		$recompile = defined('DEBUG_EXTRA') ||
			!file_exists($compiled_path) ||
			@filesize($compiled_path) === 0 ||
			($this->config['load_tplcompile'] && @filemtime($compiled_path) < @filemtime($this->files[$handle]));

		if (!$recompile && $this->config['load_tplcompile'])
		{
			// No way around it: we need to check inheritance here
			if ($this->user->theme['template_inherits_id'] && !file_exists($this->files[$handle]))
			{
				$this->files[$handle] = $this->files_inherit[$handle];
				$this->files_template[$handle] = $this->user->theme['template_inherits_id'];
			}
			$recompile = (@filemtime($compiled_path) < @filemtime($this->files[$handle])) ? true : false;
		}

		// Recompile page if the original template is newer, otherwise load the compiled version
		if (!$recompile)
		{
			return new phpbb_template_renderer_include($compiled_path, $this);
		}

		// Inheritance - we point to another template file for this one.
		if (isset($this->user->theme['template_inherits_id']) && $this->user->theme['template_inherits_id'] && !file_exists($this->files[$handle]))
		{
			$this->files[$handle] = $this->files_inherit[$handle];
			$this->files_template[$handle] = $this->user->theme['template_inherits_id'];
		}

		$source_file = $this->_source_file_for_handle($handle);

		$compile = new phpbb_template_compile();

		$output_file = $this->_compiled_file_for_handle($handle);
		if ($compile->compile_file_to_file($source_file, $output_file) !== false)
		{
			$renderer = new phpbb_template_renderer_include($output_file, $this);
		}
		else if (($code = $compile->compile_file($source_file)) !== false)
		{
			$renderer = new phpbb_template_renderer_eval($code, $this);
		}
		else
		{
			$renderer = null;
		}

		return $renderer;
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
		$compiled_file = $this->cachepath . str_replace('/', '.', $this->filename[$handle]) . '.' . $this->phpEx;
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
			$this->assign_var($key, $val);
		}
	}

	/**
	* Assign a single variable to a single key
	* @access public
	* @param string $varname Variable name
	* @param string $varval Value to assign to variable
	*/
	public function assign_var($varname, $varval)
	{
		$this->context->assign_var($varname, $varval);
	}

	// Docstring is copied from phpbb_template_context method with the same name.
	/**
	* Assign key variable pairs from an array to a specified block
	* @access public
	* @param string $blockname Name of block to assign $vararray to
	* @param array $vararray A hash of variable name => value pairs
	*/
	public function assign_block_vars($blockname, array $vararray)
	{
		return $this->context->assign_block_vars($blockname, $vararray);
	}

	// Docstring is copied from phpbb_template_context method with the same name.
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
		return $this->context->alter_block_array($blockname, $vararray, $key, $mode);
	}

	/**
	* Include a separate template
	* @access private
	* @param string $filename Template filename to include
	* @param bool $include True to include the file, false to just load it
	* @uses template_compile is used to compile uncached templates
	*/
	public function _tpl_include($filename, $include = true)
	{
		$handle = $filename;
		$this->filename[$handle] = $filename;
		$this->files[$handle] = $this->root . '/' . $filename;
		if ($this->inherit_root)
		{
			$this->files_inherit[$handle] = $this->inherit_root . '/' . $filename;
		}

		$renderer = $this->_tpl_load($handle);

		if ($renderer)
		{
			$renderer->render($this->context, $this->get_lang());
		}
		else
		{
			// trigger_error cannot be used here, as the output already started
			echo 'template->_tpl_include(): Failed including ' . htmlspecialchars($handle) . "\n";
		}
	}

	/**
	* Include a php-file
	* @access private
	*/
	private function _php_include($filename)
	{
		$file = $this->phpbb_root_path . $filename;

		if (!file_exists($file))
		{
			// trigger_error cannot be used here, as the output already started
			echo 'template->_php_include(): File ' . htmlspecialchars($file) . " does not exist\n";
			return;
		}
		include($file);
	}
}
