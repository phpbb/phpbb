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
	* @var phpbb_style_template Template class.
	* Handles everything related to templates.
	*/
	private $template;

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
	* @param phpbb_style_template $template template
	*/
	public function __construct($phpbb_root_path, $phpEx, $config, $user, phpbb_style_resource_locator $locator, phpbb_style_path_provider_interface $provider, phpbb_style_template $template)
	{
		$this->phpbb_root_path = $phpbb_root_path;
		$this->phpEx = $phpEx;
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
		$style_name = $this->user->theme['style_path'];
		$style_dirs = ($this->user->theme['style_parent_id']) ? array_reverse(explode('/', $this->user->theme['style_parent_tree'])) : array();
		$paths = array($this->get_style_path($style_name));
		foreach ($style_dirs as $dir)
		{
			$paths[] = $this->get_style_path($dir);
		}

		// Add 'all' path, used as last fallback path by hooks and extensions
		$paths[] = $this->get_style_path('all');

		return $this->set_custom_style($style_name, $paths);
	}

	/**
	* Set custom style location (able to use directory outside of phpBB).
	*
	* Note: Templates are still compiled to phpBB's cache directory.
	*
	* @param string $name Name of style, used for cache prefix. Examples: "admin", "prosilver"
	* @param array or string $paths Array of style paths, relative to current root directory
	* @param string $template_path Path to templates, relative to style directory. False if path should not be changed.
	*/
	public function set_custom_style($name, $paths, $template_path = false)
	{
		if (is_string($paths))
		{
			$paths = array($paths);
		}

		$this->provider->set_styles($paths);
		$this->locator->set_paths($this->provider);

		$this->template->cachepath = $this->phpbb_root_path . 'cache/tpl_' . str_replace('_', '-', $name) . '_';

		$this->template->context = new phpbb_style_template_context();

		if ($template_path !== false)
		{
			$this->template->template_path = $this->locator->template_path = $template_path;
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
}
