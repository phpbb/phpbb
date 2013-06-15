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

/**
* Twig Template class.
* @package phpBB3
*/
class phpbb_template_twig implements phpbb_template
{
	/**
	* Template context.
	* Stores template data used during template rendering.
	* @var phpbb_template_context
	*/
	protected $context;

	/**
	* Path of the cache directory for the template
	* @var string
	*/
	public $cachepath = '';

	/**
	* phpBB root path
	* @var string
	*/
	protected $phpbb_root_path;

	/**
	* PHP file extension
	* @var string
	*/
	protected $php_ext;

	/**
	* phpBB config instance
	* @var phpbb_config
	*/
	protected $config;

	/**
	* Current user
	* @var phpbb_user
	*/
	protected $user;

	/**
	* Template locator
	* @var phpbb_template_locator
	*/
	protected $locator;

	/**
	* Extension manager.
	*
	* @var phpbb_extension_manager
	*/
	protected $extension_manager;

	/**
	* Name of the style that the template being compiled and/or rendered
	* belongs to, and its parents, in inheritance tree order.
	*
	* Used to invoke style-specific template events.
	*
	* @var array
	*/
	protected $style_names;

	/**
	* Twig Environment
	*
	* @var Twig_Environment
	*/
	protected $twig;

	/**
	* Constructor.
	*
	* @todo remove unnecessary dependencies
	*
	* @param string $phpbb_root_path phpBB root path
	* @param user $user current user
	* @param phpbb_template_locator $locator template locator
	* @param phpbb_template_context $context template context
	* @param phpbb_extension_manager $extension_manager extension manager, if null then template events will not be invoked
	*/
	public function __construct($phpbb_root_path, $php_ext, $config, $user, phpbb_template_locator $locator, phpbb_template_context $context, phpbb_extension_manager $extension_manager = null)
	{
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		$this->config = $config;
		$this->user = $user;
		$this->locator = $locator;
		$this->context = $context;
		$this->extension_manager = $extension_manager;

		$this->cachepath = $phpbb_root_path . 'cache/twig/';

		// Setup loader with __main__ paths
		$loader = new Twig_Loader_Filesystem(array(
			$phpbb_root_path . 'styles/prosilver/template/',
		), $this->locator);

		// Add core namespace
		$loader->addPath($this->phpbb_root_path . 'styles/prosilver/template/', 'core');

		// Add admin namespace
		// @todo use phpbb_admin path
		$loader->addPath($this->phpbb_root_path . 'adm/style/', 'admin');

		// Add all namespaces for all extensions
		if ($this->extension_manager instanceof phpbb_extension_manager)
		{
			foreach ($this->extension_manager->all_enabled() as $ext_namespace => $ext_path)
			{
				// @todo proper style chain
				$loader->addPath($ext_path . 'styles/prosilver/', $ext_namespace);
				$loader->addPath($ext_path . 'styles/all/', $ext_namespace);
			}
		}

		$this->twig = new Twig_Environment($loader, array(
		    'cache'			=> $this->cachepath,
		    'debug'			=> true, // @todo
		    'auto_reload'	=> true, // @todo
    		'autoescape'	=> false,
		));

		// Clear previous cache files (while WIP)
		// @todo remove
		if (is_dir($this->cachepath))
		{
			$this->twig->clearCacheFiles();
		}

		$this->twig->addExtension(new phpbb_template_twig_extension);

		$lexer = new phpbb_template_twig_lexer($this->twig);

		$this->twig->setLexer($lexer);
	}

	/**
	* Sets the template filenames for handles.
	*
	* @param array $filename_array Should be a hash of handle => filename pairs.
	* @return phpbb_template $this
	*/
	public function set_filenames(array $filename_array)
	{
		$this->locator->set_filenames($filename_array);

		return $this;
	}

	/**
	* Sets the style names corresponding to style hierarchy being compiled
	* and/or rendered.
	*
	* @param array $style_names List of style names in inheritance tree order
	* @return phpbb_template $this
	*/
	public function set_style_names(array $style_names, $style_paths = array())
	{
		$this->style_names = $style_names;

		$this->twig->getLoader()->setPaths($style_paths);

		return $this;
	}

	/**
	* Clears all variables and blocks assigned to this template.
	*
	* @return phpbb_template $this
	*/
	public function destroy()
	{
		$this->context = array();

		return $this;
	}

	/**
	* Reset/empty complete block
	*
	* @param string $blockname Name of block to destroy
	*/
	public function destroy_block_vars($blockname)
	{
		return $this->context->destroy_block_vars($blockname);
	}

	/**
	* Display a template for provided handle.
	*
	* The template will be loaded and compiled, if necessary, first.
	*
	* This function calls hooks.
	*
	* @param string $handle Handle to display
	* @return bool True on success, false on failure
	*/
	public function display($handle)
	{
		$result = $this->call_hook($handle, __FUNCTION__);
		if ($result !== false)
		{
			return $result[0];
		}

		try
		{
			echo $this->twig->render($this->locator->get_filename_for_handle($handle), $this->get_template_vars());
		}
		catch (Twig_Error $e)
		{
			throw $e;
		}

		return true;
	}

	/**
	* Calls hook if any is defined.
	*
	* @param string $handle Template handle being displayed.
	* @param string $method Method name of the caller.
	*/
	protected function call_hook($handle, $method)
	{
		global $phpbb_hook;

		if (!empty($phpbb_hook) && $phpbb_hook->call_hook(array(__CLASS__, $method), $handle, $this))
		{
			if ($phpbb_hook->hook_return(array(__CLASS__, $method)))
			{
				$result = $phpbb_hook->hook_return_result(array(__CLASS__, $method));
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
	* Display the handle and assign the output to a template variable
	* or return the compiled result.
	*
	* @param string $handle Handle to operate on
	* @param string $template_var Template variable to assign compiled handle to
	* @param bool $return_content If true return compiled handle, otherwise assign to $template_var
	* @return bool|string false on failure, otherwise if $return_content is true return string of the compiled handle, otherwise return true
	*/
	public function assign_display($handle, $template_var = '', $return_content = true)
	{
		ob_start();
		$result = $this->display($handle);
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
	* Assign key variable pairs from an array
	*
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
	* Assign a single scalar value to a single key.
	*
	* Value can be a string, an integer or a boolean.
	*
	* @param string $varname Variable name
	* @param string $varval Value to assign to variable
	*/
	public function assign_var($varname, $varval)
	{
		return $this->context->assign_var($varname, $varval);
	}

	/**
	* Append text to the string value stored in a key.
	*
	* Text is appended using the string concatenation operator (.).
	*
	* @param string $varname Variable name
	* @param string $varval Value to append to variable
	*/
	public function append_var($varname, $varval)
	{
		return $this->context->append_var($varname, $varval);
	}

	// Docstring is copied from phpbb_template_context method with the same name.
	/**
	* Assign key variable pairs from an array to a specified block
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
	*/
	public function alter_block_array($blockname, array $vararray, $key = false, $mode = 'insert')
	{
		return $this->context->alter_block_array($blockname, $vararray, $key, $mode);
	}

	/**
	* Get template vars in a format Twig will use (from the context)
	*
	* @return array
	*/
	protected function get_template_vars()
	{
		$vars = array();

		// Work-around for now
		foreach ($this->user->lang as $key => $value)
		{
			if (!is_string($value))
			{
				continue;
			}

			$vars['L_' . strtoupper($key)] = $value;
			$vars['LA_' . strtoupper($key)] = addslashes($value);
		}

		$vars = array_merge(
			$vars,
			$this->context->get_rootref(),
			$this->context->get_tpldata()
		);

		// cleanup
		unset($vars['.']);

		return $vars;
	}
}
