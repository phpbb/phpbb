<?php
/**
*
* @package phpBB3
* @copyright (c) 2005 phpBB Group, sections (c) 2001 ispi of Lincoln Inc
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
* Base Style class.
* @package phpBB3
*/
class phpbb_style
{
	/**
	* Template class.
	* Handles everything related to templates.
	* @var phpbb_template
	*/
	private $template;

	/**
	* phpBB root path
	* @var string
	*/
	private $phpbb_root_path;

	/**
	* PHP file extension
	* @var string
	*/
	private $php_ext;

	/**
	* phpBB config instance
	* @var phpbb_config
	*/
	private $config;

	/**
	* Current user
	* @var phpbb_user
	*/
	private $user;

	/**
	* Style resource locator
	* @var phpbb_style_resource_locator
	*/
	private $locator;

	/**
	* Style path provider
	* @var phpbb_style_path_provider
	*/
	private $provider;

	/**
	* Constructor.
	*
	* @param string $phpbb_root_path phpBB root path
	* @param user $user current user
	* @param phpbb_style_resource_locator $locator style resource locator
	* @param phpbb_style_path_provider $provider style path provider
	* @param phpbb_template $template template
	*/
	public function __construct($phpbb_root_path, $php_ext, $config, $user, phpbb_style_resource_locator $locator, phpbb_style_path_provider_interface $provider, phpbb_template $template)
	{
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		$this->config = $config;
		$this->user = $user;
		$this->locator = $locator;
		$this->provider = $provider;
		$this->template = $template;
	}

	/**
	* Set style location based on (current) user's chosen style.
	*/
	public function set_style()
	{
		$style_path = $this->user->style['style_path'];
		$style_dirs = ($this->user->style['style_parent_id']) ? array_reverse(explode('/', $this->user->style['style_parent_tree'])) : array();

		$names = array($style_path);
		foreach ($style_dirs as $dir)
		{
			$names[] = $dir;
		}
		// Add 'all' path, used as last fallback path by events and extensions
		//$names[] = 'all';

		$paths = array();
		foreach ($names as $name)
		{
			$paths[] = $this->get_style_path($name);
		}

		return $this->set_custom_style($style_path, $paths, $names);
	}

	/**
	* Set custom style location (able to use directory outside of phpBB).
	*
	* Note: Templates are still compiled to phpBB's cache directory.
	*
	* @param string $name Name of style, used for cache prefix. Examples: "admin", "prosilver"
	* @param array or string $paths Array of style paths, relative to current root directory
	* @param array $names Array of names of templates in inheritance tree order, used by extensions. If empty, $name will be used.
	* @param string $template_path Path to templates, relative to style directory. False if path should be set to default (templates/).
	*/
	public function set_custom_style($name, $paths, $names = array(), $template_path = false)
	{
		if (is_string($paths))
		{
			$paths = array($paths);
		}

		if (empty($names))
		{
			$names = array($name);
		}
		$this->names = $names;

		$this->provider->set_styles($paths);
		$this->locator->set_paths($this->provider);

		if ($template_path !== false)
		{
			$this->locator->set_template_path($template_path);

			$appended_paths = array();
			foreach ($paths as $path)
			{
				$appended_paths[] = $path . '/' . $template_path;
			}

			$this->template->set_style_names($names, $appended_paths);
		}
		else
		{
			$this->locator->set_default_template_path();

			$appended_paths = array();
			foreach ($paths as $path)
			{
				$appended_paths[] = $path . '/template/';
			}

			$this->template->set_style_names($names, $appended_paths);
		}

		return true;
	}

	/**
	* Get location of style directory for specific style_path
	*
	* @param string $path Style path, such as "prosilver"
	* @return string Path to style directory, relative to current path
	*/
	public function get_style_path($path)
	{
		return $this->phpbb_root_path . 'styles/' . $path;
	}

	/**
	* Defines a prefix to use for style paths in extensions
	*
	* @param string $ext_dir_prefix The prefix including trailing slash
	* @return null
	*/
	public function set_ext_dir_prefix($ext_dir_prefix)
	{
		$this->provider->set_ext_dir_prefix($ext_dir_prefix);
	}

	/**
	* Locates source file path, accounting for styles tree and verifying that
	* the path exists.
	*
	* @param string or array $files List of files to locate. If there is only
	*				one file, $files can be a string to make code easier to read.
	* @param bool $return_default Determines what to return if file does not
	*				exist. If true, function will return location where file is
	*				supposed to be. If false, function will return false.
	* @param bool $return_full_path If true, function will return full path
	*				to file. If false, function will return file name. This
	*				parameter can be used to check which one of set of files
	*				is available.
	* @return string or boolean Source file path if file exists or $return_default is
	*				true. False if file does not exist and $return_default is false
	*/
	public function locate($files, $return_default = false, $return_full_path = true)
	{
		// convert string to array
		if (is_string($files))
		{
			$files = array($files);
		}

		// use resource locator to find files
		return $this->locator->get_first_file_location($files, $return_default, $return_full_path);
	}
}
